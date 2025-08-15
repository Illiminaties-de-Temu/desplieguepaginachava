<?php
session_start();

// Verificar permisos
if (!isset($_SESSION['nombreusuario']) || $_SESSION['tipousuario'] !== 'editor') {
    header("Location: ../login/out.php");
    exit();
}

require_once '../../config/config.php';

// Obtener mensajes de sesión y limpiarlos
$mensaje_exito = $_SESSION['mensaje_exito'] ?? '';
$mensaje_error = $_SESSION['errores'] ?? '';
$datos_formulario = $_SESSION['datos_formulario'] ?? [];

// Limpiar las variables de sesión después de usarlas
unset($_SESSION['mensaje_exito']);
unset($_SESSION['errores']);
unset($_SESSION['datos_formulario']);

// Valores por defecto para el formulario
$titulo = $datos_formulario['titulo'] ?? '';
$contenido = $datos_formulario['contenido'] ?? '';
$destacada = $datos_formulario['destacada'] ?? 'no';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Noticia - Panel Master</title>
    <link rel="stylesheet" href="../estilo.css">
    <style>
    /* FUENTE GOTHAM */
    @import url('https://fonts.cdnfonts.com/css/gotham');
    * {
        font-family: 'Gotham', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* PALETA DE COLORES */
    body {
        background-color: #f0f4f8;
        color: #2a3441;
    }

    .container {
        background: white;
        border: 1px solid rgba(29, 78, 216, 0.2);
    }

    .header {
        background: linear-gradient(135deg, #1d4ed8, #083057);
        color: white;
    }

    .logout-btn {
        background: #e74c3c;
        color: white;
    }

    .user-info {
        background: rgba(29, 78, 216, 0.1);
    }

    /* ESTILOS EXISTENTES CON NUEVA PALETA */
    .image-preview {
        border: 2px solid rgba(29, 78, 216, 0.3);
    }

    .remove-image {
        background: #e74c3c;
    }

    .file-upload-section {
        border: 2px dashed #1d4ed8;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .file-upload-section:hover {
        border-color: #083057;
    }

    .file-upload-icon {
        color: #1d4ed8;
    }

    .add-more-btn {
        background: linear-gradient(135deg, #1d4ed8 0%, #083057 100%);
    }

    .image-counter {
        background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    }

    .highlight-section {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border: 2px solid #f39c12;
    }

    .checkbox-style {
        border: 2px solid #f39c12;
    }

    .custom-checkbox input[type="checkbox"]:checked + .checkbox-style {
        background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    }

    /* MANTENIENDO TODAS LAS CLASES ORIGINALES */
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

    .btn-primary {
        background: #1d4ed8;
        color: white;
    }

    .btn-secondary {
        background: #2a3441;
        color: white;
    }

    /* CONSERVANDO TODAS LAS ANIMACIONES Y EFECTOS */
    .file-upload-section::before,
    .highlight-section::before,
    .custom-checkbox input[type="checkbox"]:checked + .checkbox-style,
    .add-more-btn:hover,
    .file-upload-section:hover {
        /* Todas las transiciones y animaciones se mantienen igual */
        transition: all 0.3s ease;
    }

    @keyframes shimmer {
        0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
        100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
    }

    /* RESPONSIVE - CONSERVADO COMPLETO */
    @media (max-width: 768px) {
        .file-upload-section,
        .highlight-section,
        .image-actions {
            padding: 15px;
        }
    }

    @media (max-width: 480px) {
        .add-more-btn,
        .image-counter {
            padding: 18px;
            font-size: 16px;
        }
    }
</style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="panel.php" class="logout-btn">← Regresar</a>
            <h1>Crear Noticia</h1>
            <div class="user-info">
                Usuario: <?php echo htmlspecialchars($_SESSION['nombreusuario']); ?> | 
                Tipo: <?php echo htmlspecialchars($_SESSION['tipousuario']); ?>
            </div>
        </div>

        <div class="content">
            <?php if ($mensaje_error): ?>
                <div class="alert alert-danger">
                    <strong>Error:</strong> <?php echo htmlspecialchars($mensaje_error); ?>
                </div>
            <?php endif; ?>

            <?php if ($mensaje_exito): ?>
                <div class="alert alert-success">
                    <strong>Éxito:</strong> <?php echo htmlspecialchars($mensaje_exito); ?>
                </div>
                <script>
                    // Limpiar el formulario después de mostrar el mensaje de éxito
                    setTimeout(() => {
                        document.getElementById('createNewsForm').reset();
                        clearAllImages();
                    }, 100);
                </script>
            <?php endif; ?>

            <form method="POST" id="createNewsForm" action="../bd/guardar_noticia_escri.php" enctype="multipart/form-data">
                <div class="form-section">
                    <h2>Información de la Noticia</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="titulo">Título:</label>
                            <input type="text" 
                                   name="titulo" 
                                   id="titulo" 
                                   value="<?php echo htmlspecialchars($titulo ?? ''); ?>" >
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="contenido">Contenido:</label>
                            <textarea name="contenido" 
                                      id="contenido" 
                                      required><?php echo htmlspecialchars($contenido ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="imagen">Imágenes (puedes seleccionar múltiples):</label>
                            <div class="file-upload-section" id="initialUploadSection">
                                <input type="file" 
                                       name="imagen[]" 
                                       id="imagen" 
                                       multiple 
                                       accept="image/*" 
                                       required>
                                <div class="file-upload-content">
                                    <div class="file-upload-text">Haz clic aquí para seleccionar imágenes</div>
                                    <div class="file-upload-hint">Puedes seleccionar múltiples archivos de imagen</div>
                                </div>
                            </div>
                            
                            <!-- Contenedor para mostrar imágenes seleccionadas -->
                            <div id="imagePreviewContainer" class="image-preview-container"></div>
                            
                            <!-- Botones de acción para las imágenes -->
                            <div class="image-actions" id="imageActions" style="display: none;">
                                <button type="button" class="add-more-btn" onclick="addMoreImages()">
                                    ➕ Agregar más imágenes
                                </button>
                                <div class="image-counter" id="imageCounter">0 imágenes seleccionadas</div>
                            </div>
                            
                            <!-- Input oculto para agregar más imágenes -->
                            <input type="file" 
                                   id="additionalImages" 
                                   multiple 
                                   accept="image/*" 
                                   style="display: none;">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <div class="highlight-section">
                                <div class="checkbox-container">
                                    <label class="custom-checkbox">
                                        <input type="checkbox" 
                                               name="destacada" 
                                               value="si" 
                                               <?php echo $destacada === 'si' ? 'checked' : ''; ?>>
                                        <span class="checkbox-style"></span>
                                    </label>
                                    <div class="checkbox-content">
                                        <span class="checkbox-label">Destacar esta noticia</span>
                                        <p class="form-hint">Las noticias destacadas aparecerán en posiciones privilegiadas en la página principal.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="buttons">
                    <button type="reset" class="btn btn-secondary" onclick="clearAllImages()">Limpiar Formulario</button>
                    <button type="submit" class="btn btn-primary">Crear Noticia</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let selectedFiles = [];

        // Manejar la selección inicial de imágenes
        document.getElementById('imagen').addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            selectedFiles = files;
            displayImagePreviews();
            updateImageCounter();
            toggleImageActions();
        });

        // Manejar la adición de más imágenes
        document.getElementById('additionalImages').addEventListener('change', function(e) {
            const newFiles = Array.from(e.target.files);
            
            // Agregar los nuevos archivos al array existente
            newFiles.forEach(file => {
                // Verificar que no sea un duplicado (opcional)
                const isDuplicate = selectedFiles.some(existingFile => 
                    existingFile.name === file.name && 
                    existingFile.size === file.size &&
                    existingFile.lastModified === file.lastModified
                );
                
                if (!isDuplicate) {
                    selectedFiles.push(file);
                }
            });
            
            // Actualizar el input principal con todos los archivos
            updateMainFileInput();
            displayImagePreviews();
            updateImageCounter();
            
            // Limpiar el input auxiliar
            this.value = '';
        });

        function addMoreImages() {
            document.getElementById('additionalImages').click();
        }

        function displayImagePreviews() {
            const container = document.getElementById('imagePreviewContainer');
            container.innerHTML = '';

            selectedFiles.forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'image-preview-item';
                        
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'image-preview';
                        img.alt = 'Vista previa';
                        img.title = file.name; // Mostrar nombre del archivo al hacer hover
                        
                        const removeBtn = document.createElement('button');
                        removeBtn.innerHTML = '×';
                        removeBtn.className = 'remove-image';
                        removeBtn.type = 'button';
                        removeBtn.title = 'Eliminar imagen';
                        removeBtn.onclick = function() {
                            removeImage(index);
                        };
                        
                        previewItem.appendChild(img);
                        previewItem.appendChild(removeBtn);
                        container.appendChild(previewItem);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        function removeImage(index) {
            selectedFiles.splice(index, 1);
            updateMainFileInput();
            displayImagePreviews();
            updateImageCounter();
            toggleImageActions();
        }

        function updateMainFileInput() {
            const fileInput = document.getElementById('imagen');
            const dt = new DataTransfer();
            
            selectedFiles.forEach(file => {
                dt.items.add(file);
            });
            
            fileInput.files = dt.files;
            
            // Manejar el atributo required
            if (selectedFiles.length === 0) {
                fileInput.setAttribute('required', 'required');
            } else {
                fileInput.removeAttribute('required');
            }
        }

        function updateImageCounter() {
            const counter = document.getElementById('imageCounter');
            const count = selectedFiles.length;
            counter.textContent = `${count} imagen${count !== 1 ? 'es' : ''} seleccionada${count !== 1 ? 's' : ''}`;
        }

        function toggleImageActions() {
            const actions = document.getElementById('imageActions');
            const initialUpload = document.getElementById('initialUploadSection');
            
            if (selectedFiles.length > 0) {
                actions.style.display = 'flex';
                initialUpload.style.display = 'none'; // Ocultar el primer selector cuando hay imágenes
            } else {
                actions.style.display = 'none';
                initialUpload.style.display = 'block'; // Mostrar el primer selector cuando no hay imágenes
            }
        }

        function clearAllImages() {
            selectedFiles = [];
            document.getElementById('imagePreviewContainer').innerHTML = '';
            const fileInput = document.getElementById('imagen');
            fileInput.value = '';
            fileInput.setAttribute('required', 'required');
            document.getElementById('additionalImages').value = '';
            updateImageCounter();
            toggleImageActions();
        }

        // Manejar el botón de reset del formulario
        document.getElementById('createNewsForm').addEventListener('reset', function() {
            setTimeout(() => {
                clearAllImages();
            }, 10);
        });

        // Inicializar contador y acciones al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            updateImageCounter();
            toggleImageActions();
        });

        // Prevenir el envío del formulario si no hay imágenes seleccionadas
        document.getElementById('createNewsForm').addEventListener('submit', function(e) {
            if (selectedFiles.length === 0) {
                e.preventDefault();
                alert('Por favor, selecciona al menos una imagen antes de crear la noticia.');
                return false;
            }
        });
    </script>
</body>
</html>