<?php
session_start();

// HAKIKI USALAMA
if(!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true){
    header("location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - SMS</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
        .header { background: #2c3e50; color: white; padding: 20px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; }
        .btn-logout { background: #e74c3c; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; font-weight: bold; }
        
        .main-content { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; }
        h2 { color: #333; margin-bottom: 30px; }
        
        .menu-grid { display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; }
        .menu-card { background: #f8f9fa; border: 1px solid #ddd; padding: 25px; width: 200px; border-radius: 8px; text-decoration: none; color: #333; font-weight: bold; font-size: 18px; transition: transform 0.2s, background 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .menu-card:hover { transform: translateY(-5px); background: #3498db; color: white; border-color: #3498db; }

        /* MAREKEBISHO YA SIMU (MOBILE RESPONSIVENESS) */
@media screen and (max-width: 600px) {
    body { padding: 10px; }
    .dashboard-container { padding: 15px; }
    .header-bar { flex-direction: column; text-align: center; gap: 10px; }
    .menu-grid { grid-template-columns: 1fr; /* Kadi zinajipanga moja moja chini chini badala ya pembeni */ }
    .menu-card { padding: 20px; }
}
    </style>
</head>
<body>

<div class="header">
    <h1>Student Management System</h1>
    <div>
        <span>welcome, <b><?php echo htmlspecialchars($_SESSION["admin_username"]); ?></b> | </span>
        <a href="logout.php" class="btn-logout">logout</a>
    </div>
</div>

<div class="main-content">
    <h2>Admin Panel</h2>
    
    <div class="menu-grid">
        <a href="add_student.php" class="menu-card">
            <div style="font-size: 30px; margin-bottom: 10px;"></div>
            Add New student
        </a>
        
        <a href="view_students.php" class="menu-card">
            <div style="font-size: 30px; margin-bottom: 10px;"></div>
            View Students <br><span style="font-size:12px; font-weight:normal;">(View Records)</span>
        </a>
        <a href="attendance.php" class="menu-card">
    <div style="font-size: 30px; margin-bottom: 10px;"></div>
    Attendance  <br><span style="font-size:12px; font-weight:normal;">(Take Attendance)</span>
</a>
    </div>
</div>

</body>
</html>