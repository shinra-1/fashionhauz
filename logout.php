<?php
include 'config.php';

if (isset($_POST['logout'])) {
    $userna = $_POST['usern'];
    
    $select = $conn->prepare("SELECT category FROM users WHERE uname = ?");
    $select->bind_param("s", $userna);
    $select->execute();
    $select->bind_result($categ);
    $select->fetch();
    $select->close();
    if($categ != "customer"){
        $archives = $conn->prepare("INSERT INTO activitylogs (uname, description) VALUES (?, ?)");
        $detail = 'Logged out';
        $archives->bind_param("ss", $userna, $detail);
        $archives->execute();
    }


    





    setcookie("username", "", time() - 604800, "/");
    setcookie("category", "", time() - 604800, "/");
    setcookie("loggedin", "", time() - 604800, "/");
    setcookie("id", "", time() - 604800, "/");
    header("Location: index.php");
    exit();
}


?>