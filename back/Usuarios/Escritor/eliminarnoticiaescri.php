<?php
session_start();

// Verificar si el usuario está logueado y es master
if (!isset($_SESSION['nombreusuario']) || $_SESSION['tipousuario'] !== 'editor') {
    header("Location: ../Login/Out.php");
    exit();
}

// Incluir el script de leer noticias
include_once '../Bd/obtenernoticias.php';

// Inicializar variables
$noticia_seleccionada = null;
$mensaje_exito = null;

// Si se envía un ID específico, cargar esa noticia
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $noticia_seleccionada = obtenerNoticiaPorId($pdo, $id);
}

// Procesar formulario de eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_eliminar'])) {
    // Redirigir al procesamiento via AJAX (esto se maneja en JavaScript)
    // El procesamiento real se hace en eliminar_noticia_bd.php
}

// Cargar todas las noticias para el selector
$todas_noticias = obtenerTodasNoticias($pdo);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Noticia - Panel Master</title>
    <link rel="stylesheet" href="../estilo.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="panel.php" class="logout-btn">← Regresar</a>
            <h1>Eliminar Noticia</h1>
            <div class="user-info">
                Usuario: <?php echo htmlspecialchars($_SESSION['nombreusuario']); ?> | 
                Tipo: <?php echo htmlspecialchars($_SESSION['tipousuario']); ?>
            </div>
        </div>

        <div class="content">
            <?php if (isset($mensaje_exito)): ?>
                <div class="alert alert-success">
                    <h3>✅ Operación Exitosa</h3>
                    <p><?php echo htmlspecialchars($mensaje_exito); ?></p>
                    <a href="eliminarnoticiaescri.php" class="btn btn-primary">Volver a la eliminacion</a>
                </div>
            <?php else: ?>
                <div class="form-section">
                    <h2>Seleccionar Noticia a Eliminar</h2>
                    <div class="form-group">
                        <label for="selector_noticia"><strong>Seleccione una noticia:</strong></label>
                        <select id="selector_noticia" class="form-control">
                            <option value="">-- Seleccione una noticia --</option>
                            <?php if ($todas_noticias): ?>
                                <?php foreach ($todas_noticias as $noticia): ?>
                                    <option value="<?php echo $noticia['id']; ?>" 
                                            <?php echo ($noticia_seleccionada && $noticia_seleccionada['id'] == $noticia['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars('Id #' . $noticia['id']); ?>
                                        <?php echo htmlspecialchars($noticia['Titulo'] . ' ---'); ?> 
                                        (<?php echo date('d/m/Y', strtotime( $noticia['fecha'])); ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div id="loading" style="display: none; text-align: center; padding: 20px;">
                    <p>Cargando datos de la noticia...</p>
                </div>

                <div id="noticia_content" style="display: <?php echo $noticia_seleccionada ? 'block' : 'none'; ?>;">
                    <div class="form-section">
                        <div class="alert alert-danger">
                            <h2>
                                <span class="warning-icon">⚠️</span>
                                ZONA DE PELIGRO
                            </h2>
                            <p>Está a punto de eliminar permanentemente esta noticia. Esta acción no se puede deshacer.</p>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2>Vista Previa de la Noticia a Eliminar</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><strong>ID:</strong></label>
                                <div class="meta-display" id="noticia_id"><?php echo $noticia_seleccionada ? '#'.$noticia_seleccionada['id'] : ''; ?></div>
                            </div>
                            <div class="form-group">
                                <label><strong>Fecha:</strong></label>
                                <div class="meta-display" id="noticia_fecha"><?php echo $noticia_seleccionada ? date('d/m/Y', strtotime($noticia_seleccionada['fecha'])) : ''; ?></div>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label><strong>Título:</strong></label>
                            <div class="content-display" id="noticia_titulo"><?php echo $noticia_seleccionada ? htmlspecialchars($noticia_seleccionada['Titulo']) : ''; ?></div>
                        </div>

                        <div class="form-group full-width">
                            <label><strong>Contenido:</strong></label>
                            <div class="content-display" id="noticia_contenido"><?php echo $noticia_seleccionada ? htmlspecialchars($noticia_seleccionada['Contenido']) : ''; ?></div>
                        </div>

                        <div class="image-section" id="imagenes_section" style="display: <?php echo ($noticia_seleccionada && !empty($noticia_seleccionada['Imagenes'])) ? 'block' : 'none'; ?>;">
                            <h3>Imágenes que serán eliminadas:</h3>
                            <div class="current-images" id="current_images">
                                <?php if ($noticia_seleccionada && !empty($noticia_seleccionada['Imagenes'])): ?>
                                    <?php 
                                    $imagenes = explode(',', $noticia_seleccionada['Imagenes']);
                                    foreach ($imagenes as $index => $imagen): 
                                        if (trim($imagen)):
                                            // Ajustar la ruta de la imagen según la estructura de directorios
                                            $rutaImagen = trim($imagen);
                                            if (strpos($rutaImagen, 'contenido/') === 0) {
                                                $rutaImagen = '../../' . $rutaImagen;
                                            }
                                    ?>
                                        <div class="image-item">
                                            <img src="<?php echo htmlspecialchars($rutaImagen); ?>" alt="Imagen <?php echo $index + 1; ?>" onerror="this.style.display='none'">
                                            <div class="image-info">
                                                <strong><?php echo basename(trim($imagen)); ?></strong>
                                            </div>
                                        </div>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2>Confirmación Requerida</h2>
                        <div class="alert alert-info">
                            <p>Para proceder con la eliminación, debe confirmar que comprende las consecuencias:</p>
                        </div>
                        
                        <form method="POST" id="deleteForm">
                            <input type="hidden" id="noticia_id_hidden" name="noticia_id" value="<?php echo $noticia_seleccionada ? $noticia_seleccionada['id'] : ''; ?>">
                            
                            <div class="form-group">
                                <div class="checkbox-container">
                                    <input type="checkbox" id="confirm1" name="confirm1" required>
                                    <label for="confirm1">Comprendo que esta acción eliminará permanentemente la noticia</label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="checkbox-container">
                                    <input type="checkbox" id="confirm2" name="confirm2" required>
                                    <label for="confirm2">Comprendo que todas las imágenes asociadas serán eliminadas</label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="checkbox-container">
                                    <input type="checkbox" id="confirm3" name="confirm3" required>
                                    <label for="confirm3">Confirmo que tengo autorización para realizar esta acción</label>
                                </div>
                            </div>

                            <div class="alert alert-danger">
                                <p><strong>Una vez eliminada, esta noticia no podrá ser recuperada.</strong></p>
                            </div>

                            <div class="buttons">
                            
                                <button type="submit" name="confirmar_eliminar" class="btn btn-danger" id="deleteBtn" disabled>
                                    🗑️ Eliminar Definitivamente
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Función para cargar imágenes actuales con rutas correctas
        function cargarImagenesActuales(imagenes) {
            const container = document.getElementById('current_images');
            const section = document.getElementById('imagenes_section');
    
            if (!imagenes || imagenes.trim() === '') {
                container.innerHTML = '<p>No hay imágenes asociadas a esta noticia.</p>';
                section.style.display = 'none';
                return;
            }
    
            const arrayImagenes = imagenes.split(',');
            let html = '';
    
            arrayImagenes.forEach((imagen, index) => {
                if (imagen.trim()) {
                    // Ajustar la ruta de la imagen según la estructura de directorios
                    const rutaImagen = imagen.trim().startsWith('contenido/') ? `../../${imagen.trim()}` : imagen.trim();
            
                    html += `
                        <div class="image-item">
                            <img src="${rutaImagen}" alt="Imagen ${index + 1}" onerror="this.style.display='none'">
                            <div class="image-info">
                                <strong>${imagen.trim().split('/').pop()}</strong>
                            </div>
                        </div>
                    `;
                }
            });
    
            container.innerHTML = html;
            section.style.display = 'block';
        }

        // Función para cargar datos de la noticia via AJAX
        function cargarNoticia(id) {
            if (!id) {
                document.getElementById('noticia_content').style.display = 'none';
                return;
            }

            document.getElementById('loading').style.display = 'block';
            document.getElementById('noticia_content').style.display = 'none';

            // Realizar petición AJAX
            fetch('../Bd/obtenernoticias.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }

                    // Rellenar los campos con los datos recibidos
                    document.getElementById('noticia_id').textContent = '#' + data.id;
                    document.getElementById('noticia_fecha').textContent = new Date(data.fecha).toLocaleDateString('es-ES');
                    document.getElementById('noticia_titulo').textContent = data.Titulo;
                    document.getElementById('noticia_contenido').textContent = data.Contenido;
                    document.getElementById('noticia_id_hidden').value = data.id;

                    // Cargar imágenes con la función corregida
                    cargarImagenesActuales(data.Imagenes);

                    // Resetear checkboxes
                    document.getElementById('confirm1').checked = false;
                    document.getElementById('confirm2').checked = false;
                    document.getElementById('confirm3').checked = false;
                    checkConfirmations();

                    // Mostrar el contenido
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('noticia_content').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar la noticia');
                    document.getElementById('loading').style.display = 'none';
                });
        }

        // Event listener para el selector de noticias
        document.getElementById('selector_noticia').addEventListener('change', function() {
            const id = this.value;
            cargarNoticia(id);
            
            // Actualizar URL sin recargar la página
            if (id) {
                window.history.pushState({}, '', '?id=' + id);
            } else {
                window.history.pushState({}, '', window.location.pathname);
            }
        });

        // Controlar el estado del botón de eliminar
        function checkConfirmations() {
            const confirm1 = document.getElementById('confirm1').checked;
            const confirm2 = document.getElementById('confirm2').checked;
            const confirm3 = document.getElementById('confirm3').checked;
            const deleteBtn = document.getElementById('deleteBtn');
            
            if (confirm1 && confirm2 && confirm3) {
                deleteBtn.disabled = false;
                deleteBtn.style.opacity = '1';
            } else {
                deleteBtn.disabled = true;
                deleteBtn.style.opacity = '0.5';
            }
        }

        // Agregar event listeners a todos los checkboxes
        document.getElementById('confirm1').addEventListener('change', checkConfirmations);
        document.getElementById('confirm2').addEventListener('change', checkConfirmations);
        document.getElementById('confirm3').addEventListener('change', checkConfirmations);

        // Confirmación final antes de enviar
        document.getElementById('deleteForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevenir envío normal del formulario
            
            const confirmed = confirm('¿Está ABSOLUTAMENTE SEGURO de que desea eliminar esta noticia?\n\nEsta acción NO se puede deshacer.');
            
            if (!confirmed) {
                return false;
            }
            
            // Mostrar mensaje de procesamiento
            const deleteBtn = document.getElementById('deleteBtn');
            deleteBtn.innerHTML = '⏳ Eliminando...';
            deleteBtn.disabled = true;
            
            // Preparar datos para enviar
            const formData = {
                noticia_id: document.getElementById('noticia_id_hidden').value,
                confirm1: document.getElementById('confirm1').checked ? 'true' : 'false',
                confirm2: document.getElementById('confirm2').checked ? 'true' : 'false',
                confirm3: document.getElementById('confirm3').checked ? 'true' : 'false'
            };
            
            // Enviar petición AJAX
            fetch('../Bd/eliminarnoticiaescri.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mostrar mensaje de éxito
                    let mensaje = data.message;
                    if (data.imagenes_eliminadas && data.imagenes_eliminadas.length > 0) {
                        mensaje += `\n\nImágenes eliminadas: ${data.imagenes_eliminadas.length}`;
                    }
                    if (data.errores_imagenes && data.errores_imagenes.length > 0) {
                       mensaje += `\n\nAdvertencias: ${data.errores_imagenes.join(', ')}`;
                    }
                    
                    alert(mensaje);
                    
                    // Redirigir a la lista de noticias
                    window.location.href = 'eliminarnoticiaescri.php';
                } else {
                    // Mostrar error
                    alert('Error al eliminar la noticia: ' + data.error);
                    
                    // Restaurar botón
                    deleteBtn.innerHTML = '🗑️ Eliminar Definitivamente';
                    deleteBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión al intentar eliminar la noticia');
                
                // Restaurar botón
                deleteBtn.innerHTML = '🗑️ Eliminar Definitivamente';
                deleteBtn.disabled = false;
            });
            
            return false;
        });

    </script>
        <style>
        /* Estilos específicos para la página de eliminación */
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }

        .meta-display, .content-display {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
            border: 1px solid #e0e0e0;
            margin-top: 5px;
        }

        .content-display {
            min-height: 40px;
            line-height: 1.5;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .checkbox-container:hover {
            border-color: #3498db;
            background: #e3f2fd;
        }

        .checkbox-container input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin: 0;
            cursor: pointer;
        }

        .checkbox-container label {
            margin: 0;
            cursor: pointer;
            font-size: 14px;
            flex: 1;
        }

        .btn-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }

        .btn-danger:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }

        .btn-danger:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .warning-icon {
            font-size: 1.2em;
            margin-right: 10px;
        }

        .image-info {
            padding: 10px;
            background: rgba(255,255,255,0.9);
            text-align: center;
            font-size: 12px;
        }

        @media (max-width: 768px) {
            .checkbox-container {
                flex-direction: column;
                text-align: center;
                gap: 8px;
            }
            
            .checkbox-container input[type="checkbox"] {
                margin-bottom: 8px;
            }
        }
    </style>
</body>
</html>