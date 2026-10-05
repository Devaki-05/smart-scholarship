<?php

session_start();

require_once "includes/db.php";

// ---------------------------------------
// CHECK STUDENT LOGIN
// ---------------------------------------

if (!isset($_SESSION["student_id"])) {

    header("Location: login.php");
    exit();

}

$student_id = (int) $_SESSION["student_id"];

$student_name = $_SESSION["student_name"] ?? "Student";

$student_email = $_SESSION["student_email"] ?? "";


// ---------------------------------------
// GET STUDENT PROFILE
// ---------------------------------------

$profile = null;

$sql = "
    SELECT *
    FROM student_profiles
    WHERE student_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if ($stmt) {

    $stmt->bind_param("i", $student_id);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $profile = $result->fetch_assoc();

    }

    $stmt->close();
}


// ---------------------------------------
// GET APPLICATION COUNT
// ---------------------------------------

$application_count = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM applications
    WHERE student_id = ?
";

$stmt = $conn->prepare($sql);

if ($stmt) {

    $stmt->bind_param("i", $student_id);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result) {

        $row = $result->fetch_assoc();

        $application_count = (int) $row["total"];

    }

    $stmt->close();
}


// ---------------------------------------
// GET APPROVED APPLICATION COUNT
// ---------------------------------------

$approved_count = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM applications
    WHERE student_id = ?
    AND status = 'Approved'
";

$stmt = $conn->prepare($sql);

if ($stmt) {

    $stmt->bind_param("i", $student_id);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result) {

        $row = $result->fetch_assoc();

        $approved_count = (int) $row["total"];

    }

    $stmt->close();
}


// ---------------------------------------
// GET PENDING APPLICATION COUNT
// ---------------------------------------

$pending_count = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM applications
    WHERE student_id = ?
    AND status = 'Pending'
";

$stmt = $conn->prepare($sql);

if ($stmt) {

    $stmt->bind_param("i", $student_id);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result) {

        $row = $result->fetch_assoc();

        $pending_count = (int) $row["total"];

    }

    $stmt->close();
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Student Dashboard - Smart Scholarship</title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {

    font-family: Arial, sans-serif;

    background: #f5f7fb;

    color: #1e293b;
}


/* --------------------------------
   SIDEBAR
-------------------------------- */

.sidebar {

    position: fixed;

    left: 0;

    top: 0;

    width: 250px;

    height: 100vh;

    background: #111827;

    color: white;

    padding: 25px 18px;
}

.logo {

    font-size: 21px;

    font-weight: bold;

    padding: 10px 12px 25px;

    border-bottom: 1px solid #374151;

    margin-bottom: 20px;
}

.logo span {

    color: #818cf8;
}

.nav a {

    display: block;

    text-decoration: none;

    color: #d1d5db;

    padding: 13px 14px;

    border-radius: 8px;

    margin-bottom: 7px;

    font-size: 14px;

    transition: 0.2s;
}

.nav a:hover {

    background: #1f2937;

    color: white;
}

.nav a.active {

    background: #4f46e5;

    color: white;
}


/* --------------------------------
   MAIN
-------------------------------- */

.main {

    margin-left: 250px;

    min-height: 100vh;
}


/* --------------------------------
   TOP BAR
-------------------------------- */

.topbar {

    background: white;

    padding: 18px 30px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    border-bottom: 1px solid #e5e7eb;
}

.topbar h2 {

    font-size: 22px;
}

.user-info {

    color: #64748b;

    font-size: 14px;
}


/* --------------------------------
   CONTENT
-------------------------------- */

.content {

    padding: 30px;
}

.welcome {

    background:
        linear-gradient(
            135deg,
            #4f46e5,
            #6366f1
        );

    color: white;

    padding: 28px;

    border-radius: 16px;

    margin-bottom: 25px;
}

.welcome h1 {

    font-size: 27px;

    margin-bottom: 8px;
}

.welcome p {

    opacity: 0.9;

    font-size: 14px;
}


/* --------------------------------
   STAT CARDS
-------------------------------- */

.stats {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 20px;

    margin-bottom: 25px;
}

.stat-card {

    background: white;

    padding: 23px;

    border-radius: 14px;

    box-shadow:
        0 4px 15px rgba(0,0,0,0.05);
}

.stat-card h3 {

    color: #64748b;

    font-size: 14px;

    margin-bottom: 10px;
}

.stat-card .number {

    font-size: 30px;

    font-weight: bold;

    color: #111827;
}


/* --------------------------------
   PROFILE CARD
-------------------------------- */

.profile-card {

    background: white;

    padding: 25px;

    border-radius: 14px;

    margin-bottom: 25px;

    box-shadow:
        0 4px 15px rgba(0,0,0,0.05);
}

.profile-card h2 {

    margin-bottom: 20px;

    font-size: 20px;
}

.profile-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 15px;
}

.profile-item {

    padding: 14px;

    background: #f8fafc;

    border-radius: 9px;
}

.profile-item strong {

    display: block;

    color: #64748b;

    font-size: 12px;

    margin-bottom: 5px;
}


/* --------------------------------
   QUICK ACTIONS
-------------------------------- */

.actions {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 18px;
}

.action {

    background: white;

    padding: 22px;

    border-radius: 14px;

    text-decoration: none;

    color: #1e293b;

    box-shadow:
        0 4px 15px rgba(0,0,0,0.05);

    transition: 0.2s;
}

