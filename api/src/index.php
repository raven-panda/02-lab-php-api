<?php
    include './_lib/functions.php';
    header('Access-Control-Allow-Origin: http://php-dev-2.online:3000');
    header('Content-Type: application/json');
    $RES = new MessageResponses();

    /**
     * Displays all available routes.
     */
    function getAllroutes($RES) {
        $list_routes = array();
        foreach ($GLOBALS['routes'] as $key => $value) {
            $list_routes[] = $key;
        }
        echo json_encode($list_routes);
    }
    /**
     * Displays categories with the GET method,
     * or add one with the POST method.
     */
    function categories($RES) {
        include './_lib/categories/categories.php';
    }
    /**
     * Checks if the category exists, display its name with the GET method,
     * update it with the PUT method and the appropriate JSON,
     * delete it with the DELETE method.
     */
    function categoryById($RES) {
        include './_lib/categories/category-by-id.php';
    }

    /**
     * Displays the available technologies under the chosen category
     */
    function technologies($RES, $category) {
        include './_lib/technologies/technologies.php';
    }
    /**
     * Technology by id
     */
    function technologyById($RES, $category) {
        include './_lib/technologies/technology-by-id.php';
    }

    /**
     * Checks if the requested route exists, and then execute the function associated.
     */
    function router($url, $method, $RES) {

        foreach($GLOBALS['routes'] as $pattern => $handler) {
            $fullUrl = $method . ':' . $url;
            $pattern = str_replace('{cat_id}', '([^/]+)', $pattern);
            $pattern = str_replace('{tech_id}', '([^/]+)', $pattern);
            $pattern = str_replace('/', '\/', $pattern);
            if(preg_match('/^' . $pattern . '$/', $fullUrl, $matches)) {
                array_shift($matches);
                call_user_func($handler, $RES, $matches);
                return;
            }
        }
        echo json_encode($RES->errorMessage(400), JSON_UNESCAPED_SLASHES);
        http_response_code(400);
    }

    /**
     * routes and functions trigger defining
     */
    $GLOBALS['routes'] = [
        'GET:/' => 'getAllRoutes',

        // Categories routes
        'GET:/categories' => 'categories',
        'POST:/categories' => 'categories',
        'GET:/categories/{cat_id}' => 'categoryById',
        'PUT:/categories/{cat_id}' => 'categoryById',
        'DELETE:/categories/{cat_id}' => 'categoryById',

        // Technologies routes
        'GET:/technologies' => 'technologies',
        'POST:/technologies' => 'technologies',
        'GET:/technologies/{tech_id}' => 'technologyById',
        'PUT:/technologies/{tech_id}' => 'technologyById',
        'DELETE:/technologies/{tech_id}' => 'technologyById'
    ];

    /**
     * Router execution
     */
    $request_url = $_SERVER['REQUEST_URI'];
    $method = $_SERVER['REQUEST_METHOD'];
    router($request_url, $method, $RES);
?>