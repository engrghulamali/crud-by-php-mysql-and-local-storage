<?php

require_once __DIR__ . '\..\db\db.php'; // datbase connection
require_once __DIR__ . '\..\db\UserQueries.php'; // db functions about crud.
class UserController
{
    private $userQueries;
    private $formValidator;
    public function __construct()
    {
        // Initialize UserQueries instance once in the constructor for reuse
        $this->userQueries = new UserQueries();
    }

    // save user record in users table.
    public function addUser()
    {
        $safePost = filter_var_array($_POST, $this->getFilterOptions(),false);
        $validationErrors = $this->getValidationErrors($safePost,$_POST);
        $errors = [];
        if (!empty($validationErrors)) {
            $this->respondJson(['status' => false, 'message' => 'Validation failed', 'errors' => $validationErrors]);
        } else {
            // new record insertion
            if ($this->userQueries->isEmailExist($id = 0, $safePost['email'])) { // here id, means existing user base email check
                $this->respondJson(['status' => false, 'message' => 'Validation failed', 'errors' => ['email' => 'Email Already Exist']]);
            }
            $response = $this->userQueries->addUser(
                $safePost['firstName'],
                $safePost['lastName'],
                $safePost['dob'],
                $safePost['phone'],
                $safePost['email']
            );
            $this->respondJson(['status' => $response['status'], 'message' => $response['message'], 'id' => $response['lastInsertedId']]);
        }
    }

    // Delete user from users table.
    public function deleteUser()
    {
        $userId = filter_input(INPUT_POST, 'userId', FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
        $response = $this->userQueries->deleteUser($userId);
        $this->respondJson(['status' => $response['status'], 'message' => $response['message'] ]);
    }

    // Function to fetch the list of users table.
    public function fetchUsersList()
    {
        $response =  $this->userQueries->usersTable(false);
        $this->respondJson(['status' => 'success', 'users' => $response['data']]);
    }

    // Function to fetch the status [updated, deleted, allUsersCount] from users table
    public function fetchUsersListForDbStatus()
    {
        $response =  $this->userQueries->usersDetailedList();
        $this->respondJson(['status' => 'success', 'updatedUsers' => $response['updatedUsers'],'deletedUsers' => $response['deletedUsers'],'allUserCount' => $response['allUserCount']]);
    }

    // Function to fetch user records based on pagination and sorting from users table.
    public function fetchUsersRecord()
    {
        // Sanitize and validate input values
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $perPage = filter_input(INPUT_GET, 'perPage', FILTER_VALIDATE_INT) ?: 5;
        $sortColumn = filter_input(INPUT_GET, 'sortColumn', FILTER_SANITIZE_STRING) ?: 'id';
        $sortDirection = filter_input(INPUT_GET, 'sortDirection', FILTER_SANITIZE_STRING) ?: 'asc';
        $search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) ?: '';
        // Calculate offset based on pagination
        $offset = ($page - 1) * $perPage;
        $users =  $this->userQueries->getAllUsers($page, $perPage, $sortColumn, $sortDirection, $offset, $search);
        $this->respondJson($users);
    }

