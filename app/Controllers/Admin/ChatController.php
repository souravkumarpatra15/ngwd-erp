<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ChatMessageModel;

class ChatController extends BaseController
{
    protected ChatMessageModel $cm;

    public function __construct()
    {
        $this->cm = new ChatMessageModel();
    }

    private function storageRoot(): string
    {
        return rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'chat' . DIRECTORY_SEPARATOR;
    }

    /**
     * Chat home — WhatsApp-style two-pane view.
     * ?user_id=NN preselects a conversation.
     */
    public function index()
    {
        if ($r = $this->requireModule('chat', 'view')) return $r;

        $adminId = (int) session()->get('user_id');
        $selectedUserId = max(0, (int) ($this->request->getGet('user_id') ?? 0));

        $conversations = $this->cm->conversationsForAdmin($adminId, 50);

        $selectedUser = null;
        $messages = [];
        if ($selectedUserId > 0) {
            $selectedUser = $this->db->table('users')->select('id, name')->where('id', $selectedUserId)->get()->getRowArray();
            if ($selectedUser) {
                $messages = $this->cm->conversation($adminId, $selectedUserId);
                $this->cm->markRead($adminId, $selectedUserId);
            } else {
                $selectedUserId = 0;
            }
        }

        return view('admin/chat/index', [
            'title' => 'Chat',
            'conversations' => $conversations,
            'selectedUserId' => $selectedUserId,
            'selectedUser' => $selectedUser,
            'messages' => $messages,
        ]);
    }

    /**
     * GET admin/chat/messages?user_id=NN (AJAX) — message history.
     */
    public function messages()
    {
        if ($r = $this->requireModule('chat', 'view')) return $r;
        $adminId = (int) session()->get('user_id');
        $userId = max(0, (int) ($this->request->getGet('user_id') ?? 0));
        if ($userId <= 0) return $this->jsonError('User ID required.');
        $messages = $this->cm->conversation($adminId, $userId);
        $this->cm->markRead($adminId, $userId);
        return $this->jsonSuccess('Messages loaded.', $messages);
    }

    /**
     * POST admin/chat/send (AJAX, JSON or form) — send a text message.
     */
    public function send()
    {
        if ($r = $this->requireModule('chat', 'view')) return $r;
        $json = $this->request->getJSON(true);
        $msg = trim((string) ($json['message'] ?? $this->request->getPost('message') ?? ''));
        $userId = (int) ($json['user_id'] ?? $this->request->getPost('user_id') ?? 0);
        if ($msg === '' || $userId <= 0) return $this->jsonError('Message and user ID required.');
        if (mb_strlen($msg) > 2000) return $this->jsonError('Message too long (max 2000 chars).');
        if (! $this->db->table('users')->where('id', $userId)->countAllResults()) {
            return $this->jsonError('Recipient not found.');
        }
        $adminId = (int) session()->get('user_id');
        $id = $this->cm->insert([
            'admin_id' => $adminId,
            'user_id' => $userId,
            'message' => $msg,
            'message_type' => 'text',
            'is_admin' => 1,
            'is_read' => 0,
        ], true);
        if (! $id) return $this->jsonError('Unable to send message.');
        $row = $this->cm->find($id);
        return $this->jsonSuccess('Message sent.', $this->cm->formatRow($row));
    }

    /**
     * POST admin/chat/upload-image (AJAX FormData: image + user_id + caption?).
     */
    public function uploadImage()
    {
        if ($r = $this->requireModule('chat', 'view')) return $r;
        $userId = max(0, (int) $this->request->getPost('user_id'));
        if ($userId <= 0) return $this->jsonError('User ID required.');
        if (! $this->db->table('users')->where('id', $userId)->countAllResults()) {
            return $this->jsonError('Recipient not found.');
        }
        $file = $this->request->getFile('image');
        if (! $file || ! $file->isValid() || $file->hasMoved()) return $this->jsonError('Invalid image file.');
        if (! $this->validate([
            'image' => 'uploaded[image]|max_size[image,5120]|ext_in[image,png,jpg,jpeg,gif,webp]',
        ])) return $this->jsonError($this->validator->getError('image') ?: 'Image must be png/jpg/gif/webp up to 5MB.');
        $folder = $this->storageRoot();
        if (! is_dir($folder) && ! mkdir($folder, 0755, true) && ! is_dir($folder)) {
            return $this->jsonError('Unable to create chat storage directory.');
        }
        $newName = 'chat_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $file->getExtension();
        try {
            $file->move($folder, $newName);
        } catch (\Throwable $e) {
            log_message('error', 'Chat image upload failed: {message}', ['message' => $e->getMessage()]);
            return $this->jsonError('Unable to store the uploaded image.');
        }
        $caption = trim((string) $this->request->getPost('caption'));
        $adminId = (int) session()->get('user_id');
        $id = $this->cm->insert([
            'admin_id' => $adminId,
            'user_id' => $userId,
            'message' => $caption,
            'image_url' => 'chat/' . $newName,
            'message_type' => 'image',
            'is_admin' => 1,
            'is_read' => 0,
        ], true);
        if (! $id) {
            @unlink($folder . $newName);
            return $this->jsonError('Unable to save message.');
        }
        return $this->jsonSuccess('Image sent.', $this->cm->formatRow($this->cm->find($id)));
    }

    /**
     * GET admin/chat/serve/NN — stream a chat image inline (auth-gated).
     */
    public function serve($id)
    {
        if ($r = $this->requireModule('chat', 'view')) return $r;
        $msg = $this->cm->find((int) $id);
        if (! $msg || empty($msg['image_url'])) return redirect()->back()->with('error', 'Image not found.');
        // Inbound WhatsApp media is stored as an external URL — redirect to it.
        if (str_starts_with((string) $msg['image_url'], 'http')) return redirect()->to((string) $msg['image_url']);
        $rel = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim((string) $msg['image_url'], '/\\'));
        if (str_contains($rel, '..')) return redirect()->back()->with('error', 'Image not found.');
        $real = realpath($this->storageRoot() . basename($rel)) ?: $this->storageRoot() . basename($rel);
        if (! is_file($real)) return redirect()->back()->with('error', 'Image no longer exists on the server.');
        return $this->streamInlineFile($real, basename($real), $this->mediaMime(pathinfo($real, PATHINFO_EXTENSION)));
    }

    /**
     * POST admin/chat/delete/NN (AJAX) — delete own admin message.
     */
    public function delete($id)
    {
        if ($r = $this->requireModule('chat', 'view')) return $r;
        $adminId = (int) session()->get('user_id');
        $msg = $this->cm->find((int) $id);
        if (! $msg || (int) $msg['admin_id'] !== $adminId) return $this->jsonError('Message not found.');
        if (! empty($msg['image_url'])) {
            $p = $this->storageRoot() . basename((string) $msg['image_url']);
            if (is_file($p)) @unlink($p);
        }
        $this->cm->delete((int) $id);
        return $this->jsonSuccess('Message deleted.');
    }
}
