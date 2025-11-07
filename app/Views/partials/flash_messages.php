<?php
use Core\Session;
$messages = Session::getFlashMessages();
?>

<?php if (!empty($messages['success'])): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($messages['success']) ?>
    </div>
<?php endif; ?>

<?php if (!empty($messages['error'])): ?>
    <div class="alert alert-error">
        <?= htmlspecialchars($messages['error']) ?>
    </div>
<?php endif; ?>

<?php if (!empty($messages['warning'])): ?>
    <div class="alert alert-warning">
        <?= htmlspecialchars($messages['warning']) ?>
    </div>
<?php endif; ?>

<?php if (!empty($messages['info'])): ?>
    <div class="alert alert-info">
        <?= htmlspecialchars($messages['info']) ?>
    </div>
<?php endif; ?>
