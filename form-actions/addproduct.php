<?php 
session_start();
require '../config.php';

if(isset($_POST['submit'])){
    $username = $_POST["usern"];
    $prodname = $_POST["prodname"];
    $desc = $_POST["desc"];
    $cate = $_POST["cate"];
    $prodimg = $_FILES["prodimg"]["name"];
    $prodimg_tmp_name = $_FILES["prodimg"]["tmp_name"];
    $prodimg_folder = '../products/'.$prodimg;
    
    if (move_uploaded_file($prodimg_tmp_name, $prodimg_folder)) {
        $stmt = $conn->prepare("INSERT INTO products (`prodname`, `description`, `category`,`image`) VALUES ( ?, ?, ?, ?)");
        $stmt->bind_param("ssss", $prodname, $desc, $cate, $prodimg);
        
        $archives = $conn->prepare("INSERT INTO activitylogs (uname, description) VALUES (?, ?)");
        $detail = 'Added product: '.$prodname;
        $archives->bind_param("ss", $username, $detail);
        $archives->execute();

        if ($stmt->execute()) {
            echo '<script language="javascript">alert("Product successfully added!");</script>';
            echo '<script language="javascript">window.location.href = "../admin-products.php";</script>';
            
        } else {
            echo "Error: " . $stmt->error;
        }
    }else{
        echo '<script language="javascript">alert("Error uploading the image file.");</script>';
    }
}else{
    echo '<script language="javascript">alert("err!");</script>';
        echo '<script language="javascript">window.location.href = "../admin-products.php";</script>';
}

?>