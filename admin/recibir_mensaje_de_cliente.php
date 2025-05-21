<?php
session_start();
require_once '../config/db.php';

// Verificar si está logueado y es administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ../public/login.php');
    exit();
}

// Obtener clientes para el buscador
$stmtClientes = $pdo->query("SELECT DISTINCT p.id, p.nombre FROM personas p JOIN mensajes m ON m.remitente_id = p.id WHERE m.destinatario_id = " . intval($_SESSION['usuario']['id']));
$clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

// Obtener los mensajes enviados por los clientes al administrador
$stmt = $pdo->prepare("SELECT m.asunto, m.contenido, m.enviado_en, p.nombre AS remitente, p.correo AS correo_remitente 
                       FROM mensajes m
                       JOIN personas p ON m.remitente_id = p.id
                       WHERE m.destinatario_id = ? 
                       ORDER BY m.enviado_en DESC");
$stmt->execute([$_SESSION['usuario']['id']]);
$mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mensajes de Clientes</title>
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
            /* max-width: 120px; */
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
    padding: 0;
    display: flex;
    align-items: flex-start;      /* Arriba */
    justify-content: center;      /* Centro horizontal */
    background: linear-gradient(120deg, #e0e7ff 0%, #f8fafc 100%);
}
.mensajes-card {
    background: #fff;
    border-radius: 24px;
    box-shadow: 0 8px 40px 0 rgba(30,41,59,0.13);
    width: 100%;
    max-width: 1200px;
    margin: 2.5rem 0 0 0; /* Solo margen arriba */
    padding: 2.2rem 2.2rem 1.5rem 2.2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
}
        .mensajes-title {
            font-size: 2rem;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .buscador-mensajes {
            width: 100%;
            max-width: 400px;
            margin-bottom: 1.5rem;
        }
        .table-responsive {
            width: 100%;
        }
        .table thead th {
            background: #2563eb;
            color: #fff;
            font-weight: 600;
            text-align: center;
        }
        .table td, .table th {
            vertical-align: middle !important;
            text-align: center;
        }
        @media (max-width: 1300px) {
            .mensajes-card {
                max-width: 98vw;
                padding: 1.2rem 0.5rem;
            }
        }
        @media (max-width: 900px) {
            .mensajes-card {
                max-width: 98vw;
                padding: 1rem 0.5rem;
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
    <!-- Sidebar izquierda -->
    <div class="sidebar">
        <div class="logo-container">
            <img src="../assets/img/logoBlanco.png" alt="Logo">
        </div>
        <h4>
            <i class="ri-admin-line" style="font-size:2rem;vertical-align:middle;margin-right:8px;"></i>
            Panel de Administración
        </h4>
        <a href="panel.php"><i class="ri-home-5-line"></i> Inicio</a>
        <a href="clientes.php"><i class="ri-group-line"></i> Lista de Clientes</a>
        <a href="crear_proyectos.php"><i class="ri-folder-add-line"></i> Crear Proyecto</a>
        <a href="mensaje.php"><i class="ri-mail-send-line"></i> Enviar Mensaje</a>
        <a href="recibir_mensaje_de_cliente.php" class="active"><i class="ri-message-3-line"></i> Mensajes de Clientes</a>
        <a href="info.php"><i class="ri-user-settings-line"></i> Información Personal</a>
        <a href="../public/login.php"><i class="ri-logout-box-r-line"></i> Cerrar Sesión</a>
    </div>

    <!-- Contenido principal -->
    <div class="content">
        <div class="mensajes-card">
            <div class="mensajes-title"><i class="ri-message-3-line"></i> Mensajes de Clientes</div>
            <div class="buscador-mensajes mb-3">
                <select id="buscadorCliente" class="form-select">
                    <option value="">Buscar por cliente...</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= htmlspecialchars($cliente['nombre']) ?>"><?= htmlspecialchars($cliente['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle" id="tablaMensajes">
                    <thead>
                        <tr>
                            <th>Remitente</th>
                            <th>Correo</th>
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
                                    <td><?= htmlspecialchars($mensaje['correo_remitente']) ?></td>
                                    <td><?= htmlspecialchars($mensaje['asunto']) ?></td>
                                    <td><?= htmlspecialchars($mensaje['contenido']) ?></td>
                                    <td><?= htmlspecialchars($mensaje['enviado_en']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No hay mensajes de clientes.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        // Filtrar mensajes por cliente
        document.getElementById('buscadorCliente').addEventListener('change', function() {
            const filtro = this.value.toLowerCase();
            const filas = document.querySelectorAll('#tablaMensajes tbody tr');
            filas.forEach(fila => {
                const remitente = fila.children[0].textContent.toLowerCase();
                if (!filtro || remitente.includes(filtro)) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>