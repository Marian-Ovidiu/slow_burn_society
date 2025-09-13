<?php

add_action('after_setup_theme', function () {
  if (get_option('sbs_intents_schema_v') === '1') return;
  global $wpdb; require_once ABSPATH.'wp-admin/includes/upgrade.php';
  $table = $wpdb->prefix.'sbs_payment_intents';
  $charset = $wpdb->get_charset_collate();
  $sql = "CREATE TABLE {$table} (
    intent_id VARCHAR(64) NOT NULL,
    items_json LONGTEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (intent_id)
  ) ENGINE=InnoDB {$charset};";
  dbDelta($sql);
  update_option('sbs_intents_schema_v','1');
});

