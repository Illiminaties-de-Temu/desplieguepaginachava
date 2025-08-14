<?php
session_start();

// Verificar si el usuario está logueado y es master
if (!isset($_SESSION['nombreusuario']) || $_SESSION['tipousuario'] !== 'editor') {
    header("Location: ../login/out.php");
    exit();
}

// Mostrar errores si existen
$errores = [];
if (isset($_SESSION['errores'])) {
    $errores = $_SESSION['errores'];
    unset($_SESSION['errores']);
}

// Mostrar mensaje de éxito si existe
$mensaje_exito = '';
if (isset($_SESSION['mensaje_exito'])) {
    $mensaje_exito = $_SESSION['mensaje_exito'];
    unset($_SESSION['mensaje_exito']);
}

?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña - Sistema de Administración</title>
    <link rel="stylesheet" href="../estilo.css">
    <style>
        /* Estilos para los campos de contraseña */
        .form-group {
            position: relative;
        }
        
        .form-group input[type="password"],
        .form-group input[type="text"] {
            width: 100%;
            padding: 12px;
            padding-right: 40px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        .form-group input[type="password"]:focus,
        .form-group input[type="text"]:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }
        
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            z-index: 1;
        }
        
        .validation-message {
            margin-top: 5px;
            font-size: 12px;
            padding: 5px;
            border-radius: 3px;
            display: block;
            min-height: 15px;
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
        
        .password-requirements {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #007bff;
        }
        
        .password-requirements h4 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #495057;
        }
        
        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .requirement {
            margin-bottom: 5px;
            color: #dc3545;
            font-size: 14px;
        }
        
        .requirement.met {
            color: #28a745;
        }
        
        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
            border-left: 4px solid;
        }
        
        .alert-info {
            color: #0c5460;
            background-color: #d1ecf1;
            border-left-color: #17a2b8;
        }
        
        .buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="panel.php" class="logout-btn">← Regresar</a>
            <h1>🔐 Cambiar Contraseña</h1>
            <div class="user-info">
                Usuario: <?php echo htmlspecialchars($_SESSION['nombreusuario']); ?> | 
                Tipo: <?php echo htmlspecialchars($_SESSION['tipousuario']); ?>
            </div>
        </div>
        
        <div class="content">
            <div class="form-section">
                <h2>🔑 Actualizar Contraseña</h2>
                
                <div class="alert alert-info">
                    <strong>Importante:</strong> Por seguridad, debes ingresar tu contraseña actual para poder cambiarla.
                </div>
                
                <!-- Mensajes de error -->
                <?php if (!empty($errores)): ?>
                <div id="error-messages">
                    <div class="alert" style="background: #f8d7da; color: #721c24; border-left-color: #dc3545;">
                        <strong>❌ Error:</strong>
                        <ul>
                            <?php foreach ($errores as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Mensaje de éxito -->
                <?php if (!empty($mensaje_exito)): ?>
                <div id="success-message">
                    <div class="alert" style="background: #d4edda; color: #155724; border-left-color: #28a745;">
                        <strong>✅ Éxito:</strong> <?php echo htmlspecialchars($mensaje_exito); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <form action="../bd/cambiarcontraescri.php" method="POST" id="password-form">
                    
                    <!-- Contraseña actual -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="contrasena_actual">🔒 Contraseña Actual *</label>
                            <input type="password" name="contrasena_actual" id="contrasena_actual" 
                                   placeholder="Ingresa tu contraseña actual" required>
                            <div class="validation-message" id="actual-msg"></div>
                        </div>
                    </div>
                    
                    <!-- Nueva contraseña -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nueva_contrasena">🔑 Nueva Contraseña *</label>
                            <input type="password" name="nueva_contrasena" id="nueva_contrasena" 
                                   placeholder="Ingresa la nueva contraseña" required minlength="8">
                            <div class="validation-message" id="nueva-msg"></div>
                        </div>
                    </div>
                    
                    <!-- Confirmar nueva contraseña -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="confirmar_contrasena">🔑 Confirmar Nueva Contraseña *</label>
                            <input type="password" name="confirmar_contrasena" id="confirmar_contrasena" 
                                   placeholder="Confirma la nueva contraseña" required minlength="8">
                            <div class="validation-message" id="confirmar-msg"></div>
                        </div>
                    </div>
                    
                    <!-- Requisitos de contraseña -->
                    <div class="form-row">
                        <div class="form-group full-width">
                            <div class="password-requirements">
                                <h4>📋 Requisitos de la contraseña:</h4>
                                <ul>
                                    <li class="requirement" id="length-req">✗ Mínimo 8 caracteres</li>
                                    <li class="requirement" id="uppercase-req">✗ Al menos una letra mayúscula</li>
                                    <li class="requirement" id="lowercase-req">✗ Al menos una letra minúscula</li>
                                    <li class="requirement" id="number-req">✗ Al menos un número</li>
                                    <li class="requirement" id="special-req">✗ Al menos un carácter especial (!@#$%^&*)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botones -->
                    <div class="buttons">
                        <button type="button" class="btn btn-secondary" onclick="limpiarFormulario()">
                            🗑️ Limpiar
                        </button>
                        <button type="submit" class="btn btn-primary" id="submit-btn">
                            💾 Cambiar Contraseña
                        </button>
                    </div>
                    
                </form>
            </div>
        </div>
    </div>

    <script>
        // Función para resetear los requisitos
        function resetRequirements() {
            const requirements = document.querySelectorAll('.requirement');
            requirements.forEach(req => {
                req.textContent = req.textContent.replace('✓', '✗');
                req.classList.remove('met');
            });
        }

        // Validación de requisitos de contraseña
        document.getElementById('nueva_contrasena').addEventListener('input', function() {
            const password = this.value;
            const msg = document.getElementById('nueva-msg');
            
            // Verificar requisitos
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /\d/.test(password),
                special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
            };
            
            // Actualizar indicadores visuales
            updateRequirement('length-req', requirements.length);
            updateRequirement('uppercase-req', requirements.uppercase);
            updateRequirement('lowercase-req', requirements.lowercase);
            updateRequirement('number-req', requirements.number);
            updateRequirement('special-req', requirements.special);
            
            // Mensaje de validación
            const allMet = Object.values(requirements).every(req => req);
            if (password.length === 0) {
                msg.textContent = '';
                msg.className = 'validation-message';
            } else if (allMet) {
                msg.textContent = 'Contraseña válida';
                msg.className = 'validation-message validation-success';
            } else {
                msg.textContent = 'La contraseña no cumple todos los requisitos';
                msg.className = 'validation-message validation-error';
            }
        });

        // Validación de confirmación de contraseña
        document.getElementById('confirmar_contrasena').addEventListener('input', function() {
            const password = document.getElementById('nueva_contrasena').value;
            const confirmPassword = this.value;
            const msg = document.getElementById('confirmar-msg');
            
            if (confirmPassword.length === 0) {
                msg.textContent = '';
                msg.className = 'validation-message';
            } else if (password === confirmPassword) {
                msg.textContent = 'Las contraseñas coinciden';
                msg.className = 'validation-message validation-success';
            } else {
                msg.textContent = 'Las contraseñas no coinciden';
                msg.className = 'validation-message validation-error';
            }
        });

        // Función para actualizar requisitos
        function updateRequirement(id, met) {
            const element = document.getElementById(id);
            if (met) {
                element.textContent = element.textContent.replace('✗', '✓');
                element.classList.add('met');
            } else {
                element.textContent = element.textContent.replace('✓', '✗');
                element.classList.remove('met');
            }
        }

        // Validación del formulario
        document.getElementById('password-form').addEventListener('submit', function(e) {
            const actualPassword = document.getElementById('contrasena_actual').value;
            const newPassword = document.getElementById('nueva_contrasena').value;
            const confirmPassword = document.getElementById('confirmar_contrasena').value;
            
            let valid = true;
            
            // Validar contraseña actual
            if (actualPassword.length === 0) {
                document.getElementById('actual-msg').textContent = 'Debe ingresar su contraseña actual';
                document.getElementById('actual-msg').className = 'validation-message validation-error';
                valid = false;
            }
            
            // Validar nueva contraseña
            const requirements = {
                length: newPassword.length >= 8,
                uppercase: /[A-Z]/.test(newPassword),
                lowercase: /[a-z]/.test(newPassword),
                number: /\d/.test(newPassword),
                special: /[!@#$%^&*(),.?":{}|<>]/.test(newPassword)
            };
            
            const allMet = Object.values(requirements).every(req => req);
            if (!allMet) {
                document.getElementById('nueva-msg').textContent = 'La contraseña no cumple todos los requisitos';
                document.getElementById('nueva-msg').className = 'validation-message validation-error';
                valid = false;
            }
            
            // Validar confirmación
            if (newPassword !== confirmPassword) {
                document.getElementById('confirmar-msg').textContent = 'Las contraseñas no coinciden';
                document.getElementById('confirmar-msg').className = 'validation-message validation-error';
                valid = false;
            }
            
            if (!valid) {
                e.preventDefault();
                alert('Por favor, corrija los errores antes de enviar el formulario');
            } else {
                // Mostrar loading
                const submitBtn = document.getElementById('submit-btn');
                submitBtn.innerHTML = '⏳ Cambiando contraseña...';
                submitBtn.disabled = true;
            }
        });

        // Función para limpiar el formulario
        function limpiarFormulario() {
            if (confirm('¿Estás seguro de que quieres limpiar el formulario?')) {
                document.getElementById('password-form').reset();
                
                // Limpiar mensajes de validación
                const messages = document.querySelectorAll('.validation-message');
                messages.forEach(msg => {
                    msg.textContent = '';
                    msg.className = 'validation-message';
                });
                
                // Resetear requisitos
                const requirements = document.querySelectorAll('.requirement');
                requirements.forEach(req => {
                    req.textContent = req.textContent.replace('✓', '✗');
                    req.classList.remove('met');
                });
                
                // Desplazar hacia arriba
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        }

        // Mostrar/ocultar contraseñas
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
        }

        // Agregar botones para mostrar/ocultar contraseñas
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInputs = document.querySelectorAll('input[type="password"]');
            passwordInputs.forEach(input => {
                const wrapper = document.createElement('div');
                wrapper.style.position = 'relative';
                input.parentNode.insertBefore(wrapper, input);
                wrapper.appendChild(input);
                
                const toggleBtn = document.createElement('button');
                toggleBtn.type = 'button';
                toggleBtn.innerHTML = '👁️';
                toggleBtn.className = 'toggle-password';
                toggleBtn.onclick = () => togglePassword(input.id);
                
                wrapper.appendChild(toggleBtn);
            });
        });
    </script>
</body>
</html>