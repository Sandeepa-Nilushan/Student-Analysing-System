<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Users</title>

    <!-- tailwind cdn -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Favicon -->
    <link rel="icon" href="assets/logo.png" type="image/x-icon">
    <link rel="shortcut icon" href="assets/logo.png" type="image/x-icon">

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body id="page-top">
     <?php
    // Initialize variables
    $year = date('Y'); // Default to current year if not provided in URL
    $month = date('n'); // Default to current month if not provided in URL

    // Check if month and year parameters are provided in the URL
    if (isset($_GET['month']) && isset($_GET['year'])) {
        // Sanitize input to prevent SQL injection
        $month = intval($_GET['month']);
        $year = intval($_GET['year']);
    }
?>
    <?php
        // Start the session
        session_start();

        // Check if the "name" session variable is set
        if (isset($_SESSION["name"])) {
            // Output the value of the "name" session variable
            $username = $_SESSION["name"];
            $role = $_SESSION["role"];
        } else {
            // If the "name" session variable is not set, set a default value for $username
            $username = "Guest";
        }
    ?>
    <?php
        // Include config file
        require_once "config.php";

        // Define variables and initialize with empty values
        $firstName = $lastName = $email = $userType = $registrationNumber = $password = $confirmPassword = $grade = "";
        $firstName_err = $lastName_err = $email_err = $userType_err = $password_err = $confirmPassword_err = $grade_err = "";

        // Processing form data when form is submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            // Validate first name
            if (empty(trim($_POST["firstName"]))) {
                $firstName_err = "Please enter your first name.";
            } else {
                $firstName = trim($_POST["firstName"]);
            }

            // Validate last name
            if (empty(trim($_POST["lastName"]))) {
                $lastName_err = "Please enter your last name.";
            } else {
                $lastName = trim($_POST["lastName"]);
            }

            // Validate grade
            if ($userType === 'STUDENT') {
                // Check if grade is empty
                if (empty(trim($_POST["grade"]))) {
                    $grade_err = "Please enter a grade.";
                } else {
                    // Validate grade number
                    $grade = trim($_POST["grade"]);
                    if ($grade < 1 || $grade > 13) {
                        $grade_err = "Grade must be between 1 and 13.";
                    }
                }
            }

            // Validate email
            if (empty(trim($_POST["email"]))) {
                $email_err = "Please enter your email.";
            } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
                $email_err = "Please enter a valid email address.";
            } else {
                // Prepare a select statement
                $sql = "SELECT ID FROM USER WHERE EMAIL = ?";

                if ($stmt = $mysqli->prepare($sql)) {
                    // Bind variables to the prepared statement as parameters
                    $stmt->bind_param("s", $param_email);

                    // Set parameters
                    $param_email = trim($_POST["email"]);

                    // Attempt to execute the prepared statement
                    if ($stmt->execute()) {
                        // Store result
                        $stmt->store_result();

                        if ($stmt->num_rows == 1) {
                            $email_err = "This email is already taken.";
                        } else {
                            $email = trim($_POST["email"]);
                        }
                    } else {
                        echo "Oops! Something went wrong. Please try again later.";
                    }

                    // Close statement
                    $stmt->close();
                }
            }

            // Validate user type
            if (empty(trim($_POST["userType"]))) {
                $userType_err = "Please select a user type.";
            } else {
                $userType = trim($_POST["userType"]);
            }

            // Validate registration number if applicable
            if (($userType === 'STUDENT' || $userType === 'PARENT') && empty(trim($_POST["registrationNumber"]))) {
                $registrationNumber_err = "Please enter your registration number.";
            } else {
                // Prepare a select statement
                $sql = "SELECT ID FROM USER WHERE REGISTRATION_NO = ? AND USER_TYPE = 'STUDENT'";

                if ($stmt = $mysqli->prepare($sql)) {
                    // Bind variables to the prepared statement as parameters
                    $stmt->bind_param("s", $param_registrationNumber);

                    // Set parameters
                    $param_registrationNumber = trim($_POST["registrationNumber"]);

                    // Attempt to execute the prepared statement
                    if ($stmt->execute()) {
                        // Store result
                        $stmt->store_result();

                        if ($stmt->num_rows == 1 && $userType === 'STUDENT') {
                            $registrationNumber_err = "This Registration Number is already taken.";
                        }elseif ($stmt->num_rows != 1 && $userType === 'PARENT') {
                            $registrationNumber_err = "Please register child before add parent";
                        } else {
                            $registrationNumber = trim($_POST["registrationNumber"]);
                        }
                    } else {
                        echo "Oops! Something went wrong. Please try again later.";
                    }

                    // Close statement
                    $stmt->close();
                }
            }

            // Validate password
            if (empty(trim($_POST["password"]))) {
                $password_err = "Please enter a password.";
            } elseif (strlen(trim($_POST["password"])) < 8) {
                $password_err = "Password must have at least 8 characters.";
            } else {
                $password = trim($_POST["password"]);
            }

            // Validate confirm password
            if (empty(trim($_POST["confirmPassword"]))) {
                $confirmPassword_err = "Please confirm password.";
            } else {
                $confirmPassword = trim($_POST["confirmPassword"]);
                if (empty($password_err) && ($password != $confirmPassword)) {
                    $confirmPassword_err = "Password did not match.";
                }
            }

            // Check input errors before inserting into database
            if (empty($firstName_err) && empty($lastName_err) && empty($email_err) && empty($userType_err) && empty($registrationNumber_err) && empty($password_err) && empty($confirmPassword_err) && empty($grade_err)) {

                // Prepare an insert statement
                $sql = "INSERT INTO USER (FIRST_NAME, LAST_NAME, EMAIL, USER_TYPE, REGISTRATION_NO, PASSWORD, GRADE) VALUES (?, ?, ?, ?, ?, ?, ?)";

                if ($stmt = $mysqli->prepare($sql)) {
                    // Bind variables to the prepared statement as parameters
                    $stmt->bind_param("sssssss", $param_firstName, $param_lastName, $param_email, $param_userType, $param_registrationNumber, $param_password, $param_grade);

                    // Set parameters
                    $param_firstName = $firstName;
                    $param_lastName = $lastName;
                    $param_email = $email;
                    $param_userType = $userType;
                    $param_registrationNumber = $registrationNumber;
                    $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
                    $param_grade = $grade;

                    // Attempt to execute the prepared statement
                    if ($stmt->execute()) {
                        // Display success alert
                        echo '<div class="alert alert-success alert-dismissible fade show position-fixed w-100" style="top: 0; z-index: 9999;" role="alert">
                                Your account has been registered successfully.
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                                </button>
                            </div>';
                        // clear data
                        $firstName = $lastName = $email = $userType = $registrationNumber = $password = $confirmPassword = $grade = "";
                        $firstName_err = $lastName_err = $email_err = $userType_err = $password_err = $confirmPassword_err = $grade_err = "";
                    } else {
                        // Display error alert
                        echo '<div class="alert alert-danger alert-dismissible fade show position-fixed w-100" style="top: 0; z-index: 9999;" role="alert">
                            An error occurred. Please try again later.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>';
                    }

                    // Close statement
                    $stmt->close();
                }
            }
        }
    ?>
    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.html">
                <div class="">
                   <img src="assets/logo.png" alt="Logo" style="display: block; margin: 0 auto; width: 32px; height: 32px;">
                </div>
                <div class="sidebar-brand-text mx-3">StudentHub</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <?php

                if ($role == 'ADMIN') {
                    // Display the <li> element only if the user's role is 'ADMIN'
            ?>
                <!-- Nav Item - Dashboard -->
                <li class="nav-item">
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-fw fa-solid fa-users"></i>
                        <span>Users</span></a>
                </li>
            <?php
                }
            ?>

            <?php
                if ($role == 'ADMIN') {
                    // Display the <li> element only if the user's role is 'ADMIN'
            ?>
                <!-- Nav Item - results -->
                <li class="nav-item">
                    <a class="nav-link" href="results.php">
                        <i class="fas fa-fw fa-solid fa-marker"></i>
                        <span>Results</span></a>
                </li>
            <?php
                }
            ?>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <div class="d-flex flex-column align-items-center justify-content-center" style="">
                                    <span class="mr-2 d-none d-lg-inline text-gray-600 large"><?php echo $username; ?></span>
                                    <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $role; ?></span>
                                </div>
                                <img class="img-profile rounded-circle"
                                    src="data:image/png;base64,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">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <h1 class="h3 mb-4 text-gray-800">User Management</h1>

                    <div>
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4">Create Account</h1>
                            </div>
                            <form class="user" action="users.php" method="post">
                                <div class="form-group row">
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                        <input type="text" class="form-control form-control-user <?php echo (!empty($firstName_err)) ? 'is-invalid' : ''; ?>"
                                            id="exampleFirstName" placeholder="First Name" name="firstName" value="<?php echo $firstName; ?>">
                                        <span class="invalid-feedback"><?php echo $firstName_err; ?></span>
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control form-control-user <?php echo (!empty($lastName_err)) ? 'is-invalid' : ''; ?>"
                                            id="exampleLastName" placeholder="Last Name" name="lastName" value="<?php echo $lastName; ?>">
                                        <span class="invalid-feedback"><?php echo $lastName_err; ?></span>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                        <input type="email" class="form-control form-control-user <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>"
                                            id="exampleInputEmail" aria-describedby="emailHelp"
                                            placeholder="Enter Email Address..." name="email" value="<?php echo $email; ?>">
                                        <span class="invalid-feedback"><?php echo $email_err; ?></span>
                                    </div>
                                    <div class="col-sm-6">
                                        <select class="form-control <?php echo (!empty($userType_err)) ? 'is-invalid' : ''; ?>" id="userType" name="userType" style="font-size: 0.8rem; border-radius: 10rem; padding-left: 1rem; height: 3rem;">
                                            <option value="">Select User Type</option>
                                            <option value="TEACHER" <?php echo ($userType == 'TEACHER') ? 'selected' : ''; ?>>TEACHER</option>
                                            <option value="STUDENT" <?php echo ($userType == 'STUDENT') ? 'selected' : ''; ?>>STUDENT</option>
                                            <option value="PARENT" <?php echo ($userType == 'PARENT') ? 'selected' : ''; ?>>PARENT</option>
                                        </select>
                                        <span class="invalid-feedback"><?php echo $userType_err; ?></span>
                                    </div>
                                </div>
                                <div class="form-group row" id="registrationNumberField" style="<?php echo (($userType === 'STUDENT' || $userType === 'PARENT') && empty($registrationNumber_err)) ? 'display:block;' : 'display:none;'; ?>">
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                        <input type="text" class="form-control form-control-user <?php echo (!empty($registrationNumber_err)) ? 'is-invalid' : ''; ?>" id="registrationNumber" name="registrationNumber" placeholder="<?php echo ($userType === 'PARENT') ? 'Child Registration No' : 'Registration No'; ?>" value="<?php echo $registrationNumber; ?>">
                                        <span class="invalid-feedback"><?php echo $registrationNumber_err; ?></span>
                                    </div>
                                </div>
                                <div class="form-group row" id="gradeField" style="<?php echo ($userType === 'STUDENT' && empty($grade_err)) ? 'display:block;' : 'display:none;'; ?>">
                                        <div class="col-sm-6 mb-3 mb-sm-0">
                                            <input type="number" class="form-control form-control-user <?php echo (!empty($grade_err)) ? 'is-invalid' : ''; ?>" id="grade" name="grade" placeholder="Grade (1-13)" value="<?php echo $grade; ?>" min="1" max="13">
                                            <span class="invalid-feedback"><?php echo $grade_err; ?></span>
                                        </div>
                                    </div>
                                <div class="form-group row">
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                        <input type="password" class="form-control form-control-user <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>"
                                            id="exampleInputPassword" placeholder="Password" name="password" value="<?php echo $password; ?>">
                                        <span class="invalid-feedback"><?php echo $password_err; ?></span>
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="password" class="form-control form-control-user <?php echo (!empty($confirmPassword_err)) ? 'is-invalid' : ''; ?>"
                                            id="exampleRepeatPassword" placeholder="Repeat Password" name="confirmPassword" value="<?php echo $confirmPassword; ?>">
                                        <span class="invalid-feedback"><?php echo $confirmPassword_err; ?></span>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-user btn-block">Register Account</button>
                            </form>
                            <hr>

                            <div class="card shadow mb-4 mt-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Users</h6>
                                </div>
                                <div class="card-body">
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get">
                                <div class="form-row">
                                <div class="col-md-3 mb-3">
                                    <label for="month">Month:</label>
                                    <select class="form-control" id="month" name="month">
                                        <?php
                                        // Generate options for months
                                        for ($i = 1; $i <= 12; $i++) {
                                            $selected = ($i == $month) ? "selected" : "";
                                            echo "<option value='$i' $selected>" . date("F", mktime(0, 0, 0, $i, 1)) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="year">Year:</label>
                                    <input type="number" class="form-control" id="year" name="year" value="<?php echo $year; ?>" min="1900" max="2100">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <button id="generatePdfBtn" class="btn btn-success mt-4">Generate Attendence PDF</button>
                                </div>
                            </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Name</th>
                                                    <th>User Type</th>
                                                    <th>Email</th>
                                                    <th>Registartion No</th>
                                                    <th>Grade</th>
                                                    <th>Generate Result Sheet</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    // Include config file
                                                    require_once "config.php";

                                                    // Prepare a select statement
                                                    $sql = "SELECT ID, FIRST_NAME, LAST_NAME, USER_TYPE, EMAIL, REGISTRATION_NO, GRADE FROM USER";
                                                    $result = $mysqli->query($sql);

                                                    if ($result->num_rows > 0) {
                                                        // Output data of each row
                                                        while ($row = $result->fetch_assoc()) {
                                                            echo "<tr>";
                                                            echo "<td>" . $row["ID"] . "</td>";
                                                            echo "<td>" . $row["FIRST_NAME"] . " " . $row["LAST_NAME"] . "</td>";
                                                            echo "<td>" . $row["USER_TYPE"] . "</td>";
                                                            echo "<td>" . $row["EMAIL"] . "</td>";
                                                            echo "<td>" . $row["REGISTRATION_NO"] . "</td>";
                                                            echo "<td>" . $row["GRADE"] . "</td>";
                                                            // Check if USER_TYPE is STUDENT
                                                            if ($row["USER_TYPE"] === "STUDENT") {
                                                                echo "<td><a href='generate_result_sheet.php?student_id=" . $row["ID"] . "' class='btn btn-primary'>Generate Result Sheet</a></td>";
                                                            } else {
                                                                echo "<td></td>"; // Empty cell if not a student
                                                            }
                                                            echo "</tr>";
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan='5'>No users found</td></tr>";
                                                    }

                                                    // Close connection
                                                    $mysqli->close();
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                        </div>

                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; StudentHub 2024</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <script>
    // Function to handle visibility of registration number field based on user type
    function toggleRegistrationNumberField() {
        var userType = document.getElementById('userType').value;
        var registrationNumberField = document.getElementById('registrationNumberField');
        var gradeField = document.getElementById('gradeField');

        if (userType === 'STUDENT' || userType === 'PARENT') {
            if (userType === 'STUDENT') {
                gradeField.style.display = 'block';
            } else {
                gradeField.style.display = 'none';
            }
            registrationNumberField.style.display = 'block';
        } else {
            registrationNumberField.style.display = 'none';
        }
    }

    // Call the function initially and whenever user type selection changes
    toggleRegistrationNumberField();
    document.getElementById('userType').addEventListener('change', toggleRegistrationNumberField);
</script>

<script>
    document.getElementById("generatePdfBtn").addEventListener("click", function() {
        // Get selected month and year
        var month = document.getElementById("month").value;
        var year = document.getElementById("year").value;

        // Construct URL with selected month and year
        var url = "generate_pdf.php?month=" + month + "&year=" + year;

        // Redirect to the generated PDF
        window.open(url, "_blank");
    });
</script>

</body>
</html>