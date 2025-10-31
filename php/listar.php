<?php
require_once __DIR__.'/conexion.php';
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../index.php');
    exit;
}

try {
    $stmt = $pdo->query('SELECT * FROM trabajadores ORDER BY id DESC');
    $rows = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al obtener datos: " . $e->getMessage());
}

$mensaje_estado = null;
if (isset($_GET['mensaje'])) {
    if ($_GET['mensaje'] === 'guardado') {
        $mensaje_estado = ['tipo' => 'success', 'texto' => '✅ Trabajador guardado correctamente.'];
    } elseif ($_GET['mensaje'] === 'actualizado') {
        $mensaje_estado = ['tipo' => 'success', 'texto' => '✅ Trabajador actualizado correctamente.'];
    } elseif ($_GET['mensaje'] === 'eliminado') {
        $mensaje_estado = ['tipo' => 'success', 'texto' => '🗑️ Trabajador eliminado correctamente.'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Trabajadores - Sistema de Pagos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Scroll moderno */
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

        /* Animación menú */
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

    <!-- Botón menú -->
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
                <a class="block px-4 py-3 rounded-lg text-white font-semibold bg-indigo-700" href="listar.php">👷‍♂️ Trabajadores (Actual)</a>
                <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition" href="adelantos.php">💰 Adelantos</a>
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
                <h1 class="text-2xl font-extrabold text-gray-800">Trabajadores</h1>
            </header>

            <main class="container p-6 flex-1">
                <a href="agregar.php" class="inline-block px-6 py-3 mb-8 bg-indigo-600 text-white font-bold rounded-lg shadow-md hover:bg-indigo-700 transition">➕ Agregar Trabajador</a>
                
                <?php if ($mensaje_estado): ?>
                    <div class="<?php echo $mensaje_estado['tipo'] === 'success' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-red-100 border-red-500 text-red-700'; ?> 
                        border-l-4 p-4 rounded-lg mb-8" role="alert">
                        <p class="font-bold"><?php echo $mensaje_estado['texto']; ?></p>
                    </div>
                <?php endif; ?>

                <?php if(count($rows)==0): ?>
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-lg" role="alert">
                        <p class="font-bold">Aviso:</p>
                        <p>No hay trabajadores. Agregá uno para comenzar.</p>
                    </div>
                <?php else: ?>
                    
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                        <div class="overflow-x-auto">
                            <div class="max-h-[500px] overflow-y-auto scroll-custom">
                                <table class="w-full text-left min-w-[850px]">
                                    <thead class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal sticky top-0 z-10">
                                        <tr>
                                            <th class="py-3 px-6 text-left">Nombre</th>
                                            <th class="py-3 px-6 text-left">Cargo</th>
                                            <th class="py-3 px-6 text-right">Sueldo Base (por hora)</th>
                                            <th class="py-3 px-6 text-left">Ingreso</th>
                                            <th class="py-3 px-6 text-center">Acciones</th> 
                                        </tr>
                                    </thead>
                                    <tbody id="tabla-trabajadores" class="text-gray-600 text-sm font-light">
                                        <?php foreach($rows as $r): ?>
                                            <tr class="border-b border-gray-200 hover:bg-indigo-50 cursor-pointer transition">
                                                <td class="py-3 px-6 text-left whitespace-nowrap font-medium text-gray-800">
                                                    <?php echo htmlspecialchars($r['nombre']); ?>
                                                </td>
                                                <td class="py-3 px-6 text-left whitespace-nowrap">
                                                    <?php echo htmlspecialchars($r['cargo']); ?>
                                                </td>
                                                <td class="py-3 px-6 text-right font-mono whitespace-nowrap">
                                                    $<?php echo number_format($r['sueldo_hora'], 2, ',', '.'); ?>
                                                </td>
                                                <td class="py-3 px-6 text-left whitespace-nowrap">
                                                    <?php echo date('d-m-Y', strtotime($r['fecha_ingreso'])); ?>
                                                </td>
                                                <td class="py-3 px-6 text-center whitespace-nowrap space-x-2">
                                                    <a href="editar_trabajador.php?id=<?php echo htmlspecialchars($r['id']); ?>" 
                                                       class="inline-flex items-center justify-center p-2 text-indigo-600 hover:text-indigo-800 transition duration-150 rounded-full hover:bg-indigo-100" 
                                                       title="Editar Trabajador">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5">
                                                            <path fill="#4f46e5" d="M3 17.25V21h3.75l11-11.03-3.75-3.75L3 17.25z"/>
                                                            <path fill="#4f46e5" d="M20.71 7.04a1 1 0 000-1.41l-2.34-2.34a1 1 0 00-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                                        </svg>
                                                    </a>
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
            const filas = document.querySelectorAll('#tabla-trabajadores tr');
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
