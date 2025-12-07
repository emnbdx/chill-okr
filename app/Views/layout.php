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
  <button onclick="toggleDarkMode()"
    class="fixed top-6 right-6 z-50 w-14 h-14 flex items-center justify-center text-2xl rounded-full bg-white dark:bg-slate-800 shadow-lg hover:shadow-xl border border-slate-200 dark:border-slate-700 transition-all duration-200 hover:scale-110"
    aria-label="Toggle dark mode">
    <span class="dark:hidden">🌙</span>
    <span class="hidden dark:inline">☀️</span>
  </button>

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