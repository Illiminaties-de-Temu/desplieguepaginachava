<?php
session_start();

// Configuración de la base de datos
require_once '../../config/config.php';

require_once 'purga_noticias.php';

// Verificar permisos
if (!isset($_SESSION['nombreusuario']) || $_SESSION['tipousuario'] !== 'editor') {
    $_SESSION['errores'] = "No tienes permisos para realizar esta acción";
    header("Location: ../escritor/cargaarchivos.php");
    exit();
}

// Verificar que todos los campos requeridos estén presentes
if (!isset($_POST['titulo']) || !isset($_POST['contenido'])) {
    $_SESSION['errores'] = "Faltan campos requeridos";
    $_SESSION['datos_formulario'] = $_POST;
    header("Location: ../escritor/cargaarchivos.php");
    exit();
}

// Recoger datos del formulario
$titulo = $_POST['titulo'];
$contenido = $_POST['contenido'];
$destacada = isset($_POST['destacada']) && $_POST['destacada'] === 'si' ? 'si' : 'no';

// Guardar datos en sesión por si hay que redireccionar por error
$_SESSION['datos_formulario'] = [
    'titulo' => $titulo,
    'contenido' => $contenido,
    'destacada' => $destacada
];

// Configuración de rutas
$rutaBase = '../../'; // Ajusta según tu estructura de directorios
$rutaImagenesBD = 'contenido/'; // Ruta que se guardará en la BD (relativa)
$rutaImagenesFisica = $rutaBase . $rutaImagenesBD; // Ruta física completa en el servidor

// Crear carpeta si no existe
if (!file_exists($rutaImagenesFisica)) {
    mkdir($rutaImagenesFisica, 0755, true);
}

// Procesar múltiples imágenes
$rutasImagenes = [];

// Procesar imágenes principales (imagen)
if (isset($_FILES['imagen']) && is_array($_FILES['imagen']['name'])) {
    // Caso cuando hay múltiples imágenes con el mismo nombre de campo (array)
    $totalImagenes = count($_FILES['imagen']['name']);
    
    for ($i = 0; $i < $totalImagenes; $i++) {
        if ($_FILES['imagen']['error'][$i] === UPLOAD_ERR_OK) {
            $nombreTemporal = $_FILES['imagen']['tmp_name'][$i];
            $nombreOriginal = basename($_FILES['imagen']['name'][$i]);
            $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
            
            // Validar que sea una imagen
            $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($extension, $extensionesPermitidas)) {
                $_SESSION['errores'] = "Solo se permiten imágenes JPG, PNG, GIF o WEBP.";
                header("Location: ../escritor/cargaarchivos.php");
                exit();
            }

            // Generar nombre único
            $nombreUnico = uniqid() . '_' . $i . '.' . $extension;
            $rutaRelativa = $rutaImagenesBD . $nombreUnico; // Ruta para BD
            $rutaCompleta = $rutaImagenesFisica . $nombreUnico; // Ruta física completa

            // Mover la imagen a la carpeta
            if (!move_uploaded_file($nombreTemporal, $rutaCompleta)) {
                $_SESSION['errores'] = "Error al guardar la imagen $nombreOriginal.";
                header("Location: ../escritor/cargaarchivos.php");
                exit();
            }
            
            $rutasImagenes[] = $rutaRelativa;
        }
    }
} elseif (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    // Caso cuando solo hay una imagen
    $imagen = $_FILES['imagen'];
    $nombreTemporal = $imagen['tmp_name'];
    $nombreOriginal = basename($imagen['name']);
    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
    
    // Validar que sea una imagen
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($extension, $extensionesPermitidas)) {
        $_SESSION['errores'] = "Solo se permiten imágenes JPG, PNG, GIF o WEBP.";
        header("Location: ../escritor/cargaarchivos.php");
        exit();
    }

    // Generar nombre único
    $nombreUnico = uniqid() . '.' . $extension;
    $rutaRelativa = $rutaImagenesBD . $nombreUnico; // Ruta para BD
    $rutaCompleta = $rutaImagenesFisica . $nombreUnico; // Ruta física completa

    // Mover la imagen a la carpeta
    if (!move_uploaded_file($nombreTemporal, $rutaCompleta)) {
        $_SESSION['errores'] = "Error al guardar la imagen.";
        header("Location: ../escritor/cargaarchivos.php");
        exit();
    }
    
    $rutasImagenes[] = $rutaRelativa;
}

// Verificar que al menos haya una imagen
if (empty($rutasImagenes)) {
    $_SESSION['errores'] = "Debes subir al menos una imagen.";
    header("Location: ../escritor/cargaarchivos.php");
    exit();
}

// Convertir el array de rutas a una cadena separada por comas
$rutasImagenesStr = implode(',', $rutasImagenes);

// Insertar en la base de datos (usando sentencias preparadas para mayor seguridad)
$sql = "INSERT INTO noticias (Titulo, Contenido, Imagenes, fecha, Destacada)
        VALUES (:titulo, :contenido, :imagenes, NOW(), :destacada)";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':titulo', $titulo);
    $stmt->bindParam(':contenido', $contenido);
    $stmt->bindParam(':imagenes', $rutasImagenesStr);
    $stmt->bindParam(':destacada', $destacada);
    
    if ($stmt->execute()) {
        // REGISTRO EN BITÁCORA
        $accion = "Creación de noticia";
        $descripcion = "Noticia ".substr($titulo, 0, 50)." creada";
        if ($destacada === 'si') {
            $descripcion .= " (DESTACADA)";
        }
        $descripcion .= " con " . count($rutasImagenes) . " imagen(es)";
        
        $stmtbitacora = $pdo->prepare("INSERT INTO bitacora (usuario, accion, descripcion, fecha)
                                      VALUES (?, ?, ?, NOW())");
        $stmtbitacora->execute([
            $_SESSION['nombreusuario'],
            $accion,
            $descripcion
        ]);
        
        // Limpiar datos del formulario guardados en sesión
        unset($_SESSION['datos_formulario']);
        
        // Mensaje de éxito
        $_SESSION['mensaje_exito'] = "Noticia creada correctamente" . ($destacada === 'si' ? " y marcada como destacada" : "") . ".";
        purgarNoticiasAntiguas($pdo);
        eliminarImagenesHuerfanas($pdo);
        purgararchivosBitacora($pdo);
        header("Location: ../escritor/cargaarchivos.php");
        exit();
    } else {
        $_SESSION['errores'] = "Error al guardar la noticia en la base de datos.";
        
        // Si hay error, borramos las imágenes subidas
        foreach ($rutasImagenes as $rutaRelativa) {
            $rutaCompleta = $rutaBase . $rutaRelativa;
            if (file_exists($rutaCompleta)) {
                unlink($rutaCompleta);
            }
        }
        
        header("Location: ../escritor/cargaarchivos.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['errores'] = "Error al guardar: " . $e->getMessage();
    
    // Si hay error, borramos las imágenes subidas
    foreach ($rutasImagenes as $rutaRelativa) {
        $rutaCompleta = $rutaBase . $rutaRelativa;
        if (file_exists($rutaCompleta)) {
            unlink($rutaCompleta);
        }
    }
    
    header("Location: ../escritor/cargaarchivos.php");
    exit();
}
?>