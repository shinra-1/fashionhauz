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
            <form action="dashboard.html" method="post">
                <h1 class="loginhead">Login</h1>
<!--                 -->
                <label class="label" for="uname">Username</label>
                <input class="icon-user" type="text" name="uname" required="">
                <label class="label" for="pw">Password</label>
                <input class="icon-pw" type="password" name="pw" id="pw" required="">
                <div class="showpw"><label><input type="checkbox" id="show-password">Show Password</label></div>
                <button class="submit" type="submit" name="push">Login</button>
            </form>
            
        </div>
        <div class="sign-up">
            <form action="dashboard.html" method="post">
                <h1 class="reghead">Register</h1>
<!--                  -->
                <label class="label" for="email">Email</label>
                <input class="icon-email" type="email" name="email" id="email" required="">
                <label class="label" for="usern">Username</label>
                <input class="icon-user" type="text" name="usern" id="usern" required="">
                <label class="label" for="pword">Password</label>
                <input class="icon-pw" type="password" name="pword" id="pword" required="">
                <div class="showpw"><label><input type="checkbox" id="show-passwordd"> Show Password</label></div>
                <button class="submit" type="submit">Register</button>
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
