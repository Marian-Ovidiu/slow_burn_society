<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="@yield('meta_description', 'Slow Burn Society: accendini, filtri, tabacco e accessori da fumo.')">
  <title>@yield('title', 'Slow Burn Society')</title>

  <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;700&display=swap" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  
  <!-- Material Symbols -->
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" rel="stylesheet" />
  <style>
    .material-symbols-rounded {
      font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }
    
   [x-cloak] { display: none !important; }

  </style>
  @yield('head')
</head>
<body class="min-h-screen bg-[#fefcf7] text-gray-800 font-sans flex flex-col">
  <?php wp_head(); ?>

  <!-- Header -->
  @widget('HeaderMenu')

  <!-- Main Content -->
  <main class="flex-1 container mx-auto">
      @yield('content')
  </main>

  <!-- Footer -->
  <footer class="bg-gray-800 text-white text-sm mt-8">
      @widget('FooterMenu')
  </footer>

  @yield('scripts')
  <?php wp_footer(); ?>
</body>
</html>
