<?php
include '../config.php';
function isValidUser($conn, $username) {
    $stmt = $conn->prepare("SELECT uname FROM users WHERE uname = ? AND category = 'customer'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0; // Returns true if the user exists
}
if (isset($_COOKIE['username'])) {
    $username = htmlspecialchars($_COOKIE['username']);
    $userID = $_COOKIE['id'];


    // Validate the cookie against the database
    if (isValidUser($conn, $username)) {
        
    } else {
        echo "Invalid session. Please log in again.";
        // Optionally, delete the cookie if invalid
        setcookie("username", "", time() - 604800, "/");
        setcookie("category", "", time() - 604800, "/");
        setcookie("loggedin", "", time() - 604800, "/");
        setcookie("id", "", time() - 604800, "/");
        header("location: ../login.php");
    }
} else {
    echo '<script language="javascript">alert("Please login first.");</script>';
    echo '<script language="javascript">window.location.href = "../login.php";</script>';
}

if(isset($_POST['add-address'])){
    if(!empty($_POST['name'])&&!empty($_POST['number'])&&!empty($_POST['region_post'])&&!empty($_POST['province_post'])&&!empty($_POST['city_post'])&&!empty($_POST['barangay'])&&!empty($_POST['street'])&&!empty($_POST['postal'])){
       $name = $_POST['name']; 
       $number = $_POST['number']; 
       $region = htmlspecialchars($_POST['region_post']); 
       $province = $_POST['province_post']; 
       $city = $_POST['city_post']; 
       $barangay = $_POST['barangay']; 
       $street = $_POST['street']; 
       $postal = $_POST['postal']; 

        if (!preg_match("/^09\d{9}$/", $number)) { // Validate if contact number is exactly 11 digits and starts with 09
            echo '<script language="javascript">alert("Invalid input. Please enter a valid 11-digit contact number.");</script>';
            echo '<script language="javascript">window.location.href = "main.php";</script>';
            exit;
        }  

        $slqinsert = $conn->prepare("INSERT INTO `address` (`userID`, `name`, `cnumber`, `street`, `barangay`, `city` , `province` , `region`, `postal`) VALUES (?,?,?,?,?,?,?,?,?)");
        $slqinsert->bind_param("isssssssi", $userID, $name, $number, $street, $barangay, $city, $province, $region, $postal);
        if($slqinsert->execute()){
            echo '<script language="javascript">alert("Address added successfully.");</script>';
            echo '<script language="javascript">window.location.href = "addresses.php";</script>';
        }
    }else{
        echo '<script language="javascript">alert("Please fulfill all details correctly.");</script>';
        echo '<script language="javascript">window.location.href = "addresses.php";</script>';
    }
}else if(isset($_POST['update-address'])){
    if(!empty($_POST['name'])&&!empty($_POST['number'])&&!empty($_POST['region_post2'])&&!empty($_POST['province_post2'])&&!empty($_POST['city_post2'])&&!empty($_POST['barangay'])&&!empty($_POST['street'])&&!empty($_POST['postal'])){
       $id_address = $_POST['id_address']; 
       $name = $_POST['name']; 
       $number = $_POST['number']; 
       $region = htmlspecialchars($_POST['region_post2']); 
       $province = $_POST['province_post2']; 
       $city = $_POST['city_post2']; 
       $barangay = $_POST['barangay']; 
       $street = $_POST['street']; 
       $postal = $_POST['postal']; 

        if (!preg_match("/^09\d{9}$/", $number)) { // Validate if contact number is exactly 11 digits and starts with 09
            echo '<script language="javascript">alert("Invalid input. Please enter a valid 11-digit contact number.");</script>';
            echo '<script language="javascript">window.location.href = "addresses.php";</script>';
            exit;
        }    

        $slqupdate = $conn->prepare("UPDATE `address` SET `name` = ?, `cnumber` = ?, `street` = ?, `barangay` = ?, `city` = ?, `province` = ?, `region` = ?, `postal` = ? WHERE `id` = ?");
        $slqupdate->bind_param("sssssssii", $name, $number, $street, $barangay, $city, $province, $region, $postal, $id_address);
        if($slqupdate->execute()){
            echo '<script language="javascript">alert("Address updated successfully.");</script>';
            echo '<script language="javascript">window.location.href = "addresses.php";</script>';
        }
    }else{
        echo '<script language="javascript">alert("Please fulfill all details correctly.");</script>';
        echo '<script language="javascript">window.location.href = "addresses.php";</script>';
    }
}else if(isset($_POST['delete'])){
    $del_address = $_POST['address_id'];
    $sqldel = $conn->prepare("UPDATE `address` SET `status` = ? WHERE `id` = ?");
    $stats = "inactive";
    $sqldel->bind_param("si", $stats, $del_address);
    if($sqldel->execute()){
        echo '<script language="javascript">alert("Address deleted successfully.");</script>';
        echo '<script language="javascript">window.location.href = "addresses.php";</script>';
    }
}


$sqlfetch = $conn->prepare("SELECT * FROM `address` WHERE userID = ?");
$sqlfetch->bind_param("i", $userID);
$sqlfetch->execute();
$data_fetch = $sqlfetch->get_result();

if(isset($_POST['address_id'])){
    $address_id = $_POST['address_id'];

    $sqledit = $conn->prepare("SELECT * FROM `address` WHERE id = ?");
    $sqledit->bind_param("i", $address_id);
    $sqledit->execute();
    $data_edit = $sqledit->get_result();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/account.css">
    <link rel="stylesheet" type="text/css" href="css/address.css">
    <link rel="icon" href="../images/logo.ico" type="image/x-icon">
    <title>Edz FashionHauz</title>
</head>
<body>
    <?php include 'nav.php'; ?>
    <main>
        <div class="navright">
            <div class="navleft">
                <div class="navtop">
                    <h1><?php echo $username; ?></h1>
                    <h3>User</h3>
                </div>
                <div class="navmiddle">
                    <a href="account.php" >Account Info</a>
                    <a href="addresses.php" class="active">Addresses</a>
                    <a href="history.php">Purchase History</a>
                    <a href="change-pass.php" >Change Password</a>
                </div>
                <div class="navbottom">
                    <form action="../logout.php" class="logout" method="post">
                        <input type="hidden" name="usern" value="<?php echo $username; ?>">
                        <button type="submit" name="logout">Logout</button>
                    </form>
                </div>
            </div>
            <div class="container-box">
                <div class="heavyy">
                    <form action="addresses.php" method="post" class="potek">
                        <button name="addaddress" type="submit"><img src="../icons/create.png" alt="">Add</button>
                    </form>
                </div>
                <div class="newbox">
                    <?php
                        while($row=mysqli_fetch_assoc($data_fetch)){
                            $data_address_id = $row['id'];
                            $data_name = $row['name'];
                            $data_number = $row['cnumber'];
                            $data_street = $row['street'];
                            $data_barangay = $row['barangay'];
                            $data_city = $row['city'];
                            $data_province = $row['province'];
                            $data_region = $row['region'];
                            $data_postal = $row['postal'];
                    ?>
                    <div class="box-address">
                            <h3 class="name"><?php echo $data_name; ?></h3>
                            <h3 class="name"><?php echo $data_number; ?></h3>
                            <h3 class="name"><?php echo $data_street; ?></h3>
                            <h3 class="name"><?php echo $data_barangay; ?></h3>
                            <h3 class="name"><?php echo $data_city; ?></h3>
                            <h3 class="name"><?php echo $data_province; ?></h3>
                            <h3 class="name"><?php echo $data_region; ?></h3>
                            <h3 class="name"><?php echo $data_postal; ?></h3>
                            <form action="address-edit.php" method="post">
                                <input type="hidden" value="<?php echo $data_address_id; ?>" name="address_id">
                                <button type="submit"><img src="../icons/update.png" alt="">Edit</button>
                                <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this address?');"><img src="../icons/delete.png" alt="">Delete</button>
                            </form>
                    </div>
                    <?php };?>
                </div>
            </div>
        </div>
    </main>

    <div class="siction" id="display" style="display: <?php echo isset($_POST['address_id']) ? 'flex' : 'none'; ?>;">
        <div class="newaddress">
            <form action="addresses.php" method="post">
                <img src="../icons/close-bg.png" class="close" alt="asdasdas" onclick="document.getElementById('display').style.display = 'none';">
                <h2>Edit Address</h2>
                <?php 
                    if($row2=mysqli_fetch_assoc($data_edit)){
                        $id_address = $row2['id'];
                        $name_address = $row2['name'];
                        $number_address = $row2['cnumber'];
                        $street_address = $row2['street'];
                        $barangay_address = $row2['barangay'];
                        $city_address = $row2['city'];
                        $province_address = $row2['province'];
                        $region_address = $row2['region'];
                        $postal_address = $row2['postal'];

                    }
                
                ?>
                <div class="address-mid">
                    <div class="address-left">
                        <input type="text" name="name" value="<?php echo $name_address; ?>">
                        <input type="text" name="number" value="<?php echo $number_address; ?>">
                        <select name="region2" id="region2" onchange="bagotext('region2', 'region_post2')">
                            <option value="<?php echo $region_address; ?>" selected><?php echo $region_address; ?></option>
                        </select>
                        <input type="hidden" name="region_post2" id="region_post2">
                        <select name="province2" id="province2" onchange="updateText('province2', 'province_post2')">
                            <option value="<?php echo $province_address; ?>" selected><?php echo $province_address; ?></option>
                        </select>
                        <input type="hidden" name="province_post2" id="province_post2">
                    </div>
                    <div class="address-right">
                        <select name="city2" id="city2" onchange="updateText('city2', 'city_post2')">
                            <option value="<?php echo $region_address; ?>" selected><?php echo $city_address; ?></option>
                        </select>
                        <input type="hidden" name="city_post2" id="city_post2">
                        <input type="hidden" name="id_address" value="<?php echo $id_address; ?>">
                        <input type="text" name="barangay" value="<?php echo $barangay_address; ?>">
                        <input type="text" name="street" value="<?php echo $street_address; ?>">
                        <input type="text" name="postal" value="<?php echo $postal_address; ?>">
                    </div>
                </div>
                <button type="submit" name="update-address"><img src="../icons/update.png" alt="">Update</button>
            </form>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    <script>
        function updateText(selectId, hiddenInputId) {
            const selectElement = document.getElementById(selectId);
            const hiddenInput = document.getElementById(hiddenInputId);
            hiddenInput.value = selectElement.options[selectElement.selectedIndex].textContent;
        }
        function bagotext(idone, idtwo) {
            const oneElement = document.getElementById(idone);
            const twoInput = document.getElementById(idtwo);
            twoInput.value = oneElement.value;
        }
        //update address
        // Load regions into the region dropdown
        fetch("../address/node_modules/philippines/regions.json")
        .then(response => response.json())
        .then(regions => {
            const regionSelecttwo = document.getElementById("region2");
            regions.forEach(region => {
            const optiontwo = document.createElement("option");
            optiontwo.value = region.key;
            optiontwo.textContent = region.name;
            regionSelecttwo.appendChild(optiontwo);
            });
        })
        .catch(error => console.error("Error fetching regions:", error));

        

        // populate update address province
        document.getElementById("region2").addEventListener("change", function() {
        const regionCodetwo = this.value;
        fetch("../address/node_modules/philippines/provinces.json")
            .then(response => response.json())
            .then(provinces => {
                const provinceSelecttwo = document.getElementById("province2");
                provinceSelecttwo.innerHTML = '<option value="" disabled selected hidden>Province</option>';
                provinces
                    .filter(province => province.region === regionCodetwo)
                    .forEach(province => {
                    const option3 = document.createElement("option");
                    option3.value = province.key;
                    option3.textContent = province.name;
                    provinceSelecttwo.appendChild(option3);
                });
            });
        });

        // Populate cities when a province is selected
        document.getElementById("province2").addEventListener("change", function() {
        const provinceCodetwo = this.value;
        fetch("../address/node_modules/philippines/cities.json")
            .then(response => response.json())
            .then(cities => {
            const citySelecttwo = document.getElementById("city2");
            citySelecttwo.innerHTML = '<option value="" disabled selected hidden>City</option>';
            cities
                .filter(city => city.province === provinceCodetwo)
                .forEach(city => {
                const optiontwo = document.createElement("option");
                optiontwo.value = city.key;
                optiontwo.textContent = city.name;
                citySelecttwo.appendChild(optiontwo);
                });
            });
        });
    </script>
</body>
</html>