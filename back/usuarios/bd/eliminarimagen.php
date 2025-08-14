<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Incluir el helper para manejo de archivos
require_once 'file_helper.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $rutaImagen = $input['rutaImagen'] ?? '';
        
        if (empty($rutaImagen)) {
            echo json_encode([
                'success' => false,
                'message' => 'Ruta de imagen no proporcionada'
            ]);
            exit;
        }
        
        // Usar la función del helper para eliminar el archivo
        $rutaCompleta = obtenerRutaCompleta($rutaImagen, '../../');
        $resultado = eliminarArchivo($rutaCompleta);
        
        echo json_encode([
            'success' => $resultado['success'],
            'message' => $resultado['success'] ? $resultado['message'] : $resultado['error']
        ]);
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Método no permitido'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>