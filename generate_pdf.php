<?php
session_start();

if(!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true){
    header("location: login.php");
    exit;
}

require_once "fpdf.php";
require_once "config.php";

// FUNCTION YA KUBADILI MAKSI KUWA GRADI (Grade Calculator)
function tafuta_gradi($score) {
    if($score >= 70) return "A";
    if($score >= 60) return "B+";
    if($score >= 50) return "B";
    if($score >= 40) return "C";
    if($score >= 35) return "D";
    return "F";
}

class PDF extends FPDF {
    // Kichwa cha habari cha ukurasa
    function Header() {
        $leo = date("d-m-Y");
        echo "<div class='pdf-header'>";
        echo "<h1>STUDENT MANAGEMENT SYSTEM</h1>";
        echo "<p style='margin-top: 5px; font-size: 14px; color: #7f8c8d; font-style: italic;'>Official Academic Progress & Attendance Report</p>";
        echo "<div style='text-align: right; font-size: 11px; color: #333; margin-top: 15px; font-weight: bold;'>";
        echo "Tarehe ya Ripoti (Date): " . $leo . " | Masaa: " . date("H:i") . " EAT";
        echo "</div>";
        echo "</div>";
    }

    function Footer() {
        echo "<div class='pdf-footer'>";
        echo "Ukurasa wa 1 | Ripoti hii inaonyesha Mchanganuo wa Masomo, GPA na Mahudhurio.";
        echo "</div>";
    }
}

// 1. ANZA NA KITUFE CHA KUCHAPA (PRINT BUTTON)
echo "<style>
    .no-print-zone { text-align: center; margin-bottom: 15px; }
    .btn-print { background: #2980b9; color: white; padding: 10px 20px; border: none; border-radius: 4px; font-size: 15px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-block; }
    .btn-print:hover { background: #34495e; }
    @media print { .no-print-zone { display: none !important; } }
</style>";

echo "<div class='no-print-zone'>";
echo "  <button onclick='window.print()' class='btn-print'>🖨 Download PDF</button>";
echo "</div>";


// 2. ANZISHA RIPOTI
$pdf = new PDF();
$pdf->AddPage();

// Vichwa vya Jedwali: Jumla ya upana ni 190mm (Fixed columns)
// Tumeziweka safu zote zienee kwa ustadi mkubwa hapa:
$pdf->Cell(8, 8, 'ID', 1, 0, 'C', true);
$pdf->Cell(40, 8, 'Full Name', 1, 0, 'L', true);
$pdf->Cell(25, 8, 'Course', 1, 0, 'L', true);
$pdf->Cell(20, 8, 'Web Dev', 1, 0, 'C', true);    // Alama + Gradi ya Web
$pdf->Cell(20, 8, 'Database', 1, 0, 'C', true);   // Alama + Gradi ya DB
$pdf->Cell(20, 8, 'Network', 1, 0, 'C', true);    // Alama + Gradi ya Net
$pdf->Cell(17, 8, 'GPA', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'Phone', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'Attendance', 1, 1, 'C', true); 

// Soma data kutoka database
$sql = "SELECT s.*, 
        COUNT(a.id) as total_days,
        SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) as present_days
        FROM students s
        LEFT JOIN attendance a ON s.id = a.student_id
        GROUP BY s.id";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        
        // Piga hesabu ya mahudhurio
        $attendance_percent = "100%";
        if($row['total_days'] > 0){
            $calc = ($row['present_days'] / $row['total_days']) * 100;
            $attendance_percent = round($calc, 0) . "%";
        }

        // Tengeneza muundo wa kuonyesha alama na Gradi (Mfano: 85 (A))
        $web_display = $row['web_score'] . " (" . tafuta_gradi($row['web_score']) . ")";
        $db_display  = $row['db_score'] . " (" . tafuta_gradi($row['db_score']) . ")";
        $net_display = $row['net_score'] . " (" . tafuta_gradi($row['net_score']) . ")";

        // Kuingiza data sasa kwenye fixed table columns (Zote zimepangika salama)
        $pdf->Cell(8, 8, $row['id'], 1, 0, 'C', false);
        $pdf->Cell(40, 8, $row['fullname'], 1, 0, 'L', false);
        $pdf->Cell(25, 8, $row['course'], 1, 0, 'L', false);
        
        // Nguzo mpya za masomo na gradi zao
        $pdf->Cell(20, 8, $web_display, 1, 0, 'C', false);
        $pdf->Cell(20, 8, $db_display, 1, 0, 'C', false);
        $pdf->Cell(20, 8, $net_display, 1, 0, 'C', false);
        $pdf->Cell(17, 8, number_format($row['gpa'], 2), 1, 0, 'C', false);
        $pdf->Cell(20, 8, $row['phone'], 1, 0, 'C', false);
        $pdf->Cell(20, 8, $attendance_percent, 1, 1, 'C', false); 
    }
}

$conn->close();
$pdf->Output('I', 'Ripoti_ya_Wanafunzi.pdf');
?>