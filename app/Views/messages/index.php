<h1>Messages</h1>

<div class="messages-container">
    <?php if (empty($messages)): ?>
        <p class="no-data">Aucun message</p>
    <?php else: ?>
        <div class="messages-list">
            <?php foreach ($messages as $message): ?>
                <div class="message-card">
                    <div class="message-header">
                        <strong>
                            <?= htmlspecialchars(($message['sender_first_name'] ?? '') . ' ' . ($message['sender_last_name'] ?? '')) ?>
                        </strong>
                        <?php if ($message['order_number'] ?? null): ?>
                            <span class="message-order">
                                Commande: <a href="/orders/<?= $message['order_id'] ?>"><?= htmlspecialchars($message['order_number']) ?></a>
                            </span>
                        <?php endif; ?>
                        <span class="message-date">
                            <?= date('d/m/Y H:i', strtotime($message['created_at'])) ?>
                        </span>
                    </div>

                    <?php if ($message['recipient_first_name'] ?? null): ?>
                        <div class="message-recipient">
                            À: <?= htmlspecialchars(($message['recipient_first_name'] ?? '') . ' ' . ($message['recipient_last_name'] ?? '')) ?>
                        </div>
                    <?php endif; ?>

                    <div class="message-content">
                        <?= nl2br(htmlspecialchars($message['content'])) ?>
                    </div>

                    <?php if ($message['requires_response']): ?>
                        <div class="message-requires-response">
                            <span class="badge" style="background-color: #f39c12">Réponse requise</span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.messages-container {
    max-width: 800px;
    margin: 20px auto;
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
    align-items: center;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.message-order {
    font-size: 0.9em;
    color: #666;
}

.message-date {
    font-size: 0.85em;
    color: #999;
}

.message-recipient {
    font-size: 0.9em;
    color: #666;
    margin-bottom: 10px;
}

.message-content {
    margin: 15px 0;
    line-height: 1.6;
}

.message-requires-response {
    margin-top: 10px;
}

.no-data {
    text-align: center;
    padding: 40px;
    color: #999;
}
</style>
