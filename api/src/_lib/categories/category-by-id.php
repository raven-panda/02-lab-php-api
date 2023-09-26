<?php
    $request_method = $_SERVER['REQUEST_METHOD'];
    
    if (in_array($request_method, array("GET", "PUT", "DELETE"), true)) {

        $response = '';

        $paths = preg_split('~/(?=[^/]*$)~', htmlspecialchars($_SERVER['REQUEST_URI']));
        $cat_name = end($paths);

        try {

            $mysql_connection = databaseConnection();

            if ($mysql_connection) {
    
                $sql_selectCategories = "SELECT c.name AS category, GROUP_CONCAT(t.name SEPARATOR ', ') AS technologies FROM categories AS c
                                        LEFT JOIN cat_tech AS ct ON c.id = ct.cat_id
                                        LEFT JOIN technologies AS t ON ct.tech_id = t.id
                                        GROUP BY c.name
                                        HAVING category = :category";
                $sth = $mysql_connection->prepare($sql_selectCategories);

                $sth->bindParam(':category', $cat_name, PDO::PARAM_STR);
        
                $sth->execute();

                $results = $sth->fetch(PDO::FETCH_ASSOC);

                if ($results['technologies']) {
                    $results['technologies'] = explode(', ', $results['technologies']);
                } else {
                    $results['technologies'] = array();
                }

                if (!empty($results)) {

                    $response = $results;

                    if ($request_method === 'PUT') {
                        $json_data = file_get_contents('php://input');
                        $_PUT = parse_raw_http_request($json_data);
        
                        if (isset($_PUT['name']) && !empty($_PUT['name'])) {

                            $new_name = htmlspecialchars($_PUT['name']);
        
                            try {
                                
                                $sql_updateCat = "UPDATE categories SET `name` = :new_name WHERE `name` = :old_name";
                                $sth = $mysql_connection->prepare($sql_updateCat);
                                
                                $sth->bindParam(':old_name', $cat_name, PDO::PARAM_STR);
                                $sth->bindParam(':new_name', $new_name, PDO::PARAM_STR);
                                
                                $sth->execute();
        
                                $row = $sth->rowCount();
        
                                if ($row && $row > 0) {
                                    $response = $RES->validMessage(2);
                                } else {
                                    $response = $RES->errorMessage(201);
                                }
        
                            } catch (Exception $err) {
                                error_log($err);
                                $response = $RES->errorMessage(200);
                            }
        
                        } else {
                            $response = $RES->errorMessage(100);
                        }
                    }
        
                    if ($request_method === 'DELETE') {
                        
                        try {

                            $sql_deleteCat = "DELETE FROM categories WHERE `name` = :name";
                            $sth = $mysql_connection->prepare($sql_deleteCat);
                            
                            $sth->bindParam(':name', $cat_name, PDO::PARAM_STR);
                            
                            $sth->execute();

                            $row = $sth->rowCount();
        
                            if ($row && $row > 0) {
                                $response = 'success';
                            } else {
                                $response = 'no-changes';
                            }

                        } catch (Exception $err) {
                            error_log($err);
                            $response = $RES->errorMessage(200);
                        }
                    }

                } else {
                    $response = $RES->errorMessage(210);
                }
            } else {
                $response = $RES->errorMessage(200);
            }

        } catch (Exception $err) {
            error_log($err);
            $response = $RES->errorMessage(200);
        }

        echo json_encode($response);

    } else {
        http_response_code(404);
    }
?>