<?php
require_once __DIR__.'/conexion.php';
$trabajadores = $pdo->query('SELECT id,nombre,sueldo_hora FROM trabajadores ORDER BY nombre')->fetchAll();

if($_SERVER['REQUEST_METHOD']==='POST'){
    $trabajador = $_POST['trabajador'];
    $desde = $_POST['desde'];
    $hasta = $_POST['hasta'];
    $accion = $_POST['accion'] ?? 'reporte'; // 'reporte' o 'guardar'

    // Totales
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

    // sueldo hora
    $stmt = $pdo->prepare('SELECT sueldo_hora FROM trabajadores WHERE id = ?');
    $stmt->execute([$trabajador]);
    $sueldo_hora = $stmt->fetchColumn();

    // cálculos
    $positivos = $horasExtras + $bonos;
    $negativos = $adelantos + $descuentos;
    $pagoNeto = ($sueldo_hora*96) + $positivos - $negativos;

    // Guardar si el usuario presionó "Guardar datos"
    if ($accion === 'guardar') {
        $insert = $pdo->prepare('INSERT INTO pagos_realizados 
            (trabajador_id, desde, hasta, sueldo_hora, horas_extras, bonos, adelantos, descuentos, pago_neto) 
            VALUES (?,?,?,?,?,?,?,?,?)');
        $insert->execute([$trabajador,$desde,$hasta,$sueldo_hora,$horasExtras,$bonos,$adelantos,$descuentos,$pagoNeto]);
        $mensaje_guardado = "✅ Datos guardados correctamente en la base de datos.";
    }

    // Detalles (para mostrar en pantalla)
    $detAdelantos = $pdo->prepare('SELECT fecha, observacion, monto FROM adelantos WHERE trabajador_id=? AND fecha BETWEEN ? AND ? ORDER BY fecha');
    $detAdelantos->execute([$trabajador,$desde,$hasta]);
    $detHoras = $pdo->prepare('SELECT fecha, horas, total FROM horas_extras WHERE trabajador_id=? AND fecha BETWEEN ? AND ? ORDER BY fecha');
    $detHoras->execute([$trabajador,$desde,$hasta]);
    $detFaltas = $pdo->prepare('SELECT fecha, motivo, descuento FROM faltas_retrasos WHERE trabajador_id=? AND fecha BETWEEN ? AND ? ORDER BY fecha');
    $detFaltas->execute([$trabajador,$desde,$hasta]);
    $detBonos = $pdo->prepare('SELECT fecha, observacion, monto FROM bonos WHERE trabajador_id=? AND fecha BETWEEN ? AND ? ORDER BY fecha');
    $detBonos->execute([$trabajador,$desde,$hasta]);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8" />
<title>Reporte Quincenal</title>
<link rel="stylesheet" href="../css/style.css" />
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
<style>
  body {
    background:#121212;
    color:#e0e0e0;
    font-family:Arial, sans-serif;
    margin:0;
    padding:0;
  }
  header.app-header {
    background:#1e1e1e;
    padding:12px 24px;
    text-align:center;
    color:#80d4ff;
  }
  main.container {
    padding:20px;
    max-width:900px;
    margin:auto;
  }
  form {
    background:#1e1e1e;
    padding:12px;
    border-radius:8px;
  }
  .form-row {
    display:flex;
    gap:10px;
    flex-wrap:wrap;
  }
  select, input[type="date"] {
    background:#2c2c2c;
    border:1px solid #444;
    color:#fff;
    padding:6px 8px;
    border-radius:4px;
  }
  .btn {
    background:#2196f3;
    color:#fff;
    padding:8px 16px;
    border:none;
    border-radius:6px;
    cursor:pointer;
  }
  .btn:hover { background:#1976d2; }
  .btn-secondary {
    background:#43a047;
  }
  .btn-secondary:hover {
    background:#2e7d32;
  }
  details {
    background:#1e1e1e;
    padding:6px 12px;
    margin:4px 0;
    border-radius:6px;
  }
  details summary {
    cursor:pointer;
    font-weight:600;
    color:#80d4ff;
  }
  table.detalle {
    width:100%;
    border-collapse:collapse;
    margin-top:6px;
  }
  table.detalle th, table.detalle td {
    border-bottom:1px solid #333;
    padding:4px 8px;
    text-align:left;
  }
  table.detalle th { color:#ccc; }
  hr { border:0; border-top:1px solid #333; margin:12px 0; }
  a.small-muted {
    color:#aaa;
    text-decoration:none;
  }
  a.small-muted:hover { text-decoration:underline; }
  .mensaje {
    margin-top:10px;
    padding:10px;
    background:#2e7d32;
    color:#fff;
    border-radius:6px;
  }
</style>
</head>
<body>
<header class="app-header"><h1>Reporte Quincenal</h1></header>
<main class="container">
<form method="post">
  <div class="form-row">
    <select name="trabajador" required>
      <option value="">— Seleccioná trabajador —</option>
      <?php foreach($trabajadores as $t): ?>
        <option value="<?php echo $t['id']; ?>" 
          <?php if(isset($trabajador) && $trabajador == $t['id']) echo 'selected'; ?>>
          <?php echo htmlspecialchars($t['nombre']); ?>
        </option>
      <?php endforeach; ?>
    </select>
    <input name="desde" type="date" required value="<?php echo isset($desde) ? $desde : ''; ?>" />
    <input name="hasta" type="date" required value="<?php echo isset($hasta) ? $hasta : ''; ?>" />
  </div>
  <div style="height:8px"></div>
  <button class="btn" type="submit" name="accion" value="reporte">Generar reporte</button>
</form>

<?php if(isset($mensaje_guardado)): ?>
  <div class="mensaje"><?php echo $mensaje_guardado; ?></div>
<?php endif; ?>

<?php if(isset($pagoNeto)): ?>
<section id="reporte" style="margin-top:20px;">
  <h3>Resumen</h3>
  <p>Sueldo por hora: <strong><?php echo number_format($sueldo_hora,2,',','.'); ?></strong></p>

  <details>
    <summary>Total horas extras: <?php echo number_format($horasExtras,2,',','.'); ?></summary>
    <table class="detalle">
      <tr><th>Fecha</th><th>Horas</th><th>Total</th></tr>
      <?php foreach($detHoras as $h): ?>
        <tr><td><?php echo $h['fecha']; ?></td><td><?php echo $h['horas']; ?></td><td><?php echo $h['total']; ?></td></tr>
      <?php endforeach; ?>
    </table>
  </details>

  <details>
    <summary>Total bonos: <?php echo number_format($bonos,2,',','.'); ?></summary>
    <table class="detalle">
      <tr><th>Fecha</th><th>Motivo</th><th>Monto</th></tr>
      <?php foreach($detBonos as $b): ?>
        <tr><td><?php echo $b['fecha']; ?></td><td><?php echo htmlspecialchars($b['observacion']); ?></td><td><?php echo $b['monto']; ?></td></tr>
      <?php endforeach; ?>
    </table>
  </details>

  <details>
    <summary>Total adelantos: <?php echo number_format($adelantos,2,',','.'); ?></summary>
    <table class="detalle">
      <tr><th>Fecha</th><th>Motivo</th><th>Monto</th></tr>
      <?php foreach($detAdelantos as $a): ?>
        <tr><td><?php echo $a['fecha']; ?></td><td><?php echo htmlspecialchars($a['observacion']); ?></td><td><?php echo $a['monto']; ?></td></tr>
      <?php endforeach; ?>
    </table>
  </details>

  <details>
    <summary>Total descuentos (faltas/retrasos): <?php echo number_format($descuentos,2,',','.'); ?></summary>
    <table class="detalle">
      <tr><th>Fecha</th><th>Motivo</th><th>Descuento</th></tr>
      <?php foreach($detFaltas as $f): ?>
        <tr><td><?php echo $f['fecha']; ?></td><td><?php echo htmlspecialchars($f['motivo']); ?></td><td><?php echo $f['descuento']; ?></td></tr>
      <?php endforeach; ?>
    </table>
  </details>

  <hr>
  <p><strong>Positivos:</strong> <?php echo number_format($positivos,2,',','.'); ?></p>
  <p><strong>Negativos:</strong> <?php echo number_format($negativos,2,',','.'); ?></p>
  <h2>Pago neto estimado: <?php echo number_format($pagoNeto,2,',','.'); ?></h2>

  <canvas id="chart" style="max-width:600px"></canvas>
  <div style="margin-top:12px; display:flex; gap:10px;">
    <button class="btn" id="btn-pdf" type="button">📤 Exportar PDF</button>
    <form method="post" style="display:inline;">
      <input type="hidden" name="trabajador" value="<?php echo $trabajador; ?>">
      <input type="hidden" name="desde" value="<?php echo $desde; ?>">
      <input type="hidden" name="hasta" value="<?php echo $hasta; ?>">
      <input type="hidden" name="accion" value="guardar">
      <button class="btn btn-secondary" type="submit">💾 Guardar datos</button>
      <a href="./historial_pagos.php" class="btn btn-secondary" style="background:#555;">Ver historial de pagos</a>

    </form>
  </div>
</section>

<script>
const ctx = document.getElementById('chart').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['Adelantos','Horas Extras','Faltas/Desc.','Bonos'],
    datasets: [{
      label: 'Montos (Bs)',
      data: [<?php echo $adelantos;?>, <?php echo $horasExtras;?>, <?php echo $descuentos;?>, <?php echo $bonos;?>],
      backgroundColor: [
        'rgba(244,67,54,0.8)',
        'rgba(0,188,212,0.8)',
        'rgba(156,39,176,0.8)',
        'rgba(76,175,80,0.8)'
      ]
    }]
  }
});

document.getElementById('btn-pdf').addEventListener('click', function(){
  var element = document.getElementById('reporte');
  html2pdf().from(element).save('reporte_quincenal_<?php echo date("Ymd"); ?>.pdf');
});
</script>
<?php endif; ?>

<p style="margin-top:18px;"><a href="../index.html" class="small-muted">← Volver</a></p>
</main>
</body>
</html>
