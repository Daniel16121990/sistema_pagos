<?php
require_once __DIR__.'/conexion.php';
$stmt = $pdo->query('SELECT * FROM trabajadores ORDER BY id DESC');
$rows = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Trabajadores - Sistema de Pagos</title>
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
				<!-- Ítems del menú (Adaptados de index.html) -->
				<a class="block px-4 py-3 rounded-lg text-white font-semibold bg-indigo-700" href="listar.php">
					👷‍♂️ Trabajadores (Actual)
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
				<h1 class="text-2xl font-extrabold text-gray-800">Trabajadores</h1>
			</header>

			<!-- Área de Contenido Principal (con la tabla PHP) -->
			<main class="container p-6 flex-1">
				
				<!-- Botón de Acción -->
				<a class="inline-block px-6 py-3 mb-8 bg-indigo-600 text-white font-bold rounded-lg shadow-md hover:bg-indigo-700 transition duration-300" href="agregar.php">
					➕ Agregar Trabajador
				</a>
				
				<?php if(count($rows)==0): ?>
					<div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-lg" role="alert">
						<p class="font-bold">Aviso:</p>
						<p>No hay trabajadores. Agregá uno para comenzar.</p>
					</div>
				<?php else: ?>
					<!-- Contenedor responsivo para la tabla con scroll horizontal -->
					<!-- Usamos overflow-x-auto para que el scroll aparezca si el contenido lo requiere -->
					<div class="overflow-x-auto bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
						<!-- min-w-[768px] asegura que la tabla siempre tenga un ancho mínimo para forzar el scroll en móviles -->
						<table class="w-full text-left min-w-[768px]">
							<thead>
								<tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
									<th class="py-3 px-6 text-left">Nombre</th>
									<th class="py-3 px-6 text-left">Cargo</th>
									<th class="py-3 px-6 text-right">Sueldo Base (por hora)</th>
									<th class="py-3 px-6 text-left">Ingreso</th>
								</tr>
							</thead>
							<tbody class="text-gray-600 text-sm font-light">
								<?php foreach($rows as $r): ?>
									<tr class="border-b border-gray-200 hover:bg-gray-50">
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
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
				
				<!-- El enlace "Volver" se mueve al final del menú o se puede mantener aquí como un botón de acción secundaria -->
				<!-- <p style="margin-top:18px;"><a href="../index.html" class="small-muted">← Volver</a></p> -->
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
				const isOpen = sidebar.classList.toggle('-translate-x-full');
				
				if (!isOpen) {
					// Menú abierto
					overlay.classList.remove('hidden', 'opacity-0');
					overlay.classList.add('opacity-100');
					menuIcon.classList.add('menu-open');
				} else {
					// Menú cerrado
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