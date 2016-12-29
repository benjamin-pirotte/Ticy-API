<?php
    // User id from db - Global Variable
    $user_id = NULL;

    /**
    * User Registration
    * url - /register
    * method - POST
    * params - first_name, last_name, email, password, phone, birthdate
    */
    require_once dirname(__FILE__) . '/dbHandler.php';

    $app->post('/user/register', function() use ($app) {
        // check for required params
        verifyRequiredParams(array('first_name', 'last_name', 'email', 'password', 'phone', 'birthdate', 'gender'));

        $json = $app->request->getBody();
        $user = json_decode($json, true); 

        $response = array();

        // reading post params
        $first_name = $user['first_name'];
        $last_name = $user['last_name'];
        $email = $user['email'];
        $password = $user['password'];
        $phone = $user['phone'];
        $birthdate = $user['birthdate'];
        $gender = $user['gender'];

        // validating email address
        validateEmail($email);

        $db = new DbHandlerUser();
        $res = $db->createUser($first_name, $last_name, $email, $password, $phone, $birthdate, $gender);

        if ($res == USER_CREATED_SUCCESSFULLY) {
        
            $user = $db->getUserByEmail($email);
        
            if ($user != NULL) {
                $response['id'] = $user['id'];
                $response['first_name'] = $user['first_name'];
                $response['last_name'] = $user['last_name'];
                $response['email'] = $user['email'];
                $response['phone'] = $user['phone'];
                $response['birthdate'] = $user['birthdate'];
                $response['gender'] = $user['gender'];
                $response['created_at'] = $user['created_at'];

                $apiKey = $db->getApiKeyById($user['id']);
                if($apiKey){
                    $response['api_key'] = $apiKey;
                } else {
                    $response["message"] = "Oops! An error occurred while registereing";
                    echoRespnse(400, $response);
                }
            } else {
                $response["message"] = "Oops! An error occurred while registereing";
                echoRespnse(400, $response);
            } 

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
    $app->post('/user/login', function() use ($app) {
        // check for required params
        verifyRequiredParams(array('email', 'password'));

        $json = $app->request->getBody();
        $user = json_decode($json, true); 

        // reading post params
        $email = $user['email'];
        $password = $user['password'];
        $response = array();

        $db = new DbHandlerUser();
        // check for correct email and password
        if ($db->checkLogin($email, $password)) {
            // get the user by email
            $user = $db->getUserByEmail($email);

            if ($user != NULL) {
                $response["error"] = false;
                $response['id'] = $user['id'];
                $response['first_name'] = $user['first_name'];
                $response['last_name'] = $user['last_name'];
                $response['email'] = $user['email'];
                $response['phone'] = $user['phone'];
                $response['birthdate'] = $user['birthdate'];
                $response['created_at'] = $user['created_at'];

                $apiKey = $db->getApiKeyById($user['id']);
                if($apiKey){
                    $response['api_key'] = $apiKey;
                    echoRespnse(200, $response);
                }else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";

                    echoRespnse(400, $response);
                }
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
    * User is Logged
    * url - /islogged
    * method - POST
    */
    $app->post('/user/details','authenticate', function() use ($app) {
        // Get params
        global $user_id;

        $db = new DbHandlerUser();
        $response = array();

        $user = $db->getUserById($user_id);
        if ($user != NULL) {
            $response["error"] = false;
            $response['id'] = $user['id'];
            $response['first_name'] = $user['first_name'];
            $response['last_name'] = $user['last_name'];
            $response['email'] = $user['email'];
            $response['phone'] = $user['phone'];
            $response['birthdate'] = $user['birthdate'];
            $response['gender'] = $user['gender'];
            $response['created_at'] = $user['created_at'];

            echoRespnse(200, $response);
        } else {
            // unknown error occurred
            $response['error'] = true;
            $response['message'] = "An error occurred. Please try again";

            echoRespnse(400, $response);
        }
    });
    /**
    * User Edit
    * url - /User/edit
    * method - Put
    * params - first_name, last_name, phone, birthdate, email
    */
    $app->put('/user/edit','authenticate',function() use ($app) { 
        // Get params
        global $user_id;
        $first_name = $app->request->put('first_name');
        $last_name = $app->request->put('last_name');
        $email = $app->request->put('email');
        $phone = $app->request->put('phone');
        $birthdate = $app->request->put('birthdate');
        $gender = $app->request->put('gender');   

        $db = new DbHandlerUser();
        $response = array();
        
        // updating task
        $result = $db->editUser($user_id, $first_name, $last_name, $email, $phone, $birthdate, $gender);
        if ($result) {
            $user = $db->getUserById($user_id);
             if ($user != NULL) {
                $response["error"] = false;
                $response["message"] = "User updated successfully";
                $response['id'] = $user['id'];
                $response['first_name'] = $user['first_name'];
                $response['last_name'] = $user['last_name'];
                $response['email'] = $user['email'];
                $response['phone'] = $user['phone'];
                $response['birthdate'] = $user['birthdate'];
                $response['created_at'] = $user['created_at'];

                echoRespnse(200, $response);
            } else {
                // unknown error occurred
                $response['error'] = true;
                $response['message'] = "An error occurred. Please try again";

                echoRespnse(400, $response);
            }
        } else {
            // task failed to update
            $response["error"] = true;
            $response["message"] = "User failed to update. Please try again!";
            echoRespnse(400, $response);
        }
    });

    /**
    * User Edit password
    * url - /User/updatepassword
    * method - Put
    * params - first_name, last_name, phone, birthdate, password, email
    */
    $app->put('/user/updatepassword','authenticate',function() use ($app) { 
        // Get params
        global $user_id;
        $password = $app->request->put('password'); 
        $password_copy = $app->request->put('password_copy'); 

        if($password !== $password_copy){
            // task failed to update
            $response["error"] = true;
            $response["message"] = "Passwords don't match. Please try again!";
            echoRespnse(400, $response);
            return;
        }

        $db = new DbHandlerUser();
        $response = array();
        
        // updating task
        $result = $db->editUserPassword($user_id, $password);
        if ($result) {
            $apiKey = $db->getApiKeyById($user_id);
            var_dump($apiKey);
            if($apiKey){
                $response['error'] = false;
                $response['message'] = "Password has been updated";
                $response['api_key'] = $apiKey;
                echoRespnse(200, $response);
            }else {
                $response['error'] = true;
                $response['message'] = "An error occurred. Please change your password again or contact our support";
                echoRespnse(400, $response);
            }
        } else {
            // task failed to update
            $response["error"] = true;
            $response["message"] = "Password failed to update. Please try again!";
            echoRespnse(400, $response);
        }
    });
?>
