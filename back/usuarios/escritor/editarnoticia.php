<?php
session_start();

// Verificar si el usuario está logueado y es master
if (!isset($_SESSION['nombreusuario']) || $_SESSION['tipousuario'] !== 'editor') {
    header("Location: ../login/out.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Noticia - Panel Escritor</title>
    <link rel="stylesheet" href="../estilo.css">
    <style>
        /* Estilos base para vista previa de imágenes */
        .image-preview-container {
            margin-top: 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            border: 2px dashed #dee2e6;
            transition: all 0.3s ease;
            min-height: 140px;
            position: relative;
        }
        
        .image-preview-container.drag-over {
            border-color: #3498db;
            background: linear-gradient(135deg, #e3f2fd 0%, #f3f8ff 100%);
            transform: scale(1.01);
            box-shadow: 0 8px 25px rgba(52, 152, 219, 0.2);
        }

        .image-preview-container.has-images {
            border-style: solid;
            border-color: #28a745;
            background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%);
        }
        
        .image-preview-item {
            position: relative;
            display: inline-block;
            cursor: grab;
            transition: all 0.3s ease;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            background: white;
        }
        
        .image-preview-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .image-preview-item.dragging {
            opacity: 0.6;
            transform: rotate(5deg) scale(0.95);
            cursor: grabbing;
            z-index: 1000;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }
        
        .image-preview-item.drag-over {
            transform: translateX(10px) scale(1.05);
            box-shadow: 0 10px 30px rgba(52, 152, 219, 0.4);
        }
        
        .image-preview {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 3px solid #fff;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .image-preview-item:hover .image-preview {
            border-color: #3498db;
        }
        
        .remove-image {
            position: absolute;
            top: -8px;
            right: -8px;
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            line-height: 1;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .remove-image:hover {
            background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
            transform: scale(1.2);
            box-shadow: 0 6px 18px rgba(231, 76, 60, 0.6);
        }

        .image-index {
            position: absolute;
            bottom: -8px;
            left: -8px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
        }

        .image-type-indicator {
            position: absolute;
            top: -8px;
            left: -8px;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .image-type-current {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .image-type-new {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        }

        /* Estilos para sección de carga de archivos */
        .file-upload-section {
            border: 2px dashed #3498db;
            border-radius: 12px;
            padding: 25px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-align: center;
            cursor: pointer;
            margin-top: 20px;
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

        .hidden-file-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
            z-index: 3;
        }

        /* Acciones de imagen */
        .image-actions {
            margin-top: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            border: 1px solid #dee2e6;
        }

        .image-counter {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.2);
            min-height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Estado vacío */
        .empty-state {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: #7f8c8d;
            font-style: italic;
            font-size: 1.1em;
            pointer-events: none;
        }

        .empty-state-icon {
            font-size: 3em;
            margin-bottom: 10px;
            opacity: 0.5;
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

        /* Mensaje temporal */
        .temp-message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            z-index: 10000;
            animation: slideIn 0.3s ease;
        }

        .temp-message.success {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
        }

        .temp-message.error {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .image-preview-container {
                gap: 10px;
                padding: 15px;
            }
            
            .image-preview {
                width: 100px;
                height: 100px;
            }
            
            .image-actions {
                flex-direction: column;
                gap: 12px;
            }
            
            .file-upload-section {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="panel.php" class="logout-btn">← Regresar</a>
            <h1>Editar Noticia</h1>
            <div class="user-info">
                Usuario: <?php echo htmlspecialchars($_SESSION['nombreusuario']); ?> | 
                Tipo: <?php echo htmlspecialchars($_SESSION['tipousuario']); ?>
            </div>
        </div>

        <div class="content">
            <!-- Selector de noticia -->
            <div class="selector-noticia">
                <h2>Seleccionar Noticia para Editar</h2>
                <div class="form-row-selector">
                    <div class="form-group-selector">
                        <select id="selector_noticia">
                            <option value="">-- Seleccionar una noticia --</option>
                        </select>
                    </div>
                </div>
                <div id="mensaje_carga"></div>
            </div>

            <!-- Formulario de edición (inicialmente oculto) -->
            <div id="formulario_edicion" class="form-hidden">
                <div class="alert alert-info" id="info_noticia">
                    <strong>Editando:</strong> <span id="noticia_info"></span>
                </div>

                <form id="form_editar_noticia" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="id_noticia" value="">
                    <input type="hidden" name="imagenes_existentes" id="imagenes_existentes" value="">
                    <input type="hidden" name="orden_imagenes" id="orden_imagenes" value="">
                    
                    <div class="form-section">
                        <h2>Información General</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="fecha">Fecha:</label>
                                <input type="date" name="fecha" id="fecha" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="titulo">Título:</label>
                                <input type="text" name="titulo" id="titulo" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="highlight-section">
                                <div class="checkbox-container">
                                    <label class="custom-checkbox">
                                        <input type="checkbox" 
                                            name="destacada" 
                                            id="destacada"
                                            value="si">
                                        <span class="checkbox-style"></span>
                                    </label>
                                    <div class="checkbox-content">
                                        <span class="checkbox-label">Destacar esta noticia</span>
                                        <p class="form-hint">Las noticias destacadas aparecerán en posiciones privilegiadas.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2>Contenido</h2>
                        
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="contenido">Contenido de la noticia:</label>
                                <textarea name="contenido" id="contenido" required></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-section image-section">
                        <h2>Gestión de Imágenes</h2>
                        
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="imagenes">Imágenes de la noticia (arrastra para reordenar):</label>
                                
                                <!-- Contenedor principal de imágenes -->
                                <div id="main_images_container" class="image-preview-container">
                                    <div class="empty-state" id="empty_state">
                                        <div class="empty-state-icon">📷</div>
                                        <div>No hay imágenes para mostrar</div>
                                    </div>
                                </div>
                                
                                <!-- Sección para agregar nuevas imágenes -->
                                <div class="file-upload-section" onclick="document.getElementById('nuevas_imagenes_input').click()">
                                    <input type="file" 
                                           name="nuevas_imagenes[]" 
                                           id="nuevas_imagenes_input" 
                                           class="hidden-file-input"
                                           multiple 
                                           accept="image/*">
                                    <div class="file-upload-content">
                                        <div class="file-upload-icon">📁</div>
                                        <div class="file-upload-text">Seleccionar nuevas imágenes</div>
                                        <div class="file-upload-hint">Haz clic para seleccionar archivos o arrastra aquí</div>
                                    </div>
                                </div>
                                
                                <!-- Contador de imágenes -->
                                <div class="image-actions" id="image_counter_section">
                                    <div class="image-counter" id="image_counter">0 imágenes</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="buttons">
                        <button type="button" class="btn btn-secondary" id="btn_limpiar">Limpiar Formulario</button>
                        <button type="submit" class="btn btn-primary">Actualizar Noticia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        let noticias = [];
        let noticiaActual = null;
        let imagenesActuales = [];
        let selectedNewFiles = [];
        let draggedElement = null;
        let draggedIndex = -1;
        let allImages = []; // Array combinado de todas las imágenes

        // Cargar lista de noticias al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            cargarListaNoticias();
            setupEventListeners();
        });

        // Configurar event listeners
        function setupEventListeners() {
            // Event listener para nuevas imágenes
            document.getElementById('nuevas_imagenes_input').addEventListener('change', function(e) {
                const files = Array.from(e.target.files);
                addNewFiles(files);
            });

            // Event listener para el selector de noticias
            document.getElementById('selector_noticia').addEventListener('change', function() {
                const idNoticia = this.value;
                if (idNoticia) {
                    cargarNoticia(idNoticia);
                } else {
                    ocultarFormulario();
                }
            });

            // Event listener para el botón limpiar
            document.getElementById('btn_limpiar').addEventListener('click', function() {
                if (confirm('¿Está seguro de que desea limpiar el formulario? Se perderán los cambios no guardados.')) {
                    limpiarFormulario();
                }
            });

            // Event listener para el envío del formulario
            document.getElementById('form_editar_noticia').addEventListener('submit', function(e) {
                e.preventDefault();
                enviarFormulario();
            });

            // Configurar drag and drop para archivos en la sección de upload
            setupFileDropZone();
        }

        // Función para cargar la lista de noticias
        function cargarListaNoticias() {
            const selector = document.getElementById('selector_noticia');
            const mensaje = document.getElementById('mensaje_carga');
            
            mensaje.innerHTML = '<div class="loading">Cargando noticias...</div>';
            
            fetch('../bd/leernoticias.php')
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    noticias = data;
                    selector.innerHTML = '<option value="">-- Seleccionar una noticia --</option>';
                    
                    data.forEach(noticia => {
                        const option = document.createElement('option');
                        option.value = noticia.id;
                        option.textContent = `#${noticia.id} - ${noticia.Titulo} - ${noticia.fecha}`;
                        selector.appendChild(option);
                    });
                    
                    mensaje.innerHTML = '<div class="success">Noticias cargadas correctamente</div>';
                    setTimeout(() => mensaje.innerHTML = '', 3000);
                })
                .catch(error => {
                    console.error('Error:', error);
                    mensaje.innerHTML = `<div class="error">Error al cargar noticias: ${error.message}</div>`;
                });
        }

        // Función para cargar una noticia específica
        function cargarNoticia(id) {
            const mensaje = document.getElementById('mensaje_carga');
    
            mensaje.innerHTML = '<div class="loading">Cargando noticia...</div>';
    
            fetch(`../bd/leernoticias.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
            
                    noticiaActual = data;
                    llenarFormulario(data);
                    mostrarFormulario();
            
                    mensaje.innerHTML = '<div class="success">Noticia cargada correctamente</div>';
                    setTimeout(() => mensaje.innerHTML = '', 3000);
                })
                .catch(error => {
                    console.error('Error:', error);
                    mensaje.innerHTML = `<div class="error">Error al cargar la noticia: ${error.message}</div>`;
                });
        }

        // Función para llenar el formulario con los datos de la noticia
        function llenarFormulario(noticia) {
            document.getElementById('id_noticia').value = noticia.id;
            document.getElementById('titulo').value = noticia.Titulo;
            document.getElementById('contenido').value = noticia.Contenido;
            document.getElementById('fecha').value = noticia.fecha;
            document.getElementById('destacada').checked = noticia.Destacada === 'si';
            
            // Actualizar información de la noticia
            document.getElementById('noticia_info').textContent = 
                `ID #${noticia.id} - Creada el ${noticia.fecha}`;
            
            // Cargar imágenes actuales
            cargarImagenesActuales(noticia.Imagenes);
            
            // Limpiar nuevas imágenes
            selectedNewFiles = [];
            updateAllImages();
        }

        // Función para cargar las imágenes actuales
        function cargarImagenesActuales(imagenes) {
            if (!imagenes || imagenes.trim() === '') {
                imagenesActuales = [];
            } else {
                imagenesActuales = imagenes.split(',')
                    .map(img => img.trim())
                    .filter(img => img !== '')
                    .map(img => ({
                        src: img,
                        type: 'current',
                        id: 'current_' + Math.random().toString(36).substr(2, 9)
                    }));
            }
            
            updateAllImages();
            actualizarImagenesExistentes();
        }

        // Función para agregar nuevos archivos
        function addNewFiles(files) {
            const newFileObjects = [];
            
            files.forEach(file => {
                const isDuplicate = selectedNewFiles.some(existingFile => 
                    existingFile.name === file.name && 
                    existingFile.size === file.size &&
                    existingFile.lastModified === file.lastModified
                );
                
                if (!isDuplicate) {
                    const newFileObj = {
                        file: file,
                        type: 'new',
                        id: 'new_' + Math.random().toString(36).substr(2, 9)
                    };
                    selectedNewFiles.push(newFileObj);
                    newFileObjects.push(newFileObj);
                }
            });
            
            // Agregar nuevos archivos al final del array principal
            allImages = allImages.concat(newFileObjects);
            
            displayAllImages();
            updateImageCounter();
            updateContainerState();
            updateFileInput();
        }

        // Función para actualizar todas las imágenes (combinar actuales y nuevas)
        function updateAllImages() {
            // Solo recombinar si allImages está vacío o si es la primera carga
            if (allImages.length === 0) {
                allImages = [...imagenesActuales, ...selectedNewFiles];
            }
            
            displayAllImages();
            updateImageCounter();
            updateContainerState();
        }

        // Función para mostrar todas las imágenes
        function displayAllImages() {
            const container = document.getElementById('main_images_container');
            const emptyState = document.getElementById('empty_state');
            
            if (allImages.length === 0) {
                emptyState.style.display = 'block';
                container.classList.remove('has-images');
                return;
            }
            
            emptyState.style.display = 'none';
            container.classList.add('has-images');
            
            // Limpiar contenedor excepto el estado vacío
            const existingItems = container.querySelectorAll('.image-preview-item');
            existingItems.forEach(item => item.remove());
            
            allImages.forEach((imageData, index) => {
                const previewItem = document.createElement('div');
                previewItem.className = 'image-preview-item';
                previewItem.draggable = true;
                previewItem.dataset.imageId = imageData.id;
                previewItem.dataset.imageType = imageData.type;
                previewItem.dataset.index = index;
                
                let imageSrc = '';
                if (imageData.type === 'current') {
                    const rutaImagen = imageData.src.startsWith('contenido/') ? `../../${imageData.src}` : `../../contenido/${imageData.src}`;
                    imageSrc = rutaImagen;
                } else {
                    // Para nuevas imágenes, crear URL temporal
                    imageSrc = URL.createObjectURL(imageData.file);
                }
                
                previewItem.innerHTML = `
                    <img src="${imageSrc}" 
                         alt="Imagen ${index + 1}" 
                         class="image-preview"
                         onerror="this.style.display='none'">
                    <button type="button" 
                            class="remove-image" 
                            onclick="removeImage('${imageData.id}')"
                            title="Eliminar imagen">×</button>
                    <div class="image-index">${index + 1}</div>
                    <div class="image-type-indicator ${imageData.type === 'current' ? 'image-type-current' : 'image-type-new'}"
                         title="${imageData.type === 'current' ? 'Imagen actual' : 'Nueva imagen'}">
                        ${imageData.type === 'current' ? '🖼️' : '🆕'}
                    </div>
                `;
                
                setupDragAndDrop(previewItem);
                container.appendChild(previewItem);
            });
        }

        // Configurar drag and drop para las imágenes (CORREGIDO)
        function setupDragAndDrop(element) {
            element.addEventListener('dragstart', function(e) {
                draggedElement = this;
                draggedIndex = parseInt(this.dataset.index);
                this.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', draggedIndex.toString());
                console.log('Drag start - Index:', draggedIndex);
            });

            element.addEventListener('dragend', function(e) {
                this.classList.remove('dragging');
                
                // Limpiar todos los indicadores de drag-over
                document.querySelectorAll('.image-preview-item').forEach(item => {
                    item.classList.remove('drag-over');
                });
                document.getElementById('main_images_container').classList.remove('drag-over');
                
                draggedElement = null;
                draggedIndex = -1;
            });

            element.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                
                if (this !== draggedElement && draggedElement) {
                    this.classList.add('drag-over');
                }
            });

            element.addEventListener('dragleave', function(e) {
                this.classList.remove('drag-over');
            });

            element.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                this.classList.remove('drag-over');
                
                if (this !== draggedElement && draggedElement) {
                    const targetIndex = parseInt(this.dataset.index);
                    const originalIndex = draggedIndex;
                    
                    console.log('Drop - From:', originalIndex, 'To:', targetIndex);
                    
                    // Verificar que los índices sean válidos
                    if (originalIndex >= 0 && targetIndex >= 0 && originalIndex !== targetIndex) {
                        // Crear una copia del array para manipular
                        const newImages = [...allImages];
                        
                        // Remover elemento de la posición original
                        const draggedImage = newImages.splice(originalIndex, 1)[0];
                        
                        // Insertar en la nueva posición
                        newImages.splice(targetIndex, 0, draggedImage);
                        
                        // Actualizar el array principal
                        allImages = newImages;
                        
                        // Actualizar arrays individuales
                        updateIndividualArrays();
                        
                        // Mostrar imágenes actualizadas
                        displayAllImages();
                        
                        // Actualizar campos ocultos
                        actualizarImagenesExistentes();
                        actualizarOrdenImagenes();
                        
                        showTempMessage('Orden de imágenes actualizado', 'success');
                        console.log('Reorder completed');
                    }
                }
            });
        }

        // Función para actualizar arrays individuales después del reordenamiento
        function updateIndividualArrays() {
            // Limpiar arrays individuales
            imagenesActuales = [];
            selectedNewFiles = [];
            
            // Redistribuir elementos según su tipo
            allImages.forEach(image => {
                if (image.type === 'current') {
                    imagenesActuales.push(image);
                } else if (image.type === 'new') {
                    selectedNewFiles.push(image);
                }
            });
        }

        // Configurar zona de drop para archivos
        function setupFileDropZone() {
            const container = document.getElementById('main_images_container');
            const uploadSection = document.querySelector('.file-upload-section');
            
            [container, uploadSection].forEach(zone => {
                zone.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Solo activar drag-over si no estamos arrastrando una imagen interna
                    if (!draggedElement) {
                        this.classList.add('drag-over');
                    }
                });
                
                zone.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (!this.contains(e.relatedTarget) && !draggedElement) {
                        this.classList.remove('drag-over');
                    }
                });
                
                zone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.classList.remove('drag-over');
                    
                    // Solo procesar archivos si no estamos reorganizando imágenes internas
                    if (!draggedElement && e.dataTransfer.files.length > 0) {
                        const files = Array.from(e.dataTransfer.files);
                        const imageFiles = files.filter(file => file.type.startsWith('image/'));
                        
                        if (imageFiles.length > 0) {
                            addNewFiles(imageFiles);
                            showTempMessage(`${imageFiles.length} imagen${imageFiles.length !== 1 ? 'es' : ''} agregada${imageFiles.length !== 1 ? 's' : ''}`, 'success');
                        }
                    }
                });
            });
        }

        // Función para eliminar una imagen
        function removeImage(imageId) {
            if (confirm('¿Está seguro de que desea eliminar esta imagen?')) {
                const imageIndex = allImages.findIndex(img => img.id === imageId);
                
                if (imageIndex !== -1) {
                    const imageData = allImages[imageIndex];
                    
                    // Si es una imagen actual, eliminar del servidor
                    if (imageData.type === 'current') {
                        fetch('../bd/eliminarimagen.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                rutaImagen: imageData.src
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                console.log('Imagen eliminada físicamente:', data.message);
                            } else {
                                console.warn('Advertencia al eliminar imagen física:', data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error al eliminar imagen física:', error);
                        });
                        
                        // Eliminar de imagenesActuales
                        const currentIndex = imagenesActuales.findIndex(img => img.id === imageId);
                        if (currentIndex !== -1) {
                            imagenesActuales.splice(currentIndex, 1);
                        }
                    } else {
                        // Si es una imagen nueva, eliminar de selectedNewFiles
                        const newIndex = selectedNewFiles.findIndex(img => img.id === imageId);
                        if (newIndex !== -1) {
                            selectedNewFiles.splice(newIndex, 1);
                        }
                    }
                    
                    // Eliminar del array principal
                    allImages.splice(imageIndex, 1);
                    
                    // Actualizar vista
                    displayAllImages();
                    updateImageCounter();
                    updateContainerState();
                    actualizarImagenesExistentes();
                    actualizarOrdenImagenes();
                    updateFileInput();
                    showTempMessage('Imagen eliminada correctamente', 'success');
                }
            }
        }

        // Función para actualizar el input de archivos
        function updateFileInput() {
            const fileInput = document.getElementById('nuevas_imagenes_input');
            const dt = new DataTransfer();
            
            selectedNewFiles.forEach(fileData => {
                dt.items.add(fileData.file);
            });
            
            fileInput.files = dt.files;
        }

        // Función para actualizar el contador de imágenes
        function updateImageCounter() {
            const counter = document.getElementById('image_counter');
            const totalImages = allImages.length;
            const currentImages = imagenesActuales.length;
            const newImages = selectedNewFiles.length;
            
            if (totalImages === 0) {
                counter.innerHTML = 'Sin imágenes';
            } else {
                counter.innerHTML = `
                    <div>${totalImages} imagen${totalImages !== 1 ? 'es' : ''} total${totalImages !== 1 ? 'es' : ''}</div>
                    <div style="font-size: 0.8em; opacity: 0.8;">
                        ${currentImages} actual${currentImages !== 1 ? 'es' : ''} • ${newImages} nueva${newImages !== 1 ? 's' : ''}
                    </div>
                `;
            }
        }

        // Función para actualizar estado del contenedor
        function updateContainerState() {
            const container = document.getElementById('main_images_container');
            if (allImages.length > 0) {
                container.classList.add('has-images');
            } else {
                container.classList.remove('has-images');
            }
        }

        // Función para actualizar el campo oculto de imágenes existentes
        function actualizarImagenesExistentes() {
            const imagenesActualesString = imagenesActuales
                .filter(img => img !== null)
                .map(img => img.src)
                .join(',');
            document.getElementById('imagenes_existentes').value = imagenesActualesString;
        }

        // Función para actualizar el orden de imágenes
        function actualizarOrdenImagenes() {
            const ordenIds = allImages.map(img => img.id);
            document.getElementById('orden_imagenes').value = ordenIds.join(',');
        }

        // Función para limpiar completamente el formulario
        function limpiarFormulario() {
            // Limpiar campos del formulario
            document.getElementById('id_noticia').value = '';
            document.getElementById('titulo').value = '';
            document.getElementById('contenido').value = '';
            document.getElementById('fecha').value = '';
            document.getElementById('imagenes_existentes').value = '';
            document.getElementById('orden_imagenes').value = '';
            document.getElementById('destacada').checked = false;
            
            // Limpiar información de la noticia
            document.getElementById('noticia_info').textContent = '';
            
            // Limpiar todas las imágenes
            imagenesActuales = [];
            selectedNewFiles = [];
            allImages = [];
            
            // Limpiar inputs de archivos
            document.getElementById('nuevas_imagenes_input').value = '';
            
            // Actualizar vista
            updateAllImages();
            
            // Resetear selector y ocultar formulario
            document.getElementById('selector_noticia').value = '';
            ocultarFormulario();
            
            showTempMessage('Formulario limpiado correctamente', 'success');
        }

        // Función para mostrar mensajes temporales
        function showTempMessage(mensaje, tipo = 'success') {
            // Remover mensajes existentes
            const existingMessages = document.querySelectorAll('.temp-message');
            existingMessages.forEach(msg => msg.remove());
            
            const messageDiv = document.createElement('div');
            messageDiv.className = `temp-message ${tipo}`;
            messageDiv.textContent = mensaje;
            
            document.body.appendChild(messageDiv);
            
            setTimeout(() => {
                messageDiv.remove();
            }, 4000);
            
            // También actualizar el mensaje principal
            const mensajeDiv = document.getElementById('mensaje_carga');
            mensajeDiv.innerHTML = `<div class="${tipo}">${mensaje}</div>`;
            setTimeout(() => mensajeDiv.innerHTML = '', 5000);
        }

        // Función para mostrar el formulario
        function mostrarFormulario() {
            document.getElementById('formulario_edicion').classList.remove('form-hidden');
        }

        // Función para ocultar el formulario
        function ocultarFormulario() {
            document.getElementById('formulario_edicion').classList.add('form-hidden');
        }

        // Función para enviar el formulario
        function enviarFormulario() {
            const formData = new FormData(document.getElementById('form_editar_noticia'));
            const btnSubmit = document.querySelector('button[type="submit"]');
            const textoOriginal = btnSubmit.textContent;
            
            // Agregar archivos de nuevas imágenes al FormData
            selectedNewFiles.forEach((fileData, index) => {
                formData.append(`nuevas_imagenes[${index}]`, fileData.file);
            });
            
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Guardando...';
            
            fetch('../bd/editarnoticia.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showTempMessage('Noticia actualizada correctamente', 'success');
                    
                    setTimeout(() => {
                        limpiarFormulario();
                    }, 1500);
                    
                    window.scrollTo(0, 0);
                } else {
                    showTempMessage(`Error: ${data.message}`, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showTempMessage('Error al actualizar la noticia', 'error');
            })
            .finally(() => {
                btnSubmit.disabled = false;
                btnSubmit.textContent = textoOriginal;
            });
        }
    </script>
</body>
</html>