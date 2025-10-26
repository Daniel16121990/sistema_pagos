<?php
require_once __DIR__.'/conexion.php';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $trabajador = $_POST['trabajador'] ?? '';
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $horas = $_POST['horas'] ?? 0;
    $valor = $_POST['valor'] ?? 0;
    $obs = $_POST['observacion'] ?? '';
    $total = floatval($horas) * floatval($valor);

    $stmt = $pdo->prepare('INSERT INTO horas_extras (trabajador_id,fecha,horas,valor_hora,total,observacion) VALUES (?,?,?,?,?,?)');
    $stmt->execute([$trabajador,$fecha,$horas,$valor,$total,$obs]);
    header('Location: horas_extras.php');
    exit;
}

// ✅ Ahora traemos también el sueldo_hora de cada trabajador
$trabajadores = $pdo->query('SELECT id, nombre, sueldo_hora FROM trabajadores ORDER BY nombre')->fetchAll();

// ✅ También mostramos el nombre del trabajador en el listado
$rows = $pdo->query('SELECT h.*, t.nombre FROM horas_extras h JOIN trabajadores t ON t.id = h.trabajador_id ORDER BY h.fecha DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Horas Extras - Sistema de Pagos</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
  <header class="app-header"><h1>Horas Extras</h1></header>
  <main class="container">
    <form method="post" action="horas_extras.php" style="max-width:760px; margin-bottom:18px;">
      <div class="form-row">
        <!-- Selector de trabajador -->
        <select name="trabajador" id="trabajador" required>
          <option value="">— Seleccioná trabajador —</option>
          <?php foreach($trabajadores as $t): ?>
            <option 
              value="<?php echo $t['id']; ?>" 
              data-valor="<?php echo htmlspecialchars($t['sueldo_hora']); ?>">
              <?php echo htmlspecialchars($t['nombre']); ?>
            </option>
          <?php endforeach; ?>
        </select>

        <!-- Fecha -->
        <input name="fecha" type="date" value="<?php echo date('Y-m-d'); ?>" />

        <!-- Cantidad de horas -->
        <input name="horas" type="number" step="0.25" placeholder="Horas" required />

        <!-- 💰 Valor por hora mostrado como texto -->
        <div style="display:flex; flex-direction:column; justify-content:center;">
          <label style="font-size:0.9em; color:#555;">Valor por hora:</label>
          <p id="valor_texto" style="margin:0; font-weight:bold;">—</p>
        </div>

        <!-- Campo oculto para enviar el valor real -->
        <input type="hidden" name="valor" id="valor" value="">
      </div>

      <div style="height:8px"></div>
      <textarea name="observacion" placeholder="Observación (opcional)"></textarea>
      <div style="height:8px"></div>
      <button class="btn" type="submit">Registrar horas</button>
    </form>

    <?php if(count($rows)): ?>
      <table class="table">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Trabajador</th>
            <th>Horas</th>
            <th>Valor</th>
            <th>Total</th>
            <th>Obs</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($rows as $r): ?>
            <tr>
              <td><?php echo htmlspecialchars($r['fecha']); ?></td>
              <td><?php echo htmlspecialchars($r['nombre']); ?></td>
              <td><?php echo number_format($r['horas'],2,',','.'); ?></td>
              <td><?php echo number_format($r['valor_hora'],2,',','.'); ?></td>
              <td><?php echo number_format($r['total'],2,',','.'); ?></td>
              <td><?php echo htmlspecialchars($r['observacion']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p class="small-muted">No hay horas extras registradas.</p>
    <?php endif; ?>

    <p style="margin:18px 0;"><a href="../index.html" class="small-muted">← Volver</a></p>
  </main>

  <!-- Script que actualiza el valor por hora -->
  <script>
  document.getElementById('trabajador').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const valor = selected.getAttribute('data-valor');
    document.getElementById('valor').value = valor || '';
    const texto = document.getElementById('valor_texto');
    texto.textContent = valor ? `$${valor}` : '—';
    texto.style.color = valor ? '#2a7a2a' : '#555'; // verde si tiene valor, gris si no
  });
  </script>
</body>
</html>
