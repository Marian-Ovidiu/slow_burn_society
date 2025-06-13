<?php

namespace Core;

class RouterProvider
{
    protected $file_routes = [
        'source/routes/web.php',
    ];

    public function register()
    {
        foreach ($this->file_routes as $helper) {
            $file = get_template_directory() . '/' . $helper;
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
}