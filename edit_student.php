<?php
// Anzisha session na ukague usalama
session_start();
if(!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true){
    header("location: login.php");
    exit;
}

// Leta faili la database
require_once "config.php";

// Define variables na uziweke zikiwa tupu
$fullname = $gender = $course = $email = $phone = $photo = "";
$web_score = $db_score = $net_score = 0;
$fullname_err = $gender_err = $course_err = $email_err = $phone_err = $photo_err = "";

// MANTIKI YA KUPIGA HESABU ZA GPA (Hii itatumika wakati wa ku-update)
function tafuta_pointi($score) {
    if($score >= 70) return 5;
    if($score >= 60) return 4;
    if($score >= 50) return 3;
    if($score >= 40) return 2;
    if($score >= 35) return 1;
    return 0;
}

// 1. HATUA YA KWANZA: Soma data zilizopo (pamoja na alama na picha)
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    $id = trim($_GET["id"]);
    
    $sql = "SELECT * FROM students WHERE id = ?";
    if($stmt = $conn->prepare($sql)){
        $stmt->bind_param("i", $param_id);
        $param_id = $id;
        
        if($stmt->execute()){
            $result = $stmt->get_result();
            
            if($result->num_rows == 1){
                $row = $result->fetch_assoc();
                
                $fullname = $row["fullname"];
                $gender = $row["gender"];
                $course = $row["course"];
                $email = $row["email"];
                $phone = $row["phone"];
                $photo = $row["photo"];
                $web_score = $row["web_score"]; // Soma alama ya Web
                $db_score = $row["db_score"];   // Soma alama ya DB
                $net_score = $row["net_score"];  // Soma alama ya Network
            } else {
                header("location: view_students.php");
                exit();
            }
        } else {
            echo "Kuna kitu kimefeli upande wa database.";
        }
        $stmt->close();
    }
}

