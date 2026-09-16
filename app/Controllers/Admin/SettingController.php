<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\SettingModel;

class SettingController extends BaseController
{
    public function index() {
        return view('admin/settings/index', ['title'=>'Settings','settings'=>$this->settings]);
    }
    public function save($group) {
        $data = $this->request->getPost(); unset($data['csrf_test_name']);
        if ($group === 'whatsapp' && trim((string)($data['msg91_authkey'] ?? '')) === '') {
            unset($data['msg91_authkey']); // keep the saved key
        }
        if ($group === 'company') {
            $logo = $this->request->getFile('company_logo');
            if ($logo && $logo->isValid() && !$logo->hasMoved()) {
                $name = 'company_logo.'.$logo->getExtension();
                $logo->move(FCPATH.'assets/images/', $name, true);
                $data['company_logo'] = 'assets/images/'.$name;
            } else unset($data['company_logo']);

            $sig = $this->request->getFile('signature_image');
            if ($sig && $sig->isValid() && !$sig->hasMoved()) {
                $name = 'signature.'.$sig->getExtension();
                $sig->move(FCPATH.'assets/images/', $name, true);
                $data['signature_image'] = 'assets/images/'.$name;
            } else unset($data['signature_image']);
        }
        (new SettingModel())->saveGroup($group, $data);
        return redirect()->to('admin/settings#'.$group)->with('success', ucfirst($group).' settings saved!');
    }

    public function testWhatsapp()
    {
        $to   = trim((string)$this->request->getPost('to'));
        $type = strtolower(trim((string)$this->request->getPost('type') ?: 'text'));
        $body = trim((string)$this->request->getPost('body'));
        $link = trim((string)$this->request->getPost('link'));
        $svc  = new \App\Services\Msg91WhatsAppService();
        if (!$svc->isConfigured()) return $this->jsonError('Save your MSG91 Auth Key + Integrated Number first.');
        if ($to === '') return $this->jsonError('Recipient number is required.');
        $res = match ($type) {
            'image'   => $svc->sendImage($to, $link, $body),
            'video'   => $svc->sendVideo($to, $link, $body),
            'link'    => $svc->sendLink($to, $link ?: $body, $link ? $body : ''),
            'buttons' => $svc->sendReplyButtons(
                $to,
                $body !== '' ? $body : 'Thanks for chatting with us! What would you like to do next?',
                [['id' => 'track', 'title' => 'Track Order'], ['id' => 'support', 'title' => 'Support']]
            ),
            'template' => $svc->sendTemplate($to, trim((string)$this->request->getPost('template')), $body !== '' ? [$body] : []),
            default   => $svc->sendText($to, $body !== '' ? $body : 'Hello from NGWebD ERP (test message).'),
        };
        return $res['ok']
            ? $this->jsonSuccess('Test message sent.' . (!empty($res['message_id']) ? ' ID: ' . $res['message_id'] : ''))
            : $this->jsonError($res['error'] ?? 'Send failed.');
    }
}
