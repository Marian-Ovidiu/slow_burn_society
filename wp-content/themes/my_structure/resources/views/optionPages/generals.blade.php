<?php
use Models\Options\OpzioniGlobaliFields;
do_action('acf/input/admin_head');

$options = OpzioniGlobaliFields::get();
?>
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <form method="post">
        <?php
        acf_form([
            'post_id' => 'options',
            'field_groups' => [$options->getGroupKey()],
            'submit_value' => __('Salva le impostazioni', 'acf'),
            'return' => false,
        ]);
        ?>
    </form>
</div>
<?php do_action('acf/input/admin_footer'); ?>
