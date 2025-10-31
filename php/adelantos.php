<?php
require_once __DIR__.'/conexion.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $trabajador = $_POST['trabajador'] ?? '';
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $monto = $_POST['monto'] ?? 0;
    $obs = $_POST['observacion'] ?? '';

    $stmt = $pdo->prepare('INSERT INTO adelantos (trabajador_id,fecha,monto,observacion) VALUES (?,?,?,?)');
    $stmt->execute([$trabajador,$fecha,$monto,$obs]);

    header('Location: adelantos.php');
    exit;
}

$trabajadores = $pdo->query('SELECT id,nombre FROM trabajadores ORDER BY nombre')->fetchAll();
$rows = $pdo->query('SELECT a.*, t.nombre FROM adelantos a JOIN trabajadores t ON t.id = a.trabajador_id ORDER BY a.fecha DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Adelantos - Sistema de Pagos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Scroll personalizado */
        .scroll-custom::-webkit-scrollbar {
            width: 8px;
        }
        .scroll-custom::-webkit-scrollbar-thumb {
            background-color: rgba(79, 70, 229, 0.6);
            border-radius: 9999px;
        }
        .scroll-custom::-webkit-scrollbar-thumb:hover {
            background-color: rgba(79, 70, 229, 0.8);
        }

        /* Icono menú animado */
        .menu-icon {
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            width: 24px;
            height: 24px;
        }
        .menu-icon span {
            display: block;
            width: 100%;
            height: 3px;
            background: currentColor;
            border-radius: 9999px;
            transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
        }
        .menu-open span:nth-child(1) {
            transform: translateY(10.5px) rotate(45deg);
        }
        .menu-open span:nth-child(2) {
            opacity: 0;
        }
        .menu-open span:nth-child(3) {
            transform: translateY(-10.5px) rotate(-45deg);
        }

        /* Fila seleccionada */
        .selected-row {
            background-color: #e0e7ff !important; /* Indigo-100 */
            transition: background-color 0.3s ease-in-out;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-100 text-gray-900 transition-colors duration-300">

    <!-- Botón menú móvil -->
    <button id="menu-toggle" class="fixed top-4 left-4 z-50 p-3 rounded-full bg-indigo-600 text-white shadow-xl lg:hidden focus:outline-none focus:ring-4 focus:ring-indigo-500/50 transition" aria-label="Abrir Menú">
        <div id="menu-icon" class="menu-icon">
            <span></span><span></span><span></span>
        </div>
    </button>

    <div class="lg:grid lg:grid-cols-[280px_1fr] min-h-screen">

        <!-- Sidebar -->
        <nav id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-gray-800 text-white z-40 
            transition-transform duration-300 transform -translate-x-full shadow-2xl
            lg:shadow-none lg:relative lg:translate-x-0 lg:flex lg:flex-col lg:h-auto">
            
            <div class="p-6 border-b border-gray-700">
                <h2 class="text-2xl font-bold text-indigo-400">Menú Principal</h2>
            </div>

            <div class="flex-1 p-4 space-y-2 overflow-y-auto">
                <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition" href="listar.php">👷‍♂️ Trabajadores</a>
                <a class="block px-4 py-3 rounded-lg text-white font-semibold bg-indigo-700" href="adelantos.php">💰 Adelantos (Actual)</a>
                <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition" href="horas_extras.php">⏱️ Horas Extras</a>
                <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition" href="faltas_retrasos.php">📅 Faltas / Retrasos</a>
                <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition" href="bonos.php">🎁 Bonos</a>
                <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition" href="reporte.php">📊 Reporte Quincenal</a>
                <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition" href="historial_pagos.php">📋 Historial de Pagos</a>
            </div>

            <div class="p-4 border-t border-gray-700">
                <a href="../logout.php" class="block text-sm text-red-400 hover:text-red-300 transition font-medium">🚪 Cerrar Sesión</a>
            </div>
        </nav>

        <!-- Contenido principal -->
        <div class="flex flex-col flex-1 pt-16 lg:pt-0">
            <header class="bg-white shadow-md p-4 lg:p-6 sticky top-0 z-30 flex justify-between items-center">
                <h1 class="text-2xl font-extrabold text-gray-800">Registro de Adelantos</h1>
            </header>

            <main class="container p-6 flex-1">
                <div class="w-full bg-white p-6 md:p-8 rounded-xl shadow-xl border border-gray-200 mb-8">
                    <h2 class="text-xl font-bold mb-6 text-gray-700">Registrar Nuevo Adelanto</h2>

                    <form method="post" action="adelantos.php" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="trabajador" class="block text-sm font-medium text-gray-700 mb-1">Trabajador</label>
                                <select id="trabajador" name="trabajador" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition">
                                    <option value="">— Seleccioná trabajador —</option>
                                    <?php foreach($trabajadores as $t): ?>
                                        <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="fecha" class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                                <input id="fecha" name="fecha" type="date" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition" />
                            </div>
                            <div>
                                <label for="monto" class="block text-sm font-medium text-gray-700 mb-1">Monto ($)</label>
                                <input id="monto" name="monto" type="number" step="0.01" placeholder="Ej. 15000" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition" />
                            </div>
                        </div>

                        <div>
                            <label for="observacion" class="block text-sm font-medium text-gray-700 mb-1">Observación (Opcional)</label>
                            <textarea id="observacion" name="observacion" rows="2" placeholder="Detalles sobre el adelanto..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition resize-y"></textarea>
                        </div>

                        <div class="pt-2">
                            <button class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-lg shadow-md hover:bg-indigo-700 transition" type="submit">💰 Registrar Adelanto</button>
                        </div>
                    </form>
                </div>

                <h2 class="text-2xl font-extrabold text-gray-800 mb-4">Historial de Adelantos</h2>

                <?php if(count($rows)==0): ?>
                    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded-lg">
                        <p class="font-bold">Información:</p>
                        <p>No hay adelantos registrados hasta el momento.</p>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                        <div class="overflow-x-auto">
                            <div class="max-h-[500px] overflow-y-auto scroll-custom">
                                <table class="w-full text-left min-w-[640px]">
                                    <thead class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal sticky top-0 z-10">
                                        <tr>
                                            <th class="py-3 px-6 text-left">Fecha</th>
                                            <th class="py-3 px-6 text-left">Trabajador</th>
                                            <th class="py-3 px-6 text-right">Monto</th>
                                            <th class="py-3 px-6 text-left">Obs</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabla-adelantos" class="text-gray-600 text-sm font-light">
                                        <?php foreach($rows as $r): ?>
                                            <tr class="border-b border-gray-200 hover:bg-indigo-50 cursor-pointer transition">
                                                <td class="py-3 px-6 text-left whitespace-nowrap">
                                                    <?php echo date('d-m-Y', strtotime($r['fecha'])); ?>
                                                </td>
                                                <td class="py-3 px-6 text-left whitespace-nowrap font-medium text-gray-800">
                                                    <?php echo htmlspecialchars($r['nombre']); ?>
                                                </td>
                                                <td class="py-3 px-6 text-right font-mono whitespace-nowrap">
                                                    $<?php echo number_format($r['monto'], 2, ',', '.'); ?>
                                                </td>
                                                <td class="py-3 px-6 text-left">
                                                    <?php echo $r['observacion'] ? htmlspecialchars($r['observacion']) : '—'; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </main>

            <footer class="p-4 text-center border-t border-gray-200 mt-8 bg-white">
                &copy; <span id="year-footer"></span> Sistema de Pagos. 😎
            </footer>
        </div>
    </div>

    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden transition-opacity duration-300 opacity-0" aria-hidden="true"></div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('year-footer').textContent = new Date().getFullYear();

            const menuToggle = document.getElementById('menu-toggle');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const menuIcon = document.getElementById('menu-icon');

            const isMobile = () => window.innerWidth < 1024;

            const toggleMenu = () => {
                const isHidden = sidebar.classList.toggle('-translate-x-full');
                if (!isHidden) {
                    overlay.classList.remove('hidden', 'opacity-0');
                    overlay.classList.add('opacity-100');
                    menuIcon.classList.add('menu-open');
                } else {
                    overlay.classList.remove('opacity-100');
                    overlay.classList.add('opacity-0');
                    setTimeout(() => overlay.classList.add('hidden'), 300);
                    menuIcon.classList.remove('menu-open');
                }
            };

            menuToggle.addEventListener('click', toggleMenu);
            overlay.addEventListener('click', toggleMenu);
            window.addEventListener('resize', () => {
                if (!isMobile()) {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.add('hidden', 'opacity-0');
                    menuIcon.classList.remove('menu-open');
                }
            });
            if (isMobile()) sidebar.classList.add('-translate-x-full');

            // === Selección de filas ===
            const filas = document.querySelectorAll('#tabla-adelantos tr');
            filas.forEach(fila => {
                fila.addEventListener('click', () => {
                    filas.forEach(f => f.classList.remove('selected-row'));
                    fila.classList.add('selected-row');
                });
            });
        });
    </script>
</body>
</html>
