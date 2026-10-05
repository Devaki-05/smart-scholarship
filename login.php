<?php

session_start();

require_once "includes/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {

        $error = "Please enter both email and password.";

    } else {

        $sql = "SELECT id, name, email, password FROM students WHERE email = ?";

        $stmt = $conn->prepare($sql);

        if ($stmt) {

            $stmt->bind_param("s", $email);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 1) {

                $student = $result->fetch_assoc();

                if (password_verify($password, $student["password"])) {

                    // Store student information in session
                    $_SESSION["student_id"] = $student["id"];
                    $_SESSION["student_name"] = $student["name"];
                    $_SESSION["student_email"] = $student["email"];

                    // Redirect to dashboard
                    header("Location: dashboard.php");
                    exit();

                } else {

                    $error = "Invalid email or password.";

                }

            } else {

                $error = "Invalid email or password.";

            }

            $stmt->close();

        } else {

            $error = "Something went wrong. Please try again.";

        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Login | Smart Scholarship</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea, #764ba2);
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 430px;
            background: white;
            border-radius: 18px;
            padding: 40px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.20);
        }

        .logo {
            width: 70px;
            height: 70px;
            margin: 0 auto 18px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            font-weight: bold;
        }

        h1 {
            text-align: center;
            color: #222;
            margin-bottom: 8px;
            font-size: 28px;
        }

        .subtitle {
            text-align: center;
            color: #777;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .error {
            background: #ffe5e5;
            color: #c62828;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
            transition: 0.3s;
        }

        input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.12);
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .login-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(102, 126, 234, 0.30);
        }

        .register-text {
            text-align: center;
            margin-top: 25px;
            color: #777;
            font-size: 14px;
        }

        .register-text a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }

        .register-text a:hover {
            text-decoration: underline;
        }

        .home-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #555;
            text-decoration: none;
            font-size: 14px;
        }

        .home-link:hover {
            color: #667eea;
        }

    </style>

</head>

<body>

    <div class="login-container">

        <div class="logo">
            S
        </div>

        <h1>Student Login</h1>

        <p class="subtitle">
            Smart Scholarship Eligibility & Recommendation System
        </p>

        <?php if ($error !== ""): ?>

            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>

        <form method="POST" action="">

            <div class="form-group">

                <label for="email">
                    Email Address
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email"
                    value="<?php echo htmlspecialchars($_POST["email"] ?? ""); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >

            </div>

            <button type="submit" class="login-btn">
                Login
            </button>

        </form>

        <p class="register-text">
            Don't have an account?
            <a href="register.php">Create Account</a>
        </p>

        <a href="index.php" class="home-link">
            ← Back to Home
        </a>

    </div>

</body>

</html>