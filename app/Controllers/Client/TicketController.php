<?php
namespace App\Controllers\Client;
use App\Controllers\BaseController;
use App\Models\TicketModel;
use App\Models\TicketReplyModel;
use App\Models\ProjectModel;
use App\Services\NotificationService;

class TicketController extends BaseController
{
    protected $tm; protected $rm;
    public function __construct() { $this->tm = new TicketModel(); $this->rm = new TicketReplyModel(); }

    public function index() {
        return view('client/tickets/index', ['title'=>'Support Tickets','tickets'=>$this->tm->where('client_id',session()->get('client_id'))->orderBy('created_at','DESC')->findAll()]);
    }

    public function create() {
        return view('client/tickets/create', ['title'=>'New Ticket','projects'=>(new ProjectModel())->where('client_id',session()->get('client_id'))->findAll()]);
    }

    public function store() {
        if (!$this->validate(['subject'=>'required|min_length[5]','description'=>'required|min_length[10]','priority'=>'required'])) {
            return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        }
        $no = sprintf('TKT/%s/%05d',date('Y'),$this->tm->countAll()+1);
        $id = $this->tm->insert(['ticket_number'=>$no,'client_id'=>session()->get('client_id'),'project_id'=>$this->request->getPost('project_id')?:null,'subject'=>$this->request->getPost('subject'),'description'=>$this->request->getPost('description'),'priority'=>$this->request->getPost('priority'),'status'=>'open']);
        (new NotificationService())->create(1,'new_ticket','New Support Ticket','New ticket: '.$this->request->getPost('subject'),$id,'ticket');
        return redirect()->to("portal/tickets/$id")->with('success','Ticket created: '.$no);
    }

    public function show($id) {
        $ticket = $this->tm->where('id',$id)->where('client_id',session()->get('client_id'))->first();
        if (!$ticket) return redirect()->to('portal/tickets');
        $replies = $this->db->table('ticket_replies')->select('ticket_replies.*, users.name as user_name')->join('users','users.id = ticket_replies.user_id','left')->where('ticket_id',$id)->orderBy('created_at','ASC')->get()->getResultArray();
        return view('client/tickets/show', ['title'=>$ticket['subject'],'ticket'=>$ticket,'replies'=>$replies]);
    }

    public function reply($id) {
        $ticket = $this->tm->where('id',$id)->where('client_id',session()->get('client_id'))->first();
        if (!$ticket || $ticket['status']==='closed') return $this->jsonError('Cannot reply');
        $this->rm->insert(['ticket_id'=>$id,'user_id'=>session()->get('user_id'),'message'=>$this->request->getPost('message'),'is_admin'=>0]);
        return $this->jsonSuccess('Reply added');
    }
}
