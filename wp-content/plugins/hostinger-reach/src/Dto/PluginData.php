<?php

namespace Hostinger\Reach\Dto;

use Hostinger\Reach\Functions;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class PluginData {

    /**
     * Unique integration/plugin identifier (slug).
     *
     * @var string
     */
    private string $id;

    /**
     * Plugin folder name.
     *
     * @var string
     */
    private string $folder;

    /**
     * Main plugin file name.
     *
     * @var string
     */
    private string $file;

    /**
     * Admin URL to the plugin settings or dashboard.
     *
     * @var string
     */
    private string $admin_url;

    /**
     * URL to add or create a new form within the plugin.
     *
     * @var string
     */
    private string $add_form_url;

    /**
     * URL to edit an existing form within the plugin.
     *
     * @var string
     */
    private string $edit_url;

    /**
     * General plugin page URL.
     *
     * @var string
     */
    private string $url;

    /**
     * URL to download or install the plugin.
     *
     * @var string
     */
    private string $download_url;

    /**
     * Human-readable plugin title.
     *
     * @var string
     */
    private string $title;

    /**
     * Absolute URL to the plugin icon.
     *
     * @var string
     */
    private string $icon;

    /**
     * Whether the "view form" action should be hidden.
     *
     * @var bool
     */
    private bool $is_view_form_hidden;

    /**
     * Whether the "edit form" action should be hidden.
     *
     * @var bool
     */
    private bool $is_edit_form_hidden;

    /**
     * Whether the UI can toggle between forms.
     *
     * @var bool
     */
    private bool $can_toggle_forms;

    public function __construct(
        string $id,
        string $title,
        string $folder = null,
        string $file = null,
        string $admin_url = '',
        string $add_form_url = '',
        string $edit_url = '',
        string $url = '',
        string $download_url = '',
        string $icon = null,
        bool $is_view_form_hidden = true,
        bool $is_edit_form_hidden = false,
        bool $can_toggle_forms = true
    ) {
        $this->id                  = $id;
        $this->title               = $title;
        $this->folder              = $folder ?? $this->id;
        $this->file                = $file ?? $this->id . '.php';
        $this->admin_url           = $admin_url;
        $this->add_form_url        = $add_form_url;
        $this->edit_url            = $edit_url;
        $this->url                 = $url;
        $this->download_url        = $download_url;
        $this->icon                = $icon ?? Functions::get_frontend_url() . 'icons/' . $this->id . '.svg';
        $this->is_view_form_hidden = $is_view_form_hidden;
        $this->is_edit_form_hidden = $is_edit_form_hidden;
        $this->can_toggle_forms    = $can_toggle_forms;
    }

    public static function from_array( array $data = array() ): PluginData {
        $id                  = $data['id'] ?? '';
        $title               = $data['title'] ?? '';
        $folder              = $data['folder'] ?? $id;
        $file                = $data['file'] ?? $id . '.php';
        $admin_url           = $data['admin_url'] ?? '';
        $add_form_url        = $data['add_form_url'] ?? '';
        $edit_url            = $data['edit_url'] ?? '';
        $url                 = $data['url'] ?? '';
        $download_url        = $data['download_url'] ?? '';
        $icon                = $data['icon'] ?? Functions::get_frontend_url() . 'icons/' . $id . '.svg';
        $is_view_form_hidden = $data['is_view_form_hidden'] ?? true;
        $is_edit_form_hidden = $data['is_edit_form_hidden'] ?? false;
        $can_toggle_forms    = $data['can_toggle_forms'] ?? true;

        return new self(
            $id,
            $title,
            $folder,
            $file,
            $admin_url,
            $add_form_url,
            $edit_url,
            $url,
            $download_url,
            $icon,
            $is_view_form_hidden,
            $is_edit_form_hidden,
            $can_toggle_forms
        );
    }


    public function to_array(): array {
        return array(
            'id'                  => $this->id,
            'folder'              => $this->folder,
            'file'                => $this->file,
            'admin_url'           => $this->admin_url,
            'add_form_url'        => $this->add_form_url,
            'edit_url'            => $this->edit_url,
            'url'                 => $this->url,
            'download_url'        => $this->download_url,
            'title'               => $this->title,
            'icon'                => $this->icon,
            'is_view_form_hidden' => $this->is_view_form_hidden,
            'is_edit_form_hidden' => $this->is_edit_form_hidden,
            'can_toggle_forms'    => $this->can_toggle_forms,

        );
    }
}
