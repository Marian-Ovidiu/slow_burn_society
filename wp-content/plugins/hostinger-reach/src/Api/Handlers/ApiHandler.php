<?php
namespace Hostinger\Reach\Api\Handlers;

use Hostinger\Reach\Functions;
use WP_Error;
use WP_Http;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class ApiHandler {
    private array $default_headers = array();
    protected string $api_base_name;
    private Functions $functions;

    public function __construct( Functions $functions ) {
        $this->functions = $functions;
    }

    public function get_functions(): Functions {
        return $this->functions;
    }

    public function get_api_basename(): string {
        return $this->api_base_name;
    }


    public function get_default_headers(): array {
        return $this->default_headers;
    }

    public function get( string $endpoint, array $params = array(), array $headers = array(), int $timeout = 120 ): WP_Error|array {
        $url          = $this->get_api_basename() . $endpoint;
        $request_args = array(
            'method'  => 'GET',
            'headers' => array_merge( $this->get_default_headers(), $headers ),
            'timeout' => $timeout,
        );

        if ( ! empty( $params ) ) {
            $url = add_query_arg( $params, $url );
        }

        $response = wp_remote_get( $url, $request_args );

        return $response;
    }

    public function post( string $endpoint, array $params = array(), array $headers = array(), int $timeout = 120 ): mixed {
        $url          = $this->get_api_basename() . $endpoint;
        $request_args = array(
            'method'  => 'POST',
            'timeout' => $timeout,
            'headers' => array_merge( $this->get_default_headers(), $headers ),
            'body'    => $params,
        );

        return wp_remote_post( $url, $request_args );
    }

    public function handle_wp_error( WP_Error $error ): WP_REST_Response {
        $response = new WP_REST_Response();
        $response->set_data( array( 'errors' => $error->get_error_code() ) );
        $response->set_status( WP_Http::BAD_REQUEST );
        return $response;
    }

    public function handle_response( array $response ): WP_REST_Response {
        $wp_response = new WP_REST_Response();
        $wp_response->set_status( wp_remote_retrieve_response_code( $response ) );
        $body = wp_remote_retrieve_body( $response );
        $wp_response->set_data( json_decode( $body ? $body : '', true ) );
        return $wp_response;
    }
}
