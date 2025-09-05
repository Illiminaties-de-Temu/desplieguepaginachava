<?php
$servername = "localhost:3306";
$username = "arielfer_GECKCODEX";
$password = "IluminatiesdetemuCodigo33";
$dbname = "arielfer_chavamxbd";

try {
    // Intentamos establecer la conexión a la base de datos utilizando PDO
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password); 
    // Configura el objeto PDO para manejar errores con excepciones
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  // Esto asegura que si ocurre un error, se lanza una excepción
} catch (PDOException $e) {
    // Si ocurre una excepción al intentar conectar, se captura y muestra un mensaje de error
    die("Error al conectar a la base de datos: " . $e->getMessage());  // Detiene el script y muestra el mensaje de error
}
?>