<?php
namespace Classes;

class IntentRepo {
    private static $inst;
    public static function instance(): self { return self::$inst ??= new self(); }
    private function key(string $piId): string { return 'sbs_pi_' . $piId; }

    public function save(string $piId, array $items): void {
        update_option($this->key($piId), wp_json_encode($items), false);
    }
    public function load(string $piId): ?array {
        $raw = get_option($this->key($piId), '');
        if (!$raw) return null;
        $arr = json_decode($raw, true);
        return is_array($arr) ? $arr : null;
    }
    public function delete(string $piId): void {
        delete_option($this->key($piId));
    }
}
