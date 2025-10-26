<?php
require_once __DIR__.'/conexion.php';

// Cargar lista de trabajadores
$trabajadores = $pdo->query("SELECT id, nombre FROM trabajadores ORDER BY nombre")->fetchAll();

// Variables de filtro
$filtro_trabajador = $_GET['trabajador'] ?? '';
$filtro_desde = $_GET['desde'] ?? '';
$filtro_hasta = $_GET['hasta'] ?? '';

// Armar consulta dinámica
$sql = "SELECT p.*, t.nombre 
        FROM pagos_realizados p 
        INNER JOIN trabajadores t ON p.trabajador_id = t.id 
        WHERE 1=1";
$params = [];

if ($filtro_trabajador != '') {
    $sql .= " AND p.trabajador_id = ?";
    $params[] = $filtro_trabajador;
}
if ($filtro_desde != '' && $filtro_hasta != '') {
    $sql .= " AND DATE(p.fecha_registro) BETWEEN ? AND ?";
    $params[] = $filtro_desde;
    $params[] = $filtro_hasta;
}

$sql .= " ORDER BY p.fecha_registro DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pagos = $stmt->fetchAll();

// Calcular total general
$totalGeneral = 0;
foreach ($pagos as $p) $totalGeneral += $p['pago_neto'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8" />
<title>Historial de Pagos</title>
<link rel="stylesheet" href="../css/style.css" />
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
  max-width:1000px;
  margin:auto;
}
form {
  background:#1e1e1e;
  padding:12px;
  border-radius:8px;
  margin-bottom:16px;
}
.form-row {
  display:flex;
  flex-wrap:wrap;
  gap:10px;
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
table {
  width:100%;
  border-collapse:collapse;
  margin-top:10px;
}
th, td {
  border-bottom:1px solid #333;
  padding:6px 10px;
  text-align:left;
}
th {
  color:#80d4ff;
}
tr:hover {
  background:#1e1e1e;
}
.total {
  text-align:right;
  margin-top:10px;
  font-weight:bold;
  color:#76ff03;
}
a.small-muted {
  color:#aaa;
  text-decoration:none;
}
a.small-muted:hover { text-decoration:underline; }
</style>
</head>
<body>
<header class="app-header">
  <h1>Historial de Pagos Realizados</h1>
</header>
<main class="container">

<form method="get">
  <div class="form-row">
    <select name="trabajador">
      <option value="">— Todos los trabajadores —</option>
      <?php foreach($trabajadores as $t): ?>
        <option value="<?php echo $t['id']; ?>" 
          <?php if($filtro_trabajador==$t['id']) echo 'selected'; ?>>
          <?php echo htmlspecialchars($t['nombre']); ?>
        </option>
      <?php endforeach; ?>
    </select>
    <input type="date" name="desde" value="<?php echo $filtro_desde; ?>" />
    <input type="date" name="hasta" value="<?php echo $filtro_hasta; ?>" />
    <button class="btn" type="submit">Filtrar</button>
  </div>
</form>

<?php if(count($pagos)>0): ?>
<table>
  <tr>
    <th>Fecha registro</th>
    <th>Trabajador</th>
    <th>Desde</th>
    <th>Hasta</th>
    <th>Horas Extras</th>
    <th>Bonos</th>
    <th>Adelantos</th>
    <th>Descuentos</th>
    <th>Pago Neto</th>
  </tr>
  <?php foreach($pagos as $p): ?>
  <tr>
    <td><?php echo date("d/m/Y H:i", strtotime($p['fecha_registro'])); ?></td>
    <td><?php echo htmlspecialchars($p['nombre']); ?></td>
    <td><?php echo $p['desde']; ?></td>
    <td><?php echo $p['hasta']; ?></td>
    <td><?php echo number_format($p['horas_extras'],2,',','.'); ?></td>
    <td><?php echo number_format($p['bonos'],2,',','.'); ?></td>
    <td><?php echo number_format($p['adelantos'],2,',','.'); ?></td>
    <td><?php echo number_format($p['descuentos'],2,',','.'); ?></td>
    <td><strong><?php echo number_format($p['pago_neto'],2,',','.'); ?></strong></td>
  </tr>
  <?php endforeach; ?>
</table>
<div class="total">
  Total general: Bs <?php echo number_format($totalGeneral,2,',','.'); ?>
</div>
<?php else: ?>
<p>No se encontraron pagos registrados con los filtros seleccionados.</p>
<?php endif; ?>

<p style="margin-top:18px;">
  <a href="./reporte.php" class="small-muted">← Volver al reporte</a>
</p>

</main>
</body>
</html>
