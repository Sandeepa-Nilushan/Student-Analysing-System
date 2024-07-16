<?php
    // Include config file
    require_once "config.php";

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';

    // Initialize variables
    $meetingDate = $meetingDate_err = "";

    // Process form data when form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate meeting date
        if (empty(trim($_POST["meetingDate"]))) {
            $meetingDate_err = "Please select a meeting date.";
            header("location: studentinfo.php");
            // Display error alert
                        echo '<div class="alert alert-danger alert-dismissible fade show position-fixed w-100" style="top: 0; z-index: 9999;" role="alert">
                            Please pick Date.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>';
        } else {
            $meetingDate = trim($_POST["meetingDate"]);
            // Check if the meeting date is a valid date format
            if (!strtotime($meetingDate)) {
                $meetingDate_err = "Invalid date format.";
            }
        }

        $parent_id = $_POST['parent_id'];
        $user_id = $_POST['user_id'];
        $parent_email = $_POST['parent_email'];

        // If no errors, proceed to schedule the meeting
        if (empty($meetingDate_err)) {
            // Prepare SQL statement to insert meeting details into the meeting table
            $sql = "INSERT INTO meeting (DATE, FK_PARENT, FK_TEACHER) VALUES (?, ?, ?)";

            if ($stmt = $mysqli->prepare($sql)) {
                // Bind parameters
                $stmt->bind_param("sii", $meetingDate, $parent_id, $user_id);

                // Attempt to execute the prepared statement
                if ($stmt->execute()) {

                    $mail = new PHPMailer(true);

                    $mail->isSMTP();// Set mailer to use SMTP
                    $mail->CharSet = "utf-8";// set charset to utf8
                    $mail->SMTPAuth = true;// Enable SMTP authentication
                    $mail->SMTPSecure = 'tls';// Enable TLS encryption, `ssl` also accepted

                    $mail->Host = 'smtp.office365.com';// Specify main and backup SMTP servers
                    $mail->Port = 587;// TCP port to connect to
                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );

                    $mail->Username   = 'madushan@sp-solutions.biz';                     //SMTP username
                    $mail->Password   = 'pleasechangema';                               //SMTP password

                    $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

                    $mail->setFrom('madushan@sp-solutions.biz', 'StudentHub');        // Sender's email address and name
                    $mail->addAddress($parent_email, "Sir / Madam");

                    // Email content
                    $mail->isHTML(true);                                       // Set email format to HTML
                    $mail->Subject = 'Meeting Scheduled';                              // Email subject
                    $mail->Body    = 'Dear Parent, A meeting has been scheduled with the teacher. Best regards, [StudentHub]';

                    // Send email
                    $mail->send();
                    echo 'Email sent successfully';

                    // Meeting scheduled successfully
                    // Redirect to studentinfo.php
                    header("location: studentinfo.php");
                    // Display success alert
                        echo '<div class="alert alert-success alert-dismissible fade show position-fixed w-100" style="top: 0; z-index: 9999;" role="alert">
                                Meeting added Successfully
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                                </button>
                            </div>';
                    exit(); // Stop further execution
                } else {
                    // Error handling
                    echo "Error: Unable to schedule meeting.";
                }

                // Close statement
                $stmt->close();
            } else {
                // Error handling
                echo "Error: Unable to prepare statement.";
            }
        }

        // Close connection
        $mysqli->close();
    }
?>