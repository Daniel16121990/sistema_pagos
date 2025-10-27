<?php
// Iniciar sesión
session_start();

// Verificar si el usuario NO ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Si no está logueado, redirigir al login
    header('Location: index.php');
    exit;
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sistema de Pagos — Login</title>
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

    /* Personalización de colores de Tailwind para un tema oscuro */
    :root {
      --color-primary: #4f46e5; /* Indigo-600 */
      --color-secondary: #1f2937; /* Gray-800 */
      --color-background: #f3f4f6; /* Gray-100 */
      --color-text: #111827; /* Gray-900 */
    }

    @media (prefers-color-scheme: dark) {
      :root {
        --color-secondary: #111827;
        --color-background: #1f2937;
        --color-text: #f9fafb;
      }
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    /* Estilo inicial para ocultar el dashboard */
    .dashboard-hidden {
        display: none;
    }
  </style>
</head>

<body class="min-h-screen bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-300">

  <button id="menu-toggle" class="fixed top-4 left-4 z-50 p-3 rounded-full bg-indigo-600 text-white shadow-xl lg:hidden focus:outline-none focus:ring-4 focus:ring-indigo-500/50 transition duration-150 ease-in-out dashboard-hidden" aria-label="Abrir Menú">
    <div id="menu-icon" class="menu-icon">
      <span></span>
      <span></span>
      <span></span>
    </div>
  </button>

  <div class="lg:grid lg:grid-cols-[280px_1fr] min-h-screen">

    <nav id="sidebar" class="
      /* Mobile: Fijo, oculto, deslizable */
      fixed inset-y-0 left-0 w-64 bg-gray-800 dark:bg-gray-900 text-white z-40 
      transition-transform duration-300 transform -translate-x-full shadow-2xl
      lg:shadow-none

      /* Desktop: Relativo, siempre visible */
      lg:relative lg:translate-x-0 lg:flex lg:flex-col lg:h-auto
      dashboard-hidden
      "
      onclick="document.getElementById('menu-toggle').click()"
    >
      <div class="p-6 border-b border-gray-700">
        <h2 class="text-2xl font-bold text-indigo-400">Menú Principal</h2>
      </div>
      <div class="flex-1 p-4 space-y-2 overflow-y-auto">
        <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition duration-200" href="php/listar.php">
          👷‍♂️ Trabajadores
        </a>
        <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition duration-200" href="php/adelantos.php">
          💰 Adelantos
        </a>
        <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition duration-200" href="php/horas_extras.php">
          ⏱️ Horas Extras
        </a>
        <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition duration-200" href="php/faltas_retrasos.php">
          📅 Faltas / Retrasos
        </a>
        <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition duration-200" href="php/bonos.php">
          🎁 Bonos
        </a>
        <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition duration-200" href="php/reporte.php">
          📊 Reporte Quincenal
        </a>
        <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition duration-200" href="php/historial_pagos.php">
          📋 Historial de Pagos
        </a>
      </div>
    </nav>

    <div id="main-content-wrapper" class="flex flex-col flex-1 lg:pl-0 pt-0 lg:pt-0">
      
      <header id="app-header" class="app-header bg-white dark:bg-gray-700 shadow-md p-4 lg:p-6 sticky top-0 z-30">
        <h1 class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">Sistema de Pagos</h1>
      </header>

      <div id="login-screen" class="flex flex-1 items-center justify-center p-4">
        <div class="w-full max-w-md bg-white dark:bg-gray-700 rounded-xl shadow-2xl p-8 space-y-6">
          <h2 class="text-3xl font-bold text-center text-gray-900 dark:text-gray-100">Iniciar Sesión</h2>
          <p class="text-center text-gray-500 dark:text-gray-300">Ingresa tus credenciales para acceder al sistema.</p>
          
          <form id="login-form" class="space-y-4">
            <div>
              <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Usuario</label>
              <input type="text" id="username" name="username" required placeholder="admin" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
            </div>
            <div>
              <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Contraseña</label>
              <input type="password" id="password" name="password" required placeholder="12345" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
            </div>
            
            <p id="error-message" class="text-red-500 text-sm text-center hidden">Usuario o contraseña incorrectos.</p>
            
            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
              Acceder
            </button>
          </form>
        </div>
      </div>
      <main id="dashboard-content" class="container p-6 flex-1 dashboard-hidden">
        <p class="lead text-xl font-bold mb-8 text-indigo-600 dark:text-indigo-400">👋 ¡Bienvenido al Dashboard!</p>
        <p class="text-lg mb-8 text-gray-600 dark:text-gray-300">Seleccioná una opción del menú lateral para comenzar a gestionar el sistema de pagos.</p>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="p-6 bg-white dark:bg-gray-700 rounded-xl shadow-lg border border-gray-200 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-1">Total Trabajadores</h3>
                <p class="text-3xl font-extrabold text-indigo-500">0</p>
            </div>
             <div class="p-6 bg-white dark:bg-gray-700 rounded-xl shadow-lg border border-gray-200 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-1">Adelantos Pendientes</h3>
                <p class="text-3xl font-extrabold text-red-500">0.00 $</p>
            </div>
             <div class="p-6 bg-white dark:bg-gray-700 rounded-xl shadow-lg border border-gray-200 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-1">Próximo Pago</h3>
                <p class="text-lg font-bold text-green-500">Pendiente</p>
            </div>
        </div>
      </main>
      <footer id="app-footer" class="app-footer p-4 text-center border-t border-gray-200 dark:border-gray-700 mt-8 bg-white dark:bg-gray-700">
        &copy; <span id="year"></span> Sistema de Pagos. 😎
      </footer>
    </div>
  </div>
  
  <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden transition-opacity duration-300 opacity-0" aria-hidden="true"></div>

  <script>
    // Lógica de JavaScript
    document.addEventListener('DOMContentLoaded', () => {
      // 1. Elementos clave
      const loginForm = document.getElementById('login-form');
      const loginScreen = document.getElementById('login-screen');
      const errorMessage = document.getElementById('error-message');
      const sidebar = document.getElementById('sidebar');
      const menuToggle = document.getElementById('menu-toggle');
      const dashboardContent = document.getElementById('dashboard-content');
      const overlay = document.getElementById('overlay');
      const menuIcon = document.getElementById('menu-icon');
      
      // 2. Establecer el año actual
      document.getElementById('year').textContent = new Date().getFullYear();

      // 3. Lógica de Login (Simulación)
      loginForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const username = e.target.username.value;
        const password = e.target.password.value;
        
        // **Credenciales de prueba**
        const VALID_USER = "admin";
        const VALID_PASS = "12345";

        if (username === VALID_USER && password === VALID_PASS) {
          // **Acceso Exitoso**
          errorMessage.classList.add('hidden');
          
          // Ocultar pantalla de login
          loginScreen.classList.add('hidden');
          
          // Mostrar elementos del dashboard
          sidebar.classList.remove('dashboard-hidden');
          menuToggle.classList.remove('dashboard-hidden');
          dashboardContent.classList.remove('dashboard-hidden');

          // Limpiar formulario (opcional)
          loginForm.reset();

          // Ajustar la URL o la historia para reflejar el estado de sesión (opcional, avanzado)
          // history.pushState(null, '', 'dashboard');
          
          // Asegurar que el sidebar esté visible en desktop (si el inicio fue en desktop)
          if (!isMobile()) {
            sidebar.classList.remove('-translate-x-full');
          }
        } else {
          // **Acceso Fallido**
          errorMessage.classList.remove('hidden');
        }
      });

      // 4. Lógica del menú responsivo (sin cambios, pero ahora se aplica al hacer login)
      const isMobile = () => window.innerWidth < 1024; // Tailwind's 'lg' breakpoint

      const toggleMenu = () => {
        // Solo ejecuta si el menú no está oculto por 'dashboard-hidden'
        if(sidebar.classList.contains('dashboard-hidden')) return;

        const isOpen = sidebar.classList.toggle('-translate-x-full');
        
        if (!isOpen) {
          overlay.classList.remove('hidden', 'opacity-0');
          overlay.classList.add('opacity-100');
          menuIcon.classList.add('menu-open');
        } else {
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

      // 5. Manejar el redimensionamiento de la ventana para corregir el estado del menú en desktop
      window.addEventListener('resize', () => {
        if (!isMobile() && !sidebar.classList.contains('dashboard-hidden')) {
          sidebar.classList.remove('-translate-x-full');
          overlay.classList.add('hidden', 'opacity-0');
          menuIcon.classList.remove('menu-open');
        }
      });
      
      // Inicializar el estado si es mobile (oculto por defecto)
      if (isMobile()) {
          // Esto ya lo maneja la clase `-translate-x-full` en la definición inicial,
          // pero lo mantenemos por si acaso se carga en desktop y se reajusta.
          sidebar.classList.add('-translate-x-full');
      }
    });
  </script>
</body>
</html>