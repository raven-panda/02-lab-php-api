<?php
    include './_lib/functions.php';
    $request_method = $_SERVER['REQUEST_METHOD'];

    if (in_array($request_method, array("GET", "POST"), true)) {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {

            try {

                $mysql_connection = databaseConnection();

                $sql_selectCategories = 'SELECT `name` FROM categories';
                $sth = $mysql_connection->prepare($sql_selectCategories);
        
                $sth->execute();

                $results = [];

                while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
                    $results[] = $row;
                }
                if (empty($results)) {
                    $results = false;
                }
        
                $response = $results;

            } catch (Exception $err) {
                error_log($err);
                $response = 'server-error';
            }

        } else if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['name']) && !empty($_POST['name'])) {

            $name = htmlspecialchars($_POST['name']);
            $name = strtolower($name);

            if (preg_match('/^[a-z0-9_-]+$/', $name)) {

                try {

                    $mysql_connection = databaseConnection();
        
                    $sql_selectCategories = 'INSERT INTO categories (name) VALUES (:new_cat)';
                    $sth = $mysql_connection->prepare($sql_selectCategories);
            
                    $sth->bindParam(':new_cat', $name, PDO::PARAM_STR);
                    $sth->execute();
        
                    $response = 'category-added';
        
                } catch (Exception $err) {
                    error_log($err);
                    if ($err->getCode() === "23000") {
                        $response = 'already-exists';
                    }
                }

            } else {
                $response = 'special-chars-not-allowed';
            }

        }

        echo json_encode($response);

    } else {
        http_response_code(404);
    }
?>