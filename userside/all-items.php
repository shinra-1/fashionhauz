<?php
include '../config.php';
// function isValidUser($conn, $username) {
//     $stmt = $conn->prepare("SELECT uname FROM users WHERE uname = ? AND category = 'customer'");
//     $stmt->bind_param("s", $username);
//     $stmt->execute();
//     $stmt->store_result();
//     return $stmt->num_rows > 0; // Returns true if the user exists
// }
// if (isset($_COOKIE['username'])) {
//     $username = htmlspecialchars($_COOKIE['username']);
//     $userID = $_COOKIE['id'];


//     // Validate the cookie against the database
//     if (isValidUser($conn, $username)) {
        
//     } else {
//         echo "Invalid session. Please log in again.";
//         // Optionally, delete the cookie if invalid
//         setcookie("username", "", time() - 604800, "/");
//         setcookie("category", "", time() - 604800, "/");
//         setcookie("loggedin", "", time() - 604800, "/");
//         setcookie("id", "", time() - 604800, "/");
//         header("location: ../login.php");
//     }
// } else {
//     echo '<script language="javascript">alert("Please login first.");</script>';
//     echo '<script language="javascript">window.location.href = "../login.php";</script>';
// }


$category = isset($_GET['category']) ? $_GET['category'] : '';
if (!empty($category)) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE category = ?");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $stmt = $conn->prepare("SELECT * FROM products ");
    $stmt->execute();
    $result = $stmt->get_result();
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/allitems.css">
    <link rel="icon" href="../images/logo.ico" type="image/x-icon">
    <title>Edz FashionHauz</title>
</head>
<body>
    <?php include 'nav.php'; ?>
    <main>
        <div class="container">
            <h1><?php echo $category = isset($_GET['category']) ? $_GET['category'] : 'All Items'; ?></h1>
            <div class="card-container">
                <?php
                    // Display the items for the selected category
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                ?>
                <div class="card">
                    <img src="../products/<?php echo $row['image']; ?>" alt="">
                    <h2><?php echo $row['prodname']; ?></h2>
                    <p><?php echo $row['description']; ?></p>
                    <a href="item.php?id=<?php echo $row['id']; ?>" class="addcart"><div class="itemlink">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M351.9 329.506H206.81l-3.072-12.56H368.16l26.63-116.019-217.23-26.04-9.952-58.09h-50.4v21.946h31.894l35.233 191.246a32.927 32.927 0 1 0 36.363 21.462h100.244a32.825 32.825 0 1 0 30.957-21.945zM181.427 197.45l186.51 22.358-17.258 75.195H198.917z" data-name="Shopping Cart"/></svg>
                        Add to Cart
                    </div></a>
                </div>
                <?php }
                    } else {
                        echo "<p>No items found for this category.</p>";
                    }
                    $stmt->close(); ?>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>