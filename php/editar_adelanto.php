<?php
require_once __DIR__.'/conexion.php';

$id_adelanto = $_GET['id'] ?? null;
$adelanto = null;
$mensaje_error = null;

// Obtener la lista de trabajadores activos
$trabajadores = $pdo->query('SELECT id,nombre FROM trabajadores WHERE activo = TRUE ORDER BY nombre')->fetchAll();

// --- LÓGICA DE ACTUALIZACIÓN (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $trabajador = $_POST['trabajador'] ?? '';
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $monto = $_POST['monto'] ?? 0;
    $obs = $_POST['observacion'] ?? '';
    
    if ($id) {
        try {
            // Actualiza la tabla adelantos
            $stmt = $pdo->prepare('UPDATE adelantos SET trabajador_id=?, fecha=?, monto=?, observacion=? WHERE id=?');
            $stmt->execute([$trabajador, $fecha, $monto, $obs, $id]);
            
            // Redireccionar a la lista con un mensaje de éxito
            header('Location: adelantos.php?mensaje=actualizado');
            exit;
        } catch (PDOException $e) {
            $mensaje_error = "Error al actualizar: " . $e->getMessage();
        }
    } else {
        $mensaje_error = "ID de adelanto no proporcionado para la actualización.";
    }
} 

// --- LÓGICA DE CARGA DE DATOS (GET) ---
if ($id_adelanto) {
    // Obtenemos los datos del adelanto específico
    $stmt = $pdo->prepare('SELECT * FROM adelantos WHERE id = ?');
    $stmt->execute([$id_adelanto]);
    $adelanto = $stmt->fetch();
    
    if (!$adelanto) {
        $mensaje_error = "Adelanto no encontrado.";
    }
} else {
    $mensaje_error = "ID de adelanto no proporcionado.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Editar Adelanto - Sistema de Pagos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .menu-icon { display: flex; flex-direction: column; justify-content: space-around; width: 24px; height: 24px; }
        .menu-icon span { display: block; width: 100%; height: 3px; background: currentColor; border-radius: 9999px; transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out; }
        .menu-open span:nth-child(1) { transform: translateY(10.5px) rotate(45deg); }
        .menu-open span:nth-child(2) { opacity: 0; }
        .menu-open span:nth-child(3) { transform: translateY(-10.5px) rotate(-45deg); }
    </style>
</head>
<body class="min-h-screen bg-gray-100 text-gray-900 transition-colors duration-300">
    <button id="menu-toggle" class="fixed top-4 left-4 z-50 p-3 rounded-full bg-indigo-600 text-white shadow-xl lg:hidden focus:outline-none focus:ring-4 focus:ring-indigo-500/50 transition" aria-label="Abrir Menú"><div id="menu-icon" class="menu-icon"><span></span><span></span><span></span></div></button>

    <div class="lg:grid lg:grid-cols-[280px_1fr] min-h-screen">
        <nav id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-gray-800 text-white z-40 transition-transform duration-300 transform -translate-x-full shadow-2xl lg:shadow-none lg:relative lg:translate-x-0 lg:flex lg:flex-col lg:h-auto">
             <div class="p-6 border-b border-gray-700"><h2 class="text-2xl font-bold text-indigo-400">Menú Principal</h2></div>
             <div class="flex-1 p-4 space-y-2 overflow-y-auto">
                 <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition" href="listar.php">👷‍♂️ Trabajadores</a>
                 <a class="block px-4 py-3 rounded-lg text-white font-semibold bg-indigo-700" href="adelantos.php">💰 Adelantos (Actual)</a>
                 <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition" href="horas_extras.php">⏱️ Horas Extras</a>
                 <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition" href="faltas_retrasos.php">📅 Faltas / Retrasos</a>
                 <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition" href="bonos.php">🎁 Bonos</a>
                 <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition" href="reporte.php">📊 Reporte Quincenal</a>
                 <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition" href="historial_pagos.php">📋 Historial de Pagos</a>
             </div>
             <div class="p-4 border-t border-gray-700"><a href="../logout.php" class="block text-sm text-red-400 hover:text-red-300 transition font-medium">🚪 Cerrar Sesión</a></div>
         </nav>

        <div class="flex flex-col flex-1 lg:pl-0 pt-16 lg:pt-0">
            <header class="app-header bg-white shadow-md p-4 lg:p-6 sticky top-0 z-30">
                <h1 class="text-2xl font-extrabold text-gray-800">Editar Adelanto</h1>
            </header>

            <main class="container p-6 flex-1 flex justify-center">
                <div class="w-full max-w-lg bg-white p-8 rounded-xl shadow-2xl border border-gray-200">
                    
                    <?php if ($mensaje_error): ?>
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6" role="alert">
                            <p class="font-bold">Error</p>
                            <p><?php echo htmlspecialchars($mensaje_error); ?></p>
                            <p class="mt-2"><a href="adelantos.php" class="text-sm text-red-500 hover:text-red-700 font-medium">← Volver a Adelantos</a></p>
                        </div>
                    <?php elseif ($adelanto): ?>
                        <h2 class="text-xl font-bold mb-6 text-gray-700">Adelanto ID: **<?php echo htmlspecialchars($adelanto['id']); ?>**</h2>

                        <form method="post" action="editar_adelanto.php" class="space-y-6">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($adelanto['id']); ?>">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="trabajador" class="block text-sm font-medium text-gray-700 mb-1">Trabajador</label>
                                    <select id="trabajador" name="trabajador" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition">
                                        <option value="">— Seleccioná trabajador —</option>
                                        <?php foreach($trabajadores as $t): ?>
                                            <option value="<?php echo $t['id']; ?>" <?php echo ($t['id'] == $adelanto['trabajador_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($t['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label for="fecha" class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                                    <input 
                                        id="fecha"
                                        name="fecha" 
                                        type="date" 
                                        value="<?php echo htmlspecialchars($adelanto['fecha']); ?>"
                                        required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition" 
                                    />
                                </div>
                            </div>

                            <div>
                                <label for="monto" class="block text-sm font-medium text-gray-700 mb-1">Monto ($)</label>
                                <input 
                                    id="monto"
                                    name="monto" 
                                    value="<?php echo htmlspecialchars($adelanto['monto']); ?>"
                                    required 
                                    type="number" 
                                    step="0.01" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition" 
                                />
                            </div>

                            <div>
                                <label for="observacion" class="block text-sm font-medium text-gray-700 mb-1">Observación</label>
                                <textarea 
                                    id="observacion"
                                    name="observacion" 
                                    rows="2" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition resize-y"
                                ><?php echo htmlspecialchars($adelanto['observacion']); ?></textarea>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row sm:justify-between items-center pt-2 space-y-4 sm:space-y-0">
                                <button class="w-full sm:w-auto px-6 py-3 bg-indigo-600 text-white font-bold rounded-lg shadow-md hover:bg-indigo-700 transition duration-300" type="submit">
                                    💾 Guardar Cambios
                                </button>
                                
                                <a href="adelantos.php" class="text-sm text-gray-500 hover:text-indigo-600 transition duration-150">
                                    ← Cancelar y Volver a Historial
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </main>

            <footer class="app-footer p-4 text-center border-t border-gray-200 mt-8 bg-white">
                &copy; <span id="year-footer-editar"></span> Sistema de Pagos. 😎
            </footer>
        </div>
    </div>
    
    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden transition-opacity duration-300 opacity-0" aria-hidden="true"></div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('year-footer-editar').textContent = new Date().getFullYear();
            // Lógica del menú
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
                    setTimeout(() => { overlay.classList.add('hidden'); }, 300);
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
            if (isMobile()) {
                sidebar.classList.add('-translate-x-full');
            }
        });
    </script>
</body>
</html>