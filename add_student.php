<?php
// Anzisha session na ukague usalama
session_start();
if(!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true){
    header("location: login.php");
    exit;
}

// Leta faili la database
require_once "config.php";

// Define variables na uziweke zikiwa tupu kwa kuanzia
$fullname = $gender = $course = $email = $phone = "";
$fullname_err = $gender_err = $course_err = $email_err = $phone_err = $photo_err = "";
$web_score = $db_score = $net_score = "";
// Kuchakata data pale fomu itakapokuwa "submitted"
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // 1. VALIDATION: Majina Kamili
    if(empty(trim($_POST["fullname"]))){
        $fullname_err = "Tafadhali jaza jina kamili.";
    } else {
        $fullname = trim($_POST["fullname"]);
    }
    
    // 2. VALIDATION: Jinsia
    if(empty(trim($_POST["gender"]))){
        $gender_err = "Tafadhali chagua jinsia.";
    } else {
        $gender = trim($_POST["gender"]);
    }
    
    // 3. VALIDATION: Kozi
    if(empty(trim($_POST["course"]))){
        $course_err = "Tafadhali jaza kozi.";
    } else {
        $course = trim($_POST["course"]);
    }
    
    // 4. VALIDATION: Barua Pepe (Email)
    if(empty(trim($_POST["email"]))){
        $email_err = "Tafadhali jaza barua pepe (email).";
    } elseif(!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)){
        $email_err = "Muundo wa email uliyoweka siyo sahihi.";
    } else {
        $email = trim($_POST["email"]);
    }
    
    // 5. VALIDATION: Namba ya Simu (pamoja na REGEX uliyogundua)
    if(empty(trim($_POST["phone"]))){
        $phone_err = "Tafadhali jaza namba ya simu.";
    } else {
        $phone = trim($_POST["phone"]);
        if(!preg_match('/^\+?[0-9]{10,13}$/', $phone)){
            $phone_err = "Namba ya simu haipo sahihi. Tumia namba tu (Mfano: 0712345678).";
        }
    }
     $web_score = !empty($_POST["web_score"]) ? intval($_POST["web_score"]) : 0;
     $db_score = !empty($_POST["db_score"]) ? intval($_POST["db_score"]) : 0;
     $net_score = !empty($_POST["net_score"]) ? intval($_POST["net_score"]) : 0;

// MANTIKI YA KUPIGA HESABU ZA GPA (GPA Logic)
function tafuta_pointi($score) {
    if($score >= 70) return 5;
    if($score >= 60) return 4;
    if($score >= 50) return 3;
    if($score >= 40) return 2;
    if($score >= 35) return 1;
    return 0;
}

