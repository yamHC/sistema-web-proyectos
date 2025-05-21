<?php

session_start();
require_once '../config/db.php';

// Verificar si está logueado y es cliente
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'cliente') {
    header('Location: ../public/login.php');
    exit();
}

// Obtener la información del cliente desde la sesión
$cliente_id = $_SESSION['usuario']['id'];

// Consultar la información del cliente desde la base de datos
$stmt = $pdo->prepare("SELECT nombre, apellido, correo, empresa, foto, creado_en FROM personas WHERE id = ?");
$stmt->execute([$cliente_id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

// Manejar la actualización de la información
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo = $_POST['correo'];
    $empresa = $_POST['empresa'];

    // Manejar foto de perfil
    $foto = $cliente['foto'];
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto = uniqid('foto_') . '.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], "../assets/img/$foto");
    }

    $stmt = $pdo->prepare("UPDATE personas SET nombre = ?, apellido = ?, correo = ?, empresa = ?, foto = ? WHERE id = ?");
    $stmt->execute([$nombre, $apellido, $correo, $empresa, $foto, $cliente_id]);

    // ACTUALIZAR LA SESIÓN para que el panel muestre la foto y datos nuevos
    $_SESSION['usuario']['nombre'] = $nombre;
    $_SESSION['usuario']['apellido'] = $apellido;
    $_SESSION['usuario']['correo'] = $correo;
    $_SESSION['usuario']['empresa'] = $empresa;
    $_SESSION['usuario']['foto'] = $foto;

    $_SESSION['mensaje'] = 'Información actualizada correctamente.';
    header('Location: info.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Información Personal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', Arial, Helvetica, sans-serif !important;
            background: linear-gradient(120deg, #e0e7ff 0%, #f8fafc 100%);
        }
        .sidebar, .sidebar a, .sidebar h4, .sidebar .logo-container {
            font-family: 'Poppins', Arial, Helvetica, sans-serif !important;
        }
        .sidebar {
            width: 380px;
            background: linear-gradient(180deg,rgb(0, 36, 134) 0%,rgb(2, 131, 217) 100%);
            color: #fff;
            padding: 40px 28px 28px 28px;
            min-height: 100vh;
            box-shadow: 2px 0 16px 0 rgba(30,41,59,0.08);
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .sidebar .logo-container {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 1.8rem;
        }
        .sidebar .logo-container img {
            max-height: 140px;
            border-radius: 12px;
            background: transparent;
            box-shadow: none;
            padding: 0;
        }
        .sidebar h4 {
            font-weight: 700;
            margin-bottom: 2.5rem;
            letter-spacing: 1px;
            text-align: center;
            font-size: 1.5rem;
            width: 100%;
        }
        .sidebar a {
            color: #e0e7ff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            border-radius: 10px;
            margin-bottom: 14px;
            font-size: 1.13rem;
            transition: background 0.18s;
            width: 100%;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #2563eb;
            color: #fff;
        }
        .sidebar a:last-child {
            margin-top: 2.5rem;
            background: #f87171;
            color: #fff;
            font-weight: 600;
        }
        .sidebar a:last-child:hover {
            background: #dc2626;
        }
        .main-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 0;
            position: relative;
        }
        .info-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 8px 40px 0 rgba(30,41,59,0.13);
            width: 100%;
            max-width: 500px;
            padding: 2.2rem 2.2rem 1.5rem 2.2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
        }
        .info-avatar {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #2563eb;
            background: #f1f5f9;
            margin-bottom: 1.2rem;
        }
        .info-title {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 1.5rem;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 1.5rem;
        }
        .info-label {
            font-weight: 600;
            color: #2563eb;
            margin-bottom: 0.2rem;
        }
        .info-input {
            background: #f8fafc;
            border: none;
            border-radius: 10px;
            margin-bottom: 1.1rem;
            font-size: 1.08rem;
            color: #334155;
            padding: 0.7rem 1rem;
            width: 100%;
        }
        .info-input[readonly] {
            color: #334155;
        }
        .edit-btn {
            background: #2563eb;
            color: #fff;
            font-weight: 600;
            border-radius: 10px;
            min-width: 160px;
            font-size: 1.1rem;
            padding: 10px 0;
            border: none;
            margin-top: 1rem;
            transition: background 0.18s;
        }
        .edit-btn:hover {
            background: #1d4ed8;
        }
        /* Panel de edición lateral */
        .edit-panel {
            position: fixed;
            top: 0;
            right: -450px;
            width: 400px;
            height: 100vh;
            background: #fff;
            box-shadow: -4px 0 32px 0 rgba(30,41,59,0.13);
            z-index: 10;
            padding: 2.2rem 2.2rem 1.5rem 2.2rem;
            border-radius: 24px 0 0 24px;
            transition: right 0.35s cubic-bezier(.4,2,.6,1);
            display: flex;
            flex-direction: column;
        }
        .edit-panel.active {
            right: 0;
        }
        .edit-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1.25rem;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 1.5rem;
        }
        .edit-label {
            font-weight: 600;
            color: #2563eb;
            margin-bottom: 0.2rem;
        }
        .edit-input {
            background: #f8fafc;
            border: none;
            border-radius: 10px;
            margin-bottom: 1.1rem;
            font-size: 1.08rem;
            color: #334155;
            padding: 0.7rem 1rem;
            width: 100%;
        }
        .edit-btns {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 1.2rem;
        }
        .btn-cancelar {
            background: #f87171;
            color: #fff;
            font-weight: 600;
            border-radius: 10px;
            min-width: 120px;
            border: none;
            transition: background 0.18s;
        }
        .btn-cancelar:hover {
            background: #dc2626;
        }
        .btn-guardar {
            background: #2563eb;
            color: #fff;
            font-weight: 600;
            border-radius: 10px;
            min-width: 160px;
            border: none;
            transition: background 0.18s;
        }
        .btn-guardar:hover {
            background: #1d4ed8;
        }
        @media (max-width: 900px) {
            .main-container {
                flex-direction: column;
                align-items: center;
                gap: 24px;
            }
            .info-card {
                max-width: 98vw;
                padding: 1.2rem 0.5rem;
            }
            .edit-panel {
                width: 100vw;
                right: -100vw;
                border-radius: 0;
                padding: 1.2rem 0.5rem;
            }
            .edit-panel.active {
                right: 0;
            }
        }
    </style>
