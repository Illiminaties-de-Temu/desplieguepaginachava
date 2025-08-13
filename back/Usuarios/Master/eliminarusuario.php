<?php
session_start();

// Verificar si el usuario está logueado y es master
if (!isset($_SESSION['nombreusuario']) || $_SESSION['tipousuario'] !== 'master') {
    header("Location: ../login/out.php");
    exit();
}

require_once '../../config/config.php';

// Variables para mensajes
$mensaje_exito = isset($_GET['success']) ? urldecode($_GET['success']) : '';
$mensaje_error = isset($_GET['error']) ? urldecode($_GET['error']) : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Usuario - Panel de Administración</title>
    <link rel="stylesheet" href="../estilo.css">
    <style>
        .search-container {
            margin-bottom: 20px;
        }
        #search-user {
            padding: 10px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        #user-results {
            position: absolute;
            background: white;
            width: 300px;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            z-index: 1000;
            display: none;
        }
        .user-result {
            padding: 8px 10px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        .user-result:hover {
            background-color: #f5f5f5;
        }
        .user-selected {
            background-color: #f8d7da;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        .alert-info {
            color: #0c5460;
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
        }
        .btn {
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            font-size: 14px;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .btn-danger:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="panel.php" class="logout-btn">← Regresar</a>
            <h1>Eliminar Usuario</h1>
            <div class="user-info">
                Usuario: <?php echo htmlspecialchars($_SESSION['nombreusuario']); ?> | 
                Tipo: <?php echo htmlspecialchars($_SESSION['tipousuario']); ?>
            </div>
        </div>

        <div class="content">
            <?php if ($mensaje_exito): ?>
                <div class="alert alert-success">
                    <strong>Éxito:</strong> <?php echo htmlspecialchars($mensaje_exito); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($mensaje_error): ?>
                <div class="alert alert-danger">
                    <strong>Error:</strong> <?php echo htmlspecialchars($mensaje_error); ?>
                </div>
            <?php endif; ?>

            <div class="alert alert-info">
                <strong>Información:</strong> Busca y selecciona un usuario para eliminarlo.
            </div>

            <div class="search-container">
                <label for="search-user">Buscar Usuario:</label>
                <input type="text" id="search-user" placeholder="Escribe el nombre de usuario">
                <div id="user-results"></div>
            </div>

            <div class="user-selected" id="user-selected" style="display: none;">
                <strong>Usuario seleccionado:</strong> <span id="selected-username"></span>
                <input type="hidden" id="id_usuario">
            </div>

            <!-- Botones -->
            <div class="action-buttons">
                <button id="delete-btn" class="btn btn-danger" disabled>Eliminar Usuario</button>
            </div>
        </div>
    </div>

    <script>
        // Búsqueda de usuarios con AJAX
        document.getElementById('search-user').addEventListener('input', function(e) {
            const query = e.target.value.trim();
            const resultsContainer = document.getElementById('user-results');
            const currentUsername = '<?php echo $_SESSION['nombreusuario']; ?>';
            
            if (query.length < 1) {
                resultsContainer.style.display = 'none';
                return;
            }
            
            fetch(`../Bd/buscar_usuarios.php?q=${encodeURIComponent(query)}&currentUser=${encodeURIComponent(currentUsername)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error(data.error);
                        return;
                    }
                    
                    resultsContainer.innerHTML = '';
                    
                    if (data.length === 0) {
                        const noResults = document.createElement('div');
                        noResults.className = 'user-result';
                        noResults.textContent = 'No se encontraron usuarios';
                        resultsContainer.appendChild(noResults);
                    } else {
                        data.forEach(user => {
                            const userElement = document.createElement('div');
                            userElement.className = 'user-result';
                            userElement.textContent = user.nombreusuario + ' (' + user.tipousuario + ')';
                            userElement.dataset.id = user.id;
                            userElement.dataset.username = user.nombreusuario;
                            
                            userElement.addEventListener('click', function() {
                                selectUser(user.id, user.nombreusuario);
                            });
                            
                            resultsContainer.appendChild(userElement);
                        });
                    }
                    
                    resultsContainer.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });
        
        // Seleccionar usuario
        function selectUser(id, username) {
            document.getElementById('user-results').style.display = 'none';
            document.getElementById('search-user').value = '';
            
            // Mostrar información del usuario seleccionado
            const selectedDiv = document.getElementById('user-selected');
            document.getElementById('selected-username').textContent = username;
            document.getElementById('id_usuario').value = id;
            selectedDiv.style.display = 'block';
            
            // Habilitar botón de eliminar
            document.getElementById('delete-btn').disabled = false;
        }
        
        // Ocultar resultados al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (e.target.id !== 'search-user') {
                document.getElementById('user-results').style.display = 'none';
            }
        });

        // Manejar el clic en el botón Eliminar
        document.getElementById('delete-btn').addEventListener('click', async function() {
            const userId = document.getElementById('id_usuario').value;
            const username = document.getElementById('selected-username').textContent;
            const btn = this;
            
            if (!userId) {
                alert('No hay usuario seleccionado');
                return;
            }
            
            if (!confirm(`¿Estás seguro de que deseas eliminar al usuario "${username}"?\nEsta acción no se puede deshacer.`)) {
                return;
            }
            
            // Mostrar feedback visual
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Eliminando...';
            
            try {
                const response = await fetch(`../Bd/borrarusuario.php?id=${encodeURIComponent(userId)}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.error || `Error ${response.status}: ${response.statusText}`);
                }
                
                if (data.success) {
                    // Mostrar mensaje de éxito y resetear interfaz
                    window.location.href = `eliminarusuario.php?success=${encodeURIComponent(data.success)}`;
                } else {
                    throw new Error(data.error || 'Error desconocido al eliminar el usuario');
                }
            } catch (error) {
                console.error('Error en la solicitud:', error);
                
                // Mostrar error al usuario
                window.location.href = `eliminarusuario.php?error=${encodeURIComponent(error.message)}`;
            } finally {
                btn.disabled = false;
                btn.innerHTML = 'Eliminar Usuario';
            }
        });
    </script>
</body>
</html>