.action:hover {

    transform: translateY(-3px);

    box-shadow:
        0 8px 20px rgba(0,0,0,0.08);
}

.action h3 {

    margin-bottom: 8px;

    color: #4f46e5;
}

.action p {

    font-size: 13px;

    color: #64748b;
}


/* --------------------------------
   RESPONSIVE
-------------------------------- */

@media (max-width: 900px) {

    .sidebar {

        width: 210px;
    }

    .main {

        margin-left: 210px;
    }

    .stats,
    .actions {

        grid-template-columns:
            repeat(2, 1fr);
    }
}


@media (max-width: 650px) {

    .sidebar {

        position: relative;

        width: 100%;

        height: auto;
    }

    .main {

        margin-left: 0;
    }

    .topbar {

        padding: 15px;
    }

    .content {

        padding: 18px;
    }

    .stats,
    .actions,
    .profile-grid {

        grid-template-columns: 1fr;
    }
}

</style>

</head>


<body>


<!-- SIDEBAR -->

<div class="sidebar">

    <div class="logo">

        🎓 Smart <span>Scholarship</span>

    </div>


    <div class="nav">

        <a href="dashboard.php"
           class="active">

            🏠 Dashboard

        </a>


        <a href="profile.php">

            👤 My Profile

        </a>


        <a href="eligibility.php">

            ✅ Check Eligibility

        </a>


        <a href="scholarships.php">

            🎓 Scholarships

        </a>


        <a href="my_applications.php">

            📄 My Applications

        </a>


        <a href="report.php">

            📊 My Report

        </a>


        <a href="logout.php">

            🚪 Logout

        </a>

    </div>

</div>


<!-- MAIN -->

<div class="main">


    <!-- TOP BAR -->

    <div class="topbar">

        <h2>Student Dashboard</h2>

        <div class="user-info">

            Welcome,
            <strong>
                <?php echo htmlspecialchars($student_name); ?>
            </strong>

        </div>

    </div>


    <!-- CONTENT -->

    <div class="content">


        <!-- WELCOME -->

        <div class="welcome">

            <h1>
                Welcome,
                <?php echo htmlspecialchars($student_name); ?>! 👋
            </h1>

            <p>
                Manage your scholarship profile,
                check eligibility and track your applications.
            </p>

        </div>


        <!-- STATISTICS -->

        <div class="stats">

            <div class="stat-card">

                <h3>
                    Total Applications
                </h3>

                <div class="number">

                    <?php echo $application_count; ?>

                </div>

            </div>


            <div class="stat-card">

                <h3>
                    Pending Applications
                </h3>

                <div class="number">

                    <?php echo $pending_count; ?>

                </div>

            </div>


            <div class="stat-card">

                <h3>
                    Approved Applications
                </h3>

                <div class="number">

                    <?php echo $approved_count; ?>

                </div>

            </div>

        </div>


        <!-- PROFILE -->

        <div class="profile-card">

            <h2>
                Profile Overview
            </h2>


            <?php if ($profile): ?>

                <div class="profile-grid">


                    <div class="profile-item">

                        <strong>
                            Full Name
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $profile["full_name"]
                        );
                        ?>

                    </div>


                    <div class="profile-item">

                        <strong>
                            Course
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $profile["course"]
                        );
                        ?>

                    </div>


                    <div class="profile-item">

                        <strong>
                            Department
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $profile["department"]
                        );
                        ?>

                    </div>


                    <div class="profile-item">

                        <strong>
                            Year
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $profile["year"]
                        );
                        ?>

                    </div>


                    <div class="profile-item">

                        <strong>
                            CGPA / Percentage
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $profile["percentage"]
                        );
                        ?>

                    </div>


                    <div class="profile-item">

                        <strong>
                            Community
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $profile["community"]
                        );
                        ?>

                    </div>


                </div>

            <?php else: ?>

                <p style="color:#64748b; margin-bottom:15px;">

                    Your profile has not been completed yet.

                </p>


                <a
                    href="profile.php"
                    style="
                        display:inline-block;
                        padding:10px 16px;
                        background:#4f46e5;
                        color:white;
                        text-decoration:none;
                        border-radius:8px;
                    "
                >

                    Complete Profile

                </a>

            <?php endif; ?>

        </div>


        <!-- QUICK ACTIONS -->

        <div class="actions">


            <a
                href="profile.php"
                class="action"
            >

                <h3>
                    👤 My Profile
                </h3>

                <p>
                    View or update your student information.
                </p>

            </a>


            <a
                href="eligibility.php"
                class="action"
            >

                <h3>
                    ✅ Check Eligibility
                </h3>

                <p>
                    Check which scholarships you are eligible for.
                </p>

            </a>


            <a
                href="scholarships.php"
                class="action"
            >

                <h3>
                    🎓 Scholarships
                </h3>

                <p>
                    Browse available scholarship opportunities.
                </p>

            </a>


            <a
                href="my_applications.php"
                class="action"
            >

                <h3>
                    📄 My Applications
                </h3>

                <p>
                    Track your scholarship applications.
                </p>

            </a>


            <a
                href="report.php"
                class="action"
            >

                <h3>
                    📊 My Report
                </h3>

                <p>
                    View your scholarship eligibility report.
                </p>

            </a>


            <a
                href="logout.php"
                class="action"
            >

                <h3>
                    🚪 Logout
                </h3>

                <p>
                    Securely logout from your account.
                </p>

            </a>


        </div>

    </div>

</div>

</body>

</html>