<?php
// Anzisha session na ukague usalama
session_start();
if(!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true){
    header("location: login.php");
    exit;
}

// Leta faili la database
require_once "config.php";

// Angalia kama ID ya mwanafunzi imetumwa kwenye URL
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    
    // Kutumia Prepared Statement kwa ajili ya usalama wa DELETE Operation
    $sql = "DELETE FROM students WHERE id = ?";
    
    if($stmt = $conn->prepare($sql)){
        // "i" inamaanisha ID ni Integer (Namba kamili)
        $stmt->bind_param("i", $param_id);
        
        $param_id = trim($_GET["id"]);
        
        // Tekeleza amri ya kufuta
        if($stmt->execute()){
            // Ikishafuta, mrudishe admin kwenye dashboard ili aone mabadiliko
            header("location: view_students.php");
            exit();
        } else {
            echo "Kuna hitilafu ilitokea, mwanafunzi hajafutwa.";
        }
        
        // Funga statement
        $stmt->close();
    }
} else {
    // Kama mtu amefungua faili hili bila kuweka ID ya mwanafunzi, mrudishe dashboard
    header("location: view_students.php");
    exit();
}

// Funga muunganisho wa database
$conn->close();
?>