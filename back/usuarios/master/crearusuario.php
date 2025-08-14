<?php
session_start();

// Verificar permisos
if (!isset($_SESSION['nombreusuario']) || $_SESSION['tipousuario'] !== 'master') {
    header("Location: ../login/out.php");
    exit();
}

require_once '../../config/config.php';

// Obtener mensajes de sesión y limpiarlos
$mensaje_exito = $_SESSION['mensaje_exito'] ?? '';
$mensaje_error = is_array($_SESSION['errores']??'');
$datos_formulario = $_SESSION['datos_formulario'] ?? [];

// Limpiar las variables de sesión después de usarlas
unset($_SESSION['mensaje_exito']);
unset($_SESSION['errores']);
unset($_SESSION['datos_formulario']);

// Valores por defecto para el formulario
$nombreusuario = $datos_formulario['nombreusuario'] ?? '';
$tipousuario = $datos_formulario['tipousuario'] ?? '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario - Panel Master</title>
    <link rel="stylesheet" href="../estilo.css">

</head>
<body>
    <div class="container">
        <div class="header">
            <a href="panel.php" class="logout-btn">← Regresar</a>
            <h1>Crear Usuario</h1>
            <div class="user-info">
                Usuario: <?php echo htmlspecialchars($_SESSION['nombreusuario']); ?> | 
                Tipo: <?php echo htmlspecialchars($_SESSION['tipousuario']); ?>
            </div>
        </div>

        <div class="content">
            <div class="form-info">
                <h3>Información Importante</h3>
                <p>Como usuario Master, tienes permisos completos para crear y gestionar usuarios. 
                   Asegúrate de asignar los permisos correctos según las responsabilidades de cada usuario.</p>
            </div>

            <?php if ($mensaje_error): ?>
                <div class="alert alert-danger">
                    <strong>Error:</strong> <?php echo htmlspecialchars($mensaje_error); ?>
                </div>
            <?php endif; ?>

            <?php if ($mensaje_exito): ?>
                <div class="alert alert-success">
                    <strong>Éxito:</strong> <?php echo htmlspecialchars($mensaje_exito); ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="createUserForm" action="../bd/guardar_usuario.php">
                <div class="form-section">
                    <h2>Información del Usuario</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombreusuario">Nombre de Usuario:</label>
                            <input type="text" 
                                   name="nombreusuario" 
                                   id="nombreusuario" 
                                   value="<?php echo htmlspecialchars($nombreusuario ?? ''); ?>" 
                                   required 
                                   maxlength="20"
                                   pattern="[a-zA-Z0-9_]+"
                                   title="Solo letras, números y guiones bajos">
                            <div class="form-validation">
                                <div id="username-validation" class="validation-message" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="contrasena">Contraseña:</label>
                            <div class="input-group">
                                <input type="password" 
                                       name="contrasena" 
                                       id="contrasena" 
                                       required 
                                       minlength="6">
                                <button type="button" class="toggle-password" onclick="togglePassword('contrasena')">
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
                            <label for="confirmar_contrasena">Confirmar Contraseña:</label>
                            <div class="input-group">
                                <input type="password" 
                                       name="confirmar_contrasena" 
                                       id="confirmar_contrasena" 
                                       required>
                                <button type="button" class="toggle-password" onclick="togglePassword('confirmar_contrasena')">
                                    👁️
                                </button>
                            </div>
                            <div class="form-validation">
                                <div id="password-match" class="validation-message" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Tipo de Usuario</h2>
                    <p>Selecciona el tipo de usuario y revisa los permisos asociados:</p>
                    
                    <div class="user-type-cards">
                        <div class="user-type-card" onclick="selectUserType('master')">
                            <input type="radio" name="tipousuario" value="master" id="type-master">
                            <h3>Master</h3>
                            <p>Acceso completo al sistema con todos los permisos administrativos.</p>
                            <ul class="permissions">
                                <li>Crear, editar y eliminar usuarios</li>
                                <li>Gestión completa de noticias</li>
                                <li>Visualización de Bitacoras</li>
                            </ul>
                        </div>
                        
                        <div class="user-type-card" onclick="selectUserType('editor')">
                            <input type="radio" name="tipousuario" value="editor" id="type-editor">
                            <h3>Editor</h3>
                            <p>Permisos para gestionar contenido y noticias del sitio.</p>
                            <ul class="permissions">
                                <li>Crear y editar noticias</li>
                                <li>Gestionar imágenes y multimedia</li>
                                <li>Eliminar contenido</li>
                            </ul>
                        </div>
                        
                
                    </div>
                </div>

                <div class="buttons">
                    <button type="reset" class="btn btn-secondary">Limpiar Formulario</button>
                    <button type="submit" class="btn btn-primary">Crear Usuario</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Validación asíncrona de nombre de usuario
        document.getElementById('nombreusuario').addEventListener('input', function() {
            const username = this.value.trim();
            const validation = document.getElementById('username-validation');
            
            if (username.length < 3) {
                validation.textContent = 'El nombre de usuario debe tener al menos 3 caracteres';
                validation.className = 'validation-message validation-error';
                validation.style.display = 'block';
                return;
            }
            
            if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                validation.textContent = 'Solo se permiten letras, números y guiones bajos';
                validation.className = 'validation-message validation-error';
                validation.style.display = 'block';
                return;
            }
            
            // Mostrar carga mientras se verifica
            validation.textContent = 'Verificando...';
            validation.className = 'validation-message validation-info';
            validation.style.display = 'block';
            
            fetch('../bd/verificar_usuario.php?username=' + encodeURIComponent(username))
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la red');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    validation.textContent = data.mensaje;
                    validation.className = data.existe ? 
                        'validation-message validation-error' : 
                        'validation-message validation-success';
                    validation.style.display = 'block';
                })
                .catch(error => {
                    validation.textContent = 'Error al verificar: ' + error.message;
                    validation.className = 'validation-message validation-error';
                    validation.style.display = 'block';
                    console.error('Error:', error);
                });
        });
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

        // Función para seleccionar tipo de usuario
        function selectUserType(type) {
            // Remover selección anterior
            document.querySelectorAll('.user-type-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Seleccionar nuevo tipo
            document.getElementById('type-' + type).checked = true;
            document.getElementById('type-' + type).closest('.user-type-card').classList.add('selected');
        }

        // Validación de nombre de usuario
        document.getElementById('nombreusuario').addEventListener('input', function(e) {
            const username = e.target.value;
            const validation = document.getElementById('username-validation');
            
            if (username.length < 3) {
                validation.textContent = 'El nombre de usuario debe tener al menos 3 caracteres';
                validation.className = 'validation-message validation-error';
                validation.style.display = 'block';
            } else if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                validation.textContent = 'Solo se permiten letras, números y guiones bajos';
                validation.className = 'validation-message validation-error';
                validation.style.display = 'block';
            } else {
                validation.textContent = 'Nombre de usuario válido';
                validation.className = 'validation-message validation-success';
                validation.style.display = 'block';
            }
        });

        // Validación de fortaleza de contraseña
        document.getElementById('contrasena').addEventListener('input', function(e) {
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
                if (requirements[req]) {
                    element.classList.add('met');
                } else {
                    element.classList.remove('met');
                }
            });
            
            // Calcular fortaleza
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
        });

        // Validación de confirmación de contraseña
        document.getElementById('confirmar_contrasena').addEventListener('input', function(e) {
            const password = document.getElementById('contrasena').value;
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
        document.getElementById('createUserForm').addEventListener('submit', function(e) {
            const username = document.getElementById('nombreusuario').value;
            const password = document.getElementById('contrasena').value;
            const confirmPassword = document.getElementById('confirmar_contrasena').value;
            const userType = document.querySelector('input[name="tipousuario"]:checked');
            
            if (!username || !password || !confirmPassword || !userType) {
                e.preventDefault();
                alert('Por favor, complete todos los campos obligatorios.');
                return;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Las contraseñas no coinciden.');
                return;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 6 caracteres.');
                return;
            }
        });
    </script>
</body>
</html>