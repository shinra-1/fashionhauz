<?php 
include "config.php";
session_start();
if (isset($_SESSION['error'])) {
    $login_err = $_SESSION['error'];
    unset($_SESSION['error']);
}

// email structure verificator
function isValidEmail($conn, $email) {
    $sql = "SELECT email FROM users WHERE email = ?";
    if ($statement = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($statement, "s", $param_email);
        $param_email = trim($email); 
        if (mysqli_stmt_execute($statement)) { 
            mysqli_stmt_store_result($statement);
            if (mysqli_stmt_num_rows($statement) == 0) { // Check if email doesn't exist in DB
                // Validate email format and domain
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $allowed_domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'bing.com'];
                    $email_domain = substr(strrchr($email, "@"), 1); // Extract domain from email
                    
                    if (in_array($email_domain, $allowed_domains)) {
                        return true; // Valid email
                    } else {
                        echo '<script language="javascript">alert("Invalid email domain! Allowed domains: gmail.com, yahoo.com, outlook.com, bing.com.");</script>';
                        echo '<script language="javascript">window.location = "login.php";</script>';
                        exit;
                    }
                } else {
                    echo '<script language="javascript">alert("Invalid email format!");</script>';
                    echo '<script language="javascript">window.location = "login.php";</script>';
                    exit;
                }
            } else {
                echo '<script language="javascript">alert("Email already exists!");</script>';
                echo '<script language="javascript">window.location = "login.php";</script>';
                exit;
            }
        } else {
            echo "Something went wrong with email verification. Please try again.";
        }
        mysqli_stmt_close($statement);
    }
    return false;
}



if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    $email = $_POST['email'];
	$usern = $_POST['usern'];
	$pword = $_POST['pword'];

    if(isValidEmail($conn,$email)){
        if(empty(trim($_POST["usern"]))){ // check if tama username
            $username_err = "Please enter a username.";
        }else if(!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["usern"]))){
            $username_err = "Username can only contain letters, numbers, and underscores.";
        }else{
            $sql = "SELECT userID FROM users WHERE uname = ?";
            if($statement = mysqli_prepare($conn, $sql)){
                mysqli_stmt_bind_param($statement, "s", $param_username);
                $param_username = trim($_POST["usern"]); // set parameter
                if(mysqli_stmt_execute($statement)){ // execute
                    mysqli_stmt_store_result($statement);
                    
                    if(mysqli_stmt_num_rows($statement) == 1){
                        $login_err = "This username is already taken.";
                    } else{
                        $usern = trim($_POST["usern"]);
                    }
                } else{
                    echo "Oops! Something went wrong. Please try again later.";
                }
                mysqli_stmt_close($statement);
            }
        }
        if(empty(trim($_POST["pword"]))){// check if tama pw
            $password_err = "Please enter a password.";     
        }else if(strlen(trim($_POST["pword"])) < 8){
            $login_err = "Password must have atleast 8 characters.";
        }else{
            $pword = trim($_POST["pword"]);
        }
        if(empty($login_err)){//check input errors bago ipasok sa db
            $search = mysqli_query($conn, "SELECT * FROM users");
            if(mysqli_fetch_assoc($search) == 0){
                $sql = "INSERT INTO users (`email`, `uname`, `pword`, `category`, `status`) VALUES (?, ?, ?, ?, ?)";   
                if($statement = mysqli_prepare($conn, $sql)){
                    mysqli_stmt_bind_param($statement, "sssss", $param_email, $param_username, $param_password, $categ, $statusadd);

                    // set parameter
                    $categ = "admin";
                    $statusadd = "active";
                    $param_email = $email;
                    $param_username = $usern;
                    $param_password = password_hash($pword, PASSWORD_DEFAULT); // creates a password hash
                    if(mysqli_stmt_execute($statement)){// execute
                        echo '<script language="javascript">alert("Successfully Registered!");</script>';
                        echo '<script language="javascript">window.location = "login.php";</script>';
                    }else{
                        echo "Oops! Something went wrong. Please try again later.";
                    }
                    mysqli_stmt_close($statement);// close
                }
            }else{
                $sql = "INSERT INTO users (`email`, `uname`, `pword`, `category`, `status`) VALUES (?, ?, ?, ?, ?)";   
                if($statement = mysqli_prepare($conn, $sql)){
                    mysqli_stmt_bind_param($statement, "sssss", $param_email, $param_username, $param_password, $categ, $statusadd);

                    // set parameter
                    $categ = "customer";
                    $statusadd = "active";
                    $param_email = $email;
                    $param_username = $usern;
                    $param_password = password_hash($pword, PASSWORD_DEFAULT); // creates a password hash
                    if(mysqli_stmt_execute($statement)){// execute
                        echo '<script language="javascript">alert("Successfully Registered!");</script>';
                        echo '<script language="javascript">window.location = "login.php";</script>';//
                    }else{
                        echo "Oops! Something went wrong. Please try again later.";
                    }
                    mysqli_stmt_close($statement);// close
                }
            }
        }
    }
    
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/login.css">
    <link rel="icon" href="images/logo.png" type="image/x-icon">
    <title>Edz FashionHauz</title>
