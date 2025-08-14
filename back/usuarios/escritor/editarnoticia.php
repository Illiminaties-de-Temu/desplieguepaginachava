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
            margin-top: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .new-image-preview-item {
            position: relative;
            display: inline-block;
        }
        
        .new-image-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border: 2px solid #ddd;
            border-radius: 5px;
        }
        
        .remove-new-image {
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
        
        .remove-new-image:hover {
            background: #cc0000;
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
                                <div class="file-input-wrapper">
                                    <input type="file" name="nuevas_imagenes[]" id="nuevas_imagenes" multiple accept="image/*">
                                    <span class="file-input-display">
                                        Seleccionar archivos de imagen o arrastra aquí
                                    </span>
                                </div>
                                <div id="newImagePreviewContainer" class="new-image-preview-container"></div>
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
            
            const display = document.querySelector('.file-input-display');
            if (files.length > 0) {
                display.textContent = `${files.length} archivo(s) seleccionado(s)`;
                display.style.background = '#e8f5e8';
                display.style.color = '#27ae60';
            } else {
                display.textContent = 'Seleccionar archivos de imagen o arrastra aquí';
                display.style.background = '#f8f9fa';
                display.style.color = '#3498db';
            }
        });

        // Función para mostrar vista previa de nuevas imágenes
        function displayNewImagePreviews() {
            const container = document.getElementById('newImagePreviewContainer');
            container.innerHTML = '';

            selectedNewFiles.forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'new-image-preview-item';
                        
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'new-image-preview';
                        img.alt = 'Vista previa';
                        
                        const removeBtn = document.createElement('button');
                        removeBtn.innerHTML = '×';
                        removeBtn.className = 'remove-new-image';
                        removeBtn.type = 'button';
                        removeBtn.onclick = function() {
                            removeNewImage(index);
                        };
                        
                        previewItem.appendChild(img);
                        previewItem.appendChild(removeBtn);
                        container.appendChild(previewItem);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // Función para eliminar una nueva imagen de la vista previa
        function removeNewImage(index) {
            selectedNewFiles.splice(index, 1);
            updateNewFileInput();
            displayNewImagePreviews();
        }

        // Función para actualizar el input de archivos con las nuevas imágenes
        function updateNewFileInput() {
            const fileInput = document.getElementById('nuevas_imagenes');
            const dt = new DataTransfer();
            
            selectedNewFiles.forEach(file => {
                dt.items.add(file);
            });
            
            fileInput.files = dt.files;
            
            // Actualizar display
            const display = document.querySelector('.file-input-display');
            if (selectedNewFiles.length > 0) {
                display.textContent = `${selectedNewFiles.length} archivo(s) seleccionado(s)`;
                display.style.background = '#e8f5e8';
                display.style.color = '#27ae60';
            } else {
                display.textContent = 'Seleccionar archivos de imagen o arrastra aquí';
                display.style.background = '#f8f9fa';
                display.style.color = '#3498db';
            }
        }

        // Función para limpiar vista previa de nuevas imágenes
        function clearNewImagePreviews() {
            selectedNewFiles = [];
            document.getElementById('newImagePreviewContainer').innerHTML = '';
            const fileInput = document.getElementById('nuevas_imagenes');
            fileInput.value = '';
            const display = document.querySelector('.file-input-display');
            display.textContent = 'Seleccionar archivos de imagen o arrastra aquí';
            display.style.background = '#f8f9fa';
            display.style.color = '#3498db';
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

        // Drag and drop para archivos
        const fileInputWrapper = document.querySelector('.file-input-wrapper');
        
        if (fileInputWrapper) {
            fileInputWrapper.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.style.background = '#e3f2fd';
            });
            
            fileInputWrapper.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.style.background = '#f8f9fa';
            });
            
            fileInputWrapper.addEventListener('drop', function(e) {
                e.preventDefault();
                this.style.background = '#f8f9fa';
                
                const files = Array.from(e.dataTransfer.files);
                selectedNewFiles = files;
                updateNewFileInput();
                displayNewImagePreviews();
            });
        }
    </script>
</body>
</html>