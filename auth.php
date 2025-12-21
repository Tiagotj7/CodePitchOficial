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

function isAdmin()
{
    return isset($_SESSION['is_admin']) && (int)$_SESSION['is_admin'] === 1;
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header("Location: index.php?login=1");
        exit;
    }
}

function requireAdmin()
{
    if (!isLoggedIn() || !isAdmin()) {
        header("Location: index.php");
        exit;
    }
}