<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DispatchLog extends Model
{
    /** @use HasFactory<\Database\Factories\DispatchLogFactory> */
    use HasFactory;

    protected $fillable = ['dispatch_id', 'user_id', 'action', 'remarks'];
    public function dispatch() { return $this->belongsTo(Dispatch::class); }
    public function user() { return $this->belongsTo(User::class); }
}
