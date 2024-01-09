<?php
    $request_method = $_SERVER['REQUEST_METHOD'];

    $response = '';
    $response_data = null;

    // If the method is POST, list all the technologies
    if ($request_method === 'GET') {

        $mysql_connection = databaseConnection();

        if ($mysql_connection) {

            try {

                // Getting technologies in the database
                $sql = "SELECT name, categories, ressources, icon FROM technologies";
                $sth = $mysql_connection->prepare($sql);
    
                $sth->execute();
    
                $result = array();
    
                while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
                    $row['categories'] = json_decode($row['categories']);
                    $row['ressources'] = json_decode($row['ressources']);
                    $result[] = $row;
                }
    
                http_response_code(200);
                $response_data = $result;
    
            } catch (Exception $err) {
                error_log($err);
                http_response_code(500);
            }
            
        } else {
            http_response_code(500);
        }

        $response = $RES->newResponse(http_response_code(), ['data' => $response_data]);

    // If the method is POST, trigger the 'add' script
    } else if ($request_method === "POST") {

        // Setting the max icon size
        $upload_max_size = trim(ini_get('upload_max_filesize'), 'M') * 1024 * 1024;

        print_r($_SERVER['CONTENT_LENGTH']);
        print_r(' '. $upload_max_size);

        if ($_SERVER['CONTENT_LENGTH'] < $upload_max_size) {

            if (isset($_POST['name']) && !empty($_POST['name'])
                // Checking if all the required fields are set and non empty
                && isset($_POST['ressources']) && !empty($_POST['ressources'])
                && isset($_POST['categories']) && !empty($_POST['categories'])
                && isset($_FILES['icon']) && !empty($_FILES['icon'])
                // Checking if all the fields in the icon file are set and non empty
                && isset($_FILES['icon']['name']) && !empty($_FILES['icon']['name'])
                && isset($_FILES['icon']['tmp_name']) && !empty($_FILES['icon']['tmp_name'])
                && isset($_FILES['icon']['size']) && !empty($_FILES['icon']['size'])
                && isset($_FILES['icon']['type']) && !empty($_FILES['icon']['type'])) {

                // Sanitizing the fields
                $name = strtolower(htmlspecialchars($_POST['name']));
        
                $ressources = sanitizeObject(json_decode($_POST['ressources'], true));
                $json_ress = json_encode($ressources, JSON_UNESCAPED_SLASHES);
        
                $categories = sanitizeObject(json_decode($_POST['categories']));
                $json_categories = json_encode($categories, JSON_UNESCAPED_SLASHES);

                if ($categories && $ressources) {

                    // Putting icon fields in variables
                    $icon = $_FILES['icon'];
                    $icon_name = htmlspecialchars($icon['name']);
                    $icon_name = preg_replace('/\s/', '_', $icon_name);
                    $icon_tmp = htmlspecialchars($icon['tmp_name']);
                    $icon_size = htmlspecialchars($icon['size']);
                    $icon_type = htmlspecialchars($icon['type']);
                    
                    $icon_data = file_get_contents($icon_tmp);
                    $icon_path = 'http://php-dev-2.online/logos/'. $icon_name;

                    if (file_put_contents('./logos/'. $icon_name, $icon_data)) {

                        // Checking if the technology name sent contains only alphanumeric characters, dash and underscore
                        if (preg_match('/^[a-z0-9_-]+$/', $name)) {
                                
                            try {
                                
                                $mysql_connection = databaseConnection();
                                // Inserting technology in the database, and the foreign keys IDs associated.
                                // Using transaction here, so if one of the two query has an error, the queries are canceled.
                                $mysql_connection->beginTransaction();
            
                                $sql_insertTech = 'INSERT INTO technologies (name, categories, ressources, icon) VALUES (:name, :category, :ressources, :icon);';
                                $sth = $mysql_connection->prepare($sql_insertTech);
                        
                                $sth->bindParam(':name', $name, PDO::PARAM_STR);
                                $sth->bindParam(':ressources', $json_ress, PDO::PARAM_STR);
                                $sth->bindParam(':icon', $icon_path, PDO::PARAM_STR);
                                $sth->bindParam(':category', $json_categories, PDO::PARAM_STR);
                                
                                $sth->execute();
                                
                                foreach ($categories as $category) {
                                    $sql_CatTech = 'INSERT INTO cat_tech (cat_id, tech_id) VALUES ((SELECT id FROM categories WHERE `name` = :category), (SELECT id FROM technologies WHERE `name` = :name))';
                                    $sth = $mysql_connection->prepare($sql_CatTech);
                                    
                                    $sth->bindParam(':name', $name, PDO::PARAM_STR);
                                    $sth->bindParam(':category', $category, PDO::PARAM_STR);
                                    
                                    $sth->execute();
                                }
                                
                                $mysql_connection->commit();
                                http_response_code(200);
                                
                            } catch (PDOException $err) {
                                error_log($err);
                                switch ($err->getCode()) {
                                    case 23000:
                                        http_response_code(409);
                                        break;
                                    default:
                                        http_response_code(500);
                                        break;
                                }
                            }
                        
                        } else {
                            http_response_code(500);
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

        $response = $RES->newResponse(http_response_code(), ['data' => $response_data]);

    }

    echo json_encode($response);
?>
