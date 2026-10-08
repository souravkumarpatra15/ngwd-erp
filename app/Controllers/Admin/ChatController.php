<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ChatMessageModel;

class ChatController extends BaseController
{
    protected $cm;

    public function __construct()
    {
        $this->cm = new ChatMessageModel();
    }

    /**
     * Chat home - list of recent conversations
     */
    public function index()
    {
        if ($r = $this->requireModule('chat', 'view')) return $r;

        // Get recent conversations (distinct user IDs with latest messages)
        $db = \Config\Database::connect();
        $userId = session()->get('user_id');

        $conversations = $db->table('chat_messages')
            ->select('chat_messages.*, users.name as user_name, users.image as user_image')
            ->join('users', 'users.id = chat_messages.user_id', 'left')
            ->where('chat_messages.admin_id', $userId)
            ->groupBy('chat_messages.user_id')
            ->orderBy('chat_messages.created_at', 'DESC')
            ->limit(50)
            ->get()->getResultArray();

        // Format last message for each conversation
        foreach ($conversations as &$conv) {
            $lastMsg = $db->table('chat_messages')
                ->where('user_id', $conv['user_id'])
                ->where('admin_id', $userId)
                ->orderBy('created_at', 'DESC')
                ->limit(1)
                ->getRowArray();

            $conv['last_message'] = $lastMsg ? [
                'message' => $lastMsg['message'],
                'created_at' => $lastMsg['created_at'],
                'is_admin' => $lastMsg['admin_id'] > 0
            ] : null;

            // Get unread count for this user
            $conv['unread_count'] = $db->table('chat_messages')
                ->where('user_id', $conv['user_id'])
                ->where('admin_id', $userId)
                ->where('is_admin', 0)
                ->countAllResults();
        }

        unset($conv);

        return $this->response->setJSON([
            'status' => 'success',
            'view' => 'admin/chat/index',
            'data' => [
                'title' => 'WhatsApp Chat',
                'conversations' => $conversations,
                'current_user' => [
                    'name' => session()->get('user_name') ?? 'Admin',
                    'role' => session()->get('user_role') ?? 'admin'
                ]
            ]
        ]);
    }

    /**
     * Load chat messages for a specific user (AJAX)
     */
    public function messages($userId = null)
    {
        if ($this->request->isAJAX()) {
            $adminId = session()->get('user_id');
            $userId = $this->request->getGet('user_id') ? (int)$this->request->getGet('user_id') : $userId;

            if (!$userId) {
                return $this->jsonError('User ID required');
            }

            // Get messages between admin and user, ordered by time
            $db = \Config\Database::connect();
            $messages = $db->table('chat_messages')
                ->select('chat_messages.*, users.name as sender_name')
                ->join('users', 'users.id = chat_messages.user_id', 'left')
                ->where('(chat_messages.admin_id = ? AND chat_messages.user_id = ?)', [$adminId, $userId])
                ->orWhere('(chat_messages.admin_id = ? AND chat_messages.user_id = ?)', [$adminId, $userId])
                ->orderBy('chat_messages.created_at', 'ASC')
                ->get()->getResultArray();

            // Mark messages as read
            $db->table('chat_messages')
                ->where('user_id', $userId)
                ->where('admin_id', $adminId)
                ->where('is_admin', 0)
                ->update(['is_read' => 1]);

            // Format messages for WhatsApp-like display
            foreach ($messages as &$msg) {
                $msg['is_me'] = $msg['admin_id'] > 0; // admin sent = me, user sent = other
                $msg['time'] = relativeTime(strtotime($msg['created_at']));
            }

            unset($msg);

            return $this->jsonSuccess('Messages loaded', $messages);
        }

        return $this->jsonError('AJAX request required');
    }

    /**
     * Send a message (AJAX)
     */
    public function send()
    {
        if ($this->request->isAJAX()) {
            $msg = trim($this->request->getPost('message'));
            $userId = (int)$this->request->getPost('user_id');

            if (empty($msg) || !$userId) {
                return $this->jsonError('Message and user ID required');
            }

            $adminId = session()->get('user_id');

            $db = \Config\Database::connect();
            $db->table('chat_messages')->insert([
                'admin_id' => $adminId,
                'user_id' => $userId,
                'message' => $msg,
                'is_admin' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Get the user's name for response
            $user = $db->table('users')->where('id', $userId)->getRowArray();

            return $this->jsonSuccess('Message sent', [
                'message' => $msg,
                'sender_name' => session()->get('user_name') ?? 'You',
                'receiver_name' => $user['name'] ?? 'Client',
                'created_at' => relativeTime(date('Y-m-d H:i:s')),
                'is_me' => true
            ]);
        }

        return $this->jsonError('AJAX request required');
    }

    /**
     * Upload image for chat (AJAX)
     */
    public function uploadImage()
    {
        if ($this->request->isAJAX()) {
            $upload = $this->request->getFile('image');

            if ($upload && $upload->isValid() && !$upload->hasMoved()) {
                // Store image in public/uploads/chat/
                $uploadPath = 'uploads/chat/';
                $ext = $upload->getExtension();
                $newName = 'chat_' . date('YmdHis') . '.' . $ext;
                $upload->move($uploadPath, $newName);

                $imageUrl = base_url('.' . $uploadPath . $newName);

                // Save message to database
                $msg = trim($this->request->getPost('caption', ''));
                $adminId = session()->get('user_id');

                $db = \Config\Database::connect();
                $db->table('chat_messages')->insert([
                    'admin_id' => $adminId,
                    'user_id' => (int)$this->request->getPost('user_id'),
                    'message' => $msg,
                    'image_url' => $imageUrl,
                    'message_type' => 'image',
                    'is_admin' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                return $this->jsonSuccess('Image uploaded', [
                    'image_url' => $imageUrl,
                    'message' => $msg,
                    'is_me' => true,
                    'message_type' => 'image'
                ]);
            }

            return $this->jsonError('Invalid image file');
        }

        return $this->jsonError('AJAX request required');
    }

    /**
     * Delete a message (AJAX)
     */
    public function delete($msgId = null)
    {
        if ($this->request->isAJAX() && $msgId) {
            $adminId = session()->get('user_id');

            $db = \Config\Database::connect();
            $result = $db->table('chat_messages')
                ->where('id', $msgId)
                ->where('admin_id', $adminId)
                ->delete();

            if ($result) {
                return $this->jsonSuccess('Message deleted');
            }
        }

        return $this->jsonError('Failed to delete message');
    }
}