<?php
require_once '../../config/config.php';

// Habilitar reporte de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Verificar autenticación y permisos
session_start();
if (!isset($_SESSION['nombreusuario']) || $_SESSION['tipousuario'] !== 'master') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit();
}

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido. Use DELETE']);
    exit();
}

// Obtener el ID del usuario
$userId = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;

// Validar el ID
if (!$userId || $userId < 1) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de usuario no válido']);
    exit();
}

try {
    // Verificar si el usuario existe y no es protegido
    $stmt = $pdo->prepare("SELECT id, nombreusuario FROM usuarios WHERE id = ? AND nombreusuario NOT IN ('GECKCODEX', 'Comunicacion_social')");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado o protegido']);
        exit();
    }

    // Verificar que no sea el usuario actual
    if ($user['nombreusuario'] === $_SESSION['nombreusuario']) {
        http_response_code(400);
        echo json_encode(['error' => 'No puedes eliminarte a ti mismo']);
        exit();
    }

    // Iniciar transacción para integridad de datos
    $pdo->beginTransaction();

    // Primero eliminar dependencias (si las hay)
    // Ejemplo si hay relaciones (ajusta según tu esquema):
    // $stmt = $pdo->prepare("DELETE FROM tabla_relacionada WHERE usuario_id = ?");
    // $stmt->execute([$userId]);

    // Luego eliminar el usuario
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([$userId]);

    if ($stmt->rowCount() > 0) {
        $pdo->commit();
        echo json_encode(['success' => "Usuario {$user['nombreusuario']} eliminado correctamente"]);

        //Insertar en la bitacora
            $accion = "Eliminacion de usuario";
            $descripcion = "Usuario {$user['nombreusuario']} eliminado ";
                $stmtbitacora = $pdo->prepare("INSERT INTO bitacora (usuario,accion,descripcion,fecha)
                VALUES (?,?,?,NOW())");
                $stmtbitacora->execute([$_SESSION['nombreusuario'],
                $accion,$descripcion]);
    
    } else {
        $pdo->rollBack();
        echo json_encode(['error' => 'No se pudo eliminar el usuario']);
    }
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    
    // Registrar error completo para depuración
    error_log("Error al eliminar usuario: " . $e->getMessage());
}