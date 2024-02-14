<?php
    /*
        Database Functionalities
        This encompasses various database operations such as database instance creation,
        adding/creating/inserting user records, updating, editing,
        and deleting user records, as well as retrieving single or all users' records.
        It also includes validation processes.
    */
    require_once 'db.php';
    class UserQueries {
        private $conn;

        public function __construct() {
            $this->conn = Database::getInstance()->getConnection();
        }

        // new/create user record
        public function addUser($firstName, $lastName, $dob, $phone, $email) {
            // Insert user into the 'users' table
            $query = "INSERT INTO users (firstName, lastName, dob, phone, email) VALUES (?, ?, ?, ?, ?)";
            $params = array('types' => 'sssss', 'values' => [$firstName, $lastName, $dob, $phone, $email]);
            $data = null;
            $response = $this->executeStatement($query, $params,$data);

            if ($response['status'] === 'success') {
                // Retrieve the last inserted ID if needed
                $lastInsertedId = $this->conn->insert_id;
                return array_merge($response, array('lastInsertedId' => $lastInsertedId));
            } else {
                return $response;
            }
        }

        // delete a specific user record
        public function deleteUser($userId)
        {
            // check, if user already exist or not
            if ($this->isUserExist($userId)) {
                $query = "update users set is_deleted = 1 WHERE id = ?";
                $params = array('types' => 'i', 'values' => [$userId]);
                $data = null;
                return $this->executeStatement($query, $params,$data);
            } else {
                return array('status' => 'error', 'message' => 'User does not exist');
            }
        }

        // specifici user details
        public function getUserDetails($userId)
        {
            // Get user details from the 'users' table if user exists
            if ($this->isUserExist($userId)) {
                $query = "SELECT * FROM users WHERE id = ?";
                $params = array('types' => 'i', 'values' => [$userId]);
                $data = 1;
                return $this->executeStatement($query, $params,$data);
            } else {
                return array('status' => 'error', 'message' => 'User does not exist');
            }
        }

        // all users record on the base of pagination, sorting and search
        public function getAllUsers($page, $perPage, $sortColumn, $sortDirection, $offset, $search) {
            $users = array();
            $searchQuery = "";
            if ($search) {
                $searchTerms = explode(' ', $search);
                // Construct the search conditions for each term
                $conditions = [];
                foreach ($searchTerms as $term) {
                    $conditions[] = "(firstName LIKE '%$term%' OR
                                    lastName LIKE '%$term%' OR
                                    email LIKE '%$term%' OR
                                    phone LIKE '%$term%' OR
                                    dob LIKE '%$term%')";
                }

                // Combine the conditions with OR
                $searchQuery = " AND " . implode(' OR ', $conditions);
            }
            // Fetch total number of users
            $totalRecordQuery = "SELECT COUNT(*) as total FROM users where is_deleted = 0 ". $searchQuery;
            $totalCountResult = $this->conn->query($totalRecordQuery);
            $totalCount = $totalCountResult->fetch_assoc()['total'];
            $sortingAndPaginationBasequery = "SELECT * FROM users where is_deleted = 0 ". $searchQuery ." ORDER BY $sortColumn $sortDirection LIMIT $offset, $perPage";

            $result = $this->conn->query($sortingAndPaginationBasequery);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $users[] = $row;
                }
            }

            return array(
                'totalUsers' => $totalCount,
                'users' => $users
            );
        }

        // get users list for local storage, also check,
        // this is first time to update in local storage or we syncing the data from database to localstorage
        // if $dbRecordSyncInLocalStorage  = true, means we are syncing the local storage record with database
        public function usersDetailedList() {
            $response = [];
            $count = true; //means we need a count, from users table
            // Check if a record has been updated or deleted in the last thirty seconds
            $updatedUsers = $this->isUsersTableUpdated();
            $deletedUsers = $this->isUsersTableDeleted();

            // Get the total count of all users
            $allUserCount = $this->usersTable($count);
            // Assign values to the response array
            $response['updatedUsers'] = $updatedUsers['data'];
            $response['deletedUsers'] = $deletedUsers['data'];
            $response['allUserCount'] = isset($allUserCount['data']['0']['total']) ? $allUserCount['data']['0']['total'] : 0;

            return $response;
        }


        // email is exist or not, against a specific user
        public function isEmailExist($id, $email)
        {
            // for existing record update case
            if($id != 0){
                $stmt = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
                $stmt->bind_param("si", $email, $id);
            }else{
                // first time new record insertion base validation
                $stmt = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
            }
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            return $count > 0;
        }

        // update a specific user
        public function updateUser($id, $firstName, $lastName, $dob, $phone, $email)
        {
            // Get user details from the 'users' table if user exists
            if ($this->isUserExist($id)) {
                // Update user in the 'users' table
                $stmt = $this->conn->prepare("UPDATE users SET firstName = ?, lastName = ?, dob = ?, phone = ?, email = ? WHERE id = ?");
                $stmt->bind_param("sssssi", $firstName, $lastName, $dob, $phone, $email, $id);
                if ($stmt->execute()) {
                    // Update successful
                    return array('status' => 'success', 'message' => 'User Updated Successfully!');
                } else {
                    // Update failed
                    echo 'Error: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                return array('status' => 'error', 'message' => 'User does not exist');
            }
        }

        // Check if the user with the given ID exists
        public function isUserExist($userId)
        {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            return $count > 0;
        }

        // check user is updated, in last thirty seconds
        public function isUsersTableUpdated()
        {
            date_default_timezone_set('Asia/Karachi');
            $thirtySecondsAgo = date('Y-m-d H:i:s', strtotime('-8 seconds'));
            $query = "SELECT * FROM users WHERE updated_at >= ? AND is_deleted = ? ORDER BY id DESC";
            $params = array('types' => 'si', 'values' => [$thirtySecondsAgo,0]); //0 for default users, and 1 for deleted users
            $data = 1;
            return $this->executeStatement($query, $params,$data);

        }
        // check user is deleted  users, in last thirty seconds
        public function isUsersTableDeleted()
        {
            date_default_timezone_set('Asia/Karachi');
            $thirtySecondsAgo = date('Y-m-d H:i:s', strtotime('-8 seconds'));
            $query = "SELECT * FROM users WHERE updated_at >= ? AND is_deleted = ? ORDER BY id DESC";
            $params = array('types' => 'si', 'values' => [$thirtySecondsAgo,1]); //1 for deleted users
            $data = 1;
            return $this->executeStatement($query, $params,$data);

        }
        // all users count
        public function usersTable($count = true)
        {
            date_default_timezone_set('Asia/Karachi');
            if($count == false){
                $query = "SELECT *  FROM users WHERE is_deleted = ? ORDER BY id DESC";
            }else{
                $query = "SELECT count(id) as total FROM users WHERE is_deleted = ? ORDER BY id DESC";
            }
            $params = array('types' => 's', 'values' => [0]);
            $data = 1;
            return $this->executeStatement($query, $params,$data);

        }

        // over all quries execution
        private function executeStatement($query, $params = null,$data = null) {
            $response = array();
            $stmt = $this->conn->prepare($query);
            if ($params !== null) {
                $stmt->bind_param($params['types'], ...$params['values']);
            }
            if ($data !== null) {
                if ($stmt->execute()) {
                    $result = $stmt->get_result(); //is used to obtain the result set, and then methods like fetch_assoc are used on the result set for row-by-row fetching
                    $data = $result->fetch_all(MYSQLI_ASSOC); //fetches all rows directly into an array, The argument MYSQLI_ASSOC specifies that the array should be associative,
                    $response['status'] = 'success';
                    $response['message'] = 'Operation successful!';
                    $response['data'] = $data;
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Error: ' . $stmt->error;
                }
            }else{
                if ($stmt->execute()) {
                    $response['status'] = 'success';
                    $response['message'] = 'Operation successful!';

                    // Additional logic if needed after a successful operation
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Error: ' . $stmt->error;
                }
            }
            $stmt->close();
            return $response;
        }
    }
?>
