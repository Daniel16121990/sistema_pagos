<?php
require_once __DIR__.'/conexion.php';

// Cargar lista de trabajadores
$trabajadores = $pdo->query("SELECT id, nombre FROM trabajadores ORDER BY nombre")->fetchAll();

// Variables de filtro
$filtro_trabajador = $_GET['trabajador'] ?? '';
$filtro_desde = $_GET['desde'] ?? '';
$filtro_hasta = $_GET['hasta'] ?? '';

// Armar consulta dinámica
$sql = "SELECT p.*, t.nombre 
		FROM pagos_realizados p 
		INNER JOIN trabajadores t ON p.trabajador_id = t.id 
		WHERE 1=1";
$params = [];

if ($filtro_trabajador != '') {
	$sql .= " AND p.trabajador_id = ?";
	$params[] = $filtro_trabajador;
}
if ($filtro_desde != '' && $filtro_hasta != '') {
	// Filtramos por el rango de fecha_registro
	$sql .= " AND DATE(p.fecha_registro) BETWEEN ? AND ?";
	$params[] = $filtro_desde;
	$params[] = $filtro_hasta;
}

$sql .= " ORDER BY p.fecha_registro DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pagos = $stmt->fetchAll();

// Calcular total general
$totalGeneral = 0;
foreach ($pagos as $p) $totalGeneral += $p['pago_neto'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Historial de Pagos - Sistema de Pagos</title>
	<!-- Script para cargar Tailwind CSS -->
	<script src="https://cdn.tailwindcss.com"></script>
	<style>
		/* Configuración de fuente principal */
		body {
			font-family: 'Inter', sans-serif;
		}
		/* Estilo para el icono de menú (hamburguesa) */
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

		/* Clase para cuando el menú está abierto */
		.menu-open span:nth-child(1) {
			transform: translateY(10.5px) rotate(45deg);
		}
		.menu-open span:nth-child(2) {
			opacity: 0;
		}
		.menu-open span:nth-child(3) {
			transform: translateY(-10.5px) rotate(-45deg);
		}
	</style>
</head>

<body class="min-h-screen bg-gray-900 text-gray-100">

	<!-- Botón Flotante para Móviles (Menú Hamburguesa) -->
	<button id="menu-toggle" class="fixed top-4 left-4 z-50 p-3 rounded-full bg-indigo-600 text-white shadow-xl lg:hidden focus:outline-none focus:ring-4 focus:ring-indigo-500/50 transition duration-150 ease-in-out" aria-label="Abrir Menú">
		<div id="menu-icon" class="menu-icon">
			<span></span>
			<span></span>
			<span></span>
		</div>
	</button>

	<!-- Contenedor Principal: Define el layout responsivo -->
	<div class="lg:grid lg:grid-cols-[280px_1fr] min-h-screen">

		<!-- Sidebar / Menú de Navegación -->
		<nav id="sidebar" class="
			/* Mobile: Fijo, oculto, deslizable */
			fixed inset-y-0 left-0 w-64 bg-gray-800 text-white z-40 
			transition-transform duration-300 transform -translate-x-full shadow-2xl
			lg:shadow-none

			/* Desktop: Relativo, siempre visible */
			lg:relative lg:translate-x-0 lg:flex lg:flex-col lg:h-auto
		">
			<div class="p-6 border-b border-gray-700">
				<h2 class="text-2xl font-bold text-indigo-400">Menú Principal</h2>
			</div>
			<div class="flex-1 p-4 space-y-2 overflow-y-auto">
				<!-- Ítems del menú -->
				<a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition duration-200" href="listar.php">
					👷‍♂️ Trabajadores
				</a>
				<a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition duration-200" href="adelantos.php">
					💰 Adelantos
				</a>
				<a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition duration-200" href="horas_extras.php">
					⏱️ Horas Extras
				</a>
				<a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition duration-200" href="faltas_retrasos.php">
					📅 Faltas / Retrasos
				</a>
				<a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition duration-200" href="bonos.php">
					🎁 Bonos
				</a>
				<a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition duration-200" href="reporte.php">
					📊 Reporte Quincenal
				</a>
				<a class="block px-4 py-3 rounded-lg text-white font-semibold bg-indigo-700" href="historial_pagos.php">
					📋 Historial de Pagos (Actual)
				</a>
			</div>
					<!-- Enlace para volver al login -->
			<div class="p-4 border-t border-gray-700">
    			<a href="../logout.php" class="block text-sm text-red-400 hover:text-red-300 transition duration-200 font-medium">
        		🚪 Cerrar Sesión
    			</a>
			</div>
		</nav>

		<!-- Contenido Principal (Scrollable) -->
		<div class="flex flex-col flex-1 lg:pl-0 pt-16 lg:pt-0">
			
			<!-- Encabezado de la Aplicación (Contenido) -->
			<header class="app-header bg-gray-800 shadow-lg p-4 lg:p-6 sticky top-0 z-30 border-b border-indigo-500">
				<h1 class="text-2xl font-extrabold text-indigo-400">📋 Historial de Pagos Realizados</h1>
			</header>

			<!-- Área de Contenido Principal -->
			<main class="container p-6 flex-1">
				
				<!-- Tarjeta del Formulario de Filtro -->
				<div class="w-full bg-gray-800 p-6 md:p-8 rounded-xl shadow-xl border border-gray-700 mb-8">
					<h2 class="text-xl font-bold mb-6 text-gray-300">Opciones de Filtro</h2>

					<form method="get" action="historial_pagos.php" class="space-y-4">
						
						<div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
							<!-- Select Trabajador -->
							<div class="md:col-span-1">
								<label for="trabajador" class="block text-sm font-medium text-gray-400 mb-1">Trabajador</label>
								<select 
									id="trabajador"
									name="trabajador" 
									class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
								>
									<option value="">— Todos los trabajadores —</option>
									<?php foreach($trabajadores as $t): ?>
										<option value="<?php echo $t['id']; ?>" 
											<?php if($filtro_trabajador==$t['id']) echo 'selected'; ?>>
											<?php echo htmlspecialchars($t['nombre']); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
							
							<!-- Input Fecha Desde -->
							<div class="md:col-span-1">
								<label for="desde" class="block text-sm font-medium text-gray-400 mb-1">Fecha de Registro Desde</label>
								<input 
									id="desde"
									name="desde" 
									type="date" 
									value="<?php echo htmlspecialchars($filtro_desde); ?>"
									class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150" 
								/>
							</div>

							<!-- Input Fecha Hasta -->
							<div class="md:col-span-1">
								<label for="hasta" class="block text-sm font-medium text-gray-400 mb-1">Fecha de Registro Hasta</label>
								<input 
									id="hasta"
									name="hasta" 
									type="date" 
									value="<?php echo htmlspecialchars($filtro_hasta); ?>"
									class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150" 
								/>
							</div>
							
							<!-- Botón de Envío -->
							<div class="md:col-span-1 flex justify-end md:justify-start">
								<button class="px-6 py-2 bg-indigo-600 text-white font-bold rounded-lg shadow-md hover:bg-indigo-700 transition duration-300 w-full" 
										type="submit">
									🔍 Filtrar Historial
								</button>
							</div>
						</div>
						
					</form>
				</div>

				<?php if(count($pagos)>0): ?>
					
					<h2 class="text-xl font-bold mb-4 text-gray-300">Resultados (<?php echo count($pagos); ?> registros)</h2>
					
					<!-- Contenedor de la Tabla con Scroll Horizontal -->
					<!-- Clase 'overflow-x-auto' de Tailwind habilita el scroll horizontal si el contenido es más ancho. -->
					<div class="overflow-x-auto rounded-xl shadow-lg border border-gray-700">
						<!-- min-w-[1000px] asegura que la tabla siempre sea lo suficientemente ancha para forzar el scroll en móviles. -->
						<table class="min-w-[1000px] text-left">
							<thead class="bg-gray-700">
								<tr>
									<th class="py-3 px-4 text-xs font-medium uppercase tracking-wider">Fecha registro</th>
									<th class="py-3 px-4 text-xs font-medium uppercase tracking-wider">Trabajador</th>
									<th class="py-3 px-4 text-xs font-medium uppercase tracking-wider">Período (Desde)</th>
									<th class="py-3 px-4 text-xs font-medium uppercase tracking-wider">Período (Hasta)</th>
									<th class="py-3 px-4 text-xs font-medium uppercase tracking-wider text-right">H. Extras (+)</th>
									<th class="py-3 px-4 text-xs font-medium uppercase tracking-wider text-right">Bonos (+)</th>
									<th class="py-3 px-4 text-xs font-medium uppercase tracking-wider text-right">Adelantos (-)</th>
									<th class="py-3 px-4 text-xs font-medium uppercase tracking-wider text-right">Descuentos (-)</th>
									<th class="py-3 px-4 text-xs font-medium uppercase tracking-wider text-right">PAGO NETO</th>
								</tr>
							</thead>
							<tbody class="divide-y divide-gray-700">
								<?php foreach($pagos as $p): ?>
								<tr class="hover:bg-gray-700 transition duration-150">
									<!-- Eliminamos data-label y display:block en móviles para mantener la vista de tabla scrollable -->
									<td class="py-3 px-4 text-sm whitespace-nowrap text-gray-400"><?php echo date("d/m/Y H:i", strtotime($p['fecha_registro'])); ?></td>
									<td class="py-3 px-4 text-sm whitespace-nowrap text-indigo-300 font-semibold"><?php echo htmlspecialchars($p['nombre']); ?></td>
									<td class="py-3 px-4 text-sm whitespace-nowrap"><?php echo date("d/m/Y", strtotime($p['desde'])); ?></td>
									<td class="py-3 px-4 text-sm whitespace-nowrap"><?php echo date("d/m/Y", strtotime($p['hasta'])); ?></td>
									<td class="py-3 px-4 text-sm text-right text-green-400 whitespace-nowrap font-mono"> <?php echo number_format($p['horas_extras'],2,',','.'); ?></td>
									<td class="py-3 px-4 text-sm text-right text-green-400 whitespace-nowrap font-mono"> <?php echo number_format($p['bonos'],2,',','.'); ?></td>
									<td class="py-3 px-4 text-sm text-right text-red-400 whitespace-nowrap font-mono"> <?php echo number_format($p['adelantos'],2,',','.'); ?></td>
									<td class="py-3 px-4 text-sm text-right text-red-400 whitespace-nowrap font-mono"> <?php echo number_format($p['descuentos'],2,',','.'); ?></td>
									<td class="py-3 px-4 text-sm text-right text-yellow-300 whitespace-nowrap font-extrabold"> <?php echo number_format($p['pago_neto'],2,',','.'); ?></td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>

					<!-- Total General -->
					<div class="total bg-gray-800 mt-4 rounded-lg p-4 shadow-xl">
						Total General Pagado en el Filtro: <span class="text-3xl font-extrabold text-teal-400 block sm:inline-block mt-2 sm:mt-0">ARS <?php echo number_format($totalGeneral,2,',','.'); ?></span>
					</div>

				<?php else: ?>
				<div class="bg-gray-800 p-8 rounded-xl border border-gray-700 text-center">
					<p class="text-xl text-gray-400">No se encontraron pagos registrados con los filtros seleccionados.</p>
					<p class="text-sm text-gray-500 mt-2">Intenta ajustar el trabajador o el rango de fechas.</p>
				</div>
				<?php endif; ?>



			</main>
			
			<!-- Pie de Página de la Aplicación -->
			<footer class="app-footer p-4 text-center border-t border-gray-700 mt-8 bg-gray-800">
				&copy; <span id="year-footer"></span> Sistema de Pagos.
			</footer>
		</div>
	</div>
	
	<!-- Overlay oscuro para dispositivos móviles -->
	<div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden transition-opacity duration-300 opacity-0" aria-hidden="true"></div>

	<!-- Script para la lógica del menú responsivo -->
	<script>
		document.addEventListener('DOMContentLoaded', () => {
			
			// 1. Elementos DOM y Pie de página
			const menuToggle = document.getElementById('menu-toggle');
			const sidebar = document.getElementById('sidebar');
			const overlay = document.getElementById('overlay');
			const menuIcon = document.getElementById('menu-icon');
			
			document.getElementById('year-footer').textContent = new Date().getFullYear();

			// 2. Lógica del Menú Responsivo
			const isMobile = () => window.innerWidth < 1024; // Tailwind's 'lg' breakpoint

			const toggleMenu = () => {
				const isCurrentlyHidden = sidebar.classList.contains('-translate-x-full');
				
				if (isCurrentlyHidden) {
					// Abrir Menú
					sidebar.classList.remove('-translate-x-full');
					overlay.classList.remove('hidden', 'opacity-0');
					overlay.classList.add('opacity-100');
					menuIcon.classList.add('menu-open');
				} else {
					// Cerrar Menú
					sidebar.classList.add('-translate-x-full');
					overlay.classList.remove('opacity-100');
					overlay.classList.add('opacity-0');
					setTimeout(() => {
						overlay.classList.add('hidden');
					}, 300);
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