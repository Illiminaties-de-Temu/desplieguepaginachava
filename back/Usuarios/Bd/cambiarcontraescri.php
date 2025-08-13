<?php
session_start();

// Incluir el archivo de configuración de base de datos
require_once '../../config/config.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['nombreusuario']) || $_SESSION['tipousuario'] !== 'editor') {
    header("Location: ../Iniciodesesion.php");
    exit();
}

// Verificar si es una petición POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contrasena_actual = $_POST['contrasena_actual'] ?? '';
    $nueva_contrasena = $_POST['nueva_contrasena'] ?? '';
    $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';
    
    $errores = [];
    
    // Validaciones básicas
    if (empty($contrasena_actual)) {
        $errores[] = "La contraseña actual es requerida";
    }
    
    if (empty($nueva_contrasena)) {
        $errores[] = "La nueva contraseña es requerida";
    }
    
    if (empty($confirmar_contrasena)) {
        $errores[] = "La confirmación de contraseña es requerida";
    }
    
    if ($nueva_contrasena !== $confirmar_contrasena) {
        $errores[] = "Las contraseñas no coinciden";
    }
    
    // Validar requisitos de contraseña
    if (strlen($nueva_contrasena) < 8) {
        $errores[] = "La contraseña debe tener al menos 8 caracteres";
    }
    
    if (!preg_match('/[A-Z]/', $nueva_contrasena)) {
        $errores[] = "La contraseña debe contener al menos una letra mayúscula";
    }
    
    if (!preg_match('/[a-z]/', $nueva_contrasena)) {
        $errores[] = "La contraseña debe contener al menos una letra minúscula";
    }
    
    if (!preg_match('/\d/', $nueva_contrasena)) {
        $errores[] = "La contraseña debe contener al menos un número";
    }
    
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $nueva_contrasena)) {
        $errores[] = "La contraseña debe contener al menos un carácter especial";
    }
    
    if (empty($errores)) {
        try {
            // Obtener datos del usuario actual por nombre de usuario
            $stmt = $pdo->prepare("SELECT id, contrasena FROM usuarios WHERE nombreusuario = ?");
            $stmt->execute([$_SESSION['nombreusuario']]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario) {
                $errores[] = "Usuario no encontrado";
            } else {
                // Verificar contraseña actual (verificar si está hasheada o en texto plano)
                $contrasena_valida = false;
                
                // Intentar verificar como hash primero
                if (password_verify($contrasena_actual, $usuario['contrasena'])) {
                    $contrasena_valida = true;
                } 
                // Si no funciona como hash, verificar como texto plano (para compatibilidad)
                elseif ($contrasena_actual === $usuario['contrasena']) {
                    $contrasena_valida = true;
                }
                
                if (!$contrasena_valida) {
                    $errores[] = "La contraseña actual es incorrecta";
                } else {
                    // Hashear la nueva contraseña
                    $nueva_contrasena_hash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
                    
                    // Actualizar contraseña en la base de datos (siempre hasheada)
                    $stmt = $pdo->prepare("UPDATE usuarios SET contrasena = ? WHERE id = ?");
                    $resultado = $stmt->execute([$nueva_contrasena_hash, $usuario['id']]);
                    
                    if ($resultado) {
                        $_SESSION['mensaje_exito'] = "Contraseña cambiada exitosamente";
                        header("Location: ../Escritor/cambiarcontraescri.php?exito=1");
                        exit();
                    } else {
                        $errores[] = "Error al actualizar la contraseña";
                    }
                }
            }
            
        } catch (PDOException $e) {
            $errores[] = "Error de base de datos: " . $e->getMessage();
        }
    }
    
    // Si hay errores, redirigir con mensaje de error
    if (!empty($errores)) {
        $_SESSION['errores'] = $errores;
        header("Location: ../Escritor/cambiarcontraescri.php?error=1");
        exit();
    }
    
} else {
    // Si no es POST, redirigir al formulario
    header("Location: ../Escritor/cambiarcontraescri.php");
    exit();
}
?>