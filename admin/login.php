<?php

session_start();

require_once "../includes/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $sql = "
        SELECT id, username, password
        FROM admins
        WHERE username = ?
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("s", $username);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $admin = $result->fetch_assoc();

        /*
        |--------------------------------------------------------------------------
        | Check Password
        |--------------------------------------------------------------------------
        */

        if (
            $password === $admin["password"] ||
            password_verify($password, $admin["password"])
        ) {

            $_SESSION["admin_id"] = $admin["id"];

            $_SESSION["admin_username"] =
                $admin["username"];

            header("Location: dashboard.php");

            exit();

        } else {

            $error = "Invalid username or password.";
        }

    } else {

        $error = "Invalid username or password.";
    }

    $stmt->close();
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Login</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {

            margin: 0;

            font-family: Arial, sans-serif;

            background:
                linear-gradient(
                    135deg,
                    #1e3a8a,
                    #4f46e5
                );

            min-height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;
        }

        .login-box {

            width: 400px;

            max-width: 90%;

            background: white;

            padding: 35px;

            border-radius: 12px;

            box-shadow:
                0 10px 30px
                rgba(0,0,0,0.2);
        }

        .login-box h1 {

            text-align: center;

            margin-top: 0;

            color: #1e3a8a;
        }

        .login-box p {

            text-align: center;

            color: #64748b;

            margin-bottom: 30px;
        }

        label {

            display: block;

            margin-bottom: 7px;

            font-weight: bold;

            color: #334155;
        }

        input {

            width: 100%;

            padding: 12px;

            margin-bottom: 20px;

            border: 1px solid #cbd5e1;

            border-radius: 6px;

            font-size: 15px;
        }

        input:focus {

            outline: none;

            border-color: #4f46e5;
        }

        button {

            width: 100%;

            padding: 13px;

            background: #4f46e5;

            color: white;

            border: none;

            border-radius: 6px;

            font-size: 16px;

            font-weight: bold;

            cursor: pointer;
        }

        button:hover {

            background: #3730a3;
        }

        .error {

            background: #fee2e2;

            color: #991b1b;

            padding: 12px;

            border-radius: 6px;

            margin-bottom: 20px;

            text-align: center;
        }

        .demo {

            margin-top: 20px;

            background: #f1f5f9;

            padding: 12px;

            border-radius: 6px;

            text-align: center;

            font-size: 14px;

            color: #475569;
        }

        .back {

            display: block;

            text-align: center;

            margin-top: 20px;

            color: #4f46e5;

            text-decoration: none;
        }

    </style>

</head>

<body>

<div class="login-box">

    <h1>Admin Login</h1>

    <p>Smart Scholarship Management System</p>


    <?php if ($error != ""): ?>

        <div class="error">

            <?php
            echo htmlspecialchars($error);
            ?>

        </div>

    <?php endif; ?>


    <form method="POST" action="login.php">

        <label>
            Username
        </label>

        <input
            type="text"
            name="username"
            placeholder="Enter admin username"
            required
        >


        <label>
            Password
        </label>

        <input
            type="password"
            name="password"
            placeholder="Enter admin password"
            required
        >


        <button type="submit">
            Login
        </button>

    </form>


    <div class="demo">

        <strong>Demo Admin</strong><br>

        Username: admin<br>

        Password: admin123

    </div>


    <a
        href="../login.php"
        class="back"
    >
        ← Student Login
    </a>

</div>

</body>

</html>