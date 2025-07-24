<?php
// Check if the form is submitted and the PDF file is uploaded
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['pdfFile'])) {
    $uploadDirectory = "uploads/"; // Directory for uploading the file
    $uploadedFile = $_FILES['pdfFile'];
    $fileName = basename($uploadedFile["name"]);
    $uploadPath = $uploadDirectory . $fileName;

    // Ensure the uploads directory exists
    if (!is_dir($uploadDirectory)) {
        mkdir($uploadDirectory, 0777, true);
    }

    // Check if the file is a PDF and upload it
    if ($uploadedFile['type'] == 'application/pdf') {
        if (move_uploaded_file($uploadedFile['tmp_name'], $uploadPath)) {
            // Adjust file URL to be accessed via the web browser
            // Assuming your XAMPP document root is in 'C:\xampp\htdocs\gitdemo\Student_report_generator'
            $fileUrl = "http://localhost/gitdemo/Student_report_generator/" . $uploadPath;

            // Google Drive folder link
            $googleDriveLink = "https://drive.google.com/drive/folders/1ANPidka-_VE7bDvsFmCPntTmDT-rCXNs?usp=sharing";

            // Handling the WhatsApp message
            $parentPhone = $_POST['parentPhone'];
            $message = $_POST['message'];

            // WhatsApp URL with both links (uploaded PDF and Google Drive folder)
            $whatsappMessage = $message . "\n\nHere is the uploaded PDF: " . $fileUrl . 
                               "\n\nYou can also access all reports in this Google Drive folder: " . $googleDriveLink;

            $whatsappUrl = "https://wa.me/$parentPhone?text=" . urlencode($whatsappMessage);
        } else {
            $error = "Error uploading the file.";
        }
    } else {
        $error = "Please upload a valid PDF file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylesi.css">
    <title>Send PDF to WhatsApp</title>
    <nav>
        <button onclick="location.href='home.html'">Home</button>
        <button onclick="location.href='add_faculty.php'">Add Faculty</button>
        <button onclick="location.href='send_message.php'">Send Message</button>
        <button onclick="location.href='index.php'">Insert Student</button>
        <button onclick="location.href='view_student_page.html'">View All</button>
        <button onclick="location.href='logout.html'">Logout</button>
    </nav>
    <style>
        /* Styling for the WhatsApp link */
        .whatsapp-link {
            color: blue;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <h1>Send Message to Parent with PDF Link via WhatsApp</h1>

    <!-- Form for uploading PDF and sending message -->
    <form action="" method="post" enctype="multipart/form-data">
        <label for="usn">Enter USN:</label>
        <input type="text" id="usn" name="usn" placeholder="Enter USN" required><br><br>

        <label for="parentPhone">Parent's Phone Number:</label>
        <input type="text" id="parentPhone" name="parentPhone" placeholder="Enter Parent's Phone Number (e.g., 9876543210)" required><br><br>

        <label for="pdfFile">Upload PDF:</label>
        <input type="file" id="pdfFile" name="pdfFile" accept="application/pdf" required><br><br>

        <label for="message">Message:</label>
        <textarea id="message" name="message" placeholder="Enter message to the parent" rows="4" required></textarea><br><br>

        <button type="submit">Upload and Send Message</button>
    </form>

    <?php if (isset($fileUrl)): ?>
        <h3>File successfully uploaded!</h3>
        <p>PDF URL: <a href="<?php echo $fileUrl; ?>" target="_blank"><?php echo $fileUrl; ?></a></p>

        <!-- WhatsApp link -->
        <a href="<?php echo $whatsappUrl; ?>" target="_blank" class="whatsapp-link">
            <button>Send Message to WhatsApp</button>
        </a>
    <?php elseif (isset($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>

</body>
</html>
