<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once "includes/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    // Validation
    if ($name === "" || $email === "" || $password === "" || $confirm_password === "") {

        $error = "Please fill in all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";

    } elseif (strlen($password) < 6) {

        $error = "Password must contain at least 6 characters.";

    } else {

        // Check existing email
        $check_sql = "SELECT id FROM students WHERE email = ?";

        $check_stmt = $conn->prepare($check_sql);

        if (!$check_stmt) {

            $error = "Database error: " . $conn->error;

        } else {

            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();

            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {

                $error = "This email is already registered.";

            } else {

                // Create secure password hash
                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // Insert student
                $insert_sql = "
                    INSERT INTO students
                    (name, email, password)
                    VALUES (?, ?, ?)
                ";

                $insert_stmt = $conn->prepare($insert_sql);

                if (!$insert_stmt) {

                    $error = "Insert preparation failed: " . $conn->error;

                } else {

                    $insert_stmt->bind_param(
                        "sss",
                        $name,
                        $email,
                        $hashed_password
                    );

                    if ($insert_stmt->execute()) {

                        // Confirm insertion
                        $new_student_id = $conn->insert_id;

                        if ($new_student_id > 0) {

                            header(
                                "Location: login.php?registered=1"
                            );

                            exit();

                        } else {

                            $error = "Registration appeared successful, but student ID was not created.";
                        }

                    } else {

                        $error = "Registration failed: " . $insert_stmt->error;
                    }

                    $insert_stmt->close();
                }
            }

            $check_stmt->close();
        }
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Student Registration - Smart Scholarship</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {

            font-family: Arial, sans-serif;

            min-height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 20px;

            background:
                linear-gradient(
                    135deg,
                    #eef2ff,
                    #f8fafc
                );
        }

        .container {

            width: 100%;
            max-width: 450px;
        }

        .card {

            background: white;

            padding: 35px;

            border-radius: 18px;

            box-shadow:
                0 15px 40px rgba(0,0,0,0.10);
        }

        .header {

            text-align: center;

            margin-bottom: 25px;
        }

        .header h1 {

            color: #4f46e5;

            font-size: 28px;

            margin-bottom: 8px;
        }

        .header p {

            color: #64748b;

            font-size: 14px;
        }

        .form-group {

            margin-bottom: 18px;
        }

        label {

            display: block;

            margin-bottom: 7px;

            color: #334155;

            font-weight: 600;

            font-size: 14px;
        }

        input {

            width: 100%;

            padding: 13px;

            border: 1px solid #cbd5e1;

            border-radius: 9px;

            font-size: 15px;

            outline: none;
        }

        input:focus {

            border-color: #4f46e5;

            box-shadow:
                0 0 0 3px
                rgba(79,70,229,0.10);
        }

        .btn {

            width: 100%;

            padding: 13px;

            border: none;

            border-radius: 9px;

            background: #4f46e5;

            color: white;

            font-size: 16px;

            font-weight: 600;

            cursor: pointer;
        }

        .btn:hover {

            background: #4338ca;
        }

        .error {

            background: #fee2e2;

            color: #b91c1c;

            padding: 12px;

            border-radius: 8px;

            margin-bottom: 18px;

            font-size: 14px;
        }

        .login-link {

            text-align: center;

            margin-top: 22px;

            color: #64748b;

            font-size: 14px;
        }

        .login-link a {

            color: #4f46e5;

            text-decoration: none;

            font-weight: 600;
        }

        .home-link {

            text-align: center;

            margin-top: 15px;
        }

        .home-link a {

            color: #64748b;

            text-decoration: none;

            font-size: 13px;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="card">

        <div class="header">

            <h1>🎓 Smart Scholarship</h1>

            <p>Create your student account</p>

        </div>


        <?php if ($error !== ""): ?>

            <div class="error">

                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <form method="POST" action="register.php">

            <div class="form-group">

                <label>Name</label>

                <input
                    type="text"
                    name="name"
                    placeholder="Enter your name"
                    required
                >

            </div>


            <div class="form-group">

                <label>Email</label>

                <input
                    type="email"
                    name="email"
                    placeholder="Enter your email"
                    required
                >

            </div>


            <div class="form-group">

                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Minimum 6 characters"
                    required
                >

            </div>


            <div class="form-group">

                <label>Confirm Password</label>

                <input
                    type="password"
                    name="confirm_password"
                    placeholder="Re-enter password"
                    required
                >

            </div>


            <button
                type="submit"
                class="btn"
            >
                Create Account
            </button>

        </form>


        <div class="login-link">

            Already have an account?

            <a href="login.php">
                Login here
            </a>

        </div>


        <div class="home-link">

            <a href="index.php">
                ← Back to Home
            </a>

        </div>

    </div>

</div>

</body>

</html>