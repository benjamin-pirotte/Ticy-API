<?php
  require_once dirname(__FILE__) . '/helpers/PassHash.php';
  require '.././lib/Slim/Slim.php';

  \Slim\Slim::registerAutoloader();

  $app = new \Slim\Slim();

  // User id from db - Global Variable
  $user_id = NULL;

  // Helpers
  require_once dirname(__FILE__) . '/helpers/verifyParams.php';
  require_once dirname(__FILE__) . '/helpers/validateEmail.php';
  require_once dirname(__FILE__) . '/helpers/authenticate.php';
  require_once dirname(__FILE__) . '/helpers/echoRespnse.php';

  //require modules
  require dirname(__FILE__) . '/modules/modules.php';
  $app->run();

?>
