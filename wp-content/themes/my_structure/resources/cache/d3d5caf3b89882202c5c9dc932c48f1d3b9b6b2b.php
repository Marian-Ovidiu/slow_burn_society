     <?php
         /***
          *  @var $dataHero[] Core\Bases\BaseGroupAcf\OpzioniGlobaliFields
          */
     ?>
     <!-- HERO -->
     <section class="relative w-full h-48 md:h-64 lg:h-80">
         
         <img src="<?php echo e($dataHero->immagine_hero['url']); ?>"
             class="w-full h-full object-cover" alt="Banner accendini">

         <!-- Overlay -->
         <div class="absolute inset-0 bg-black/40"></div>
         <!-- Testo -->
         <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-4">

             <?php if($dataHero->titolo): ?>
                 <h1 class="text-2xl md:text-4xl font-bold drop-shadow-lg"> <?php echo $dataHero->titolo; ?> </h1>
             <?php endif; ?>

             <?php if($dataHero): ?>
                 <p class="mt-2 text-sm md:text-base drop-shadow"> <?php echo $dataHero->sottotitolo; ?></p>
             <?php endif; ?>
             <?php
                 $ctaUrl = $dataHero->cta['url'] ?? '/shop';
                 $ctaText = $dataHero->cta['title'] ?? 'Scopri lo shop';
             ?>

             <a href="<?php echo e($ctaUrl); ?>"
                 class="mt-4 bg-[#45752c] hover:bg-[#386322] text-white font-semibold px-6 py-2 rounded shadow transition">
                 <?php echo e($ctaText); ?>

             </a>
         </div>
     </section>
<?php /**PATH C:\MAMP\htdocs\slow_burn_society\wp-content\themes\my_structure\resources\views/components/heroSection.blade.php ENDPATH**/ ?>