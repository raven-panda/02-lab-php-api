<?php
    $request_method = $_SERVER['REQUEST_METHOD'];

    $response = '';
    $response_data = null;

    // Retrieving the name of the technology in the URL
    $tech_name = $technology[0];

    try {

        $mysql_connection = databaseConnection();

        $sql_checkTech = "SELECT id FROM technologies WHERE name = :technology";
        $sth = $mysql_connection->prepare($sql_checkTech);
        $sth->bindParam(':technology', $tech_name, PDO::PARAM_STR);
        $sth->execute();

        $tech_exists = $sth->fetch(PDO::FETCH_ASSOC);
        unset($sth);

        if (!empty($tech_exists)) {

            // Selecting the technology in the database
            $sql_selectTech = 'SELECT `name`, categories, ressources, icon FROM technologies WHERE `name` = :name';
            $sth = $mysql_connection->prepare($sql_selectTech);

            $sth->bindParam(':name', $tech_name, PDO::PARAM_STR);

            $sth->execute();

            $results = $sth->fetch(PDO::FETCH_ASSOC);
            unset($sth);

            // Sanitizing the ressources and categories JSON in order to prevent multiple backslahes or returning a 'stringified' array.
            $results['ressources'] = sanitizeObject(json_decode($results['ressources'], true));
            $results['categories'] = sanitizeObject(json_decode($results['categories'], true));
            
            if ($request_method === "GET" && !empty($results)) $response_data = $results;

            // Edit technology script
            if ($request_method === "PUT") {

                // Parsing the PUT form-data request
                $data = file_get_contents('php://input');
                $_PUT = parse_raw_http_request($data);

                // Setting the max icon size
                $upload_max_size = trim(ini_get('upload_max_filesize'), 'M') * 1024 * 1024;

                if ($_SERVER['CONTENT_LENGTH'] < $upload_max_size) {

                    // Checking all the fields
                    if (isset($_PUT['name']) && isset($_PUT['ressources']) && isset($_PUT['icon']) && isset($_PUT['categories'])
                    && isset($_PUT['icon']['file_name']) && isset($_PUT['icon']["file_type"]) && isset($_PUT['icon']['file_data']) && isset($_PUT['icon']['file_size'])) {
                        
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

                                    try {

                                        $mysql_connection->beginTransaction();

                                        // Updating the technology with given values
                                        $sql_updateTech = 'UPDATE technologies SET `name` = :name, ressources = :ressources, categories = :category, icon = :icon WHERE `name` = :old_name;';
                                        $sth = $mysql_connection->prepare($sql_updateTech);
                        
                                        
                                        $sth->bindParam(':name', $name, PDO::PARAM_STR);
                                        $sth->bindParam(':ressources', $json_ress, PDO::PARAM_STR);
                                        $sth->bindParam(':category', $json_categories, PDO::PARAM_STR);
                                        $sth->bindParam(':icon', $icon_path, PDO::PARAM_STR);
                                        $sth->bindParam(':old_name', $tech_name, PDO::PARAM_STR);
                    
                                        $sth->execute();
                                        unset($sth);

                                        try {

                                            $sql_deloldfk = 'DELETE FROM cat_tech WHERE tech_id = (SELECT id FROM technologies WHERE `name` = :name)';
                                            $sth = $mysql_connection->prepare($sql_deloldfk);
                                            $sth->bindParam(':name', $name, PDO::PARAM_STR);
                                            $sth->execute();
                                            unset($sth);

                                            foreach ($categories as $category) {
                                                $sql_CatTech = 'INSERT INTO cat_tech (cat_id, tech_id)
                                                                VALUES ((SELECT id FROM categories WHERE `name` = :category),
                                                                        (SELECT id FROM technologies WHERE `name` = :name))';
                                                $sth = $mysql_connection->prepare($sql_CatTech);
                                                
                                                $sth->bindParam(':name', $name, PDO::PARAM_STR);
                                                $sth->bindParam(':category', $category, PDO::PARAM_STR);
                                                $test = $sth->execute();
                                                var_dump($test);
                                                unset($sth);
                                            }
                                            http_response_code(200);
        
                                        } catch (PDOException $err) {
                                            $mysql_connection->rollBack();
                                            switch ($err->getCode()) {
                                                case 23000:
                                                    http_response_code(400);
                                                    $response_type = "category not found";
                                                    break;
                                                default:
                                                    http_response_code(500);
                                                    break;
                                            }
                                        }
                                        
                                        $mysql_connection->commit();
                                        http_response_code(200);
                                            
                                    } catch (PDOException $err) {
                                        print_r($err->getMessage());
                                        $mysql_connection->rollBack();
                                        error_log($err);
                                        http_response_code(500);
                                    }

                                } else {
                                    http_response_code(400);
                                }

                            } else {
                                http_response_code(500);
                            }
                            
                        } else {
                            http_response_code(400);
                        }
                            
                    } else {
                        http_response_code(400);
                    }

                } else {
                    http_response_code(413);
                }

            }

            if ($request_method === "DELETE") {

                preg_match('/\/logos\/[a-z0-9_-]+\.[a-z0-9]+$/', $results['icon'], $icon_path);
                $icon_path = '.'. $icon_path[0];

                unlink($icon_path);

                try {

                    $mysql_connection->beginTransaction();

                    $sql_deleteTech = 'DELETE FROM technologies WHERE `name` = :name';
                    $sth = $mysql_connection->prepare($sql_deleteTech);

                    $sth->bindParam(':name', $tech_name, PDO::PARAM_STR);

                    $sth->execute();
                    unset($sth);

                    try {

                        $sql_deleteFk = 'DELETE FROM cat_tech WHERE tech_id = (SELECT id FROM technologies WHERE `name` = :name)';
                        $sth = $mysql_connection->prepare($sql_deleteFk);

                        $sth->bindParam(':name', $tech_name, PDO::PARAM_STR);
                        $sth->execute();
                        unset($sth);
                        
                    } catch (PDOException $err) {
                        error_log($err);
                        $mysql_connection->rollBack();
                        http_response_code(500);
                    }

                    http_response_code(200);

                    $mysql_connection->commit();

                } catch (Exception $err) {
                    error_log($err);
                    $mysql_connection->rollBack();
                    http_response_code(500);
                }
            }

            } else {
                http_response_code(404);
            }

    } catch (PDOException $err) {
        error_log($err);
        http_response_code(500);
    }

    $response = $RES->newResponse(http_response_code(), ['data' => $response_data]);
    echo json_encode($response, JSON_UNESCAPED_SLASHES);
?>
