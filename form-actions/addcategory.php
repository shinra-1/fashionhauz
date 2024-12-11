<?php 
session_start();
require '../config.php';

if(isset($_POST['submit'])){
    $username = $_POST["usern"];
    $cate = $_POST["cate"];
    
    $stmt = $conn->prepare("INSERT INTO category (`category`) VALUES (?)");
    $stmt->bind_param("s",$cate);
    if ($stmt->execute()) {
        $archives = $conn->prepare("INSERT INTO activitylogs (uname, description) VALUES (?, ?)");
        $detail = 'New category: '.$cate;
        $archives->bind_param("ss", $username, $detail);
        $archives->execute();

        echo '<script language="javascript">alert("Category Successfully Added!");</script>';
        echo '<script language="javascript">window.location.href = "category.php";</script>';
        
    } else {
        echo "Error: " . $stmt->error;
    }
}else if(isset($_POST['update'])){
    $username = $_POST["usern"];
    $cate = $_POST["cate"];
    $id = $_POST["id"];
    
    $stmt = $conn->prepare("UPDATE category SET category = ? WHERE id = ?");
    $stmt->bind_param("si", $cate, $id);
    if ($stmt->execute()) {
        $archives = $conn->prepare("INSERT INTO activitylogs (uname, description) VALUES (?, ?)");
        $detail = 'Updated category: '.$cate;
        $archives->bind_param("ss", $username, $detail);
        $archives->execute();

        echo '<script language="javascript">alert("Category Successfully Added!");</script>';
        echo '<script language="javascript">window.location.href = "category.php";</script>';
        
    } else {
        echo "Error: " . $stmt->error;
    }
}else{
    echo '<script language="javascript">alert("err!");</script>';
        echo '<script language="javascript">window.location.href = "category.php";</script>';
}

?>