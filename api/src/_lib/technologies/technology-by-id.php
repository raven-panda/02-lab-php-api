<?php
    include './_lib/functions.php';
    $request_method = $_SERVER['REQUEST_METHOD'];

    if (in_array($request_method, array("GET", "PUT", "DELETE"))) {
        
        $response = 'error';

        $paths = preg_split('~/(?=[^/]*$)~', htmlspecialchars($_SERVER['REQUEST_URI']));
        $tech_name = end($paths);

        try {

            $mysql_connection = databaseConnection();

            $sql_selectTech = 'SELECT `name`, category, ressources, icon, icon_name FROM technologies WHERE `name` = :name';
            $sth = $mysql_connection->prepare($sql_selectTech);

            $sth->bindParam(':name', $tech_name, PDO::PARAM_STR);
    
            $sth->execute();

            $results = $sth->fetch(PDO::FETCH_ASSOC);

            if (!empty($results)) {
                $sanit = sanitizeObject(json_decode($results['ressources'], true));
                $results['ressources'] = $sanit;
                $response = $results;
                unset($response['icon']);
                
            } else {
                $response = 'not-found';
            }

        } catch (Exception $err) {
            error_log($err);
            $response = 'server-error';
        }

        if ($request_method === "PUT") {

            $data = file_get_contents('php://input');
            $response = '';
            $_PUT = parse_raw_http_request($data);

            if (isset($_PUT['name']) && isset($_PUT['ressources']) && isset($_PUT['icon']) && isset($_PUT['category'])
            && isset($_PUT['icon']['file_name']) && isset($_PUT['icon']["file_type"]) && isset($_PUT['icon']['file_data'])) {

                // WIP
                
                $response = 'success';
                
            } else {
                $response = 'invalid-formdata';
            }

        }

        echo json_encode($response, JSON_UNESCAPED_SLASHES);

    } else {
        http_response_code(404);
    }
?>
