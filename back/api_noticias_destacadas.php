<?php
// Headers iniciales para evitar errores
if (headers_sent()) {
    die('Headers already sent');
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('X-Content-Type-Options: nosniff');

// Incluir configuración
require_once __DIR__ . '/config/config.php';

// Función para manejar errores
function sendError($message, $code = 500) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $message,
        'timestamp' => time()
    ]);
    exit;
}

try {
    // Validar conexión a la base de datos
    if (!$pdo) {
        sendError('Database connection failed', 500);
    }

    // Consulta SQL segura adaptada a tu estructura
    $sql = "SELECT 
                id, 
                IFNULL(Titulo, '') AS titulo, 
                IFNULL(Contenido, '') AS contenido, 
                IFNULL(Imagenes, '') AS imagenes, 
                IFNULL(fecha, '') AS fecha
            FROM noticias
            WHERE destacada = 'si' OR destacada = 1
            ORDER BY fecha DESC
            LIMIT 10";

    $stmt = $pdo->prepare($sql);
    
    if (!$stmt) {
        sendError('Failed to prepare query', 500);
    }

    if (!$stmt->execute()) {
        sendError('Failed to execute query', 500);
    }

    $noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Procesar resultados
    $response = [
        'success' => true,
        'data' => [],
        'meta' => [
            'count' => count($noticias),
            'generated_at' => date('c')
        ]
    ];

    foreach ($noticias as $noticia) {
        // Procesar imágenes (rutas separadas por comas)
        $imagenes = array_filter(
            array_map('trim', 
                explode(',', $noticia['imagenes'])
            ),
            function($img) { return !empty($img); }
        );

        // Construir ruta correcta para imágenes
        $imagenPrincipal = '';
        if (!empty($imagenes)) {
            $imagenPrincipal = '/back/' . $imagenes[0];
        }

        // Limitar contenido a 10 palabras
        $contenidoBreve = implode(' ', 
            array_slice(
                preg_split('/\s+/', strip_tags($noticia['contenido'])), 
                0, 10
            )
        );

        $response['data'][] = [
            'id' => (int)$noticia['id'],
            'titulo' => $noticia['titulo'],
            'contenido' => $contenidoBreve . (str_word_count($noticia['contenido']) > 10 ? '...' : ''),
            'imagen' => $imagenPrincipal,
            'fecha' => date('d M Y', strtotime($noticia['fecha'])),
            'autor' => 'Redacción', // Valor por defecto ya que no existe en tu estructura
            'imagenes' => array_map(function($img) {
                return '/back/' . $img;
            }, $imagenes)
        ];
    }

    // Enviar respuesta
    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    error_log('PDO Exception: ' . $e->getMessage());
    sendError('Database error occurred', 500);
} catch (Exception $e) {
    error_log('General Exception: ' . $e->getMessage());
    sendError('An unexpected error occurred', 500);
}