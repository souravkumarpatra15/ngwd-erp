<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\LeadModel;
use App\Models\ClientModel;
use App\Models\ProjectModel;
use App\Models\PaymentModel;
use App\Models\InvoiceModel;
use App\Models\DomainModel;
use App\Models\HostingModel;
use App\Models\ProposalModel;
use App\Models\NotificationModel;

class DashboardController extends BaseController
{
    public function index() {
        $leadModel    = new LeadModel();
        $clientModel  = new ClientModel();
        $projectModel = new ProjectModel();
        $paymentModel = new PaymentModel();
        $invoiceModel = new InvoiceModel();
        $domainModel  = new DomainModel();
        $hostingModel = new HostingModel();

        return view('admin/dashboard/index', [
            'title'              => 'Dashboard',
            'total_leads'        => $leadModel->countAll(),
            'total_clients'      => $clientModel->countAll(),
            'active_projects'    => $projectModel->where('status','development')->countAllResults(),
            'completed_projects' => $projectModel->where('status','completed')->countAllResults(),
            'pending_proposals'  => (new ProposalModel())->where('status','sent')->countAllResults(),
            'pending_payments'   => $invoiceModel->where('status !=','paid')->sumBy('balance_due'),
            'monthly_revenue'    => $paymentModel->getMonthlyRevenue(),
            'domain_renewals'    => $domainModel->getExpiringCount(30),
            'hosting_renewals'   => $hostingModel->getExpiringCount(30),
            'todays_followups'   => $leadModel->getTodaysFollowUps(),
            'upcoming_renewals'  => array_merge($domainModel->getUpcomingRenewals(30), $hostingModel->getUpcomingRenewals(30)),
            'recent_payments'    => $paymentModel->getRecent(5),
            'recent_leads'       => $leadModel->getRecent(5),
            'monthly_revenue_chart' => $paymentModel->getMonthlyRevenueChart(),
            'lead_conversion_chart' => $leadModel->getConversionChart(),
            'project_status_chart'  => $projectModel->getStatusChart(),
        ]);
    }

    public function notifications() {
        return view('admin/notifications/index', [
            'title'         => 'Notifications',
            'notifications' => (new NotificationModel())->getUserNotifications(session()->get('user_id')),
        ]);
    }

    public function markRead($id) {
        (new NotificationModel())->markRead($id);
        return $this->jsonSuccess('Marked as read');
    }

    public function markAllRead() {
        (new NotificationModel())->markAllRead(session()->get('user_id'));
        return $this->jsonSuccess('All marked as read');
    }

    public function ajaxClients() {
        $clients = (new ClientModel())->search($this->request->getGet('q'));
        return $this->response->setJSON($clients);
    }

    public function ajaxProjects($clientId) {
        $projects = (new ProjectModel())->where('client_id',$clientId)->select('id,name')->findAll();
        return $this->response->setJSON($projects);
    }
}
