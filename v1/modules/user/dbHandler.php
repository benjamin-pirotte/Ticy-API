<?php

  /**
   * Class to handle all db operations
   * This class will have CRUD methods for database tables
   *
   * @author Ravi Tamada
   */
  class DbHandlerUser {

      private $conn;

      function __construct() {
          // opening db connection
          $db = new DbConnect();
          $this->conn = $db->connect();
      }

      /* ------------- `users` table method ------------------ */

      /**
       * Creating new user
       * @param String $firstName
       * @param String $lastName
       * @param String $email User login email id
       * @param String $password User login password
       * @param String $phone
       * @param String $birthdate
       * @param String $email
       */
      public function createUser($first_name, $last_name, $email, $password, $phone, $birthdate, $gender) {
          $response = array();

          // First check if user already existed in db
          if (!$this->isUserExists($email)) {
              // Generating password hash
              $password_hash = PassHash::hash($password);

              // Generating API key
              $api_key = $this->generateApiKey();

              // insert query
              $stmt = $this->conn->prepare("INSERT INTO users(first_name, last_name, email, password_hash, phone, birthdate, gender, api_key, status) values(?, ?, ?, ?, ?, ?, ?, 1)");
              
              $stmt->bind_param("ssssssss", $first_name, $last_name, $email, $password_hash, $phone, $birthdate, $gender, $api_key);

              $result = $stmt->execute();

              $stmt->close();

              // Check for successful insertion
              if ($result) {
                  // User successfully inserted
                  return USER_CREATED_SUCCESSFULLY;
              } else {
                  // Failed to create user
                  return USER_CREATE_FAILED;
              }
          } else {
              // User with same email already existed in the db
              return USER_ALREADY_EXISTED;
          }

          return $response;
      }

      /**
       * Checking user login
       * @param String $email User login email id
       * @param String $password User login password
       * @return boolean User login status success/fail
       */
      public function checkLogin($email, $password) {
          // fetching user by email
          $stmt = $this->conn->prepare("SELECT password_hash FROM users WHERE email = ?");

          $stmt->bind_param("s", $email);

          $stmt->execute();

          $stmt->bind_result($password_hash);

          $stmt->store_result();

          if ($stmt->num_rows > 0) {
              // Found user with the email
              // Now verify the password

              $stmt->fetch();

              $stmt->close();

              if (PassHash::check_password($password_hash, $password)) {
                  // User password is correct
                  return TRUE;
              } else {
                  // user password is incorrect
                  return FALSE;
              }
          } else {
              $stmt->close();

              // user not existed with the email
              return FALSE;
          }
      }

      /**
       * Edit user
       * @param String $first_name User new first_name
       * @param String $last_name User new last_name
       * @param String $email User new email
       * @param String $phone User new phone
       * @param String $gender User new gender
       */
      public function editUser($user_id, $first_name, $last_name, $email, $phone, $birthdate, $gender) {
          if (!$this->isUserExists($email)) {
            $stmt = $this->conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, birthdate = ?, gender = ? Where id = ?");
            $stmt->bind_param("sssssi", $first_name, $last_name, $email, $phone, $birthdate, $gender, $user_id);
            $stmt->execute();
            $num_affected_rows = $stmt->affected_rows;
            $stmt->close();
            return $num_affected_rows > 0;
          }else {
              return USER_ALREADY_EXISTED;
          }
      }

       /**
       * Edit user password
       * @param String $password User new password
       */
        public function editUserPassword($user_id, $password) {
            $password_hash = PassHash::hash($password);

            // Generating API key
            $api_key = $this->generateApiKey();

            $stmt = $this->conn->prepare("UPDATE users SET password_hash = ?, api_key = ? Where id = ?");
            $stmt->bind_param("ssi", $password_hash, $api_key, $user_id);
            $stmt->execute();
            $num_affected_rows = $stmt->affected_rows;
            $stmt->close();
            return $num_affected_rows > 0;
        }


      /**
       * Checking for duplicate user by email address
       * @param String $email email to check in db
       * @return boolean
       */
      private function isUserExists($email) {
          $stmt = $this->conn->prepare("SELECT id from users WHERE email = ?");
          $stmt->bind_param("s", $email);
          $stmt->execute();
          $stmt->store_result();
          $num_rows = $stmt->num_rows;
          $stmt->close();
          return $num_rows > 0;
      }

      /**
       * Fetching user by email
       * @param String $email User email id
       */
      public function getUserByEmail($email) {
          $stmt = $this->conn->prepare("SELECT id, first_name, last_name, email, phone, birthdate, gender, created_at FROM users WHERE email = ?");
          $stmt->bind_param("s", $email);  
          if ($stmt->execute()) {
              $user = $stmt->get_result()->fetch_assoc();
              $stmt->close();
              return $user;
          } else {
              return NULL;
          }
      }

      /**
       * Fetching user by id
       * @param Int $id User id
       */
      public function getUserById($id) {
          $stmt = $this->conn->prepare("SELECT id, first_name, last_name, email, phone, birthdate, gender, created_at FROM users WHERE id = ?");
          $stmt->bind_param("s", $id);  
          if ($stmt->execute()) {
              $user = $stmt->get_result()->fetch_assoc();
              $stmt->close();
              return $user;
          } else {
              return NULL;
          }
      }

      /**
       * Fetching user api key
       * @param String $user_id user id primary key in user table
       */
      public function getApiKeyById($user_id) {
          $stmt = $this->conn->prepare("SELECT api_key FROM users WHERE id = ?");
          $stmt->bind_param("i", $user_id);
          if ($stmt->execute()) {
              $result = $stmt->get_result()->fetch_assoc();
              $stmt->close();
              return $result['api_key'];
          } else {
              return NULL;
          }
      }

      /**
       * Fetching user id by api key
       * @param String $api_key user api key
       */
      public function getUserId($api_key) {
          $stmt = $this->conn->prepare("SELECT id FROM users WHERE api_key = ?");
          $stmt->bind_param("s", $api_key);
          if ($stmt->execute()) {
              $user_id = $stmt->get_result()->fetch_assoc();
              $stmt->close();
              return $user_id;
          } else {
              return NULL;
          }
      }

      /**
       * Validating user api key
       * If the api key is there in db, it is a valid key
       * @param String $api_key user api key
       * @return boolean
       */
      public function isValidApiKey($api_key) {
          $stmt = $this->conn->prepare("SELECT id from users WHERE api_key = ?");
          $stmt->bind_param("s", $api_key);
          $stmt->execute();
          $stmt->store_result();
          $num_rows = $stmt->num_rows;
          $stmt->close();
          return $num_rows > 0;
      }

      /**
       * Generating random Unique MD5 String for user Api key
       */
      private function generateApiKey() {
          return md5(uniqid(rand(), true));
      }
  }

?>
