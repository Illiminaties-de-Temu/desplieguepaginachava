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
            border: 2px solid #e0e0e0;
            border-radius: 5px;
        }
        
        .remove-image {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #e74c3c;
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
            background: #c0392b;
        }

        /* Estilos para el campo de imágenes múltiples */
        .file-upload-section {
            border: 2px dashed #1d4ed8;
            border-radius: 12px;
            padding: 20px;
            background: linear-gradient(135deg, #f0f4f8 0%, #e3f2fd 100%);
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
            background: linear-gradient(45deg, transparent, rgba(29, 78, 216, 0.1), transparent);
            transform: rotate(45deg);
            transition: all 0.3s ease;
            opacity: 0;
        }

        .file-upload-section:hover {
            border-color: #083057;
            background: linear-gradient(135deg, white 0%, #f0f4f8 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(29, 78, 216, 0.15);
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
            color: #1d4ed8;
            margin-bottom: 15px;
            display: block;
        }

        .file-upload-text {
            color: #2a3441;
            font-weight: 600;
            font-size: 1.1em;
            margin-bottom: 8px;
        }

        .file-upload-hint {
            color: #2a3441;
            font-size: 0.9em;
            font-style: italic;
            opacity: 0.7;
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
            margin-top: 25px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(135deg, #f0f4f8 0%, #e3f2fd 100%);
            border-radius: 15px;
            border: 1px solid #e0e0e0;
        }

        .add-more-btn {
            background: linear-gradient(135deg, #1d4ed8 0%, #083057 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 50px;
            box-shadow: 0 4px 15px rgba(29, 78, 216, 0.2);
        }

        .add-more-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(29, 78, 216, 0.4);
            background: linear-gradient(135deg, #083057 0%, #1d4ed8 100%);
        }

        .add-more-btn:active {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(29, 78, 216, 0.3);
        }

        .image-counter {
            background: linear-gradient(135deg, #1d4ed8 0%, #083057 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(29, 78, 216, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.2);
            min-height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Estilos para el checkbox de noticia destacada */
        .highlight-section {
            background: linear-gradient(135deg, #e3f2fd 0%, rgba(29, 78, 216, 0.1) 100%);
            border: 2px solid #1d4ed8;
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
            color: #1d4ed8;
        }

        .highlight-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(29, 78, 216, 0.2);
            border-color: #083057;
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
            border: 2px solid #1d4ed8;
            border-radius: 4px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            position: relative;
        }

        .custom-checkbox input[type="checkbox"]:checked + .checkbox-style {
            background: linear-gradient(135deg, #1d4ed8 0%, #083057 100%);
            border-color: #083057;
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
            color: #2a3441;
            font-weight: 600;
            font-size: 1.1em;
            margin-bottom: 8px !important;
            display: block;
            cursor: pointer;
        }

        .form-hint {
            color: #2a3441 !important;
            font-size: 0.9em !important;
            font-style: italic;
            line-height: 1.4;
            margin: 0 !important;
            opacity: 0.7;
        }

        /* Ajustes para mantener la coherencia con los estilos existentes */
        .form-group label[for="imagen"] {
            margin-bottom: 15px;
            font-size: 1.1em;
            color: #2a3441;
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
                gap: 12px;
                padding: 15px;
            }

            .add-more-btn {
                padding: 18px 25px;
                font-size: 18px;
                justify-content: center;
                min-height: 55px;
            }

            .image-counter {
                font-size: 18px;
                padding: 15px 20px;
                text-align: center;
                min-height: 55px;
            }
        }

        @media (max-width: 480px) {
            .add-more-btn {
                gap: 15px;
                padding: 20px;
                font-size: 20px;
                min-height: 60px;
            }

            .image-counter {
                font-size: 16px;
                padding: 18px;
                min-height: 60px;
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