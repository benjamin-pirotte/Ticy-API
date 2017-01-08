<?php
    define("EMAIL_ADDRESS_IS_NOT_VALID", "EMAIL_ADDRESS_IS_NOT_VALID");
    /**
     * Validating email address
     */
    function validateEmail($email) {
        $app = \Slim\Slim::getInstance();
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response["message"] = EMAIL_ADDRESS_IS_NOT_VALID;
            echoRespnse(400, $response);
            $app->stop();
        }
    }
  ?>
