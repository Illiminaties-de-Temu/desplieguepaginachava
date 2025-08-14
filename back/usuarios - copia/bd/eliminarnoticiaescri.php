<?php
session_start();

// Verificar si el usuario está logueado y es master
if (!isset($_SESSION['nombreusuario']) || $_SESSION['tipousuario'] !== 'editor') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Acceso no autorizado']);
    exit();
}

// Incluir configuración de base de datos y funciones auxiliares
require_once '../../config/config.php';
require_once 'file_helper.php';

// Función para eliminar noticia de la base de datos
function eliminarNoticiaBD($pdo, $id) {
    try {
        // Primero obtener la información de la noticia para poder eliminar las imágenes
        $stmt = $pdo->prepare("SELECT Imagenes FROM noticias WHERE id = ?");
        $stmt->execute([$id]);
        $noticia = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$noticia) {
            return ['success' => false, 'error' => 'Noticia no encontrada'];
        }
        
        // Eliminar las imágenes físicas usando la función auxiliar
        $resultadoImagenes = eliminarMultiplesArchivos($noticia['Imagenes'], '../../');
        
        // Eliminar la noticia de la base de datos
        $stmt = $pdo->prepare("DELETE FROM noticias WHERE id = ?");
        $resultado = $stmt->execute([$id]);

        // Registrar en bitácora después de eliminar
        $accion = "Eliminación de noticia";
        $descripcion = "Noticia ID " . $id . " eliminada"; // Ajusta $idNoticia según tu variable

        $stmtBitacora = $pdo->prepare("INSERT INTO bitacora (usuario, accion, descripcion, fecha) 
                                    VALUES (?, ?, ?, NOW())");
        $stmtBitacora->execute([
            $_SESSION['nombreusuario'],
            $accion,
            $descripcion
        ]);
        
        if ($resultado) {
            return [
                'success' => true, 
                'message' => 'Noticia eliminada correctamente',
                'imagenes_eliminadas' => $resultadoImagenes['eliminados'],
                'errores_imagenes' => $resultadoImagenes['errores']
            ];
        } else {
            return ['success' => false, 'error' => 'Error al eliminar la noticia de la base de datos'];
        }
        
    } catch (PDOException $e) {
        return ['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()];
    }
}

// Procesar la petición
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Si no hay datos JSON, intentar obtener de $_POST
    if (!$input) {
        $input = $_POST;
    }
    
    // Validar que se recibió el ID
    if (!isset($input['noticia_id']) || empty($input['noticia_id'])) {
        echo json_encode(['success' => false, 'error' => 'ID de noticia no proporcionado']);
        exit();
    }
    
    $noticia_id = intval($input['noticia_id']);
    
    // Validar que el ID sea válido
    if ($noticia_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'ID de noticia inválido']);
        exit();
    }
    
    // Verificar confirmaciones (opcional, ya que se hace en el frontend)
    $confirmaciones = ['confirm1', 'confirm2', 'confirm3'];
    foreach ($confirmaciones as $confirmacion) {
        if (!isset($input[$confirmacion]) || $input[$confirmacion] !== 'true') {
            echo json_encode(['success' => false, 'error' => 'Todas las confirmaciones son requeridas']);
            exit();
        }
    }
    
    // Ejecutar eliminación
    $resultado = eliminarNoticiaBD($pdo, $noticia_id);
    echo json_encode($resultado);
    
} else {
    // Método no permitido
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
?>