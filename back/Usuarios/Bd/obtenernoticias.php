<?php

// Incluir la configuración de la base de datos
require_once '../../config/config.php';

/**
 * Función para obtener una noticia específica por ID
 * @param PDO $pdo - Conexión a la base de datos
 * @param int $id - ID de la noticia
 * @return array|null - Datos de la noticia o null si no existe
 */
function obtenerNoticiaPorId($pdo, $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM noticias WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener noticia por ID: " . $e->getMessage());
        return null;
    }
}

/**
 * Función para obtener todas las noticias
 * @param PDO $pdo - Conexión a la base de datos
 * @return array - Array con todas las noticias
 */
function obtenerTodasNoticias($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT id, Titulo, fecha, Imagenes FROM noticias ORDER BY fecha DESC, id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener todas las noticias: " . $e->getMessage());
        return [];
    }
}

// Solo ejecutar el código AJAX/JSON si se accede directamente al archivo
// No si se incluye desde otro archivo
if (basename($_SERVER['PHP_SELF']) === 'obtenernoticias.php') {
    try {
        // Verificar si se solicita una noticia específica por ID
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = $pdo->prepare("SELECT * FROM noticias WHERE id = ?");
            $stmt->execute([$id]);
            $noticia = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($noticia) {
                header('Content-Type: application/json');
                echo json_encode($noticia);
            } else {
                header('Content-Type: application/json');
                http_response_code(404);
                echo json_encode(['error' => 'Noticia no encontrada']);
            }
        } 
        // Si no se especifica ID, devolver todas las noticias
        else {
            $stmt = $pdo->prepare("SELECT id, Titulo, fecha FROM noticias ORDER BY fecha DESC, id DESC");
            $stmt->execute();
            $noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode($noticias);
        }
        
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    }
}
?>