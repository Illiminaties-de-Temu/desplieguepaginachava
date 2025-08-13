<?php
session_start();

require_once '../../config/config.php';

// Verificar permisos y método POST
if (!isset($_SESSION['nombreusuario']) || $_SESSION['tipousuario'] !== 'master' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../login/out.php");
    exit();
}

// Recuperar y validar datos
$id_usuario = $_POST['id_usuario'] ?? '';
$nombreusuario = trim($_POST['nombreusuario'] ?? '');
$tipousuario = $_POST['tipousuario'] ?? '';
$nueva_contrasena = $_POST['nueva_contrasena'] ?? '';
$confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';

// Validaciones básicas
if (empty($id_usuario) || empty($nombreusuario) || empty($tipousuario)) {
    header("Location: ../master/editarusuario.php?error=" . urlencode('Todos los campos son obligatorios'));
    exit();
}

if (!empty($nueva_contrasena) && $nueva_contrasena !== $confirmar_contrasena) {
    header("Location: ../master/editarusuario.php?error=" . urlencode('Las contraseñas no coinciden'));
    exit();
}

try {
    // 1. Obtener datos ANTIGUOS del usuario
    $stmt = $pdo->prepare("SELECT nombreusuario, tipousuario FROM usuarios WHERE id = ?");
    $stmt->execute([$id_usuario]);
    $datos_antiguos = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$datos_antiguos) {
        header("Location: ../master/editarusuario.php?error=" . urlencode('Usuario no encontrado'));
        exit();
    }
    
    // Verificar usuarios protegidos
    $usuarios_protegidos = ['GECKCODEX', 'Comunicacion_social', $_SESSION['nombreusuario']];
    if (in_array($datos_antiguos['nombreusuario'], $usuarios_protegidos)) {
        header("Location: ../master/editarusuario.php?error=" . urlencode('No puedes editar este usuario protegido'));
        exit();
    }
    
    // Verificar si el nuevo nombre de usuario ya existe (excluyendo al actual)
    if ($datos_antiguos['nombreusuario'] !== $nombreusuario) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE nombreusuario = ? AND id != ?");
        $stmt->execute([$nombreusuario, $id_usuario]);
        if ($stmt->fetchColumn() > 0) {
            header("Location: ../master/editarusuario.php?error=" . urlencode('El nombre de usuario ya está en uso'));
            exit();
        }
    }
    
    // 2. Realizar la actualización
    $cambios = [];
    if (!empty($nueva_contrasena)) {
        $hash_contrasena = password_hash($nueva_contrasena, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE usuarios SET nombreusuario = ?, tipousuario = ?, contrasena = ? WHERE id = ?");
        $stmt->execute([$nombreusuario, $tipousuario, $hash_contrasena, $id_usuario]);
        $cambios[] = "contraseña actualizada";
    } else {
        $stmt = $pdo->prepare("UPDATE usuarios SET nombreusuario = ?, tipousuario = ? WHERE id = ?");
        $stmt->execute([$nombreusuario, $tipousuario, $id_usuario]);
    }
    
    // 3. Detectar cambios específicos
    if ($datos_antiguos['nombreusuario'] !== $nombreusuario) {
        $cambios[] = "nombre cambiado de '{$datos_antiguos['nombreusuario']}' a '{$nombreusuario}'";
    }
    
    if ($datos_antiguos['tipousuario'] !== $tipousuario) {
        $cambios[] = "tipo de usuario cambiado de '{$datos_antiguos['tipousuario']}' a '{$tipousuario}'";
    }
    
    // 4. Registrar en bitácora solo si hubo cambios
    if (!empty($cambios)) {
        $accion = "Actualización de usuario";
        $descripcion = "({$datos_antiguos['nombreusuario']}): " . implode(', ', $cambios);
        
        $stmt_bitacora = $pdo->prepare("INSERT INTO bitacora (usuario, accion, descripcion, fecha) 
                                      VALUES (?, ?, ?, NOW())");
        $stmt_bitacora->execute([
            $_SESSION['nombreusuario'],
            $accion,
            $descripcion
        ]);
    }
    
    header("Location: ../master/editarusuario.php?success=" . urlencode('Usuario actualizado correctamente'));
    exit();
    
} catch (PDOException $e) {
    header("Location: ../master/editarusuario.php?error=" . urlencode('Error al actualizar usuario: ' . $e->getMessage()));
    exit();
}