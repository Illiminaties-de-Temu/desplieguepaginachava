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
            padding: 15px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            border: 2px dashed transparent;
            transition: all 0.3s ease;
            min-height: 120px;
        }
        
        .image-preview-container.drag-over {
            border-color: #3498db;
            background: linear-gradient(135deg, #e3f2fd 0%, #f3f8ff 100%);
            transform: scale(1.02);
        }
        
        .image-preview-item {
            position: relative;
            display: inline-block;
            cursor: grab;
            transition: all 0.3s ease;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .image-preview-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .image-preview-item.dragging {
            opacity: 0.5;
            transform: rotate(5deg) scale(0.9);
            cursor: grabbing;
            z-index: 1000;
        }
        
        .image-preview-item.drag-over {
            transform: translateX(10px);
            box-shadow: 0 8px 25px rgba(52, 152, 219, 0.3);
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
            width: 28px;
            height: 28px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            line-height: 1;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
        }
        
        .remove-image:hover {
            background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
            transform: scale(1.2);
            box-shadow: 0 6px 18px rgba(231, 76, 60, 0.5);
        }

        .image-index {
            position: absolute;
            bottom: -8px;
            left: -8px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
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

        .clear-images-btn {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
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
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.2);
        }

        .clear-images-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(231, 76, 60, 0.4);
            background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
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

        /* Separación de imágenes actuales y nuevas */
        .current-images-section {
            background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%);
            border: 2px solid #4caf50;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .current-images-section h3 {
            color: #2e7d32;
            margin-bottom: 15px;
            font-size: 1.2em;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .current-images-section h3::before {
            content: '🖼️';
            font-size: 1.1em;
        }

        .new-images-section {
            background: linear-gradient(135deg, #e3f2fd 0%, #f3f8ff 100%);
            border: 2px solid #2196f3;
            border-radius: 12px;
            padding: 20px;
        }

        .new-images-section h3 {
            color: #1976d2;
            margin-bottom: 15px;
            font-size: 1.2em;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .new-images-section h3::before {
            content: '➕';
            font-size: 1.1em;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #7f8c8d;
            font-style: italic;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .image-preview-container {
                gap: 10px;
                padding: 10px;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="panel.php" class="logout-btn">← Regresar</a>
            <h1>Editar Noticia</h1>
            <div class="user-info">
                Usuario: Editor | Tipo: editor
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
                        
                        <!-- Imágenes actuales -->
                        <div class="current-images-section">
                            <h3>Imágenes Actuales</h3>
                            <div id="imagenes_actuales_container">
                                <div class="empty-state" id="empty_current_images">
                                    No hay imágenes actuales para mostrar
                                </div>
                                <div id="current_images_preview" class="image-preview-container"></div>
                            </div>
                        </div>
                        
                        <!-- Sección para agregar nuevas imágenes -->
                        <div class="new-images-section">
                            <h3>Agregar Nuevas Imágenes</h3>
                            
                            <div class="file-upload-section" id="newImagesUploadSection" onclick="document.getElementById('nuevas_imagenes_input').click()">
                                <input type="file" 
                                       name="nuevas_imagenes[]" 
                                       id="nuevas_imagenes_input" 
                                       class="hidden-file-input"
                                       multiple 
                                       accept="image/*">
                                <div class="file-upload-content">
                                    <div class="file-upload-icon">📸</div>
                                    <div class="file-upload-text">Haz clic aquí para agregar imágenes</div>
                                    <div class="file-upload-hint">Puedes seleccionar múltiples archivos de imagen</div>
                                </div>
                            </div>
                            
                            <div id="new_images_preview_container">
                                <div class="empty-state" id="empty_new_images">
                                    No hay nuevas imágenes seleccionadas
                                </div>
                                <div id="new_images_preview" class="image-preview-container"></div>
                                
                                <div class="image-actions" id="new_image_actions" style="display: none;">
                                    <button type="button" class="add-more-btn" onclick="document.getElementById('additional_images_input').click()">
                                        ➕ Agregar más imágenes
                                    </button>
                                    <div class="image-counter" id="new_image_counter">0 imágenes nuevas</div>
                                    <button type="button" class="clear-images-btn" onclick="clearAllNewImages()">
                                        🗑️ Limpiar nuevas
                                    </button>
                                </div>
                            </div>
                            
                            <input type="file" 
                                   id="additional_images_input" 
                                   multiple 
                                   accept="image/*" 
                                   style="display: none;">
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
        let currentImageOrder = [];

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
                selectedNewFiles = files;
                displayNewImagePreviews();
                updateNewImageCounter();
                toggleNewImageActions();
            });

            // Event listener para imágenes adicionales
            document.getElementById('additional_images_input').addEventListener('change', function(e) {
                const newFiles = Array.from(e.target.files);
                
                newFiles.forEach(file => {
                    const isDuplicate = selectedNewFiles.some(existingFile => 
                        existingFile.name === file.name && 
                        existingFile.size === file.size &&
                        existingFile.lastModified === file.lastModified
                    );
                    
                    if (!isDuplicate) {
                        selectedNewFiles.push(file);
                    }
                });
                
                updateNewFileInput();
                displayNewImagePreviews();
                updateNewImageCounter();
                
                this.value = '';
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
        }

        // Función para cargar la lista de noticias
        function cargarListaNoticias() {
            const selector = document.getElementById('selector_noticia');
            const mensaje = document.getElementById('mensaje_carga');
            
            mensaje.innerHTML = '<div class="loading">Cargando noticias...</div>';
            
            // Simular datos de noticias para la demo
            const noticiasDemo = [
                {id: 1, Titulo: "Primera noticia de prueba", fecha: "2024-01-15"},
                {id: 2, Titulo: "Segunda noticia importante", fecha: "2024-01-16"},
                {id: 3, Titulo: "Noticia destacada del día", fecha: "2024-01-17"}
            ];
            
            setTimeout(() => {
                noticias = noticiasDemo;
                selector.innerHTML = '<option value="">-- Seleccionar una noticia --</option>';
                
                noticiasDemo.forEach(noticia => {
                    const option = document.createElement('option');
                    option.value = noticia.id;
                    option.textContent = `#${noticia.id} - ${noticia.Titulo} - ${noticia.fecha}`;
                    selector.appendChild(option);
                });
                
                mensaje.innerHTML = '<div class="success">Noticias cargadas correctamente</div>';
                setTimeout(() => mensaje.innerHTML = '', 3000);
            }, 1000);
        }

        // Función para cargar una noticia específica
        function cargarNoticia(id) {
            const mensaje = document.getElementById('mensaje_carga');
    
            mensaje.innerHTML = '<div class="loading">Cargando noticia...</div>';
    
            // Simular carga de datos para la demo
            const noticiaDemo = {
                id: id,
                Titulo: `Noticia de ejemplo #${id}`,
                Contenido: "Este es el contenido de ejemplo de la noticia. Aquí iría el texto completo.",
                fecha: "2024-01-15",
                Destacada: id === "2" ? "si" : "no",
                Imagenes: "imagen1.jpg,imagen2.jpg,imagen3.jpg"
            };
            
            setTimeout(() => {
                noticiaActual = noticiaDemo;
                llenarFormulario(noticiaDemo);
                mostrarFormulario();
        
                mensaje.innerHTML = '<div class="success">Noticia cargada correctamente</div>';
                setTimeout(() => mensaje.innerHTML = '', 3000);
            }, 800);
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
            clearAllNewImages();
        }

        // Función para cargar las imágenes actuales
        function cargarImagenesActuales(imagenes) {
            const container = document.getElementById('current_images_preview');
            const emptyState = document.getElementById('empty_current_images');
            
            if (!imagenes || imagenes.trim() === '') {
                emptyState.style.display = 'block';
                container.style.display = 'none';
                imagenesActuales = [];
                currentImageOrder = [];
                actualizarImagenesExistentes();
                return;
            }
            
            imagenesActuales = imagenes.split(',').map(img => img.trim()).filter(img => img !== '');
            currentImageOrder = imagenesActuales.map((_, index) => index);
            
            emptyState.style.display = 'none';
            container.style.display = 'flex';
            
            displayCurrentImages();
            actualizarImagenesExistentes();
        }

        // Función para mostrar imágenes actuales con drag & drop
        function displayCurrentImages() {
            const container = document.getElementById('current_images_preview');
            container.innerHTML = '';
            
            currentImageOrder.forEach((imageIndex, position) => {
                const imagen = imagenesActuales[imageIndex];
                if (!imagen) return;
                
                const rutaImagen = imagen.startsWith('contenido/') ? `../../${imagen}` : `../../contenido/${imagen}`;
                
                const previewItem = document.createElement('div');
                previewItem.className = 'image-preview-item';
                previewItem.draggable = true;
                previewItem.dataset.originalIndex = imageIndex;
                previewItem.dataset.currentPosition = position;
                
                previewItem.innerHTML = `
                    <img src="https://picsum.photos/300/300?random=${imageIndex}" 
                         alt="Imagen ${imageIndex + 1}" 
                         class="image-preview">
                    <button type="button" 
                            class="remove-image" 
                            onclick="eliminarImagen(${imageIndex})"
                            title="Eliminar imagen">×</button>
                    <div class="image-index">${position + 1}</div>
                `;
                
                setupDragAndDrop(previewItem);
                container.appendChild(previewItem);
            });
        }

        // Configurar drag and drop para las imágenes
        function setupDragAndDrop(element) {
            element.addEventListener('dragstart', function(e) {
                draggedElement = this;
                this.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/html', this.outerHTML);
            });

            element.addEventListener('dragend', function() {
                this.classList.remove('dragging');
                draggedElement = null;
                
                // Limpiar todos los indicadores de drag-over
                document.querySelectorAll('.image-preview-item').forEach(item => {
                    item.classList.remove('drag-over');
                });
            });

            element.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                
                if (this !== draggedElement) {
                    this.classList.add('drag-over');
                }
            });

            element.addEventListener('dragleave', function() {
                this.classList.remove('drag-over');
            });

            element.addEventListener('drop', function(e) {
                e.preventDefault();
                
                if (this !== draggedElement) {
                    const draggedPosition = parseInt(draggedElement.dataset.currentPosition);
                    const targetPosition = parseInt(this.dataset.currentPosition);
                    
                    // Reordenar el array
                    const draggedIndex = currentImageOrder[draggedPosition];
                    currentImageOrder.splice(draggedPosition, 1);
                    currentImageOrder.splice(targetPosition, 0, draggedIndex);
                    
                    // Actualizar la vista
                    displayCurrentImages();
                    actualizarOrdenImagenes();
                    showTempMessage('Orden de imágenes actualizado', 'success');
                }
                
                this.classList.remove('drag-over');
            });
        }

        // Función para mostrar vista previa de nuevas imágenes
        function displayNewImagePreviews() {
            const container = document.getElementById('new_images_preview');
            const emptyState = document.getElementById('empty_new_images');
            
            if (selectedNewFiles.length === 0) {
                emptyState.style.display = 'block';
                container.style.display = 'none';
                return;
            }
            
            emptyState.style.display = 'none';
            container.style.display = 'flex';
            container.innerHTML = '';

            selectedNewFiles.forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'image-preview-item';
                        previewItem.draggable = true;
                        previewItem.dataset.fileIndex = index;
                        
                        previewItem.innerHTML = `
                            <img src="${e.target.result}" 
                                 class="image-preview" 
                                 alt="Vista previa">
                            <button type="button" 
                                    class="remove-image" 
                                    onclick="removeNewImage(${index})"
                                    title="Eliminar imagen">×</button>
                            <div class="image-index">${index + 1}</div>
                        `;
                        
                        setupNewImageDragAndDrop(previewItem);
                        container.appendChild(previewItem);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // Configurar drag and drop para nuevas imágenes
        function setupNewImageDragAndDrop(element) {
            element.addEventListener('dragstart', function(e) {
                draggedElement = this;
                this.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
            });

            element.addEventListener('dragend', function() {
                this.classList.remove('dragging');
                draggedElement = null;
                
                document.querySelectorAll('.image-preview-item').forEach(item => {
                    item.classList.remove('drag-over');
                });
            });

            element.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                
                if (this !== draggedElement) {
                    this.classList.add('drag-over');
                }
            });

            element.addEventListener('dragleave', function() {
                this.classList.remove('drag-over');
            });

            element.addEventListener('drop', function(e) {
                e.preventDefault();
                
                if (this !== draggedElement) {
                    const draggedIndex = parseInt(draggedElement.dataset.fileIndex);
                    const targetIndex = parseInt(this.dataset.fileIndex);
                    
                    // Reordenar archivos
                    const draggedFile = selectedNewFiles[draggedIndex];
                    selectedNewFiles.splice(draggedIndex, 1);
                    selectedNewFiles.splice(targetIndex, 0, draggedFile);
                    
                    // Actualizar input y vista
                    updateNewFileInput();
                    displayNewImagePreviews();
                    showTempMessage('Orden de nuevas imágenes actualizado', 'success');
                }
                
                this.classList.remove('drag-over');
            });
        }

        // Función para actualizar el campo oculto de imágenes existentes
        function actualizarImagenesExistentes() {
            const imagenesOrdenadas = currentImageOrder.map(index => imagenesActuales[index]).filter(img => img);
            document.getElementById('imagenes_existentes').value = imagenesOrdenadas.join(',');
        }

        // Función para actualizar el orden de imágenes
        function actualizarOrdenImagenes() {
            document.getElementById('orden_imagenes').value = currentImageOrder.join(',');
        }

        // Función para eliminar una imagen actual
        function eliminarImagen(originalIndex) {
            if (confirm('¿Está seguro de que desea eliminar esta imagen?')) {
                // Encontrar la posición actual de esta imagen
                const currentPosition = currentImageOrder.indexOf(originalIndex);
                
                if (currentPosition !== -1) {
                    // Eliminar de la ordenación actual
                    currentImageOrder.splice(currentPosition, 1);
                }
                
                // Eliminar de las imágenes actuales
                imagenesActuales[originalIndex] = null;
                
                // Actualizar la vista
                displayCurrentImages();
                actualizarImagenesExistentes();
                actualizarOrdenImagenes();
                
                showTempMessage('Imagen eliminada correctamente', 'success');
                
                // Si no quedan imágenes, mostrar estado vacío
                if (currentImageOrder.length === 0) {
                    document.getElementById('empty_current_images').style.display = 'block';
                    document.getElementById('current_images_preview').style.display = 'none';
                }
            }
        }

        // Función para eliminar una nueva imagen
        function removeNewImage(index) {
            selectedNewFiles.splice(index, 1);
            updateNewFileInput();
            displayNewImagePreviews();
            updateNewImageCounter();
            toggleNewImageActions();
            showTempMessage('Nueva imagen eliminada', 'success');
        }

        // Función para actualizar el input de nuevas imágenes
        function updateNewFileInput() {
            const fileInput = document.getElementById('nuevas_imagenes_input');
            const dt = new DataTransfer();
            
            selectedNewFiles.forEach(file => {
                dt.items.add(file);
            });
            
            fileInput.files = dt.files;
        }

        // Función para actualizar contador de nuevas imágenes
        function updateNewImageCounter() {
            const counter = document.getElementById('new_image_counter');
            const count = selectedNewFiles.length;
            counter.textContent = `${count} imagen${count !== 1 ? 'es' : ''} nueva${count !== 1 ? 's' : ''}`;
        }

        // Función para mostrar/ocultar acciones de nuevas imágenes
        function toggleNewImageActions() {
            const actions = document.getElementById('new_image_actions');
            actions.style.display = selectedNewFiles.length > 0 ? 'flex' : 'none';
        }

        // Función para limpiar todas las nuevas imágenes
        function clearAllNewImages() {
            selectedNewFiles = [];
            document.getElementById('nuevas_imagenes_input').value = '';
            document.getElementById('additional_images_input').value = '';
            displayNewImagePreviews();
            updateNewImageCounter();
            toggleNewImageActions();
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
            
            // Limpiar imágenes actuales
            imagenesActuales = [];
            currentImageOrder = [];
            document.getElementById('empty_current_images').style.display = 'block';
            document.getElementById('current_images_preview').style.display = 'none';
            
            // Limpiar nuevas imágenes
            clearAllNewImages();
            
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
            clearAllNewImages();
        }

        // Función para enviar el formulario
        function enviarFormulario() {
            const formData = new FormData(document.getElementById('form_editar_noticia'));
            const btnSubmit = document.querySelector('button[type="submit"]');
            const textoOriginal = btnSubmit.textContent;
            
            // Agregar archivos de nuevas imágenes
            selectedNewFiles.forEach((file, index) => {
                formData.append(`nuevas_imagenes[${index}]`, file);
            });
            
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Guardando...';
            
            // Simular envío para la demo
            setTimeout(() => {
                showTempMessage('Noticia actualizada correctamente', 'success');
                
                setTimeout(() => {
                    limpiarFormulario();
                }, 1500);
                
                window.scrollTo(0, 0);
                
                btnSubmit.disabled = false;
                btnSubmit.textContent = textoOriginal;
            }, 2000);
        }

        // Configurar drag and drop para el contenedor de imágenes
        document.addEventListener('DOMContentLoaded', function() {
            const containers = [
                document.getElementById('current_images_preview'),
                document.getElementById('new_images_preview')
            ];
            
            containers.forEach(container => {
                container.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    this.classList.add('drag-over');
                });
                
                container.addEventListener('dragleave', function(e) {
                    if (!this.contains(e.relatedTarget)) {
                        this.classList.remove('drag-over');
                    }
                });
                
                container.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.classList.remove('drag-over');
                });
            });
        });
    </script>
</body>
</html>