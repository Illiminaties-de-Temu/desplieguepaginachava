<?php
session_start();

// Verificar si el usuario está logueado y es editor
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
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="../estilo.css">
    <style>
    /* FUENTE GOTHAM */
    @import url('https://fonts.cdnfonts.com/css/gotham');
    * {
        font-family: 'Gotham', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* PALETA DE COLORES */
    body {
        background-color: #f0f4f8;
        color: #2a3441;
    }

    .container {
        background: white;
        border: 1px solid rgba(0, 145, 255, 0.2);
    }

    .header {
        background: linear-gradient(135deg, #1d4ed8, #083057);
        color: white;
    }

    .logout-btn {
        background: #e74c3c;
        color: white;
    }

    .user-info {
        background: rgba(29, 78, 216, 0.1);
    }

    .alert {
        background: #e3f2fd;
        border-left: 4px solid #1d4ed8;
    }

    .nav-category {
        background: white;
        border: 1px solid #e0e0e0;
    }

    .nav-link {
        background: #f8f9fa;
        border-left: 3px solid #1d4ed8;
        color: #2a3441;
    }

    .nav-link:hover {
        background: #e3f2fd;
    }

    .nav-category h3 {
        color: #1d4ed8;
    }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="../login/out.php" class="logout-btn">Cerrar Sesión</a>
            <h1>Panel de Administración</h1>
            <div class="user-info">
                Usuario: <?php echo htmlspecialchars($_SESSION['nombreusuario']); ?> | 
                Tipo: <?php echo htmlspecialchars($_SESSION['tipousuario']); ?>
            </div>
        </div>

        <div class="content">
            <div class="alert alert-info">
                <strong>Bienvenido al Panel de Administración</strong><br>
                Selecciona una opción del menú para comenzar a gestionar el sistema.
            </div>

            <div class="nav-grid">
                <div class="nav-category">
                    <h3>
                        <span class="nav-category-icon">📰</span>
                        Gestión de Noticias
                    </h3>
                    <div class="nav-links">
                        <a href="cargaarchivos.php" class="nav-link">
                            <span class="nav-link-icon">➕</span>
                            Crear Noticia
                        </a>
                        <a href="editarnoticia.php" class="nav-link">
                            <span class="nav-link-icon">✏️</span>
                            Editar Noticia
                        </a>
                        <a href="eliminarnoticiaescri.php" class="nav-link">
                            <span class="nav-link-icon">🗑️</span>
                            Eliminar Noticia
                        </a>
                    </div>
                </div>

                <div class="nav-category">
                    <h3>
                        <span class="nav-category-icon">🔧</span>
                        Seguridad
                    </h3>
                    <div class="nav-links">
                        <a href="cambiarcontraescri.php" class="nav-link">
                            <span class="nav-link-icon">🔐</span>
                            Cambiar Contraseña
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

 
</body>
</html>