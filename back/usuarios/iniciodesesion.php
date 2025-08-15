<?php session_start(); ?>
<?php
        /* Validación de que se tiene el error */
        if (isset($_GET['error']) == 1) {  // Verifica si se ha pasado un parámetro 'error' en la URL
            echo '<script>alert("Usuario o contraseña incorrectos")</script>';  // Si es así, muestra un mensaje de alerta
        }
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="estilo.css">
    <style>
        /* Fuente gótica */
        @import url('https://fonts.googleapis.com/css2?family=UnifrakturMaguntia&display=swap');
        
        /* Estilos específicos para el login gótico */
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: #021037 url('https://example.com/gothic-bg.jpg') no-repeat center/cover;
            font-family: 'UnifrakturMaguntia', cursive;
        }
        
        .login-container {
            background: rgba(2, 16, 55, 0.8);
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(29, 78, 216, 0.5), 
                        inset 0 0 10px rgba(255, 255, 255, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            border: 1px solid #1d4ed8;
            backdrop-filter: blur(5px);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h2 {
            color: #1d4ed8;
            font-size: 2em;
            margin-bottom: 10px;
            text-shadow: 0 0 10px rgba(29, 78, 216, 0.7);
            letter-spacing: 2px;
            -webkit-text-stroke: 0.5px white;
        }
        
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group input {
            padding: 15px;
            border: 2px solid #1d4ed8;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(0, 0, 0, 0.3);
            color: white;
            font-family: Arial, sans-serif; /* Mejor legibilidad en inputs */
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #8B0000;
            background: rgba(0, 0, 0, 0.5);
            box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.3);
        }
        
        .error-message {
            background: rgba(139, 0, 0, 0.3);
            color: #ffcccc;
            border: 1px solid #8B0000;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            text-shadow: 0 0 5px black;
        }
        
        .login-button {
            background: linear-gradient(135deg, #1d4ed8, #8B0000);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-shadow: 0 1px 3px black;
            letter-spacing: 1px;
            box-shadow: 0 0 15px rgba(29, 78, 216, 0.5);
        }
        
        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 25px rgba(29, 78, 216, 0.8);
            background: linear-gradient(135deg, #1a43b5, #6d0000);
        }
        
        .login-button:disabled {
            background: #2a3441 !important;
            cursor: not-allowed;
            opacity: 0.65;
            transform: none;
            box-shadow: none;
        }
        
        .loader {
            text-align: center;
            margin-top: 20px;
        }
        
        .spinner {
            border: 4px solid rgba(2, 16, 55, 0.8);
            border-top: 4px solid #8B0000;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
            box-shadow: 0 0 10px rgba(139, 0, 0, 0.5);
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loader p {
            color: #1d4ed8;
            font-weight: 600;
            text-shadow: 0 0 5px black;
        }
        
        @media (max-width: 768px) {
            .login-container {
                padding: 30px 20px;
                margin: 10px;
                background: rgba(2, 16, 55, 0.9);
            }
            
            .login-header h2 {
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-container">
        <div class="login-header">
            <h2>Iniciar Sesión</h2>
        </div>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message" id="error-box">
                <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']); 
                ?>
            </div>
        <?php endif; ?>
        
        <form id="login-form" class="login-form" action="login/login.php" method="post">
            <div class="form-group">
                <input type="text" name="username" id="username" placeholder="Nombre de usuario" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" id="password" placeholder="Contraseña" required>
            </div>
            <div class="form-group">
                <button type="submit" id="login-button" class="login-button">Entrar</button>
            </div>
        </form>

        <div class="loader" id="loader" style="display:none;">
            <div class="spinner"></div>
            <p>Verificando credenciales...</p>
        </div>
    </div>
</div>

<script>
    // Script para mostrar el loader al enviar el formulario
    document.getElementById('login-form').addEventListener('submit', function() {
        document.getElementById('login-button').disabled = true;
        document.getElementById('loader').style.display = 'block';
    });
</script>

</body>
</html>