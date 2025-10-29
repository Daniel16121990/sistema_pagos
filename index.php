
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sistema de Pagos — Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      font-family: 'Inter', sans-serif;
    }
    /* Personalización de colores de Tailwind para un tema oscuro */
    :root {
      --color-primary: #4f46e5; /* Indigo-600 */
      --color-background: #f3f4f6; /* Gray-100 */
      --color-text: #111827; /* Gray-900 */
    }

    @media (prefers-color-scheme: dark) {
      :root {
        --color-background: #1f2937;
        --color-text: #f9fafb;
      }
    }
    /* Estilo para el mensaje de error */
    .error-message {
        color: #ef4444; /* red-500 */
        font-size: 0.875rem; /* text-sm */
        text-align: center;
    }
  </style>
</head>

<body class="min-h-screen bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100 transition-colors duration-300 flex items-center justify-center">

  <div class="w-full max-w-md">
    <header class="app-header mb-8 text-center">
      <h1 class="text-3xl font-extrabold text-indigo-600 dark:text-indigo-400">Sistema de Pagos</h1>
      <p class="text-xl font-medium text-gray-600 dark:text-gray-300 mt-2">Acceso al Sistema</p>
    </header>

    <div class="bg-white dark:bg-gray-700 rounded-xl shadow-2xl p-8 space-y-6 border border-gray-200 dark:border-gray-600">
      <h2 class="text-2xl font-bold text-center text-gray-900 dark:text-gray-100">Iniciar Sesión</h2>
      
      <form action="validar.php" method="POST" class="space-y-4">
        <div>
          <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Usuario</label>
          <input type="text" id="username" name="username" required placeholder="admin" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
        </div>
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Contraseña</label>
          <input type="password" id="password" name="password" required placeholder="12345" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
        </div>
        
        <?php 
          if (isset($_GET['error']) && $_GET['error'] == 1) {
            echo '<p class="text-red-500 text-sm text-center">Usuario o contraseña incorrectos.</p>';
          }
        ?>
        
        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
          Acceder
        </button>
      </form>
    </div>

    <footer class="text-center mt-8 text-sm text-gray-500 dark:text-gray-400">
      &copy; <span id="year"><?php echo date('Y'); ?></span> obrasDNL.
    </footer>
  </div>
  
  <script>
    // Si bien ahora puedes usar PHP para el año, mantenemos el JS por si se quita el PHP.
    // document.getElementById('year').textContent = new Date().getFullYear(); 
  </script>
</body>
</html>