<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\TicketModel;
use App\Models\TicketReplyModel;
use App\Services\EmailService;

class TicketController extends BaseController
{
    public function index() {
        return view('admin/tickets/index', [
            'title'       => 'Support Tickets',
            'tickets'     => $this->db->table('tickets')->select('tickets.*, clients.name as client_name')->join('clients','clients.id = tickets.client_id','left')->orderBy('tickets.created_at','DESC')->get()->getResultArray(),
            'open'        => (new TicketModel())->where('status','open')->countAllResults(),
            'in_progress' => (new TicketModel())->where('status','in_progress')->countAllResults(),
        ]);
    }

    public function show($id) {
        $ticket  = $this->db->table('tickets')->select('tickets.*, clients.name as client_name, clients.email as client_email')->join('clients','clients.id = tickets.client_id','left')->where('tickets.id',$id)->get()->getRowArray();
        $replies = $this->db->table('ticket_replies')->select('ticket_replies.*, users.name as user_name')->join('users','users.id = ticket_replies.user_id','left')->where('ticket_id',$id)->orderBy('created_at','ASC')->get()->getResultArray();
        return view('admin/tickets/show', ['title'=>'Ticket #'.$ticket['ticket_number'],'ticket'=>$ticket,'replies'=>$replies]);
    }

    public function reply($id) {
        $msg    = $this->request->getPost('message');
        $ticket = (new TicketModel())->find($id);
        (new TicketReplyModel())->insert(['ticket_id'=>$id,'user_id'=>session()->get('user_id'),'message'=>$msg,'is_admin'=>1]);
        (new TicketModel())->update($id, ['status'=>'in_progress']);
        $client = $this->db->table('clients')->where('id',$ticket['client_id'])->get()->getRowArray();
        (new EmailService())->send($client['email'],"Reply on Ticket #{$ticket['ticket_number']}","<p>Dear {$client['name']},</p><p><strong>Reply:</strong><br>{$msg}</p><p>View: ".base_url("portal/tickets/$id")."</p>");
        return $this->jsonSuccess('Reply sent');
    }

    public function updateStatus($id) {
        $s=$this->request->getPost('status');$u=['status'=>$s];
        if($s==='closed')$u['closed_at']=date('Y-m-d H:i:s');
        (new TicketModel())->update($id,$u);return $this->jsonSuccess('Status updated');
    }
}
