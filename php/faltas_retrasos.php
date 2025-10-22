<?php
require_once __DIR__.'/conexion.php';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $trabajador_id = $_POST['trabajador_id'];
    $fecha = $_POST['fecha'];
    $tipo = $_POST['tipo'];
    $motivo = $_POST['motivo'] ?? null;
    $descuento = $_POST['descuento'] ?? 0;
    $observacion = $_POST['observacion'] ?? null;
    $stmt = $pdo->prepare('INSERT INTO faltas_retrasos (trabajador_id, fecha, tipo, motivo, descuento, observacion) VALUES (?,?,?,?,?,?)');
    $stmt->execute([$trabajador_id,$fecha,$tipo,$motivo,$descuento,$observacion]);
    header('Location: faltas_retrasos.php');
    exit;
}
$trabajadores = $pdo->query('SELECT id,nombre FROM trabajadores ORDER BY nombre')->fetchAll();
$registros = $pdo->query('SELECT f.*, t.nombre FROM faltas_retrasos f JOIN trabajadores t ON t.id = f.trabajador_id ORDER BY f.fecha DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Faltas y Retrasos - Sistema de Pagos</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
  <header class="app-header"><h1>Faltas / Retrasos</h1></header>
  <main class="container">
    <form method="post" action="faltas_retrasos.php" style="max-width:720px; margin-bottom:18px;">
      <div class="form-row">
        <select name="trabajador_id" required>
          <option value="">— Seleccioná trabajador —</option>
          <?php foreach($trabajadores as $t): ?>
            <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['nombre']); ?></option>
          <?php endforeach; ?>
        </select>
        <input name="fecha" type="date" value="<?php echo date('Y-m-d'); ?>" />
        <select name="tipo" required>
          <option value="falta">Falta</option>
          <option value="retraso">Retraso</option>
        </select>
      </div>
      <div style="height:8px"></div>
      <input name="motivo" placeholder="Motivo (ej. enfermedad, viaje, irresponsabilidad)" />
      <input name="descuento" type="number" step="0.01" placeholder="Descuento (ej. 200.00)" />
      <div style="height:8px"></div>
      <textarea name="observacion" placeholder="Observación (opcional)"></textarea>
      <div style="height:8px"></div>
      <button class="btn" type="submit">Registrar falta/retraso</button>
    </form>

    <?php if(count($registros)): ?>
      <table class="table">
        <thead><tr><th>Fecha</th><th>Trabajador</th><th>Tipo</th><th>Motivo</th><th>Descuento</th><th>Obs</th></tr></thead>
        <tbody>
          <?php foreach($registros as $r): ?>
            <tr>
              <td><?php echo htmlspecialchars($r['fecha']); ?></td>
              <td><?php echo htmlspecialchars($r['nombre']); ?></td>
              <td><?php echo ucfirst($r['tipo']); ?></td>
              <td><?php echo htmlspecialchars($r['motivo']); ?></td>
              <td><?php echo number_format($r['descuento'],2,',','.'); ?></td>
              <td><?php echo htmlspecialchars($r['observacion']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p class="small-muted">No hay registros.</p>
    <?php endif; ?>

    <p style="margin-top:18px;"><a href="../index.html" class="small-muted">← Volver</a></p>
  </main>
</body>
</html>
