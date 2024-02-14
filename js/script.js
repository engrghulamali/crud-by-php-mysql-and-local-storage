$(document).ready(function () {
    // Pagination and Sorting
    var currentPage = 1; // Default current page
    var usersPerPage = 5; // Default records per page
    var sortColumn = 'id'; // Default sort column
    var sortDirection = 'desc'; // Default sort direction
    var usersList = [];  // defult usersList
    var usersCount = 0;  //default user count is zero, this represents the totalUsers Count from database
    var  searchInput = ''; //defult search
    localStorage.clear();
    // search button
    $('#searchButton').on('click', function (e) {
        searchInput = $('#searchInput').val();
        searchInput = searchInput.trim(); // Remove spaces from the start and end of the word
        var regexPattern = /^[^<>?/:;]+$/;
        if (containsSqlInjection(searchInput)) {
            showMessage('warning', 'Warning: Enter a valid record!');
            return false;
        }else if(regexPattern.test(searchInput) || searchInput == ''){
            currentPage = 1;
            usersPerPage = 5;
            sortColumn = 'id';
            sortDirection = 'desc';
            loadUserRecords();
        } else {
            searchInput = '';
            showMessage('warning', 'Warning: Enter a valid record!');
            return false;
        }
    });

    // Function to fetch user records and update the table
    function loadUserRecords() {
        $.ajax({
            url: 'services/UserController.php',
            type: 'GET',
            data: {
                page: currentPage,
                perPage: usersPerPage,
                sortColumn: sortColumn,
                sortDirection: sortDirection,
                search: searchInput,
                action: 'fetchUsersRecord'
            },
            dataType: 'json',
            success: function (response) {
                var usersRecord = response.users;
                var totalRecords = response.totalUsers || 0;
                usersCount = totalRecords;
                // Clear existing table rows
                $('#userTableBody').empty();
                updatePaginationLinks(0);
                if (usersRecord.length > 0) {
                    var totalPages = Math.ceil(totalRecords / usersPerPage);
                    if(totalRecords > usersPerPage){
                        updatePaginationLinks(totalPages);
                    }

                    // Populate table with new records
                    usersRecord.forEach(function (user) {
                        var newRow = `<tr>
                            <td>${user.id}</td>
                            <td>${user.firstName}</td>
                            <td>${user.lastName}</td>
                            <td>${user.dob}</td>
                            <td>${user.phone}</td>
                            <td>${user.email}</td>
                            <td>
                                <button class="btn btn-info btn-sm edit-btn">Edit</button>
                                <button class="btn btn-danger btn-sm del-btn">Delete</button>
                            </td>
                        </tr>`;
                        $('#userTableBody').append(newRow);
                    });
                } else {
                    $('#userTableBody').append(`<tr class="empty-table"><td colspan="12">Record Not Found</td></tr>`);
                }
            },
            error: function (error) {
                console.error('Error fetching user records:', error);
                showMessage('danger', 'Something went wrong!');
            }
        });
    }

    // Initial load of user records
    loadUserRecords();

    // hide ereros, alerts type popups
    setInterval(function () {
        $('#errorContainer').css({'display':'none'});
    }, 6000); // Check for changes every 6 second

    // Pagination click event
    $('#pagination').on('click', 'a.page-link', function (e) {
        e.preventDefault();
        currentPage = parseInt($(this).text());
        loadUserRecords();
    });


    // Sorting click event
    $('.sort').on('click', function (e) {
        e.preventDefault();
        var column = $(this).data('column');
        // Toggle sort direction
        if (sortColumn === column) {
            sortDirection = (sortDirection === 'asc') ? 'desc' : 'asc';
        } else {
            sortColumn = column;
            sortDirection = 'asc';
        }
        loadUserRecords();
    });

    // Function to update pagination links
    function updatePaginationLinks(totalPages = 0) {
        $('.pagination').empty();
        var roundedPages = Math.floor(totalPages) + Math.ceil(totalPages % 1);
        for (var i = 1; i <= roundedPages; i++) {
            var activeClass = (i === currentPage) ? 'active' : '';
            var pageLink = '<li class="page-item ' + activeClass + '"><a class="page-link" href="#">' + i + '</a></li>';
            $('.pagination').append(pageLink);
        }
    }

    // model for new user or update user
    $('.addNewUserModal').on('click', function () {
        resetErrorMessages();
        // Reset form fields and error messages
        $('.modal-title').text('Add User');
        $('.update-user').css({'display':'none'});
        $('.add-user').css({'display':'block'});
        $('#addUserForm')[0].reset();
        $('#addUserModal').modal('show');
        $('#errorContainer').text('');
        $('#errorContainer').css({'display':'none'});
    });

    // AJAX for Adding User
    $('.add-user').on('click', function () {
        // Reset error messages
        resetErrorMessages();
        // Validate form fields
        var validationErrors = validateForm();
        // var validationErrors = [];
        if (validationErrors.length > 0) {
            // all validation errors
            if (validationErrors.length > 0) {
                // Display errors next to corresponding fields
                validationErrors.forEach(function (error) {
                    var fieldId = error.field;
                    $('#' + fieldId).closest('.form-group').addClass('has-error');
                    $('#' + fieldId).after('<span class="help-block">' + error.message + '</span>');
                });
                return;
            }
        }
        // Validate form fields
        var firstName = $('#firstName').val().trim();
        var lastName = $('#lastName').val().trim();
        var dob = $('#dob').val().trim();
        var phone = $('#phone').val().trim();
        var email = $('#email').val().trim();
        $.ajax({
            url: 'services/UserController.php',
            method: 'POST',
            data: {
                firstName: firstName,
                lastName: lastName,
                dob: dob,
                phone: phone,
                email: email,
                action: 'add'
            },
            success: function (response) {
                action = "New User Created Succesfully!"
                responseMessage(response,action);
                if(response.status == 'success'){
                    $('#addUserModal').modal('hide');
                    loadUserRecords();
                }else{
                    var errorsList = response.errors;
                    if (Object.keys(errorsList).length > 0) {
                        // Clear previous error messages
                        resetErrorMessages();

                        // Display errors next to corresponding fields
                        Object.keys(errorsList).forEach(function (field) {
                            var errorMessage = errorsList[field];
                            var $field = $('#' + field);

                            // Check if the field exists in the form
                            if ($field.length > 0) {
                                $field.closest('.form-group').addClass('has-error');
                                $field.after('<span class="help-block">' + errorMessage + '</span>');
                            } else {
                                console.error('Field not found in the form:', field);
                            }
                        });
                        return;
                    }
                }
            },
            error: function (xhr, status, error) {
                showMessage('warning', 'Something went wrong!');
            }
        });
    });

    // AJAX for updating User
    $('.update-user').on('click', function () {
        // Reset error messages
        resetErrorMessages();
         // Validate form fields
        var validationErrors = validateForm();
        if (validationErrors.length > 0) {
            // Display errors next to corresponding fields
            validationErrors.forEach(function (error) {
                var fieldId = error.field;
                $('#' + fieldId).closest('.form-group').addClass('has-error');
                $('#' + fieldId).after('<span class="help-block">' + error.message + '</span>');
            });
            return;
        }
        var editUserId = $('#editUserId').val();
        if (editUserId == '') {
            showMessage('danger', 'User Not Exist!');
        }
        var firstName = $('#firstName').val().trim();
        var lastName = $('#lastName').val().trim();
        var dob = $('#dob').val().trim();
        var phone = $('#phone').val().trim();
        var email = $('#email').val().trim();
        $.ajax({
            url: 'services/UserController.php',
            method: 'POST',
            data: {
                firstName: firstName,
                lastName: lastName,
                dob: dob,
                phone: phone,
                email: email,
                userId: editUserId,
                action: 'updateUser'
            },
            success: function (response) {
                // Handle success
                if(response.status == 'success'){
                    $('#addUserModal').modal('hide');
                    showMessage('success', response.message);
                    loadUserRecords();
                } else if((response.status == 'error') && response.message == 'User does not exist'){
                    showMessage('danger', 'User does not exist!');
                }
                else{
                    var errorsList = response.errors;
                    if (Object.keys(errorsList).length > 0) {
                        // Clear previous error messages
                        resetErrorMessages();

                        // Display errors next to corresponding fields
                        Object.keys(errorsList).forEach(function (field) {
                            var errorMessage = errorsList[field];
                            var $field = $('#' + field);

                            // Check if the field exists in the form
                            if ($field.length > 0) {
                                $field.closest('.form-group').addClass('has-error');
                                $field.after('<span class="help-block">' + errorMessage + '</span>');
                            } else {
                                console.error('Field not found in the form:', field);
                            }
                        });
                        return;
                    }
                }
            },
            error: function (xhr, status, error) {
                showMessage('warning', 'Something went wrong!');
            }
        });
    });

    // Use event delegation to handle button clicks
    $('#userTableBody').on('click', '.edit-btn', function () {
        var userId = $(this).closest('tr').find('td:first').text();
        editUser(userId);
    });
    $('#userTableBody').on('click', '.del-btn', function () {
        var userId = $(this).closest('tr').find('td:first').text();
        deleteUser(userId);
    });

    // Function to handle delete user
    function deleteUser(userId) {
        if (confirm('Are you sure you want to delete this user?')) {
            // Perform AJAX request to delete user by ID
            $.ajax({
                url: 'services/UserController.php',
                method: 'POST',
                data: {
                    userId: userId,
                    action: 'delete'
                },
                success: function (response) {
                    // Remove the deleted user from local storage
                    action = "User Deleted Succesfully!"
                    responseMessage(response,action);
                    if(response.status == 'success'){
                        loadUserRecords();
                    }
                },
                error: function (xhr, status, error) {
                    // Handle error
                    showMessage('warning', 'Something went wrong!');
                }
            });
        }
    }

    // popup/alert message for success/warning etc
    function responseMessage(response, action){
        if(response.status == 'success'){
            showMessage('success', action);
        }else if(response.status == 'error'){
            showMessage('danger', response.message);
        }else if(response.message == 'Validation failed'){
        }else{
            showMessage('danger', 'Something went wrong!');
        }
    }

    // Function to handle edit user
    function editUser(userId) {
        resetErrorMessages();
        $.ajax({
            url: 'services/UserController.php',
            method: 'GET',
            data: {
                userId: userId,
                action: 'getUserDetails'
             },
            success: function (response) {
                if(response.status == 'success'){
                    var userDetails = response.userDetails['0'];
                    // Populate the modal with user details for editing
                    $('#editUserId').val(userDetails.id);
                    $('#firstName').val(userDetails.firstName);
                    $('#lastName').val(userDetails.lastName);
                    $('#dob').val(userDetails.dob);
                    $('#phone').val(userDetails.phone);
                    $('#email').val(userDetails.email);
                    $('.modal-title').text('Edit User');
                    $('.update-user').css({'display':'block'});
                    $('.add-user').css({'display':'none'});

                    // Show the edit modal
                    $('#errorContainer').text('');
                    $('#errorContainer').css({'display':'none'});
                    $('#addUserModal').modal('show');
                }else{
                    showMessage('danger', response.message);
                }
            },
            error: function (xhr, status, error) {
                showMessage('danger', 'Something went wrong!');
            }
        });
    }

    // client side validation form for edit/create users record.
    function validateForm() {
        var errors = [];
        var firstName = $('#firstName').val();
        var lastName = $('#lastName').val();
        var dob = $('#dob').val();
        var phone = $('#phone').val();
        var email = $('#email').val();
        // first name validation
        firstName = firstName.trim(); // Remove spaces from the start and end of the word
        // Check for the presence of the first name
        if (firstName.length === 0) {
            errors.push({ field: 'firstName', message: 'First name is required!' });
        } else {
            // Check if the first name contains only letters
            if (!/^[a-zA-Z]+([ -][a-zA-Z]+)*$/.test(firstName)) {
                errors.push({ field: 'firstName', message: 'Invalid characters or format in the First name!' });
            }else if(containsSqlInjection(firstName)){
                errors.push({ field: 'firstName', message: 'Invalid data in the First name!!' });
            }else if(firstName.replace(/[\s-]/g, '').length < 3){
            // Check if the first name meets the minimum length requirement
                errors.push({ field: 'firstName', message: 'First name must have at least three characters!' });
            }
        }

        // last name validation
        lastName = lastName.trim();
        if (lastName.length === 0) {
            errors.push({ field: 'lastName', message: 'Last name is required!' });
        } else {
            if (!/^[a-zA-Z]+([ -][a-zA-Z]+)*$/.test(lastName)) {
                errors.push({ field: 'lastName', message: 'Invalid characters or format in the Last name!' });
            }else if(containsSqlInjection(lastName)){
                errors.push({ field: 'lastName', message: 'Invalid data in the Last name!!' });
            }else if(lastName.replace(/[\s-]/g, '').length < 3){
            // Check if the first name meets the minimum length requirement
                errors.push({ field: 'lastName', message: 'Last name must have at least three characters!' });
            }
        }

        // Check if DOB is not empty and is a valid date
        var dobDate = new Date(dob);
        var currentDate = new Date();
        var minAgeDate = new Date(currentDate.getFullYear() - 18, currentDate.getMonth(), currentDate.getDate());

        // Check if DOB is not empty and is a valid date
        if (!dob || dobDate.toString() === 'Invalid Date') {
            errors.push({ field: 'dob', message: 'Please select a valid date of birth.' });
        } else {
                if (!/^\d{4}(?:-(0[1-9]|1[0-2])(?:-(0[1-9]|[12][0-9]|3[01]))?)?$|^(0[1-9]|[12][0-9]|3[01])-(0[1-9]|1[0-2])-\d{4}$/.test(dob)) {
                errors.push({
                    field: 'dob',
                    message: 'Invalid date format. Please use YYYY or YYYY-MM or YYYY-MM-DD or DD-MM-YYYY.'
                });
            } else {
                const [year, month, day] = dob.split(/[-]/).map(Number);
                // Check if the day is valid for the given month
                const maxDaysInMonth = new Date(year, month, 0).getDate();
                if (day > maxDaysInMonth) {
                    errors.push({
                        field: 'dob',
                        message: `Invalid day for the selected month. Please enter a day between 1 and ${maxDaysInMonth}.`
                    });
                }
                // Check if the age is less than 18 years
                if (dobDate > minAgeDate) {
                    errors.push({ field: 'dob', message: 'Minimum age must be 18 years or older.' });
                }

                // Check if the year is not less than 1900
                if (dobDate.getFullYear() < 1900) {
                    errors.push({ field: 'dob', message: 'Invalid year. Year should be 1900 or later.' });
                }
            }
        }

        // phone number validation
        phone = phone.trim();
        if (phone.length === 0) {
            errors.push({ field: 'phone', message: 'Phone is required!' });
        }else{
            const phoneRegex = /^(?:(?:\+\d{1,3}|\(\d{1,4}\)|\d{1,4})[\s-]?)?(\(\d{3}\)\s?\d{8}|\d{10})$/;
            if (!phoneRegex.test(phone)) {
                errors.push({ field: 'phone', message: 'Invalid Pakistani phone number' });
            }
        }

        // email validation
        email = email.trim();
        if (email.length === 0) {
            errors.push({ field: 'email', message: 'Email is required!' });
        }else{
            var emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
            if (!emailRegex.test(email)) {
                errors.push({ field: 'email', message: 'Invalid email address' });
            }
        }

        return errors;
    }

    // reset error message on add/edit user model
    function resetErrorMessages() {
        $('.form-group').removeClass('has-error');
        $('.help-block').remove();
    }

    // Function to remove a user from local storage based on the user ID
    function removeUserFromLocalStorage(userId) {
        // Retrieve existing users from local storage
        var usersFromLocalStorage = localStorage.getItem('users');
        var storedUsers = usersFromLocalStorage ? JSON.parse(usersFromLocalStorage) : [];
        // Find the index of the user with the specified ID
        var userIndex = storedUsers.findIndex(function (user) {
            if (Array.isArray(user)) {
                return user[0] === userId;
            } else if (typeof user === 'object' && user.id) {
                return user.id === userId;
            }

            return false;
        });

        // If the user is found, remove it from the array
        if (userIndex !== -1) {
            storedUsers.splice(userIndex, 1);
            // Store the updated array back in local storage
            localStorage.setItem('users', JSON.stringify(storedUsers));
            console.log("#3 removeUserFromLocalStorage");
        }
    }

    // sync/update a record in local storage from database
    function updateUserInLocalStorageFromDb(userId, updatedUser) {
        var usersFromLocalStorage = localStorage.getItem('users');
        var storedUsers = usersFromLocalStorage ? JSON.parse(usersFromLocalStorage) : [];
        var userIndex = storedUsers.findIndex(function (user) {
            if(user.id === String(userId)){
                return user.id === String(userId);
            }else if(user.id === userId){
                return user.id === userId;
            }
        });

        if (userIndex !== -1) {
            storedUsers[userIndex] = updatedUser;
        } else {
            storedUsers.push(updatedUser);
        }
         console.log("#4 updateUserInLocalStorageFromDb");
        localStorage.setItem('users', JSON.stringify(storedUsers));
    }


    // Function to fetch user records from the database and update local storage
    function syncLocalStorageWithDatabase() {
        $.ajax({
            url: 'services/UserController.php',
            method: 'GET',
            data: {
                action: 'fetchUsersList',
             },
            success: function (response) {
                var allUsersList = response.users;
                usersList = response.users;
                if(allUsersList.length > 0){
                    localStorage.clear();
                    localStorage.setItem('users', JSON.stringify(allUsersList));
                    console.log("#5 syncLocalStorageWithDatabase: local storage updated from db record");
                }
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    }
    function checDbStatus() {
        $.ajax({
            url: 'services/UserController.php',
            method: 'GET',
            data: {
                action: 'fetchUsersListForDbStatus',
             },
            success: function (response) {
                const deletedUsers = response.deletedUsers;
                const updatedUsers = response.updatedUsers;
                console.log("response");
                console.log(response);
                 // delete from local storage
                 if(deletedUsers.length > 0){
                    deleteUserRecordFromLocalStorage(deletedUsers);
                }
                if(updatedUsers.length > 0){
                    var usersFromLocalStorage = localStorage.getItem('users');
                    var localStorageUsersList = usersFromLocalStorage ? JSON.parse(usersFromLocalStorage) : [];
                    if (!arraysAreEqual(updatedUsers, localStorageUsersList)) {
                        // Iterate over each user from the server and update in local storage
                        updatedUsers.forEach(function (serverUser) {
                            updateUserInLocalStorageFromDb(serverUser.id, serverUser);
                        });
                        console.log('Local storage updated with server data.');
                    } else {
                        console.log('Local storage is already up to date.');
                    }
                }
                const currentUsersCount =  response.allUserCount;
                if(usersCount != currentUsersCount || deletedUsers.length > 0 || updatedUsers.length > 0){
                    usersCount =currentUsersCount;
                    loadUserRecords();
                }


            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    }

    // if record is not matched with my array, then remove it from local storage
    function deleteUserRecordFromLocalStorage(allUsersList) {
        console.log("#1 deleteUserRecordFromLocalStorage");
        var usersFromLocalStorage = localStorage.getItem('users');
        var localStorageUsersList = usersFromLocalStorage ? JSON.parse(usersFromLocalStorage) : [];

        // Loop through each user in allUsersList
        allUsersList.forEach(function (userToDelete) {
            // Find the index of the user in localStorageUsersList
            var indexToDelete = localStorageUsersList.findIndex(function (localStorageUser) {
                return localStorageUser.id === userToDelete.id;
            });

            // If the user is found, remove it from the list
            if (indexToDelete !== -1) {
                localStorageUsersList.splice(indexToDelete, 1);
            }
        });

        // Save the updated list back to localStorage
        localStorage.setItem('users', JSON.stringify(localStorageUsersList));
    }

    // Function to check list of users are updated/deleted/inserted in local storage
    function syncDatabaseWithLocalStorage() {
        var usersFromLocalStorage = localStorage.getItem('users');
        var storedUsers = usersFromLocalStorage ? JSON.parse(usersFromLocalStorage) : [];
        const updatedUsers = [];
        var oldUsersList = usersList || [];
        const insertedUsers = [];
        const deletedUsers = [];
        if (oldUsersList.length > 0 ) {
            oldUsersList.forEach(newUser => {
                const storedUser = storedUsers.find(oldUser => oldUser.id === newUser.id);
                // If the user is not found in local storage, consider it as delete
                if (!storedUser) {
                    deletedUsers.push(newUser);
                } else if (JSON.stringify(storedUser) !== JSON.stringify(newUser)) {
                    updatedUsers.push(storedUser);  // Use newUser if found, otherwise storedUser
                }
            });

            // Check for inserted users
            storedUsers.forEach(storedUser => {
                const userInserted = !oldUsersList.some(newUser => newUser.id === storedUser.id);
                if (userInserted) {
                    insertedUsers.push(storedUser);
                }
            });
        }

        if(updatedUsers.length > 0 && storedUsers.length > 0){
            updateAllUsersInDb(updatedUsers);
        }
        else if(insertedUsers.length > 0 && storedUsers.length > 0){
            insertAllUsersInDb(insertedUsers);
        }
        else if(deletedUsers.length > 0){
            deleteAllUsersInDb(deletedUsers);
        }
        else{
            console.log("#2 syncDatabaseWithLocalStorage");
            // localStorage.setItem('users', JSON.stringify(oldUsersList));
            console.log("Already, Record is same with database.");
        }
        if(storedUsers.length === 0){
            syncLocalStorageWithDatabase();
        }
    }

     // list of updated users to update in database
    function updateAllUsersInDb(updatedUsers){
        $.ajax({
            url: 'services/UserController.php',
            method: 'POST',
            data: { users: updatedUsers, action : 'updateAllUsers' },
            success: function (response) {
                console.log("Errors: Insert Users in db by local storage");
                console.log(response.errors);
                console.log("Message: Insert Users in db by local storage");
                console.log(response.message);
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    }

    // list of new users from local storage to save in database
    function insertAllUsersInDb(insertedUsers){
        $.ajax({
            url: 'services/UserController.php',
            method: 'POST',
            data: { users: insertedUsers, action : 'insertAllUsers' },
            success: function (response) {
                console.log("Errors: Insert Users in db by local storage");
                console.log(response.errors);
                console.log("Message: Insert Users in db by local storage");
                console.log(response.message);
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    }

    // list of users deleted from local storage, to delete from database.
    function deleteAllUsersInDb(deletedUsers){
        $.ajax({
            url: 'services/UserController.php',
            method: 'POST',
            data: { users: deletedUsers, action : 'deleteAllUsers' },
            success: function (response) {
                console.log("Errors: Insert Users in db by local storage");
                console.log(response.errors);
                console.log("Message: Insert Users in db by local storage");
                console.log(response.message);
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    }

    // Check if local storage is empty and sync with the database
    if (!localStorage.getItem('users')) {
        syncLocalStorageWithDatabase();
    }

    // Update the database when local storage changes
    window.addEventListener('storage', function (e) {
        try {
            // Code that might throw an exception
            if (e.key === 'users') {
                syncDatabaseWithLocalStorage();
            }
        } catch (error) {
            console.error('An error occurred during storage key press:', error);
        }
    });

    // database changes reflection in local storage
    setInterval(function () {
        // DB status for deleted/updated/new recrods
        checDbStatus();
    }, 5000);

    // Helper function to compare arrays for local storage and db record
    function arraysAreEqual(arr1, arr2) {
        if (arr1.length !== arr2.length) {
            return false;
        }

        for (var i = 0; i < arr1.length; i++) {
            if (JSON.stringify(arr1[i]) !== JSON.stringify(arr2[i])) {
                return false;
            }
        }
        return true;
    }

    // Function to show a message
    function showMessage(type, text) {
       var messageBox = $('<div>').addClass('message ' + type);
       messageBox.append('<p>' + text + '</p>');
       messageBox.append('<span class="close-btn">X</span>');

       $('#message-container').append(messageBox);
       messageBox.fadeIn();
       // Close the message after 6 seconds
       setTimeout(function() {
           messageBox.fadeOut(300, function() {
               $(this).remove();
           });
       }, 5000);
       // Close the message on close button click
       messageBox.find('.close-btn').on('click', function() {
           messageBox.fadeOut(300, function() {
               $(this).remove();
           });
       });
    }

    // Function to check for SQL injection risk
    function containsSqlInjection(input) {
        // Implement your own logic to check for SQL injection risk
        // This is a basic example; you might want to enhance it based on your requirements
        var sqlInjectionKeywords = ['SELECT', 'UPDATE', 'DELETE', 'INSERT', 'DROP', 'ALTER', 'EXEC','SCRIPT','ALERT'];
        return sqlInjectionKeywords.some(function (keyword) {
            return input.toUpperCase().includes(keyword);
        });
    }
});



