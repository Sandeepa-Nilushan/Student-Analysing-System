<?php
    // Include FPDF library
    require_once "fpdf/fpdf.php";

    // Create new PDF instance with landscape orientation
    $pdf = new FPDF('L');

    // Add new page
    $pdf->AddPage();

    // Set font for the table headers
    $pdf->SetFont('Arial', 'B', 12);

    // Add table headers
    $pdf->Cell(40, 8, 'ID', 1);
    $pdf->Cell(40, 8, 'Registration No', 1);
    $pdf->Cell(40, 8, 'Subject', 1);
    $pdf->Cell(40, 8, 'Mark', 1);
    $pdf->Cell(40, 8, 'Semester', 1);
    $pdf->Cell(40, 8, 'Grade', 1);
    $pdf->Ln();

    // Set font for the table body
    $pdf->SetFont('Arial', '', 12);

    // Fetch result records for the specific student
    require_once "config.php";

    // Check if student id is provided
    if (isset($_GET['student_id'])) {
        $student_id = $_GET['student_id'];

        // Prepare a select statement to fetch result records for the specified student
        $sql = "SELECT R.ID, R.REGISTRATION_NO, R.MARK, R.SEMESTER, R.GRADE, S.NAME 
                FROM RESULT R 
                INNER JOIN SUBJECT S ON S.ID = R.FK_SUBJECT
                INNER JOIN USER SU ON (SU.REGISTRATION_NO = R.REGISTRATION_NO AND SU.USER_TYPE = 'STUDENT')
                WHERE SU.ID = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            // Bind parameters
            $stmt->bind_param("i", $student_id);

            // Execute the prepared statement
            $stmt->execute();

            // Bind result variables
            $stmt->bind_result($id, $registrationNo, $mark, $semester, $grade, $subjectName);

            // Fetch result records and output to PDF
            while ($stmt->fetch()) {
                $pdf->Cell(40, 8, $id, 1);
                $pdf->Cell(40, 8, $registrationNo, 1);
                $pdf->Cell(40, 8, $subjectName, 1);
                $pdf->Cell(40, 8, $mark, 1);
                $pdf->Cell(40, 8, $semester, 1);
                $pdf->Cell(40, 8, $grade, 1);
                $pdf->Ln();
            }

            // Close statement
            $stmt->close();
        } else {
            // Error handling
            echo "Error: Unable to prepare statement.";
        }
    } else {
        // Error handling if student id is not provided
        echo "Error: Student ID is required.";
    }

    // Close connection
    $mysqli->close();

    // Output the PDF to the browser
    $pdf->Output();
?>
