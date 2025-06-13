<?php
use KKomelin\TranslatableStringExporter\Core\Exporter;

require __DIR__ . '/../../vendor/autoload.php';

$data = config('laravel-translatable-string-exporter');
$langPath = $data['lang_path'];
$stringsPath = $data['strings_path'];
$stringFunctions = $data['string_functions'];
$exporter = new Exporter($langPath);
$exporter->export($stringsPath, $stringFunctions);
echo "Traduzioni esportate correttamente!†";