$gpa = (tafuta_pointi($web_score) + tafuta_pointi($db_score) + tafuta_pointi($net_score)) / 3;
$gpa = round($gpa, 2); // Weka katika muundo wa decimal mbili (mfano: 4.33)

    // 6. KUCHAKATA PICHA (Photo Upload Logic)
    $photo_name = "default.png"; // Jina la msingi kama picha haitapakiwa

    if(isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $file_tmp = $_FILES['photo']['tmp_name'];
        $file_name = $_FILES['photo']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Ruhusu maumbo ya picha tu
        $allowed_ext = array('jpg', 'jpeg', 'png');
        
        if(in_array($file_ext, $allowed_ext)) {
            // Badilisha jina la picha liwe la kipekee ili zisigongane
            $photo_name = time() . '_' . rand(1000, 9999) . '.' . $file_ext;
            $upload_path = 'uploads/' . $photo_name;
            
            // Hamisha picha kwenda kwenye folder la uploads
            if(!move_uploaded_file($file_tmp, $upload_path)){
                $photo_err = "Imeshindwa kuhifadhi picha kwenye server.";
            }
        } else {
            $photo_err = "Aina ya faili hairuhusiwi. Tafadhali weka picha ya JPG, JPEG au PNG.";
        }
    }
    
    // 7. KAMA HAKUNA MAKOSA, WEKA DATA KWENYE DATABASE
    if(empty($fullname_err) && empty($gender_err) && empty($course_err) && empty($email_err) && empty($phone_err) && empty($photo_err) && empty($web_score_err) && empty($db_score_err) && empty($net_score_err)){
        
       $sql = "INSERT INTO students (fullname, gender, course, email, phone, photo, web_score, db_score, net_score, gpa) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

if($stmt = $conn->prepare($sql)){
    // "ssssssiiid" -> strings 6, integers 3 za alama, na double/decimal 1 ya GPA
    $stmt->bind_param("ssssssiiid", $param_fullname, $param_gender, $param_course, $param_email, $param_phone, $param_photo, $param_web, $param_db, $param_net, $param_gpa);

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

    if($stmt->execute()){
                // ==================================================
                // MFUMO WA TAARIFA KWA EMAIL (EMAIL NOTIFICATION LOGIC)
                // ==================================================
                $to_email = $email;
                $subject = "Karibu Kwenye Mfumo - Student Management System";
                
                // Ujumbe wa Email
                $message = "Habari " . $fullname . ",\n\n";
                $message .= "Hongera! Umesajiliwa kikamilifu kwenye Student Management System.\n";
                $message .= "Course yako iliyosajiliwa ni: " . $course . "\n\n";
                $message .= "Asante,\nUongozi wa Chuo.";
                
                $headers = "From: no-reply@chuochetu.ac.tz\r\n" .
                           "Reply-To: support@chuochetu.ac.tz\r\n" .
                           "X-Mailer: PHP/" . phpversion();

                // Hapa tunajaribu kutuma barua pepe kwa kutumia injini ya PHP
                // Tumeongeza '@' mbele ili kuzuia makosa (errors) kuonekana ubaoni kama localhost haina mtandao
                @mail($to_email, $subject, $message, $headers);
                
                // Ili kuthibitisha mbele ya Lecture (Kwenye Presentation), tunatunza ujumbe kwenye session ili tuuonyeshe
                $_SESSION["email_sent_alert"] = "Barua pepe ya uthibitisho imetumwa kwenda kwa: " . $to_email;
                // ==================================================

                header("location: view_students.php");
                exit();
            }
}
    }
    // Funga muunganisho
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <title>Add students - SMS</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f6f9; padding: 20px; display: flex; justify-content: center; }
        .form-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); width: 450px; }
        h2 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #f1f2f6; padding-bottom: 10px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #555; font-weight: bold; }
        .form-group input[type="text"], .form-group input[type="email"], .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .error-msg { color: #e74c3c; font-size: 13px; margin-top: 5px; }
        .btn-box { display: flex; justify-content: space-between; margin-top: 20px; }
        .btn-save { background: #2ecc71; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .btn-save:hover { background: #27ae60; }
        .btn-cancel { background: #95a5a6; color: white; text-decoration: none; padding: 10px 20px; border-radius: 4px; font-size: 16px; }
        .btn-cancel:hover { background: #7f8c8d; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Register New student</h2>
    <p style="color: #777; font-size: 14px;">fill this form in a correct way as instructed per each fields</p>

    <form action="add_student.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>">
            <div class="error-msg"><?php echo $fullname_err; ?></div>
        </div>

        <div class="form-group">
            <label>Gender</label>
            <select name="gender">
                <option value="">-- select gender--</option>
                <option value="male" <?php if($gender == "male") echo "selected"; ?>> Male</option>
                <option value="female" <?php if($gender == "female") echo "selected"; ?>>Female</option>
            </select>
            <div class="error-msg"><?php echo $gender_err; ?></div>
        </div>

        <div class="form-group">
            <label>Student Course</label>
            <input type="text" name="course" value="<?php echo htmlspecialchars($course); ?>">
            <div class="error-msg"><?php echo $course_err; ?></div>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
            <div class="error-msg"><?php echo $email_err; ?></div>
        </div>

        <div class="form-group">
            <label>Phone Number</label>
           <input type="tel" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
            <div class="error-msg"><?php echo $phone_err; ?></div>
        </div>
        <div class="form-group">
    <label>student Photo</label>
    <input type="file" name="photo" accept="image/*">
</div>
<div style="display: flex; gap: 10px;">
    <div class="form-group" style="flex: 1;">
        <label>web marks (0-100)</label>
        <input type="number" name="web_score" min="0" max="100" required>
    </div>
    <div class="form-group" style="flex: 1;">
        <label>Database  marks</label>
        <input type="number" name="db_score" min="0" max="100" required>
    </div>
    <div class="form-group" style="flex: 1;">
        <label>Network marks</label>
        <input type="number" name="net_score" min="0" max="100" required>
    </div>
</div>
        <div class="btn-box">
            <a href="dashboard.php" class="btn-cancel">Cancel</a>
            <input type="submit" class="btn-save" value="Save">
        </div>
    </form>
</div>

</body>
</html>