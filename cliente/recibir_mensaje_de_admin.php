<?php
session_start();
require_once '../config/db.php';

// Verificar si está logueado y es cliente
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'cliente') {
    header('Location: ../public/login.php');
    exit();
}

// Obtener el ID del cliente desde la sesión
$cliente_id = $_SESSION['usuario']['id'];

// Obtener los mensajes recibidos por el cliente, incluyendo el nombre del administrador remitente
$stmt = $pdo->prepare("SELECT m.asunto, m.contenido, m.enviado_en, p.nombre AS remitente 
                       FROM mensajes m
                       JOIN personas p ON m.remitente_id = p.id
                       WHERE m.destinatario_id = ? 
                       ORDER BY m.enviado_en DESC");
$stmt->execute([$cliente_id]);
$mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener lista de administradores para el buscador
$stmtAdmin = $pdo->query("SELECT nombre FROM personas WHERE rol = 'admin'");
$adminList = $stmtAdmin->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mensajes Recibidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
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
            min-height: 100vh;
            padding: 40px 0 0 0;
            display: block;
            background: linear-gradient(120deg, #e0e7ff 0%, #f8fafc 100%);
        }
        .mensajes-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 8px 40px 0 rgba(30,41,59,0.13);
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            padding: 2.2rem 2.2rem 1.5rem 2.2rem;
        }
        .mensajes-title {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-size: 2.2rem;
            font-weight: 700;
            color: #2563eb;
        }
        .mensajes-desc {
            color: #64748b;
            margin-top: 0.5rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        .buscador-mensajes {
            max-width: 340px;
            border-radius: 10px;
            box-shadow: 0 1px 8px 0 rgba(30,41,59,0.07);
            text-align: center;
            margin: 0 auto 2rem auto;
            display: block;
        }
        .table-mensajes thead th {
            background: #2563eb !important;
            color: #fff !important;
            text-align: center;
            vertical-align: middle;
            font-size: 1.08rem;
            font-weight: 600;
            border: none;
        }
        .table-mensajes tbody tr {
            background: #f8fafc;
            transition: background 0.18s;
        }
        .table-mensajes tbody tr:hover {
            background: #e0e7ff;
        }
        .table-mensajes td {
            text-align: center;
            vertical-align: middle;
            font-size: 1.05rem;
            border: none;
        }
        @media (max-width: 900px) {
            .mensajes-card {
                max-width: 98vw;
                padding: 1.2rem 0.5rem;
            }
        }
        @media (max-width: 600px) {
            .sidebar {
                display: none;
            }
            .mensajes-card {
                width: 100vw;
                padding: 1rem 0.2rem;
            }
            .buscador-mensajes {
                max-width: 98vw;
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
            <i class="ri-user-line" style="font-size:1.5rem;vertical-align:middle;margin-right:8px;"></i>
            Panel del Cliente
        </h4>
        <a href="panel.php"><i class="ri-home-5-line"></i> Inicio</a>
        <a href="proyecto.php"><i class="ri-folder-2-line"></i> Mis Proyectos</a>
        <a href="mensaje.php" ><i class="ri-mail-send-line"></i> Mensajes</a>
        <a href="recibir_mensaje_de_admin.php" class="active"><i class="ri-mail-unread-line"></i> Mensajes Recibidos</a>
        <a href="info.php"><i class="ri-user-settings-line"></i> Información</a>
        <a href="../public/login.php"><i class="ri-logout-box-r-line"></i> Cerrar Sesión</a>
    </div>

    <!-- Contenido principal -->
    <div class="content">
        <div class="mensajes-card">
            <div class="mensajes-title"><i class="ri-message-3-line"></i> Mensajes del Administrador</div>
            <div class="mensajes-desc">
                Aquí puedes ver todos los mensajes que te envió el administrador.
            </div>
            <div class="buscador-mensajes mb-3">
                <select id="buscadoradmin" class="form-select">
                    <option value="">Buscar por Administrador...</option>
                    <?php foreach ($adminList as $admin): ?>
                        <option value="<?= htmlspecialchars($admin['nombre']) ?>"><?= htmlspecialchars($admin['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="table-responsive">
                <table class="table table-mensajes align-middle" id="tablaMensajes">
                    <thead>
                        <tr>
                            <th>Remitente</th>
                            <th>Asunto</th>
                            <th>Contenido</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($mensajes) > 0): ?>
                            <?php foreach ($mensajes as $mensaje): ?>
                                <tr>
                                    <td><?= htmlspecialchars($mensaje['remitente']) ?></td>
                                    <td><?= htmlspecialchars($mensaje['asunto']) ?></td>
                                    <td><?= htmlspecialchars($mensaje['contenido']) ?></td>
                                    <td><?= htmlspecialchars($mensaje['enviado_en']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No tienes mensajes.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    // Buscador en la tabla por administrador
    document.getElementById('buscadoradmin').addEventListener('change', function() {
        let filtro = this.value.toLowerCase();
        let filas = document.querySelectorAll('#tablaMensajes tbody tr');
        filas.forEach(function(fila) {
            let remitente = fila.querySelector('td') ? fila.querySelector('td').textContent.toLowerCase() : '';
            fila.style.display = filtro === "" || remitente.includes(filtro) ? '' : 'none';
        });
    });
</script>
</body>
</html>