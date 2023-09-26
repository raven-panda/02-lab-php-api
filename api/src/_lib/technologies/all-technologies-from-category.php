<?php
    include './_lib/functions.php';
    $request_method = $_SERVER['REQUEST_METHOD'];

    $response = '';
    if ($request_method === 'GET') {

        try {
            
            $mysql_connection = databaseConnection();

            $sql = "SELECT name, category, ressources, icon_name FROM technologies";
            $sth = $mysql_connection->prepare($sql);

            $sth->execute();

            $result = [];

            while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
                $result[] = $row;
            }

            if ($result) {
                $response = $result;
            } else {
                $response = 'not-found';
            }

        } catch (Exception $err) {
            error_log($err);
            $response = 'server-error';
        }

        echo json_encode($response, JSON_UNESCAPED_SLASHES);

    } else if ($request_method === "POST"
        && isset($_POST['name']) && !empty($_POST['name'])
        && isset($_POST['ressources']) && !empty($_POST['ressources'])
        && isset($_POST['category']) && !empty($_POST['category'])
        && isset($_FILES['icon']) && !empty($_FILES['icon'])) {

        $name = htmlspecialchars($_POST['name']);
        $name = strtolower($name);

        $ressources = sanitizeObject(json_decode($_POST['ressources'], true));

        $json_ress = json_encode($ressources, JSON_UNESCAPED_SLASHES);

        $category = htmlspecialchars($_POST['category']);

        $icon = $_FILES['icon'];
        $icon_name = $icon['name'];
        $icon_tmp = $icon['tmp_name'];
        $icon_size = $icon['size'];
        $icon_type = $icon['type'];

        $icon_data = file_get_contents($icon_tmp);

        if (preg_match('/^[a-z0-9_-]+$/', $name)) {

            try {

                $mysql_connection = databaseConnection();
    
                $sql_selectCategories = 'INSERT INTO technologies (name, category, ressources, icon, icon_name) VALUES (:name, :category, :ressources, :icon, :icon_name)';
                $sth = $mysql_connection->prepare($sql_selectCategories);
        
                $sth->bindParam(':name', $name, PDO::PARAM_STR);
                $sth->bindParam(':category', $category, PDO::PARAM_STR);
                $sth->bindParam(':ressources', $json_ress, PDO::PARAM_STR);
                $sth->bindParam(':icon', $icon_data, PDO::PARAM_STR);
                $sth->bindParam(':icon_name', $icon_name, PDO::PARAM_STR);

                $sth->execute();
    
                $response = 'tech-added';
    
            } catch (Exception $err) {
                error_log($err);
                if ($err->getCode() === "23000") {
                    $response = 'already-exists';
                }
            }

        } else {
            $response = 'special-chars-not-allowed';
        }

        echo json_encode($response);

    } else {
        http_response_code(404);
    }
?>
