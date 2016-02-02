<?php
  /**
   * Verifying required params posted or not
   */
  function verifyRequiredParams($required_fields) {
      $error = false;
      $error_fields = "";
      $request_params = array();
      $request_params = $_REQUEST;
      // Handling PUT request params
      if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
          $app = \Slim\Slim::getInstance();
          parse_str($app->request()->getBody(), $request_params);
      }
      foreach ($required_fields as $field) {
          if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
              $error = true;
              $error_fields .= $field . ', ';
          }
      }

      if ($error) {
          // Required field(s) are missing or empty
          // echo error json and stop the app
          $response = array();
          $app = \Slim\Slim::getInstance();
          $response["error"] = true;
          $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
          echoRespnse(400, $response);
          $app->stop();
      }
  }
?>
