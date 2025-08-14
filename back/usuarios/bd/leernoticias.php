<?php

// Incluir la configuración de la base de datos
require_once '../../config/config.php';

try {
    
    // Verificar si se solicita una noticia específica por ID
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $pdo->prepare("SELECT * FROM noticias WHERE id = ?");
        $stmt->execute([$id]);
        $noticia = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($noticia) {
            echo json_encode($noticia);
        } else {
            echo json_encode(['error' => 'Noticia no encontrada']);
        }
    } 
    // Si no se especifica ID, devolver todas las noticias
    else {
        $stmt = $pdo->prepare("SELECT id, Titulo, fecha FROM noticias ORDER BY fecha DESC, id DESC");
        $stmt->execute();
        $noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($noticias);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
}
?>