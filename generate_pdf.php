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
    $pdf->Cell(40, 8, 'Registration No', 1);
    for ($i = 1; $i <= 30; $i++) {
        $pdf->Cell(8, 8, $i, 1);
    }
    $pdf->Ln();

    // Set font for the table body
    $pdf->SetFont('Arial', '', 12);

    // Fetch attendance records based on month and year
    require_once "config.php";

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        // Retrieve selected month and year
        $month = isset($_GET['month']) ? $_GET['month'] : date('n'); // If month is set in GET parameters, use that, otherwise default to current month
        $year = isset($_GET['year']) ? $_GET['year'] : date('Y');// If year is set in GET parameters, use that, otherwise default to current year
    }

    // Prepare a select statement to fetch attendance records for the specified month and year
    $sql = "SELECT REGISTRATION_NO, DAY FROM attendance WHERE MONTH = ? AND YEAR = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        // Bind parameters
        $stmt->bind_param("ii", $month, $year);

        // Execute the prepared statement
        $stmt->execute();

        // Bind result variables
        $stmt->bind_result($registrationNo, $day);

        // Initialize an associative array to store attendance data
        $attendanceData = [];

        // Fetch attendance records and store in the array
        while ($stmt->fetch()) {
            // Store attendance data in the format [registrationNo => [day1 => 1, day2 => 1, ...]]
            $attendanceData[$registrationNo][$day] = 1;
        }

        // Close statement
        $stmt->close();
    } else {
        // Error handling
        echo "Error: Unable to prepare statement.";
    }

    // Loop through each registration number and fill in the attendance table
    foreach ($attendanceData as $registrationNo => $attendance) {
        // Add registration number to PDF
        $pdf->Cell(40, 8, $registrationNo, 1);

        // Loop through each day of the month and check if attendance is marked
        for ($i = 1; $i <= 30; $i++) {
            // Check if attendance is marked for the current day
            $attended = isset($attendance[$i]) ? "1" : "0";
            $pdf->Cell(8, 8, $attended, 1);
        }
        $pdf->Ln();
    }

    // Close connection
    $mysqli->close();

    // Output the PDF to the browser
    $pdf->Output();
?>