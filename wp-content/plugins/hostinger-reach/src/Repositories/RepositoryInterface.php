<?php

namespace Hostinger\Reach\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

interface RepositoryInterface {
    public function all( array $where = array() ): array;
    public function exists( string $id ): bool;
    public function get( string $id ): array;
    public function insert( array $fields ): bool;
    public function update( array $fields ): bool;
}
