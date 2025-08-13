<?php
session_start();
require_once '../../config/config.php';

// Verificar si ya está logueado (evitar re-login)
if (isset($_SESSION['nombreusuario'])) {
    redirigirSegunTipoUsuario($_SESSION['tipousuario']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombreusuario = trim($_POST["username"] ?? '');
    $contraseña = $_POST['password'] ?? '';

    // Validación básica
    if (empty($nombreusuario) || empty($contraseña)) {
        header("Location: ../Iniciodesesion.php?error=3"); // Campos vacíos
        exit();
    }

    $stmt = $pdo->prepare("SELECT id, nombreusuario, tipousuario, contrasena FROM usuarios WHERE nombreusuario = :username");
    $stmt->bindParam(':username', $nombreusuario);  
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $login_exitoso = false;
        
        // Verificación de contraseña
        if (password_verify($contraseña, $usuario['contrasena'])) {
            $login_exitoso = true;
        } elseif ($contraseña === $usuario['contrasena']) { // Para contraseñas en texto plano (migración)
            $login_exitoso = true;
            // Actualizar a hash
            $hash = password_hash($contraseña, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE usuarios SET contrasena = ? WHERE id = ?")
               ->execute([$hash, $usuario['id']]);
        }

        if ($login_exitoso) {
            $_SESSION['id'] = $usuario['id'];
            $_SESSION['nombreusuario'] = $usuario['nombreusuario'];
            $_SESSION['tipousuario'] = $usuario['tipousuario'];
            
            // Redirección segura
            redirigirSegunTipoUsuario($usuario['tipousuario']);
            exit();
        }
    }
    
    // Si llega aquí es porque falló el login
    header("Location: ../Iniciodesesion.php?error=1");
    exit();
}

// Función para redirección centralizada
function redirigirSegunTipoUsuario($tipoUsuario) {
    $baseUrl = '../';
    
    switch ($tipoUsuario) {
        case 'master':
            header("Location: {$baseUrl}Master/panel.php");
            break;
        case 'editor':
            header("Location: {$baseUrl}Escritor/panel.php"); // Asumiendo que existe
            break;
        default:
            header("Location: {$baseUrl}Iniciodesesion.php?error=2"); // Tipo desconocido
    }
}
?>