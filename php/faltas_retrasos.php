<?php
require_once __DIR__.'/conexion.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // Recolección de datos del formulario POST
    $trabajador_id = $_POST['trabajador_id'];
    $fecha = $_POST['fecha'];
    $tipo = $_POST['tipo'];
    $motivo = $_POST['motivo'] ?? null;
    $descuento = $_POST['descuento'] ?? 0;
    $observacion = $_POST['observacion'] ?? null;

    // Inserción en la base de datos
    $stmt = $pdo->prepare('INSERT INTO faltas_retrasos (trabajador_id, fecha, tipo, motivo, descuento, observacion) VALUES (?,?,?,?,?,?)');
    $stmt->execute([$trabajador_id,$fecha,$tipo,$motivo,$descuento,$observacion]);
    
    // Redirección
    header('Location: faltas_retrasos.php');
    exit;
}

// Cargar la lista de trabajadores, incluyendo sueldo_hora para el cálculo automático
$trabajadores = $pdo->query('SELECT id, nombre, sueldo_hora FROM trabajadores ORDER BY nombre')->fetchAll();

// Cargar la lista de registros, uniendo con el nombre del trabajador
$registros = $pdo->query('SELECT f.*, t.nombre FROM faltas_retrasos f JOIN trabajadores t ON t.id = f.trabajador_id ORDER BY f.fecha DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Faltas y Retrasos - Sistema de Pagos</title>
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
    
    /* ❌ ELIMINAMOS las clases .table-container y .table-responsive 
       para evitar conflictos con Tailwind. La funcionalidad la daremos 
       directamente con las clases de utilidad de Tailwind en el HTML. */

  </style>
</head>

<body class="min-h-screen bg-gray-100 text-gray-900 transition-colors duration-300">

  <button id="menu-toggle" class="fixed top-4 left-4 z-50 p-3 rounded-full bg-indigo-600 text-white shadow-xl lg:hidden focus:outline-none focus:ring-4 focus:ring-indigo-500/50 transition duration-150 ease-in-out" aria-label="Abrir Menú">
    <div id="menu-icon" class="menu-icon">
      <span></span>
      <span></span>
      <span></span>
    </div>
  </button>

  <div class="lg:grid lg:grid-cols-[280px_1fr] min-h-screen">

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
        <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition duration-200" href="listar.php">
          👷‍♂️ Trabajadores
        </a>
        <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition duration-200" href="adelantos.php">
          💰 Adelantos
        </a>
        <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition duration-200" href="horas_extras.php">
          ⏱️ Horas Extras
        </a>
        <a class="block px-4 py-3 rounded-lg text-white font-semibold bg-indigo-700" href="faltas_retrasos.php">
          📅 Faltas / Retrasos (Actual)
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
		<!-- Enlace para volver al login -->
			<div class="p-4 border-t border-gray-700">
    			<a href="../logout.php" class="block text-sm text-red-400 hover:text-red-300 transition duration-200 font-medium">
        		🚪 Cerrar Sesión
    			</a>
			</div>
    </nav>

    <div class="flex flex-col flex-1 lg:pl-0 pt-16 lg:pt-0">
      
      <header class="app-header bg-white shadow-md p-4 lg:p-6 sticky top-0 z-30">
        <h1 class="text-2xl font-extrabold text-gray-800">Registro de Faltas, Retrasos y Permisos</h1>
      </header>

      <main class="container p-6 flex-1">
        
        <div class="w-full bg-white p-6 md:p-8 rounded-xl shadow-xl border border-gray-200 mb-8">
          <h2 class="text-xl font-bold mb-6 text-gray-700">Aplicar Descuento o Registrar Ausencia</h2>

          <form method="post" action="faltas_retrasos.php" class="space-y-4">
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label for="trabajador" class="block text-sm font-medium text-gray-700 mb-1">Trabajador</label>
                    <select 
                        id="trabajador"
                        name="trabajador_id" 
                        required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                    >
                        <option value="" data-sueldo="">— Seleccioná trabajador —</option>
                        <?php foreach($trabajadores as $t): ?>
                          <option 
                            value="<?php echo $t['id']; ?>" 
                            data-sueldo="<?php echo htmlspecialchars($t['sueldo_hora']); ?>">
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
                        value="<?php echo date('Y-m-d'); ?>"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150" 
                    />
                </div>

                <div>
                    <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Ausencia</label>
                    <select 
                        id="tipo"
                        name="tipo" 
                        required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                    >
                        <option value="falta">Falta</option>
                        <option value="retraso">Retraso</option>
                        <option value="permiso">Permiso</option>
                    </select>
                </div>

                <div>
                    <label for="descuento" class="block text-sm font-medium text-gray-700 mb-1">Descuento ($)</label>
                    <input 
                        id="descuento"
                        name="descuento" 
                        type="number" 
                        step="0.01" 
                        placeholder="Monto a descontar" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 transition duration-150" 
                    />
                    <p id="descuento_hint" class="text-xs mt-1 text-red-600 font-medium hidden">
                        *Descuento sugerido: $0.00 (8h)
                    </p>
                </div>
            </div>
            
            <div>
                <label for="motivo" class="block text-sm font-medium text-gray-700 mb-1">Motivo / Razón</label>
                <input 
                    id="motivo"
                    name="motivo" 
                    placeholder="Ej. enfermedad, trámite personal, irresponsabilidad" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                />
            </div>
            
            <div>
                <label for="observacion" class="block text-sm font-medium text-gray-700 mb-1">Observación Adicional (Opcional)</label>
                <textarea 
                    id="observacion"
                    name="observacion" 
                    placeholder="Detalles adicionales sobre la situación..." 
                    rows="2"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 resize-y"
                ></textarea>
            </div>
            
            <div class="pt-2">
                <button class="px-6 py-3 bg-red-600 text-white font-bold rounded-lg shadow-md hover:bg-red-700 transition duration-300" type="submit">
                    📅 Registrar Ausencia / Descuento
                </button>
            </div>
            
          </form>
        </div>

        <h2 class="text-2xl font-extrabold text-gray-800 mb-4">Historial de Ausencias y Descuentos</h2>
        
        <?php if(count($registros)==0): ?>
          <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded-lg" role="alert">
            <p class="font-bold">Información:</p>
            <p>No hay registros de faltas, retrasos o permisos hasta el momento.</p>
          </div>
        <?php else: ?>
          <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-x-auto">
            <table class="min-w-[800px] w-full text-left"> 
              <thead>
                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                  <th class="py-3 px-4 text-left">Fecha</th>
                  <th class="py-3 px-4 text-left">Trabajador</th>
                  <th class="py-3 px-4 text-center">Tipo</th>
                  <th class="py-3 px-4 text-left">Motivo</th>
                  <th class="py-3 px-4 text-right">Descuento</th>
                  <th class="py-3 px-4 text-left">Obs</th>
                </tr>
              </thead>
              <tbody class="text-gray-600 text-sm font-light">
                <?php foreach($registros as $r): ?>
                  <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="py-3 px-4 text-left whitespace-nowrap">
                      <?php echo date('d-m-Y', strtotime($r['fecha'])); ?>
                    </td>
                    <td class="py-3 px-4 text-left whitespace-nowrap font-medium text-gray-800">
                      <?php echo htmlspecialchars($r['nombre']); ?>
                    </td>
                    <td class="py-3 px-4 text-center whitespace-nowrap">
                      <?php 
                        $tipo_clase = match($r['tipo']) {
                            'falta' => 'bg-red-200 text-red-800',
                            'retraso' => 'bg-yellow-200 text-yellow-800',
                            'permiso' => 'bg-blue-200 text-blue-800',
                            default => 'bg-gray-200 text-gray-800'
                        };
                      ?>
                      <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $tipo_clase; ?>">
                        <?php echo ucfirst($r['tipo']); ?>
                      </span>
                    </td>
                    <td class="py-3 px-4 text-left">
                      <?php echo htmlspecialchars($r['motivo'] ?? '—'); ?>
                    </td>
                    <td class="py-3 px-4 text-right font-bold text-red-700 font-mono whitespace-nowrap">
                      $<?php echo number_format($r['descuento'], 2, ',', '.'); ?>
                    </td>
                    <td class="py-3 px-4 text-left">
                      <?php echo htmlspecialchars($r['observacion'] ?? '—'); ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
        
      </main>

      <footer class="app-footer p-4 text-center border-t border-gray-200 mt-8 bg-white">
        &copy; <span id="year-footer"></span> Sistema de Pagos. 😎
      </footer>
    </div>
  </div>
  
  <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden transition-opacity duration-300 opacity-0" aria-hidden="true"></div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // 1. Elementos DOM
      const selectTrabajador = document.getElementById('trabajador');
      const selectTipo = document.getElementById('tipo');
      const inputDescuento = document.getElementById('descuento');
      const descuentoHint = document.getElementById('descuento_hint');
      
      const menuToggle = document.getElementById('menu-toggle');
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('overlay');
      const menuIcon = document.getElementById('menu-icon');
      
      document.getElementById('year-footer').textContent = new Date().getFullYear();


      // 2. Lógica de Descuento
      function formatCurrency(amount) {
          if (typeof amount !== 'number') return '0.00';
          // Formato: 1.000,00
          return amount.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
      }

      function actualizarDescuento() {
        const tipo = selectTipo.value;
        const selected = selectTrabajador.options[selectTrabajador.selectedIndex];
        const sueldoHora = selected.getAttribute('data-sueldo');
        let sueldoPorDia = 0;
        
        if (sueldoHora) {
            sueldoPorDia = parseFloat(sueldoHora) * 8; // Cálculo base de 8 horas de trabajo
        }

        // Mostrar u ocultar la sugerencia
        descuentoHint.classList.add('hidden');
        inputDescuento.classList.remove('border-green-500');

        if (tipo === 'falta' && sueldoHora) {
          // Descuento sugerido de 8 horas
          inputDescuento.value = sueldoPorDia.toFixed(2);
          
          descuentoHint.textContent = `*Descuento sugerido para Falta (8h): $${formatCurrency(sueldoPorDia)}`;
          descuentoHint.classList.remove('hidden');
          inputDescuento.classList.add('border-green-500');

        } else if (tipo === 'retraso' || tipo === 'permiso') {
          // Si es retraso o permiso, deja el campo vacío para entrada manual
          // o mantén el valor actual si ya fue introducido
          if (tipo === 'retraso') {
            descuentoHint.textContent = `*Sugerencia: Calcula horas de retraso por Sueldo/Hora.`;
            descuentoHint.classList.remove('hidden');
          } else {
            descuentoHint.classList.add('hidden');
          }
          if (inputDescuento.value === sueldoPorDia.toFixed(2)) { // Limpia si tenía el valor de Falta
             inputDescuento.value = '';
          }
          inputDescuento.placeholder = 'Monto a descontar (ej. 50.00)';
        }
      }

      // Ejecuta al cambiar tipo o trabajador
      selectTrabajador.addEventListener('change', actualizarDescuento);
      selectTipo.addEventListener('change', actualizarDescuento);
      
      // Inicializar el estado al cargar la página
      actualizarDescuento();


      // 3. Lógica del Menú Responsivo
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
        } else {
           // Asegurar que si vuelve a móvil, el menú esté cerrado inicialmente
           if (!sidebar.classList.contains('-translate-x-full')) {
              sidebar.classList.add('-translate-x-full');
              overlay.classList.add('hidden', 'opacity-0');
              menuIcon.classList.remove('menu-open');
           }
        }
      });
      
      // Inicializar estado del sidebar en móvil si se carga por primera vez
      if (isMobile()) {
         sidebar.classList.add('-translate-x-full');
      }
    });
  </script>
</body>
</html>