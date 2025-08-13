
<?php
session_start();

// Verificar permisos
if (!isset($_SESSION['nombreusuario']) || $_SESSION['tipousuario'] !== 'master') {
    header("Location: ../Login/Out.php");
    exit();
}

require_once '../../config/config.php';

// Inicializar array de errores
$errores = [];

// Recoger y limpiar datos del formulario
$nombreusuario = trim($_POST['nombreusuario'] ?? '');
$contrasena = trim($_POST['contrasena'] ?? '');
$confirmar_contrasena = trim($_POST['confirmar_contrasena'] ?? '');
$tipousuario = $_POST['tipousuario'] ?? '';

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

// Si no hay errores, verificar y crear usuario
if (empty($errores)) {
    try {
        // Verificar si el usuario ya existe
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE nombreusuario = ?");
        $stmt->execute([$nombreusuario]);
        
        if ($stmt->fetchColumn() > 0) {
            $errores[] = 'El nombre de usuario ya está en uso.';
        } else {
            // Crear hash de la contraseña
            $hash_contrasena = password_hash($contrasena, PASSWORD_BCRYPT);
            
            // Insertar nuevo usuario
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombreusuario, contrasena, tipousuario) VALUES (?, ?, ?)");
            $stmt->execute([$nombreusuario, $hash_contrasena, $tipousuario]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['mensaje_exito'] = "Usuario $nombreusuario creado exitosamente";
                
                //Insertar en la bitacora
                $accion = "Creacion usuario";
                $descripcion = "Usuario $nombreusuario creado de tipo $tipousuario ";
                    $stmtbitacora = $pdo->prepare("INSERT INTO bitacora (usuario,accion,descripcion,fecha)
                    VALUES (?,?,?,NOW())");
                    $stmtbitacora->execute([$_SESSION['nombreusuario'],
                    $accion,$descripcion]);

            } else {
                $errores[] = "Error al crear el usuario";
            }
        }
    } catch (PDOException $e) {
        $errores[] = "Error de base de datos: " . $e->getMessage();
    }
}

// Manejar redirección con errores o éxito
if (!empty($errores)) {
    // Guardar errores y datos del formulario para repoblar
    $_SESSION['errores'] = $errores;
    $_SESSION['datos_formulario'] = [
        'nombreusuario' => $nombreusuario,
        'tipousuario' => $tipousuario
    ];
}

// Redirigir de vuelta al formulario
header("Location: ../Master/crearusuario.php");
exit();