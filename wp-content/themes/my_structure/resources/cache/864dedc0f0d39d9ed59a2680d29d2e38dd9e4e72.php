<?php
use Models\Options\OpzioniArchivioProgettoFields;
do_action('acf/input/admin_head');
?>
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <form method="post">
        <?php
        $options = OpzioniArchivioProgettoFields::get();
        acf_form([
            'post_id'    => 'options',
            'field_groups' => [$options->getGroupKey()],
            'submit_value' => __('Salva le impostazioni', 'acf'),
            'return' => false,
        ]);
        ?>
    </form>
</div>
<?php do_action('acf/input/admin_footer');?>
<?php /**PATH C:\MAMP\htdocs\slow_burn_society\wp-content\themes\my_structure\resources\views/optionPages/archivioOpzioniProgetto.blade.php ENDPATH**/ ?>