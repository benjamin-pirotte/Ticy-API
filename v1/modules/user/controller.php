<?php
    /**
    * User Registration
    * url - /register
    * method - POST
    * params - first_name, last_name, email, password, phone, birthdate
    */
    require_once dirname(__FILE__) . '/dbHandler.php';

    $app->post('/user/register', function() use ($app) {
        // check for required params
        verifyRequiredParams(array('first_name', 'last_name', 'email', 'password', 'phone', 'birthdate'));

        $response = array();

        // reading post params
        $first_name = $app->request->post('first_name');
        $last_name = $app->request->post('last_name');
        $email = $app->request->post('email');
        $password = $app->request->post('password');
        $phone = $app->request->post('phone');
        $birthdate = $app->request->post('birthdate');

        // validating email address
        validateEmail($email);

        $db = new DbHandlerUser();
        $res = $db->createUser($first_name, $last_name, $email, $password, $phone, $birthdate);

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
    $app->post('/user/login', function() use ($app) {
        // check for required params
        verifyRequiredParams(array('email', 'password'));

        // reading post params
        $email = $app->request()->post('email');
        $password = $app->request()->post('password');
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
    $app->post('/user/islogged','authenticate', function() use ($app) {
        // Get params
        global $user_id;

        $response['error'] = false;
        $response['message'] = "User is logged";
        echoRespnse(200, $response);
    });
    /**
    * User Edit
    * url - /User/edit
    * method - Put
    * params - first_name, last_name, phone, birthdate, password, email
    */
    $app->put('/user/edit','authenticate',function() use ($app) { 
        // Get params
        global $user_id;
        $first_name = $app->request->put('first_name');
        $last_name = $app->request->put('last_name');
        $phone = $app->request->put('phone');
        $birthdate = $app->request->put('birthdate');
        $password = $app->request->put('password');        

        $db = new DbHandlerUser();
        $response = array();
        
        // updating task
        $result = $db->editUser($user_id, $first_name, $last_name, $phone, $birthdate);
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
