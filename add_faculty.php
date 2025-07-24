<?php
// Database connection
$host = 'localhost'; // Replace with your MySQL host
$db = 'faculty_management'; // Replace with your database name
$user = 'root'; // Replace with your MySQL username
$pass = ''; // Replace with your MySQL password

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['facultyName'];
    $faculty_id = $_POST['facultyId'];
    $phone = $_POST['facultyPhone'];
    $email = $_POST['facultyEmail'];
    $password = password_hash($_POST['facultyPassword'], PASSWORD_BCRYPT); // Secure password hashing

    // Check if the faculty ID or email already exists
    $checkQuery = "SELECT * FROM faculty WHERE faculty_id = ? OR email = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ss", $faculty_id, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $message = 'Faculty ID or Email already exists.';
        $messageClass = 'error';
    } else {
        // Insert the new faculty record
        $insertQuery = "INSERT INTO faculty (name, faculty_id, phone, email, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("sssss", $name, $faculty_id, $phone, $email, $password);

        if ($stmt->execute()) {
            $message = 'Faculty registered successfully!';
            $messageClass = 'success';
        } else {
            $message = 'Failed to register faculty.';
            $messageClass = 'error';
        }
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Faculty</title>
    <nav>
        <button onclick="location.href='home.html'">Home</button>
        <button onclick="location.href='add_faculty.php'">Add Faculty</button>
        <button onclick="location.href='send_message.php'">Send Message</button>
        <button onclick="location.href='index.php'">Insert Student</button>
        <button onclick="location.href='view_student_page.html'">View All</button>
        <button onclick="location.href='logout.html'">Logout</button>
    </nav>
    <style>
        nav{
            height: 100vh;
            margin: 0;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h1 {
            margin-bottom: 20px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }
        button {
            background-color: #25D366;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            margin: 10px 0;
        }
        button:hover {
            background-color: #1aa355;
        }
        .message {
            margin-top: 20px;
            font-size: 1rem;
            text-align: center;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Register Faculty</h1>

        <!-- Display Success or Error Message -->
        <?php if (isset($message)): ?>
            <div class="message <?php echo $messageClass; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Faculty Registration Form -->
        <form method="POST" action="">
            <label for="facultyName">Full Name:</label>
            <input type="text" id="facultyName" name="facultyName" placeholder="Enter Faculty Name" required>

            <label for="facultyId">Faculty ID:</label>
            <input type="text" id="facultyId" name="facultyId" placeholder="Enter Faculty ID" required>

            <label for="facultyPhone">Phone Number:</label>
            <input type="tel" id="facultyPhone" name="facultyPhone" placeholder="Enter Faculty Phone Number" required>

            <label for="facultyEmail">Email:</label>
            <input type="email" id="facultyEmail" name="facultyEmail" placeholder="Enter Faculty Email" required>

            <label for="facultyPassword">Password:</label>
            <input type="password" id="facultyPassword" name="facultyPassword" placeholder="Enter Password" required>

            <button type="submit">Insert Faculty</button>
        </form>
    </div>
</body>
</html>
