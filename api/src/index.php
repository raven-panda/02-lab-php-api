<?php

    /**
     * Displays all available roots.
     */
    function getAllroots() {
        $list_roots = [];
        foreach ($GLOBALS['roots'] as $key => $value) {
            $list_roots[] = $key;
        }
        echo json_encode($list_roots);
    }
    /**
     * Displays categories with the GET method,
     * or add one with the POST method.
     */
    function categories() {
        include './_lib/categories/categories.php';
    }
    /**
     * Checks if the category exists, display its name with the GET method,
     * update it with the PUT method and the appropriate JSON,
     * delete it with the DELETE method.
     */
    function categoryById() {
        include './_lib/categories/category-by-id.php';
    }
    /**
     * Checls if the requested root exists, and then execute the function associated.
     */
    function rooter($url, $method) {
        foreach($GLOBALS['roots'] as $pattern => $handler) {
            $fullUrl = $method . ':' . $url;
            $pattern = str_replace('{id}', '([^/]+$)', $pattern);
            $pattern = str_replace('/', '\/', $pattern);
            if(preg_match('/^' . $pattern . '$/', $fullUrl, $matches)) {
                array_shift($matches);
                call_user_func($handler, $matches);
                return;
            }
        }
        http_response_code(404);
    }

    /**
     * Roots and functions trigger defining
     */
    $GLOBALS['roots'] = [
        'GET:/' => 'getAllRoots',
        'GET:/categories' => 'categories',
        'POST:/categories' => 'categories',
        'GET:/categories/{id}' => 'categoryById',
        'PUT:/categories/{id}' => 'categoryById',
        'DELETE:/categories/{id}' => 'categoryById'
    ];

    /**
     * Rooter execution
     */
    $request_url = $_SERVER['REQUEST_URI'];
    $method = $_SERVER['REQUEST_METHOD'];
    rooter($request_url, $method);
?>