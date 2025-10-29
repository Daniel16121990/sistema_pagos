<?php
// Contraseña que estás intentando usar
$password_a_hashear = 'dany.007'; 
$hash_generado = password_hash($password_a_hashear, PASSWORD_DEFAULT);

echo "Contraseña: " . $password_a_hashear . "<br>";
echo "<strong>NUEVO HASH PARA DB: " . $hash_generado . "</strong><br><br>";
echo "Copia el texto en negrita y pégalo directamente en la columna 'password' de tu tabla 'usuarios' para el usuario 'admin'.";
?>