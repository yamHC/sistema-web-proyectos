<?php
session_start();
require_once '../config/db.php';

// Verificar si está logueado y es admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ../public/login.php');
    exit();
}

// Verificar si se enviaron los datos del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $empresa = $_POST['empresa'];
    $correo = $_POST['correo'];

    // Actualizar los datos del cliente en la base de datos
    $stmt = $pdo->prepare("UPDATE personas SET nombre = ?, apellido = ?, empresa = ?, correo = ? WHERE id = ?");
    $stmt->execute([$nombre, $apellido, $empresa, $correo, $id]);

    // Redirigir de vuelta a la lista de clientes con un mensaje de éxito
    $_SESSION['mensaje'] = 'Cliente actualizado correctamente.';
    header('Location: clientes.php');
    exit();
}
?>