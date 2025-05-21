<?php
session_start();
require_once '../config/db.php';

// Verificar si está logueado y es admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ../public/login.php');
    exit();
}

// Paginación
$por_pagina = 8;
$pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio = ($pagina - 1) * $por_pagina;

// Buscar clientes
$busqueda = $_GET['busqueda'] ?? '';
$sql_base = "FROM personas WHERE rol = 'cliente'";
$params = [];

if ($busqueda) {
    $sql_base .= " AND (nombre LIKE :busqueda OR apellido LIKE :busqueda OR empresa LIKE :busqueda OR correo LIKE :busqueda)";
    $params[':busqueda'] = "%$busqueda%";
}

// Total de clientes para paginación
$stmt_total = $pdo->prepare("SELECT COUNT(*) $sql_base");
$stmt_total->execute($params);
$total_clientes = $stmt_total->fetchColumn();
$total_paginas = ceil($total_clientes / $por_pagina);

// Obtener clientes de la página actual
$sql = "SELECT id, nombre, apellido, empresa, correo $sql_base LIMIT :inicio, :por_pagina";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':inicio', $inicio, PDO::PARAM_INT);
$stmt->bindValue(':por_pagina', $por_pagina, PDO::PARAM_INT);
$stmt->execute();
$clientes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Clientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
            align-items: flex-start;
            justify-content: center;
            background: linear-gradient(120deg, #e0e7ff 0%, #f8fafc 100%);
        }
        .clientes-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 8px 40px 0 rgba(30,41,59,0.13);
            width: 100%;
            max-width: 1200px;
            margin: 2.5rem auto;
            padding: 2.2rem 2.2rem 1.5rem 2.2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .clientes-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .clientes-busqueda {
            width: 100%;
            max-width: 500px;
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
        .btn-warning {
            background: #fbbf24;
            color: #222;
            border: none;
        }
        .btn-warning:hover {
            background: #f59e42;
            color: #fff;
        }
        .btn-danger {
            background: #ef4444;
            color: #fff;
            border: none;
        }
        .btn-danger:hover {
            background: #dc2626;
            color: #fff;
        }
        .modal-content {
            border-radius: 18px;
        }
        .modal-header {
            background: #2563eb;
            color: #fff;
            border-radius: 18px 18px 0 0;
        }
        .modal-title {
            font-weight: 600;
        }
        .btn-close {
            background: #fff;
        }
        .pagination {
            margin-top: 1.5rem;
            justify-content: center;
        }
        .pagination .page-link {
            color: #2563eb;
            font-weight: 600;
            border-radius: 8px;
            margin: 0 2px;
        }
        .pagination .page-item.active .page-link {
            background: #2563eb;
            color: #fff;
            border: none;
        }
        @media (max-width: 1300px) {
            .clientes-card {
                max-width: 98vw;
                padding: 1.2rem 0.5rem;
            }
        }
        @media (max-width: 900px) {
            .clientes-card {
                max-width: 98vw;
                padding: 1rem 0.5rem;
            }
        }
        @media (max-width: 600px) {
            .sidebar {
                display: none;
            }
            .clientes-card {
                width: 100vw;
                padding: 1rem 0.2rem;
            }
            .clientes-busqueda {
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
        <a href="clientes.php" class="active"><i class="ri-group-line"></i> Lista de Clientes</a>
        <a href="crear_proyectos.php"><i class="ri-folder-add-line"></i> Crear Proyecto</a>
        <a href="mensaje.php"><i class="ri-mail-send-line"></i> Enviar Mensaje</a>
        <a href="recibir_mensaje_de_cliente.php"><i class="ri-message-3-line"></i> Mensajes de Clientes</a>
        <a href="info.php"><i class="ri-user-settings-line"></i> Información Personal</a>
        <a href="../public/login.php"><i class="ri-logout-box-r-line"></i> Cerrar Sesión</a>
    </div>

    <!-- Contenido principal -->
    <div class="content">
        <div class="clientes-card">
            <?php if (isset($_SESSION['mensaje'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['mensaje'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['mensaje']); ?>
            <?php endif; ?>

            <div class="clientes-title"><i class="ri-group-line"></i> Lista de Clientes</div>
            <form id="buscador" class="clientes-busqueda" method="get" action="">
                <div class="input-group">
                    <input type="text" id="busqueda" name="busqueda" class="form-control" placeholder="Buscar cliente..." value="<?= htmlspecialchars($busqueda) ?>">
                    <button class="btn btn-primary" type="submit"><i class="ri-search-line"></i></button>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Empresa</th>
                            <th>Correo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-clientes">
                        <?php if (count($clientes) > 0): ?>
                            <?php foreach ($clientes as $cliente): ?>
                                <tr>
                                    <td><?= htmlspecialchars($cliente['nombre']) ?></td>
                                    <td><?= htmlspecialchars($cliente['apellido']) ?></td>
                                    <td><?= htmlspecialchars($cliente['empresa']) ?></td>
                                    <td><?= htmlspecialchars($cliente['correo']) ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editarModal" 
                                            data-id="<?= $cliente['id'] ?>" 
                                            data-nombre="<?= htmlspecialchars($cliente['nombre']) ?>" 
                                            data-apellido="<?= htmlspecialchars($cliente['apellido']) ?>" 
                                            data-empresa="<?= htmlspecialchars($cliente['empresa']) ?>" 
                                            data-correo="<?= htmlspecialchars($cliente['correo']) ?>">
                                            Editar
                                        </button>
                                        <a href="eliminar_cliente.php?id=<?= $cliente['id'] ?>" 
                                           class="btn btn-danger btn-sm btn-eliminar" 
                                           data-id="<?= $cliente['id'] ?>" 
                                           data-nombre="<?= htmlspecialchars($cliente['nombre']) ?>">
                                           Eliminar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No se encontraron clientes.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Paginación -->
            <?php if ($total_paginas > 1): ?>
            <nav>
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $i ?>&busqueda=<?= urlencode($busqueda) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para editar cliente -->
    <div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="editar_cliente.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editarModalLabel">Editar Cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="cliente-id">
                        <div class="mb-3">
                            <label for="cliente-nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="cliente-nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="cliente-apellido" class="form-label">Apellido</label>
                            <input type="text" class="form-control" id="cliente-apellido" name="apellido" required>
                        </div>
                        <div class="mb-3">
                            <label for="cliente-empresa" class="form-label">Empresa</label>
                            <input type="text" class="form-control" id="cliente-empresa" name="empresa" required>
                        </div>
                        <div class="mb-3">
                            <label for="cliente-correo" class="form-label">Correo</label>
                            <input type="email" class="form-control" id="cliente-correo" name="correo" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Pasar datos al modal de edición
        const editarModal = document.getElementById('editarModal');
        editarModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const nombre = button.getAttribute('data-nombre');
            const apellido = button.getAttribute('data-apellido');
            const empresa = button.getAttribute('data-empresa');
            const correo = button.getAttribute('data-correo');

            document.getElementById('cliente-id').value = id;
            document.getElementById('cliente-nombre').value = nombre;
            document.getElementById('cliente-apellido').value = apellido;
            document.getElementById('cliente-empresa').value = empresa;
            document.getElementById('cliente-correo').value = correo;
        });

        // Alerta bonita para eliminar cliente
        document.querySelectorAll('.btn-eliminar').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault(); // Evitar la acción predeterminada del enlace

                const href = this.getAttribute('href');
                const nombre = this.getAttribute('data-nombre');

                Swal.fire({
                    title: `¿Estás seguro de eliminar a ${nombre}?`,
                    text: "¡Esta acción no se puede deshacer!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                });
            });
        });
    </script>
</body>
</html>