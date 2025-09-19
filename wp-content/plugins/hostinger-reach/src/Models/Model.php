<?php
namespace Hostinger\Reach\Models;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

interface Model {
    public function to_array(): array;
}
