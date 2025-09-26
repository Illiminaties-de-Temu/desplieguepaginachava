<?php
session_start();

// Verificar permisos
if (!isset($_SESSION['nombreusuario']) || $_SESSION['tipousuario'] !== 'master') {
    header("Location: ../login/out.php");
    exit();
}

require_once '../../config/config.php';

// Configurar timeout y manejo de errores
set_time_limit(60); // 60 segundos máximo
error_log("=== INICIO PROCESO CREAR USUARIO ===");

// Inicializar array de errores
$errores = [];

try {
    // Verificar método POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    error_log("Punto 1: Método POST verificado");

    // Configurar PDO para mostrar errores
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_TIMEOUT, 30);

    // Recoger y limpiar datos del formulario
    $nombreusuario = trim($_POST['nombreusuario'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');
    $confirmar_contrasena = trim($_POST['confirmar_contrasena'] ?? '');
    $tipousuario = $_POST['tipousuario'] ?? '';

    error_log("Punto 2: Datos recogidos - Usuario: $nombreusuario, Tipo: $tipousuario");

    // Validaciones
    if (empty($nombreusuario)) {
        $errores[] = 'El nombre de usuario es obligatorio.';
    } elseif (strlen($nombreusuario) < 3 || strlen($nombreusuario) > 20) {
        $errores[] = 'El nombre de usuario debe tener entre 3 y 20 caracteres.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $nombreusuario)) {
        $errores[] = 'El nombre de usuario solo puede contener letras, números y guiones bajos.';
    }

    if (empty($contrasena)) {
        $errores[] = 'La contraseña es obligatoria.';
    } elseif (strlen($contrasena) < 6) {
        $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($contrasena !== $confirmar_contrasena) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    if (empty($tipousuario)) {
        $errores[] = 'Debe seleccionar un tipo de usuario.';
    }

    error_log("Punto 3: Validaciones completadas. Errores: " . count($errores));

    // Si no hay errores, verificar y crear usuario
    if (empty($errores)) {
        error_log("Punto 4: Iniciando proceso BD");
        
        // Verificar si el usuario ya existe
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE nombreusuario = ?");
        $stmt->execute([$nombreusuario]);
        error_log("Punto 5: Consulta de verificación ejecutada");
        
        if ($stmt->fetchColumn() > 0) {
            $errores[] = 'El nombre de usuario ya está en uso.';
            error_log("Punto 6: Usuario ya existe");
        } else {
            error_log("Punto 7: Usuario disponible, creando hash");
            // Crear hash de la contraseña
            $hash_contrasena = password_hash($contrasena, PASSWORD_BCRYPT);
            
            error_log("Punto 8: Insertando nuevo usuario");
            // Insertar nuevo usuario
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombreusuario, contrasena, tipousuario) VALUES (?, ?, ?)");
            $stmt->execute([$nombreusuario, $hash_contrasena, $tipousuario]);
            
            error_log("Punto 9: Insert ejecutado, verificando filas afectadas");
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['mensaje_exito'] = "Usuario $nombreusuario creado exitosamente";
                error_log("Punto 10: Usuario creado exitosamente");
                
                //Insertar en la bitacora
                $accion = "Creacion usuario";
                $descripcion = "Usuario $nombreusuario creado de tipo $tipousuario ";
                error_log("Punto 11: Preparando bitácora");
                
                $stmtbitacora = $pdo->prepare("INSERT INTO bitacora (usuario,accion,descripcion,fecha) VALUES (?,?,?,NOW())");
                $stmtbitacora->execute([$_SESSION['nombreusuario'], $accion, $descripcion]);
                error_log("Punto 12: Bitácora registrada");
                
            } else {
                $errores[] = "Error al crear el usuario";
                error_log("Punto 13: Error - ninguna fila afectada");
            }
        }
    }

} catch (PDOException $e) {
    $errorMsg = "Error de base de datos: " . $e->getMessage();
    $errores[] = $errorMsg;
    error_log("ERROR PDO: " . $errorMsg);
} catch (Exception $e) {
    $errorMsg = "Error general: " . $e->getMessage();
    $errores[] = $errorMsg;
    error_log("ERROR GENERAL: " . $errorMsg);
}

error_log("Punto 14: Proceso completado. Total errores: " . count($errores));

// Manejar redirección con errores o éxito
if (!empty($errores)) {
    // Guardar errores y datos del formulario para repoblar
    $_SESSION['errores'] = $errores;
    $_SESSION['datos_formulario'] = [
        'nombreusuario' => $nombreusuario,
        'tipousuario' => $tipousuario
    ];
    error_log("Errores guardados en sesión: " . implode(", ", $errores));
}

error_log("Punto 15: Redirigiendo a formulario");
error_log("=== FIN PROCESO CREAR USUARIO ===");

// Redirigir de vuelta al formulario
if (headers_sent()) {
    echo "<script>window.location.href = '../master/crearusuario.php';</script>";
    exit();
} else {
    header("Location: ../master/crearusuario.php");
    exit();
}