<?php
namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Models\WhatsappConversationModel;
use App\Models\WhatsappMessageModel;
use App\Services\WhatsappChatService;

class ChatController extends BaseController
{
    protected WhatsappConversationModel $convs;
    protected WhatsappMessageModel $msgs;
    protected WhatsappChatService $chat;

    public function __construct()
    {
        $this->convs = new WhatsappConversationModel();
        $this->msgs = new WhatsappMessageModel();
        $this->chat = new WhatsappChatService();
    }

    /** Resolve this portal user's conversation via their client record. */
    private function myConversation(): ?array
    {
        $cid = (int) session()->get('client_id');
        if ($cid <= 0) return null;
        $client = $this->db->table('clients')->where('id', $cid)->get()->getRowArray();
        if (! $client) return null;
        $phone = $this->chat->normalize((string) ($client['whatsapp'] ?? $client['phone'] ?? ''));
        if ($phone === '') return null;
        return $this->chat->getOrCreateConversation($phone, (string) ($client['name'] ?? ''));
    }

    public function index()
    {
        if ($r = $this->requireModule('chat', 'view')) return $r;
        $conv = $this->myConversation();
        $messages = $conv ? $this->msgs->history((int) $conv['id']) : [];
        foreach ($messages as &$m) $m['is_me'] = ($m['direction'] ?? '') === 'inbound';
        unset($m);
        return view('client/chat/index', [
            'title' => 'Chat',
            'conversation' => $conv,
            'messages' => $messages,
        ]);
    }

    public function messages()
    {
        if ($r = $this->requireModule('chat', 'view')) return $r;
        $conv = $this->myConversation();
        if (! $conv) return $this->jsonError('No conversation yet.');
        // CRM-side direction is inverted for the portal viewer: the
        // customer's own messages (stored inbound) are "me" here.
        $rows = $this->msgs->history((int) $conv['id'], max(0, (int) ($this->request->getGet('after_id') ?? 0)));
        foreach ($rows as &$m) $m['is_me'] = ($m['direction'] ?? '') === 'inbound';
        unset($m);
        return $this->jsonSuccess('Messages loaded.', $rows);
    }

    public function send()
    {
        if ($r = $this->requireModule('chat', 'view')) return $r;
        $conv = $this->myConversation();
        if (! $conv) return $this->jsonError('No conversation yet.');
        $json = $this->request->getJSON(true);
        $text = trim((string) ($json['message'] ?? $this->request->getPost('message') ?? ''));
        if ($text === '') return $this->jsonError('Message required.');
        if (mb_strlen($text) > 2000) return $this->jsonError('Message too long (max 2000 chars).');
        // Portal messages enter as inbound-from-CRM-view... they are customer
        // replies: store directly so the admin sees them instantly.
        $id = $this->msgs->insert([
            'conversation_id' => (int) $conv['id'], 'phone_number' => (string) $conv['phone_number'],
            'direction' => 'inbound', 'message_type' => 'text', 'message_text' => $text,
            'created_by' => (int) session()->get('user_id'), 'status' => 'received',
        ], true);
        if (! $id) return $this->jsonError('Unable to send message.');
        $this->convs->touch((int) $conv['id'], $text, 'inbound', true);
        $row = $this->msgs->formatRow($this->msgs->find($id));
        $row['is_me'] = true; // viewer is the sender
        return $this->jsonSuccess('Message sent.', $row);
    }

    /** GET portal/chat/serve/NN — own-conversation images only. */
    public function serve($id)
    {
        if ($r = $this->requireModule('chat', 'view')) return $r;
        $conv = $this->myConversation();
        if (! $conv) return redirect()->back()->with('error', 'Image not found.');
        $msg = $this->msgs->where('id', (int) $id)->where('conversation_id', (int) $conv['id'])->first();
        if (! $msg || empty($msg['media_url'])) return redirect()->back()->with('error', 'Image not found.');
        if (str_starts_with((string) $msg['media_url'], 'http')) return redirect()->to((string) $msg['media_url']);
        $real = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'chat' . DIRECTORY_SEPARATOR . basename((string) $msg['media_url']);
        if (! is_file($real)) return redirect()->back()->with('error', 'Image no longer exists on the server.');
        return $this->streamInlineFile($real, basename($real), $this->mediaMime(pathinfo($real, PATHINFO_EXTENSION)));
    }
}
