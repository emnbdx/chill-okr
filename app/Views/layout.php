<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
    }
  </script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <title>OKR Manager</title>
</head>

<body class="bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
  <div class="max-w-6xl mx-auto p-8">
    <?php require $viewPath; ?>
  </div>

  <script>
    function toggleDarkMode() {
      const html = document.documentElement;
      const isDark = html.classList.contains('dark');

      if (isDark) {
        html.classList.remove('dark');
        localStorage.setItem('darkMode', 'false');
      } else {
        html.classList.add('dark');
        localStorage.setItem('darkMode', 'true');
      }
    }

    if (localStorage.getItem('darkMode') === 'true') {
      document.documentElement.classList.add('dark');
    }
  </script>
</body>

</html>