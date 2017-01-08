<?php
    // User id from db - Global Variable
    $user_id = NULL;

    /**
    * User Registration
    * url - /register
    * method - POST
    * params - email, first_name, last_name, email, password, age
    */
    require_once dirname(__FILE__) . '/constants.php';
    require_once dirname(__FILE__) . '/dbHandler.php';

    $app->post('/user/register', function() use ($app) {
        // check for required params
        verifyRequiredParams(array('first_name', 'last_name', 'email', 'password', 'age'));

        $json = $app->request->getBody();
        $user = json_decode($json, true); 

        $response = array();

        // reading post params
        $email = $user['email'];
        $first_name = $user['first_name'];
        $last_name = $user['last_name'];
        $password = $user['password'];
        $age = $user['age'];

        // validating email address
        validateEmail($email);

        $db = new DbHandlerUser();
        $res = $db->createUser($first_name, $last_name, $email, $password, $age);

        if ($res == USER_CREATED_SUCCESSFULLY) {
        
            $user = $db->getUserByEmail($email);
        
            if ($user != NULL) {
                $response['id'] = $user['id'];
                $response['first_name'] = $user['first_name'];
                $response['last_name'] = $user['last_name'];
                $response['email'] = $user['email'];
                $response['age'] = $user['age'];
                $response['phone'] = $user['phone'];
                $response['birthdate'] = $user['birthdate'];
                $response['profile_picture_uri'] = $user['profile_picture_uri'];
                $response['created_at'] = $user['created_at'];
                $response['api_key'] = $user['api_key'];
                echoRespnse(201, $response);
            } else {
                $response["message"] = CANT_RETURN_USER;
                echoRespnse(400, $response);
            } 
        } else if ($res == FAILED_TO_CREATE) {
            $response["message"] = FAILED_TO_CREATE;
            echoRespnse(400, $response);
        } else if ($res == EMAIL_ALREADY_TAKEN) {
            $response["message"] = EMAIL_ALREADY_TAKEN;
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
                $response['id'] = $user['id'];
                $response['first_name'] = $user['first_name'];
                $response['last_name'] = $user['last_name'];
                $response['email'] = $user['email'];
                $response['age'] = $user['age'];
                $response['phone'] = $user['phone'];
                $response['birthdate'] = $user['birthdate'];
                $response['profile_picture_uri'] = $user['profile_picture_uri'];
                $response['created_at'] = $user['created_at'];
                $response['api_key'] = $user['api_key'];
                echoRespnse(200, $response);
            } else {
                // unknown error occurred
                $response['message'] = CANT_RETURN_USER;
                echoRespnse(400, $response);
            }
        } else {
            // user credentials are wrong
            $response['message'] = INCORRECT_CREDENTIALS;
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
            $response['age'] = $user['age'];
            $response['phone'] = $user['phone'];
            $response['birthdate'] = $user['birthdate'];
            $response['profile_picture_uri'] = $user['profile_picture_uri'];
            $response['created_at'] = $user['created_at'];
            $response['api_key'] = $user['api_key'];
            echoRespnse(200, $response);
        } else {
            // unknown error occurred
            $response['message'] = CANT_RETURN_USER;
            echoRespnse(400, $response);
        }
    });
    /**
    * User Edit
    * url - /User/edit
    * method - Put
    * params - email, first_name, last_name, age
    */
    $app->put('/user/edit','authenticate',function() use ($app) {
        //verify params 
        verifyRequiredParams(array('first_name', 'last_name', 'email', 'age'));

        // Get params
        $json = $app->request->getBody();
        $user = json_decode($json, true); 

        global $user_id;
        $email = $user['email'];
        $first_name = $user['first_name'];
        $last_name = $user['last_name'];
        $age = $user['age'];

        $db = new DbHandlerUser();
        $response = array();

        // updating task
        $res = $db->editUser($user_id, $email, $first_name, $last_name, $age);
        if ($res == USER_UPDATED) {
            $user = $db->getUserById($user_id);
             if ($user != NULL) {
                $response["error"] = false;
                $response["message"] = "User updated successfully";
                $response['id'] = $user['id'];
                $response['first_name'] = $user['first_name'];
                $response['last_name'] = $user['last_name'];
                $response['email'] = $user['email'];
                $response['age'] = $user['age'];
                $response['phone'] = $user['phone'];
                $response['birthdate'] = $user['birthdate'];
                $response['profile_picture_uri'] = $user['profile_picture_uri'];
                $response['created_at'] = $user['created_at'];
                $response['api_key'] = $user['api_key'];
                echoRespnse(200, $response);
            } else {
                // unknown error occurred
                $response['message'] = CANT_RETURN_USER;
                echoRespnse(400, $response);
            }
        } else if($res == EMAIL_ALREADY_TAKEN) {
            // task failed to update
            $response["message"] = EMAIL_ALREADY_TAKEN;
            echoRespnse(401, $response);
        } else if($res == NO_CHANGE) {
            // task didn't update user because no change
            $response["message"] = NO_CHANGE;
        } else {
            $response["message"] = FAILED_TO_UPDATE;
            echoRespnse(400, $response);
        }
    });

    /**
    * User Edit password
    * url - /User/editpassword
    * method - Put
    * params - old_password, password
    */
    $app->put('/user/editpassword','authenticate',function() use ($app) { 
        //verify params 
        verifyRequiredParams(array('old_password', 'new_password'));

        // Get params
        $json = $app->request->getBody();
        $password = json_decode($json, true); 

        global $user_id;

        $old_password = $password['old_password']; 
        $new_password = $password['new_password']; 

        $db = new DbHandlerUser();
        $response = array();
        
        // updating task
        $res = $db->editUserPassword($user_id, $old_password, $new_password);
        if ($res == PASSWORD_IS_CHANGED) {
            $apiKey = $db->getApiKeyById($user_id);
            if($apiKey){
                $response['api_key'] = $apiKey;
                echoRespnse(200, $response);
            } else {
                $response['message'] = "An error occurred. Please log out and login";
                echoRespnse(400, $response);
            }
        } else if($res == NO_CHANGE) {
            // task failed to update
            $response["message"] = NO_CHANGE;
            echoRespnse(400, $response);
        } else if($res == INCORRECT_CREDENTIALS) {
            // task failed to update
            $response["message"] = INCORRECT_CREDENTIALS;
            echoRespnse(401, $response);
        } else {
            // task failed to update
            $response["message"] = FAILED_TO_UPDATE;
            echoRespnse(400, $response);
        }
    });
?>
