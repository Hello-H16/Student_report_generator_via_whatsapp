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

// Handle Form Submission for Adding or Updating Students and Marks
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_name = $_POST['studentName'];
    $parent_name = $_POST['parentName'];
    $parent_phone = $_POST['parentPhone']; // Added Parent Phone Number
    $address = $_POST['address'];
    $usn = $_POST['usn'];
    $student_id = $_POST['studentId'] ?? null;

    if ($student_id) {
        // Update Student
        $sql = "UPDATE students SET student_name = ?, parent_name = ?, parent_phone = ?, address = ?, usn = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $student_name, $parent_name, $parent_phone, $address, $usn, $student_id);
        if ($stmt->execute()) {
            // Delete existing marks for this student
            $delete_marks = "DELETE FROM marks WHERE student_id = ?";
            $delete_stmt = $conn->prepare($delete_marks);
            $delete_stmt->bind_param("i", $student_id);
            $delete_stmt->execute();

            // Insert updated marks
            for ($i = 0; $i < count($_POST['subjectName']); $i++) {
                $subject_name = $_POST['subjectName'][$i];
                $max_marks = $_POST['maxMarks'][$i];
                $marks = $_POST['marks'][$i];
                $classes_held = $_POST['classesHeld'][$i];
                $classes_attended = $_POST['classesAttended'][$i];
                $attendance = $_POST['attendance'][$i];

                $marks_sql = "INSERT INTO marks (student_id, subject_name, max_marks, marks, classes_held, classes_attended, attendance) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)";
                $marks_stmt = $conn->prepare($marks_sql);
                $marks_stmt->bind_param("isiiiii", $student_id, $subject_name, $max_marks, $marks, $classes_held, $classes_attended, $attendance);
                $marks_stmt->execute();
            }
            echo "<script>alert('Student and Marks details updated successfully!');</script>";
        } else {
            echo "<script>alert('Error updating details: " . $stmt->error . "');</script>";
        }
    } else {
        // Insert New Student
        $sql = "INSERT INTO students (student_name, parent_name, parent_phone, address, usn) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $student_name, $parent_name, $parent_phone, $address, $usn);
        if ($stmt->execute()) {
            $student_id = $stmt->insert_id;

            // Insert marks into 'marks' table
            for ($i = 0; $i < count($_POST['subjectName']); $i++) {
                $subject_name = $_POST['subjectName'][$i];
                $max_marks = $_POST['maxMarks'][$i];
                $marks = $_POST['marks'][$i];
                $classes_held = $_POST['classesHeld'][$i];
                $classes_attended = $_POST['classesAttended'][$i];
                $attendance = $_POST['attendance'][$i];

                $marks_sql = "INSERT INTO marks (student_id, subject_name, max_marks, marks, classes_held, classes_attended, attendance) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)";
                $marks_stmt = $conn->prepare($marks_sql);
                $marks_stmt->bind_param("isiiiii", $student_id, $subject_name, $max_marks, $marks, $classes_held, $classes_attended, $attendance);
                $marks_stmt->execute();
            }
            echo "<script>alert('Student and Marks details saved successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Student Performance Report</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf-autotable"></script>
</head>
<body>
<nav>
        <button onclick="location.href='home.html'">Home</button>
        <button onclick="location.href='add_faculty.php'">Add Faculty</button>
        <button onclick="location.href='send_message.php'">Send Message</button>
        <button onclick="location.href='index.php'">Insert Student</button>
        <button onclick="location.href='view_student_page.html'">View All</button>
        <button onclick="location.href='logout.html'">Logout</button>
    </nav>
    <h1>Enter or Update Student Performance Details</h1>

    <!-- Form to Add or Update Student -->
    <form id="performanceForm" method="POST" action="">
        <input type="hidden" id="studentId" name="studentId">

        <label for="studentName">Student Name:</label><br>
        <input type="text" id="studentName" name="studentName" required><br><br>

        <label for="parentName">Parent Name:</label><br>
        <input type="text" id="parentName" name="parentName" required><br><br>

        <label for="parentPhone">Parent Phone Number:</label><br>
        <input type="text" id="parentPhone" name="parentPhone" required><br><br>

        <label for="address">Address:</label><br>
        <textarea id="address" name="address" rows="3" required></textarea><br><br>

        <label for="usn">USN:</label><br>
        <input type="text" id="usn" name="usn" required><br><br>

        <h3>Enter Marks Details</h3>
        <div id="marksSection">
            <div>
                <input type="text" placeholder="Subject Name" name="subjectName[]" required>
                <input type="number" placeholder="Max Marks" name="maxMarks[]" required>
                <input type="number" placeholder="Marks" name="marks[]" required>
                <input type="number" placeholder="Classes Held" name="classesHeld[]" required>
                <input type="number" placeholder="Classes Attended" name="classesAttended[]" required>
                <input type="number" placeholder="Attendance (%)" name="attendance[]" required>
            </div>
        </div>
        <button type="button" onclick="addSubject()">Add Another Subject</button><br><br>

        <button type="submit">Save Details</button>
    </form>

    <!-- Generate PDF -->
    <h2>Generate PDF Report</h2>
    <form>
        <label for="usnSearch">Generate PDF for USN:</label><br>
        <input type="text" id="usnSearch" name="usn" required><br><br>
        <button type="button" onclick="generatePDF()">Generate PDF</button>
    </form>

    <script>
        // Add another subject row
        function addSubject() {
            const marksSection = document.getElementById("marksSection");
            const newSubject = document.createElement("div");
            newSubject.innerHTML = `
                <input type="text" placeholder="Subject Name" name="subjectName[]" required>
                <input type="number" placeholder="Max Marks" name="maxMarks[]" required>
                <input type="number" placeholder="Marks" name="marks[]" required>
                <input type="number" placeholder="Classes Held" name="classesHeld[]" required>
                <input type="number" placeholder="Classes Attended" name="classesAttended[]" required>
                <input type="number" placeholder="Attendance (%)" name="attendance[]" required>
            `;
            marksSection.appendChild(newSubject);
        }

        // Function to generate PDF
        async function generatePDF() {
            const usn = document.getElementById("usnSearch").value;

            if (!usn) {
                alert("Please enter a valid USN");
                return;
            }

            try {
                // Fetch data from the backend
                const response = await fetch(`generate_pdf.php?usn=${usn}`);
                const data = await response.json();

                if (data.error) {
                    alert(data.error);
                    return;
                }

                // Create a jsPDF instance
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();

                // Add Title
                doc.setFontSize(18);
                doc.text("Student Performance Report", 20, 20);

                // Add Student Details
                doc.setFontSize(12);
                doc.text(`Student Name: ${data.student_name}`, 20, 40);
                doc.text(`Parent Name: ${data.parent_name}`, 20, 50);
                doc.text(`Parent Phone: ${data.parent_phone}`, 20, 60); // Displaying Parent Phone Number
                doc.text(`Address: ${data.address}`, 20, 70);
                doc.text(`USN: ${data.usn}`, 20, 80);

                // Add Marks Table
                doc.autoTable({
                    startY: 90,
                    head: [['Subject', 'Max Marks', 'Marks', 'Classes Held', 'Classes Attended', 'Attendance']],
                    body: data.marks.map(mark => [
                        mark.subject_name,
                        mark.max_marks,
                        mark.marks,
                        mark.classes_held,
                        mark.classes_attended,
                        `${mark.attendance}%`
                    ])
                });

                // Save PDF
                doc.save(`${data.usn}_Performance_Report.pdf`);
            } catch (error) {
                console.error("Error generating PDF:", error);
                alert("Failed to generate PDF. Check the console for more details.");
            }
        }
    </script>
</body>
</html>
