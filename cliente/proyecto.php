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
$cliente_foto = (isset($_SESSION['usuario']['foto']) && $_SESSION['usuario']['foto'])
    ? '../assets/img/' . $_SESSION['usuario']['foto']
    : '../assets/img/user-default.png';
$cliente_id = $_SESSION['usuario']['id'];

// Obtener los proyectos asignados al cliente
$stmt = $pdo->prepare("SELECT id, nombre_proyecto, estado, avance, fecha_publicacion, fecha_finalizacion 
                       FROM proyectos 
                       WHERE persona_id = ?");
$stmt->execute([$cliente_id]);
$proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Proyectos</title>
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
        .table {
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 2px 12px 0 rgba(30,41,59,0.07);
        }
        .table th {
            background: #e0e7ff;
            color: #2563eb;
            font-weight: 600;
            border-bottom: 2px solid #c7d2fe;
        }
        .table td {
            vertical-align: middle;
        }
        .btn-info {
            background: #2563eb;
            border: none;
            color: #fff;
            font-weight: 500;
            border-radius: 8px;
            transition: background 0.18s;
        }
        .btn-info:hover {
            background: #1d4ed8;
            color: #fff;
        }
        @media (max-width: 900px) {
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
    <!-- Imagen de perfil arriba -->
    <div class="logo-container">
            <img src="../assets/img/logoBlanco.png" alt="Logo">
        </div>
    <h4>
        <i class="ri-user-line" style="font-size:1.5rem;vertical-align:middle;margin-right:8px;"></i>
        Panel del Cliente
    </h4>
    <a href="panel.php"><i class="ri-home-5-line"></i> Inicio</a>
<a href="proyecto.php" class="active"><i class="ri-folder-2-line"></i> Mis Proyectos</a>
    <a href="mensaje.php"><i class="ri-mail-send-line"></i> Mensajes</a>
    <a href="recibir_mensaje_de_admin.php"><i class="ri-mail-unread-line"></i> Mensajes Recibidos</a>
    <a href="info.php"><i class="ri-user-settings-line"></i> Información</a>
    <a href="../public/login.php"><i class="ri-logout-box-r-line"></i> Cerrar Sesión</a>
</div>
    <!-- Contenido principal -->
    <div class="content">
        <div class="welcome-card mb-4">
            <div>
                <h2>Mis Proyectos</h2>
                <p>A continuación se muestran los proyectos asignados a tu cuenta:</p>
            </div>
        </div>
        <!-- Tabla de proyectos -->
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Nombre del Proyecto</th>
                        <th>Estado</th>
                        <th>Avance (%)</th>
                        <th>Fecha de Finalización</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($proyectos) > 0): ?>
                        <?php foreach ($proyectos as $proyecto): ?>
                            <tr>
                                <td><?= htmlspecialchars($proyecto['nombre_proyecto']) ?></td>
                                <td><?= htmlspecialchars($proyecto['estado']) ?></td>
                                <td><?= htmlspecialchars($proyecto['avance']) ?>%</td>
                                <td><?= $proyecto['fecha_finalizacion'] ? htmlspecialchars($proyecto['fecha_finalizacion']) : 'No definida' ?></td>
                                <td>
                                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detalleModal" 
                                            data-id="<?= $proyecto['id'] ?>" 
                                            data-nombre="<?= htmlspecialchars($proyecto['nombre_proyecto']) ?>" 
                                            data-estado="<?= htmlspecialchars($proyecto['estado']) ?>" 
                                            data-avance="<?= htmlspecialchars($proyecto['avance']) ?>" 
                                            data-publicacion="<?= htmlspecialchars($proyecto['fecha_publicacion']) ?>" 
                                            data-finalizacion="<?= $proyecto['fecha_finalizacion'] ? htmlspecialchars($proyecto['fecha_finalizacion']) : 'No definida' ?>">
                                        <i class="ri-eye-line"></i> Ver Detalle
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No tienes proyectos asignados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="detalleModal" tabindex="-1" aria-labelledby="detalleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:18px;">
            <div class="modal-header" style="background: #2563eb; color: #fff; border-top-left-radius: 18px; border-top-right-radius: 18px;">
                <h5 class="modal-title" id="detalleModalLabel">
                    <i class="ri-folder-info-line" style="margin-right:8px;"></i>
                    Detalle del Proyecto
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" style="background: #f8fafc;">
                <div class="mb-3 text-center">
                    <i class="ri-folder-2-line" style="font-size:3rem;color:#2563eb;background:#e0e7ff;border-radius:50%;padding:18px;"></i>
                </div>
                <ul class="list-group list-group-flush" style="font-size:1.08rem;">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="ri-file-list-2-line me-2"></i> <strong>Nombre del Proyecto:</strong></span>
                        <span id="modalNombre" class="text-end"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="ri-information-line me-2"></i> <strong>Estado:</strong></span>
                        <span id="modalEstado" class="badge rounded-pill" style="background:#22c55e;color:#fff;font-size:1rem;"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="ri-bar-chart-2-line me-2"></i> <strong>Avance:</strong></span>
                        <span>
                            <span id="modalAvance"></span>%
                            <div class="progress mt-1" style="height: 8px; width:120px; display:inline-block; vertical-align:middle;">
                                <div id="modalAvanceBar" class="progress-bar" role="progressbar" style="background:#2563eb;width:0%"></div>
                            </div>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="ri-calendar-check-line me-2"></i> <strong>Fecha de Publicación:</strong></span>
                        <span id="modalPublicacion" class="text-end"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="ri-calendar-event-line me-2"></i> <strong>Fecha de Finalización:</strong></span>
                        <span id="modalFinalizacion" class="text-end"></span>
                    </li>
                </ul>
            </div>
            <div class="modal-footer" style="background:rgb(255, 255, 255); border-bottom-left-radius: 18px; border-bottom-right-radius: 18px;">
    <button type="button" class="btn" style="background:#dc2626; color:#fff;" data-bs-dismiss="modal">
        <i class="ri-close-line"></i> Cerrar
    </button>
</div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Llenar el modal con los datos del proyecto seleccionado
    const detalleModal = document.getElementById('detalleModal');
    detalleModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget; // Botón que activó el modal
        const nombre = button.getAttribute('data-nombre');
        const estado = button.getAttribute('data-estado');
        const avance = button.getAttribute('data-avance');
        const publicacion = button.getAttribute('data-publicacion');
        const finalizacion = button.getAttribute('data-finalizacion');

        // Actualizar el contenido del modal
        document.getElementById('modalNombre').textContent = nombre;
        document.getElementById('modalEstado').textContent = estado;
        document.getElementById('modalAvance').textContent = avance;
        document.getElementById('modalPublicacion').textContent = publicacion;
        document.getElementById('modalFinalizacion').textContent = finalizacion;

        // Actualizar barra de progreso
        document.getElementById('modalAvanceBar').style.width = avance + '%';
    });
</script>
</body>
</html>