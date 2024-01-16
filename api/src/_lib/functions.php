<?php
    class Response {
        private int $code;
        private bool $success;
        private string $message = "";
        private array $data;

        public function __construct() {}

        public function setResponse(int $code, bool $success, array $options = null) {
            $this->code = $code;
            $this->success = $success;
            if (isset($options['message'])) $this->message = $options['message'];
            if (isset($options['type'])) $this->message = $this->message . " (" . $options['type'] . ")";
            if (isset($options['data'])) $this->data = $options['data'];
        }

        public function toArray(): array {
            $response_array = array(
                'code' => $this->code,
                'success' => $this->success
            );
            if ($this->message) $response_array['message'] = $this->message;
            if ($this->data) $response_array['data'] = $this->data;
                else $response_array['data'] = array();
            return $response_array;
        }
    }
    /**
     * This is the response manager.
     */
    class MessageResponses {
        private string $not_found = "Ressource not found, path or method used may be incorrect.";
        private string $bad_request = "Bad request or incorrect body provided.";
        private string $internal_server_error = "An internal server error occured. Please try again later.";
        private string $already_exists = "Your request enter in conflicts with another ressource.";
        private string $entity_too_large = "The ressource you provided is too large. 2Mb maximum are allowed.";

        /**
         * The response manager method
         * @param int Message code
         */
        public function newResponse(int $code, array $options = null): array {
            $response = new Response();
            $type = null;
            $data = null;
            if (isset($options['type'])) $type = $options['type'];
            if (isset($options['data'])) $data = $options['data'];

            switch ($code) {
                case 200:
                    $response->setResponse($code, true, ['data' => $data]);
                    break;
                case 400:
                    $response->setResponse($code, false, ['message' => $this->bad_request, 'type' => $type]);
                    break;
                case 404:
                    $response->setResponse($code, false, ['message' => $this->not_found, 'type' => $type]);
                    break;
                case 409:
                    $response->setResponse($code, false, ['message' => $this->already_exists, 'type' => $type]);
                    break;
                case 413:
                    $response->setResponse($code, false, ['message' => $this->entity_too_large]);
                    break;
                case 500:
                    $response->setResponse($code, false, ['message' => $this->internal_server_error, 'type' => $type]);
                    break;
                default:
                    $response->setResponse(500, false, ['message' => $this->internal_server_error]);
                    break;
            }

            return $response->toArray();
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

            $object_sanitized = [];

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