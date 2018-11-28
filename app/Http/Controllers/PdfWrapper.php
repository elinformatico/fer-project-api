<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use mPDF;

class PdfWrapper extends Controller
{
    protected $mpdf;

    public function __construct($character = 'utf-8') {
        $this->mpdf = new \mPDF($character);
    }

    public function AddPage($orientation, $m_left, $m_right, $m_top, $m_bottom, $m_header, $m_footer, $sheet) {
        $this->mpdf->AddPage($orientation, '', '', '', '', $m_left, $m_right, $m_top, $m_bottom, $m_header, $m_footer, '', '', '', '', '', '', '', '', '', $sheet);
    }

    public function SetHTMLHeader($header, $param = '') {
        $this->mpdf->SetHTMLHeader($header, $param);
    }

    public function SetHTMLFooter($footer, $param = '') {
        $this->mpdf->SetHTMLFooter($footer, $param);
    }

    public function mirrorMargins($mirrorMargins) {
        $this->mpdf->mirrorMargins = $mirrorMargins;
    }

    public function displayBarcodeNumbers($show = true) {
        $this->mpdf->displayBarcodeNumbers = $show;
    }

    public function SetProtection($protection = []) {
        $this->mpdf->SetProtection($protection);
    }

    public function SetTitle($title) {
        $this->mpdf->SetTitle($title);
    }

    public function SetAuthor($author) {
        $this->mpdf->SetAuthor($author);
    }

    public function SetWatermarkText($wm_text) {
        $this->mpdf->SetWatermarkText($wm_text);
    }

    public function SetShowWatermarkText($showWatermarkText) {
        $this->mpdf->showWatermarkText = $showWatermarkText;
    }

    public function SetWatermark_font($font) {
        $this->mpdf->watermark_font = $font;
    }

    public function SetWatermarkTextAlpha($alpha) {
        $this->mpdf->watermarkTextAlpha = $alpha;
    }

    public function SetDisplayMode($mode) {
        $this->mpdf->SetDisplayMode($mode);
    }

    public function loadView($view, $data = [], $mergeData = []) {
        $this->html = \View::make($view, $data, $mergeData)->render();

        return $this;
    }

    public function output() {
        $this->mpdf->WriteHTML($this->html);

        return $this->mpdf->Output('', 'S');
    }

    public function save($filename) {
        $this->mpdf->WriteHTML($this->html);

        return $this->mpdf->Output($filename, 'F');
    }

    public function download($filename = 'document.pdf') {
        $this->mpdf->WriteHTML($this->html);

        return $this->mpdf->Output($filename, 'D');
    }

    public function stream($filename = 'document.pdf') {
        $this->mpdf->WriteHTML($this->html);

        return $this->mpdf->Output($filename, 'I');
    }
}