</head>
<body>
<div style="display:flex;min-height:100vh;">
    <!-- Sidebar izquierda -->
    <div class="sidebar">
        <div class="logo-container">
            <img src="../assets/img/logoBlanco.png" alt="Logo">
        </div>
        <h4>
            <i class="ri-user-line" style="font-size:2rem;vertical-align:middle;margin-right:8px;"></i>
            Panel del Cliente
        </h4>
        <a href="panel.php"><i class="ri-home-5-line"></i> Inicio</a>
        <a href="proyecto.php"><i class="ri-folder-2-line"></i> Mis Proyectos</a>
        <a href="mensaje.php"><i class="ri-mail-send-line"></i> Mensajes</a>
        <a href="recibir_mensaje_de_admin.php"><i class="ri-mail-unread-line"></i> Mensajes Recibidos</a>
        <a href="info.php" class="active"><i class="ri-user-settings-line"></i> Información Personal</a>
        <a href="../public/login.php"><i class="ri-logout-box-r-line"></i> Cerrar Sesión</a>
    </div>

    <!-- Contenido principal centrado -->
    <div class="main-container">
        <!-- Información Personal -->
        <div class="info-card">
            <img src="<?= ($cliente['foto'] && file_exists(__DIR__ . '/../assets/img/' . $cliente['foto'])) ? "../assets/img/" . htmlspecialchars($cliente['foto']) : "../assets/img/user-default.png" ?>" class="info-avatar" alt="Foto de perfil">
            <div class="info-title"><i class="ri-user-3-line"></i> Información Personal</div>
            <div class="w-100">
                <div class="info-label">Nombre</div>
                <input class="info-input" type="text" value="<?= htmlspecialchars($cliente['nombre']) ?>" readonly>
                <div class="info-label">Apellido</div>
                <input class="info-input" type="text" value="<?= htmlspecialchars($cliente['apellido']) ?>" readonly>
                <div class="info-label">Correo</div>
                <input class="info-input" type="text" value="<?= htmlspecialchars($cliente['correo']) ?>" readonly>
                <div class="info-label">Empresa</div>
                <input class="info-input" type="text" value="<?= $cliente['empresa'] ? htmlspecialchars($cliente['empresa']) : 'No especificada' ?>" readonly>
                <div class="info-label">Fecha de Registro</div>
                <input class="info-input" type="text" value="<?= htmlspecialchars($cliente['creado_en']) ?>" readonly>
            </div>
            <button class="edit-btn" onclick="abrirPanelEdicion()">
                <i class="ri-edit-2-line"></i> Editar
            </button>
        </div>
        <!-- Panel de edición lateral -->
        <div class="edit-panel" id="editPanel">
            <form method="POST" enctype="multipart/form-data">
                <div class="edit-title"><i class="ri-edit-2-line"></i> Editar Información</div>
                <div class="edit-label">Nombre</div>
                <input class="edit-input" type="text" name="nombre" value="<?= htmlspecialchars($cliente['nombre']) ?>" required>
                <div class="edit-label">Apellido</div>
                <input class="edit-input" type="text" name="apellido" value="<?= htmlspecialchars($cliente['apellido']) ?>" required>
                <div class="edit-label">Correo</div>
                <input class="edit-input" type="email" name="correo" value="<?= htmlspecialchars($cliente['correo']) ?>" required>
                <div class="edit-label">Empresa</div>
                <input class="edit-input" type="text" name="empresa" value="<?= htmlspecialchars($cliente['empresa']) ?>">
                <div class="edit-label">Foto de perfil</div>
                <input class="form-control mb-2" type="file" name="foto" accept="image/*">
                <div class="edit-btns">
                    <button type="button" class="btn-cancelar" onclick="cerrarPanelEdicion()">Cancelar</button>
                    <button type="submit" class="btn-guardar">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function abrirPanelEdicion() {
        document.getElementById('editPanel').classList.add('active');
    }
    function cerrarPanelEdicion() {
        document.getElementById('editPanel').classList.remove('active');
    }
    // Cerrar panel con ESC
    document.addEventListener('keydown', function(e){
        if(e.key === "Escape") cerrarPanelEdicion();
    });
</script>
</body>
</html>