<?php
// purga_noticias.php

// Configuración de la base de datos
require_once '../../config/config.php';

/**
 * Función para purgar noticias antiguas y sus imágenes asociadas
 * @param PDO $pdo Conexión a la base de datos
 */
function purgarNoticiasAntiguas($pdo) {
    // Contar el número total de noticias
    $countQuery = $pdo->query("SELECT COUNT(*) as total FROM noticias");
    $totalNoticias = $countQuery->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Si hay más de 300 noticias, borrar las 100 más antiguas
    if ($totalNoticias >= 300) {
        // Obtener las 100 noticias más antiguas
        $query = $pdo->query("SELECT id, Imagenes FROM noticias ORDER BY fecha ASC LIMIT 100");
        $noticiasAntiguas = $query->fetchAll(PDO::FETCH_ASSOC);
        
        // Eliminar las imágenes asociadas
        foreach ($noticiasAntiguas as $noticia) {
            if (!empty($noticia['Imagenes'])) {
                $imagenes = explode(',', $noticia['Imagenes']);
                foreach ($imagenes as $imagen) {
                    $rutaImagen = '../../' . $imagen; // Ajusta según tu estructura
                    if (file_exists($rutaImagen)) {
                        unlink($rutaImagen);
                    }
                }
            }
        }
        
        // Eliminar las noticias de la base de datos
        $ids = array_column($noticiasAntiguas, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $deleteQuery = $pdo->prepare("DELETE FROM noticias WHERE id IN ($placeholders)");
        $deleteQuery->execute($ids);
        
        // Registrar en bitácora
        registrarBitacora($pdo, "Purga de noticias", "Se eliminaron 100 noticias antiguas por mantenimiento del servidor");
    }
}

/**
 * Función para eliminar imágenes huérfanas (no asociadas a ninguna noticia)
 * @param PDO $pdo Conexión a la base de datos
 * @param string $directorioImagenes Ruta al directorio donde se almacenan las imágenes
 */
function eliminarImagenesHuerfanas($pdo, $directorioImagenes = '../../contenido') {
    // Obtener todas las imágenes referenciadas en la base de datos
    $query = $pdo->query("SELECT Imagenes FROM noticias");
    $imagenesBD = [];
    
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['Imagenes'])) {
            $imagenesNoticia = explode(',', $row['Imagenes']);
            foreach ($imagenesNoticia as $img) {
                $imagenesBD[] = basename($img); // Solo el nombre del archivo
            }
        }
    }
    
    // Obtener todas las imágenes físicas en el directorio
    $imagenesFisicas = [];
    if (is_dir($directorioImagenes)) {
        $archivos = scandir($directorioImagenes);
        foreach ($archivos as $archivo) {
            if ($archivo !== '.' && $archivo !== '..' && is_file($directorioImagenes . $archivo)) {
                $imagenesFisicas[] = $archivo;
            }
        }
    }
    
    // Encontrar imágenes huérfanas (presentes en el directorio pero no en la BD)
    $imagenesHuerfanas = array_diff($imagenesFisicas, $imagenesBD);
    
    // Eliminar las imágenes huérfanas
    $contador = 0;
    foreach ($imagenesHuerfanas as $imagen) {
        $rutaCompleta = $directorioImagenes . $imagen;
        if (file_exists($rutaCompleta)) {
            unlink($rutaCompleta);
            $contador++;
        }
    }
    
    if ($contador > 0) {
        registrarBitacora($pdo, "Purga de imágenes", "Se eliminaron $contador imágenes huérfanas no asociadas a noticias");
    }
    
    return $contador;
}

/**
 * Función auxiliar para registrar en bitácora
 */
function registrarBitacora($pdo, $accion, $descripcion) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $usuario = isset($_SESSION['nombreusuario']) ? $_SESSION['nombreusuario'] : 'Sistema';
    
    $stmt = $pdo->prepare("INSERT INTO bitacora (usuario, accion, descripcion, fecha)
                          VALUES (?, ?, ?, NOW())");
    $stmt->execute([$usuario, $accion, $descripcion]);
}

/**
 * Función para purgar registros antiguos de la bitácora
 * @param PDO $pdo Conexión a la base de datos
 */

function registrarBitacorapurga($pdo, $accion, $descripcion) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $usuario = isset($_SESSION['nombreusuario']) ? $_SESSION['nombreusuario'] : 'Sistema';
    
    $stmt = $pdo->prepare("INSERT INTO bitacora (usuario, accion, descripcion, fecha)
                          VALUES (?, ?, ?, NOW())");
    $stmt->execute([$usuario, $accion, $descripcion]);
}


function purgararchivosBitacora($pdo) {
    try {
        // Contar el número total de registros en la bitácora
        $countQuery = $pdo->query("SELECT COUNT(*) as total FROM bitacora");
        $totalRegistros = $countQuery->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Si hay más de 1000 registros, borrar los 500 más antiguos
        if ($totalRegistros >= 1000) {
            // Obtener los IDs de los 500 registros más antiguos
            $query = $pdo->query("SELECT id FROM bitacora ORDER BY fecha ASC LIMIT 500");
            $registrosAntiguos = $query->fetchAll(PDO::FETCH_ASSOC);
            
            // Eliminar los registros de la base de datos
            $ids = array_column($registrosAntiguos, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            
            $deleteQuery = $pdo->prepare("DELETE FROM bitacora WHERE id IN ($placeholders)");
            $deleteQuery->execute($ids);
            
            // Registrar en bitácora esta acción (si la bitácora no está llena)
            if ($totalRegistros - 500 < 900) { // Solo registrar si no estamos cerca del límite otra vez
                registrarBitacorapurga($pdo, "Purga de bitácora", "Se eliminaron 500 registros antiguos de la bitácora por mantenimiento");
            }
            
            return count($ids); // Devolver el número de registros eliminados
        }
        
        return 0; // No se eliminó nada
    } catch (PDOException $e) {
        // Registrar error en caso de fallo
        error_log("Error al purgar bitácora: " . $e->getMessage());
        return false;
    }
}

/**
 * Función auxiliar para registrar en bitácora (similar a la del archivo anterior)
 */

?>