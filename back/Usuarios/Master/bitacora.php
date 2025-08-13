<?php
session_start();
// Verificar permisos
if (!isset($_SESSION['nombreusuario']) || $_SESSION['tipousuario'] !== 'master') {
    header("Location: ../Login/Out.php");
    exit();
}

require_once '../../config/config.php';

// Función para purgar registros antiguos
function purgarBitacora($pdo) {
    // Primero contar los registros actuales
    $countStmt = $pdo->query("SELECT COUNT(*) as total FROM bitacora");
    $totalRegistros = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Si hay más de 400 registros, borrar los 50 más antiguos
    if ($totalRegistros > 400) {
        // Obtener el ID del registro número 350 (para dejar 350 después de borrar)
        $stmt = $pdo->query("SELECT id FROM bitacora ORDER BY id ASC LIMIT 1 OFFSET 349");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $idLimite = $result['id'];
            // Borrar registros más antiguos que este ID
            $deleteStmt = $pdo->prepare("DELETE FROM bitacora WHERE id < ?");
            $deleteStmt->execute([$idLimite]);
            
            return $deleteStmt->rowCount(); // Retorna cuántos registros se borraron
        }
    }
    
    return 0;
}

// Ejecutar la purga antes de mostrar los registros
$registrosBorrados = purgarBitacora($pdo);

// Obtener registros de bitácora (los 400 más recientes)
$stmt = $pdo->query("SELECT * FROM bitacora ORDER BY id DESC LIMIT 400");
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registros de Bitácora</title>
    <link rel="stylesheet" href="../estilo.css">
    <style>
        /* Estilos adicionales específicos para la tabla de bitácora */
        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        th {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9em;
            letter-spacing: 0.5px;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        td {
            color: #34495e;
            font-size: 0.9em;
        }
        
        .fecha-col {
            width: 150px;
            white-space: nowrap;
        }
        
        .usuario-col {
            width: 120px;
        }
        
        .accion-col {
            width: 150px;
        }
        
        .descripcion-col {
            max-width: 300px;
            word-wrap: break-word;
        }
        
        .no-records {
            text-align: center;
            color: #7f8c8d;
            padding: 40px;
            font-style: italic;
        }
        
        .search-container {
            margin-bottom: 20px;
        }
        
        .search-input {
            width: 100%;
            max-width: 400px;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 14px;
            background: #f8f9fa;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #3498db;
            background: white;
        }
        
        .record-count {
            color: #7f8c8d;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="panel.php" class="logout-btn">← Regresar</a>
            <h1>📋 Registros de Bitácora</h1>
            <div class="user-info">
                Usuario: <?= htmlspecialchars($_SESSION['nombreusuario']) ?>
            </div>
        </div>
        
        <div class="content">
            <div class="form-section">
                <h2>Historial de Actividades del Sistema</h2>
                
                <div class="search-container">
                    <input type="text" id="searchInput" class="search-input" placeholder="🔍 Buscar en registros...">
                </div>
                
                <div class="record-count">
                    Mostrando los últimos <?= count($registros) ?> registros
                </div>
                
                <div class="table-container">
                    <?php if (empty($registros)): ?>
                        <div class="no-records">
                            No hay registros de bitácora disponibles.
                        </div>
                    <?php else: ?>
                        <table id="bitacoraTable">
                            <thead>
                                <tr>
                                    <th class="id-col">Id</th>
                                    <th class="fecha-col">Fecha</th>
                                    <th class="usuario-col">Usuario</th>
                                    <th class="accion-col">Acción</th>
                                    <th class="descripcion-col">Descripción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($registros as $registro): ?>
                                <tr>
                                    <td class="id-col"><?= htmlspecialchars($registro['id']) ?></td>
                                    <td class="fecha-col"><?= htmlspecialchars($registro['fecha']) ?></td>
                                    <td class="usuario-col"><?= htmlspecialchars($registro['usuario']) ?></td>
                                    <td class="accion-col"><?= htmlspecialchars($registro['accion']) ?></td>
                                    <td class="descripcion-col"><?= htmlspecialchars($registro['descripcion']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Funcionalidad de búsqueda en tiempo real
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const table = document.getElementById('bitacoraTable');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < cells.length; j++) {
                    if (cells[j].textContent.toLowerCase().includes(searchTerm)) {
                        found = true;
                        break;
                    }
                }
                
                row.style.display = found ? '' : 'none';
            }
        });
    </script>
</body>
</html>