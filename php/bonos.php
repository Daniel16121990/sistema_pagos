<?php
require_once __DIR__.'/conexion.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // Recolección de datos del formulario POST
    $trabajador_id = $_POST['trabajador_id'];
    $fecha = $_POST['fecha'];
    $tipo = $_POST['tipo'];
    $monto = $_POST['monto'];
    $observacion = $_POST['observacion'] ?? null;
    
    // Inserción en la base de datos
    $stmt = $pdo->prepare('INSERT INTO bonos (trabajador_id, fecha, tipo, monto, observacion) VALUES (?,?,?,?,?)');
    $stmt->execute([$trabajador_id,$fecha,$tipo,$monto,$observacion]);
    
    // Redirección
    header('Location: bonos.php');
    exit;
}

// Cargar la lista de trabajadores
$trabajadores = $pdo->query('SELECT id,nombre FROM trabajadores ORDER BY nombre')->fetchAll();

// Cargar la lista de registros de bonos
$registros = $pdo->query('SELECT b.*, t.nombre FROM bonos b JOIN trabajadores t ON t.id = b.trabajador_id ORDER BY b.fecha DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Bonos - Sistema de Pagos</title>
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
    
    /* ❌ ELIMINAMOS las clases .table-container y .table-responsive 
       ya que la funcionalidad será manejada por las clases de utilidad de Tailwind 
       directamente en el HTML (overflow-x-auto y min-w-[]). */
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
        <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition duration-200" href="faltas_retrasos.php">
          📅 Faltas / Retrasos
        </a>
        <a class="block px-4 py-3 rounded-lg text-white font-semibold bg-indigo-700" href="bonos.php">
          🎁 Bonos (Actual)
        </a>
        <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition duration-200" href="reporte.php">
          📊 Reporte Quincenal
        </a>
        <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition duration-200" href="historial_pagos.php">
          📋 Historial de Pagos
        </a>
      </div>
      <div class="p-4 border-t border-gray-700">
          <a href="../index.html" class="block text-sm text-indigo-400 hover:text-indigo-300 transition duration-200">
              ← Volver al Dashboard
          </a>
      </div>
    </nav>

    <div class="flex flex-col flex-1 lg:pl-0 pt-16 lg:pt-0">
      
      <header class="app-header bg-white shadow-md p-4 lg:p-6 sticky top-0 z-30">
        <h1 class="text-2xl font-extrabold text-gray-800">Registro de Bonos</h1>
      </header>

      <main class="container p-6 flex-1">
        
        <div class="w-full bg-white p-6 md:p-8 rounded-xl shadow-xl border border-gray-200 mb-8">
          <h2 class="text-xl font-bold mb-6 text-gray-700">Registrar un Nuevo Bono</h2>

          <form method="post" action="bonos.php" class="space-y-4">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="trabajador" class="block text-sm font-medium text-gray-700 mb-1">Trabajador</label>
                    <select 
                        id="trabajador"
                        name="trabajador_id" 
                        required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                    >
                        <option value="">— Seleccioná trabajador —</option>
                        <?php foreach($trabajadores as $t): ?>
                          <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="fecha" class="block text-sm font-medium text-gray-700 mb-1">Fecha de Aplicación</label>
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
                    <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Bono</label>
                    <select 
                        id="tipo"
                        name="tipo" 
                        required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                    >
                        <option value="feriado">Feriado</option>
                        <option value="festivo">Festivo</option>
                        <option value="incentivo">Incentivo por Desempeño</option>
                        <option value="recompensa">Recompensa Especial</option>
                    </select>
                </div>
            </div>
            
            <div>
                <label for="monto" class="block text-sm font-medium text-gray-700 mb-1">Monto del Bono ($)</label>
                <input 
                    id="monto"
                    name="monto" 
                    type="number" 
                    step="0.01" 
                    placeholder="Monto (ej. 150.00)" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500 transition duration-150" 
                />
            </div>
            
            <div>
                <label for="observacion" class="block text-sm font-medium text-gray-700 mb-1">Observación (Opcional)</label>
                <textarea 
                    id="observacion"
                    name="observacion" 
                    placeholder="Detalles sobre por qué se otorga el bono..." 
                    rows="2"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 resize-y"
                ></textarea>
            </div>
            
            <div class="pt-2">
                <button class="px-6 py-3 bg-teal-600 text-white font-bold rounded-lg shadow-md hover:bg-teal-700 transition duration-300" type="submit">
                    🎁 Registrar Bono
                </button>
            </div>
            
          </form>
        </div>

        <h2 class="text-2xl font-extrabold text-gray-800 mb-4">Historial de Bonos Otorgados</h2>
        
        <?php if(count($registros)==0): ?>
          <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded-lg" role="alert">
            <p class="font-bold">Información:</p>
            <p>No hay bonos registrados hasta el momento.</p>
          </div>
        <?php else: ?>
          <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden overflow-x-auto">
            <table class="min-w-[700px] w-full text-left">
              <thead>
                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                  <th class="py-3 px-4 text-left">Fecha</th>
                  <th class="py-3 px-4 text-left">Trabajador</th>
                  <th class="py-3 px-4 text-center">Tipo</th>
                  <th class="py-3 px-4 text-right">Monto</th>
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
                            'feriado' => 'bg-teal-200 text-teal-800',
                            'festivo' => 'bg-emerald-200 text-emerald-800',
                            'incentivo' => 'bg-indigo-200 text-indigo-800',
                            'recompensa' => 'bg-purple-200 text-purple-800',
                            default => 'bg-gray-200 text-gray-800'
                        };
                      ?>
                      <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $tipo_clase; ?>">
                        <?php echo ucfirst($r['tipo']); ?>
                      </span>
                    </td>
                    <td class="py-3 px-4 text-right font-bold text-teal-700 font-mono whitespace-nowrap">
                      +$<?php echo number_format($r['monto'], 2, ',', '.'); ?>
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