// 2. HATUA YA PILI: Kuchakata data pale Admin anapobonyeza "Sasisha (Update)"
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $id = $_POST["id"];
    $current_photo = $_POST["current_photo"];
    
    // VALIDATIONS
    if(empty(trim($_POST["fullname"]))){
        $fullname_err = "Tafadhali jaza jina kamili.";
    } else {
        $fullname = trim($_POST["fullname"]);
    }
    
    if(empty(trim($_POST["gender"]))){
        $gender_err = "Tafadhali chagua jinsia.";
    } else {
        $gender = trim($_POST["gender"]);
    }
    
    if(empty(trim($_POST["course"]))){
        $course_err = "Tafadhali jaza kozi.";
    } else {
        $course = trim($_POST["course"]);
    }
    
    if(empty(trim($_POST["email"]))){
        $email_err = "Tafadhali jaza email.";
    } elseif(!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)){
        $email_err = "Muundo wa email siyo sahihi.";
    } else {
        $email = trim($_POST["email"]);
    }
    
    if(empty(trim($_POST["phone"]))){
        $phone_err = "Tafadhali jaza namba ya simu.";
    } else {
        $phone = trim($_POST["phone"]);
        if(!preg_match('/^\+?[0-9]{10,13}$/', $phone)){
            $phone_err = "Namba ya simu haipo sahihi. Tumia namba tu (Mfano: 0712345678).";
        }
    }
    
    // Pokea alama mpya zilizohaririwa
    $web_score = !empty($_POST["web_score"]) ? intval($_POST["web_score"]) : 0;
    $db_score = !empty($_POST["db_score"]) ? intval($_POST["db_score"]) : 0;
    $net_score = !empty($_POST["net_score"]) ? intval($_POST["net_score"]) : 0;

    // Piga hesabu ya GPA MPYA hapa hapa
    $gpa = (tafuta_pointi($web_score) + tafuta_pointi($db_score) + tafuta_pointi($net_score)) / 3;
    $gpa = round($gpa, 2);

    // KUCHAKATA PICHA
    $photo_name = $current_photo;
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $file_tmp = $_FILES['photo']['tmp_name'];
        $file_name = $_FILES['photo']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = array('jpg', 'jpeg', 'png');
        
        if(in_array($file_ext, $allowed_ext)) {
            $photo_name = time() . '_' . rand(1000, 9999) . '.' . $file_ext;
            $upload_path = 'uploads/' . $photo_name;
            
            if(move_uploaded_file($file_tmp, $upload_path)){
                if($current_photo != "default.png" && file_exists("uploads/" . $current_photo)){
                    unlink("uploads/" . $current_photo);
                }
            } else {
                $photo_err = "Imeshindwa kuhifadhi picha mpya.";
            }
        } else {
            $photo_err = "Weka JPG, JPEG au PNG tu.";
        }
    }
    
    // KAMA HAKUNA MAKOSA, FANYA UPDATE (Pamoja na alama mpya na GPA mpya)
    if(empty($fullname_err) && empty($gender_err) && empty($course_err) && empty($email_err) && empty($phone_err) && empty($photo_err)){
        
        $sql = "UPDATE students SET fullname=?, gender=?, course=?, email=?, phone=?, photo=?, web_score=?, db_score=?, net_score=?, gpa=? WHERE id=?";
         
        if($stmt = $conn->prepare($sql)){
            // "ssssssiiidi" -> strings 6, integers 3, double 1, na integer ya ID mwishoni 1
            $stmt->bind_param("ssssssiiidi", $param_fullname, $param_gender, $param_course, $param_email, $param_phone, $param_photo, $param_web, $param_db, $param_net, $param_gpa, $param_id);
            
            $param_fullname = $fullname;
            $param_gender = $gender;
            $param_course = $course;
            $param_email = $email;
            $param_phone = $phone;
            $param_photo = $photo_name;
            $param_web = $web_score;
            $param_db = $db_score;
            $param_net = $net_score;
            $param_gpa = $gpa;
            $param_id = $id;
            
            if($stmt->execute()){
                header("location: view_students.php");
                exit();
            } else {
                echo "Kuna hitilafu ilitokea wakati wa kusasisha data.";
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <title>Edit Student - SMS</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f6f9; padding: 20px; display: flex; justify-content: center; }
        .form-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); width: 450px; }
        h2 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #f1f2f6; padding-bottom: 10px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #555; font-weight: bold; }
        .form-group input[type="text"], .form-group input[type="email"], .form-group input[type="number"], .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .error-msg { color: #e74c3c; font-size: 13px; margin-top: 5px; }
        .btn-box { display: flex; justify-content: space-between; margin-top: 20px; }
        .btn-save { background: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .btn-save:hover { background: #2980b9; }
        .btn-cancel { background: #95a5a6; color: white; text-decoration: none; padding: 10px 20px; border-radius: 4px; font-size: 16px; }
        .current-photo-box { text-align: center; margin-bottom: 15px; }
        .current-photo-box img { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid #3498db; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Edit Student Information</h2>
    <p style="color: #777; font-size: 14px;">Modify the information and scores as needed, then click Update.</p>

    <form action="edit_student.php" method="POST" enctype="multipart/form-data">
        
        <input type="hidden" name="id" value="<?php echo $id; ?>"/>
        <input type="hidden" name="current_photo" value="<?php echo $photo; ?>"/>

        <div class="current-photo-box">
            <img src="uploads/<?php echo htmlspecialchars($photo); ?>" alt="Current Photo">
            <p style="margin: 5px 0 0 0; font-size: 12px; color: #777;">Current Photo</p>
        </div>

        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>">
            <div class="error-msg"><?php echo $fullname_err; ?></div>
        </div>

        <div class="form-group">
            <label>Gender</label>
            <select name="gender">
                <option value="Male" <?php if($gender == "Male") echo "selected"; ?>>Male</option>
                <option value="Female" <?php if($gender == "Female") echo "selected"; ?>>Female</option>
            </select>
            <div class="error-msg"><?php echo $gender_err; ?></div>
        </div>

        <div class="form-group">
            <label>Course</label>
            <input type="text" name="course" value="<?php echo htmlspecialchars($course); ?>">
            <div class="error-msg"><?php echo $course_err; ?></div>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
            <div class="error-msg"><?php echo $email_err; ?></div>
        </div>

        <div class="form-group">
            <label>Phone Number</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
            <div class="error-msg"><?php echo $phone_err; ?></div>
        </div>

        <div style="display: flex; gap: 10px; background: #f8f9fa; padding: 10px; border-radius: 6px; margin-bottom: 15px;">
            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <label style="font-size: 12px;">Web Score</label>
                <input type="number" name="web_score" min="0" max="100" value="<?php echo $web_score; ?>" required>
            </div>
            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <label style="font-size: 12px;">DB Score</label>
                <input type="number" name="db_score" min="0" max="100" value="<?php echo $db_score; ?>" required>
            </div>
            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <label style="font-size: 12px;">Net Score</label>
                <input type="number" name="net_score" min="0" max="100" value="<?php echo $net_score; ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label>Change Photo</label>
            <input type="file" name="photo" accept="image/*">
            <div class="error-msg"><?php echo $photo_err; ?></div>
        </div>

        <div class="btn-box">
            <a href="view_students.php" class="btn-cancel">Cancel</a>
            <input type="submit" class="btn-save" value="Update">
        </div>
    </form>
</div>

</body>
</html>