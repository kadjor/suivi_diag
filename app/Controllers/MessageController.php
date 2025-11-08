<?php

namespace Controllers;

use Core\Controller;
use Core\Auth;
use Core\View;
use Models\Message;

class MessageController extends Controller
{
    private $messageModel;

    public function __construct()
    {
        if (!Auth::check()) {
            View::redirect('/login');
        }
        $this->messageModel = new Message();
    }

    public function index()
    {
        $orderId = $_GET['order_id'] ?? null;

        if ($orderId) {
            $messages = $this->messageModel->getByOrder($orderId);
            View::render('messages.order', ['messages' => $messages, 'order_id' => $orderId]);
        } else {
            $messages = $this->messageModel->getRecent(Auth::id());
            View::render('messages.index', ['messages' => $messages]);
        }
    }

    public function send()
    {
        if (!Auth::can('send_messages')) {
            View::json(['error' => 'Accès refusé'], 403);
        }

        $orderId = $_POST['order_id'] ?? null;
        $content = $_POST['content'] ?? '';

        if (!$orderId || empty($content)) {
            View::json(['error' => 'Données incomplètes'], 400);
        }

        try {
            $messageId = $this->messageModel->create([
                'order_id' => $orderId,
                'sender_id' => Auth::id(),
                'content' => $content
            ]);

            View::json(['success' => true, 'message_id' => $messageId]);
        } catch (\Exception $e) {
            View::json(['error' => $e->getMessage()], 400);
        }
    }

    public function getMessages($orderId)
    {
        $messages = $this->messageModel->getByOrder($orderId);
        View::json(['messages' => $messages]);
    }

    /**
     * Alias pour send() - pour correspondre à la route store
     */
    public function store($orderId = null)
    {
        // Si l'orderId vient de l'URL (route), l'utiliser
        if ($orderId) {
            $_POST['order_id'] = $orderId;
        }
        return $this->send();
    }
}
