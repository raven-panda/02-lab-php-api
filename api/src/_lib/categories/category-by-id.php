<?php
    include './_lib/functions.php';
    $request_method = $_SERVER['REQUEST_METHOD'];
    
    if (in_array($request_method, array("GET", "PUT", "DELETE"), true)) {

        $response = '';

        $paths = preg_split('~/(?=[^/]*$)~', htmlspecialchars($_SERVER['REQUEST_URI']));
        $cat_name = end($paths);

        try {

            $mysql_connection = databaseConnection();

            $sql_selectCategories = 'SELECT `name` FROM categories WHERE `name` = :name';
            $sth = $mysql_connection->prepare($sql_selectCategories);

            $sth->bindParam(':name', $cat_name, PDO::PARAM_STR);
    
            $sth->execute();

            $results = $sth->fetch(PDO::FETCH_ASSOC);

            if (!empty($results)) {

                $response = $results;

                if ($request_method === 'PUT') {
                    $json_data = file_get_contents('php://input');
                    $put_data = json_decode($json_data, true);
    
                    if ($put_data && json_last_error() === JSON_ERROR_NONE
                    && isset($put_data['name']) && !empty($put_data['name'])) {
    
                        try {

                            $new_name = htmlspecialchars($put_data['name']);
                            
                            $sql_updateCat = "UPDATE categories SET `name` = :new_name WHERE `name` = :old_name";
                            $sth = $mysql_connection->prepare($sql_updateCat);
                            
                            $sth->bindParam(':old_name', $cat_name, PDO::PARAM_STR);
                            $sth->bindParam(':new_name', $new_name, PDO::PARAM_STR);
                            
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
    
                    } else {
                        $response = 'invalid-json';
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
                        $response = 'server-error';
                    }
                }

            } else {
                $response = 'not-found';
                http_response_code(404);
            }

        } catch (Exception $err) {
            error_log($err);
            $response = 'server-error';
        }

        if (http_response_code() !== 404) {
            echo json_encode($response);
        }

    } else {
        http_response_code(404);
    }
?>