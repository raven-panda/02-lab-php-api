<?php
    $request_method = $_SERVER['REQUEST_METHOD'];

    //=-=-=-=-Script triggered by any of the method checked-=-=-=-=//
    $response = '';

    // Retrieving the name of the category in the URL
    $paths = preg_split('~/(?=[^/]*$)~', htmlspecialchars($_SERVER['REQUEST_URI']));
    $cat_name = end($paths);

    try {

        $mysql_connection = databaseConnection();

        if ($mysql_connection) {
            
            // Selecting the category with its attached technologies in the database
            $sql_selectCategories = "SELECT c.name AS category, GROUP_CONCAT(t.name SEPARATOR ', ') AS technologies FROM categories AS c
                                    LEFT JOIN cat_tech AS ct ON c.id = ct.cat_id
                                    LEFT JOIN technologies AS t ON ct.tech_id = t.id
                                    GROUP BY c.name
                                    HAVING category = :category";
            $sth = $mysql_connection->prepare($sql_selectCategories);

            $sth->bindParam(':category', $cat_name, PDO::PARAM_STR);
    
            $sth->execute();

            $results = $sth->fetch(PDO::FETCH_ASSOC);

            if (!empty($results)) {

                // Technologies key is exploded by the comma, so it will return an array of the technologies
                if ($results['technologies']) {
                    $results['technologies'] = explode(', ', $results['technologies']);
                } else {
                    $results['technologies'] = array();
                }

                $response = $results;

                // If the method is PUT, trigger the 'edit' script
                if ($request_method === 'PUT') {

                    // Retrieving and parsing the form-data
                    $json_data = file_get_contents('php://input');
                    $_PUT = parse_raw_http_request($json_data);
    
                    if (isset($_PUT['name']) && !empty($_PUT['name'])) {

                        $new_name = strtolower(htmlspecialchars($_PUT['name']));

                        // Checking if the category name sent contains only alphanumeric characters, dash and underscore
                        if (preg_match('/^[a-z0-9_-]+$/', $new_name)) {
    
                            try {
                                
                                // Updating the category in the database
                                $sql_updateCat = "UPDATE categories SET `name` = :new_name WHERE `name` = :old_name";
                                $sth = $mysql_connection->prepare($sql_updateCat);
                                
                                $sth->bindParam(':old_name', $cat_name, PDO::PARAM_STR);
                                $sth->bindParam(':new_name', $new_name, PDO::PARAM_STR);
                                
                                $sth->execute();
        
                                $row = $sth->rowCount();
        
                                // Checking if it changed anything to send the appropriate response
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
                            $response = $RES->errorMessage(101);
                        }
    
                    } else {
                        $response = $RES->errorMessage(100);
                    }
                }
    
                // If the method is DELETE
                if ($request_method === 'DELETE') {
                    
                    try {

                        // Deleting the category from the database
                        $sql_deleteCat = "DELETE FROM categories WHERE `name` = :name";
                        $sth = $mysql_connection->prepare($sql_deleteCat);
                        
                        $sth->bindParam(':name', $cat_name, PDO::PARAM_STR);
                        
                        $sth->execute();

                        $row = $sth->rowCount();
    
                        // Checking if it changed anything to send the appropriate response
                        if ($row && $row > 0) {
                            $response = $RES->validMessage(3);
                        } else {
                            $response = $RES->errorMessage(201);
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
?>