<?php

namespace Hostinger\Reach\Integrations\Elementor;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Hostinger\Reach\Blocks\SubscriptionFormBlock;

class SubscriptionFormElementorWidget extends Widget_Base {

    public const FORM_ID_PREFIX = 'elementor-hostinger-reach-form-';
    public const WIDGET_NAME    = 'hostinger-reach';

    public function get_name(): string {
        return self::WIDGET_NAME;
    }


    public function get_title(): ?string {
        return __( 'Hostinger Reach', 'hostinger-reach' );
    }

    public function get_icon(): string {
        return 'eicon-envelope';
    }

    public function get_keywords(): array {
        return array(
            'form',
            'forms',
            'reach',
            'contact form',
            'hostinger',
            'email',
            'newsletter',
        );
    }

    public function get_categories(): array {
        return array(
            'basic',
        );
    }

    protected function register_controls(): void {

        $this->start_controls_section(
            'form',
            array(
                'label' => esc_html__( 'Form', 'hostinger-reach' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'formId',
            array(
                'label'      => esc_html__( 'Form ID', 'hostinger-reach' ),
                'type'       => Controls_Manager::TEXT,
                'input_type' => 'hidden',
                'default'    => self::FORM_ID_PREFIX . random_int( 1, PHP_INT_MAX ),
            )
        );

        $this->add_control(
            'showName',
            array(
                'label'        => esc_html__( 'Show Name', 'hostinger-reach' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'hostinger-reach' ),
                'label_off'    => esc_html__( 'No', 'hostinger-reach' ),
                'return_value' => 1,
                'default'      => 0,
            )
        );

        $this->add_control(
            'showSurname',
            array(
                'label'        => esc_html__( 'Show Surname', 'hostinger-reach' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'hostinger-reach' ),
                'label_off'    => esc_html__( 'No', 'hostinger-reach' ),
                'return_value' => 1,
                'default'      => 0,
            )
        );

        $this->end_controls_section();
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();
        SubscriptionFormBlock::render_block_html( $settings, ElementorIntegration::INTEGRATION_NAME );
    }

    protected function content_template(): void {
        ?>
        <div class="hostinger-reach-block-subscription-form-wrapper">
            <form id="{{{ settings.formId }}}" class="hostinger-reach-block-subscription-form">
                <input type="hidden" name="id" value="{{{ settings.formId }}}">
                <input type="hidden" name="metadata.plugin" value="elementor">

                <div class="hostinger-reach-block-form-field">
                    <label
                        for="{{{ settings.formId }}}-email"><?php esc_html_e( 'Email', 'hostinger-reach' ); ?>
                        <span class="required">*</span></label>
                    <input type="email" id="{{{ settings.formId }}}-email" name="email" required>
                </div>

                <# if ( settings.showName ) { #>
                    <div class="hostinger-reach-block-form-field">
                        <label
                            for="{{{ settings.formId }}}-name"><?php esc_html_e( 'Name', 'hostinger-reach' ); ?></label>
                        <input type="text" id="{{{ settings.formId }}}-name" name="name">
                    </div>
                <# } #>

                <# if ( settings.showSurname ) { #>
                    <div class="hostinger-reach-block-form-field">
                        <label
                            for="{{{ settings.formId }}}-surname"><?php esc_html_e( 'Surname', 'hostinger-reach' ); ?></label>
                        <input type="text" id="{{{ settings.formId }}}-surname" name="surname">
                    </div>
                <# } #>

                <button
                    type="submit"
                    class="hostinger-reach-block-submit wp-block-button__link has-dark-color has-color-1-background-color has-text-color has-background has-link-color has-medium-font-size wp-element-button">
                    <?php esc_html_e( 'Subscribe', 'hostinger-reach' ); ?>
                </button>

                <div class="reach-subscription-message" style="display: none;"></div>
            </form>
        </div>
        <?php
    }
}
