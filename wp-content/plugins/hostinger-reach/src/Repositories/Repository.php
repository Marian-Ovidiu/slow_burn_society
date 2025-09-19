<?php
namespace Hostinger\Reach\Repositories;

use Hostinger\Reach\Admin\Database\DatabaseInterface;
use wpdb;

if ( ! DEFINED( 'ABSPATH' ) ) {
    exit;
}

abstract class Repository implements RepositoryInterface {
    protected DatabaseInterface $table;
    protected wpdb $db;

    public function __construct( wpdb $db ) {
        $this->db = $db;
    }

    abstract public function all( array $where = array() ): array;
    abstract public function exists( string $id ): bool;
    abstract public function get( string $id ): array;
    abstract public function insert( array $fields ): bool;
    abstract public function update( array $fields ): bool;
    protected function build_query( array $where = array(), int $limit = 100 ): string {
        $sql    = 'SELECT * FROM %i';
        $params = array( $this->table->table_name() );

        if ( ! empty( $where ) ) {
            $where_conditions = array();
            foreach ( $where as $column => $value ) {
                $where_conditions[] = $column . ' = %s';
                $params[]           = $value;
            }
            $sql .= ' WHERE ' . implode( ' AND ', $where_conditions );
        }

        $sql .= ' LIMIT ' . $limit;
        return $this->db->prepare( $sql, ...$params );
    }
}
