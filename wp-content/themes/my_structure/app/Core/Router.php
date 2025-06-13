<?php

namespace Core;

class Router extends Singleton
{
    protected $routes = [];
    protected $ajaxRoutes = [];

    public function __construct()
    {
        parent::__construct();
        $this->init();
    }

    protected function init()
    {
        add_action('template_redirect', [$this, 'handleRoute']);
        add_action('wp_ajax_nopriv_', [$this, 'handleAjax']);
        add_action('wp_ajax_', [$this, 'handleAjax']);
    }

    public function get($uri, $action)
    {
        $this->addRoute('GET', $uri, $action);
    }

    public function post($uri, $action)
    {
        $this->addRoute('POST', $uri, $action);
    }

    public function request($uri, $action)
    {
        $this->addRoute('GET', $uri, $action);
        $this->addRoute('POST', $uri, $action);
    }

    public function ajax($action, $callback)
    {
        $this->ajaxRoutes[$action] = $callback;
    }

    protected function addRoute($method, $uri, $action)
    {
        $this->routes[] = compact('method', 'uri', 'action');
    }

    public function handleRoute()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        foreach ($this->routes as $route) {
            if ($this->match($route['uri']) && $this->checkMethod($route['method'], $requestMethod)) {
                global $wp_query;
                $wp_query->is_404 = false;
                status_header(200);
                $this->callAction($route['action']);
                exit;
            }
        }
    }

    public function handleAjax()
    {
        $action = $_REQUEST['action'] ?? '';
        if (isset($this->ajaxRoutes[$action])) {
            call_user_func($this->ajaxRoutes[$action]);
            wp_die();
        }
    }

    protected function match($uri)
    {
        global $wp;
        return trim($uri, '/') === trim($wp->request, '/');
    }

    protected function checkMethod($routeMethod, $requestMethod)
    {
        return strtoupper($routeMethod) === strtoupper($requestMethod);
    }

    protected function callAction($action)
    {
        if (is_callable($action) && !is_array($action)) {
            call_user_func($action);
        } else {
            list($controller, $method) = $action;

            $reflection = new \ReflectionMethod($controller, $method);

            if ($reflection->isStatic()) {
                call_user_func_array([$controller, $method], []);
            } else {
                $controllerInstance = new $controller();
                call_user_func_array([$controllerInstance, $method], []);
            }
        }
    }
}
