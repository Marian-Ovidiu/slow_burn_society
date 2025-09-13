<?php

namespace Classes;

class IntentRepo {
  private static $i; private $db; private $table;
  private function __construct(){ global $wpdb; $this->db=$wpdb; $this->table=$wpdb->prefix.'sbs_payment_intents'; }
  public static function instance(): self { return self::$i ?? (self::$i=new self()); }
  public function save(string $intentId, array $items): void {
    $this->db->replace($this->table, [
      'intent_id'=>$intentId,
      'items_json'=> wp_json_encode($items, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
      'created_at'=> current_time('mysql')
    ], ['%s','%s','%s']);
  }
  public function load(string $intentId): ?array {
    $row = $this->db->get_row($this->db->prepare("SELECT items_json FROM {$this->table} WHERE intent_id=%s", $intentId));
    if(!$row) return null;
    $arr = json_decode($row->items_json, true);
    return is_array($arr) ? $arr : null;
  }
  public function delete(string $intentId): void {
    $this->db->delete($this->table, ['intent_id'=>$intentId], ['%s']);
  }
}
