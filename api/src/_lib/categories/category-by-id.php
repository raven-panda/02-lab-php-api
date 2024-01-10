<?php
    $request_method = $_SERVER['REQUEST_METHOD'];
    $response = '';
    $response_data = null;
    
    // Retrieving the name of the category in the URL
    $cat_name = $category[0];

    try {

        $mysql_connection = databaseConnection();

        if ($mysql_connection) {

            $sql_checkCategory = "SELECT id FROM categories WHERE name = :category";
            $sth = $mysql_connection->prepare($sql_checkCategory);
            $sth->bindParam(':category', $cat_name, PDO::PARAM_STR);
            $sth->execute();

            $cat_exists = $sth->fetch(PDO::FETCH_ASSOC);
            unset($sth);

            if (!empty($cat_exists)) {

                // Selecting the category with its attached technologies in the database
                $sql_selectCategories = "SELECT t.name, t.ressources, t.categories, t.icon
                                        FROM categories AS c
                                        JOIN cat_tech AS ct ON c.id = ct.cat_id
                                        JOIN technologies AS t ON ct.tech_id = t.id
                                        WHERE c.name = :category";
                
                $sth = $mysql_connection->prepare($sql_selectCategories);

                $sth->bindParam(':category', $cat_name, PDO::PARAM_STR); 

                $sth->execute();

                $results = $sth->fetchAll(PDO::FETCH_ASSOC);
         
                if ($request_method === "GET" && !empty($results)) $response_data = $results;

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
        
                                // Checking if it changed anything to send the appropriate response
                                http_response_code(200);
        
                            } catch (Exception $err) {
                                error_log($err);
                                http_response_code(500);
                            }

                        } else {
                            http_response_code(400);
                        }

                    } else {
                        http_response_code(400);
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
                        http_response_code(200);

                    } catch (Exception $err) {
                        error_log($err);
                        http_response_code(500);
                    }
                }

            } else {
                http_response_code(404);
            }

        } else {
            http_response_code(400);
        }

    } catch (Exception $err) {
        error_log($err);
        http_response_code(500);
    }

    $response = $RES->newResponse(http_response_code(), ['data' => $response_data]);
    echo json_encode($response);
?>