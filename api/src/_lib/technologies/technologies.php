<?php
    $request_method = $_SERVER['REQUEST_METHOD'];

    $response = '';

    if ($request_method === 'GET') {

        $mysql_connection = databaseConnection();

        if ($mysql_connection) {

            try {

                $sql = "SELECT name, categories, ressources, icon_name FROM technologies";
                $sth = $mysql_connection->prepare($sql);
    
                $sth->execute();
    
                $result = array();
    
                while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
                    $row['categories'] = json_decode($row['categories']);
                    $row['ressources'] = json_decode($row['ressources']);
                    $result[] = $row;
                }
    
                if ($result) {
                    $response = $result;
                } else {
                    $response = $RES->errorMessage(210);
                }
    
            } catch (Exception $err) {
                error_log($err);
                $response = 'server-error';
            }
            
        } else {
            $response = $RES->errorMessage(200);
        }

        echo json_encode($response, JSON_UNESCAPED_SLASHES);

    } else if ($request_method === "POST") {

        if (isset($_POST['name']) && !empty($_POST['name'])
            && isset($_POST['ressources']) && !empty($_POST['ressources'])
            && isset($_POST['categories']) && !empty($_POST['categories'])
            && isset($_FILES['icon']) && !empty($_FILES['icon'])

            && isset($_FILES['icon']['name']) && !empty($_FILES['icon']['name'])
            && isset($_FILES['icon']['tmp_name']) && !empty($_FILES['icon']['tmp_name'])
            && isset($_FILES['icon']['size']) && !empty($_FILES['icon']['size'])
            && isset($_FILES['icon']['type']) && !empty($_FILES['icon']['type'])) {

            $upload_max_size = 2 * 1024 * 1024;

            $name = htmlspecialchars($_POST['name']);
            $name = strtolower($name);
    
            $ressources = sanitizeObject(json_decode($_POST['ressources'], true));
            $json_ress = json_encode($ressources, JSON_UNESCAPED_SLASHES);
    
            $categories = sanitizeObject(json_decode($_POST['categories']));
            $json_categories = json_encode($categories, JSON_UNESCAPED_SLASHES);
    
            $icon = $_FILES['icon'];
            $icon_name = $icon['name'];
            $icon_tmp = $icon['tmp_name'];
            $icon_size = $icon['size'];
            $icon_type = $icon['type'];
    
            $icon_data = file_get_contents($icon_tmp);
    
            if (preg_match('/^[a-z0-9_-]+$/', $name)) {

                if ($icon_size < $upload_max_size) {

                    $mysql_connection = databaseConnection();

                    if ($mysql_connection) {
    
                        try {
    
                            $mysql_connection->beginTransaction();
        
                            $sql_insertTech = 'INSERT INTO technologies (name, categories, ressources, icon, icon_name) VALUES (:name, :category, :ressources, :icon, :icon_name);';
                            $sth = $mysql_connection->prepare($sql_insertTech);
                    
                            $sth->bindParam(':name', $name, PDO::PARAM_STR);
                            $sth->bindParam(':ressources', $json_ress, PDO::PARAM_STR);
                            $sth->bindParam(':icon', $icon_data, PDO::PARAM_STR);
                            $sth->bindParam(':icon_name', $icon_name, PDO::PARAM_STR);
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
                
                            $response = $RES->validMessage(1);
                
                        } catch (PDOException $err) {
                            error_log($err);
                            if (preg_match('/SQLSTATE\[23000\]\: Integrity constraint violation\: 1062/', $err->getMessage())) {
                                $response = $RES->errorMessage(202);
                            }
                            if (preg_match('/SQLSTATE\[23000\]\: Integrity constraint violation\: 1048/', $err->getMessage())) {
                                $response = $RES->errorMessage(203);
                            }
                        }
    
                    } else {
                        $response = $RES->errorMessage(200);
                    }

                } else {
                    $response = $RES->errorMessage(102);
                }
    
            } else {
                $response = $RES->errorMessage(101);
            }

        } else {
            $response = $RES->errorMessage(100);
        }

        echo json_encode($response);

    } else {
        http_response_code(404);
    }
?>
