<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

if (!isset($_GET['username']) || strlen(trim($_GET['username'])) < 3) {
    exit(json_encode(['skip' => true]));
}

$username = trim($_GET['username']);

if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    exit(json_encode([
        'existe' => true,
        'mensaje' => 'Formato inválido (solo letras, números y _)'
    ]));
}

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE nombreusuario = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    $existe = $stmt->fetchColumn() > 0;
    
    echo json_encode([
        'existe' => $existe,
        'mensaje' => $existe ? '✖ Nombre de usuario no disponible' : '✔ Nombre de usuario disponible'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Error de base de datos',
        'detalle' => $e->getMessage()
    ]);
}