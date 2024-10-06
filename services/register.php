<?php

header('Content-Type: application/json');

// Shared secret for authorization
$sharedSecret = "gradeplus";

// Get POST data
$username = $_POST['username'] ?? null;
$email = $_POST['email'] ?? null;
$password = $_POST['password'] ?? null;
$authorize = $_POST['authorize'] ?? null;

// Check authorization
if ($authorize != $sharedSecret){
    header("Location: illegal.php");
    exit();
}

// Database connection
$servername = "localhost";          
$usernameDB = "gradeplusclient";   
$passwordDB = "gradeplussql"; 
$dbname = "gradeplus";

// Connect to the database
$conn = mysqli_connect($servername, $usernameDB, $passwordDB, $dbname);

// Check connection
if ($conn->connect_error){
    echo json_encode(["success" => 0, "exists" => 0, "error" => 1]);
    exit();
}

// Check if username already exists
$sql = $conn->prepare("SELECT username FROM login WHERE username = ?");
$sql->bind_param("s", $username);
$sql->execute();
$sql->store_result();

if ($sql->num_rows > 0){
    echo json_encode(["success" => 0, "exists" => 1, "error" => 0]);
} else {
    // Insert new user with plain text password
    $insertSql = $conn->prepare("INSERT INTO login (username, email, password) VALUES (?, ?, ?)");
    $insertSql->bind_param("sss", $username, $email, $password); // Use plain text password here

    if ($insertSql->execute()){
        echo json_encode(["success" => 1, "exists" => 0, "error" => 0]);
    } else {
        echo json_encode(["success" => 0, "exists" => 0, "error" => 1]);
    }

    $insertSql->close();
}

$sql->close();
$conn->close();

?>
