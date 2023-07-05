<?php
// Start the session
session_start();

// Check if the user is logged in (email stored in session)
if (!isset($_SESSION['email'])) {
    // Redirect to the login page
    header("Location: loginform.php");
    exit;
}

// Check if the project ID is provided
if (isset($_GET['id'])) {
    $projectId = $_GET['id'];

    // Database configuration
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "website";

    // Create a new PDO instance
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Prepare the SQL statement to fetch the project details
        $stmt = $conn->prepare("SELECT * FROM project WHERE id = :id");
        $stmt->bindParam(':id', $projectId);
        $stmt->execute();

        // Fetch the project details
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        // Close the database connection
        $conn = null;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
} else {
    // Redirect to the profile page if the project ID is not provided
    header("Location: profile.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Project</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
        }

        form {
            width: 100%;
        }

        label {
            display: block;
            margin-top: 10px;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
        }

        input[type="submit"] {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Project</h1>
        <form method="post" action="updateproject.php">
            <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
            <label>Name:</label>
            <input type="text" name="name" value="<?php echo $project['name']; ?>">
            <label>Roll No:</label>
            <input type="text" name="rollno" value="<?php echo $project['rollno']; ?>">
            <label>Technology:</label>
            <input type="text" name="technology" value="<?php echo $project['technology']; ?>">
            <label>Project Title:</label>
            <input type="text" name="projectTitle" value="<?php echo $project['projectTitle']; ?>">
            <label>Google Drive Link:</label>
            <input type="text" name="googleDriveLink" value="<?php echo $project['googleDriveLink']; ?>">
            <input type="submit" value="Update">
        </form>
    </div>
</body>
</html>
