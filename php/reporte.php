<?php
require_once __DIR__.'/conexion.php';
$trabajadores = $pdo->query('SELECT id,nombre,sueldo_base FROM trabajadores ORDER BY nombre')->fetchAll();

if($_SERVER['REQUEST_METHOD']==='POST'){
    $trabajador = $_POST['trabajador'];
    $desde = $_POST['desde'];
    $hasta = $_POST['hasta'];

    // Totales
    $stmt = $pdo->prepare('SELECT IFNULL(SUM(monto),0) AS total FROM adelantos WHERE trabajador_id=? AND fecha BETWEEN ? AND ?');
    $stmt->execute([$trabajador,$desde,$hasta]); $adelantos = $stmt->fetchColumn();
    $stmt = $pdo->prepare('SELECT IFNULL(SUM(total),0) AS total FROM horas_extras WHERE trabajador_id=? AND fecha BETWEEN ? AND ?');
    $stmt->execute([$trabajador,$desde,$hasta]); $horasExtras = $stmt->fetchColumn();
    $stmt = $pdo->prepare('SELECT IFNULL(SUM(descuento),0) AS total FROM faltas_retrasos WHERE trabajador_id=? AND fecha BETWEEN ? AND ?');
    $stmt->execute([$trabajador,$desde,$hasta]); $descuentos = $stmt->fetchColumn();
    $stmt = $pdo->prepare('SELECT IFNULL(SUM(monto),0) AS total FROM bonos WHERE trabajador_id=? AND fecha BETWEEN ? AND ?');
    $stmt->execute([$trabajador,$desde,$hasta]); $bonos = $stmt->fetchColumn();

    // Detalles
    $detAdelantos = $pdo->prepare('SELECT fecha, observacion, monto FROM adelantos WHERE trabajador_id=? AND fecha BETWEEN ? AND ? ORDER BY fecha');
    $detAdelantos->execute([$trabajador,$desde,$hasta]);
    $detHoras = $pdo->prepare('SELECT fecha, horas, total FROM horas_extras WHERE trabajador_id=? AND fecha BETWEEN ? AND ? ORDER BY fecha');
    $detHoras->execute([$trabajador,$desde,$hasta]);
    $detFaltas = $pdo->prepare('SELECT fecha, motivo, descuento FROM faltas_retrasos WHERE trabajador_id=? AND fecha BETWEEN ? AND ? ORDER BY fecha');
    $detFaltas->execute([$trabajador,$desde,$hasta]);
    $detBonos = $pdo->prepare('SELECT fecha, observacion, monto FROM bonos WHERE trabajador_id=? AND fecha BETWEEN ? AND ? ORDER BY fecha');
    $detBonos->execute([$trabajador,$desde,$hasta]);

    // sueldo base
    $stmt = $pdo->prepare('SELECT sueldo_base FROM trabajadores WHERE id = ?');
    $stmt->execute([$trabajador]);
    $sueldo_base = $stmt->fetchColumn();

    // cálculos
    $positivos = $horasExtras + $bonos;
    $negativos = $adelantos + $descuentos;
    $pagoNeto = ($sueldo_base/2) + $positivos - $negativos;
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
  details {background:#1e1e1e;padding:6px 12px;margin:4px 0;border-radius:6px;}
  details summary {cursor:pointer;font-weight:600;color:#80d4ff;}
  table.detalle {width:100%;border-collapse:collapse;margin-top:6px;}
  table.detalle th, table.detalle td {border-bottom:1px solid #333;padding:4px 8px;text-align:left;}
  table.detalle th {color:#ccc;}
</style>
</head>
<body>
<header class="app-header"><h1>Reporte Quincenal</h1></header>
<main class="container">
<form method="post" style="max-width:840px; margin-bottom:18px;">
  <div class="form-row">
    <select name="trabajador" required>
      <option value="">— Seleccioná trabajador —</option>
      <?php foreach($trabajadores as $t): ?>
        <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['nombre']); ?></option>
      <?php endforeach; ?>
    </select>
    <input name="desde" type="date" required />
    <input name="hasta" type="date" required />
  </div>
  <div style="height:8px"></div>
  <div class="form-row">
    <input name="ganancia_manual" placeholder="Saldo total (opcional)" type="number" step="0.01" />
    <input name="valor_hora_manual" placeholder="Valor por hora (opcional)" type="number" step="0.01" />
  </div>
  <div style="height:8px"></div>
  <button class="btn" type="submit">Generar reporte</button>
</form>

<?php if(isset($pagoNeto)): ?>
<section id="reporte">
  <h3>Resumen</h3>
  <p>Sueldo base quincenal: <strong><?php echo number_format($sueldo_base/2,2,',','.'); ?></strong></p>

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
  <div style="margin-top:12px">
    <button class="btn" id="btn-pdf">📤 Exportar PDF</button>
  </div>
</section>

<script>
const ctx = document.getElementById('chart').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['Adelantos','HorasExtras','FaltasDesc','Bonos'],
    datasets: [{
      label: 'Montos (Bs)',
      data: [<?php echo $adelantos;?>, <?php echo $horasExtras;?>, <?php echo $descuentos;?>, <?php echo $bonos;?>],
      backgroundColor: ['rgba(244,67,54,0.8)','rgba(0,188,212,0.8)','rgba(156,39,176,0.8)','rgba(76,175,80,0.8)']
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
