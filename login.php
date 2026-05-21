<?php
// Anzisha session
session_start();

// Kama admin ameshajilogin tayari, akija hapa mpeleke dashboard moja kwa moja
if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true){
    header("location: dashboard.php");
    exit;
}

// Leta faili la muunganisho wa database
require_once "config.php";

$username = $password = "";
$username_err = $password_err = $login_err = "";

// Angalia kama fomu imetumwa (Submitted)
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // 1. Validations (Hakikisha fomu haijatumwa ikiwa wazi)
    if(empty(trim($_POST["username"]))){
        $username_err = "Tafadhali weka username.";
    } else {
        $username = trim($_POST["username"]);
    }
    
    if(empty(trim($_POST["password"]))){
        $password_err = "Tafadhali weka password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // 2. Kama hakuna makosa kwenye fomu, fanya uhakiki kwenye database
    if(empty($username_err) && empty($password_err)){
        // Hapa tunatumia Prepared Statement kwa usalama dhidi ya SQL Injection
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("s", $param_username);
            $param_username = $username;
            
            if($stmt->execute()){
                $stmt->store_result();
                
                // Angalia kama username ipo, kama ipo hakiki password
                if($stmt->num_rows == 1){                    
                    $stmt->bind_result($id, $username, $hashed_password);
                    if($stmt->fetch()){
                        // password_verify inalinganisha password ya kawaida na ile ya mdondoko wa hashi (hash)
                        if(password_verify($password, $hashed_password)){
                            // Login imefanikiwa! Anzisha session mpya
                            $_SESSION["admin_logged_in"] = true;
                            $_SESSION["admin_id"] = $id;
                            $_SESSION["admin_username"] = $username;                            
                            
                            // Mpeleke admin kwenye dashboard
                            header("location: dashboard.php");
                            exit;
                        } else {
                            $login_err = "Username au Password siyo sahihi.";
                        }
                    }
                } else {
                    $login_err = "Username au Password siyo sahihi.";
                }
            } else {
                echo "Kuna hitilafu imetokea, tafadhali jaribu tena baadae.";
            }

            // Funga statement
            $stmt->close();
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
    <title>Login - Admin</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .wrapper { width: 350px; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #555; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .error-msg { color: red; font-size: 13px; margin-top: 5px; }
        .btn-submit { width: 100%; background: #2ecc71; color: white; border: none; padding: 10px; font-size: 16px; border-radius: 4px; cursor: pointer; }
        .btn-submit:hover { background: #27ae60; }
    </style>
</head>
<body>

<div class="wrapper">
    <h2>Admin Login</h2>
    <p style="text-align: center; color: #777;">Fill in your credentials to log in.</p>

    <?php 
    if(!empty($login_err)){
        echo '<div class="error-msg" style="text-align:center; margin-bottom:10px; font-weight:bold;">' . $login_err . '</div>';
    }        
    ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" value="<?php echo $username; ?>">
            <div class="error-msg"><?php echo $username_err; ?></div>
        </div>    
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password">
            <div class="error-msg"><?php echo $password_err; ?></div>
        </div>
        <div class="form-group">
            <input type="submit" class="btn-submit" value="LOGIN">
        </div>
    </form>
</div>

</body>
</html>