<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Suivi Diagnostics' ?> - Plateforme de Suivi</title>

    <!-- CSS -->
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">

    <!-- Leaflet (cartographie) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- FullCalendar -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" />

    <!-- Chart.js pour les graphiques -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>
    <?php include __DIR__ . '/../partials/nav.php'; ?>

    <main class="main-content">
        <?php include __DIR__ . '/../partials/flash_messages.php'; ?>

        <div class="container">
            <?= $content ?>
        </div>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <!-- JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="/assets/js/main.js"></script>
    <script src="/assets/js/map.js"></script>
</body>
</html>
