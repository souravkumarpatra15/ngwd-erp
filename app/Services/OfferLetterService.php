<?php

namespace App\Services;

use CodeIgniter\HTML\Image;
use Dompdf\Dompdf;
use Dompdf\Options;

class OfferLetterService
{
    protected $dompdf;

    public function __construct()
    {
        $options = new Options();
        $options->set('isHtml5Enabled', true);
        $options->set('defaultFont', 'Noto Sans');
        $this->dompdf = new Dompdf($options);
    }

    public function generatePdf(string $htmlContent, string $filename = 'offer_letter.pdf'): string
    {
        $this->dompdf->loadHtml($htmlContent);
        $this->dompdf->render();
        $output = $this->dompdf->output();
        file_put_contents('internship/letters/' . $filename, $output);
        return $filename;
    }

    public function renderCertificateTemplate(array $data): string
    {
        $e = static fn($v, $d = '') => esc((string) (($v ?? '') !== '' ? $v : $d));
        $fmtDate = static function ($v): string {
            $t = $v ? strtotime((string) $v) : false;
            return $t ? date('F j, Y', $t) : '—';
        };

        $name        = $e($data['candidate_name'] ?? null, 'Candidate Name');
        $position    = $e($data['position'] ?? null, 'Web Development Intern');
        $program     = $e($data['program_name'] ?? null, 'NGWebD Internship Programme');
        $department  = $e($data['department'] ?? null);
        $duration    = $e($data['duration'] ?? null);
        $certId      = $e($data['certificate_id'] ?? null, 'N/A');
        $signatory   = $e($data['signatory_name'] ?? null, 'Authorized Signatory');
        $sigTitle    = $e($data['signatory_title'] ?? null, 'Founder, NGWebD');
        $verifyUrl   = $e($data['verify_url'] ?? null);
        $startDate   = $fmtDate($data['start_date'] ?? null);
        $endDate     = $fmtDate($data['end_date'] ?? null);
        $issueDate   = $fmtDate($data['issue_date'] ?? date('Y-m-d'));
        $year        = date('Y', strtotime((string) ($data['issue_date'] ?? 'now')) ?: time());

        $achievement = $e(
            $data['achievement_text'] ?? null,
            'for successfully completing the internship programme with dedication, '
                . 'professionalism and a strong commitment to learning.'
        );

        $skills = $data['skills'] ?? [];
        if (is_string($skills)) {
            $skills = array_filter(array_map('trim', explode(',', $skills)));
        }
        $skillsHtml = '';
        foreach (array_slice((array) $skills, 0, 8) as $s) {
            $skillsHtml .= '<span class="chip">' . esc((string) $s) . '</span> ';
        }
        $skillsBlock = $skillsHtml !== ''
            ? '<div class="skills"><div class="skills-label">SKILLS &amp; TECHNOLOGIES</div>' . $skillsHtml . '</div>'
            : '';

        $logo = !empty($data['logo_data_uri'])
            ? '<img src="' . esc($data['logo_data_uri']) . '" style="height:42px;" alt="NGWebD">'
            : '<span class="wordmark">NG<span class="gold">WebD</span></span>';

        $deptLine = $department !== '' ? " &nbsp;&bull;&nbsp; {$department}" : '';
        $durLine  = $duration !== '' ? " ({$duration})" : '';
        $verify   = $verifyUrl !== '' ? "Verify at {$verifyUrl}" : '';

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Certificate of Internship - {$name}</title>
<style>
    @page { size: A4 landscape; margin: 0; }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; }
    body { font-family: "DejaVu Sans", "Noto Sans", Arial, sans-serif; color: #1F2A44; background: #FFFFFF; }

    .page    { width: 297mm; height: 209mm; padding: 8mm; background: #FBF8F1; }
    .frame   { width: 100%; height: 100%; border: 3mm solid #14213D; padding: 2mm; }
    .inner   { width: 100%; height: 100%; border: 0.6mm solid #C9A227; padding: 5mm 14mm 0 14mm; text-align: center; position: relative; }

    .corner  { position: absolute; width: 14mm; height: 14mm; border: 0 solid #C9A227; }
    .tl { top: 3mm; left: 3mm;  border-top-width: 1.2mm; border-left-width: 1.2mm; }
    .tr { top: 3mm; right: 3mm; border-top-width: 1.2mm; border-right-width: 1.2mm; }
    .bl { bottom: 3mm; left: 3mm;  border-bottom-width: 1.2mm; border-left-width: 1.2mm; }
    .br { bottom: 3mm; right: 3mm; border-bottom-width: 1.2mm; border-right-width: 1.2mm; }

    .wordmark { font-size: 24px; font-weight: bold; letter-spacing: 2px; color: #14213D; }
    .gold     { color: #C9A227; }

    .eyebrow  { margin-top: 3mm; font-size: 10px; letter-spacing: 5px; color: #8A6D0B; }
    .title    { margin: 2mm 0 0 0; font-family: "DejaVu Serif", Georgia, "Times New Roman", serif; font-size: 40px; letter-spacing: 4px; color: #14213D; font-weight: bold; }
    .subtitle { margin: 1mm 0 0 0; font-family: "DejaVu Serif", Georgia, serif; font-size: 15px; letter-spacing: 6px; color: #C9A227; }

    .divider  { width: 60mm; height: 0.5mm; background: #C9A227; margin: 4mm auto; }

    .presented{ font-size: 11px; letter-spacing: 3px; color: #5B6785; margin-top: 2mm; }
    .name     { font-family: "DejaVu Serif", Georgia, "Times New Roman", serif; font-style: italic; font-size: 38px; color: #14213D; margin: 3mm auto 0 auto; padding: 0 10mm 2mm 10mm; border-bottom: 0.4mm solid #C9A227; display: inline-block; }

    .body     { width: 190mm; margin: 5mm auto 0 auto; font-size: 12.5px; line-height: 1.7; color: #33415F; }
    .body b   { color: #14213D; }

    .skills   { margin-top: 4mm; }
    .skills-label { font-size: 8px; letter-spacing: 3px; color: #8A6D0B; margin-bottom: 2mm; }
    .chip     { display: inline-block; font-size: 9.5px; padding: 1mm 3mm; margin: 0 1mm; border: 0.3mm solid #C9A227; color: #14213D; background: #FFFFFF; }

    .sign     { width: 100%; margin-top: 6mm; border-collapse: collapse; }
    .sign td  { vertical-align: bottom; text-align: center; width: 33.33%; }
    .line     { width: 55mm; border-top: 0.3mm solid #14213D; margin: 0 auto; padding-top: 1.5mm; font-size: 11px; font-weight: bold; color: #14213D; }
    .small    { font-size: 9.5px; color: #5B6785; font-weight: normal; }

    .seal-o   { width: 30mm; height: 30mm; border-radius: 50%; border: 0.8mm solid #C9A227; background: #14213D; margin: 0 auto; padding: 1.5mm; }
    .seal-i   { width: 100%; height: 100%; border-radius: 50%; border: 0.3mm dashed #C9A227; text-align: center; color: #C9A227; padding-top: 7.5mm; }
    .seal-1   { font-size: 11px; font-weight: bold; letter-spacing: 1px; }
    .seal-2   { font-size: 7px; letter-spacing: 2px; margin-top: 1mm; color: #FBF8F1; }
    .seal-3   { font-size: 8px; margin-top: 1mm; }

    .foot     { position: absolute; left: 0; right: 0; bottom: 4mm; font-size: 8.5px; color: #7A859E; letter-spacing: 0.5px; }
</style>
</head>
<body>
<div class="page">
  <div class="frame">
    <div class="inner">
      <div class="corner tl"></div><div class="corner tr"></div>
      <div class="corner bl"></div><div class="corner br"></div>

      {$logo}
      <div class="eyebrow">NGWEBD &nbsp;&bull;&nbsp; INTERNSHIP PROGRAMME</div>
      <div class="title">CERTIFICATE</div>
      <div class="subtitle">OF INTERNSHIP COMPLETION</div>
      <div class="divider"></div>

      <div class="presented">THIS CERTIFICATE IS PROUDLY PRESENTED TO</div>
      <div class="name">{$name}</div>

      <div class="body">
        who has served as <b>{$position}</b>{$deptLine} in the <b>{$program}</b>
        from <b>{$startDate}</b> to <b>{$endDate}</b>{$durLine},
        {$achievement}
      </div>

      {$skillsBlock}

      <table class="sign">
        <tr>
          <td>
            <div class="line">{$issueDate}<br><span class="small">Date of Issue</span></div>
          </td>
          <td>
            <div class="seal-o"><div class="seal-i">
              <div class="seal-1">NGWebD</div>
              <div class="seal-2">CERTIFIED</div>
              <div class="seal-3">{$year}</div>
            </div></div>
          </td>
          <td>
            <div class="line">{$signatory}<br><span class="small">{$sigTitle}</span></div>
          </td>
        </tr>
      </table>

      <div class="foot">Certificate ID: {$certId} &nbsp;&nbsp;|&nbsp;&nbsp; {$verify}</div>
    </div>
  </div>
</div>
</body>
</html>
HTML;

        return $html;
    }
}
