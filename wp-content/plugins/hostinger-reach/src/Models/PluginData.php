<?php

namespace Hostinger\Reach\Models;

use Hostinger\Reach\Functions;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class PluginData implements Model {

    private string $id;
    private string $folder;
    private string $file;
    private string $admin_url;
    private string $add_form_url;
    private string $edit_url;
    private string $url;
    private string $download_url;
    private string $title;
    private string $icon;
    private bool $is_view_form_hidden;
    private bool $is_edit_form_hidden;
    private bool $can_toggle_forms;

    public function __construct( array $data = array() ) {
        $this->id           = $data['id'] ?? '';
        $this->title        = $data['title'] ?? '';
        $this->folder       = $data['folder'] ?? $this->id;
        $this->file         = $data['file'] ?? $this->id . '.php';
        $this->admin_url    = $data['admin_url'] ?? '';
        $this->add_form_url = $data['add_form_url'] ?? '';
        $this->edit_url     = $data['edit_url'] ?? '';
        $this->url          = $data['url'] ?? '';
        $this->download_url = $data['download_url'] ?? '';
        $this->icon         = $data['icon'] ?? Functions::get_frontend_url() . 'icons/' . $this->id . '.svg';

        $this->is_view_form_hidden = $data['is_view_form_hidden'] ?? true;
        $this->is_edit_form_hidden = $data['is_edit_form_hidden'] ?? false;
        $this->can_toggle_forms    = $data['can_toggle_forms'] ?? true;
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
