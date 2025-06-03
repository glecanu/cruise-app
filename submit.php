<?php
// --- PHPMailer ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';

// --- Configuration ---
$yourEmailAddress = "your_email@example.com"; // <--- !!! SET YOUR EMAIL ADDRESS HERE !!!
$csvFilePath = "submissions.csv";

// --- Helper function for redirection ---
function redirect($status) {
    header("Location: index.html?status=" . $status);
    exit;
}

// --- Script Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Get and sanitize data
    $firstName = isset($_POST['firstName']) ? trim(htmlspecialchars($_POST['firstName'])) : '';
    $homeCity = isset($_POST['homeCity']) ? trim(htmlspecialchars($_POST['homeCity'])) : '';
    $homeCountry = isset($_POST['homeCountry']) ? trim(htmlspecialchars($_POST['homeCountry'])) : '';

    // 2. Validate data (simple validation)
    if (empty($firstName) || empty($homeCity) || empty($homeCountry)) {
        redirect("invalid");
    }

    // 3. Store data in CSV
    $data = [$firstName, $homeCity, $homeCountry, date("Y-m-d H:i:s")]; // Added timestamp
    $fileHandle = fopen($csvFilePath, 'a'); // Open in append mode
    if ($fileHandle === false) {
        error_log("Failed to open CSV file: " . $csvFilePath);
        redirect("error");
    }

    // Add header row if file is new/empty
    if (filesize($csvFilePath) == 0) {
        fputcsv($fileHandle, ["FirstName", "HomeCity", "HomeCountry", "SubmissionTime"]);
    }
    fputcsv($fileHandle, $data);
    fclose($fileHandle);

    // 4. Send email notification using PHPMailer
    $mail = new PHPMailer(true); // Passing `true` enables exceptions

    try {
        // Server settings
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output for troubleshooting
        $mail->isSMTP();                                // Send using SMTP
        $mail->Host       = 'smtp.example.com';         // Set the SMTP server to send through (e.g., smtp.gmail.com)
        $mail->SMTPAuth   = true;                       // Enable SMTP authentication
        $mail->Username   = 'your_smtp_username@example.com'; // SMTP username (e.g., your Gmail address)
        $mail->Password   = 'your_smtp_password';       // SMTP password (e.g., Gmail App Password if 2FA is enabled)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Enable implicit TLS encryption (often 'ssl' for port 465)
        $mail->Port       = 465;                        // TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        // Recipients
        $mail->setFrom('form-notifications@yourdomain.com', 'Form Submitter'); // Sender email and name
        $mail->addAddress($yourEmailAddress, 'Site Admin');     // Add a recipient (your email)

        // Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = 'New Form Submission: ' . $firstName;
        $mail->Body    = "A new form submission has been received:<br><br>" .
                         "<b>First Name:</b> " . htmlspecialchars($firstName) . "<br>" .
                         "<b>Home City:</b> " . htmlspecialchars($homeCity) . "<br>" .
                         "<b>Home Country:</b> " . htmlspecialchars($homeCountry) . "<br>" .
                         "<b>Submission Time:</b> " . $data[3]; // Use the timestamp from the data array
        $mail->AltBody = "New Form Submission:\n" .
                         "First Name: " . $firstName . "\n" .
                         "Home City: " . $homeCity . "\n" .
                         "Home Country: " . $homeCountry . "\n" .
                         "Submission Time: " . $data[3];

        $mail->send();
        redirect("success");

    } catch (Exception $e) {
        error_log("PHPMailer Error: {$mail->ErrorInfo}");
        // Don't reveal detailed error to user, but log it.
        // You might want to send a generic error or still consider it a success if data was saved.
        // For now, let's say email failure is an overall error.
        redirect("error"); // Or "success_email_failed" if you want to distinguish
    }

} else {
    // Not a POST request, redirect to form
    redirect("error");
}
?>
