<?php
session_start();
require_once '../config/db.php';

// Verificar si está logueado y es admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ../public/login.php');
    exit();
}

// Obtener el nombre y foto del administrador desde la sesión
$admin_nombre = $_SESSION['usuario']['nombre'];
$admin_foto = (isset($_SESSION['usuario']['foto']) && $_SESSION['usuario']['foto'])
    ? '../assets/img/' . $_SESSION['usuario']['foto']
    : '../assets/img/user-default.png';

// Contar usuarios, proyectos y mensajes
$total_usuarios = $pdo->query("SELECT COUNT(*) FROM personas WHERE rol = 'cliente'")->fetchColumn();
$total_proyectos = $pdo->query("SELECT COUNT(*) FROM proyectos")->fetchColumn();
$total_mensajes = $pdo->query("SELECT COUNT(*) FROM mensajes")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(120deg, #e0e7ff 0%, #f8fafc 100%);
            font-family: 'Poppins', Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
        }
        .sidebar, .sidebar a, .sidebar h4, .sidebar .logo-container, .content, .welcome-card, .dashboard-card, .dashboard-title, .dashboard-value, .dashboard-link {
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
        .content {
            flex: 1;
            padding: 50px 50px 30px 50px;
            background: transparent;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .welcome-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 24px 0 rgba(30,41,59,0.07);
            padding: 2.5rem 2rem 2rem 2rem;
            margin-bottom: 2.5rem;
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .welcome-card h2 {
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 0.5rem;
        }
        .welcome-card p {
            color: #64748b;
            font-size: 1.1rem;
            margin-bottom: 0;
        }
        .user-menu-panel {
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
        }
        .user-menu-panel img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #2563eb;
            background: #fff;
        }
        .dropdown-user {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        .dropdown-menu {
            font-family: 'Poppins', Arial, Helvetica, sans-serif !important;
        }
        .dashboard-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 2rem;
            width: 100%;
        }
        .dashboard-card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 4px 24px 0 rgba(30,41,59,0.08);
            transition: transform 0.15s;
            background: #fff;
            min-width: 220px;
            min-height: 210px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .dashboard-card:hover {
            transform: translateY(-6px) scale(1.03);
            box-shadow: 0 8px 32px 0 rgba(37,99,235,0.13);
        }
        .dashboard-icon {
            font-size: 2.7rem;
            margin-bottom: 0.7rem;
        }
        .dashboard-icon.clientes { color: #2563eb; }
        .dashboard-icon.proyectos { color: #22c55e; }
        .dashboard-icon.mensajes { color: #f59e42; }
        .dashboard-icon.info { color: #6366f1; }
        .dashboard-title {
            font-size: 1.18rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .dashboard-value {
            font-size: 2.4rem;
            font-weight: 700;
            margin-bottom: 0.7rem;
        }
        .dashboard-link {
            font-size: 1rem;
            font-weight: 500;
            color: #2563eb;
            text-decoration: none;
        }
        .dashboard-link:hover {
            text-decoration: underline;
        }

        /* ...resto de tu CSS... */
.dashboard-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
    width: 100%;
}
@media (max-width: 1100px) {
    .dashboard-row {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 700px) {
    .dashboard-row {
        grid-template-columns: 1fr;
    }

}
@media (max-width: 900px) {
    .dashboard-row {
        grid-template-columns: 1fr;
        gap: 1.2rem;
    }
}
        @media (max-width: 1200px) {
            .dashboard-row {
                gap: 1.2rem;
            }
            .content {
                padding: 25px 8px;
            }
        }
        @media (max-width: 900px) {
            .dashboard-row {
                grid-template-columns: 1fr 1fr;
                gap: 1.5rem;
            }
            .content {
                padding: 25px 8px;
            }
            .sidebar {
                width: 100vw;
                min-width: unset;
                max-width: unset;
            }
        }
        @media (max-width: 700px) {
            .dashboard-row {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 600px) {
            .sidebar {
                display: none;
            }
            .content {
                padding: 10px 2px;
            }
            .welcome-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar izquierda -->
    <div class="sidebar">
        <div class="logo-container">
            <img src="../assets/img/logoBlanco.png" alt="Logo">
        </div>
        <h4>
            <i class="ri-admin-line" style="font-size:2rem;vertical-align:middle;margin-right:8px;"></i>
            Panel de Administración
        </h4>
        <a href="panel.php" class="active"><i class="ri-home-5-line"></i> Inicio</a>
        <a href="clientes.php"><i class="ri-group-line"></i> Lista de Clientes</a>
        <a href="crear_proyectos.php"><i class="ri-folder-add-line"></i> Crear Proyecto</a>
        <a href="mensaje.php"><i class="ri-mail-send-line"></i> Enviar Mensaje</a>
        <a href="recibir_mensaje_de_cliente.php"><i class="ri-message-3-line"></i> Mensajes de Clientes</a>
        <a href="info.php"><i class="ri-user-settings-line"></i> Información Personal</a>
        <a href="../public/login.php"><i class="ri-logout-box-r-line"></i> Cerrar Sesión</a>
    </div>

    <!-- Contenido principal -->
    <div class="content">
        <!-- Bienvenida y usuario a la derecha dentro del fondo blanco -->
        <div class="welcome-card mb-4">
            <div>
                <h2>Bienvenido, <?= htmlspecialchars($admin_nombre) ?></h2>
                <p>¡Hola! Aquí tienes un resumen general del sistema y accesos rápidos a las principales funciones.</p>
            </div>
            <div class="user-menu-panel">
                <div class="dropdown">
                    <a href="#" class="dropdown-user text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= $admin_foto ?>" alt="Foto de perfil" style="width:120px;height:120px;border-radius:50%;border:2px solid #2563eb;">
                        <i class="ri-arrow-down-s-line" style="font-size:2rem;color:#2563eb;"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownUser" style="min-width:180px;">
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="info.php">
                                <i class="ri-user-settings-line me-2"></i> Editar Perfil
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="../public/login.php">
                                <i class="ri-logout-box-r-line me-2"></i> Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="dashboard-row">
    <div class="dashboard-card text-center py-4">
        <div class="dashboard-icon clientes"><i class="ri-group-line"></i></div>
        <div class="dashboard-title">Clientes Registrados</div>
        <div class="dashboard-value"><?= $total_usuarios ?></div>
        <a href="clientes.php" class="dashboard-link">Ver Clientes</a>
    </div>
    <div class="dashboard-card text-center py-4">
        <div class="dashboard-icon proyectos"><i class="ri-folder-2-line"></i></div>
        <div class="dashboard-title">Proyectos</div>
        <div class="dashboard-value"><?= $total_proyectos ?></div>
        <a href="crear_proyectos.php" class="dashboard-link">Ver Proyectos</a>
    </div>
    <div class="dashboard-card text-center py-4">
        <div class="dashboard-icon mensajes"><i class="ri-mail-unread-line"></i></div>
        <div class="dashboard-title">Mensajes</div>
        <div class="dashboard-value"><?= $total_mensajes ?></div>
        <a href="recibir_mensaje_de_cliente.php" class="dashboard-link">Ver Mensajes</a>
    </div>
    <div class="dashboard-card text-center py-4">
        <div class="dashboard-icon mensajes"><i class="ri-mail-send-line"></i></div>
        <div class="dashboard-title">Enviar Mensaje</div>
        <div class="dashboard-value"><i class="ri-send-plane-2-line"></i></div>
        <a href="mensaje.php" class="dashboard-link">Ir a Enviar Mensaje</a>
    </div>
    <div class="dashboard-card text-center py-4">
        <div class="dashboard-icon info"><i class="ri-user-settings-line"></i></div>
        <div class="dashboard-title">Información Personal</div>
        <div class="dashboard-value"><i class="ri-user-3-line" style="font-size:2.1rem;color:#2563eb;"></i></div>
        <a href="info.php" class="dashboard-link">Ver Información</a>
    </div>
</div>
</body>
</html>