<?php
session_start();

// HAKIKI USALAMA
if(!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true){
    header("location: login.php");
    exit;
}

require_once "config.php";

// Mantiki ya SEARCH
$search = "";
if(isset($_GET['search'])){
    $search = trim($_GET['search']);
    $sql = "SELECT * FROM students WHERE fullname LIKE ? OR id = ?";
    $stmt = $conn->prepare($sql);
    $search_param = "%" . $search . "%";
    $stmt->bind_param("ss", $search_param, $search);
} else {
    $sql = "SELECT * FROM students";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <title>View Students - SMS</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
        .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-back { background: #7f8c8d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; font-weight: bold; }
        .btn-back:hover { background: #95a5a6; }
        
        .main-content { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .actions-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        
        .search-form input[type="text"] { padding: 10px; width: 250px; border: 1px solid #ccc; border-radius: 4px; }
        .search-form input[type="submit"] { padding: 10px 15px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; color: #333; }
        tr:hover { background-color: #f1f2f6; }
        
        .btn-edit { color: #3498db; text-decoration: none; font-weight: bold; margin-right: 10px; }
        .btn-delete { color: #e74c3c; text-decoration: none; font-weight: bold; }
        .no-data { text-align: center; color: #7f8c8d; padding: 20px; }

        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; margin-top: 15px; }

@media screen and (max-width: 600px) {
    .header-bar { flex-direction: column; gap: 10px; text-align: center; }
    .action-buttons { display: flex; flex-direction: column; gap: 5px; }
    .btn-edit, .btn-delete { display: block; text-align: center; margin: 0; }
}
    </style>
</head>
<body>

<div class="header-bar">
    <h2>List of registered students</h2>
    <?php
    if(isset($_SESSION["email_sent_alert"])){
        echo "<div style='background: #d1ecf1; color: #0c5460; padding: 12px; border-radius: 4px; margin-bottom: 15px; font-weight: bold; border-left: 5px solid #17a2b8;'>";
        echo "📧 " . $_SESSION["email_sent_alert"];
        echo "</div>";
        unset($_SESSION["email_sent_alert"]); // Futa baada ya kuonyesha mara moja
    }
    ?>
    <a href="dashboard.php" class="btn-back">Logout</a>
</div>

<div class="main-content">
    <div class="actions-bar">
        <form action="view_students.php" method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search by Name or ID..." value="<?php echo htmlspecialchars($search); ?>">
            <input type="submit" value="Search">
            <?php if(!empty($search)): ?>
                <a href="view_students.php" style="margin-left:5px; color:#7f8c8d;">Clear</a>
            <?php endif; ?>
        </form>
        <a href="generate_pdf.php" target="_blank" style="background:#e67e22; color:white; padding:10px; text-decoration:none; border-radius:4px; font-weight:bold; margin-right:5px;">🖨 Download students Report</a>
        <a href="add_student.php" style="background:#2ecc71; color:white; padding:10px; text-decoration:none; border-radius:4px; font-weight:bold;">+ Register new student</a>
            </div>
      <div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Photo</th>
                <th>ID</th>
                <th>Full Name</th>
                <th>Gender</th>
                <th>Student Course</th>
                <th>Email</th>
                <th>Phone Number</th>
                <th>GPA</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
        <img src="uploads/<?php echo htmlspecialchars($row['photo']); ?>" alt="Photo" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 1px solid #ddd;">
    </td>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($row['gender']); ?></td>
                        <td><?php echo htmlspecialchars($row['course']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><b style="color: #2c3e50;"><?php echo number_format($row['gpa'], 2); ?></b></td>
                        <td>
                            <a href="edit_student.php?id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                            <a href="delete_student.php?id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="no-data">No students found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
            </div>
</div>

</body>
</html>
<?php 
$stmt->close();
$conn->close();
?>