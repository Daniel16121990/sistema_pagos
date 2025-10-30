<?php
// Asegúrate de que este archivo esté en la carpeta /php/
require_once __DIR__.'/conexion.php'; 

// 1. Obtener lista de trabajadores para el <select>
$trabajadores = $pdo->query('SELECT id,nombre,sueldo_hora FROM trabajadores ORDER BY nombre')->fetchAll();

// Inicialización de variables para evitar errores PHP antes del primer POST
$trabajador = $desde = $hasta = null;
$pagoNeto = null;
$mensaje_guardado = null;
$sueldo_hora = 0;
$adelantos = $horasExtras = $descuentos = $bonos = 0;
$positivos = $negativos = 0;
$pagoBase = 0;
$trabajador_nombre = 'Trabajador Seleccionado';
$detAdelantos = $detHoras = $detFaltas = $detBonos = [];
$diasTrabajables = 0; // Nueva variable para mostrar los días reales
$horasBase = 0;       // Nueva variable para mostrar las horas base reales

if($_SERVER['REQUEST_METHOD']==='POST'){
    $trabajador = $_POST['trabajador'];
    $desde = $_POST['desde'];
    $hasta = $_POST['hasta'];
    $accion = $_POST['accion'] ?? 'reporte'; // 'reporte' o 'guardar'

    // --- 1. Obtener Totales (Adelantos, Horas Extras, Descuentos, Bonos) ---
    // (Estas consultas NO necesitan ser modificadas, ya filtran por rango de fecha)
    
    $stmt = $pdo->prepare('SELECT IFNULL(SUM(monto),0) FROM adelantos WHERE trabajador_id=? AND fecha BETWEEN ? AND ?');
    $stmt->execute([$trabajador,$desde,$hasta]); 
    $adelantos = $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT IFNULL(SUM(total),0) FROM horas_extras WHERE trabajador_id=? AND fecha BETWEEN ? AND ?');
    $stmt->execute([$trabajador,$desde,$hasta]); 
    $horasExtras = $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT IFNULL(SUM(descuento),0) FROM faltas_retrasos WHERE trabajador_id=? AND fecha BETWEEN ? AND ?');
    $stmt->execute([$trabajador,$desde,$hasta]); 
    $descuentos = $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT IFNULL(SUM(monto),0) FROM bonos WHERE trabajador_id=? AND fecha BETWEEN ? AND ?');
    $stmt->execute([$trabajador,$desde,$hasta]); 
    $bonos = $stmt->fetchColumn();

    // --- 2. Obtener Sueldo por Hora y Nombre ---
    $stmt = $pdo->prepare('SELECT sueldo_hora, nombre FROM trabajadores WHERE id = ?');
    $stmt->execute([$trabajador]);
    $trabajador_data = $stmt->fetch();
    $sueldo_hora = $trabajador_data['sueldo_hora'] ?? 0;
    $trabajador_nombre = $trabajador_data['nombre'] ?? 'Error';

    // ----------------------------------------------------------------------
    // --- 3. CÁLCULOS DINÁMICOS DE PAGO BASE (NUEVA LÓGICA) ---
    // ----------------------------------------------------------------------
    
    // Configuración de la jornada laboral
    $horasJornada = 8; // Asumimos 8 horas diarias de trabajo normal

    // 3a. Convertir las fechas a objetos DateTime
    $fechaInicio = new DateTime($desde);
    $fechaFin = new DateTime($hasta);
    // Para incluir la fecha final en el rango, ajustamos el límite superior
    $fechaFin->modify('+1 day'); 

    $diasTrabajables = 0;
    $intervalo = DateInterval::createFromDateString('1 day');
    $periodo = new DatePeriod($fechaInicio, $intervalo, $fechaFin);

    foreach ($periodo as $dt) {
        // Obtenemos el día de la semana (N: 1=Lunes, ..., 7=Domingo)
        $diaSemana = (int)$dt->format('N'); 
        
        // Excluimos domingos (Día 7) de los días base de pago.
        if ($diaSemana !== 7) { 
            $diasTrabajables++;
        }
    }
    
    // 3b. Calcular Horas Base y Pago Base
    $horasBase = $diasTrabajables * $horasJornada;
    $pagoBase = $sueldo_hora * $horasBase;

    // 3c. Cálculo Final del Pago Neto (Ajustes)
    $positivos = $horasExtras + $bonos;
    $negativos = $adelantos + $descuentos;
    
    // Cálculo final: Pago Base (Horas reales) + Ingresos - Egresos
    $pagoNeto = $pagoBase + $positivos - $negativos;


    // --- 4. Guardar Pago (si se solicita) ---
    if ($accion === 'guardar') {
        $insert = $pdo->prepare('INSERT INTO pagos_realizados 
            (trabajador_id, desde, hasta, sueldo_hora, horas_base, dias_base, horas_extras, bonos, adelantos, descuentos, pago_neto) 
            VALUES (?,?,?,?,?,?,?,?,?,?,?)');
        $insert->execute([
            $trabajador,
            $desde,
            $hasta,
            (float)$sueldo_hora,
            (float)$horasBase,   // Nuevo: Guardar horas base
            (int)$diasTrabajables, // Nuevo: Guardar días base
            (float)$horasExtras,
            (float)$bonos,
            (float)$adelantos,
            (float)$descuentos,
            (float)$pagoNeto
        ]);
        $mensaje_guardado = "✅ ¡Datos de pago guardados correctamente!";
    }

    // --- 5. Obtener Detalles para Tablas ---
    // (Estas consultas son iguales, ya filtran correctamente por el rango)
    $detAdelantosStmt = $pdo->prepare('SELECT fecha, observacion, monto FROM adelantos WHERE trabajador_id=? AND fecha BETWEEN ? AND ? ORDER BY fecha');
    $detAdelantosStmt->execute([$trabajador,$desde,$hasta]);
    $detAdelantos = $detAdelantosStmt->fetchAll();

    $detHorasStmt = $pdo->prepare('SELECT fecha, horas, total, observacion FROM horas_extras WHERE trabajador_id=? AND fecha BETWEEN ? AND ? ORDER BY fecha');
    $detHorasStmt->execute([$trabajador,$desde,$hasta]);
    $detHoras = $detHorasStmt->fetchAll();

    $detFaltasStmt = $pdo->prepare('SELECT fecha, motivo, descuento FROM faltas_retrasos WHERE trabajador_id=? AND fecha BETWEEN ? AND ? ORDER BY fecha');
    $detFaltasStmt->execute([$trabajador,$desde,$hasta]);
    $detFaltas = $detFaltasStmt->fetchAll();

    $detBonosStmt = $pdo->prepare('SELECT fecha, observacion, monto FROM bonos WHERE trabajador_id=? AND fecha BETWEEN ? AND ? ORDER BY fecha');
    $detBonosStmt->execute([$trabajador,$desde,$hasta]);
    $detBonos = $detBonosStmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reporte Dinámico - Sistema de Pagos</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>

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
    
    /* Estilos para el elemento <details> */
    .custom-details summary {
        list-style: none; /* Oculta el marcador por defecto en Chrome/Edge */
        cursor: pointer;
        padding: 0.75rem 1rem;
        background-color: #f3f4f6; /* gray-100 */
        border-radius: 0.5rem; /* rounded-lg */
        font-weight: 600;
        color: #4b5563; /* gray-600 */
        transition: background-color 0.2s ease;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .custom-details summary:hover {
        background-color: #e5e7eb; /* gray-200 */
    }
    .custom-details summary::-webkit-details-marker {
        display: none; /* Oculta el marcador en WebKit */
    }
    .custom-details[open] summary {
        background-color: #e0f2f1; /* teal-50 */
        color: #0d9488; /* teal-600 */
    }
    .custom-details-content {
        padding: 0.5rem 0;
        border-left: 3px solid #0d9488; /* teal-600 */
        margin-top: 0.25rem;
        padding-left: 1rem;
    }

    /* Contenedor para scroll horizontal en tablas pequeñas */
    .table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
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
        <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition duration-200" href="bonos.php">
          🎁 Bonos
        </a>
        <a class="block px-4 py-3 rounded-lg text-white font-semibold bg-indigo-700" href="reporte.php">
          📊 Reporte de Pago
        </a>
        <a class="block px-4 py-3 rounded-lg text-gray-300 hover:bg-indigo-600 hover:text-white transition duration-200" href="historial_pagos.php">
          📋 Historial de Pagos
        </a>
      </div>
          <div class="p-4 border-t border-gray-700">
        <a href="../logout.php" class="block text-sm text-red-400 hover:text-red-300 transition duration-200 font-medium">
          🚪 Cerrar Sesión
        </a>
      </div>
    </nav>

    <div class="flex flex-col flex-1 lg:pl-0 pt-16 lg:pt-0">
      
      <header class="app-header bg-white shadow-md p-4 lg:p-6 sticky top-0 z-30">
        <h1 class="text-2xl font-extrabold text-gray-800">Generador de Reporte de Pago Dinámico</h1>
      </header>

      <main class="container p-6 flex-1">
        
        <div class="w-full bg-white p-6 md:p-8 rounded-xl shadow-xl border border-gray-200 mb-8">
          <h2 class="text-xl font-bold mb-6 text-gray-700">Seleccionar Período y Trabajador</h2>

          <form method="post" action="reporte.php" class="space-y-4">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="md:col-span-2">
                    <label for="trabajador" class="block text-sm font-medium text-gray-700 mb-1">Trabajador</label>
                    <select 
                        id="trabajador"
                        name="trabajador" 
                        required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                    >
                        <option value="">— Seleccioná trabajador —</option>
                        <?php foreach($trabajadores as $t): ?>
                            <option value="<?php echo $t['id']; ?>" 
                              <?php if(isset($trabajador) && $trabajador == $t['id']) echo 'selected'; ?>>
                              <?php echo htmlspecialchars($t['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="desde" class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                    <input 
                        id="desde"
                        name="desde" 
                        type="date" 
                        required 
                        value="<?php echo isset($desde) ? $desde : date('Y-m-01'); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150" 
                    />
                </div>

                <div>
                    <label for="hasta" class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                    <input 
                        id="hasta"
                        name="hasta" 
                        type="date" 
                        required 
                        value="<?php echo isset($hasta) ? $hasta : date('Y-m-d'); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition duration-150" 
                    />
                </div>
            </div>
            
            <div class="pt-2">
                <button class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-lg shadow-md hover:bg-indigo-700 transition duration-300 w-full md:w-auto" 
                        type="submit" 
                        name="accion" 
                        value="reporte">
                    📊 Generar Reporte
                </button>
            </div>
            
          </form>
        </div>

        <?php if(isset($mensaje_guardado)): ?>
          <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6" role="alert">
            <p class="font-bold">¡Éxito!</p>
            <p><?php echo $mensaje_guardado; ?></p>
          </div>
        <?php endif; ?>

        <?php if(isset($pagoNeto)): ?>
          
          <h2 class="text-2xl font-extrabold text-gray-800 mb-4">Resultado del Reporte</h2>
          <div class="bg-white p-6 md:p-8 rounded-xl shadow-xl border border-gray-200 mb-8" id="reporte">
            
            <div class="border-b pb-4 mb-4">
                <h3 class="text-xl font-bold text-gray-800 mb-1">
                    Reporte para: <?php echo htmlspecialchars($trabajador_nombre); ?>
                </h3>
                <p class="text-gray-500 text-sm">Período: <?php echo date('d/m/Y', strtotime($desde)); ?> al <?php echo date('d/m/Y', strtotime($hasta)); ?></p>
                
                <p class="text-gray-600 text-sm mt-2">Sueldo Base por Hora: <strong class="text-teal-600">$<?php echo number_format($sueldo_hora, 2, ',', '.'); ?></strong></p>
                <p class="text-gray-600 text-sm">Días Base Trabajables (sin domingos): <strong class="text-teal-600"><?php echo $diasTrabajables; ?> días</strong></p>
                <p class="text-gray-600 text-sm">Pago Base (<?php echo $horasBase; ?> Horas): <strong class="text-teal-600">$<?php echo number_format($pagoBase, 2, ',', '.'); ?></strong></p>
                </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="md:col-span-1 bg-indigo-600 text-white p-5 rounded-xl shadow-lg transform hover:scale-[1.02] transition duration-300">
                    <p class="text-sm font-medium opacity-80">PAGO NETO ESTIMADO</p>
                    <h2 class="text-3xl font-extrabold mt-1">$<?php echo number_format($pagoNeto, 2, ',', '.'); ?></h2>
                </div>
                
                <div class="bg-green-100 text-green-700 p-5 rounded-xl shadow-md">
                    <p class="text-sm font-medium">TOTAL INGRESOS (Bonos + H.E.)</p>
                    <h2 class="text-2xl font-bold mt-1">+$<?php echo number_format($positivos, 2, ',', '.'); ?></h2>
                </div>
                
                <div class="bg-red-100 text-red-700 p-5 rounded-xl shadow-md">
                    <p class="text-sm font-medium">TOTAL EGRESOS (Adelantos + Desc.)</p>
                    <h2 class="text-2xl font-bold mt-1">-$<?php echo number_format($negativos, 2, ',', '.'); ?></h2>
                </div>
            </div>

            <div class="mb-8">
                <h4 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Distribución de Ajustes</h4>
                <div class="relative max-w-lg mx-auto">
                    <canvas id="chart"></canvas>
                </div>
            </div>
            
            <h4 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Detalle de Transacciones</h4>

            <details class="custom-details mb-2">
              <summary>
                <span>💰 Adelantos Realizados</span>
                <span class="text-red-600 font-bold">-$<?php echo number_format($adelantos, 2, ',', '.'); ?></span>
              </summary>
              <div class="custom-details-content table-container">
                <table class="w-full text-left detalle min-w-[400px]">
                  <thead class="text-xs uppercase text-gray-500">
                    <tr><th>Fecha</th><th>Motivo</th><th class="text-right">Monto</th></tr>
                  </thead>
                  <tbody>
                    <?php foreach($detAdelantos as $a): ?>
                      <tr class="border-b border-gray-100">
                        <td class="py-2 px-1 text-xs whitespace-nowrap"><?php echo date('d/m/Y', strtotime($a['fecha'])); ?></td>
                        <td class="py-2 px-1 text-xs"><?php echo htmlspecialchars($a['observacion'] ?? '—'); ?></td>
                        <td class="py-2 px-1 text-xs text-right text-red-600 font-mono whitespace-nowrap">-$<?php echo number_format($a['monto'], 2, ',', '.'); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </details>
            
            <details class="custom-details mb-2">
  <summary>
    <span>⏱️ Horas Extras Trabajadas</span>
    <span class="text-green-600 font-bold">+$<?php echo number_format($horasExtras, 2, ',', '.'); ?></span>
  </summary>
  <div class="custom-details-content table-container">
    <table class="w-full text-left detalle min-w-[500px]"> <thead class="text-xs uppercase text-gray-500">
        <tr>
          <th>Fecha</th>
          <th>Horas</th>
          <th>Observación</th>
          <th class="text-right">Total</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($detHoras as $h): ?>
          <tr class="border-b border-gray-100">
            <td class="py-2 px-1 text-xs whitespace-nowrap"><?php echo date('d/m/Y', strtotime($h['fecha'])); ?></td>
            <td class="py-2 px-1 text-xs text-center"><?php echo $h['horas']; ?></td>
            <td class="py-2 px-1 text-xs"><?php echo htmlspecialchars($h['observacion'] ?? '—'); ?></td>
            <td class="py-2 px-1 text-xs text-right text-green-600 font-mono whitespace-nowrap">+$<?php echo number_format($h['total'], 2, ',', '.'); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</details>

            <details class="custom-details mb-2">
              <summary>
                <span>📅 Faltas y Retrasos Descontados</span>
                <span class="text-red-600 font-bold">-$<?php echo number_format($descuentos, 2, ',', '.'); ?></span>
              </summary>
              <div class="custom-details-content table-container">
                <table class="w-full text-left detalle min-w-[400px]">
                  <thead class="text-xs uppercase text-gray-500">
                    <tr><th>Fecha</th><th>Motivo</th><th class="text-right">Descuento</th></tr>
                  </thead>
                  <tbody>
                    <?php foreach($detFaltas as $f): ?>
                      <tr class="border-b border-gray-100">
                        <td class="py-2 px-1 text-xs whitespace-nowrap"><?php echo date('d/m/Y', strtotime($f['fecha'])); ?></td>
                        <td class="py-2 px-1 text-xs"><?php echo htmlspecialchars($f['motivo'] ?? '—'); ?></td>
                        <td class="py-2 px-1 text-xs text-right text-red-600 font-mono whitespace-nowrap">-$<?php echo number_format($f['descuento'], 2, ',', '.'); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </details>
            
            <details class="custom-details mb-2">
              <summary>
                <span>🎁 Bonos Otorgados</span>
                <span class="text-green-600 font-bold">+$<?php echo number_format($bonos, 2, ',', '.'); ?></span>
              </summary>
              <div class="custom-details-content table-container">
                <table class="w-full text-left detalle min-w-[400px]">
                  <thead class="text-xs uppercase text-gray-500">
                    <tr><th>Fecha</th><th>Motivo</th><th class="text-right">Monto</th></tr>
                  </thead>
                  <tbody>
                    <?php foreach($detBonos as $b): ?>
                      <tr class="border-b border-gray-100">
                        <td class="py-2 px-1 text-xs whitespace-nowrap"><?php echo date('d/m/Y', strtotime($b['fecha'])); ?></td>
                        <td class="py-2 px-1 text-xs"><?php echo htmlspecialchars($b['observacion'] ?? '—'); ?></td>
                        <td class="py-2 px-1 text-xs text-right text-green-600 font-mono whitespace-nowrap">+$<?php echo number_format($b['monto'], 2, ',', '.'); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </details>
          </div>

          <div class="flex flex-col md:flex-row gap-4 mb-8">
            <button class="flex-1 px-6 py-3 bg-teal-600 text-white font-bold rounded-lg shadow-md hover:bg-teal-700 transition duration-300" id="btn-pdf" type="button">
              <span class="inline-block mr-2">📤</span> Exportar PDF
            </button>
            <form method="post" action="reporte.php" class="flex-1">
              <input type="hidden" name="trabajador" value="<?php echo htmlspecialchars($trabajador); ?>">
              <input type="hidden" name="desde" value="<?php echo htmlspecialchars($desde); ?>">
              <input type="hidden" name="hasta" value="<?php echo htmlspecialchars($hasta); ?>">
              <input type="hidden" name="accion" value="guardar">
              <button class="w-full px-6 py-3 bg-green-600 text-white font-bold rounded-lg shadow-md hover:bg-green-700 transition duration-300" type="submit">
                <span class="inline-block mr-2">💾</span> Guardar datos de Pago
              </button>
            </form>
          </div>

          <script>
            // Solo inicializar Chart.js si hay datos
            document.addEventListener('DOMContentLoaded', function() {
              const ctx = document.getElementById('chart');
              if (ctx) {
                  // Datos pasados desde PHP
                  const adelantos = <?php echo $adelantos;?>;
                  const horasExtras = <?php echo $horasExtras;?>;
                  const descuentos = <?php echo $descuentos;?>;
                  const bonos = <?php echo $bonos;?>;

                  new Chart(ctx, {
                      type: 'bar',
                      data: {
                          labels: ['Adelantos','Horas Extras','Faltas/Desc.','Bonos'],
                          datasets: [{
                              label: 'Impacto en el Pago Neto ($)',
                              data: [
                                  adelantos, 
                                  horasExtras, 
                                  descuentos, 
                                  bonos
                              ],
                              backgroundColor: [
                                  'rgba(239, 68, 68, 0.8)',  // red-500 (Adelantos)
                                  'rgba(20, 184, 166, 0.8)', // teal-500 (Horas Extras)
                                  'rgba(124, 58, 237, 0.8)', // violet-600 (Descuentos)
                                  'rgba(34, 197, 94, 0.8)'   // green-500 (Bonos)
                              ],
                              borderColor: 'rgba(255, 255, 255, 1)',
                              borderWidth: 1
                          }]
                      },
                      options: {
                          responsive: true,
                          plugins: {
                              legend: {
                                  position: 'top',
                              },
                              title: {
                                  display: true,
                                  text: 'Desglose de Componentes de Ajuste'
                              }
                          },
                          scales: {
                              y: {
                                  beginAtZero: true
                              }
                          }
                      }
                  });
              }
            });

            // Lógica de exportación a PDF
            document.getElementById('btn-pdf').addEventListener('click', function(){
                var element = document.getElementById('reporte');
                var workerName = "<?php echo htmlspecialchars($trabajador_nombre ?? 'trabajador'); ?>";
                var dateRange = "<?php echo date('Ymd', strtotime($desde ?? '')); ?>-<?php echo date('Ymd', strtotime($hasta ?? '')); ?>";
                
                // Opciones de configuración para el PDF (ajustar margen, tamaño)
                var opt = {
                    margin:       1,
                    filename:     'reporte_pago_' + workerName.replace(/\s/g, '_') + '_' + dateRange + '.pdf',
                    image:        { type: 'jpeg', quality: 0.98 },
                    html2canvas:  { scale: 2, logging: true, dpi: 192, letterRendering: true },
                    jsPDF:        { unit: 'cm', format: 'a4', orientation: 'portrait' }
                };

                html2pdf().set(opt).from(element).save();
            });
          </script>
        <?php endif; ?>
        
      </main>

      <footer class="app-footer p-4 text-center border-t border-gray-200 mt-8 bg-white">
        &copy; <span id="year-footer"></span> Sistema de Pagos. 📊
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