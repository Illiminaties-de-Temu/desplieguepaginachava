<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once '../../config/config.php';

// Verificar permisos
if (!isset($_SESSION['nombreusuario']) || ($_SESSION['tipousuario'] !== 'master' && $_SESSION['tipousuario'] !== 'editor')) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Configuración básica
        $rutaBase = '../../';
        $carpetaImagenes = 'contenido/';
        $rutaImagenesFisica = $rutaBase . $carpetaImagenes;
        
        // Procesar imágenes si existen
        $rutasNuevasImagenes = [];
        if (!empty($_FILES['nuevas_imagenes'])) {
            if (!file_exists($rutaImagenesFisica)) {
                mkdir($rutaImagenesFisica, 0755, true);
            }
            
            foreach ($_FILES['nuevas_imagenes']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['nuevas_imagenes']['error'][$key] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['nuevas_imagenes']['name'][$key], PATHINFO_EXTENSION);
                    $newName = uniqid() . '_' . $key . '.' . $ext;
                    $destino = $rutaImagenesFisica . $newName;
                    
                    if (move_uploaded_file($tmp_name, $destino)) {
                        $rutasNuevasImagenes[] = $carpetaImagenes . $newName;
                    }
                }
            }
        }

        // Obtener datos básicos
        $id = $_POST['id'] ?? null;
        $titulo = $_POST['titulo'] ?? null;
        $contenido = $_POST['contenido'] ?? '';
        $fecha = $_POST['fecha'] ?? date('Y-m-d');
        $imagenesExistentes = $_POST['imagenes_existentes'] ?? '';
        $destacada = isset($_POST['destacada']) ? 'si' : 'no'; // Nuevo campo

        // Validar campos obligatorios
        if (empty($id) || empty($contenido) || empty($fecha)) {
            throw new Exception('ID, contenido y fecha son obligatorios');
        }

        // Preparar imágenes finales
        $imagenesFinal = !empty($rutasNuevasImagenes) 
            ? implode(',', array_filter(array_merge(explode(',', $imagenesExistentes), $rutasNuevasImagenes)))
            : $imagenesExistentes;

        // Consulta SQL optimizada con el nuevo campo
        $sql = "UPDATE noticias SET 
                Contenido = ?,
                fecha = ?,
                Imagenes = ?,
                Titulo = COALESCE(?, Titulo),
                Destacada = ?
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $resultado = $stmt->execute([$contenido, $fecha, $imagenesFinal, $titulo, $destacada, $id]);

        if (!$resultado) {
            throw new Exception('Error al actualizar en la base de datos');
        }

        // Bitácora
        $stmtBitacora = $pdo->prepare("INSERT INTO bitacora (usuario, accion, descripcion, fecha) 
                                     VALUES (?, ?, ?, NOW())");
        $stmtBitacora->execute([
            $_SESSION['nombreusuario'],
            'Edición de noticia',
            'Noticia ID ' . $id . ' actualizada' . ($destacada === 'si' ? ' (DESTACADA)' : '')
        ]);

        // Respuesta exitosa
        echo json_encode([
            'success' => true,
            'message' => 'Noticia actualizada',
            'nuevas_imagenes' => $rutasNuevasImagenes,
            'destacada' => $destacada
        ]);

    } else {
        throw new Exception('Método no permitido');
    }
} catch (Exception $e) {
    // Limpiar imágenes subidas si hubo error
    if (!empty($rutasNuevasImagenes)) {
        foreach ($rutasNuevasImagenes as $img) {
            @unlink($rutaBase . $img);
        }
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>