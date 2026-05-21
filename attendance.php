<?php
session_start();
if(!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true){
    header("location: login.php");
    exit;
}

require_once "config.php";

$msg = "";

// Kuchakata mahudhurio yakitumwa (Submitted)
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $attendance_date = $_POST["attendance_date"];
    $status_array = $_POST["status"]; // Hii itapokea array ya ID za wanafunzi na status zao

    if(!empty($attendance_date) && !empty($status_array)){
        
        // Kwanza futa mahudhurio ya tarehe hiyo kama yalisharekodiwa kabla (Ili kuzuia double entry)
        $delete_sql = "DELETE FROM attendance WHERE attendance_date = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("s", $attendance_date);
        $stmt->execute();
        $stmt->close();

        // Ingiza mahudhurio mapya
        $insert_sql = "INSERT INTO attendance (student_id, attendance_date, status) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);

        foreach($status_array as $student_id => $status){
            $stmt->bind_param("iss", $student_id, $attendance_date, $status);
            $stmt->execute();
        }
        $stmt->close();
        $msg = "<div class='success-msg'>attendance of date  $attendance_date successfully saved!</div>";
    } else {
        $msg = "<div class='error-msg'>Please select a date and fill in all attendance records.</div>";
    }
}

// Leta orodha ya wanafunzi kwa ajili ya kuonyesha kwenye fomu
$sql = "SELECT id, fullname FROM students";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <title>Attendance Management - SMS</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
        .container { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); max-width: 700px; margin: 0 auto; }
        .header-bar { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f2f6; padding-bottom: 15px; margin-bottom: 20px; }
        .btn-back { background: #7f8c8d; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
        .date-picker { padding: 10px; font-size: 16px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 15px; }
        .btn-submit { background: #2ecc71; color: white; border: none; padding: 12px 20px; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold; margin-top: 20px; width: 100%; }
        .btn-submit:hover { background: #27ae60; }
        .success-msg { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .error-msg { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .radio-group label { margin-right: 15px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-bar">
        <h2>Take Attendance</h2>
        <a href="dashboard.php" class="btn-back">Back</a>
    </div>

    <?php echo $msg; ?>

    <form action="attendance.php" method="POST">
        <label style="font-weight: bold; display: block; margin-bottom: 5px;">select date of today:</label>
        <input type="date" name="attendance_date" class="date-picker" value="<?php echo date('Y-m-d'); ?>" required>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Attendance Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><b><?php echo htmlspecialchars($row['fullname']); ?></b></td>
                            <td class="radio-group">
                                <label style="color: #2ecc71;">
                                    <input type="radio" name="status[<?php echo $row['id']; ?>]" value="Present" checked> Present
                                </label>
                                <label style="color: #e74c3c;">
                                    <input type="radio" name="status[<?php echo $row['id']; ?>]" value="Absent"> Absent
                                </label>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align: center; color: #7f8c8d;">No student registered yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if($result->num_rows > 0): ?>
            <input type="submit" class="btn-submit" value="store to day attendance">
        <?php endif; ?>
    </form>
</div>

</body>
</html>
<?php $conn->close(); ?>