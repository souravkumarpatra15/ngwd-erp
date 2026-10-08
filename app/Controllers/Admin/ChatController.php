<?php
namespace App\Controllers\Admin;

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

    private function publicRoot(): string
    {
        return rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'chat' . DIRECTORY_SEPARATOR;
    }

    /** GET admin/chat — WhatsApp inbox. ?c=ID preselects a conversation. */
    public function index()
    {
        if ($r = $this->requireModule('chat', 'view')) return $r;
        $filter = (string) ($this->request->getGet('filter') ?? 'all');
        $search = (string) ($this->request->getGet('q') ?? '');
        $conversations = $this->convs->inbox($search, $filter);
        $selectedId = max(0, (int) ($this->request->getGet('c') ?? 0));
        $selected = $selectedId > 0 ? $this->convs->find($selectedId) : null;
        $messages = [];
        $contact = null;
        if ($selected) {
            $messages = $this->msgs->history($selectedId);
            $this->convs->markRead($selectedId);
            if (! empty($selected['client_id'])) {
                $contact = $this->db->table('clients')->where('id', (int) $selected['client_id'])->get()->getRowArray();
                if ($contact) $contact['_kind'] = 'Client';
            } elseif (! empty($selected['lead_id'])) {
                $contact = $this->db->table('leads')->where('id', (int) $selected['lead_id'])->get()->getRowArray();
                if ($contact) {
                    $contact['_kind'] = 'Lead';
                    $contact['company_name'] = $contact['company_name'] ?? null;
                }
            }
        }
        return view('admin/chat/index', [
            'title' => 'WhatsApp Inbox',
            'conversations' => $conversations,
            'selectedId' => $selectedId,
            'selected' => $selected,
            'contact' => $contact,
            'messages' => $messages,
            'filter' => $filter,
            'search' => $search,
            'templates' => WhatsappChatService::TEMPLATES,
        ]);
    }

    /** GET admin/chat/conversations?q=&filter= (AJAX polling for the list). */
    public function conversations()
    {
        if ($r = $this->requireModule('chat', 'view')) return $r;
        return $this->jsonSuccess('Conversations.', $this->convs->inbox(
            (string) ($this->request->getGet('q') ?? ''),
            (string) ($this->request->getGet('filter') ?? 'all')
        ));
    }

    /** GET admin/chat/messages?c=ID&after_id=0 (AJAX). */
    public function messages()
    {
        if ($r = $this->requireModule('chat', 'view')) return $r;
        $convId = max(0, (int) ($this->request->getGet('c') ?? $this->request->getGet('user_id') ?? 0));
        if ($convId <= 0) return $this->jsonError('Conversation required.');
        return $this->jsonSuccess('Messages loaded.', $this->msgs->history(
            $convId, max(0, (int) ($this->request->getGet('after_id') ?? 0))
        ));
    }

    /** POST admin/chat/send {c, message} (AJAX). */
    public function send()
    {
        if ($r = $this->requireModule('chat', 'view')) return $r;
        $json = $this->request->getJSON(true);
        $convId = (int) ($json['c'] ?? $json['conversation_id'] ?? $this->request->getPost('c') ?? 0);
        $text = trim((string) ($json['message'] ?? $this->request->getPost('message') ?? ''));
        if ($convId <= 0) return $this->jsonError('Conversation required.');
        $res = $this->chat->sendText($convId, $text, (int) session()->get('user_id'));
        return $res['ok'] ? $this->jsonSuccess('Message sent.', $res['message']) : $this->jsonError($res['error'] ?? 'Unable to send message.');
    }

    /** GET admin/chat/templates (AJAX) — approved templates + variable labels. */
    public function templates()
    {
        if ($r = $this->requireModule('chat', 'view')) return $r;
        return $this->jsonSuccess('Templates.', WhatsappChatService::TEMPLATES);
    }

    /** POST admin/chat/send-template {c, template, params[]} (AJAX). */
    public function sendTemplate()
    {
        if ($r = $this->requireModule('chat', 'view')) return $r;
        $json = $this->request->getJSON(true);
        $convId = (int) ($json['c'] ?? $this->request->getPost('c') ?? 0);
        $template = trim((string) ($json['template'] ?? $this->request->getPost('template') ?? ''));
        $params = $json['params'] ?? $this->request->getPost('params') ?? [];
        if ($convId <= 0 || $template === '') return $this->jsonError('Conversation and template required.');
        $res = $this->chat->sendTemplate($convId, $template, (array) $params, (int) session()->get('user_id'));
        return $res['ok'] ? $this->jsonSuccess('Template sent.', $res['message']) : $this->jsonError($res['error'] ?? 'Unable to send template.');
    }

    /** POST admin/chat/read/ID (AJAX) — open = read. */
    public function read($id)
    {
        if ($r = $this->requireModule('chat', 'view')) return $r;
        $this->convs->markRead((int) $id);
        return $this->jsonSuccess('Marked read.');
    }

    /** POST admin/chat/retry/ID (AJAX) — resend a failed message. */
    public function retry($id)
    {
        if ($r = $this->requireModule('chat', 'view')) return $r;
        $res = $this->chat->retry((int) $id);
        return $res['ok'] ? $this->jsonSuccess('Message resent.', $res['message']) : $this->jsonError($res['error'] ?? 'Retry failed.');
    }

    /**
     * POST admin/chat/upload-image (AJAX FormData: c + image + caption?).
     * Stored under public/uploads/chat so MSG91 can fetch the real URL.
     */
    public function uploadImage()
    {
        if ($r = $this->requireModule('chat', 'view')) return $r;
        $convId = max(0, (int) $this->request->getPost('c'));
        if ($convId <= 0) return $this->jsonError('Conversation required.');
        $file = $this->request->getFile('image');
        if (! $file || ! $file->isValid() || $file->hasMoved()) return $this->jsonError('Invalid image file.');
        if (! $this->validate([
            'image' => 'uploaded[image]|max_size[image,5120]|ext_in[image,png,jpg,jpeg,gif,webp]',
        ])) return $this->jsonError($this->validator->getError('image') ?: 'Image must be png/jpg/gif/webp up to 5MB.');
        $folder = $this->publicRoot();
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
        $res = $this->chat->sendImage($convId, base_url('uploads/chat/' . $newName), $caption, (int) session()->get('user_id'));
        if (! $res['ok']) @unlink($folder . $newName);
        return $res['ok'] ? $this->jsonSuccess('Image sent.', $res['message']) : $this->jsonError($res['error'] ?? 'Unable to send image.');
    }

    /** GET admin/chat/serve/ID — stream a chat image inline (auth-gated). */
    public function serve($id)
    {
        if ($r = $this->requireModule('chat', 'view')) return $r;
        $msg = $this->msgs->find((int) $id);
        if (! $msg || empty($msg['media_url'])) return redirect()->back()->with('error', 'Image not found.');
        if (str_starts_with((string) $msg['media_url'], 'http')) return redirect()->to((string) $msg['media_url']);
        $real = $this->publicRoot() . basename((string) $msg['media_url']);
        if (! is_file($real)) return redirect()->back()->with('error', 'Image no longer exists on the server.');
        return $this->streamInlineFile($real, basename($real), $this->mediaMime(pathinfo($real, PATHINFO_EXTENSION)));
    }

    /** POST admin/chat/delete/ID (AJAX) — delete a CRM-side message. */
    public function delete($id)
    {
        if ($r = $this->requireModule('chat', 'view')) return $r;
        $msg = $this->msgs->find((int) $id);
        if (! $msg || ($msg['direction'] ?? '') !== 'outbound') return $this->jsonError('Message not found.');
        if (! empty($msg['media_url']) && ! str_starts_with((string) $msg['media_url'], 'http')) {
            $p = $this->publicRoot() . basename((string) $msg['media_url']);
            if (is_file($p)) @unlink($p);
        }
        $this->msgs->delete((int) $id);
        return $this->jsonSuccess('Message deleted.');
    }
}
