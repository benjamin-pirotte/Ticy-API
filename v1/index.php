<?php
  require_once dirname(__FILE__) . '/helpers/PassHash.php';
  require '.././lib/Slim/Slim.php';

  \Slim\Slim::registerAutoloader();

  $app = new \Slim\Slim();
  $app->request->headers->set('Content-Type', 'application/json');

  // Db connnect
  require_once dirname(dirname(__FILE__)) . '/config/DbConnect.php';

  // Helpers
  require_once dirname(__FILE__) . '/helpers/verifyParams.php';
  require_once dirname(__FILE__) . '/helpers/validateEmail.php';
  require_once dirname(__FILE__) . '/helpers/authenticate.php';
  require_once dirname(__FILE__) . '/helpers/echoRespnse.php';

  //require modules
  require dirname(__FILE__) . '/modules/modules.php';
  $app->run();

?>
