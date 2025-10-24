<?php
require_once __DIR__.'/conexion.php';
$stmt = $pdo->query('SELECT * FROM trabajadores ORDER BY id DESC');
$rows = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Trabajadores - Sistema de Pagos</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
  <header class="app-header">
    <h1>Trabajadores</h1>
  </header>
  <main class="container">
    <a class="card" href="agregar.php">➕ Agregar Trabajador</a>
    <div style="height:18px"></div>
    <?php if(count($rows)==0): ?>
      <p class="small-muted">No hay trabajadores. Agregá uno.</p>
    <?php else: ?>
      <table class="table">
        <thead><tr><th>ID</th><th>Nombre</th><th>Cargo</th><th>Sueldo base</th><th>Ingreso</th></tr></thead>
        <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><?php echo htmlspecialchars($r['id']); ?></td>
            <td><?php echo htmlspecialchars($r['nombre']); ?></td>
            <td><?php echo htmlspecialchars($r['cargo']); ?></td>
            <td><?php echo number_format($r['sueldo_hora'],2,',','.'); ?></td>
            <td><?php echo htmlspecialchars($r['fecha_ingreso']); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
    <p style="margin-top:18px;"><a href="../index.html" class="small-muted">← Volver</a></p>
  </main>
</body>
</html>
