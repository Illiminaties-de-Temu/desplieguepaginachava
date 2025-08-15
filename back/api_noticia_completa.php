<?php
error_log("ID recibido en API: " . $_GET['id']);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Permite CORS (temporal para desarrollo)

// Debug (solo para desarrollo)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ruta absoluta al config.php (ajusta según tu estructura)
require_once __DIR__ . '/config/config.php';

try {
    // Validación del ID
    if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
        throw new Exception("ID inválido o no proporcionado", 400);
    }

    $id = intval($_GET['id']);

    // Consulta SQL corregida
    $sql = "SELECT 
                id, 
                Titulo AS titulo, 
                Contenido AS contenido, 
                Imagenes AS imagenes, 
                fecha AS fecha_publicacion 
            FROM noticias 
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $noticia = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$noticia) {
        throw new Exception("Noticia no encontrada", 404);
    }

    // Procesamiento seguro de imágenes
    $noticia['imagenes'] = !empty($noticia['imagenes']) ? 
        array_map(function($img) {
            return '/back/' . trim($img);
        }, explode(',', $noticia['imagenes'])) : 
        [];

    // Respuesta exitosa
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $noticia
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>