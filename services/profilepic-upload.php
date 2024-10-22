<?php

//Ben Thomas: Need to start the session to get the username and update profilePicture in session.
session_start();
ini_set('display_errors', 0);   //Ben Thomas: This is to prevent the error messages from being displayed on the webpage.

$success = 0;
$error = 0;
$missingFeild = 0;
try {
    $conn = mysqli_connect('localhost', 'gradeplusclient', 'gradeplussql', 'gradeplus');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        //$username = $_POST['username'];
        //Ben Thomas: Username can be read from the session for simpler ajax calls.
        $username = $_SESSION['username'];


        if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] == 0) {
            $profilePicture = $_FILES['profilePicture']['tmp_name'];


            $stmt = $conn->prepare("SELECT * FROM login WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows != 0) {
                $stmt->close();

                $imageData = file_get_contents($profilePicture);

                $stmt = $conn->prepare("SELECT * FROM login WHERE username = ?");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows == 0) {

                    $stmt->close();
                    $stmt = $conn->prepare("INSERT INTO login (username, profilePicture) VALUES (?, ?)");
                    $stmt->bind_param("sb", $username, $imageData);
                    $stmt->send_long_data(1, $imageData);

                    if ($stmt->execute()) {
                        //echo "Profile picture uploaded successfully!";
                        $success = 1; //Ben Thomas: Was this forgotten?
                        $_SESSION['profilePicture'] = $imageData; //Update session profile picture
                    } else {
                        $error = 1; //Ben Thomas: Was this forgotten?
                        //echo "Error uploading profile picture: " . $stmt->error;
                    }
                } else {

                    $stmt->close();
                    $stmt = $conn->prepare("UPDATE login SET profilePicture = ? WHERE username = ?");
                    $stmt->bind_param("bs", $imageData, $username);
                    $stmt->send_long_data(0, $imageData);

                    if ($stmt->execute()) {
                        $success = 1;
                        $_SESSION['profilePicture'] = $imageData; //Update session profile picture
                    } else {
                        $error = 1;
                    }
                }
            }

            $stmt->close();
        } else {
            $missingFeild = 0;
        }
    }

    $conn->close();
} catch (exception $e) {
    $error = 1;
}
?>


<!-- Template HTML not necessary once the PHP code is integrated into the services file.


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Profile Picture</title>
</head>

<body>
    <h2>Upload Your Profile Picture</h2>
    <form action="profilePicUpload.php" method="post" enctype="multipart/form-data">
        <label for="username">Username:</label>
        <input type="text" name="username" required><br><br>
        <label for="profilePicture">Select Profile Picture:</label>
        <input type="file" name="profilePicture" accept="image/*" required><br><br>
        <button type="submit">Upload</button>
    </form>
</body>

</html>

-->