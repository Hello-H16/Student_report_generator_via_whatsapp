<?php
// Database Connection
$servername = "localhost";
$username = "root"; // Database username
$password = ""; // Database password
$dbname = "student_db"; // Database name

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['usn'])) {
    $usn = $_GET['usn'];

    // Fetch student details
    $sql = "SELECT * FROM students WHERE usn = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usn);
    $stmt->execute();
    $student_result = $stmt->get_result();

    if ($student_result->num_rows > 0) {
        $student = $student_result->fetch_assoc();
        $student_name = $student['student_name'];
        $parent_name = $student['parent_name'];
        $parent_phone = $student['parent_phone']; // Parent phone number
        $address = $student['address'];
        $usn = $student['usn'];

        // Fetch marks details
        $marks_sql = "SELECT * FROM marks WHERE student_id = ?";
        $marks_stmt = $conn->prepare($marks_sql);
        $marks_stmt->bind_param("i", $student['id']);
        $marks_stmt->execute();
        $marks_result = $marks_stmt->get_result();

        $marks = [];
        while ($row = $marks_result->fetch_assoc()) {
            $marks[] = $row;
        }

        // Return student and marks data as JSON for PDF generation
        echo json_encode([
            'student_name' => $student_name,
            'parent_name' => $parent_name,
            'parent_phone' => $parent_phone,
            'address' => $address,
            'usn' => $usn,
            'marks' => $marks
        ]);
    } else {
        echo json_encode(['error' => 'Student not found']);
    }
} else {
    echo json_encode(['error' => 'USN is required']);
}
?>
