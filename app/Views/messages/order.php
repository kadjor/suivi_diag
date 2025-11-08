<h1>Messages - Commande #<?= htmlspecialchars($order_id) ?></h1>

<div class="page-actions">
    <a href="/orders/<?= $order_id ?>" class="btn">Retour à la commande</a>
</div>

<div class="messages-container">
    <?php if (!empty($messages)): ?>
        <?php foreach ($messages as $message): ?>
        <div class="message-card">
            <div class="message-header">
                <strong><?= htmlspecialchars($message['sender_name'] ?? 'Utilisateur') ?></strong>
                <span class="message-date"><?= date('d/m/Y H:i', strtotime($message['created_at'])) ?></span>
            </div>
            <div class="message-content">
                <?= nl2br(htmlspecialchars($message['content'])) ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="no-data">Aucun message pour cette commande</p>
    <?php endif; ?>
</div>

<div class="message-form-container">
    <h2>Envoyer un message</h2>
    <form method="POST" action="/orders/<?= $order_id ?>/messages" id="messageForm">
        <div class="form-group">
            <textarea name="content" id="content" rows="4" placeholder="Votre message..." required class="form-control"></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Envoyer</button>
        </div>
    </form>
</div>

<style>
.messages-container {
    margin: 20px 0;
}

.message-card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}

.message-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.message-date {
    color: #666;
    font-size: 0.9em;
}

.message-content {
    line-height: 1.6;
    color: #2c3e50;
}

.message-form-container {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-top: 20px;
}

.message-form-container h2 {
    margin-top: 0;
}
</style>
