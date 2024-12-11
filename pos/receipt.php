<?php

include '../config.php';
require('../fpdf/fpdf.php'); 

function isValidUser($conn, $username) {
    $stmt = $conn->prepare("SELECT uname FROM users WHERE uname = ? AND category = 'staff'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0; // Returns true if the user exists
}
if (isset($_COOKIE['username'])) {
    $username = htmlspecialchars($_COOKIE['username']);
    $user_id = $_COOKIE['id'];

    // Validate the cookie against the database
    if (isValidUser($conn, $username)) {
        $search = $conn->prepare("SELECT fname FROM users where uname = '$username'");
        $search->execute();
        $resultss=$search->get_result();
        if ($resultss->num_rows > 0) {
            if($namethis = $resultss->fetch_assoc()) {
                $usern = $namethis['fname'];
            }
        }
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
    header("location: ../login.php");
}

function fetchCartItems($conn,$username) { 
    $username = htmlspecialchars($_COOKIE['username']);
    $cartItem = [];
    $stmt5 = $conn->prepare("SELECT * FROM cart where usercateg = '$username'");
    $stmt5->execute();
    $result5 = $stmt5->get_result();
    if ($result5->num_rows > 0) {
        while ($row5 = $result5->fetch_assoc()) {
            $cartItem[] = $row5;
        }
    }
    return $cartItem;
}
$cartItems = fetchCartItems($conn,$username);

function intoOrderlist($conn, $username, $user_id, $totalprice,  $cartItems) {
    $ordersql = $conn->query("SELECT MAX(orderID) AS max FROM orderlist");//kuhain max orderid para plus 1 sa order na to
    $row2 = $ordersql->fetch_assoc();
    $orderNumber = $row2['max'] ? $row2['max'] + 1 : 1;

    $countitem = $conn->query("SELECT SUM(qty) AS qty FROM cart WHERE usercateg = '$username'");//kunin total qty ng laman ng cart
    $row3 = $countitem->fetch_assoc();
    $qty = $row3['qty'];

    $insertSql = $conn->prepare("INSERT INTO orderlist (`orderID`, `userID`, `total_qty`,`mop`, `total_price`, `address_id`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?)");//insert order sa orderlist
    $mop = "Cash";
    $status = "Completed";
    $address = 0;
    $insertSql->bind_param("iiisdis", $orderNumber, $user_id, $qty,$mop, $totalprice, $address, $status);
    $insertSql->execute();


    $insert2Sql = $conn->prepare("INSERT INTO order_details (orderID, prodID, sizeID,qty, price) VALUES (?, ?, ?, ?, ?)");//insert bawat order item na laman ng cart papunta sa order details
    foreach ($cartItems as $item) {
        $prodname = $item['prodname'];
        $size = $item['size'];
        $qty = $item['qty'];
        $price = $item['price'];

        $search2 = $conn->query("SELECT id FROM products WHERE prodname = '$prodname'");//kuha id ng each product para makuha size id
        $search2row = $search2->fetch_assoc();
        $prodID = $search2row['id'];

        $search3 = $conn->prepare("SELECT size_id FROM product_sizes WHERE id = ? AND size = ?");//kuha size id based sa prod id and size name
        $search3->bind_param("is", $prodID, $size); 
        $search3->execute();
        $resultsearch = $search3->get_result();
        if ($resultsearch->num_rows > 0) {
            $search3row = $resultsearch->fetch_assoc();
            $sizeID = $search3row['size_id'];
        } else {
            echo '<script language="javascript">alert("aaaa.");</script>';
            exit;
        }
        $insert2Sql->bind_param("iiiid", $orderNumber, $prodID, $sizeID, $qty, $price);
        $insert2Sql->execute();

        $updateQty = $conn->prepare("UPDATE product_sizes SET qty = qty - 1 WHERE id = ? AND size = ?");//update qty size ng product na yon minus 1
        $updateQty->bind_param("is", $prodID, $size);
        $updateQty->execute();
    }

    $deleteCart = $conn->prepare("DELETE FROM cart WHERE usercateg = ?");//delete items sa cart ng pos after mag checkout
    $deleteCart->bind_param("s", $username);
    $deleteCart->execute();

    $archives = $conn->prepare("INSERT INTO activitylogs (uname, description) VALUES (?, ?)");
    $detail ='Transacted order #'.$orderNumber;
    $archives->bind_param("ss", $username, $detail);
    $archives->execute();
    // Close the statement
    $ordersql->close();
    $countitem->close();
    $insertSql->close();
    $insert2Sql->close();
    $updateQty->close();
    $deleteCart->close();
}


$sql = $conn->prepare("SELECT COUNT(*) as count FROM orderlist");
$sql->execute();
$results=$sql->get_result();
if ($results->num_rows > 0) {
    if($rowcount = $results->fetch_assoc()) {
        $counter = $rowcount['count'] +1;
    }
}


$cash=0;
if(isset($_POST['checkout'])){
    $cash = (double)$_POST['cash'];
    $totalprice = (double)$_POST['totalprice'];
    
    if (!is_numeric($cash) || !is_numeric($totalprice)) {    // Validate inputs kung number ba talaga sila
        echo '<script language="javascript">alert("Invalid input. Please enter valid numbers.");</script>';
        echo '<script language="javascript">window.location.href = "main.php";</script>';
        exit;
    }
    if($cash || $totalprice){
        $sukli = $cash - $totalprice;
        if($sukli <0){
            echo '<script language="javascript">alert("Insufficient cash, please transact again.");</script>';
            echo '<script language="javascript">window.location.href = "main.php";</script>';
            exit;
        }else{
            try {
                $pdf = new FPDF('P', 'mm', array(57,100));
                $pdf->AddPage();
                $pdf->AddFont('Times-Roman', '', 'times.php');
                $pdf->AddFont('Times-Bold', '', 'timesb.php');
                intoOrderlist($conn, $username, $user_id, $totalprice,  $cartItems);

                $cellWidth = 5; 
                $cellHeight = 1;
                $x = 22;
                $y = 2;

                $pdf->SetMargins(0, 0, 0,0);
                $pdf->SetXY($x, $y);
                $pdf->SetFont('Times-Bold', '', 11);
                $pdf->Cell($cellWidth, $cellHeight, 'Edz FashionHauz', 0, 0, 'C', false);
                $pdf->SetXY($x, 5);
                $pdf->SetFont('Times-Roman', '', 8);
                $pdf->Cell($cellWidth, $cellHeight, 'Banga 1st, Plaridel Bulacan', 0,0, 'C', false);
                $pdf->SetXY($x, 8);
                $pdf->SetFont('Times-Roman', '', 8);
                $pdf->Cell($cellWidth, $cellHeight, '09123123123', 0,0, 'C', false);
                //end of header
                $pdf->SetXY(1, 14);
                $pdf->SetFont('Times-Roman', '', 8);
                $pdf->Cell($cellWidth, $cellHeight, ' Cashier: '.$usern.'', 0,0, 'L', false);
                $pdf->SetXY(1, 18);
                $pdf->Cell($cellWidth, $cellHeight, ' Order #:'.$counter, 0, 0, 'L', false);//baguhin mamaya order number
                $date = date("M. d, Y");
                $pdf->SetXY(43, 18);
                $pdf->Cell($cellWidth, $cellHeight, 'Date: '.$date.' ', 0, 0, 'R', false);
                $pdf->SetXY($x, 20);
                $pdf->Cell($cellWidth, $cellHeight,'-----------------------------------------------',0,0,'C', false);
                $pdf->SetXY(1, 23);
                $pdf->Cell($cellWidth, $cellHeight, ' Name', 0, 0, 'L', false);
                $pdf->SetXY(24, 23);
                $pdf->Cell($cellWidth, $cellHeight, 'Size', 0, 0, 'C', false);
                $pdf->SetXY(30, 23);
                $pdf->Cell($cellWidth, $cellHeight, 'Qty', 0, 0, 'C', false);
                $pdf->SetXY(39, 23);
                $pdf->Cell($cellWidth, $cellHeight, 'Price', 0, 0, 'R', false);
                $pdf->SetXY($x, 25);
                //products
                $pdf->Cell($cellWidth, $cellHeight,'-----------------------------------------------',0,0,'C', false);
                $tempY = 25;
                foreach ($cartItems as $all){
                    $tempY = $tempY +3;
                    $pdf->SetXY(1.5, $tempY);
                    $pdf->Cell($cellWidth, $cellHeight, $all['prodname'], 0, 0, 'L', false);
                    $pdf->SetXY(24, $tempY);
                    $pdf->Cell($cellWidth, $cellHeight, $all['size'], 0, 0, 'C', false);
                    $pdf->SetXY(30, $tempY);
                    $pdf->Cell($cellWidth, $cellHeight, $all['qty'], 0, 0, 'C', false);
                    $pdf->SetXY(41, $tempY);
                    $pdf->Cell($cellWidth, $cellHeight, 'P'.number_format($all['price'], 2), 0, 0, 'R', false);
                }
                $pdf->SetXY($x, $tempY+=3);
                $pdf->Cell($cellWidth, $cellHeight,'-----------------------------------------------',0,0,'C', false);
                $pdf->SetAutoPageBreak(false); 
                $pdf->SetXY(1, $tempY+=3);
                $pdf->Cell($cellWidth, $cellHeight, 'Total:', 0, 0, 'L', false);
                $pdf->SetXY(41, $tempY);
                $pdf->Cell($cellWidth, $cellHeight, 'P'.number_format($totalprice, 2), 0, 0, 'R', false);
                $pdf->SetXY(1, $tempY+=3);
                $pdf->Cell($cellWidth, $cellHeight, 'Cash:', 0, 0, 'L', false);
                $pdf->SetXY(41, $tempY);
                $pdf->Cell($cellWidth, $cellHeight, 'P'.number_format($cash, 2), 0, 0, 'R', false);
                $pdf->SetXY($x, $tempY+=3);
                $pdf->Cell($cellWidth, $cellHeight,'-----------------------------------------------',0,0,'C', false);
                $pdf->SetXY(1, $tempY+=3);
                $pdf->Cell($cellWidth, 1, 'Change:', 0, 0, 'L', false);
                $pdf->SetXY(41, $tempY);
                $pdf->Cell($cellWidth, 1, 'P'.number_format($sukli, 2), 0, 0, 'R', false);
                $pdf->SetXY(9, $tempY+=2);
                $pdf->SetLineWidth(0.5);
                //footer
                $pdf->MultiCell(30, 3,'Thank you for purchasing! Scan this qr to visit us again!',0,'C', false);
                $pdf->SetXY(30, $tempY+=9);
                $pdf->Image('../images/qr.jpg', 19.5, $tempY, -1200);
                $pdf->SetXY($x, $tempY+=9.5);
                $pdf->SetFont('Times-Bold', '', 8);
                $pdf->Cell($cellWidth, $cellHeight,'POS Solutions',0,0,'C', false);
                $pdf->SetXY($x, $tempY+=3);
                $pdf->SetFont('Times-Roman', '', 8);
                $pdf->Cell($cellWidth, $cellHeight,'Sipat, Plaridel, Bulacan',0,0,'C', false);
                $pdf->SetXY($x, $tempY+=3);
                $pdf->Cell($cellWidth, $cellHeight,'09158658172',0,0,'C', false);
                


                

                $pdfFileName = 'receipt.pdf';
                $pdf->Output('F', $pdfFileName);
                header("Location: $pdfFileName");
                exit;
            } catch (Exception $e) {
                echo "Couldn't print to this printer: " . $e->getMessage() . "\n";
            }
            
        }
    }
    
}else{
    echo '<script language="javascript">alert("Invalid access.");</script>';
    echo '<script language="javascript">window.location.href = "main.php";</script>';
    exit;
}



?>