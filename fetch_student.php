<?php
// Database connection
$host = 'localhost';
$dbname = 'student_db';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch student details and marks by USN
if (isset($_GET['usn'])) {
    $usn = $_GET['usn'];

    // Fetch student details
    $sqlStudent = "SELECT * FROM students WHERE usn = ?";
    $stmtStudent = $conn->prepare($sqlStudent);
    $stmtStudent->bind_param("s", $usn);
    $stmtStudent->execute();
    $resultStudent = $stmtStudent->get_result();

    if ($resultStudent->num_rows > 0) {
        $student = $resultStudent->fetch_assoc();

        // Fetch marks details for the student
        $sqlMarks = "SELECT * FROM marks WHERE student_id = ?";
        $stmtMarks = $conn->prepare($sqlMarks);
        $stmtMarks->bind_param("i", $student['id']);
        $stmtMarks->execute();
        $resultMarks = $stmtMarks->get_result();

        $marks = [];
        while ($row = $resultMarks->fetch_assoc()) {
            $marks[] = $row;
        }

        // Send response as JSON
        echo json_encode(['student' => $student, 'marks' => $marks]);
    } else {
        echo json_encode(["error" => "No student found"]);
    }

    $stmtStudent->close();
    $stmtMarks->close();
}

$conn->close();
?>
