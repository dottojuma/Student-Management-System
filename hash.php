<?php
// Badilisha hapa uandike password unayotaka kuingiza kwenye database, kisha run file hili kwenye server yako ili kupata hashing ya password hiyo na ile hash ndio uingize kwenye database yako
$password_normal = "dotto0727"; 

echo "<h1>code for password hashing</h1>";
echo password_hash($password_normal, PASSWORD_BCRYPT);
?>