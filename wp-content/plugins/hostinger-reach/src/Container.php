<?php

namespace Hostinger\Reach;

use Psr\Container\ContainerInterface;
use Closure;
use ReflectionException;
use ReflectionClass;
use ReflectionMethod;
use Exception;

class Container implements ContainerInterface {
    protected array $entries   = array();
    protected array $instances = array();
    protected array $rules     = array();

    public function get( mixed $id ): mixed {
        if ( ! $this->has( $id ) ) {
            $this->set( $id );
        }

        if ( $this->entries[ $id ] instanceof Closure || is_callable( $this->entries[ $id ] ) ) {
            return $this->entries[ $id ]( $this );
        }

        if ( isset( $this->rules['shared'] ) && in_array( $id, $this->rules['shared'], true ) ) {
            return $this->singleton( $id );
        }

        return $this->resolve( $id );
    }

    public function has( mixed $id ): bool {
        return isset( $this->entries[ $id ] );
    }

    public function set( mixed $entry, mixed $concrete = null ): void {
        if ( is_null( $concrete ) ) {
            $concrete = $entry;
        }
        $this->entries[ $entry ] = $concrete;
    }

    public function resolve( mixed $alias ): mixed {
        $reflector   = $this->getReflector( $alias );
        $constructor = $reflector->getConstructor();
        if ( $reflector->isInterface() ) {
            return $this->resolveInterface( $reflector );
        }
        if ( ! $reflector->isInstantiable() ) {
            throw new Exception(
                esc_html( "Cannot inject {$reflector->getName()} because it cannot be instantiated" )
            );
        }
        if ( $constructor === null ) {
            return $reflector->newInstance();
        }
        $args = $this->getArguments( $alias, $constructor );

        return $reflector->newInstanceArgs( $args );
    }

    public function singleton( mixed $alias ): mixed {
        if ( ! isset( $this->instances[ $alias ] ) ) {
            $this->instances[ $alias ] = $this->resolve(
                $this->entries[ $alias ]
            );
        }

        return $this->instances[ $alias ];
    }

    public function getReflector( string $alias ): ReflectionClass {
        $class = $this->entries[ $alias ];
        try {
            return ( new ReflectionClass( $class ) );
        } catch ( ReflectionException $e ) {
            throw new Exception(
                esc_html( $e->getMessage() ),
                esc_html( $e->getCode() )
            );
        }
    }

    public function getArguments( mixed $alias, ReflectionMethod $constructor ): array {
        $args   = array();
        $params = $constructor->getParameters();

        foreach ( $params as $param ) {
            $type = $param->getType();

            if ( $type && ! $type->isBuiltin() ) {
                $class_name = $type->getName();
                $args[]     = $this->get( $class_name );
            } elseif ( $param->isDefaultValueAvailable() ) {
                $args[] = $param->getDefaultValue();
            } elseif ( isset( $this->rules[ $alias ][ $param->getName() ] ) ) {
                $args[] = $this->rules[ $alias ][ $param->getName() ];
            }
        }

        return $args;
    }

    public function resolveInterface( ReflectionClass $reflector ): mixed {
        if ( isset( $this->rules['substitute'][ $reflector->getName() ] ) ) {
            return $this->get(
                $this->rules['substitute'][ $reflector->getName() ]
            );
        }

        $classes = get_declared_classes();
        foreach ( $classes as $class ) {
            $rf = new ReflectionClass( $class );
            if ( $rf->implementsInterface( $reflector->getName() ) ) {
                return $this->get( $rf->getName() );
            }
        }
        throw new Exception(
            esc_html( "Class {$reflector->getName()} not found" ),
            1
        );
    }

    public function configure( array $config ): static {
        $this->rules = array_merge( $this->rules, $config );

        return $this;
    }
}
