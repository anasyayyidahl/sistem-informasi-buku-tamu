<?php
session_start();

// Ganti ini dengan token yang kamu tentukan
$adminToken = "bdk2025";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $inputToken = $_POST["token"];

    if ($inputToken === $adminToken) {
        // Set session login + keamanan tambahan
        $_SESSION["admin_logged_in"] = true;
        $_SESSION["user_agent"] = $_SERVER["HTTP_USER_AGENT"];
        $_SESSION["last_activity"] = time();
        
        header("Location: admin.php");
        exit;
    } else {
        echo "<script>alert('Token salah!'); window.location.href='login.html';</script>";
        exit;
    }
} else {
    header("Location: login.html");
    exit;
}
