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
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: #f5f7fa;
            color: #2a3441;
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #1d4ed8, #083057);
            color: white;
            padding: 20px;
            position: relative;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .logout-btn:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }
        
        .user-info {
            background: rgba(255, 255, 255, 0.15);
            padding: 10px;
            border-radius: 4px;
            font-size: 14px;
            margin-top: 10px;
            display: inline-block;
        }
        
        .content {
            padding: 20px;
        }
        
        .alert {
            background: #e3f2fd;
            border-left: 4px solid #1d4ed8;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 0 4px 4px 0;
        }
        
        .nav-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .nav-category {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 15px;
            border: 1px solid #e0e0e0;
        }
        
        .nav-category h3 {
            color: #1d4ed8;
            margin-bottom: 15px;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .nav-links {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            background: #f8f9fa;
            border-radius: 6px;
            color: #2a3441;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid #1d4ed8;
        }
        
        .nav-link:hover {
            background: #e3f2fd;
            transform: translateX(5px);
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 15px;
            }
            
            .header h1 {
                font-size: 20px;
                padding-right: 80px;
            }
            
            .logout-btn {
                top: 15px;
                right: 15px;
                padding: 6px 12px;
            }
            
            .nav-grid {
                grid-template-columns: 1fr;
            }
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