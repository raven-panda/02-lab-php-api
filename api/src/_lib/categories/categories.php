<?php
    $request_method = $_SERVER['REQUEST_METHOD'];
    $response = '';

    // Checking if the request method is GET or POST
    if (in_array($request_method, array("GET", "POST"), true)) {

        // If the method is GET, list all the categories
        if ($request_method === "GET") {

            $mysql_connection = databaseConnection();

            if ($mysql_connection) {

                try {

                    // Selecting categories with their attached technologies in the database
                    $sql_selectCategories = "SELECT c.name AS category, GROUP_CONCAT(t.name SEPARATOR ', ') AS technologies FROM categories AS c
                                             LEFT JOIN cat_tech AS ct ON c.id = ct.cat_id 
                                             LEFT JOIN technologies AS t ON ct.tech_id = t.id
                                             GROUP BY c.name";
                    $sth = $mysql_connection->prepare($sql_selectCategories);
            
                    $sth->execute();
    
                    $results = array();
    
                    $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
    
                    foreach ($rows as $row) {

                        // Technologies key is exploded by the comma, so it will return an array of the technologies
                        if ($row['technologies']) {
                            $row['technologies'] = explode(', ', $row['technologies']);
                        } else {
                            $row['technologies'] = array();
                        }

                        $results[] = $row;

                    }
    
                    // If there's no results, return the error as response
                    if ($results) {
                        $response = $results;
                    } else {
                        $results = $RES->errorMessage(211);
                    }
    
                } catch (Exception $err) {
                    error_log($err);
                    $response = $RES->errorMessage(200);
                }
                
            } else {
                $response = $RES->errorMessage(200);
            }

        // If the method is PUT, trigger the 'add' script
        } else if ($request_method === "POST") {

            // Checking if fields are set and not empty
            if (isset($_POST['name']) && !empty($_POST['name'])) {

                $name = htmlspecialchars($_POST['name']);
                $name = strtolower($name);
    
                // Checking if the category name sent contains only alphanumeric characters, dash and underscore
                if (preg_match('/^[a-z0-9_-]+$/', $name)) {

                    $mysql_connection = databaseConnection();

                    if ($mysql_connection) {

                        try {

                            // Adding the category in the database
                            $sql_selectCategories = 'INSERT INTO categories (name) VALUES (:new_cat)';
                            $sth = $mysql_connection->prepare($sql_selectCategories);
                    
                            $sth->bindParam(':new_cat', $name, PDO::PARAM_STR);
                            $sth->execute();
                
                            $response = $RES->validMessage(1);
                
                        } catch (Exception $err) {
                            error_log($err);
                            // If the query failed, checks if the category already exists to return the right response, else returns the basic database connection error
                            if (preg_match('/SQLSTATE\[23000\]\: Integrity constraint violation\: 1062/', $err->getMessage())) {
                                $response = $RES->errorMessage(202);
                            } else {
                                $response = $RES->errorMessage(200);
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
        echo json_encode($RES->errorMessage(400));
    }
?>