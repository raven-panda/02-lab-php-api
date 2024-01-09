<?php
    $request_method = $_SERVER['REQUEST_METHOD'];
    
    $response = '';
    $response_data = null;

    // Retrieving the name of the technology in the URL
    $paths = preg_split('~/(?=[^/]*$)~', htmlspecialchars($_SERVER['REQUEST_URI']));
    $tech_name = end($paths);

    try {

        $mysql_connection = databaseConnection();

        // Selecting the technology in the database
        $sql_selectTech = 'SELECT `name`, categories, ressources, icon FROM technologies WHERE `name` = :name';
        $sth = $mysql_connection->prepare($sql_selectTech);

        $sth->bindParam(':name', $tech_name, PDO::PARAM_STR);

        $sth->execute();

        $results = $sth->fetch(PDO::FETCH_ASSOC);

        if (!empty($results)) {
            // Sanitizing the ressources and categories JSON in order to prevent multiple backslahes or returning a 'stringified' array.
            $results['ressources'] = sanitizeObject(json_decode($results['ressources'], true));
            $results['categories'] = sanitizeObject(json_decode($results['categories'], true));
            
            $response = $results;

            // Edit technology script
            if ($request_method === "PUT") {

                // Parsing the PUT form-data request
                $data = file_get_contents('php://input');
                $_PUT = parse_raw_http_request($data);
    
                // Checking all the fields
                if (isset($_PUT['name']) && isset($_PUT['ressources']) && isset($_PUT['icon']) && isset($_PUT['categories'])
                && isset($_PUT['icon']['file_name']) && isset($_PUT['icon']["file_type"]) && isset($_PUT['icon']['file_data']) && isset($_PUT['icon']['file_size'])) {
    
                    // Setting the icon max size allowed (2Mb)
                    $upload_max_size = 2 * 1024 * 1024;
                    
                    // Sanitizing fields
                    $name = strtolower(htmlspecialchars($_PUT['name']));
                    $categories = sanitizeObject(json_decode($_PUT['categories']));
                    $json_categories = json_encode($categories, JSON_UNESCAPED_SLASHES);
    
                    $ressources = sanitizeObject(json_decode($_PUT['ressources'], true));
                    $json_ress = json_encode($ressources, JSON_UNESCAPED_SLASHES);

                    if ($categories && $ressources) {
                    
                        $icon_data = $_PUT['icon']['file_data'];
                        $icon_name = strtolower(htmlspecialchars($_PUT['icon']['file_name']));
                        $icon_name = preg_replace('/\s/', '_', $icon_name);
                        $icon_size = htmlspecialchars($_PUT['icon']['file_size']);

                        $icon_path = 'http://php-dev-2.online/logos/'. $icon_name;
                        preg_match('/\/logos\/[a-z0-9_-]+\.[a-z0-9]+$/', $results['icon'], $icon_old_path);
                        $icon_old_path = '.'. $icon_old_path[0];

                        if (file_exists($icon_old_path)) {
                            unlink($icon_old_path);
                        }

                        if (file_put_contents('./logos/'. $icon_name, $icon_data)) {

                            // Checking if the technology name sent contains only alphanumeric characters, dash and underscore
                            if (preg_match('/^[a-z0-9_-]+$/', $name)) {

                                if ($icon_size < $upload_max_size) {
                
                                    try {

                                        // Updating the technology with given values
                                        $sql_updateTech = 'UPDATE technologies SET `name` = :name, ressources = :ressources, categories = :category, icon = :icon WHERE `name` = :old_name;';
                                        $sth = $mysql_connection->prepare($sql_updateTech);
                        
                                        $sth->bindParam(':name', $name, PDO::PARAM_STR);
                                        $sth->bindParam(':ressources', $json_ress, PDO::PARAM_STR);
                                        $sth->bindParam(':category', $json_categories, PDO::PARAM_STR);
                                        $sth->bindParam(':icon', $icon_path, PDO::PARAM_STR);
                                        $sth->bindParam(':old_name', $tech_name, PDO::PARAM_STR);
                    
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
                                    $response = $RES->errorMessage(102);
                                }

                            } else {
                                $response = $RES->errorMessage(101);
                            }

                        } else {
                            $response = $RES->errorMessage(105);
                        }
                        
                    } else {
                        $response = $RES->errorMessage(103);
                    }
                        
                } else {
                    $response = $RES->errorMessage(100);
                }
    
            }

            if ($request_method === "DELETE") {

                preg_match('/\/logos\/[a-z0-9_-]+\.[a-z0-9]+$/', $results['icon'], $icon_path);
                $icon_path = '.'. $icon_path[0];

                unlink($icon_path);

                try {

                    $sql_deleteTech = 'DELETE FROM technologies WHERE `name` = :name';
                    $sth = $mysql_connection->prepare($sql_deleteTech);

                    $sth->bindParam(':name', $tech_name, PDO::PARAM_STR);

                    $sth->execute();

                    $row = $sth->rowCount();

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

    } catch (Exception $err) {
        error_log($err);
        $response = $RES->errorMessage(200);
    }

    echo json_encode($response, JSON_UNESCAPED_SLASHES);
?>
