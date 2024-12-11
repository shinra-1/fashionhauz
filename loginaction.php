<?php 
session_start();
include 'config.php';


if(isset($_POST['push'])){
    $uname = trim($_POST["uname"]);
    $pw = trim($_POST["pw"]);
    $sql = "SELECT userID, uname, pword, category FROM users WHERE uname = ?";
    if($statement = mysqli_prepare($conn,$sql)){
        mysqli_stmt_bind_param($statement, "s", $param_username);
        $param_username = $uname;
        if(mysqli_stmt_execute($statement)){ // execute
            mysqli_stmt_store_result($statement);
            if(mysqli_stmt_num_rows($statement) == 1){ // check if username exists, verify password kung oo                 
                mysqli_stmt_bind_result($statement, $id, $uname, $hashed_password, $category);
                if(mysqli_stmt_fetch($statement)){
                    if ($category === "staff"){
                        if($pw === $hashed_password){
                            setcookie("username", $uname, time() + (7 * 24 * 60 * 60), "/"); //cookie expires in 7 days//604,800 secs for deletion
                            setcookie("category", $category, time() + (7 * 24 * 60 * 60), "/");
                            setcookie("loggedin", true, time() + (7 * 24 * 60 * 60), "/");
                            setcookie("id", $id, time() + (7 * 24 * 60 * 60), "/");
                            unset($_SESSION['error']);
                            $archives = $conn->prepare("INSERT INTO activitylogs (uname, description) VALUES (?, ?)");
                            $detail = 'Logged in';
                            $archives->bind_param("ss", $uname, $detail);
                            $archives->execute();

                            header("location: pos/main.php");
                        }else{
                            $_SESSION['error'] = "Invalid password.";
                            header("location: login.php");
                        } 
                    }else if($category === "admin"){
                            if(password_verify($pw, $hashed_password)){
                                setcookie("username", $uname, time() + (7 * 24 * 60 * 60), "/"); //cookie expires in 7 days//604,800 secs for deletion
                                setcookie("category", $category, time() + (7 * 24 * 60 * 60), "/");
                                setcookie("loggedin", true, time() + (7 * 24 * 60 * 60), "/");
                                setcookie("id", $id, time() + (7 * 24 * 60 * 60), "/");
                                unset($_SESSION['error']);
                                $archives = $conn->prepare("INSERT INTO activitylogs (uname, description) VALUES (?, ?)");
                                $detail = 'Logged in';
                                $archives->bind_param("ss", $uname, $detail);
                                $archives->execute();

                                header("location: admin-db.php");
                            } else{
                                // pw is not valid, error message
                                $_SESSION['error'] = "Invalid password.";
                                header("location: login.php");
                            }
                    }else if($category === "customer"){
                        if(password_verify($pw, $hashed_password)){
                            setcookie("username", $uname, time() + (7 * 24 * 60 * 60), "/"); //cookie expires in 7 days//604,800 secs for deletion
                            setcookie("category", $category, time() + (7 * 24 * 60 * 60), "/");
                            setcookie("loggedin", true, time() + (7 * 24 * 60 * 60), "/");
                            setcookie("id", $id, time() + (7 * 24 * 60 * 60), "/");
                            unset($_SESSION['error']);

                            header("location: index.php");
                        } else{
                            // pw is not valid, error message
                            $_SESSION['error'] = "Invalid password.";
                            header("location: login.php");
                        }
                    }else{ 
                        $_SESSION['error'] = "Invalid account category.";
                        header("location: login.php");
                    }
                }
                
            } else{
                // username doesn't exist, error message
                $_SESSION['error'] = "Username does not exist.";
                header("location: login.php");
            }
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
        mysqli_stmt_close($statement);// close
    }
    mysqli_close($conn);// close
}





?>