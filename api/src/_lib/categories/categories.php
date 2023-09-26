<?php
    $request_method = $_SERVER['REQUEST_METHOD'];
    $response = '';

    if (in_array($request_method, array("GET", "POST"), true)) {

        if ($request_method === "GET") {

            $mysql_connection = databaseConnection();

            if ($mysql_connection) {

                try {

                    $sql_selectCategories = "SELECT c.name AS category, GROUP_CONCAT(t.name SEPARATOR ', ') AS technologies FROM categories AS c
                                             LEFT JOIN cat_tech AS ct ON c.id = ct.cat_id 
                                             LEFT JOIN technologies AS t ON ct.tech_id = t.id
                                             GROUP BY c.name";
                    $sth = $mysql_connection->prepare($sql_selectCategories);
            
                    $sth->execute();
    
                    $results = array();
    
                    $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
    
                    foreach ($rows as $row) {

                        if ($row['technologies']) {
                            $row['technologies'] = explode(', ', $row['technologies']);
                        } else {
                            $row['technologies'] = array();
                        }
                        $results[] = $row;

                    }
    
                    if (empty($results)) {
                        $results = $RES->errorMessage(210);
                    }
                    
                    $response = $results;
    
                } catch (Exception $err) {
                    error_log($err);
                    $response = $RES->errorMessage(200);
                }
                
            } else {
                $response = $RES->errorMessage(200);
            }

        } else if ($request_method === "POST") {

            if (isset($_POST['name']) && !empty($_POST['name'])) {

                $name = htmlspecialchars($_POST['name']);
                $name = strtolower($name);
    
                if (preg_match('/^[a-z0-9_-]+$/', $name)) {

                    $mysql_connection = databaseConnection();

                    if ($mysql_connection) {

                        try {

                            $sql_selectCategories = 'INSERT INTO categories (name) VALUES (:new_cat)';
                            $sth = $mysql_connection->prepare($sql_selectCategories);
                    
                            $sth->bindParam(':new_cat', $name, PDO::PARAM_STR);
                            $sth->execute();
                
                            $response = $RES->validMessage(1);
                
                        } catch (Exception $err) {
                            error_log($err);
                            if ($err->getCode() === "23000") {
                                $response = $RES->errorMessage(202);
                            }
                        }

                    } else {
                        $response = $RES->errorMessage(200);
                    }
    
                } else {
                    $response = $RES->errorMessage(101);
                }
    
            } else {
                $response = $RES->errorMessage(100);
            }
        }

        echo json_encode($response);

    } else {
        http_response_code(404);
    }
?>