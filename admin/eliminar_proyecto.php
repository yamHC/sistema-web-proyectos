<?php
session_start();
require_once '../config/db.php';

// Verificar si está logueado y es admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ../public/login.php');
    exit();
}

// Verificar si se recibió el ID del proyecto
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Eliminar el proyecto de la base de datos
    $stmt = $pdo->prepare("DELETE FROM proyectos WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['mensaje'] = 'Proyecto eliminado exitosamente.';
    header('Location: crear_proyectos.php');
    exit();
} else {
    $_SESSION['mensaje'] = 'Error: No se pudo eliminar el proyecto.';
    header('Location: crear_proyectos.php');
    exit();
}