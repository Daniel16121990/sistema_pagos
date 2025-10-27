<?php
// 1. Iniciar sesión para almacenar el estado del usuario
session_start();

// 2. Incluir el archivo de conexión
// ESTA RUTA ES CORRECTA BASADA EN TU ESTRUCTURA:
require_once './php/conexion.php'; 

// 3. Verificar si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validar que los campos no estén vacíos
    if (empty($username) || empty($password)) {
        header('Location: index.php?error=1');
        exit;
    }

    try {
        // ... (Tu código de consulta SQL y verificación de password_verify) ...
        $stmt = $pdo->prepare('SELECT id, username, password FROM usuarios WHERE username = :username');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        // 5. Verificar si el usuario existe y si la contraseña es correcta
        if ($user && password_verify($password, $user['password'])) {
            
            // 6. Autenticación exitosa: Crear variables de sesión
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // 7. Redirigir al dashboard (principal.php)
            // ¡CORRECCIÓN CLAVE! DEBE SER .PHP
            header('Location: principal.php'); 
            exit;
            
        } else {
            // 8. Autenticación fallida: Redirigir al login con mensaje de error
            header('Location: index.php?error=1');
            exit;
        }

    } catch (PDOException $e) {
        // IMPORTANTE: Para depurar, cambia 'die' a un mensaje que te muestre el error exacto
        die('Error en la base de datos (PDO): ' . $e->getMessage());
    }
} else {
    // Si se accede directamente a validar.php sin POST, redirigir al login
    header('Location: index.php');
    exit;
}
?>