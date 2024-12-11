<?php
include '../config.php';

if (isset($_GET['postal'])) {
    $postal = $_GET['postal'];

    if ($postal >= 1000 && $postal <= 4999) {
        $shipping_fee = 70;
    } elseif ($postal >= 5000 && $postal <= 5599) {
        $shipping_fee = 80;
    } elseif ($postal >= 5600 && $postal <= 5999) {
        $shipping_fee = 85;
    } elseif ($postal >= 6000 && $postal <= 6999) {
        $shipping_fee = 90;
    } elseif ($postal >= 7000 && $postal <= 7999) {
        $shipping_fee = 100;
    } else{
        $shipping_fee = 110;
    }

    echo json_encode(['shipping_fee' => $shipping_fee]);
}
?>
