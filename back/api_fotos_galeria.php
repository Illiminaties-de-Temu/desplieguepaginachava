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

    // Obtener parámetros de paginación (con valores por defecto)
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $perPage = isset($_GET['per_page']) ? max(1, (int)$_GET['per_page']) : 10;
    $offset = ($page - 1) * $perPage;

    // Consulta para contar el total de noticias
    $countSql = "SELECT COUNT(*) AS total FROM noticias";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute();
    $totalNoticias = $countStmt->fetchColumn();
    $totalPages = ceil($totalNoticias / $perPage);

    // Consulta SQL principal con paginación
    $sql = "SELECT 
                id, 
                IFNULL(Imagenes, '') AS imagenes,
                IFNULL(Destacada, 'no') AS destacada
            FROM noticias
            ORDER BY fecha DESC
            LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    
    if (!$stmt) {
        sendError('Failed to prepare query', 500);
    }

    // Bind parameters para evitar SQL injection
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    if (!$stmt->execute()) {
        sendError('Failed to execute query', 500);
    }

    $noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Procesar resultados
    $response = [
        'success' => true,
        'data' => [],
        'pagination' => [
            'current_page' => $page,
            'per_page' => $perPage,
            'total_items' => (int)$totalNoticias,
            'total_pages' => $totalPages,
            'has_next_page' => $page < $totalPages,
            'has_prev_page' => $page > 1
        ],
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

        // Construir ruta correcta para la primera imagen
        $imagenPrincipal = '';
        if (!empty($imagenes)) {
            $imagenPrincipal = '/back/' . $imagenes[0];
        }

        // Convertir 'si'/'no' a booleano
        $esDestacada = strtolower($noticia['destacada']) === 'si';

        $response['data'][] = [
            'id' => (int)$noticia['id'],
            'destacada' => $esDestacada, // Ahora es un booleano (true/false)
            'imagen' => $imagenPrincipal
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