<?php 
  /**
   * Echoing json response to client
   * @param String $status_code Http response code
   * @param Int $response Json response
   */
  function echoRespnse($status_code, $response) {
      $app = \Slim\Slim::getInstance();
      // Http response code
      $app->status($status_code);

      // setting response content type to json
      $app->contentType('application/json');

      echo json_encode($response);
  }
 ?>
