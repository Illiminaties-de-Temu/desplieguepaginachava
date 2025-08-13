<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config.php';

// Verificar si se proporcionó el ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID de noticia no proporcionado']);
    exit;
}

$noticiaId = intval($_GET['id']); // Convertir a entero por seguridad

try {
    $stmt = $pdo->prepare("
        SELECT 
            id,
            Titulo,
            Contenido,
            Imagenes,
            fecha
        FROM noticias
        WHERE id = :id
    ");
    $stmt->bindParam(':id', $noticiaId, PDO::PARAM_INT);
    $stmt->execute();
    $noticia = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($noticia) {
        // Procesar las imágenes si existen
        $imagenes = !empty($noticia['imagenes']) ? explode(',', $noticia['imagenes']) : [];
        
        $noticia['imagenes'] = array_filter(array_map(function($img) {
            $trimmed = trim($img);
            return !empty($trimmed) ? '/back/' . $trimmed : null;
        }, $imagenes));
        
        // Asegurar que el contenido tenga formato HTML seguro
        $noticia['contenido'] = nl2br(htmlspecialchars($noticia['contenido']));
        
        echo json_encode([
            'success' => true, 
            'data' => $noticia
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        echo json_encode([
            'success' => false, 
            'error' => 'Noticia no encontrada'
        ]);
    }
} catch(PDOException $e) {
    error_log('Error en api_noticia_completa.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => 'Error al obtener la noticia'
    ]);
}
?>