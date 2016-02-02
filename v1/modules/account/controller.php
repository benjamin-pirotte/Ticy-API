<?php
  /**
   * User Registration
   * url - /register
   * method - POST
   * params - name, email, password
   */
  require_once dirname(__FILE__) . '/dbHandler.php';

  $app->post('/register', function() use ($app) {
      // check for required params
      verifyRequiredParams(array('name', 'email', 'password'));

      $response = array();

      // reading post params
      $name = $app->request->post('name');
      $email = $app->request->post('email');
      $password = $app->request->post('password');

      // validating email address
      validateEmail($email);

      $db = new DbHandlerAccount();
      $res = $db->createUser($name, $email, $password);

      if ($res == USER_CREATED_SUCCESSFULLY) {
          $response["error"] = false;
          $response["message"] = "You are successfully registered";
          echoRespnse(201, $response);
      } else if ($res == USER_CREATE_FAILED) {
          $response["error"] = true;
          $response["message"] = "Oops! An error occurred while registereing";
          echoRespnse(400, $response);
      } else if ($res == USER_ALREADY_EXISTED) {
          $response["error"] = true;
          $response["message"] = "Sorry, this email already existed";
          echoRespnse(401, $response);
      }
  });
/**
 * User Login
 * url - /login
 * method - POST
 * params - email, password
 */
$app->post('/login', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('email', 'password'));

            // reading post params
            $email = $app->request()->post('email');
            $password = $app->request()->post('password');
            $response = array();

            $db = new DbHandlerAccount();
            // check for correct email and password
            if ($db->checkLogin($email, $password)) {
                // get the user by email
                $user = $db->getUserByEmail($email);

                if ($user != NULL) {
                    $response["error"] = false;
                    $response['name'] = $user['name'];
                    $response['email'] = $user['email'];
                    $response['apiKey'] = $user['api_key'];
                    $response['createdAt'] = $user['created_at'];

                    echoRespnse(200, $response);
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";

                    echoRespnse(400, $response);
                }
            } else {
                // user credentials are wrong
                $response['error'] = true;
                $response['message'] = 'Login failed. Incorrect credentials';

                echoRespnse(400, $response);
            }
        });

        /**
         * User Edit
         * url - /User/edit
         * method - POST
         * params - email, password
         */
        $app->put('/user/edit','authenticate',function() use ($app) {
          // check for required params
           verifyRequiredParams(array('name', 'password'));

           global $user_id;
           $name = $app->request->put('name');
           $password = $app->request->put('password');

           $db = new DbHandlerAccount();
           $response = array();

           // updating task
           $result = $db->editUser($user_id, $name, $password);
           if ($result) {
               // task updated successfully
               $response["error"] = false;
               $response["message"] = "User updated successfully";
               echoRespnse(200, $response);
           } else {
               // task failed to update
               $response["error"] = true;
               $response["message"] = "User failed to update. Please try again!";
               echoRespnse(400, $response);
           }
        });
?>
