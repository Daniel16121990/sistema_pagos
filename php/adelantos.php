<?php
require_once __DIR__.'/conexion.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
	// Recolección de datos del formulario POST
	$trabajador = $_POST['trabajador'] ?? '';
	$fecha = $_POST['fecha'] ?? date('Y-m-d');
	$monto = $_POST['monto'] ?? 0;
	$obs = $_POST['observacion'] ?? '';
	
	// Inserción en la base de datos
	$stmt = $pdo->prepare('INSERT INTO adelantos (trabajador_id,fecha,monto,observacion) VALUES (?,?,?,?)');
	$stmt->execute([$trabajador,$fecha,$monto,$obs]);
	
	// Redirección
	header('Location: adelantos.php');
	exit;
}

// Cargar la lista de trabajadores para el SELECT del formulario
$trabajadores = $pdo->query('SELECT id,nombre FROM trabajadores ORDER BY nombre')->fetchAll();

// Cargar la lista de adelantos registrados
$rows = $pdo->query('SELECT a.*, t.nombre FROM adelantos a JOIN trabajadores t ON t.id = a.trabajador_id ORDER BY a.fecha DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Adelantos - Sistema de Pagos</title>
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

		/* Clase para cuando el menú está abierto (opcional, para animar el icono) */
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

<body class="min-h-screen bg-gray-100 text-gray-900 transition-colors duration-300">

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
				<a class="block px-4 py-3 rounded-lg text-white font-semibold bg-indigo-700" href="adelantos.php">
					💰 Adelantos (Actual)
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
				<a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition duration-200" href="historial_pagos.php">
					📋 Historial de Pagos
				</a>
			</div>
			<!-- Enlace para volver al Dashboard -->
			<div class="p-4 border-t border-gray-700">
				<a href="../index.html" class="block text-sm text-indigo-400 hover:text-indigo-300 transition duration-200">
					← Volver al Dashboard
				</a>
			</div>
		</nav>

		<!-- Contenido Principal (Scrollable) -->
		<div class="flex flex-col flex-1 lg:pl-0 pt-16 lg:pt-0">
			
			<!-- Encabezado de la Aplicación (Contenido) -->
			<header class="app-header bg-white shadow-md p-4 lg:p-6 sticky top-0 z-30">
				<h1 class="text-2xl font-extrabold text-gray-800">Registro de Adelantos</h1>
			</header>

			<!-- Área de Contenido Principal -->
			<main class="container p-6 flex-1">
				
				<!-- Tarjeta del Formulario de Adelantos -->
				<div class="w-full bg-white p-6 md:p-8 rounded-xl shadow-xl border border-gray-200 mb-8">
					<h2 class="text-xl font-bold mb-6 text-gray-700">Registrar Nuevo Adelanto</h2>

					<form method="post" action="adelantos.php" class="space-y-4">
						
						<!-- Fila de Inputs (Trabajador, Fecha, Monto) -->
						<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
								<!-- Select Trabajador -->
								<div>
									<label for="trabajador" class="block text-sm font-medium text-gray-700 mb-1">Trabajador</label>
									<select 
										id="trabajador"
										name="trabajador" 
										required 
										class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
									>
										<option value="">— Seleccioná trabajador —</option>
										<?php foreach($trabajadores as $t): ?>
											<option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['nombre']); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								
								<!-- Input Fecha -->
								<div>
									<label for="fecha" class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
									<input 
										id="fecha"
										name="fecha" 
										type="date" 
										value="<?php echo date('Y-m-d'); ?>"
										required
										class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150" 
									/>
								</div>

								<!-- Input Monto -->
								<div>
									<label for="monto" class="block text-sm font-medium text-gray-700 mb-1">Monto ($)</label>
									<input 
										id="monto"
										name="monto" 
										type="number" 
										step="0.01" 
										placeholder="Ej. 15000" 
										required
										class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150" 
									/>
								</div>
						</div>
						
						<!-- Textarea Observación -->
						<div>
							<label for="observacion" class="block text-sm font-medium text-gray-700 mb-1">Observación (Opcional)</label>
							<textarea 
								id="observacion"
								name="observacion" 
								placeholder="Detalles sobre el adelanto..." 
								rows="2"
								class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 resize-y"
							></textarea>
						</div>
						
						<!-- Botón de Envío -->
						<div class="pt-2">
							<button class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-lg shadow-md hover:bg-indigo-700 transition duration-300" type="submit">
								💰 Registrar Adelanto
							</button>
						</div>
						
					</form>
				</div>

				<!-- Sección de Listado de Adelantos -->
				<h2 class="text-2xl font-extrabold text-gray-800 mb-4">Historial de Adelantos</h2>
				
				<?php if(count($rows)==0): ?>
					<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded-lg" role="alert">
						<p class="font-bold">Información:</p>
						<p>No hay adelantos registrados hasta el momento.</p>
					</div>
				<?php else: ?>
					<!-- Contenedor responsivo para la tabla con scroll horizontal -->
					<!-- Clase Tailwind: overflow-x-auto para el scroll -->
					<div class="overflow-x-auto bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
						<!-- min-w-[640px] para asegurar el ancho mínimo y forzar el scroll en móviles -->
						<table class="w-full text-left min-w-[640px]">
							<thead>
								<tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
									<th class="py-3 px-6 text-left">Fecha</th>
									<th class="py-3 px-6 text-left">Trabajador</th>
									<th class="py-3 px-6 text-right">Monto</th>
									<th class="py-3 px-6 text-left">Obs</th>
								</tr>
							</thead>
							<tbody class="text-gray-600 text-sm font-light">
								<?php foreach($rows as $r): ?>
									<tr class="border-b border-gray-200 hover:bg-gray-50">
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
											<?php 
												$obs_text = htmlspecialchars($r['observacion']);
												echo $obs_text ? $obs_text : '—'; // Muestra guión si no hay obs.
											?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
				
			</main>

			<!-- Pie de Página de la Aplicación -->
			<footer class="app-footer p-4 text-center border-t border-gray-200 mt-8 bg-white">
				&copy; <span id="year-footer"></span> Sistema de Pagos. 😎
			</footer>
		</div>
	</div>
	
	<!-- Overlay oscuro para dispositivos móviles -->
	<div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden transition-opacity duration-300 opacity-0" aria-hidden="true"></div>

	<script>
		// Lógica de JavaScript para el menú y el año
		document.addEventListener('DOMContentLoaded', () => {
			// 1. Establecer el año actual
			document.getElementById('year-footer').textContent = new Date().getFullYear();

			// 2. Lógica del menú responsivo
			const menuToggle = document.getElementById('menu-toggle');
			const sidebar = document.getElementById('sidebar');
			const overlay = document.getElementById('overlay');
			const menuIcon = document.getElementById('menu-icon');

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

			// Abre/Cierra el menú al hacer clic en el botón
			menuToggle.addEventListener('click', toggleMenu);

			// Cierra el menú al hacer clic en el overlay
			overlay.addEventListener('click', toggleMenu);

			// 3. Manejar el redimensionamiento de la ventana
			window.addEventListener('resize', () => {
				if (!isMobile()) {
					// Asegurar que el menú esté visible en desktop
					sidebar.classList.remove('-translate-x-full');
					overlay.classList.add('hidden', 'opacity-0');
					menuIcon.classList.remove('menu-open');
				}
			});
			
			// Inicializar el estado si es mobile (oculto por defecto)
			if (isMobile()) {
				sidebar.classList.add('-translate-x-full');
			}
		});
	</script>
</body>
</html>