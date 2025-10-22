Sistema de Pagos — Prototipo v2 (con Faltas/Bonos/Reporte)

Instrucciones rápidas:
1. Colocá la carpeta `sistema_pagos_v2` dentro de tu carpeta pública (htdocs o www).
2. Importá `sql/schema.sql` en tu MySQL (phpMyAdmin o consola).
3. Editá `php/conexion.php` si tu usuario/clave de MySQL son distintos.
4. En el navegador abrí: http://localhost/sistema_pagos_v2/index.html
5. Usá las secciones Trabajadores, Adelantos, Horas Extras, Faltas/Retasos, Bonos y Reporte.

Notas:
- El reporte genera un PDF usando html2pdf.js en el navegador.
- Esta versión usa PDO y requiere PHP >=7.4.
