<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID de noticia no proporcionado']);
    exit;
}

$noticiaId = $_GET['id'];

try {
    $stmt = $pdo->prepare("
        SELECT 
            id,
            Titulo,
            Contenido,
            Imagenes,  // Campo con todas las imágenes
            fecha,
        FROM noticias
        WHERE id = :id
    ");
    $stmt->bindParam(':id', $noticiaId, PDO::PARAM_INT);
    $stmt->execute();
    $noticia = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($noticia) {
        // Procesar las imágenes
        $imagenes = explode(',', $noticia['imagenes']);
        $noticia['imagenes'] = array_map(function($img) {
            return '/back/' . trim($img);
        }, $imagenes);
        
        echo json_encode(['success' => true, 'data' => $noticia]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Noticia no encontrada']);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>