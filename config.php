<?php
// Vigezo vya kuunganishia Database ya XAMPP
$servername = "localhost";
$username = "root";
$password = ""; // XAMPP kwa kawaida haina password ya MySQL
$dbname = "web"; // Jina la database tuliyotengeneza

// Kutengeneza muunganisho (Connection) kwa kutumia mfumo wa MySQLi
$conn = new mysqli($servername, $username, $password, $dbname);

// Angalia kama muunganisho umefeli na kutoa taarifa
if ($conn->connect_error) {
    die("Muunganisho umefeli: " . $conn->connect_error);
}

// Hii inasaidia database isome lugha na herufi zote vizuri (UTF-8)
$conn->set_charset("utf8mb4");
?>