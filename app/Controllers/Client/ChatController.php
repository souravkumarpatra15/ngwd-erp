<?php
namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Models\ChatMessageModel;

class ChatController extends BaseController
{
    protected ChatMessageModel $cm;

    public function __construct()
    {
        $this->cm = new ChatMessageModel();
    }

    private function myAdminId(): int
    {
        $userId = (int) session()->get('user_id');
        $adminId = $this->cm->adminForUser($userId);
        if ($adminId) return $adminId;
        $row = $this->db->table('users')->selectMin('id')->whereIn('role', ['superadmin', 'admin'])->get()->getRowArray();
        return (int) ($row['id'] ?? 1);
    }

    public function index()
    {
        if ($r = $this->requireModule('chat', 'view')) return $r;
        $userId = (int) session()->get('user_id');
        $adminId = $this->myAdminId();
        $messages = $this->cm->conversation($adminId, $userId, false);
        return view('client/chat/index', [
            'title' => 'Chat',
            'messages' => $messages,
            'adminId' => $adminId,
        ]);
    }

    public function messages()
    {
        if ($r = $this->requireModule('chat', 'view')) return $r;
        $userId = (int) session()->get('user_id');
        return $this->jsonSuccess('Messages loaded.', $this->cm->conversation($this->myAdminId(), $userId, false));
    }

    public function send()
    {
        if ($r = $this->requireModule('chat', 'view')) return $r;
        $json = $this->request->getJSON(true);
        $msg = trim((string) ($json['message'] ?? $this->request->getPost('message') ?? ''));
        if ($msg === '') return $this->jsonError('Message required.');
        if (mb_strlen($msg) > 2000) return $this->jsonError('Message too long (max 2000 chars).');
        $userId = (int) session()->get('user_id');
        $adminId = $this->myAdminId();
        $id = $this->cm->insert([
            'admin_id' => $adminId,
            'user_id' => $userId,
            'message' => $msg,
            'message_type' => 'text',
            'is_admin' => 0,
            'is_read' => 0,
        ], true);
        if (! $id) return $this->jsonError('Unable to send message.');
        return $this->jsonSuccess('Message sent.', $this->cm->formatRow($this->cm->find($id), false, 'portal/chat/serve'));
    }

    /**
     * GET portal/chat/serve/NN — stream a chat image inline (owner-checked).
     */
    public function serve($id)
    {
        if ($r = $this->requireModule('chat', 'view')) return $r;
        $userId = (int) session()->get('user_id');
        $msg = $this->cm->where('id', (int) $id)->where('user_id', $userId)->first();
        if (! $msg || empty($msg['image_url'])) return redirect()->back()->with('error', 'Image not found.');
        $root = rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'chat' . DIRECTORY_SEPARATOR;
        $real = $root . basename((string) $msg['image_url']);
        if (! is_file($real)) return redirect()->back()->with('error', 'Image no longer exists on the server.');
        return $this->streamInlineFile($real, basename($real), $this->mediaMime(pathinfo($real, PATHINFO_EXTENSION)));
    }
}
