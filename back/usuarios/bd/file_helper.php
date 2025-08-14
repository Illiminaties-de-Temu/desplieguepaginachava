<?php
/**
 * Funciones auxiliares para manejo de archivos
 */

/**
 * Obtiene la ruta completa de un archivo basado en la ruta almacenada en BD
 * @param string $rutaBD Ruta como se almacena en la base de datos
 * @param string $directorioBase Directorio base desde donde se ejecuta el script
 * @return string Ruta completa del archivo
 */
function obtenerRutaCompleta($rutaBD, $directorioBase = '../../') {
    $ruta = trim($rutaBD);
    
    // Si la ruta ya incluye 'contenido/', ajustar según el directorio base
    if (strpos($ruta, 'contenido/') === 0) {
        return $directorioBase . $ruta;
    }
    
    // Si no, asumir que está en el directorio contenido/
    return $directorioBase . 'contenido/' . $ruta;
}

/**
 * Elimina un archivo del sistema
 * @param string $rutaArchivo Ruta del archivo a eliminar
 * @return array Resultado de la operación
 */
function eliminarArchivo($rutaArchivo) {
    if (!file_exists($rutaArchivo)) {
        return [
            'success' => false,
            'error' => 'Archivo no encontrado: ' . basename($rutaArchivo)
        ];
    }
    
    if (unlink($rutaArchivo)) {
        return [
            'success' => true,
            'message' => 'Archivo eliminado: ' . basename($rutaArchivo)
        ];
    } else {
        return [
            'success' => false,
            'error' => 'No se pudo eliminar el archivo: ' . basename($rutaArchivo)
        ];
    }
}

/**
 * Elimina múltiples archivos basados en una cadena de rutas separadas por comas
 * @param string $cadenaImagenes Cadena con rutas separadas por comas
 * @param string $directorioBase Directorio base desde donde se ejecuta el script
 * @return array Resultado de la operación con archivos eliminados y errores
 */
function eliminarMultiplesArchivos($cadenaImagenes, $directorioBase = '../../') {
    $eliminados = [];
    $errores = [];
    
    if (empty($cadenaImagenes)) {
        return ['eliminados' => [], 'errores' => []];
    }
    
    $archivos = explode(',', $cadenaImagenes);
    
    foreach ($archivos as $archivo) {
        $archivo = trim($archivo);
        if (empty($archivo)) continue;
        
        $rutaCompleta = obtenerRutaCompleta($archivo, $directorioBase);
        $resultado = eliminarArchivo($rutaCompleta);
        
        if ($resultado['success']) {
            $eliminados[] = $archivo;
        } else {
            $errores[] = $resultado['error'];
        }
    }
    
    return ['eliminados' => $eliminados, 'errores' => $errores];
}

/**
 * Valida que un archivo sea una imagen válida
 * @param string $rutaArchivo Ruta del archivo a validar
 * @return bool True si es una imagen válida
 */
function esImagenValida($rutaArchivo) {
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($rutaArchivo, PATHINFO_EXTENSION));
    
    return in_array($extension, $extensionesPermitidas);
}

/**
 * Obtiene información de un archivo
 * @param string $rutaArchivo Ruta del archivo
 * @return array Información del archivo
 */
function obtenerInfoArchivo($rutaArchivo) {
    if (!file_exists($rutaArchivo)) {
        return [
            'existe' => false,
            'nombre' => basename($rutaArchivo),
            'extension' => pathinfo($rutaArchivo, PATHINFO_EXTENSION),
            'tamaño' => 0
        ];
    }
    
    return [
        'existe' => true,
        'nombre' => basename($rutaArchivo),
        'extension' => pathinfo($rutaArchivo, PATHINFO_EXTENSION),
        'tamaño' => filesize($rutaArchivo),
        'fecha_modificacion' => filemtime($rutaArchivo)
    ];
}

/**
 * Crea un directorio si no existe
 * @param string $directorio Ruta del directorio
 * @return bool True si el directorio existe o se creó correctamente
 */
function crearDirectorioSiNoExiste($directorio) {
    if (!is_dir($directorio)) {
        return mkdir($directorio, 0755, true);
    }
    return true;
}

/**
 * Limpia el nombre de un archivo para que sea seguro
 * @param string $nombreArchivo Nombre del archivo original
 * @return string Nombre del archivo limpio
 */
function limpiarNombreArchivo($nombreArchivo) {
    // Eliminar caracteres especiales y espacios
    $nombreLimpio = preg_replace('/[^a-zA-Z0-9._-]/', '_', $nombreArchivo);
    
    // Eliminar múltiples guiones bajos consecutivos
    $nombreLimpio = preg_replace('/_+/', '_', $nombreLimpio);
    
    // Eliminar guiones bajos al inicio y final
    $nombreLimpio = trim($nombreLimpio, '_');
    
    return $nombreLimpio;
}
?>