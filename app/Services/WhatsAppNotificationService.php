<?php

namespace App\Services;

class WhatsAppNotificationService
{
    protected WhatsAppService $whatsapp;

    public function __construct()
    {
        $this->whatsapp = new WhatsAppService();
    }

    /*
    |--------------------------------------------------------------------------
    | Client
    |--------------------------------------------------------------------------
    */

    public function clientWelcome(
        string $phone,
        string $clientName
    ): bool {
        return $this->whatsapp->sendTemplate(
            $phone,
            'erp_client_welcome',
            $clientName,
            [
                $clientName,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Leads
    |--------------------------------------------------------------------------
    */

    public function leadCreated(
        string $phone,
        string $name,
        string $leadName
    ): bool {
        return $this->whatsapp->sendTemplate(
            $phone,
            'erp_lead_created',
            $name,
            [
                $name,
                $leadName,
            ]
        );
    }

    public function leadFollowup(
        string $phone,
        string $name,
        string $date
    ): bool {
        return $this->whatsapp->sendTemplate(
            $phone,
            'erp_lead_followup',
            $name,
            [
                $name,
                $date,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Proposals
    |--------------------------------------------------------------------------
    */

    public function proposalSent(
        string $phone,
        string $clientName,
        string $proposalNumber,
        string $amount
    ): bool {
        return $this->whatsapp->sendTemplate(
            $phone,
            'erp_proposal_sent',
            $clientName,
            [
                $clientName,
                $proposalNumber,
                $amount,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Agreements
    |--------------------------------------------------------------------------
    */

    public function agreementSent(
        string $phone,
        string $clientName,
        string $agreementNumber
    ): bool {
        return $this->whatsapp->sendTemplate(
            $phone,
            'erp_agreement_sent',
            $clientName,
            [
                $clientName,
                $agreementNumber,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Projects
    |--------------------------------------------------------------------------
    */

    public function projectCreated(
        string $phone,
        string $clientName,
        string $projectName
    ): bool {
        return $this->whatsapp->sendTemplate(
            $phone,
            'erp_project_created',
            $clientName,
            [
                $clientName,
                $projectName,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Milestones
    |--------------------------------------------------------------------------
    */

    public function milestoneUpdated(
        string $phone,
        string $clientName,
        string $projectName,
        string $milestoneName,
        string $status
    ): bool {
        return $this->whatsapp->sendTemplate(
            $phone,
            'erp_milestone_updated',
            $clientName,
            [
                $clientName,
                $projectName,
                $milestoneName,
                $status,
            ]
        );
    }

    public function milestoneDue(
        string $phone,
        string $clientName,
        string $milestoneName,
        string $dueDate
    ): bool {
        return $this->whatsapp->sendTemplate(
            $phone,
            'erp_milestone_due',
            $clientName,
            [
                $clientName,
                $milestoneName,
                $dueDate,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Invoices
    |--------------------------------------------------------------------------
    */

    public function invoiceCreated(
        string $phone,
        string $clientName,
        string $invoiceNumber,
        string $amount
    ): bool {
        return $this->whatsapp->sendTemplate(
            $phone,
            'erp_invoice_created',
            $clientName,
            [
                $clientName,
                $invoiceNumber,
                $amount,
            ]
        );
    }

    /**
     * Invoice "like email": client + number + amount + due date + company.
     * Mirrors EmailService::sendInvoice(). Build with the wa_* helpers so the
     * approved MSG91 template `erp_invoice_sent` stays in sync with code.
     * $options (optional): ['header'=>wa_header_media(...), 'buttons'=>[wa_button_url(...),...]]
     *   e.g. attach the invoice PDF as a document header once you have a
     *   public HTTPS URL for it, or add a "Pay Now" URL button.
     */
    public function invoiceSent(
        string $phone,
        string $clientName,
        string $invoiceNumber,
        string $amount,
        string $dueDate = '',
        string $companyName = '',
        array $options = []
    ): bool {
        $due = trim((string)$dueDate);
        if ($due !== '' && $due !== '0000-00-00' && strtotime($due) !== false) {
            $due = date('d M Y', strtotime($due));
        }
        if (trim($companyName) === '') {
            try {
                $companyName = (new \App\Models\SettingModel())->getAllSettings()['company_name'] ?? 'NGWebD';
            } catch (\Throwable $e) {
                $companyName = 'NGWebD';
            }
            if (trim((string)$companyName) === '') $companyName = 'NGWebD';
        }
        $res = wa_send_template($phone, wa_template('erp_invoice_sent', wa_body(
            $clientName,
            $invoiceNumber,
            $amount,
            $due,
            $companyName
        ), $options));
        if (!$res['ok']) log_message('error', 'invoiceSent WA failed: ' . ($res['error'] ?? 'unknown'));
        return $res['ok'];
    }

    public function invoiceReminder(
        string $phone,
        string $clientName,
        string $invoiceNumber,
        string $amount,
        string $dueDate
    ): bool {
        return $this->whatsapp->sendTemplate(
            $phone,
            'erp_invoice_reminder',
            $clientName,
            [
                $clientName,
                $invoiceNumber,
                $amount,
                $dueDate,
            ]
        );
    }

    public function invoiceOverdue(
        string $phone,
        string $clientName,
        string $invoiceNumber,
        string $amount
    ): bool {
        return $this->whatsapp->sendTemplate(
            $phone,
            'erp_invoice_overdue',
            $clientName,
            [
                $clientName,
                $invoiceNumber,
                $amount,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    */

    public function paymentReceived(
        string $phone,
        string $clientName,
        string $invoiceNumber,
        string $amount
    ): bool {
        return $this->whatsapp->sendTemplate(
            $phone,
            'erp_payment_received',
            $clientName,
            [
                $clientName,
                $invoiceNumber,
                $amount,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Tasks
    |--------------------------------------------------------------------------
    */

    public function taskAssigned(
        string $phone,
        string $name,
        string $taskName,
        string $dueDate
    ): bool {
        return $this->whatsapp->sendTemplate(
            $phone,
            'erp_task_assigned',
            $name,
            [
                $name,
                $taskName,
                $dueDate,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | General
    |--------------------------------------------------------------------------
    */

    public function general(
        string $phone,
        string $name,
        string $message
    ): bool {
        return $this->whatsapp->sendTemplate(
            $phone,
            'erp_general_notification',
            $name,
            [
                $name,
                $message,
            ]
        );
    }
}