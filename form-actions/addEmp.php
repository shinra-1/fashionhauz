<?php 
session_start();
require '../config.php';

if(isset($_POST['submit'])){
    $username = $_POST["userto"];
    $fname = trim($_POST["fname"]);
    $mname = trim($_POST["mname"]);
    $lname = trim($_POST["lname"]);
    $age = $_POST["age"];
    $bday = $_POST["bday"];
    $gender = $_POST["sex"];
    $cnumber = $_POST["cnumber"];
    $email = $_POST["email"];
    $usern = $_POST["userr"];
    $passw = $_POST["passw"];
    $statadd = "active";
    $categ = "staff";

    

    if (!preg_match("/^09\d{9}$/", $cnumber)) {
        echo '<script language="javascript">alert("Invalid contact number! It should start with 09 and be 11 digits long.");</script>';
        echo '<script language="javascript">window.location.href = "../admin-employee.php";</script>';
        exit;
    };
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $allowed_domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'bing.com'];
        $email_domain = substr(strrchr($email, "@"), 1); // Extract domain from email
        
        if (in_array($email_domain, $allowed_domains)) {

        } else {
            echo '<script language="javascript">alert("Invalid email domain! Allowed domains: gmail.com, yahoo.com, outlook.com, bing.com.");</script>';
            echo '<script language="javascript">window.location.href = "../admin-employee.php";</script>';
            exit;
        }
    } else {
        echo '<script language="javascript">alert("Invalid email format!");</script>';
        echo '<script language="javascript">window.location.href = "../admin-employee.php";</script>';
        exit;
    };

    $stmt = $conn->prepare("INSERT INTO users (fname, mname, lname, age, bday, gender, 
    cnumber, email, uname, pword, category, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)");
    $stmt->bind_param("sssidsssssss", $fname, $mname, $lname, $age, $bday, $gender, $cnumber, $email, $usern, $passw, $categ, $statadd);
    if ($stmt->execute()) {
        $archives = $conn->prepare("INSERT INTO activitylogs (uname, description) VALUES (?, ?)");
        $detail = 'Added employee username: '.$usern;
        $archives->bind_param("ss", $username, $detail);
        $archives->execute();

        echo '<script language="javascript">alert("Account Successfully created!");</script>';
        echo '<script language="javascript">window.location.href = "../admin-employee.php";</script>';
        
    } else {
        echo "Error: " . $stmt->error;
    }
}else{
    echo '<script language="javascript">alert("err!");</script>';
    echo '<script language="javascript">window.location.href = "../admin-employee.php";</script>';
}

?>