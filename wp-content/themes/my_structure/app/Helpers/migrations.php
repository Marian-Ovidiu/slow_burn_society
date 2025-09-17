<?php

add_action('after_setup_theme', function () {
  global $wpdb; 
  require_once ABSPATH.'wp-admin/includes/upgrade.php';

  // -------- v2: sbs_payment_intents --------
  if (get_option('sbs_intents_schema_v') !== '2') {
    $table   = $wpdb->prefix.'sbs_payment_intents';
    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE {$table} (
      intent_id VARCHAR(64) NOT NULL,
      cart_token VARCHAR(36) NULL,
      status VARCHAR(24) NOT NULL DEFAULT 'pending_payment',
      items_json LONGTEXT NOT NULL,
      amount_subtotal INT NOT NULL DEFAULT 0,
      amount_shipping INT NOT NULL DEFAULT 0,
      amount_discount INT NOT NULL DEFAULT 0,
      amount_tax INT NOT NULL DEFAULT 0,
      amount_total INT NOT NULL DEFAULT 0,
      currency CHAR(3) NOT NULL DEFAULT 'EUR',
      email VARCHAR(190) NULL,
      user_id BIGINT UNSIGNED NULL,
      order_post_id BIGINT UNSIGNED NULL,
      client_ip VARCHAR(45) NULL,
      user_agent VARCHAR(255) NULL,
      referrer VARCHAR(512) NULL,
      utm_json TEXT NULL,
      expires_at DATETIME NULL,
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (intent_id),
      KEY cart_token (cart_token),
      KEY created_at (created_at),
      KEY status (status),
      KEY order_post_id (order_post_id)
    ) ENGINE=InnoDB {$charset};";
    dbDelta($sql);
    update_option('sbs_intents_schema_v','2');
  }

  // -------- v1: sbs_cart_events --------
  if (get_option('sbs_events_schema_v') !== '1') {
    $table   = $wpdb->prefix.'sbs_cart_events';
    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE {$table} (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      cart_token VARCHAR(36) NOT NULL,
      intent_id VARCHAR(64) NULL,
      type VARCHAR(32) NOT NULL,
      item_id VARCHAR(64) NULL,
      qty INT NULL,
      meta_json TEXT NULL,
      client_ip VARCHAR(45) NULL,
      user_agent VARCHAR(255) NULL,
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      KEY cart_token_created (cart_token, created_at),
      KEY type (type)
    ) ENGINE=InnoDB {$charset};";
    dbDelta($sql);
    update_option('sbs_events_schema_v','1');
  }

  // -------- v1: sbs_inventory (centralizzato qui) --------
  if (get_option('sbs_inventory_schema_v') !== '1') {
    $table   = $wpdb->prefix.'sbs_inventory';
    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE {$table} (
      product_id BIGINT(20) UNSIGNED NOT NULL,
      stock INT NOT NULL DEFAULT 0,
      updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (product_id)
    ) ENGINE=InnoDB {$charset};";
    dbDelta($sql);
    update_option('sbs_inventory_schema_v','1');
  }

  // -------- GC (pulizia) via WP-Cron --------
  if (!wp_next_scheduled('sbs_gc_job')) {
    wp_schedule_event(time()+300, 'hourly', 'sbs_gc_job');
  }
});

// Garbage collector
add_action('sbs_gc_job', function () {
  global $wpdb;
  $pi = $wpdb->prefix.'sbs_payment_intents';
  $ev = $wpdb->prefix.'sbs_cart_events';

  // pulisci intents “vecchi” non riusciti
  $wpdb->query("DELETE FROM {$pi}
    WHERE (status IN ('expired','abandoned','payment_failed') AND created_at < (NOW() - INTERVAL 14 DAY))
       OR (status IN ('pending_payment','draft') AND created_at < (NOW() - INTERVAL 7 DAY))");

  // pulisci eventi oltre 30gg
  $wpdb->query("DELETE FROM {$ev} WHERE created_at < (NOW() - INTERVAL 30 DAY)");
});
