<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if USN is provided
if (!isset($_GET['usn']) || empty($_GET['usn'])) {
    die("USN is required to generate the photo.");
}

$usn = $_GET['usn'];

// Fetch student details
$student_sql = "SELECT * FROM students WHERE usn = ?";
$stmt = $conn->prepare($student_sql);
$stmt->bind_param("s", $usn);
$stmt->execute();
$student_result = $stmt->get_result();

if ($student_result->num_rows === 0) {
    die("No student found with the provided USN.");
}

$student = $student_result->fetch_assoc();

// Fetch marks details
$marks_sql = "SELECT * FROM marks WHERE student_id = ?";
$stmt = $conn->prepare($marks_sql);
$stmt->bind_param("i", $student['id']);
$stmt->execute();
$marks_result = $stmt->get_result();
$marks = $marks_result->fetch_all(MYSQLI_ASSOC);

// Create the image
$image = imagecreate(800, 600); // Create a blank image (width: 800px, height: 600px)

// Colors
$background_color = imagecolorallocate($image, 255, 255, 255); // White
$text_color = imagecolorallocate($image, 0, 0, 0);             // Black
$header_color = imagecolorallocate($image, 0, 102, 204);       // Blue

// Add title
imagestring($image, 5, 280, 10, "Student Performance Report", $header_color);

// Add student details
$y = 50;
imagestring($image, 4, 50, $y, "Student Name: " . $student['student_name'], $text_color);
$y += 20;
imagestring($image, 4, 50, $y, "Parent Name: " . $student['parent_name'], $text_color);
$y += 20;
imagestring($image, 4, 50, $y, "Parent Phone: " . $student['parent_phone'], $text_color);
$y += 20;
imagestring($image, 4, 50, $y, "Address: " . $student['address'], $text_color);
$y += 20;
imagestring($image, 4, 50, $y, "USN: " . $student['usn'], $text_color);

// Add marks details
$y += 40;
imagestring($image, 5, 50, $y, "Subject Details", $header_color);
$y += 20;

foreach ($marks as $mark) {
    $details = sprintf(
        "Subject: %s | Max Marks: %d | Marks: %d | Attendance: %d%%",
        $mark['subject_name'],
        $mark['max_marks'],
        $mark['marks'],
        $mark['attendance']
    );
    imagestring($image, 3, 50, $y, $details, $text_color);
    $y += 20;

    if ($y > 550) {
        imagestring($image, 3, 50, $y, "(Content exceeds page limit)", $header_color);
        break;
    }
}

// Save the image to a file
$filepath = 'images/student_report_' . $student['usn'] . '.png'; // Define the path and filename
imagepng($image, $filepath);  // Save the image to the server

// Destroy the image to free up memory
imagedestroy($image);

// Close the database connection
$conn->close();

// Return the file path or a success message
echo "Report saved as: " . $filepath;
?>
