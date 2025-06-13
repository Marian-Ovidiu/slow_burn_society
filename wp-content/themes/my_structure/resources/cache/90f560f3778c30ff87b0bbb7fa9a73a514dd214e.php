<?php
 /***
  *  @var $data[] Core\Bases\BaseGroupAcf\OpzioniProdottoFields
  */   
?>
<section class="px-4 md:px-8 lg:px-16 py-6">
    <?php if(isset($data)): ?>
        <h2 class="text-xl md:text-2xl font-bold mb-4">
            <?php echo $prodottoFields->titolo ?? 'Prodotti in evidenza 🔥'; ?>

        </h2>
    <?php endif; ?>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white rounded-xl shadow p-4 text-center cursor-pointer transition hover:shadow-md"
                @click="modalOpen = true; selected = <?php echo e(json_encode($product)); ?>">
                <img src="<?php echo e($product['image']); ?>" alt="<?php echo e($product['name']); ?>"
                    class="w-full h-32 md:h-40 lg:h-48 object-cover rounded" />
                <h3 class="mt-2 font-medium text-sm"><?php echo e($product['name']); ?></h3>
                <p class="text-green-600 font-semibold">€<?php echo e($product['price']); ?></p>
                
                <?php if(!empty($product['description'])): ?>
                    <p class="text-xs text-gray-500 mt-1">
                        <?php echo e(\Illuminate\Support\Str::limit($product['description'], 40, '...')); ?>

                    </p>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</section>
<?php /**PATH C:\MAMP\htdocs\slow_burn_society\wp-content\themes\my_structure\resources\views/components/cardProdottoEvidenza.blade.php ENDPATH**/ ?>