<?php
session_start();

// Verificar si el usuario está logueado y es master
if (!isset($_SESSION['nombreusuario']) || $_SESSION['tipousuario'] !== 'master') {
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
    
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="../login/out.php" class="logout-btn">Cerrar Sesión</a>
            <br>
            <h1>Panel de Administración</h1>
            <br>
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
                        <a href="eliminarnoticia.php" class="nav-link">
                            <span class="nav-link-icon">🗑️</span>
                            Eliminar Noticia
                        </a>
                    </div>
                </div>

                <div class="nav-category">
                    <h3>
                        <span class="nav-category-icon">👥</span>
                        Gestión de Usuarios
                    </h3>
                    <div class="nav-links">
                        <a href="crearusuario.php" class="nav-link">
                            <span class="nav-link-icon">➕</span>
                            Crear Usuario
                        </a>
                        <a href="editarusuario.php" class="nav-link">
                            <span class="nav-link-icon">✏️</span>
                            Editar Usuario
                        </a>
                        <a href="eliminarusuario.php" class="nav-link">
                            <span class="nav-link-icon">🗑️</span>
                            Eliminar Usuario
                        </a>
                    </div>
                </div>

                <div class="nav-category">
                    <h3>
                        <span class="nav-category-icon">🔧</span>
                        Sistema y Seguridad
                    </h3>
                    <div class="nav-links">
                        <a href="bitacora.php" class="nav-link">
                            <span class="nav-link-icon">📋</span>
                            Bitácora del Sistema
                        </a>
                        <a href="cambiarcontra.php" class="nav-link">
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