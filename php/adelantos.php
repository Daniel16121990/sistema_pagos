<?php
require_once __DIR__.'/conexion.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $trabajador = $_POST['trabajador'] ?? '';
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $monto = $_POST['monto'] ?? 0;
    $obs = $_POST['observacion'] ?? '';
    $stmt = $pdo->prepare('INSERT INTO adelantos (trabajador_id,fecha,monto,observacion) VALUES (?,?,?,?)');
    $stmt->execute([$trabajador,$fecha,$monto,$obs]);
    header('Location: adelantos.php');
    exit;
}
$trabajadores = $pdo->query('SELECT id,nombre FROM trabajadores ORDER BY nombre')->fetchAll();
$rows = $pdo->query('SELECT a.*, t.nombre FROM adelantos a JOIN trabajadores t ON t.id = a.trabajador_id ORDER BY a.fecha DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Adelantos - Sistema de Pagos</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
  <header class="app-header"><h1>Adelantos</h1></header>
  <main class="container">
    <form method="post" action="adelantos.php" style="max-width:720px; margin-bottom:18px;">
      <div class="form-row">
        <select name="trabajador" required>
          <option value="">— Seleccioná trabajador —</option>
          <?php foreach($trabajadores as $t): ?>
            <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['nombre']); ?></option>
          <?php endforeach; ?>
        </select>
        <input name="fecha" type="date" value="<?php echo date('Y-m-d'); ?>" />
        <input name="monto" type="number" step="0.01" placeholder="Monto" required />
      </div>
      <div style="height:8px"></div>
      <textarea name="observacion" placeholder="Observación (opcional)"></textarea>
      <div style="height:8px"></div>
      <button class="btn" type="submit">Registrar adelanto</button>
    </form>

    <?php if(count($rows)): ?>
      <table class="table">
        <thead><tr><th>Fecha</th><th>Trabajador</th><th>Monto</th><th>Obs</th></tr></thead>
        <tbody>
          <?php foreach($rows as $r): ?>
            <tr>
              <td><?php echo htmlspecialchars($r['fecha']); ?></td>
              <td><?php echo htmlspecialchars($r['nombre']); ?></td>
              <td><?php echo number_format($r['monto'],2,',','.'); ?></td>
              <td><?php echo htmlspecialchars($r['observacion']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p class="small-muted">No hay adelantos registrados.</p>
    <?php endif; ?>

    <p style="margin-top:18px;"><a href="../index.html" class="small-muted">← Volver</a></p>
  </main>
</body>
</html>
