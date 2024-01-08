<?php
    include './_lib/functions.php';
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

        // Trims a potential enclosing slash, the router will not recognize the endpoint if there's one
        $url = rtrim($url, '/');

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
        http_response_code(404);
    }

    /**
     * routes and functions trigger defining
     */
    $GLOBALS['routes'] = [
        'GET:/api' => 'getAllRoutes',

        // Categories routes
        'GET:/api/categories' => 'categories',
        'POST:/api/categories' => 'categories',
        'GET:/api/categories/{cat_id}' => 'categoryById',
        'PUT:/api/categories/{cat_id}' => 'categoryById',
        'DELETE:/api/categories/{cat_id}' => 'categoryById',

        // Technologies routes
        'GET:/api/technologies' => 'technologies',
        'POST:/api/technologies' => 'technologies',
        'GET:/api/technologies/{tech_id}' => 'technologyById',
        'PUT:/api/technologies/{tech_id}' => 'technologyById',
        'DELETE:/api/technologies/{tech_id}' => 'technologyById'
    ];

    /**
     * Router execution
     */
    $request_url = $_SERVER['REQUEST_URI'];
    $method = $_SERVER['REQUEST_METHOD'];
    router($request_url, $method, $RES);
?>