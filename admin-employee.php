<?php

include 'config.php';
function isValidUser($conn, $username) {
    $stmt = $conn->prepare("SELECT uname FROM users WHERE uname = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0; // Returns true if the user exists
}
if (isset($_COOKIE['username'])) {
    $username = htmlspecialchars($_COOKIE['username']);

    // Validate the cookie against the database
    if (isValidUser($conn, $username)) {
        
    } else {
        echo "Invalid session. Please log in again.";
        // Optionally, delete the cookie if invalid
        setcookie("username", "", time() - 604800, "/");
        setcookie("category", "", time() - 604800, "/");
        setcookie("loggedin", "", time() - 604800, "/");
        setcookie("id", "", time() - 604800, "/");
    }
} else {
    echo "Hello, Guest! Please log in.";
    header("location: index.php");//palitan to pag may login page na
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/admin-emplist.css">
    <link rel="icon" href="images/logo.ico" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
    <title>Edz FashionHauz</title>
</head>
<body>
    <?php include 'admin-nav.php'; ?>
    <div class="upper margin">
        <button><img src="icons/create.png" alt="create">Create</button>
    </div>
    <h1 class="content-title">Employee List</h1>
    <section>
        <div class="table-bg">
            <table>
                <thead>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Contact</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Creation Date</th>
                    <th>Action</th>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Juan</td>
                        <td>Dela Cruz</td>
                        <td>09158658163</td>
                        <td>juan</td>
                        <td>juan123</td>
                        <td>01/20/2024</td>
                        <td>
                            <div class="option">
                                <a href="#" class="op1"> Update</a>
                                <a href="#" class="op2"> Delete</a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td>Juan</td>
                        <td>Dela Cruz</td>
                        <td>09158658163</td>
                        <td>juan</td>
                        <td>juan123</td>
                        <td>01/20/2024</td>
                        <td>
                            <div class="option">
                                <a href="#" class="op1"> Update</a>
                                <a href="#" class="op2"> Delete</a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td>Juan</td>
                        <td>Dela Cruz</td>
                        <td>09158658163</td>
                        <td>juan</td>
                        <td>juan123</td>
                        <td>01/20/2024</td>
                        <td>
                            <div class="option">
                                <a href="#" class="op1"> Update</a>
                                <a href="#" class="op2"> Delete</a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Juan</td>
                        <td>Dela Cruz</td>
                        <td>09158658163</td>
                        <td>juan</td>
                        <td>juan123</td>
                        <td>01/20/2024</td>
                        <td>
                            <div class="option">
                                <a href="#" class="op1"> Update</a>
                                <a href="#" class="op2"> Delete</a>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
    <?php include 'admin-footer.php'; ?>
</body>
</html>