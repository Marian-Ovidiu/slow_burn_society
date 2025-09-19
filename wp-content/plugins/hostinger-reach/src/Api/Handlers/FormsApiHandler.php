<?php

namespace Hostinger\Reach\Api\Handlers;

use Hostinger\Reach\Functions;
use Hostinger\Reach\Repositories\ContactListRepository;
use Hostinger\Reach\Repositories\FormRepository;
use WP_REST_Request;
use WP_REST_Response;
use Exception;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class FormsApiHandler extends ApiHandler {

    private ContactListRepository $contact_list_repository;
    private FormRepository $form_repository;

    public function __construct( Functions $functions, ContactListRepository $contact_list_repository, FormRepository $form_repository ) {
        parent::__construct( $functions );
        $this->contact_list_repository = $contact_list_repository;
        $this->form_repository         = $form_repository;
    }

    public function get_contact_lists_handler(): WP_REST_Response {
        $contact_lists = $this->contact_list_repository->all();

        return $this->handle_response(
            array(
                'response' => array(
                    'code' => 200,
                ),
                'body'     => wp_json_encode( $contact_lists ),
            )
        );
    }

    public function get_forms_handler( WP_REST_Request $request ): WP_REST_Response {
        $contact_list_id = $request->get_param( 'contact_list_id' );
        $type            = $request->get_param( 'type' );
        $args            = array( 'type' => $type );
        if ( $contact_list_id ) {
            $args['contact_list_id'] = $contact_list_id;
        }
        $forms = apply_filters( 'hostinger_reach_forms', array(), $args );

        return $this->handle_response(
            array(
                'response' => array(
                    'code' => 200,
                ),
                'body'     => wp_json_encode( $forms ),
            )
        );
    }

    public function post_forms_handler( WP_REST_Request $request ): WP_REST_Response {
        $form_id   = $request->get_param( 'form_id' );
        $is_active = (int) $request->get_param( 'is_active' );

        try {
            $is_updated = apply_filters(
                'hostinger_reach_after_form_state_is_set',
                $this->form_repository->update(
                    array(
                        'form_id'   => $form_id,
                        'is_active' => $is_active,
                    )
                ),
                $form_id,
                $is_active
            );
        } catch ( Exception $e ) {
            $message = $e->getMessage();

            return $this->handle_response(
                array(
                    'body'     => wp_json_encode( array( 'error' => sanitize_text_field( $message ) ) ),
                    'response' => array(
                        'code' => 400,
                    ),
                )
            );
        }

        return $this->handle_response(
            array(
                'response' => array(
                    'code' => $is_updated ? 201 : 400,
                ),
            )
        );
    }
}
