<?php
require_once __DIR__.'/conexion.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $nombre = $_POST['nombre'] ?? '';
    $cargo = $_POST['cargo'] ?? '';
    $sueldo = $_POST['sueldo'] ?? 0;
    $fecha = $_POST['fecha'] ?? null;
    $stmt = $pdo->prepare('INSERT INTO trabajadores (nombre,cargo,sueldo_hora,fecha_ingreso) VALUES (?,?,?,?)');
    $stmt->execute([$nombre,$cargo,$sueldo,$fecha]);
    header('Location: listar.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Agregar Trabajador - Sistema de Pagos</title>
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
        <h1 class="text-2xl font-extrabold text-gray-800">Agregar Trabajador</h1>
      </header>

      <!-- Área de Contenido Principal (con el formulario PHP) -->
      <main class="container p-6 flex-1 flex justify-center">
        
        <!-- Tarjeta del Formulario -->
        <div class="w-full max-w-lg bg-white p-8 rounded-xl shadow-2xl border border-gray-200">
          <h2 class="text-xl font-bold mb-6 text-gray-700">Completá los datos:</h2>

          <form method="post" action="agregar.php" class="space-y-6">
            
            <!-- Campo Nombre -->
            <div>
              <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre Completo</label>
              <input 
                id="nombre"
                name="nombre" 
                placeholder="Ej. Juan Pérez" 
                required 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150" 
              />
            </div>

            <!-- Campo Cargo -->
            <div>
              <label for="cargo" class="block text-sm font-medium text-gray-700 mb-1">Cargo</label>
              <input 
                id="cargo"
                name="cargo" 
                placeholder="Ej. Ayudante o Chofer" 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150" 
              />
            </div>

            <!-- Campo Sueldo por hora -->
            <div>
              <label for="sueldo" class="block text-sm font-medium text-gray-700 mb-1">Sueldo por hora (Monto)</label>
              <input 
                id="sueldo"
                name="sueldo" 
                placeholder="Ej. 3000" 
                required 
                type="number" 
                step="0.01" 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150" 
              />
            </div>

            <!-- Campo Fecha de Ingreso -->
            <div>
              <label for="fecha" class="block text-sm font-medium text-gray-700 mb-1">Fecha de Ingreso</label>
              <input 
                id="fecha"
                name="fecha" 
                type="date" 
                required 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150" 
              />
            </div>
            
            <div class="flex flex-col sm:flex-row sm:justify-between items-center pt-2 space-y-4 sm:space-y-0">
                <button class="w-full sm:w-auto px-6 py-3 bg-indigo-600 text-white font-bold rounded-lg shadow-md hover:bg-indigo-700 transition duration-300" type="submit">
                    Guardar Trabajador
                </button>
                
                <a href="listar.php" class="text-sm text-gray-500 hover:text-indigo-600 transition duration-150">
                    ← Volver a Trabajadores
                </a>
            </div>
            
          </form>
        </div>
        
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