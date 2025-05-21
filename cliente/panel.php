<?php
session_start();
require_once '../config/db.php';

// Verificar si está logueado y es cliente
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'cliente') {
    header('Location: ../public/login.php');
    exit();
}

// Obtener datos del cliente desde la sesión
$cliente_nombre = $_SESSION['usuario']['nombre'];
$cliente_id = $_SESSION['usuario']['id'];

// FOTO DE PERFIL ROBUSTA
$foto = '../assets/img/user-default.png';
if (
    isset($_SESSION['usuario']['foto']) &&
    $_SESSION['usuario']['foto'] &&
    file_exists(__DIR__ . '/../assets/img/' . $_SESSION['usuario']['foto'])
) {
    $foto = '../assets/img/' . $_SESSION['usuario']['foto'];
}

// Contar proyectos asignados y mensajes recibidos
$total_proyectos = $pdo->prepare("SELECT COUNT(*) FROM proyectos WHERE persona_id = ?");
$total_proyectos->execute([$cliente_id]);
$total_proyectos = $total_proyectos->fetchColumn();

$total_mensajes = $pdo->prepare("SELECT COUNT(*) FROM mensajes WHERE destinatario_id = ?");
$total_mensajes->execute([$cliente_id]);
$total_mensajes = $total_mensajes->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            background: linear-gradient(120deg, #e0e7ff 0%, #f8fafc 100%);
            font-family: 'Poppins', Arial, Helvetica, sans-serif;
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
            padding: 40px 40px 30px 40px;
            display: flex;
            flex-direction: column;
        }
        .welcome-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 24px 0 rgba(30,41,59,0.07);
            padding: 2.2rem 2rem 2rem 2rem;
            margin-bottom: 2.2rem;
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
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            width: 100%;
        }
        .dashboard-card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 4px 24px 0 rgba(30,41,59,0.08);
            background: #fff;
            min-width: 220px;
            min-height: 180px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: transform 0.15s;
        }
        .dashboard-card:hover {
            transform: translateY(-6px) scale(1.03);
            box-shadow: 0 8px 32px 0 rgba(37,99,235,0.13);
        }
        .dashboard-icon {
            font-size: 2.3rem;
            margin-bottom: 0.7rem;
        }
        .dashboard-icon.proyectos { color: #22c55e; }
        .dashboard-icon.mensajes { color: #f59e42; }
        .dashboard-title {
            font-size: 1.13rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .dashboard-value {
            font-size: 2.1rem;
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
        @media (max-width: 900px) {
            .dashboard-row {
                grid-template-columns: 1fr;
                gap: 1.2rem;
            }
            .content {
                padding: 20px 5px;
            }
            .sidebar {
                width: 100vw;
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
            <i class="ri-user-line" style="font-size:1.5rem;vertical-align:middle;margin-right:8px;"></i>
            Panel del Cliente
        </h4>
        <a href="panel.php" class="active"><i class="ri-home-5-line"></i> Inicio</a>
        <a href="proyecto.php"><i class="ri-folder-2-line"></i> Mis Proyectos</a>
        <a href="mensaje.php"><i class="ri-mail-send-line"></i> Mensajes</a>
        <a href="recibir_mensaje_de_admin.php"><i class="ri-mail-unread-line"></i> Mensajes Recibidos</a>
        <a href="info.php"><i class="ri-user-settings-line"></i> Información</a>
        <a href="../public/login.php"><i class="ri-logout-box-r-line"></i> Cerrar Sesión</a>
    </div>
    <!-- Contenido principal -->
    <div class="content">
        <!-- Bienvenida y usuario a la derecha dentro del fondo blanco -->
        <div class="welcome-card mb-4">
            <div>
                <h2>Bienvenido, <?= htmlspecialchars($cliente_nombre) ?></h2>
                <p>Hola, aquí tienes un resumen de tu información y accesos rápidos.</p>
            </div>
            <div class="user-menu-panel">
                <div class="dropdown">
                    <a href="#" class="dropdown-user text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                       <img src="<?= (isset($_SESSION['usuario']['foto']) && $_SESSION['usuario']['foto'] && file_exists(__DIR__ . '/../assets/img/' . $_SESSION['usuario']['foto']))
    ? '../assets/img/' . htmlspecialchars($_SESSION['usuario']['foto'])
    : '../assets/img/user-default.png' ?>" alt="Foto de perfil">
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
                <div class="dashboard-icon proyectos"><i class="ri-folder-2-line"></i></div>
                <div class="dashboard-title">Proyectos Asignados</div>
                <div class="dashboard-value"><?= $total_proyectos ?></div>
                <a href="proyecto.php" class="dashboard-link">Ver Proyectos</a>
            </div>
            <div class="dashboard-card text-center py-4">
                <div class="dashboard-icon mensajes"><i class="ri-mail-send-line"></i></div>
                <div class="dashboard-title">Mensajes Enviados</div>
                <div class="dashboard-value">
                    <?php
                        $total_enviados = $pdo->prepare("SELECT COUNT(*) FROM mensajes WHERE remitente_id = ?");
                        $total_enviados->execute([$cliente_id]);
                        echo $total_enviados->fetchColumn();
                    ?>
                </div>
                <a href="mensaje.php" class="dashboard-link">Ver Enviados</a>
            </div>
            <div class="dashboard-card text-center py-4">
                <div class="dashboard-icon mensajes"><i class="ri-mail-unread-line"></i></div>
                <div class="dashboard-title">Mensajes Recibidos</div>
                <div class="dashboard-value"><?= $total_mensajes ?></div>
                <a href="recibir_mensaje_de_admin.php" class="dashboard-link">Ver Recibidos</a>
            </div>
            <div class="dashboard-card text-center py-4">
                <div class="dashboard-icon"><i class="ri-user-settings-line"></i></div>
                <div class="dashboard-title">Información Personal</div>
                <div class="dashboard-value">
                    <i class="ri-user-3-line" style="font-size:2.1rem;color:#2563eb;"></i>
                </div>
                <a href="info.php" class="dashboard-link">Ver Información</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>