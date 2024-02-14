<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment</title>
    <!-- Include Bootstrap CSS and jQuery -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <!-- custom styles -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-5">
        <!-- success/warning alerts -->
            <div id="message-container">
                <div class="message success" style="display:none;">
                    <p>Success message goes here.</p>
                    <span class="close-btn close-btn-alert">X</span>
                </div>
                <div class="message warning" style="display:none;">
                    <p>Warning message goes here.</p>
                    <span class="close-btn close-btn-alert">X</span>
                </div>
                <div class="message danger" style="display:none;">
                    <p>Warning message goes here.</p>
                    <span class="close-btn close-btn-alert">X</span>
                </div>
            </div>
        <!-- end success/warning alerts -->

        <!-- Add User Button -->
            <button type="button" class="btn btn-primary addNewUserModal" data-toggle="modal" data-target="#addNewUserModal">
                Add User
            </button>
        <!-- End Add User Button -->

        <!-- Search Bar -->
            <div class="input-group mt-3">
                <input type="text" class="form-control" id="searchInput" placeholder="Search">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" id="searchButton">Search</button>
                </div>
            </div>
        <!-- End Search Bar -->

        <!-- Users Table -->
            <table class="table mt-3">
                <thead>
                <tr>
                    <th>ID</th>
                    <th><a href="#" class="sort" data-column="firstName">First Name</a></th>
                    <th><a href="#" class="sort" data-column="lastName">Last Name</a></th>
                    <th><a href="#" class="sort" data-column="dob">DOB</a></th>
                    <th><a href="#" class="sort" data-column="phone">Phone</a></th>
                    <th><a href="#" class="sort" data-column="email">Email</a></th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody id="userTableBody">
                <!-- User records will be dynamically added here -->
                </tbody>
            </table>
        <!-- end Users table -->

        <!-- Pagination -->
            <div class="pagination-container">
                <ul class="pagination" id="pagination">
                    <!-- Pagination links will be added here dynamically -->
                </ul>
            </div>
        <!-- end pagination -->
    </div>

    <!-- Add/Edit User Modal -->
        <div class="modal addUserModal" id="addUserModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add User</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Add/Edit User Form -->
                        <form id="addUserForm">
                            <!-- in this hidden input tab, will be our user id for update record -->
                            <input type="hidden" id="editUserId" name="editUserId">
                            <div class="form-group">
                                <label for="firstName">First Name*</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" placeholder="John">
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name*</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Nokes">
                            </div>
                            <div class="form-group">
                                <label for="dob">Date of Birth*</label>
                                <input type="text" class="form-control" id="dob" name="dob" placeholder="2000-12-25">
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone*</label>
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="+923076069760">
                            </div>
                            <div class="form-group">
                                <label for="email">Email*</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="john@gmail.com">
                            </div>
                            <!-- Error Container -->
                                <div id="errorContainer" class="alert alert-danger" style="display: none;"></div>
                            <!-- end Error Container -->
                            <!-- Button for Add new user -->
                            <button type="button" class="btn btn-primary add-user">Add User</button>
                            <!-- Button for Update a Existing User -->
                            <button type="button" class="btn btn-primary update-user">Update User</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <!-- end Add/Edit User Model -->


    <!-- Include your custom JavaScript file -->
    <script src="js/script.js"></script>
</body>
</html>
