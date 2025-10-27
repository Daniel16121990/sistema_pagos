<?php
require_once __DIR__.'/conexion.php';

if($_SERVER['REQUEST_METHOD']==='POST'){
    // Recolección de datos del formulario POST
    $trabajador = $_POST['trabajador'] ?? '';
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $horas = $_POST['horas'] ?? 0;
    $valor = $_POST['valor'] ?? 0; // Valor de la hora traído del campo oculto
    $obs = $_POST['observacion'] ?? '';
    
    // Cálculo del total
    $total = floatval($horas) * floatval($valor);

    // Inserción en la base de datos
    $stmt = $pdo->prepare('INSERT INTO horas_extras (trabajador_id,fecha,horas,valor_hora,total,observacion) VALUES (?,?,?,?,?,?)');
    $stmt->execute([$trabajador,$fecha,$horas,$valor,$total,$obs]);
    
    // Redirección
    header('Location: horas_extras.php');
    exit;
}

// Cargar la lista de trabajadores, incluyendo sueldo_hora para el cálculo automático
$trabajadores = $pdo->query('SELECT id, nombre, sueldo_hora FROM trabajadores ORDER BY nombre')->fetchAll();

// Cargar la lista de horas extras registradas, uniendo con el nombre del trabajador
$rows = $pdo->query('SELECT h.*, t.nombre FROM horas_extras h JOIN trabajadores t ON t.id = h.trabajador_id ORDER BY h.fecha DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Horas Extras - Sistema de Pagos</title>
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
    
    /* Estilo base para tablas responsivas (para desplazamiento horizontal) */
    .table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table-responsive {
        /* Asegura que la tabla tenga suficiente ancho para forzar el scroll en móviles */
        min-width: 700px; 
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
        <a class="block px-4 py-3 rounded-lg text-white font-semibold bg-indigo-700" href="horas_extras.php">
          ⏱️ Horas Extras (Actual)
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
        <h1 class="text-2xl font-extrabold text-gray-800">Registro de Horas Extras</h1>
      </header>

      <!-- Área de Contenido Principal -->
      <main class="container p-6 flex-1">
        
        <!-- Tarjeta del Formulario de Horas Extras -->
        <div class="w-full bg-white p-6 md:p-8 rounded-xl shadow-xl border border-gray-200 mb-8">
          <h2 class="text-xl font-bold mb-6 text-gray-700">Registrar Horas Extras Trabajadas</h2>

          <form method="post" action="horas_extras.php" class="space-y-4">
            
            <!-- Fila de Inputs (Trabajador, Fecha, Horas, Valor) -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 items-end">
                <!-- Select Trabajador -->
                <div>
                    <label for="trabajador" class="block text-sm font-medium text-gray-700 mb-1">Trabajador</label>
                    <select 
                        id="trabajador"
                        name="trabajador" 
                        required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                    >
                        <option value="" data-valor="">— Seleccioná trabajador —</option>
                        <?php foreach($trabajadores as $t): ?>
                          <option 
                            value="<?php echo $t['id']; ?>" 
                            data-valor="<?php echo htmlspecialchars($t['sueldo_hora']); ?>">
                            <?php echo htmlspecialchars($t['nombre']); ?>
                          </option>
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

                <!-- Input Horas -->
                <div>
                    <label for="horas" class="block text-sm font-medium text-gray-700 mb-1">Cant. Horas</label>
                    <input 
                        id="horas"
                        name="horas" 
                        type="number" 
                        step="0.25" 
                        placeholder="Ej. 4.5" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150" 
                    />
                </div>

                <!-- Valor por Hora (Muestra visual) -->
                <div class="flex flex-col">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor por Hora ($)</label>
                    <div id="valor_texto" class="px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg font-bold text-gray-800 text-center select-none">
                        —
                    </div>
                    <!-- Campo oculto para enviar el valor real a PHP -->
                    <input type="hidden" name="valor" id="valor_input" value="">
                </div>
            </div>
            
            <!-- Textarea Observación -->
            <div>
                <label for="observacion" class="block text-sm font-medium text-gray-700 mb-1">Observación (Opcional)</label>
                <textarea 
                    id="observacion"
                    name="observacion" 
                    placeholder="Motivo de las horas extras..." 
                    rows="2"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 resize-y"
                ></textarea>
            </div>
            
            <!-- Botón de Envío -->
            <div class="pt-2">
                <button class="px-6 py-3 bg-green-600 text-white font-bold rounded-lg shadow-md hover:bg-green-700 transition duration-300" type="submit">
                    ⏱️ Registrar Horas
                </button>
            </div>
            
          </form>
        </div>

        <!-- Sección de Listado de Horas Extras -->
        <h2 class="text-2xl font-extrabold text-gray-800 mb-4">Historial de Horas Extras</h2>
        
        <?php if(count($rows)==0): ?>
          <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded-lg" role="alert">
            <p class="font-bold">Información:</p>
            <p>No hay horas extras registradas hasta el momento.</p>
          </div>
        <?php else: ?>
          <!-- INICIO: Contenedor responsivo con scroll horizontal -->
          <div class="overflow-x-auto bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
            <!-- NOTA: Agregué min-w-[700px] para forzar el scroll en pantallas pequeñas. -->
            <table class="w-full text-left min-w-[700px]">
              <thead>
                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                  <th class="py-3 px-4 text-left">Fecha</th>
                  <th class="py-3 px-4 text-left">Trabajador</th>
                  <th class="py-3 px-4 text-right">Horas</th>
                  <th class="py-3 px-4 text-right">Valor Hora</th>
                  <th class="py-3 px-4 text-right">Total</th>
                  <th class="py-3 px-4 text-left">Obs</th>
                </tr>
              </thead>
              <tbody class="text-gray-600 text-sm font-light">
                <?php foreach($rows as $r): ?>
                  <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="py-3 px-4 text-left whitespace-nowrap">
                      <?php echo date('d-m-Y', strtotime($r['fecha'])); ?>
                    </td>
                    <td class="py-3 px-4 text-left whitespace-nowrap font-medium text-gray-800">
                      <?php echo htmlspecialchars($r['nombre']); ?>
                    </td>
                    <td class="py-3 px-4 text-right font-mono whitespace-nowrap">
                      <?php echo number_format($r['horas'], 2, ',', '.'); ?>
                    </td>
                    <td class="py-3 px-4 text-right font-mono whitespace-nowrap">
                      $<?php echo number_format($r['valor_hora'], 2, ',', '.'); ?>
                    </td>
                    <td class="py-3 px-4 text-right font-bold text-green-700 whitespace-nowrap">
                      $<?php echo number_format($r['total'], 2, ',', '.'); ?>
                    </td>
                    <td class="py-3 px-4 text-left">
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

  <!-- Script que actualiza el valor por hora automáticamente -->
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
      const trabajadorSelect = document.getElementById('trabajador');
      const valorInput = document.getElementById('valor_input');
      const valorTexto = document.getElementById('valor_texto');

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

      // 4. Lógica para actualizar el Valor por Hora
      trabajadorSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const valorHora = selectedOption.getAttribute('data-valor');

        // Actualizar el campo oculto que se envía a PHP
        valorInput.value = valorHora || '';

        // Actualizar el texto visible para el usuario
        if (valorHora) {
          // Formatear el número (aunque sea solo para mostrar)
          const formattedValor = parseFloat(valorHora).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
          valorTexto.textContent = `$${formattedValor}`;
          valorTexto.classList.remove('bg-gray-50', 'text-gray-800');
          valorTexto.classList.add('bg-green-100', 'text-green-700');
        } else {
          valorTexto.textContent = '—';
          valorTexto.classList.remove('bg-green-100', 'text-green-700');
          valorTexto.classList.add('bg-gray-50', 'text-gray-800');
        }
      });
      
      // Llamar al evento change si ya hay un valor seleccionado al cargar la página
      trabajadorSelect.dispatchEvent(new Event('change'));
    });
  </script>
</body>
</html>