</head>
<body>
    <div class="background-blur"></div>
    <div class="container" id="main">
        <div class="log-in">
            <form action="loginaction.php" method="post">
                <h1 class="loginhead">Login</h1>
                <?php if(!empty($login_err)){echo '<div class="errormsg">'.$login_err.'</div>';} ?> 
                <label class="label" for="uname">Username</label>
                <input class="icon-user" type="text" name="uname" required="">
                <label class="label" for="pw">Password</label>
                <input class="icon-pw" type="password" name="pw" id="pw" required="">
                <div class="showpw"><input type="checkbox" id="show-password"><label for="show-password">Show Password</label></div>
                <button class="submit loginbut" type="submit" name="push">Login</button>
                <a href="forgot-pass.php?forgotpass=true" class="forgot">Forgot Password?</a>
            </form>
            
        </div>
        <div class="sign-up">
            <form action="forgot-pass.php" method="post">
                <h1 class="reghead">Register</h1>
                <label class="label" for="email">Email</label>
                <input class="icon-email" type="email" name="email" id="email" required="">
                <label class="label" for="usern">Username</label>
                <input class="icon-user" type="text" name="usern" id="usern" required="">
                <label class="label" for="pword">Password</label>
                <input class="icon-pw" type="password" name="pword" id="pword" required="">
                <div class="showpw"><input type="checkbox" id="show-passwordd"><label for="show-passwordd">Show Password</label></div>
                <button class="submit regbut" type="submit" name="register">Register</button>
                <span>By clicking register, you agree to our <a href="userside/terms.php">Terms and Condition</a></span>
            </form>
            
        </div>
        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-left">
                    <img src="images/logo.png" alt="#">
                    <div class="flex-lipat">
                        <h4>Have an account already?</h4><button id="lipat-right">Sign in</button>
                    </div>
                </div>
                <div class="overlay-right">
                    <img src="images/logo.png" alt="#">
                    <div class="flex-lipat">
                        <h4>Create account?</h4><button id="lipat-left">Sign up</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        const signupButton = document.getElementById('lipat-left');
        const signinButton = document.getElementById('lipat-right');
        const main = document.getElementById('main');
        
        signupButton.addEventListener('click', () =>{
            main.classList.add("right-panel-active");
        });
        signinButton.addEventListener('click', () =>{
            main.classList.remove("right-panel-active");
        });
        document.getElementById('show-password').addEventListener('change', function() {
            const passwordInput = document.getElementById('pw');
            if (this.checked) {
                passwordInput.type = 'text'; // Show password
            } else {
                passwordInput.type = 'password'; // Hide password
            }
        });
        document.getElementById('show-passwordd').addEventListener('change', function() {
            const passwordInputt = document.getElementById('pword');
            if (this.checked) {
                passwordInputt.type = 'text'; // Show password
            } else {
                passwordInputt.type = 'password'; // Hide password
            }
        });
    </script>
</body>
</html>