<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Solo para desarrollo

// Ruta absoluta al config.php
require_once __DIR__ . '/../config.php';

// Manejo de errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('ID de noticia inválido');
    }

    $id = intval($_GET['id']);
    
    $stmt = $pdo->prepare("
        SELECT id, Titulo AS titulo, Contenido AS contenido, 
               Imagenes AS imagenes, fecha AS fecha_publicacion
        FROM noticias 
        WHERE id = ?
    ");
    
    $stmt->execute([$id]);
    $noticia = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$noticia) {
        throw new Exception('Noticia no encontrada');
    }

    // Procesar imágenes
    $noticia['imagenes'] = array_filter(array_map('trim', explode(',', $noticia['imagenes'])));
    $noticia['imagenes'] = array_map(function($img) {
        return '/back/uploads/' . ltrim($img, '/');
    }, $noticia['imagenes']);

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'data' => $noticia
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>