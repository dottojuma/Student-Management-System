<?php
// Enhanced CSS-Stable FPDF Simulator for Student Report
class FPDF {
    protected $page = 0;
    protected $is_header_printed = false;

    public function __construct() {
        // Inaanza kuandaa muundo mzuri wa ukurasa na staili za CSS
        echo "<style>
            body { background-color: #f5f5f5; margin: 0; padding: 20px; font-family: 'Helvetica', Arial, sans-serif; }
            .pdf-page { width: 210mm; background: white; margin: 0 auto; padding: 20px; box-sizing: border-box; box-shadow: 0 0 10px rgba(0,0,0,0.1); min-height: 297mm; position: relative; }
            .pdf-header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #333; padding-bottom: 10px; }
            .pdf-header h1 { margin: 0; font-size: 24px; color: #2c3e50; font-weight: bold; }
            .pdf-header p { margin: 5px 0 0 0; font-size: 14px; color: #7f8c8d; font-style: italic; }
            
            /* JEDWALI THABITI (FIXED TABLE) */
            .pdf-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 10px; }
            .pdf-table th, .pdf-table td { 
                border: 1px solid #333; 
                padding: 8px 5px; 
                font-size: 12px; 
                white-space: nowrap; 
                overflow: hidden; 
                text-overflow: ellipsis; /* Kama neno ni refu sana linaweka vitone ... badala ya kuingilia jirani */
            }
            .pdf-table th { background-color: #e6e6e6; font-weight: bold; text-align: left; }
            .text-center { text-align: center !important; }
            
            .pdf-footer { position: absolute; bottom: 20px; left: 0; width: 100%; text-align: center; font-size: 10px; color: #7f8c8d; font-style: italic; }
            @media print {
                body { background: white; padding: 0; }
                .pdf-page { box-shadow: none; margin: 0; width: 100%; }
            }
        </style>";
        echo "<div class='pdf-page'>";
    }

    public function AddPage() {
        $this->page++;
        if(!$this->is_header_printed) {
            $this->Header();
            $this->is_header_printed = true;
            // Anzisha jedwali rasmi hapa
            echo "<table class='pdf-table'>";
        }
    }

    public function Header() {}
    public function Footer() {}
    public function AliasNbPages() {}
    public function SetFont($family, $style='', $size=0) {}
    public function SetFillColor($r, $g=-1, $b=-1) {}
    public function Ln($h=null) {}
    public function Line($x1, $y1, $x2, $y2) {}

    // Hii sasa inatambua kama ni Kichwa cha jedwali (TH) au maudhui (TD)
    public function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false) {
        // Badilisha upana wa mm kwenda asilimia (%) ya karatasi ili iwe dhabiti zaidi
        // Karatasi nzima ya A4 inayoweza kutumika ina upana wa karibu 190mm
        $width_percent = ($w / 190) * 100;
        
        $class = "";
        if($align == 'C') $class = "class='text-center'";
        
        if($fill) {
            // Kama fill ni true, inamaanisha tunatengeneza Kichwa cha Jedwali (Header Row)
            echo "<th style='width: {$width_percent}%;' {$class}>" . htmlspecialchars($txt) . "</th>";
            if($ln == 1) echo "</tr><tr>"; // Kama mstar umeisha, funga mstari wa sasa fungua mwingine
        } else {
            // Vinginevyo ni data ya kawaida (Normal row cell)
            echo "<td style='width: {$width_percent}%;' {$class}>" . htmlspecialchars($txt) . "</td>";
            if($ln == 1) echo "</tr><tr>";
        }
    }

    public function Output($dest='', $name='') {
        // Funga lile jedwali na kurasa zilizofunguliwa
        echo "</tr></table>"; 
        $this->Footer();
        echo "</div>";
        echo "<title>".$name."</title>";
        exit;
    }
}
?>