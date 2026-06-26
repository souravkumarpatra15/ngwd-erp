<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->group('', ['namespace' => 'App\Controllers'], function ($routes) {

    // ── Auth ───────────────────────────────────────────────────
    $routes->get('/',  'Auth\LoginController::index');
    $routes->get('login',  'Auth\LoginController::index');
    $routes->post('login', 'Auth\LoginController::process');
    $routes->get('logout', 'Auth\LoginController::logout');
    $routes->get('admin/search', 'Admin\SearchController::index');

    // Password Reset
    $routes->get('forgot-password', 'Auth\ForgotPasswordController::index');
    $routes->post('forgot-password', 'Auth\ForgotPasswordController::sendLink');
    $routes->get('reset-password/(:segment)', 'Auth\ForgotPasswordController::resetForm/$1');
    $routes->post('reset-password/(:segment)', 'Auth\ForgotPasswordController::resetPassword/$1');

    // ── Admin ──────────────────────────────────────────────────
    $routes->group('admin', ['filter' => 'adminauth'], function ($routes) {
        $routes->get('/', 'Admin\DashboardController::index');
        $routes->get('dashboard', 'Admin\DashboardController::index');
        $routes->get('notifications', 'Admin\DashboardController::notifications');
        $routes->post('notifications/read/(:num)', 'Admin\DashboardController::markRead/$1');
        $routes->post('notifications/read-all', 'Admin\DashboardController::markAllRead');
        $routes->get('ajax/clients', 'Admin\DashboardController::ajaxClients');
        $routes->get('ajax/projects/(:num)', 'Admin\DashboardController::ajaxProjects/$1');

        // Leads
        $routes->get('leads', 'Admin\LeadController::index');
        $routes->get('leads/datatable', 'Admin\LeadController::datatable');
        $routes->get('leads/create', 'Admin\LeadController::create');
        $routes->post('leads/store', 'Admin\LeadController::store');
        $routes->get('leads/(:num)', 'Admin\LeadController::show/$1');
        $routes->get('leads/edit/(:num)', 'Admin\LeadController::edit/$1');
        $routes->post('leads/update/(:num)', 'Admin\LeadController::update/$1');
        $routes->post('leads/delete/(:num)', 'Admin\LeadController::delete/$1');
        $routes->post('leads/convert/(:num)', 'Admin\LeadController::convertToClient/$1');
        $routes->post('leads/activity/(:num)', 'Admin\LeadController::addActivity/$1');
        $routes->post('leads/send-whatsapp/(:num)', 'Admin\LeadController::sendWhatsApp/$1');
        $routes->post('leads/send-email/(:num)', 'Admin\LeadController::sendEmail/$1');

        // Clients
        $routes->get('clients', 'Admin\ClientController::index');
        $routes->get('clients/datatable', 'Admin\ClientController::datatable');
        $routes->get('clients/create', 'Admin\ClientController::create');
        $routes->post('clients/store', 'Admin\ClientController::store');
        $routes->get('clients/(:num)', 'Admin\ClientController::show/$1');
        $routes->get('clients/edit/(:num)', 'Admin\ClientController::edit/$1');
        $routes->post('clients/update/(:num)', 'Admin\ClientController::update/$1');
        $routes->post('clients/delete/(:num)', 'Admin\ClientController::delete/$1');

        // Projects
        $routes->get('projects', 'Admin\ProjectController::index');
        $routes->get('projects/datatable', 'Admin\ProjectController::datatable');
        $routes->get('projects/create', 'Admin\ProjectController::create');
        $routes->post('projects/store', 'Admin\ProjectController::store');
        $routes->get('projects/(:num)', 'Admin\ProjectController::show/$1');
        $routes->get('projects/edit/(:num)', 'Admin\ProjectController::edit/$1');
        $routes->post('projects/update/(:num)', 'Admin\ProjectController::update/$1');
        $routes->post('projects/delete/(:num)', 'Admin\ProjectController::delete/$1');
        $routes->post('projects/status/(:num)', 'Admin\ProjectController::updateStatus/$1');

        // Proposals
        $routes->get('proposals', 'Admin\ProposalController::index');
        $routes->get('proposals/create', 'Admin\ProposalController::create');
        $routes->post('proposals/store', 'Admin\ProposalController::store');
        $routes->get('proposals/(:num)', 'Admin\ProposalController::show/$1');
        $routes->get('proposals/edit/(:num)', 'Admin\ProposalController::edit/$1');
        $routes->post('proposals/update/(:num)', 'Admin\ProposalController::update/$1');
        $routes->post('proposals/delete/(:num)', 'Admin\ProposalController::delete/$1');
        $routes->get('proposals/pdf/(:num)', 'Admin\ProposalController::generatePDF/$1');
        $routes->post('proposals/send-email/(:num)', 'Admin\ProposalController::sendEmail/$1');
        $routes->post('proposals/send-whatsapp/(:num)', 'Admin\ProposalController::sendWhatsApp/$1');

        // Agreements
        $routes->get('agreements', 'Admin\AgreementController::index');
        $routes->get('agreements/create', 'Admin\AgreementController::create');
        $routes->post('agreements/store', 'Admin\AgreementController::store');
        $routes->get('agreements/(:num)', 'Admin\AgreementController::show/$1');
        $routes->get('agreements/edit/(:num)', 'Admin\AgreementController::edit/$1');
        $routes->post('agreements/update/(:num)', 'Admin\AgreementController::update/$1');
        $routes->get('agreements/pdf/(:num)', 'Admin\AgreementController::generatePDF/$1');
        $routes->post('agreements/send-email/(:num)', 'Admin\AgreementController::sendEmail/$1');
        $routes->post('agreements/send-whatsapp/(:num)', 'Admin\AgreementController::sendWhatsApp/$1');
        $routes->post('agreements/delete/(:num)', 'Admin\AgreementController::delete/$1');
        $routes->post('agreements/status/(:num)', 'Admin\AgreementController::updateStatus/$1');

        // Milestones
        $routes->get('milestones', 'Admin\MilestoneController::index');
        $routes->post('milestones/store', 'Admin\MilestoneController::store');
        $routes->post('milestones/update/(:num)', 'Admin\MilestoneController::update/$1');
        $routes->post('milestones/delete/(:num)', 'Admin\MilestoneController::delete/$1');
        $routes->post('milestones/status/(:num)', 'Admin\MilestoneController::updateStatus/$1');
        $routes->post('milestones/payment-link/(:num)', 'Admin\MilestoneController::generatePaymentLink/$1');

        // Payments
        $routes->get('payments', 'Admin\PaymentController::index');
        $routes->get('payments/datatable', 'Admin\PaymentController::datatable');
        $routes->get('payments/create', 'Admin\PaymentController::create');
        $routes->post('payments/store', 'Admin\PaymentController::store');
        $routes->get('payments/(:num)', 'Admin\PaymentController::show/$1');
        $routes->get('payments/receipt/(:num)', 'Admin\PaymentController::receipt/$1');

        // Invoices
        $routes->get('invoices', 'Admin\InvoiceController::index');
        $routes->get('invoices/datatable', 'Admin\InvoiceController::datatable');
        $routes->get('invoices/create', 'Admin\InvoiceController::create');
        $routes->post('invoices/store', 'Admin\InvoiceController::store');
        $routes->get('invoices/(:num)', 'Admin\InvoiceController::show/$1');
        $routes->get('invoices/edit/(:num)', 'Admin\InvoiceController::edit/$1');
        $routes->post('invoices/update/(:num)', 'Admin\InvoiceController::update/$1');
        $routes->get('invoices/pdf/(:num)', 'Admin\InvoiceController::generatePDF/$1');
        $routes->post('invoices/send-email/(:num)', 'Admin\InvoiceController::sendEmail/$1');
        $routes->post('invoices/send-whatsapp/(:num)', 'Admin\InvoiceController::sendWhatsApp/$1');
        $routes->post('invoices/payment-link/(:num)', 'Admin\InvoiceController::generatePaymentLink/$1');
        $routes->post('invoices/delete/(:num)', 'Admin\InvoiceController::delete/$1');
        $routes->post('invoices/void/(:num)', 'Admin\InvoiceController::void/$1');

        // Domains
        $routes->get('domains', 'Admin\DomainController::index');
        $routes->get('domains/create', 'Admin\DomainController::create');
        $routes->post('domains/store', 'Admin\DomainController::store');
        $routes->get('domains/edit/(:num)', 'Admin\DomainController::edit/$1');
        $routes->post('domains/update/(:num)', 'Admin\DomainController::update/$1');
        $routes->post('domains/delete/(:num)', 'Admin\DomainController::delete/$1');
        $routes->post('domains/remind/(:num)', 'Admin\DomainController::sendReminder/$1');

        // Hostings
        $routes->get('hostings', 'Admin\HostingController::index');
        $routes->get('hostings/create', 'Admin\HostingController::create');
        $routes->post('hostings/store', 'Admin\HostingController::store');
        $routes->get('hostings/edit/(:num)', 'Admin\HostingController::edit/$1');
        $routes->post('hostings/update/(:num)', 'Admin\HostingController::update/$1');
        $routes->post('hostings/delete/(:num)', 'Admin\HostingController::delete/$1');
        $routes->post('hostings/remind/(:num)', 'Admin\HostingController::sendReminder/$1');

        // Documents
        $routes->get('documents', 'Admin\DocumentController::index');
        $routes->post('documents/upload', 'Admin\DocumentController::upload');
        $routes->get('documents/download/(:num)', 'Admin\DocumentController::download/$1');
        $routes->post('documents/delete/(:num)', 'Admin\DocumentController::delete/$1');

        // Tasks
        $routes->get('tasks', 'Admin\TaskController::index');
        $routes->get('tasks/kanban', 'Admin\TaskController::kanban');
        $routes->post('tasks/store', 'Admin\TaskController::store');
        $routes->post('tasks/update/(:num)', 'Admin\TaskController::update/$1');
        $routes->post('tasks/status/(:num)', 'Admin\TaskController::updateStatus/$1');
        $routes->post('tasks/delete/(:num)', 'Admin\TaskController::delete/$1');

        // Tickets
        $routes->get('tickets', 'Admin\TicketController::index');
        $routes->get('tickets/(:num)', 'Admin\TicketController::show/$1');
        $routes->post('tickets/reply/(:num)', 'Admin\TicketController::reply/$1');
        $routes->post('tickets/status/(:num)', 'Admin\TicketController::updateStatus/$1');

        // Reports
        $routes->get('reports', 'Admin\ReportController::index');
        $routes->get('reports/revenue', 'Admin\ReportController::revenue');
        $routes->get('reports/leads', 'Admin\ReportController::leads');
        $routes->get('reports/projects', 'Admin\ReportController::projects');
        $routes->get('reports/invoices', 'Admin\ReportController::invoices');
        $routes->get('reports/payments', 'Admin\ReportController::payments');
        $routes->get('reports/domains', 'Admin\ReportController::domains');
        $routes->get('reports/export/(:alpha)/(:alpha)', 'Admin\ReportController::export/$1/$2');

        // Settings
        $routes->get('settings', 'Admin\SettingController::index');
        $routes->post('settings/save/(:alpha)', 'Admin\SettingController::save/$1');

        // Profile
        $routes->get('profile', 'Admin\ProfileController::index');
        $routes->post('profile/update', 'Admin\ProfileController::update');
        $routes->post('profile/change-password', 'Admin\ProfileController::changePassword');

        // User Management
        $routes->get('users', 'Admin\UserManagementController::index');
        $routes->get('users/create', 'Admin\UserManagementController::create');
        $routes->post('users/store', 'Admin\UserManagementController::store');
        $routes->get('users/edit/(:num)', 'Admin\UserManagementController::edit/$1');
        $routes->post('users/update/(:num)', 'Admin\UserManagementController::update/$1');
        $routes->post('users/delete/(:num)', 'Admin\UserManagementController::delete/$1');
        $routes->post('users/toggle-active/(:num)', 'Admin\UserManagementController::toggleActive/$1');
        
    });

    // ── Client Portal ──────────────────────────────────────────
    $routes->group('portal', ['filter' => 'clientauth'], function ($routes) {
        $routes->get('/', 'Client\PortalController::dashboard');
        $routes->get('dashboard', 'Client\PortalController::dashboard');
        $routes->get('projects', 'Client\PortalController::projects');
        $routes->get('projects/(:num)', 'Client\PortalController::projectDetail/$1');
        $routes->get('invoices', 'Client\PortalController::invoices');
        $routes->get('invoices/(:num)', 'Client\PortalController::invoiceDetail/$1');
        $routes->get('payments', 'Client\PortalController::payments');
        $routes->get('proposals', 'Client\PortalController::proposals');
        $routes->get('proposals/(:num)', 'Client\PortalController::proposalDetail/$1');
        $routes->get('agreements', 'Client\PortalController::agreements');
        $routes->get('agreements/sign/(:num)', 'Client\PortalController::signAgreement/$1');
        $routes->post('agreements/sign/(:num)', 'Client\PortalController::processSign/$1');
        $routes->get('documents', 'Client\PortalController::documents');
        $routes->get('tickets', 'Client\TicketController::index');
        $routes->get('tickets/create', 'Client\TicketController::create');
        $routes->post('tickets/store', 'Client\TicketController::store');
        $routes->get('tickets/(:num)', 'Client\TicketController::show/$1');
        $routes->post('tickets/reply/(:num)', 'Client\TicketController::reply/$1');
        $routes->get('pay/(:num)', 'Client\PaymentController::checkout/$1');
        $routes->post('pay/verify', 'Client\PaymentController::verify');
    });

    // ── Webhooks ───────────────────────────────────────────────
    $routes->post('webhook/razorpay', 'Api\WebhookController::razorpay');
});
