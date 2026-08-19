<section class="stats-grid">
    <?php foreach ($dashboard['stats'] as $stat): ?>
        <article class="stat-card<?= $stat['positive'] ? '' : ' negative-card' ?>">
            <div class="stat-heading"><?= htmlspecialchars($stat['title']) ?></div>
            <div class="stat-value"><?= $stat['currency'] ? htmlspecialchars($stat['currency_symbol'] . number_format($stat['value'])) : htmlspecialchars((string)$stat['value']) ?></div>
            <div class="stat-meta <?= $stat['positive'] ? 'positive' : 'negative' ?>"><?= htmlspecialchars($stat['meta']) ?></div>
        </article>
    <?php endforeach; ?>
</section>

<section class="content-grid">
    <div class="content-column">
        <article class="panel">
            <div class="panel-header">
                <div>
                    <p class="eyebrow">Fleet status</p>
                    <h3>Vehicle Availability Overview</h3>
                </div>
                <button class="pill-button">Export</button>
            </div>
            <div class="availability-layout">
                <div class="chart-card">
                    <div class="pie-chart"></div>
                    <div class="chart-caption">
                        <div><span class="dot teal"></span> Available</div>
                        <div><span class="dot orange"></span> Booked</div>
                        <div><span class="dot blue"></span> Maintenance</div>
                        <div><span class="dot gray"></span> Delayed</div>
                        <div><span class="dot gold"></span> Unavailable</div>
                    </div>
                </div>
                <div class="legend-table">
                    <div class="legend-row"><span>Available</span><strong>198</strong></div>
                    <div class="legend-row"><span>Booked</span><strong>27</strong></div>
                    <div class="legend-row"><span>Maintenance</span><strong>16</strong></div>
                    <div class="legend-row"><span>Delayed</span><strong>9</strong></div>
                    <div class="legend-row"><span>Unavailable</span><strong>6</strong></div>
                </div>
            </div>
        </article>

        <article class="panel">
            <div class="panel-header">
                <div>
                    <p class="eyebrow">Dispatch queue</p>
                    <h3>Pending Vehicle Reservations</h3>
                </div>
                <button class="pill-button">View All</button>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Driver / Employee</th>
                            <th>Vehicle Type</th>
                            <th>Reservation Date</th>
                            <th>Duration</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dashboard['reservations'] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= htmlspecialchars($item['vehicle']) ?></td>
                                <td><?= htmlspecialchars($item['date']) ?></td>
                                <td><?= htmlspecialchars($item['duration']) ?></td>
                                <td><span class="status-pill <?= $item['status'] === 'Pending' ? 'status-pending' : 'status-approved' ?>"><?= htmlspecialchars($item['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </article>
    </div>

    <div class="content-column">
        <article class="panel">
            <div class="panel-header">
                <div>
                    <p class="eyebrow">Operations</p>
                    <h3>Recent Alerts & Fleet Events</h3>
                </div>
            </div>
            <div class="list-stack">
                <?php foreach ($dashboard['alerts'] as $alert): ?>
                    <div class="list-item">
                        <div class="list-icon"><?= htmlspecialchars($alert['icon']) ?></div>
                        <div>
                            <h4><?= htmlspecialchars($alert['title']) ?></h4>
                            <p><?= htmlspecialchars($alert['detail']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="panel">
            <div class="panel-header">
                <div>
                    <p class="eyebrow">Performance</p>
                    <h3>Top Driver Performances</h3>
                </div>
            </div>
            <div class="list-stack">
                <?php foreach ($dashboard['drivers'] as $driver): ?>
                    <div class="list-item">
                        <div class="avatar avatar-dark"><?= htmlspecialchars(substr($driver['name'], 0, 2)) ?></div>
                        <div>
                            <h4><?= htmlspecialchars($driver['name']) ?></h4>
                            <p><?= htmlspecialchars($driver['role'] . ' • ' . $driver['dispatches']) ?></p>
                        </div>
                        <div class="status-pill status-approved" style="margin-left:auto;"><?= htmlspecialchars($driver['score']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="panel">
            <div class="panel-header">
                <div>
                    <p class="eyebrow">Quick access</p>
                    <h3>Fleet Quick Actions</h3>
                </div>
            </div>
            <div class="quick-grid">
                <?php foreach ($dashboard['quickActions'] as $action): ?>
                    <div class="quick-item"><?= htmlspecialchars($action) ?></div>
                <?php endforeach; ?>
            </div>
        </article>
    </div>
</section>
