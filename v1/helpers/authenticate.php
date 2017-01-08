<?php
    define("NO_AUTHORIZATION", "NO_AUTHORIZATION");
    define("INVALID_AUTHORIZATION", "INVALID_AUTHORIZATION");
    /**
    * Adding Middle Layer to authenticate every request
    * Checking if the request has valid api key in the 'Authorization' header
    */
    function authenticate(\Slim\Route $route) {
        // Getting request headers
        $headers = apache_request_headers();
        $response = array();
        $app = \Slim\Slim::getInstance();

        // Verifying Authorization Header
        if (isset($headers['Authorization'])) {
            $db = new DbHandlerUser();

            // get the api key
            $api_key = $headers['Authorization'];
            // validating api key
            if (!$db->isValidApiKey($api_key)) {
                // api key is not present in users table
                $response["message"] = INVALID_AUTHORIZATION;
                echoRespnse(401, $response);
                $app->stop();
            } else {
                global $user_id;
                // get user primary key id
                $user = $db->getUserId($api_key);
                if ($user != NULL)
                    $user_id = $user["id"];
            }
        } else {
            // api key is missing in header
            $response["message"] = NO_AUTHORIZATION;
            echoRespnse(400, $response);
            $app->stop();
        }
    }
?>
