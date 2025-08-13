<?php
session_start();

// Verificar si el usuario está logueado y es master
if (!isset($_SESSION['nombreusuario']) || $_SESSION['tipousuario'] !== 'master') {
    header("Location: ../Login/Out.php");
    exit();
}

require_once '../../config/config.php';

// Cargar datos del usuario si se pasa ID por GET
$usuario = null;
if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ? AND id != (SELECT MIN(id) FROM usuarios)");
        $stmt->execute([$_GET['id']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Error al cargar usuario: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - Panel de Administración</title>
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
            background-color: #e9f7ef;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .form-hidden {
            display: none;
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
        .action-buttons {
            margin-top: 20px;
            display: flex;
            gap: 10px;
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
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-primary:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        
        /* Estilos para los campos de contraseña */
        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .input-group input[type="password"],
        .input-group input[type="text"] {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .toggle-password {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            z-index: 1;
        }
        
        .password-strength {
            margin-top: 5px;
            height: 4px;
            background-color: #f0f0f0;
            border-radius: 2px;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            transition: width 0.3s ease;
            border-radius: 2px;
        }
        
        .strength-weak {
            background-color: #dc3545;
        }
        
        .strength-medium {
            background-color: #ffc107;
        }
        
        .strength-strong {
            background-color: #28a745;
        }
        
        .password-requirements {
            margin-top: 10px;
            font-size: 12px;
        }
        
        .requirement {
            color: #666;
            margin-bottom: 2px;
        }
        
        .requirement.met {
            color: #28a745;
        }
        
        .requirement.met::before {
            content: '✓ ';
        }
        
        .validation-message {
            margin-top: 5px;
            font-size: 12px;
            padding: 5px;
            border-radius: 3px;
        }
        
        .validation-success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }
        
        .validation-error {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="panel.php" class="logout-btn">← Regresar</a>
            <h1>Editar Usuario</h1>
            <div class="user-info">
                Usuario: <?php echo htmlspecialchars($_SESSION['nombreusuario']); ?> | 
                Tipo: <?php echo htmlspecialchars($_SESSION['tipousuario']); ?>
            </div>
        </div>

        <div class="content">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <strong>Éxito:</strong> <?php echo htmlspecialchars(urldecode($_GET['success'])); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <strong>Error:</strong> <?php echo htmlspecialchars(urldecode($_GET['error'])); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="alert alert-info">
                <strong>Información:</strong> Busca y selecciona un usuario para editarlo. Deja la contraseña vacía si no deseas cambiarla.
            </div>

            <div class="search-container">
                <label for="search-user">Buscar Usuario:</label>
                <input type="text" id="search-user" placeholder="Escribe el nombre de usuario" 
                       value="<?php echo isset($usuario['nombreusuario']) ? htmlspecialchars($usuario['nombreusuario']) : ''; ?>">
                <div id="user-results"></div>
            </div>

            <div class="user-selected" id="user-selected" <?php echo !isset($usuario) ? 'style="display: none;"' : ''; ?>>
                <strong>Editando usuario:</strong> <span id="selected-username">
                    <?php echo isset($usuario) ? htmlspecialchars($usuario['nombreusuario']) : ''; ?>
                </span>
                <input type="hidden" name="id_usuario" id="id_usuario" value="<?php echo isset($usuario) ? $usuario['id'] : ''; ?>">
            </div>

            <form method="POST" action="../bd/actualizarusuario.php" id="edit-form" <?php echo !isset($usuario) ? 'class="form-hidden"' : ''; ?>>
                <input type="hidden" name="id_usuario" id="form-id_usuario" value="<?php echo isset($usuario) ? $usuario['id'] : ''; ?>">
                
                <div class="form-section">
                    <h2>Información del Usuario</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombreusuario">Nombre de Usuario</label>
                            <input type="text" id="nombreusuario" name="nombreusuario" required
                                   value="<?php echo isset($usuario) ? htmlspecialchars($usuario['nombreusuario']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="tipousuario">Tipo de Usuario</label>
                            <select id="tipousuario" name="tipousuario" required>
                                <option value="master" <?php echo (isset($usuario) && $usuario['tipousuario'] === 'master') ? 'selected' : ''; ?>>Master</option>
                                <option value="editor" <?php echo (isset($usuario) && $usuario['tipousuario'] === 'editor') ? 'selected' : ''; ?>>Editor</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section">
    <h2>Seguridad</h2>
    
    <div class="form-row">
        <div class="form-group">
            <label for="nueva_contrasena">Nueva Contraseña</label>
            <div class="input-group">
                <input type="password" id="nueva_contrasena" name="nueva_contrasena" 
                       placeholder="Deja vacío si no quieres cambiarla">
                <button type="button" class="toggle-password" onclick="togglePassword('nueva_contrasena')">
                    👁️
                </button>
            </div>
            <div class="password-strength">
                <div class="password-strength-bar" id="strength-bar"></div>
            </div>
            <div class="password-requirements">
                <div class="requirement" id="req-length">Al menos 6 caracteres</div>
                <div class="requirement" id="req-uppercase">Al menos una mayúscula</div>
                <div class="requirement" id="req-number">Al menos un número</div>
                <div class="requirement" id="req-special">Al menos un carácter especial</div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="confirmar_contrasena">Confirmar Nueva Contraseña</label>
            <div class="input-group">
                <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" 
                       placeholder="Confirma la nueva contraseña">
                <button type="button" class="toggle-password" onclick="togglePassword('confirmar_contrasena')">
                    👁️
                </button>
            </div>
            <div class="form-validation">
                <div id="password-match" class="validation-message" style="display: none;"></div>
            </div>
            <div class="action-buttons">
                <button type="button" id="update-btn" class="btn btn-primary" <?php echo !isset($usuario) ? 'disabled' : ''; ?>>Actualizar</button>
            </div>
        </div>
    </div>
            </form>
        </div>
    </div>

    

    <script>
        // Limpiar el formulario después de una actualización exitosa
        <?php if (isset($_GET['success'])): ?>
            document.addEventListener('DOMContentLoaded', function() {
                // Limpiar campos
                document.getElementById('search-user').value = '';
                
                // Ocultar secciones
                document.getElementById('user-selected').style.display = 'none';
                document.getElementById('edit-form').classList.add('form-hidden');
                
                // Deshabilitar botón
                document.getElementById('update-btn').disabled = true;
            });
        <?php endif; ?>

        // Búsqueda de usuarios con AJAX
        document.getElementById('search-user').addEventListener('input', function(e) {
            const query = e.target.value.trim();
            const resultsContainer = document.getElementById('user-results');
            const currentUserId = document.getElementById('id_usuario')?.value;
            const currentUsername = '<?php echo $_SESSION['nombreusuario']; ?>';
            
            if (query.length < 1) {
                resultsContainer.style.display = 'none';
                return;
            }
            
            fetch(`../Bd/buscar_usuarios.php?q=${encodeURIComponent(query)}${currentUserId ? '&exclude=' + currentUserId : ''}&currentUser=${encodeURIComponent(currentUsername)}`)
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
                            userElement.dataset.type = user.tipousuario;
                            
                            userElement.addEventListener('click', function() {
                                selectUser(user.id, user.nombreusuario, user.tipousuario);
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
        function selectUser(id, username, type) {
            document.getElementById('user-results').style.display = 'none';
            document.getElementById('search-user').value = username;
            
            // Mostrar información del usuario seleccionado
            const selectedDiv = document.getElementById('user-selected');
            document.getElementById('selected-username').textContent = username + ' (' + type + ')';
            selectedDiv.style.display = 'block';
            
            // Llenar el formulario
            document.getElementById('nombreusuario').value = username;
            document.getElementById('tipousuario').value = type;
            document.getElementById('form-id_usuario').value = id;
            document.getElementById('id_usuario').value = id;
            
            // Mostrar el formulario y habilitar botón
            document.getElementById('edit-form').classList.remove('form-hidden');
            document.getElementById('update-btn').disabled = false;
        }
        
        // Ocultar resultados al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (e.target.id !== 'search-user') {
                document.getElementById('user-results').style.display = 'none';
            }
        });
        
        // Validación de contraseñas
        document.getElementById('confirmar_contrasena')?.addEventListener('input', function() {
            const nuevaContrasena = document.getElementById('nueva_contrasena').value;
            const confirmarContrasena = this.value;
            
            if (nuevaContrasena !== confirmarContrasena) {
                this.setCustomValidity('Las contraseñas no coinciden');
            } else {
                this.setCustomValidity('');
            }
        });

        // Manejar el clic en el botón Actualizar
        document.getElementById('update-btn').addEventListener('click', function() {
            // Validar contraseñas
            const nuevaContrasena = document.getElementById('nueva_contrasena').value;
            const confirmarContrasena = document.getElementById('confirmar_contrasena').value;
            
            if (nuevaContrasena && nuevaContrasena !== confirmarContrasena) {
                alert('Las contraseñas no coinciden');
                return;
            }
            
            // Enviar el formulario
            document.getElementById('edit-form').submit();
        });

        // Validación en tiempo real para habilitar/deshabilitar botón
        document.getElementById('nombreusuario')?.addEventListener('input', function() {
            const nombre = this.value.trim();
            const updateBtn = document.getElementById('update-btn');
            updateBtn.disabled = nombre.length === 0;
        });
    </script>
</body>
</html>

<script>
    // Función para mostrar/ocultar contraseña
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const button = field.nextElementSibling;
    
    if (field.type === 'password') {
        field.type = 'text';
        button.textContent = '🙈';
    } else {
        field.type = 'password';
        button.textContent = '👁️';
    }
}

// Validación de fortaleza de contraseña
document.getElementById('nueva_contrasena')?.addEventListener('input', function(e) {
    const password = e.target.value;
    const strengthBar = document.getElementById('strength-bar');
    
    // Verificar requisitos
    const requirements = {
        length: password.length >= 6,
        uppercase: /[A-Z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[^A-Za-z0-9]/.test(password)
    };
    
    // Actualizar indicadores visuales
    Object.keys(requirements).forEach(req => {
        const element = document.getElementById('req-' + req);
        if (element) {
            if (requirements[req]) {
                element.classList.add('met');
            } else {
                element.classList.remove('met');
            }
        }
    });
    
    // Calcular fortaleza solo si hay contraseña
    if (password.length > 0) {
        const score = Object.values(requirements).reduce((acc, val) => acc + (val ? 1 : 0), 0);
        
        strengthBar.style.width = (score / 4) * 100 + '%';
        strengthBar.className = 'password-strength-bar';
        
        if (score <= 1) {
            strengthBar.classList.add('strength-weak');
        } else if (score <= 3) {
            strengthBar.classList.add('strength-medium');
        } else {
            strengthBar.classList.add('strength-strong');
        }
    } else {
        strengthBar.style.width = '0%';
    }
});

// Validación de confirmación de contraseña
document.getElementById('confirmar_contrasena')?.addEventListener('input', function(e) {
    const password = document.getElementById('nueva_contrasena').value;
    const confirmPassword = e.target.value;
    const validation = document.getElementById('password-match');
    
    if (confirmPassword.length === 0) {
        validation.style.display = 'none';
        return;
    }
    
    if (password === confirmPassword) {
        validation.textContent = 'Las contraseñas coinciden';
        validation.className = 'validation-message validation-success';
        validation.style.display = 'block';
    } else {
        validation.textContent = 'Las contraseñas no coinciden';
        validation.className = 'validation-message validation-error';
        validation.style.display = 'block';
    }
});

// Validación del formulario antes de enviar
document.getElementById('update-btn')?.addEventListener('click', function(e) {
    const nuevaContrasena = document.getElementById('nueva_contrasena').value;
    const confirmarContrasena = document.getElementById('confirmar_contrasena').value;
    
    // Validar solo si se está cambiando la contraseña
    if (nuevaContrasena) {
        if (nuevaContrasena !== confirmarContrasena) {
            alert('Las contraseñas no coinciden');
            e.preventDefault();
            return;
        }
        
        if (nuevaContrasena.length < 6) {
            alert('La contraseña debe tener al menos 6 caracteres');
            e.preventDefault();
            return;
        }
        
        // Verificar otros requisitos si es necesario
        const hasUppercase = /[A-Z]/.test(nuevaContrasena);
        const hasNumber = /[0-9]/.test(nuevaContrasena);
        const hasSpecial = /[^A-Za-z0-9]/.test(nuevaContrasena);
        
        if (!hasUppercase || !hasNumber || !hasSpecial) {
            alert('La contraseña debe contener al menos una mayúscula, un número y un carácter especial');
            e.preventDefault();
            return;
        }
    }
    
    // Si todo está bien, enviar el formulario
    document.getElementById('edit-form').submit();
});
</script>