    // Function to get details of a specific user
    public function getUserDetails()
    {
        $userId = filter_input(INPUT_GET, 'userId', FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
        $response =  $this->userQueries->getUserDetails($userId);
        if($response['status'] == 'success'){
            $this->respondJson(['status' => $response['status'], 'message' => $response['message'], 'userDetails' => $response['data'] ]);
        }else{
            $this->respondJson(['status' => $response['status'], 'message' => $response['message'], 'userDetails' => [] ]);
        }
    }

    // Function to update an existing user
    public function updateUser()
    {
        $id = filter_input(INPUT_POST, 'userId', FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
        $safePost = filter_var_array($_POST, $this->getFilterOptions(),false);
        $validationErrors = $this->getValidationErrors($safePost,$_POST);
        $errors = [];
        if (!empty($validationErrors)) {
            $this->respondJson(['status' => false, 'message' => 'Validation failed', 'errors' => $validationErrors]);
        } else {
            // is email exist
            if ($this->userQueries->isEmailExist($id, $safePost['email'])) {
                $this->respondJson(['status' => false, 'message' => 'Validation failed', 'errors' => ['email' => 'Email Already Exist']]);
            }
            $response = $this->userQueries->updateUser($id, $safePost['firstName'], $safePost['lastName'], $safePost['dob'], $safePost['phone'], $safePost['email']);
            $this->respondJson(['status' => $response['status'], 'message' => $response['message'] ]);
        }
    }

    // Function to update multiple users coming from local storage
    public function updateAllUsers()
    {
        $updatedUsers = filter_input(INPUT_POST, 'users', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if ($updatedUsers === null) {
            $this->respondJson(['success' => false, 'message' => 'Invalid JSON data']);
            exit;
        }
        $errorsArray = [];
        $messagesArray = [];

        foreach ($updatedUsers as $userData) {
            $id = $userData['id'];
            $safePost = filter_var_array($userData, $this->getFilterOptions(), false);
            $validationErrors = $this->getValidationErrors($safePost, $userData);
            if ($this->userQueries->isEmailExist($id, $safePost['email'])) {
                $errorsArray[] = ['email' => 'Email Already Exist'];
            }
            elseif (!empty($validationErrors)) {
                $errorsArray[] = $validationErrors;
            } else {
                $response = $this->userQueries->updateUser(
                    $id,
                    $safePost['firstName'],
                    $safePost['lastName'],
                    $safePost['dob'],
                    $safePost['phone'],
                    $safePost['email']
                );
                $messagesArray[] = $response['message'];
            }
        }

        $this->respondJson(['action' => "Update specific Users in db by local storage.", 'errors' => $errorsArray, 'message' => $messagesArray]);
    }

    // Function to update multiple users coming from local storage
    public function insertAllUsers()
    {
        $newUsersList = filter_input(INPUT_POST, 'users', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if ($newUsersList === null) {
            $this->respondJson(['success' => false, 'message' => 'Invalid JSON data']);
            exit;
        }

        $errorsArray = [];
        $messagesArray = [];
        $id = 0;

        foreach ($newUsersList as $userData) {
            $safePost = filter_var_array($userData, $this->getFilterOptions(), false);
            $validationErrors = $this->getValidationErrors($safePost, $userData);
            // Additional check for email existence
            if ($this->userQueries->isEmailExist($id, $safePost['email'])) {
                $errorsArray[] = ['email' => 'Email Already Exist'];
            }elseif(!empty($validationErrors))
            {
                $errorsArray[] = $validationErrors;
            } else {
                $response = $this->userQueries->addUser(
                    $safePost['firstName'],
                    $safePost['lastName'],
                    $safePost['dob'],
                    $safePost['phone'],
                    $safePost['email']
                );
                $messagesArray[] = $response['message'];
            }
        }

        $this->respondJson(['action' =>  "All New Users Insert in db by local storage.", 'errors' =>  $errorsArray, 'message' => $messagesArray]);
    }


    // Function to delete multiple users comming from the local storage
    public function deleteAllUsers()
    {
        $deletedUsersList = filter_input(INPUT_POST, 'users', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if ($deletedUsersList === null) {
            $this->respondJson(['success' => false, 'message' => 'Invalid JSON data']);
            exit;
        }
        $success = true;
        $message = '';
        $validationErrors = [];
        $errorsArray = [];
        $messagesArray = [];
        foreach ($deletedUsersList as $userData) {
            $id = $userData['id'];
            $response = $this->userQueries->deleteUser($id);
            $messagesArray[] = $response['message'];

        }
        $this->respondJson(['action' =>  "Delete All Users  from db by local storage.",'errors' =>  [], 'message' => $messagesArray]);
    }

    // Utility function to send a JSON response with optional HTTP status code
    private function respondJson($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    // filter option for add/update user
    public function getFilterOptions() {
        $filterOptions = [
            "firstName" => [
                "filter" => FILTER_VALIDATE_REGEXP,
                "options" => [
                    "regexp" => '/^[a-zA-Z]+([ -][a-zA-Z]+)*$/',
                    "message" => 'Invalid characters or format in the first name!'
                ]
            ],
            "lastName" => [
                "filter" => FILTER_VALIDATE_REGEXP,
                "options" => [
                    "regexp" => '/^[a-zA-Z]+([ -][a-zA-Z]+)*$/',
                    "message" => 'Invalid characters or format in the last name!'
                ]
            ],
            "dob" => [
                "filter" => FILTER_VALIDATE_REGEXP,
                "options" => [
                    "regexp" => '/^(\d{4})(-(0[1-9]|1[0-2])(-(0[1-9]|[12][0-9]|3[01]))?)?$/',
                    "message" => 'Invalid date format.'
                ]
            ],
            "phone" => [
                "filter" => FILTER_VALIDATE_REGEXP,
                "options" => [
                    "regexp" => '/^(?:(?:\+\d{1,3}|\(\d{1,4}\)|\d{1,4})[\s-]?)?(\(\d{3}\)\s?\d{8}|\d{10})$/',
                    "message" => 'Invalid Pakistani phone number'
                ]
            ],
            "email" => [
                "filter" => FILTER_VALIDATE_REGEXP,
                "options" => [
                    "regexp" => '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/',
                    "message" => 'Invalid email address'
                ]
            ],
        ];
        return $filterOptions;
    }

    // specific field base validation with error message
    public function validateInput($input) {
        $filterOptions = $this->getFilterOptions();
        $flags = [
            'flags' => FILTER_REQUIRE_SCALAR,
            'options' => []
        ];

        $result = filter_var_array($input, $filterOptions, false);

        $errors = [];
        foreach ($result as $key => $value) {
            if ($value === false) {
                $errors[$key] = $filterOptions[$key]['options']['message'];
            }
        }

        return ['result' => $result, 'errors' => $errors];
    }

    // validations errors like required and filter base errors messages
    private function getValidationErrors($data,$formData)
    {
        $filterOptions = $this->getFilterOptions();
        $errors = [];

        foreach ($data as $field => $value) {
            if (empty($value) && empty($formData[$field])) {
                $fieldName = $this->getFieldName($field);
                $errors[$field] = $fieldName. " is required!. ";
            }elseif(empty($value) && !empty($formData[$field])){
                $errors[$field] = $filterOptions[$field]['options']['message'];
            }elseif($field == 'dob' && !empty($value)){
                $dobErr = $this->dateValidation($value);
                if(!empty($dobErr)){
                    $errors[$field] = $dobErr;
                }
            }
        }

        return $errors;
    }

    // get a field name for error message, for example: 'firstName to First Name';
    private function getFieldName($field)
    {
        switch ($field) {
            case 'firstName':
                return 'First Name';
            case 'lastName':
                return 'Last Name';
            case 'dob':
                return 'Date Of Birth';
            case 'phone':
                return 'Pakistani phone number';
            case 'email':
                return 'Email address';
            default:
                return 'Unknown error';
        }
    }

    // date of birth base specific/custom validation
    public function dateValidation($dob){
        $dateError = '';
        $pattern = '/^(\d{4})(-(0[1-9]|1[0-2])(-(0[1-9]|[12][0-9]|3[01]))?)?$/';
        if (preg_match($pattern,$dob, $matches)) {
            $year = (int)$matches[1];

            // Check if the year is within a valid range
            if ($year >= 1900 && $year <= 9999) {
                $month = isset($matches[3]) ? (int)ltrim($matches[3], '0') : 0;
                $month = preg_replace('/[-\s]+/', '', $month);
                // Check if the month is valid
                if ($month > 12) {
                    $dateError = 'Month should not be greater than 12.';
                }

                $day = isset($matches[4]) ? (int)ltrim($matches[4], '0') : 0;
                $day = preg_replace('/[-\s]+/', '', $day);
                // Check if the day is valid
                if($month > 0){
                    $dateBaseMonthDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);

                    if ($day > 0 && $day > $dateBaseMonthDays) {
                        $dateError = 'Enter a valid day for the selected month.';
                    }
                    // Check if the date is at least 18 years ago
                    $currentYear = date('Y');
                    $minimumBirthYear = $currentYear - 18;
                    if ($year > $minimumBirthYear) {
                        $dateError = 'Age must be at least 18 years.';
                    }
                }
            } else {
                $dateError = 'Year should be 1900 or greater.';
            }
        } else {
            $dateError = 'Invalid date format.';
        }
        return $dateError;
    }
}

// Usage Example:
$userOperations = new UserController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    switch ($action) {
        case 'add':
            // New user record
            $userOperations->addUser();
            break;
        case 'delete':
            // delete user
            $userOperations->deleteUser();
            break;
        case 'updateUser':
            // update user
            $userOperations->updateUser();
            break;
        case 'updateAllUsers':
            // update all users on the base of local storage
            $userOperations->updateAllUsers();
            break;
        case 'insertAllUsers':
            // update all users on the base of local storage
            $userOperations->insertAllUsers();
            break;
        case 'deleteAllUsers':
            // update all users on the base of local storage
            $userOperations->deleteAllUsers();
            break;
        default:
            // Invalid action
            $userOperations->respondJson(['error' => 'Invalid action'], 400); // Bad Request
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'];

    switch ($action) {
        case 'fetchUsersList':
            // fetch all users
            $userOperations->fetchUsersList();
            break;
        case 'fetchUsersRecord':
            // users record with pagination, sorting and search
            $userOperations->fetchUsersRecord();
            break;
        case 'getUserDetails':
            // single user details
            $userOperations->getUserDetails();
            break;
        case 'fetchUsersListForDbStatus':
            // single user details
            $userOperations->fetchUsersListForDbStatus();
            break;
        default:
            // Invalid action
            $userOperations->respondJson(['error' => 'Invalid action'], 400); // Bad Request
            break;
    }
} else {
    // Invalid request method
    $userOperations->respondJson(['error' => 'Invalid request method'], 405); // Method Not Allowed
}

