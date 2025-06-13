<?php
    use Models\Options\OpzioniGlobaliFields;
    $dataHero = OpzioniGlobaliFields::get();
?>

<header x-data="{ open: false }" class="bg-[#21231E] text-white shadow-lg px-4">
  <div class="flex justify-between items-center max-w-7xl mx-auto">

    <!-- Logo simbolico -->
    <div class="flex items-center gap-3" style="max-height: 100px">
      <img src="<?php echo e($dataHero->logo['url']); ?>" class="text-3xl" alt="">
      <span class="text-lg font-bold uppercase">Slow Burn Society</span>
    </div>

    <!-- Mobile Menu Button -->
    <button @click="open = !open" class="lg:hidden focus:outline-none">
      <span x-show="!open" class="material-symbols-rounded text-3xl">menu</span>
      <span x-show="open" class="material-symbols-rounded text-3xl">close</span>
    </button>

    <!-- Desktop Menu -->
    <?php if(!empty($menu)): ?>
      <nav class="hidden lg:flex gap-6">
        <?php $__currentLoopData = $menu; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <a href="<?php echo e($item->url); ?>" class="hover:text-yellow-200 font-semibold"><?php echo e($item->title); ?></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </nav>
    <?php endif; ?>

  </div>

  <!-- Mobile Menu -->
  <?php if(!empty($menu)): ?>
    <nav x-show="open" x-transition class="lg:hidden mt-4">
      <div class="flex flex-col gap-3 px-4 pb-4">
        <?php $__currentLoopData = $menu; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <a href="<?php echo e($item->url); ?>" class="text-white font-medium hover:underline"><?php echo e($item->title); ?></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
    </nav>
  <?php endif; ?>
</header>
<?php /**PATH C:\MAMP\htdocs\slow_burn_society\wp-content\themes\my_structure\resources\views/partials/header-menu.blade.php ENDPATH**/ ?>