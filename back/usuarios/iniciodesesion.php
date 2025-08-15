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
    /* Estilos específicos para el login */
    .login-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background-color: var(--gris-claro);
        font-family: 'Gotham', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .login-container {
        background: var(--blanco-cristal);
        border-radius: 15px;
        box-shadow: var(--sombra-profunda);
        padding: 40px;
        width: 100%;
        max-width: 400px;
        border: 1px solid rgba(0,212,255,.2);
        backdrop-filter: blur(10px);
    }
    
    .login-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .login-header h2 {
        color: var(--azul-profundo);
        font-size: 2em;
        margin-bottom: 10px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        font-weight: 700;
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
        border: 2px solid var(--azul-cristal);
        border-radius: 8px;
        font-size: 16px;
        transition: all 0.3s ease;
        background: rgba(248, 249, 250, 0.8);
    }
    
    .form-group input:focus {
        outline: none;
        border-color: var(--azul-neon);
        background: var(--blanco-puro);
        box-shadow: 0 0 0 3px rgba(0, 145, 255, 0.1);
    }
    
    .error-message {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        text-align: center;
    }
    
    .login-button {
        background: linear-gradient(135deg, var(--azul-neon) 0%, var(--azul-electrico) 100%);
        color: var(--blanco-puro);
        padding: 15px 30px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
        box-shadow: var(--sombra-neon);
    }
    
    .login-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 145, 255, 0.4);
    }
    
    .login-button:disabled {
        background: var(--gris-plasma) !important;
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
        border: 4px solid var(--gris-plasma);
        border-top: 4px solid var(--azul-neon);
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto 10px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .loader p {
        color: var(--azul-electrico);
        font-weight: 600;
    }
    
    @media (max-width: 768px) {
        .login-container {
            padding: 30px 20px;
            margin: 10px;
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