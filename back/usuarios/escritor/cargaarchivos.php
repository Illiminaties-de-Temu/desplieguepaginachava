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
        .image-preview-container {
            margin-top: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .image-preview-item {
            position: relative;
            display: inline-block;
        }
        
        .image-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border: 2px solid #ddd;
            border-radius: 5px;
        }
        
        .remove-image {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4444;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            cursor: pointer;
            font-size: 12px;
            line-height: 1;
        }
        
        .remove-image:hover {
            background: #cc0000;
        }

        /* Estilos para el campo de imágenes múltiples */
        .file-upload-section {
            border: 2px dashed #3498db;
            border-radius: 12px;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .file-upload-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(52, 152, 219, 0.1), transparent);
            transform: rotate(45deg);
            transition: all 0.3s ease;
            opacity: 0;
        }

        .file-upload-section:hover {
            border-color: #2980b9;
            background: linear-gradient(135deg, #ffffff 0%, #f1f3f4 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(52, 152, 219, 0.15);
        }

        .file-upload-section:hover::before {
            opacity: 1;
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .file-upload-content {
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .file-upload-icon {
            font-size: 3em;
            color: #3498db;
            margin-bottom: 15px;
            display: block;
        }

        .file-upload-text {
            color: #2c3e50;
            font-weight: 600;
            font-size: 1.1em;
            margin-bottom: 8px;
        }

        .file-upload-hint {
            color: #7f8c8d;
            font-size: 0.9em;
            font-style: italic;
        }

        input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
            z-index: 3;
        }

        /* Botones para agregar más imágenes */
        .image-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .add-more-btn {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .add-more-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.3);
            background: linear-gradient(135deg, #229954 0%, #27ae60 100%);
        }

        .image-counter {
            background: #ecf0f1;
            padding: 8px 15px;
            border-radius: 20px;
            color: #2c3e50;
            font-size: 14px;
            font-weight: 600;
        }

        .clear-all-btn {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .clear-all-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        /* Estilos para el checkbox de noticia destacada */
        .highlight-section {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 2px solid #f39c12;
            border-radius: 12px;
            padding: 20px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .highlight-section::before {
            content: '⭐';
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 1.8em;
            opacity: 0.3;
            transition: all 0.3s ease;
        }

        .highlight-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(243, 156, 18, 0.2);
            border-color: #e67e22;
        }

        .highlight-section:hover::before {
            opacity: 0.6;
            transform: rotate(360deg) scale(1.2);
        }

        .checkbox-container {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            position: relative;
        }

        .custom-checkbox {
            position: relative;
            cursor: pointer;
            margin-top: 3px;
        }

        .custom-checkbox input[type="checkbox"] {
            opacity: 0;
            position: absolute;
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .checkbox-style {
            width: 20px;
            height: 20px;
            border: 2px solid #f39c12;
            border-radius: 4px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            position: relative;
        }

        .custom-checkbox input[type="checkbox"]:checked + .checkbox-style {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            border-color: #d68910;
            transform: scale(1.1);
        }

        .custom-checkbox input[type="checkbox"]:checked + .checkbox-style::after {
            content: '✓';
            color: white;
            font-weight: bold;
            font-size: 14px;
        }

        .checkbox-content {
            flex: 1;
        }

        .checkbox-label {
            color: #2c3e50;
            font-weight: 600;
            font-size: 1.1em;
            margin-bottom: 8px !important;
            display: block;
            cursor: pointer;
        }

        .form-hint {
            color: #7f8c8d !important;
            font-size: 0.9em !important;
            font-style: italic;
            line-height: 1.4;
            margin: 0 !important;
        }

        /* Ajustes para mantener la coherencia con los estilos existentes */
        .form-group label[for="imagen"] {
            margin-bottom: 15px;
            font-size: 1.1em;
            color: #2c3e50;
            font-weight: 600;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .file-upload-section {
                padding: 15px;
            }
            
            .file-upload-icon {
                font-size: 2.5em;
            }
            
            .highlight-section {
                padding: 15px;
            }
            
            .checkbox-container {
                flex-direction: column;
                gap: 10px;
            }

            .image-actions {
                flex-direction: column;
                align-items: stretch;
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

            <form method="POST" id="createNewsForm" action="../bd/guardar_noticia.php" enctype="multipart/form-data">
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
                            <div class="file-upload-section">
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
                                <button type="button" class="clear-all-btn" onclick="clearAllImages()">
                                    🗑️ Limpiar todo
                                </button>
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
            if (selectedFiles.length > 0) {
                actions.style.display = 'flex';
            } else {
                actions.style.display = 'none';
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