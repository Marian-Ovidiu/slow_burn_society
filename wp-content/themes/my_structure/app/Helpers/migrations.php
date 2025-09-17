<?php
/**
 * SBS – Schema install (first install, no ALTER)
 * - Opzionale: definire SBS_SCHEMA_HARD_RESET true per droppare e ricreare
 *   define('SBS_SCHEMA_HARD_RESET', true);
 */

add_action('after_setup_theme', function () {
  global $wpdb;
  require_once ABSPATH.'wp-admin/includes/upgrade.php';

  // ---- CONFIG
  // Bump unico per tutto lo schema (cambia se modifichi le colonne)
  $SCHEMA_VERSION = '2025-09-17.1';
  $optKey = 'sbs_schema_version';

  // ---- HARD RESET (opzionale: attiva con define in wp-config.php o tema)
  $hardReset = defined('SBS_SCHEMA_HARD_RESET') && SBS_SCHEMA_HARD_RESET === true;

  $pi = $wpdb->prefix.'sbs_payment_intents';
  $ev = $wpdb->prefix.'sbs_cart_events';
  $iv = $wpdb->prefix.'sbs_inventory';

  if ($hardReset) {
    // Droppa con IF EXISTS per essere idempotenti
    $wpdb->query("DROP TABLE IF EXISTS {$pi}");
    $wpdb->query("DROP TABLE IF EXISTS {$ev}");
    $wpdb->query("DROP TABLE IF EXISTS {$iv}");
    // Ripulisci vecchi flag di versione per evitare ALTAR strani di dbDelta
    delete_option('sbs_intents_schema_v');
    delete_option('sbs_events_schema_v');
    delete_option('sbs_inventory_schema_v');
    delete_option($optKey);
  }

  // Se lo schema è già a posto e non c’è hard reset, esci
  if (!$hardReset && get_option($optKey) === $SCHEMA_VERSION) {
    return;
  }

  $charset = $wpdb->get_charset_collate();

  // ====== sbs_payment_intents (PRIMARY KEY = intent_id) ======
  // NIENTE colonna auto_increment, niente id extra -> evitiamo ALTER indesiderati
  $sqlIntents = "CREATE TABLE {$pi} (
    intent_id        VARCHAR(64)  NOT NULL,
    cart_token       VARCHAR(36)  NULL,

    status           VARCHAR(24)  NOT NULL DEFAULT 'pending_payment',
    shipping_status  VARCHAR(20)  NULL,

    items_json       LONGTEXT     NOT NULL,

    amount_subtotal  INT          NOT NULL DEFAULT 0,
    amount_shipping  INT          NOT NULL DEFAULT 0,
    amount_discount  INT          NOT NULL DEFAULT 0,
    amount_tax       INT          NOT NULL DEFAULT 0,
    amount_total     INT          NOT NULL DEFAULT 0,
    currency         CHAR(3)      NOT NULL DEFAULT 'EUR',

    email            VARCHAR(190) NULL,
    first_name       VARCHAR(100) NULL,
    last_name        VARCHAR(100) NULL,
    shipping_json    TEXT         NULL,

    tracking_number  VARCHAR(100) NULL,
    tracking_url     VARCHAR(512) NULL,

    user_id          BIGINT UNSIGNED NULL,
    order_post_id    BIGINT UNSIGNED NULL,

    client_ip        VARCHAR(45)  NULL,
    user_agent       VARCHAR(255) NULL,
    referrer         VARCHAR(512) NULL,
    utm_json         TEXT         NULL,

    expires_at       DATETIME     NULL,
    created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY  (intent_id),
    KEY cart_token       (cart_token),
    KEY email            (email),
    KEY status           (status),
    KEY shipping_status  (shipping_status),
    KEY created_at       (created_at),
    KEY order_post_id    (order_post_id),
    KEY status_created   (status, created_at)
  ) ENGINE=InnoDB {$charset};";

  // ====== sbs_cart_events ======
  $sqlEvents = "CREATE TABLE {$ev} (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    cart_token  VARCHAR(36)     NOT NULL,
    intent_id   VARCHAR(64)     NULL,
    type        VARCHAR(32)     NOT NULL,
    item_id     VARCHAR(64)     NULL,
    qty         INT             NULL,
    meta_json   TEXT            NULL,
    client_ip   VARCHAR(45)     NULL,
    user_agent  VARCHAR(255)    NULL,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY cart_token_created (cart_token, created_at),
    KEY intent_id          (intent_id),
    KEY type               (type)
  ) ENGINE=InnoDB {$charset};";

  // ====== sbs_inventory ======
  $sqlInventory = "CREATE TABLE {$iv} (
    product_id BIGINT(20) UNSIGNED NOT NULL,
    stock      INT                 NOT NULL DEFAULT 0,
    updated_at DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (product_id)
  ) ENGINE=InnoDB {$charset};";

  // Crea effettivamente le tabelle
  dbDelta($sqlIntents);
  dbDelta($sqlEvents);
  dbDelta($sqlInventory);

  // Salva unica versione schema
  update_option($optKey, $SCHEMA_VERSION);

  // (Facoltativo) ripulisci i vecchi flag legacy se presenti
  delete_option('sbs_intents_schema_v');
  delete_option('sbs_events_schema_v');
  delete_option('sbs_inventory_schema_v');

  // Schedula GC
  if (!wp_next_scheduled('sbs_gc_job')) {
    wp_schedule_event(time() + 300, 'hourly', 'sbs_gc_job');
  }
});

// Garbage collector (soft)
add_action('sbs_gc_job', function () {
  global $wpdb;
  $pi = $wpdb->prefix.'sbs_payment_intents';
  $ev = $wpdb->prefix.'sbs_cart_events';

  // Pulisci intents “vecchi” non riusciti
  $wpdb->query("DELETE FROM {$pi}
    WHERE (status IN ('expired','abandoned','payment_failed','failed') AND created_at < (NOW() - INTERVAL 14 DAY))
       OR (status IN ('pending_payment','draft','processing') AND created_at < (NOW() - INTERVAL 7 DAY))");

  // Pulisci eventi oltre 30gg
  $wpdb->query("DELETE FROM {$ev} WHERE created_at < (NOW() - INTERVAL 30 DAY)");
});
