<?php
    class Response {
        private $code;
        private $success;
        private $message;

        public function __construct() {}

        public function setResponse($code, $success, $message, $type = null) {
            $this->code = $code;
            $this->success = $success;
            $this->message = $message;
            if ($type) {
                $this->message = $this->message . " (" . $type . ")";
            }
        }

        public function toArray() {
            return array(
                'code' => $this->code,
                'success' => $this->success,
                'message' => $this->message
            );
        }
    }
    /**
     * This is my validation and errors/warnings response messages manager.
     */
    class MessageResponses {
        /**
         * The error manager function
         * @param int Message code
         */
        function errorMessage($code) {
            switch ($code) {
                // Form invalidity errors/warnings
                case 100:
                    return array('100' => 'Syntax Error: Fields are missing or incorrect.');
                case 101:
                    return array('101' => 'Syntax Error: Special characters aren\'t allowed.');
                case 102:
                    return array('102' => 'File Error: You icon size exceeds the 2Mb allowed.');
                case 103:
                    return array('103' => 'Syntax Error: Ressources or categories is not JSON arrays.');
                case 104:
                    return array('104' => 'File Error: Failed to store the icon.');
                case 105:
                    return array('105' => 'File Error: Failed to store the new icon or the old one cannot be removed.');

                // PDO MySQL errors/warnings
                case 200:
                    return array("200" => 'Server Error: There was a problem during your request, please try again.');
                case 201:
                    return array("201" => 'Server Warning: No changes.');
                case 202:
                    return array("202" => 'Server Error: Already exists.');
                case 203:
                    return array("203" => 'Server Error: One of the categories doesn\'t exist or you wrote wrong categories.');
                case 210:
                    return array("210" => 'Server Error: Not found.');
                case 211:
                    return array("211" => 'Server Error: No entries.');
                        
                // API request errors/warnings
                case 400:
                    return array("400" => "Request Error: Path or method used may be incorrect. Please read the README of this API on how to use it there: https://github.com/raven-panda/02-lab-php-api.git.");
                default:
                    return null;
            }
        }
        /**
         * The validation messages manager function
         * @param int Message code
         */
        function validMessage($code) {
            switch ($code) {
                case 1:
                    return array("1" => 'Server: Added Successfully.');
                case 2:
                    return array("2" => 'Server: Edited Successfully.');
                case 3:
                    return array("3" => 'Server: Deleted Successfully.');
                default:
                    return null;
            }
        }
    }
    /**
     * Function for connection to the MySQL Database
     * @return object The PDO connection to the database
     */
    function databaseConnection() {
        
        try {    
            $dsn = 'mysql:host='. getenv('MYSQL_HOST') .';dbname='. getenv('MYSQL_DATABASE') .';charset=utf8';
            $mysql_connection = new PDO($dsn, getenv('MYSQL_USER'), getenv('MYSQL_PASSWORD'));
        } catch (Exception $err) {
            error_log($err->getMessage());
            $mysql_connection = false;
        }

        return $mysql_connection;
    }

    /**
     * Function for JSON objects sanitizing
     * @param array The input JSON decoded array
     * @return array The output PHP array
     */
    function sanitizeObject($object) {
        // Checking if the given parameter is an array
        if (is_array($object)) {

            // Sanitizing the array values
            foreach($object as $key => $value) {
                // Checking if the value is an array to sanitize
                if (is_array($value)) {
                    $value_sanitized = sanitizeObject($value);
                } else {
                    $value_sanitized = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                }

                // Defining the output array values with their original keys
                $object_sanitized[$key] = $value_sanitized;
            }

            return $object_sanitized;
        } else {
            return false;
        }
    }

    /**
     * This function parses a raw HTTP request, used for the PUT method that needs a form-data type of body for updating images.
     * 
     * @param string The raw HTTP request body.
     * @return array Key => Values pairs of the form-data.
     */
    function parse_raw_http_request($data) {

        // Spliting the request by the boundary
        $boundary = '/\-{28}\d{24}/';
        $raw_data = preg_split($boundary, $data);
        array_shift($raw_data);

        // Initializing the output arrays
        $input_values = array();
        $icon = array();

        foreach($raw_data as $str) {
            // Removing unwanted string
            $str_clean = str_replace('Content-Disposition: form-data;', '', $str);

            // Getting the field name value
            $value_regex = '/name\=\"[a-zA-Z]+\"/';
            preg_match($value_regex, $str_clean, $field_key);
            if (isset($field_key[0])) {
                preg_match('/name\=\"([^"]+)\"/', $field_key[0], $field_name);
                $field_name = $field_name[1];
            }

            // Removing unwanted string
            $str_clean = preg_replace($value_regex, '', $str_clean);

            // File parsing for an image
            $contenttype_regex = '/Content-Type: image\/[a-z]+/';

            if (preg_match($contenttype_regex, $str_clean, $content_type)) {
                
                // Getting the file name
                $filename_regex = '/\; filename\=\".+\.[a-zA-Z0-9]+\"/';
                preg_match($filename_regex, $str_clean, $filename);
                
                $filename = str_replace(';', '', $filename[0]);
                preg_match('/filename\=\"([^"]+)\"/', $filename, $filename_matches);
                $filename = $filename_matches[1];

                // Removing unwanted string
                $content_type = str_replace('Content-Type: image/', '', $content_type);
                $file_data = preg_replace($filename_regex, '', $str_clean);
                $file_data = preg_replace($contenttype_regex, '', $file_data);

                // Defining the icon array key => values
                $icon['file_name'] = $filename;
                $icon['file_type'] = $content_type[0];
                $icon['file_data'] = trim($file_data);
                $icon['file_size'] = strval(strlen($file_data));

                $str_clean = $icon;
            }

            // Removing unwanted whitespaces in the beginning and end of the string
            if (!is_array($str_clean)) $str_clean = trim($str_clean);

            // Pushing the value in the array with the field name as key
            if ($str_clean !== '--') {
                $input_values[$field_name] = $str_clean;
            }
        }

        return $input_values;
    }
?>