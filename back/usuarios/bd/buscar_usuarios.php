<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

if (!isset($_GET['q'])) {
    echo json_encode(['error' => 'Parámetro de búsqueda no proporcionado']);
    exit();
}

$searchTerm = '%' . $_GET['q'] . '%';
$excludeId = $_GET['exclude'] ?? null;
$currentUser = $_GET['currentUser'] ?? null; // Nuevo parámetro para el usuario actual

try {
    // Construir la consulta SQL excluyendo usuarios protegidos y el usuario actual
    $sql = "SELECT id, nombreusuario, tipousuario 
            FROM usuarios 
            WHERE nombreusuario LIKE :search 
            AND id != (SELECT MIN(id) FROM usuarios)
            AND nombreusuario NOT IN ('GECKCODEX', 'Comunicacion_social')";
    
    $params = [':search' => $searchTerm];
    
    // Excluir usuario específico si se proporciona
    if ($excludeId) {
        $sql .= " AND id != :exclude";
        $params[':exclude'] = $excludeId;
    }
    
    // Excluir usuario actual si se proporciona
    if ($currentUser) {
        $sql .= " AND nombreusuario != :currentUser";
        $params[':currentUser'] = $currentUser;
    }
    
    $sql .= " ORDER BY nombreusuario LIMIT 10";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($results);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
}