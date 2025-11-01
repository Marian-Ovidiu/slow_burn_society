<?php

namespace Hostinger\Reach\Admin\Database;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

interface DatabaseInterface {
    public function create(): void;
    public function table_name(): string;
    public function schema(): array;
    public function execute_db_delta( string $sql ): void;
}
