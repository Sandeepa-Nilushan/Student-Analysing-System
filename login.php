<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Login</title>

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
<body class="bg-gradient-primary">
    <?php
        // Start the session
        session_start();

        // Check if the user is already logged in, if yes, redirect to dashboard
        if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
            header("location: dashboard.php");
            exit;
        }

        // Include config file
        require_once "config.php";

        // Define variables and initialize with empty values
        $email = $password = "";
        $email_err = $password_err = "";

        // Processing form data when form is submitted
        if($_SERVER["REQUEST_METHOD"] == "POST"){

            // Check if email is empty
            if(empty(trim($_POST["email"]))){
                $email_err = "Please enter your email.";
            } else{
                $email = trim($_POST["email"]);
            }

            // Check if password is empty
            if(empty(trim($_POST["password"]))){
                $password_err = "Please enter your password.";
            } else{
                $password = trim($_POST["password"]);
            }

            // Validate credentials
            if(empty($email_err) && empty($password_err)){
                // Prepare a select statement
                $sql = "SELECT ID, EMAIL, PASSWORD, FIRST_NAME, LAST_NAME, USER_TYPE, REGISTRATION_NO FROM USER WHERE EMAIL = ?";

                if($stmt = $mysqli->prepare($sql)){
                    // Bind variables to the prepared statement as parameters
                    $stmt->bind_param("s", $param_email);

                    // Set parameters
                    $param_email = $email;

                    // Attempt to execute the prepared statement
                    if($stmt->execute()){
                        // Store result
                        $stmt->store_result();

                        // Check if email exists, if yes then verify password
                        if($stmt->num_rows == 1){
                            // Bind result variables
                            $stmt->bind_result($id, $email, $hashed_password, $first_name, $last_name, $user_type, $registration_no);
                            if($stmt->fetch()){
                                if(password_verify($password, $hashed_password)){

                                    // Store data in session variables
                                    $_SESSION["loggedin"] = true;
                                    $_SESSION["id"] = $id;
                                    $_SESSION["email"] = $email;
                                    $_SESSION["name"] = $first_name . " " . $last_name;
                                    $_SESSION["role"] = $user_type;
                                     $_SESSION["registrationNo"] = $registration_no;

                                    // Redirect user to dashboard page
                                    header("location: index.php");
                                } else{
                                    // Display an error message if password is not valid
                                    $password_err = "The password you entered was not valid.";
                                }
                            }
                        } else{
                            // Display an error message if email doesn't exist
                            $email_err = "No account found with that email.";
                        }
                    } else{
                        echo "Oops! Something went wrong. Please try again later.";
                    }

                    // Close statement
                    $stmt->close();
                }
            }

            // Close connection
            $mysqli->close();
        }
    ?>

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div
                                style="background-image: url('https://images.pexels.com/photos/8199562/pexels-photo-8199562.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1'); background-size: cover; background-position: center;"
                                class="col-lg-6 d-none d-lg-block "
                            >
                            </div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="mb-4" style="display: flex; justify-content: center; align-items: center;">
                                        <img src="assets/studenthub.png" alt="Logo" style="display: block; margin: 0 auto; width: 90px; height: 90px;">
                                    </div>
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                                    </div>
                                    <form class="user" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                        <div class="form-group">
                                            <input type="email" class="form-control form-control-user <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>"
                                                id="exampleInputEmail" aria-describedby="emailHelp"
                                                placeholder="Enter Email Address..." name="email" value="<?php echo $email; ?>">
                                            <span class="invalid-feedback"><?php echo $email_err; ?></span>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>"
                                                id="exampleInputPassword" placeholder="Password" name="password">
                                            <span class="invalid-feedback"><?php echo $password_err; ?></span>
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" class="custom-control-input" id="customCheck">
                                                <label class="custom-control-label" for="customCheck">Remember
                                                    Me</label>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            Login
                                        </button>
                                    </form>
                                    <hr>
                                </div>
                            </div>
                        </div>
                    </div>
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

</body>
</html>