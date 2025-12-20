<?php
// auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function currentUserId()
{
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

function currentUserName()
{
    return isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header("Location: index.php?login=1");
        exit;
    }
}