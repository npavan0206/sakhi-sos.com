<?php
// Start the session to store error messages
session_start();

// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_registration";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize error message variable
$errorMessage = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user input
    $fullName = trim($_POST['fullName']);
    $phone = trim($_POST['phone']);
    $age = trim($_POST['age']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);

    // Validate input
    if (empty($fullName) || empty($phone) || empty($age) || empty($email) || empty($password) || empty($confirmPassword)) {
        $errorMessage = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Invalid email format.";
    } elseif ($password !== $confirmPassword) {
        $errorMessage = "Passwords do not match.";
    } elseif (strlen($password) < 8 || !preg_match("/[A-Za-z]/", $password) || !preg_match("/\d/", $password)) {
        $errorMessage = "Password must be at least 8 characters long and contain both letters and numbers.";
    } elseif (strlen($phone) !== 10 || !is_numeric($phone)) {
        $errorMessage = "Phone number must be 10 digits.";
    } else {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Prepare SQL statement to insert user data
        $stmt = $conn->prepare("INSERT INTO users (full_name, phone, age, email, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiss", $fullName, $phone, $age, $email, $hashedPassword);

        // Execute the query and check for errors
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Sign Up Successful!";
            header("Location: login.php"); // Redirect to login page
            exit();
        } else {
            $errorMessage = "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link href="logo.png" type="x-icon" rel="shortcut icon">
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #004080;
            margin: 0;
        }
        .container {
            text-align: center;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 400px;
        }
        h2 {
            color: #333;
        }
        input, button {
            margin: 10px 0;
            padding: 10px;
            width: 90%;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            background: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
        }
        a {
            display: block;
            margin-top: 10px;
            color: #007BFF;
            text-decoration: none;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-top: -5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Sign Up</h2>
        <?php if ($errorMessage): ?>
            <p class="error"><?php echo $errorMessage; ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="fullName" placeholder="Full Name" required>
            <input type="tel" name="phone" placeholder="Phone Number" required pattern="[0-9]{10}">
            <input type="number" name="age" placeholder="Age" min="1" max="120" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Create Password" required>
            <input type="password" name="confirmPassword" placeholder="Confirm Password" required>
            <button type="submit">Submit</button>
        </form>
        <a href="login.php">Already have an account? Login here</a>
    </div>
</body>
</html>
