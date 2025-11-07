<h1>Tableau de bord - Technicien</h1>

<div class="dashboard-content">
    <section>
        <h2>Mes interventions du jour</h2>
        <div class="appointments-list">
            <?php foreach ($todays_appointments ?? [] as $appt): ?>
            <div class="appointment-card">
                <h3><?= htmlspecialchars($appt['site_name']) ?></h3>
                <p><?= htmlspecialchars($appt['address']) ?></p>
                <p><strong>Heure:</strong> <?= htmlspecialchars($appt['scheduled_time']) ?></p>
                <a href="/orders/<?= $appt['order_id'] ?>" class="btn btn-sm">Voir commande</a>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section>
        <h2>Prochaines interventions</h2>
        <ul>
            <?php foreach ($upcoming_appointments ?? [] as $appt): ?>
            <li><?= htmlspecialchars($appt['scheduled_date']) ?> - <?= htmlspecialchars($appt['site_name']) ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
</div>
