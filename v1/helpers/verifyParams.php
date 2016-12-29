<?php
  /**
   * Verifying required params posted or not
   */
  function verifyRequiredParams($required_fields) {
      $error = false;
      $error_fields = "";
      $request_params = array();

      $app = \Slim\Slim::getInstance();
      $json = $app->request->getBody();
      $request_params = json_decode($json, true); 
            
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
          $response["errorFields"] = $error_fields;
          $response["message"] = 'Required fields are missing or empty';
          echoRespnse(400, $response);
          $app->stop();
      }
  }
?>
