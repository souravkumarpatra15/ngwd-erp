<?php
namespace App\Services;
use Dompdf\Dompdf;
use Dompdf\Options;

class PDFService
{
    protected function makeDompdf(): Dompdf {
        $opts = new Options();
        $opts->set('defaultFont','DejaVu Sans');
        $opts->set('isRemoteEnabled', true);
        $opts->set('isHtml5ParserEnabled', true);
        return new Dompdf($opts);
    }

    public function generateInvoice(array $invoice, array $items, array $settings) {
        $html = view('pdf/invoice', compact('invoice','items','settings'));
        $this->stream($html, "Invoice-{$invoice['invoice_number']}.pdf");
    }

    public function generateInvoicePDF(array $invoice, array $items, array $settings): string {
        $html = view('pdf/invoice', compact('invoice','items','settings'));
        return $this->save($html, "invoice-{$invoice['id']}.pdf");
    }

    public function generateProposal(array $proposal, array $settings) {
        $html = view('pdf/proposal', compact('proposal','settings'));
        $this->stream($html, "Proposal-{$proposal['proposal_number']}.pdf");
    }

    public function saveProposalPDF(array $proposal, array $settings): string {
        $html = view('pdf/proposal', compact('proposal','settings'));
        return $this->save($html, "proposal-{$proposal['id']}.pdf");
    }

    public function generateAgreement(array $agreement, array $settings) {
        $html = view('pdf/agreement', compact('agreement','settings'));
        $this->stream($html, "Agreement-{$agreement['agreement_number']}.pdf");
    }

    public function saveAgreementPDF(array $agreement, array $settings): string {
        $html = view('pdf/agreement', compact('agreement','settings'));
        return $this->save($html, "agreement-{$agreement['id']}.pdf");
    }

    public function generateReceipt(array $payment, array $settings) {
        $html = view('pdf/receipt', compact('payment','settings'));
        $this->stream($html, "Receipt-{$payment['payment_number']}.pdf");
    }

    protected function stream(string $html, string $filename) {
        $pdf = $this->makeDompdf();
        $pdf->loadHtml($html); $pdf->setPaper('A4','portrait'); $pdf->render();
        $pdf->stream($filename, ['Attachment'=>0]); exit;
    }

    protected function save(string $html, string $filename): string {
        $pdf = $this->makeDompdf();
        $pdf->loadHtml($html); $pdf->setPaper('A4','portrait'); $pdf->render();
        $path = WRITEPATH.'pdfs/'.$filename;
        file_put_contents($path, $pdf->output());
        return $path;
    }
}
