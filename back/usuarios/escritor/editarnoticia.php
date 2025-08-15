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
        .new-image-preview-container {
            margin-top: 15px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            padding: 15px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            border: 2px dashed #dee2e6;
            transition: all 0.3s ease;
        }
        
        .new-image-preview-item {
            position: relative;
            display: flex;
            flex-direction: column;
            background: white;
            border-radius: 8px;
            padding: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            cursor: grab;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .new-image-preview-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            border-color: #3498db;
        }

        .new-image-preview-item.dragging {
            opacity: 0.5;
            transform: rotate(5deg);
            cursor: grabbing;
            z-index: 1000;
        }

        .new-image-preview-item.drag-over {
            border-color: #27ae60;
            background: #e8f5e8;
            transform: scale(1.05);
        }
        
        .new-image-preview {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 6px;
            margin-bottom: 8px;
        }
        
        .image-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .image-order {
            background: #3498db;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 10px;
        }

        .image-name {
            flex: 1;
            margin-left: 5px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .remove-new-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            font-size: 12px;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            z-index: 10;
        }
        
        .remove-new-image:hover {
            background: #c0392b;
            transform: scale(1.1);
        }

        /* Estilos para el área de carga de archivos mejorada */
        .file-upload-section {
            border: 2px dashed #3498db;
            border-radius: 12px;
            padding: 25px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .file-upload-section:hover {
            border-color: #2980b9;
            background: linear-gradient(135deg, #ffffff 0%, #f1f3f4 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(52, 152, 219, 0.15);
        }

        .file-upload-content {
            position: relative;
            z-index: 2;
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

        .file-input-wrapper input[type="file"] {
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
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            border: 1px solid #dee2e6;
        }

        .add-more-btn {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
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
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.2);
        }

        .add-more-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(39, 174, 96, 0.4);
            background: linear-gradient(135deg, #229954 0%, #27ae60 100%);
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

        .drag-instructions {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 15px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(243, 156, 18, 0.2);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
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

        /* Responsive */
        @media (max-width: 768px) {
            .new-image-preview-container {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
                gap: 10px;
                padding: 10px;
            }
            
            .new-image-preview {
                height: 100px;
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
            .new-image-preview-container {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            }
            
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
                                <input type="text" name="titulo" id="titulo" >
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
                        <h2>Imágenes</h2>
                        
                        <div id="imagenes_actuales">
                            <!-- Las imágenes actuales se cargarán aquí -->
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="nuevas_imagenes">Agregar nuevas imágenes:</label>
                                
                                <!-- Sección inicial de carga -->
                                <div class="file-upload-section" id="initialUploadSection">
                                    <input type="file" name="nuevas_imagenes[]" id="nuevas_imagenes" multiple accept="image/*">
                                    <div class="file-upload-content">
                                        <div class="file-upload-text">Haz clic aquí para seleccionar imágenes</div>
                                        <div class="file-upload-hint">Puedes seleccionar múltiples archivos de imagen</div>
                                    </div>
                                </div>
                                
                                <!-- Área de vista previa con funciones de arrastrar -->
                                <div id="newImagePreviewContainer" class="new-image-preview-container" style="display: none;">
                                    <div class="drag-instructions">
                                        📋 Arrastra las imágenes para cambiar su orden
                                    </div>
                                </div>
                                
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

        // Cargar lista de noticias al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            cargarListaNoticias();
        });

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

        // Event listener para el selector de noticias - Cambio automático
        document.getElementById('selector_noticia').addEventListener('change', function() {
            const idNoticia = this.value;
            if (idNoticia) {
                cargarNoticia(idNoticia);
            } else {
                ocultarFormulario();
            }
        });

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
            // Establecer estado del checkbox destacada
            document.getElementById('destacada').checked = noticia.Destacada === 'si';
            
            // Actualizar información de la noticia
            document.getElementById('noticia_info').textContent = 
                `ID #${noticia.id} - Creada el ${noticia.fecha}`;
            
            // Cargar imágenes actuales
            cargarImagenesActuales(noticia.Imagenes);
            
            // Limpiar vista previa de nuevas imágenes
            clearNewImagePreviews();
        }

        // Función para cargar las imágenes actuales
        function cargarImagenesActuales(imagenes) {
            const container = document.getElementById('imagenes_actuales');
            
            if (!imagenes || imagenes.trim() === '') {
                container.innerHTML = '<p>No hay imágenes asociadas a esta noticia.</p>';
                imagenesActuales = [];
                actualizarImagenesExistentes();
                return;
            }
            
            imagenesActuales = imagenes.split(',').map(img => img.trim()).filter(img => img !== '');
            let html = '<h3>Imágenes actuales:</h3><div class="current-images">';
            
            imagenesActuales.forEach((imagen, index) => {
                const rutaImagen = imagen.startsWith('contenido/') ? `../../${imagen}` : `../../contenido/${imagen}`;
                
                html += `
                    <div class="image-item" id="imagen_${index}">
                        <img src="${rutaImagen}" alt="Imagen ${index + 1}" onerror="this.style.display='none'">
                        <div class="image-actions">
                            <button type="button" class="btn-small btn-danger" onclick="eliminarImagen(${index})">×</button>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
            actualizarImagenesExistentes();
        }

        // Función para actualizar el campo oculto de imágenes existentes
        function actualizarImagenesExistentes() {
            document.getElementById('imagenes_existentes').value = imagenesActuales.join(',');
        }

        // Función para eliminar una imagen
        function eliminarImagen(index) {
            if (confirm('¿Está seguro de que desea eliminar esta imagen?')) {
                // Obtener la ruta de la imagen a eliminar
                const imagenEliminada = imagenesActuales[index];
                
                // Eliminar físicamente del servidor usando file_helper
                fetch('../bd/eliminarimagen.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        rutaImagen: imagenEliminada
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
                
                // Eliminar la imagen del array
                imagenesActuales.splice(index, 1);
                
                // Actualizar la vista
                document.getElementById(`imagen_${index}`).remove();
                
                // Actualizar el campo oculto
                actualizarImagenesExistentes();
                
                // Recargar la vista de imágenes para actualizar los índices
                cargarImagenesActuales(imagenesActuales.join(','));
                
                // Mostrar mensaje de confirmación
                mostrarMensaje('Imagen eliminada del servidor y de la noticia.', 'success');
            }
        }

        // Event listener para el input de nuevas imágenes
        document.getElementById('nuevas_imagenes').addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            selectedNewFiles = files;
            displayNewImagePreviews();
            updateImageCounter();
            toggleImageActions();
        });

        // Manejar la adición de más imágenes
        document.getElementById('additionalImages').addEventListener('change', function(e) {
            const newFiles = Array.from(e.target.files);
            
            // Agregar los nuevos archivos al array existente
            newFiles.forEach(file => {
                // Verificar que no sea un duplicado (opcional)
                const isDuplicate = selectedNewFiles.some(existingFile => 
                    existingFile.name === file.name && 
                    existingFile.size === file.size &&
                    existingFile.lastModified === file.lastModified
                );
                
                if (!isDuplicate) {
                    selectedNewFiles.push(file);
                }
            });
            
            // Actualizar el input principal con todos los archivos
            updateMainFileInput();
            displayNewImagePreviews();
            updateImageCounter();
            
            // Limpiar el input auxiliar
            this.value = '';
        });

        function addMoreImages() {
            document.getElementById('additionalImages').click();
        }

        // Función para mostrar vista previa de nuevas imágenes con drag & drop
        function displayNewImagePreviews() {
            const container = document.getElementById('newImagePreviewContainer');
            
            if (selectedNewFiles.length === 0) {
                container.style.display = 'none';
                return;
            }

            container.style.display = 'block';
            
            // Limpiar contenido anterior pero mantener las instrucciones
            const dragInstructions = container.querySelector('.drag-instructions');
            container.innerHTML = '';
            if (dragInstructions) {
                container.appendChild(dragInstructions);
            } else {
                const instructions = document.createElement('div');
                instructions.className = 'drag-instructions';
                instructions.innerHTML = '📋 Arrastra las imágenes para cambiar su orden';
                container.appendChild(instructions);
            }

            selectedNewFiles.forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'new-image-preview-item';
                        previewItem.draggable = true;
                        previewItem.dataset.index = index;
                        
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'new-image-preview';
                        img.alt = 'Vista previa';
                        
                        const imageInfo = document.createElement('div');
                        imageInfo.className = 'image-info';
                        
                        const orderBadge = document.createElement('div');
                        orderBadge.className = 'image-order';
                        orderBadge.textContent = index + 1;
                        
                        const imageName = document.createElement('div');
                        imageName.className = 'image-name';
                        imageName.textContent = file.name;
                        imageName.title = file.name;
                        
                        imageInfo.appendChild(orderBadge);
                        imageInfo.appendChild(imageName);
                        
                        const removeBtn = document.createElement('button');
                        removeBtn.innerHTML = '×';
                        removeBtn.className = 'remove-new-image';
                        removeBtn.type = 'button';
                        removeBtn.title = 'Eliminar imagen';
                        removeBtn.onclick = function(e) {
                            e.stopPropagation();
                            removeNewImage(index);
                        };
                        
                        // Eventos de drag & drop
                        previewItem.addEventListener('dragstart', handleDragStart);
                        previewItem.addEventListener('dragover', handleDragOver);
                        previewItem.addEventListener('drop', handleDrop);
                        previewItem.addEventListener('dragend', handleDragEnd);
                        previewItem.addEventListener('dragenter', handleDragEnter);
                        previewItem.addEventListener('dragleave', handleDragLeave);
                        
                        previewItem.appendChild(imageInfo);
                        previewItem.appendChild(img);
                        previewItem.appendChild(removeBtn);
                        container.appendChild(previewItem);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // Variables para el drag & drop
        let draggedElement = null;
        let draggedIndex = null;

        function handleDragStart(e) {
            draggedElement = this;
            draggedIndex = parseInt(this.dataset.index);
            this.classList.add('dragging');
            
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', this.outerHTML);
        }

        function handleDragOver(e) {
            if (e.preventDefault) {
                e.preventDefault();
            }
            e.dataTransfer.dropEffect = 'move';
            return false;
        }

        function handleDragEnter(e) {
            if (this !== draggedElement) {
                this.classList.add('drag-over');
            }
        }

        function handleDragLeave(e) {
            this.classList.remove('drag-over');
        }

        function handleDrop(e) {
            if (e.stopPropagation) {
                e.stopPropagation();
            }

            if (draggedElement !== this) {
                const targetIndex = parseInt(this.dataset.index);
                
                // Reordenar el array de archivos
                const draggedFile = selectedNewFiles[draggedIndex];
                selectedNewFiles.splice(draggedIndex, 1);
                selectedNewFiles.splice(targetIndex, 0, draggedFile);
                
                // Actualizar el input y la vista previa
                updateMainFileInput();
                displayNewImagePreviews();
            }

            this.classList.remove('drag-over');
            return false;
        }

        function handleDragEnd(e) {
            this.classList.remove('dragging');
            
            // Limpiar todos los estados de drag-over
            const items = document.querySelectorAll('.new-image-preview-item');
            items.forEach(item => {
                item.classList.remove('drag-over', 'dragging');
            });
        }

        // Función para eliminar una nueva imagen de la vista previa
        function removeNewImage(index) {
            selectedNewFiles.splice(index, 1);
            updateMainFileInput();
            displayNewImagePreviews();
            updateImageCounter();
            toggleImageActions();
        }

        // Función para actualizar el input de archivos con las nuevas imágenes
        function updateMainFileInput() {
            const fileInput = document.getElementById('nuevas_imagenes');
            const dt = new DataTransfer();
            
            selectedNewFiles.forEach(file => {
                dt.items.add(file);
            });
            
            fileInput.files = dt.files;
        }

        function updateImageCounter() {
            const counter = document.getElementById('imageCounter');
            const count = selectedNewFiles.length;
            counter.textContent = `${count} imagen${count !== 1 ? 'es' : ''} seleccionada${count !== 1 ? 's' : ''}`;
        }

        function toggleImageActions() {
            const actions = document.getElementById('imageActions');
            const initialUpload = document.getElementById('initialUploadSection');
            
            if (selectedNewFiles.length > 0) {
                actions.style.display = 'flex';
                initialUpload.style.display = 'none';
            } else {
                actions.style.display = 'none';
                initialUpload.style.display = 'block';
            }
        }

        // Función para limpiar vista previa de nuevas imágenes
        function clearNewImagePreviews() {
            selectedNewFiles = [];
            const container = document.getElementById('newImagePreviewContainer');
            container.innerHTML = '';
            container.style.display = 'none';
            
            const fileInput = document.getElementById('nuevas_imagenes');
            fileInput.value = '';
            
            document.getElementById('additionalImages').value = '';
            updateImageCounter();
            toggleImageActions();
        }

        // Función para limpiar completamente el formulario
        function limpiarFormulario() {
            // Limpiar campos del formulario
            document.getElementById('id_noticia').value = '';
            document.getElementById('titulo').value = '';
            document.getElementById('contenido').value = '';
            document.getElementById('fecha').value = '';
            document.getElementById('imagenes_existentes').value = '';
            document.getElementById('destacada').checked = false;
            
            // Limpiar información de la noticia
            document.getElementById('noticia_info').textContent = '';
            
            // Limpiar imágenes actuales
            document.getElementById('imagenes_actuales').innerHTML = '';
            imagenesActuales = [];
            
            // Limpiar vista previa de nuevas imágenes
            clearNewImagePreviews();
            
            // Resetear selector y ocultar formulario
            document.getElementById('selector_noticia').value = '';
            ocultarFormulario();
            
            // Mostrar mensaje de confirmación
            mostrarMensaje('Formulario limpiado correctamente', 'success');
        }

        // Función para mostrar mensajes
        function mostrarMensaje(mensaje, tipo = 'success') {
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
            clearNewImagePreviews();
        }

        // Event listener para el botón limpiar formulario
        document.getElementById('btn_limpiar').addEventListener('click', function() {
            if (confirm('¿Está seguro de que desea limpiar el formulario? Se perderán los cambios no guardados.')) {
                limpiarFormulario();
            }
        });

        // Event listener para el envío del formulario
        document.getElementById('form_editar_noticia').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const btnSubmit = this.querySelector('button[type="submit"]');
            const textoOriginal = btnSubmit.textContent;
            
            // Guardar la posición actual del scroll
            const scrollPosition = window.pageYOffset || document.documentElement.scrollTop;
            
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Guardando...';
            
            fetch('../bd/editarnoticia.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarMensaje('Noticia actualizada correctamente', 'success');
                    
                    // Limpiar completamente el formulario después del éxito
                    setTimeout(() => {
                        limpiarFormulario();
                    }, 1500);
                    
                    // Volver al inicio de la página inmediatamente
                    window.scrollTo(0, 0);
                } else {
                    mostrarMensaje(`Error: ${data.message}`, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarMensaje('Error al actualizar la noticia', 'error');
            })
            .finally(() => {
                btnSubmit.disabled = false;
                btnSubmit.textContent = textoOriginal;
            });
        });

        // Drag and drop para la zona de carga inicial
        const initialUploadSection = document.getElementById('initialUploadSection');
        
        if (initialUploadSection) {
            initialUploadSection.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.style.background = 'linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%)';
                this.style.borderColor = '#2196f3';
            });
            
            initialUploadSection.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.style.background = 'linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%)';
                this.style.borderColor = '#3498db';
            });
            
            initialUploadSection.addEventListener('drop', function(e) {
                e.preventDefault();
                this.style.background = 'linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%)';
                this.style.borderColor = '#3498db';
                
                const files = Array.from(e.dataTransfer.files);
                selectedNewFiles = files.filter(file => file.type.startsWith('image/'));
                updateMainFileInput();
                displayNewImagePreviews();
                updateImageCounter();
                toggleImageActions();
            });
        }

        // Inicializar contador y acciones al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            updateImageCounter();
            toggleImageActions();
        });

        // Prevenir el envío del formulario si hay cambios sin guardar
        document.getElementById('form_editar_noticia').addEventListener('submit', function(e) {
            const hasImages = selectedNewFiles.length > 0 || imagenesActuales.length > 0;
            
            if (!hasImages) {
                const confirmSubmit = confirm('Esta noticia no tiene imágenes asociadas. ¿Está seguro de que desea continuar?');
                if (!confirmSubmit) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    </script>
</body>
</html>