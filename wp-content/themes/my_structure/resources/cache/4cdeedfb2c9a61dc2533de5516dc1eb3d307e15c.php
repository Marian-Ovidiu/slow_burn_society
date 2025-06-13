<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="<?php echo $__env->yieldContent('meta_description', 'Slow Burn Society: accendini, filtri, tabacco e accessori da fumo.'); ?>">
  <title><?php echo $__env->yieldContent('title', 'Slow Burn Society'); ?></title>

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
  <?php echo $__env->yieldContent('head'); ?>
</head>
<body class="min-h-screen bg-[#fefcf7] text-gray-800 font-sans flex flex-col">
  <?php wp_head(); ?>

  <!-- Header -->
  <?php the_widget('Widget\MenuWidget', ['menu_name' => 'HeaderMenu']); ?>

  <!-- Main Content -->
  <main class="flex-1 container mx-auto">
      <?php echo $__env->yieldContent('content'); ?>
  </main>

  <!-- Footer -->
  <footer class="bg-gray-800 text-white text-sm mt-8">
      <?php the_widget('Widget\MenuWidget', ['menu_name' => 'FooterMenu']); ?>
  </footer>

  <?php echo $__env->yieldContent('scripts'); ?>
  <?php wp_footer(); ?>
</body>
</html>
<?php /**PATH C:\MAMP\htdocs\slow_burn_society\wp-content\themes\my_structure\resources\views/layouts/mainLayout.blade.php ENDPATH**/ ?>