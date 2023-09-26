<?php
    $request_method = $_SERVER['REQUEST_METHOD'];

    if (in_array($request_method, array("GET", "PUT", "DELETE"), true)) {
        
        $response = '';

        $paths = preg_split('~/(?=[^/]*$)~', htmlspecialchars($_SERVER['REQUEST_URI']));
        $tech_name = end($paths);

        try {

            $mysql_connection = databaseConnection();

            $sql_selectTech = 'SELECT `name`, categories, ressources, icon, icon_name FROM technologies WHERE `name` = :name';
            $sth = $mysql_connection->prepare($sql_selectTech);

            $sth->bindParam(':name', $tech_name, PDO::PARAM_STR);
    
            $sth->execute();

            $results = $sth->fetch(PDO::FETCH_ASSOC);

            if (!empty($results)) {
                $sanit = sanitizeObject(json_decode($results['ressources'], true));
                $results['ressources'] = $sanit;
                $response = $results;
                unset($response['icon']);

                if ($request_method === "PUT") {

                    $data = file_get_contents('php://input');
                    $response = '';
                    $_PUT = parse_raw_http_request($data);
        
                    if (isset($_PUT['name']) && isset($_PUT['ressources']) && isset($_PUT['icon']) && isset($_PUT['categories'])
                    && isset($_PUT['icon']['file_name']) && isset($_PUT['icon']["file_type"]) && isset($_PUT['icon']['file_data'])) {
        
                        $name = strtolower(htmlspecialchars($_PUT['name']));
                        $categories = sanitizeObject(json_decode($_PUT['categories']));
                        $json_categories = json_encode($categories, JSON_UNESCAPED_SLASHES);
        
                        $ressources = sanitizeObject(json_decode($_PUT['ressources'], true));
                        $json_ress = json_encode($ressources, JSON_UNESCAPED_SLASHES);
        
                        
                        $icon_data = $_PUT['icon']['file_data'];
                        $icon_name = htmlspecialchars($_PUT['icon']['file_name']);
        
                        try {
                            $sql_updateTech = 'UPDATE technologies SET `name` = :name, ressources = :ressources, categories = :category, icon = :icon, icon_name = :icon_name WHERE `name` = :old_name;';
                            $sth = $mysql_connection->prepare($sql_updateTech);
            
                            $sth->bindParam(':name', $name, PDO::PARAM_STR);
                            $sth->bindParam(':ressources', $json_ress, PDO::PARAM_STR);
                            $sth->bindParam(':category', $json_categories, PDO::PARAM_STR);
                            $sth->bindParam(':icon', $icon_data, PDO::PARAM_STR);
                            $sth->bindParam(':icon_name', $icon_name, PDO::PARAM_STR);
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
                            $response = e->getCode();
                        }
                        
                    } else {
                        $response = 'invalid-formdata';
                    }
        
                }

                if ($request_method === "DELETE") {
                    try {

                        $sql_deleteTech = 'DELETE FROM technologies WHERE `name` = :name';
                        $sth = $mysql_connection->prepare($sql_deleteTech);

                        $sth->bindParam(':name', $tech_name, PDO::PARAM_STR);

                        $sth->execute();

                        $row = $sth->rowCount();
    
                        if ($row && $row > 0) {
                            $response = 'success';
                        } else {
                            $response = 'no-changes';
                        }

                    } catch (Exception $err) {
                        error_log($err);
                        $response = 'server-error';
                    }
                }
                
            } else {
                $response = 'not-found';
            }

        } catch (Exception $err) {
            error_log($err);
            $response = $err->getMessage();
        }

        echo json_encode($response, JSON_UNESCAPED_SLASHES);

    } else {
        http_response_code(404);
    }
?>
