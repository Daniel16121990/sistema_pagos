<?php
require_once __DIR__.'/conexion.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $nombre = $_POST['nombre'] ?? '';
    $cargo = $_POST['cargo'] ?? '';
    $sueldo = $_POST['sueldo'] ?? 0;
    $fecha = $_POST['fecha'] ?? null;
    $stmt = $pdo->prepare('INSERT INTO trabajadores (nombre,cargo,sueldo_base,fecha_ingreso) VALUES (?,?,?,?)');
    $stmt->execute([$nombre,$cargo,$sueldo,$fecha]);
    header('Location: listar.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Agregar trabajador</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
  <header class="app-header"><h1>Agregar trabajador</h1></header>
  <main class="container">
    <form method="post" action="agregar.php" style="max-width:520px">
      <div class="form-row"><input name="nombre" placeholder="Nombre completo" required /></div>
      <div class="form-row"><input name="cargo" placeholder="Cargo (ej. ayudante)" /></div>
      <div class="form-row"><input name="sueldo" placeholder="Sueldo quincenal (ej. 200000)" required type="number" step="0.01" /></div>
      <div class="form-row"><input name="fecha" placeholder="Fecha de ingreso (YYYY-MM-DD)" /></div>
      <div style="height:12px"></div>
      <button class="btn" type="submit">Guardar</button>
    </form>
    <p style="margin-top:18px;"><a href="listar.php" class="small-muted">← Volver</a></p>
  </main>
</body>
</html>
