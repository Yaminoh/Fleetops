<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TwoFactorLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_remembered_browser_skips_otp_after_logout_but_requires_it_after_seven_days(): void
    {
        $this->freezeTime();
        $this->fakeBrevoResponse(201);
        $user = User::factory()->create(['status' => 'active']);
        $credentials = ['email' => $user->email, 'password' => 'password'];
        $this->post(route('login.store'), $credentials)->assertRedirect(route('two-factor.challenge'));
        $response = $this->post(route('two-factor.verify'), [
            'code' => $user->fresh()->two_factor_code,
            'remember' => '1',
        ]);
        $response->assertCookie('fleetops_otp_device');
        $cookie = $response->getCookie('fleetops_otp_device');
        $this->assertSame(now()->addDays(7)->getTimestamp(), $cookie->getExpiresTime());
        $this->assertDatabaseCount('trusted_devices', 1);
        $this->post(route('logout'))->assertRedirect('/login');

        Http::fake();
        $this->withCookie('fleetops_otp_device', $cookie->getValue())
            ->post(route('login.store'), $credentials)->assertRedirect('/dashboard');
        Http::assertNothingSent();
        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'));
        $this->travel(7)->days();
        $this->fakeBrevoResponse(201);
        $this->post(route('login.store'), $credentials)->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest();
        $this->assertBrevoRequestWasSentTo($user);
    }

    public function test_tampered_browser_token_does_not_skip_otp(): void
    {
        $this->fakeBrevoResponse(201);
        $user = User::factory()->create(['status' => 'active']);
        $selector = str_repeat('a', 24);
        \App\Models\TrustedDevice::create([
            'user_id' => $user->id,
            'selector' => $selector,
            'token_hash' => hash('sha256', str_repeat('b', 64).'|'.$user->getAuthPassword()),
            'expires_at' => now()->addDays(7),
        ]);
        $this->withCookie('fleetops_otp_device', $selector.':'.str_repeat('c', 64))
            ->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest();
        $this->assertBrevoRequestWasSentTo($user);
    }

    public function test_password_change_invalidates_remembered_otp_verification(): void
    {
        $this->fakeBrevoResponse(201);
        $user = User::factory()->create(['status' => 'active']);
        $selector = str_repeat('a', 24);
        $token = str_repeat('b', 64);
        \App\Models\TrustedDevice::create([
            'user_id' => $user->id,
            'selector' => $selector,
            'token_hash' => hash('sha256', $token.'|'.$user->getAuthPassword()),
            'expires_at' => now()->addDays(7),
        ]);
        $user->setPassword('new-password');
        $user->save();

        $this->withCookie('fleetops_otp_device', $selector.':'.$token)
            ->post(route('login.store'), ['email' => $user->email, 'password' => 'new-password'])
            ->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest();
        $this->assertBrevoRequestWasSentTo($user);
    }

    public function test_remember_checkbox_is_on_the_otp_page_only(): void
    {
        $this->get(route('login'))->assertOk()->assertDontSee('Remember me for 7 days');

        $user = User::factory()->create(['status' => 'active']);
        $this->withSession(['two_factor.user_id' => $user->id])
            ->get(route('two-factor.challenge'))
            ->assertOk()
            ->assertSeeInOrder(['Verification code', 'Remember me for 7 days', 'Verify &amp; sign in'], false)
            ->assertSee('name="remember"', false);
    }

    public function test_remember_me_is_only_issued_after_otp_and_expires_in_seven_days(): void
    {
        $this->freezeTime();
        $this->fakeBrevoResponse(201);
        $user = User::factory()->create(['status' => 'active']);
        $cookieName = auth('web')->getRecallerName();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('two-factor.challenge'))->assertCookieMissing($cookieName);
        $this->assertGuest();

        $response = $this->post(route('two-factor.verify'), [
            'code' => $user->fresh()->two_factor_code,
            'remember' => '1',
        ]);
        $response->assertRedirect('/dashboard')->assertCookie($cookieName);
        $this->assertAuthenticatedAs($user);
        $cookie = collect($response->headers->getCookies())->first(fn ($cookie) => $cookie->getName() === $cookieName);
        $this->assertSame(now()->addDays(7)->getTimestamp(), $cookie->getExpiresTime());
        $this->assertNull($user->fresh()->two_factor_code);
        $response->assertSessionMissing('two_factor');
    }

    public function test_otp_login_without_remember_does_not_issue_a_remember_cookie(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->two_factor_code = '123456';
        $user->two_factor_expires_at = now()->addMinutes(10);
        $user->save();

        $this->withSession(['two_factor.user_id' => $user->id])
            ->post(route('two-factor.verify'), ['code' => '123456'])
            ->assertRedirect('/dashboard')
            ->assertCookieMissing(auth('web')->getRecallerName());
        $this->assertAuthenticatedAs($user);
    }

    public function test_expired_code_cannot_sign_in_or_create_a_remember_cookie(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->two_factor_code = '123456';
        $user->two_factor_expires_at = now()->subMinute();
        $user->save();

        $this->withSession(['two_factor.user_id' => $user->id])
            ->post(route('two-factor.verify'), ['code' => '123456', 'remember' => '1'])
            ->assertSessionHasErrors('code')
            ->assertCookieMissing(auth('web')->getRecallerName());
        $this->assertGuest();
    }

    public function test_user_deactivated_during_otp_challenge_cannot_sign_in(): void
    {
        $user = User::factory()->create(['status' => 'inactive']);
        $user->two_factor_code = '123456';
        $user->two_factor_expires_at = now()->addMinutes(10);
        $user->save();

        $this->withSession(['two_factor.user_id' => $user->id])
            ->post(route('two-factor.verify'), ['code' => '123456'])
            ->assertRedirect(route('login'))
            ->assertSessionMissing('two_factor');
        $this->assertGuest();
    }

    public function test_active_admin_login_sends_a_code_and_redirects_to_the_two_factor_challenge(): void
    {
        $this->assertLoginStartsTwoFactorForRole('Admin');
    }

    public function test_active_staff_login_sends_a_code_and_redirects_to_the_two_factor_challenge(): void
    {
        $this->assertLoginStartsTwoFactorForRole('Staff');
    }

    public function test_brevo_api_failure_does_not_return_server_error_or_leave_an_unsent_code(): void
    {
        $this->fakeBrevoResponse(401);

        $user = User::factory()->create([
            'role' => 'Admin',
            'status' => 'active',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertLessThan(500, $response->getStatusCode());

        $user->refresh();
        $this->assertNull($user->two_factor_code);
        $this->assertNull($user->two_factor_expires_at);
        $this->assertBrevoRequestWasSentTo($user);
    }

    private function assertLoginStartsTwoFactorForRole(string $role): void
    {
        $this->fakeBrevoResponse(201);

        $user = User::factory()->create([
            'role' => $role,
            'status' => 'active',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('two-factor.challenge'));
        $response->assertSessionHas('two_factor.user_id', $user->id);

        $user->refresh();
        $this->assertNotEmpty($user->two_factor_code);
        $this->assertTrue($user->two_factor_expires_at->isFuture());
        $this->assertBrevoRequestWasSentTo($user);
    }

    private function fakeBrevoResponse(int $status): void
    {
        config([
            'services.brevo.api_key' => 'test-brevo-api-key',
            'mail.from.address' => 'fleetops@example.test',
            'mail.from.name' => 'Fleetops Test',
        ]);

        Http::fake([
            'https://api.brevo.com/v3/smtp/email' => Http::response([], $status),
        ]);
    }

    private function assertBrevoRequestWasSentTo(User $user): void
    {
        Http::assertSent(function (Request $request) use ($user): bool {
            $payload = $request->data();

            return $request->method() === 'POST'
                && $request->url() === 'https://api.brevo.com/v3/smtp/email'
                && $request->hasHeader('api-key', 'test-brevo-api-key')
                && ($payload['sender']['email'] ?? null) === 'fleetops@example.test'
                && ($payload['sender']['name'] ?? null) === 'Fleetops Test'
                && ($payload['to'][0]['email'] ?? null) === $user->email
                && ($payload['subject'] ?? null) === 'Your sign-in verification code'
                && str_contains((string) ($payload['textContent'] ?? ''), (string) $user->fresh()->two_factor_code);
        });
    }
}
