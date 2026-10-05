<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

require_once "../includes/db.php";


/* =========================
   GET COUNTS
========================= */

$total_students = 0;
$total_applications = 0;
$pending_applications = 0;
$approved_applications = 0;
$rejected_applications = 0;


/* Total students */

$result = $conn->query(
    "SELECT COUNT(*) AS total FROM students"
);

if ($result) {

    $row = $result->fetch_assoc();

    $total_students = (int)$row["total"];
}


/* Total applications */

$result = $conn->query(
    "SELECT COUNT(*) AS total FROM applications"
);

if ($result) {

    $row = $result->fetch_assoc();

    $total_applications = (int)$row["total"];
}


/* Pending */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM applications
     WHERE status = 'Pending'"
);

if ($result) {

    $row = $result->fetch_assoc();

    $pending_applications = (int)$row["total"];
}


/* Approved */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM applications
     WHERE status = 'Approved'"
);

if ($result) {

    $row = $result->fetch_assoc();

    $approved_applications = (int)$row["total"];
}


/* Rejected */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM applications
     WHERE status = 'Rejected'"
);

if ($result) {

    $row = $result->fetch_assoc();

    $rejected_applications = (int)$row["total"];
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | Smart Scholarship</title>


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {

            font-family: Arial, Helvetica, sans-serif;

            background: #f5f7fb;

            color: #1f2937;

        }


        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {

            position: fixed;

            left: 0;

            top: 0;

            width: 240px;

            height: 100vh;

            background: #111827;

            padding: 25px 15px;

        }


        .sidebar-logo {

            color: white;

            font-size: 21px;

            font-weight: bold;

            text-align: center;

            padding-bottom: 30px;

            border-bottom: 1px solid #374151;

        }


        .sidebar-menu {

            margin-top: 30px;

        }


        .sidebar-menu a {

            display: block;

            color: #d1d5db;

            text-decoration: none;

            padding: 13px 15px;

            border-radius: 8px;

            margin-bottom: 8px;

            font-size: 14px;

        }


        .sidebar-menu a:hover {

            background: #374151;

            color: white;

        }


        .sidebar-menu .active {

            background: #4f46e5;

            color: white;

        }


        .logout {

            background: #dc2626 !important;

            color: white !important;

            margin-top: 25px;

        }


        /* =========================
           MAIN
        ========================= */

        .main {

            margin-left: 240px;

            padding: 30px 40px;

        }


        /* =========================
           TOP BAR
        ========================= */

        .topbar {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 35px;

        }


        .topbar h1 {

            font-size: 30px;

            color: #111827;

        }


        .welcome {

            color: #6b7280;

            font-size: 14px;

        }


        .admin-name {

            color: #4f46e5;

            font-weight: bold;

        }


        /* =========================
           STAT CARDS
        ========================= */

        .stats {

            display: grid;

            grid-template-columns: repeat(5, 1fr);

            gap: 18px;

            margin-bottom: 35px;

        }


        .stat-card {

            background: white;

            padding: 22px;

            border-radius: 14px;

            border: 1px solid #e5e7eb;

            box-shadow: 0 5px 15px rgba(0,0,0,0.05);

        }


        .stat-icon {

            width: 45px;

            height: 45px;

            border-radius: 10px;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 22px;

            margin-bottom: 15px;

            background: #eef2ff;

        }


        .stat-card h3 {

            font-size: 28px;

            margin-bottom: 5px;

            color: #111827;

        }


        .stat-card p {

            color: #6b7280;

            font-size: 13px;

        }


        /* =========================
           QUICK ACTIONS
        ========================= */

        .section-title {

            margin-bottom: 18px;

        }


        .section-title h2 {

            font-size: 22px;

            color: #111827;

        }


        .action-grid {

            display: grid;

            grid-template-columns: repeat(4, 1fr);

            gap: 20px;

            margin-bottom: 35px;

        }


        .action-card {

            background: white;

            padding: 25px;

            border-radius: 14px;

            border: 1px solid #e5e7eb;

            text-decoration: none;

            color: #111827;

            transition: 0.3s;

        }


        .action-card:hover {

            transform: translateY(-4px);

            box-shadow: 0 10px 25px rgba(0,0,0,0.08);

            border-color: #c7d2fe;

        }


        .action-icon {

            font-size: 30px;

            margin-bottom: 15px;

        }


        .action-card h3 {

            font-size: 17px;

            margin-bottom: 7px;

        }


        .action-card p {

            color: #6b7280;

            font-size: 13px;

            line-height: 1.5;

        }


        /* =========================
           APPLICATION SUMMARY
        ========================= */

        .summary {

            background: white;

            padding: 25px;

            border-radius: 14px;

            border: 1px solid #e5e7eb;

        }


        .summary-row {

            display: flex;

            justify-content: space-between;

            align-items: center;

            padding: 15px 0;

            border-bottom: 1px solid #f1f5f9;

        }


        .summary-row:last-child {

            border-bottom: none;

        }


        .summary-label {

            color: #374151;

            font-weight: bold;

        }


        .summary-value {

            font-weight: bold;

        }


        .pending {

            color: #d97706;

        }


        .approved {

            color: #16a34a;

        }


        .rejected {

            color: #dc2626;

        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1200px) {

            .stats {

                grid-template-columns: repeat(3, 1fr);

            }


            .action-grid {

                grid-template-columns: repeat(2, 1fr);

            }

        }


        @media (max-width: 800px) {

            .sidebar {

                position: relative;

                width: 100%;

                height: auto;

            }


            .sidebar-menu {

                display: flex;

                flex-wrap: wrap;

                gap: 8px;

            }


            .sidebar-menu a {

                margin: 0;

            }


            .main {

                margin-left: 0;

                padding: 25px;

            }


            .stats {

                grid-template-columns: 1fr 1fr;

            }

        }


        @media (max-width: 500px) {

            .stats,

            .action-grid {

                grid-template-columns: 1fr;

            }


            .topbar {

                flex-direction: column;

                align-items: flex-start;

                gap: 10px;

            }

        }

    </style>

</head>


<body>


<!-- =========================
     SIDEBAR
========================= -->

<aside class="sidebar">

    <div class="sidebar-logo">

        🎓 Smart Scholarship

    </div>


    <div class="sidebar-menu">

        <a href="dashboard.php" class="active">
            📊 Dashboard
        </a>


        <a href="applications.php">
            📋 Applications
        </a>


        <a href="students.php">
            👨‍🎓 Students
        </a>


        <a href="../scholarships.php">
            🎓 Scholarships
        </a>


        <a href="logout.php" class="logout">
            🚪 Logout
        </a>

    </div>

</aside>


<!-- =========================
     MAIN CONTENT
========================= -->

<main class="main">


    <!-- TOP BAR -->

    <div class="topbar">

        <div>

            <h1>
                Admin Dashboard
            </h1>

            <p class="welcome">

                Welcome back,

                <span class="admin-name">

                    <?php

                    echo htmlspecialchars(
                        $_SESSION["admin_username"]
                    );

                    ?>

                </span>

            </p>

        </div>

    </div>


    <!-- =========================
         STATISTICS
    ========================= -->

    <div class="stats">


        <div class="stat-card">

            <div class="stat-icon">
                👨‍🎓
            </div>

            <h3>
                <?php echo $total_students; ?>
            </h3>

            <p>
                Total Students
            </p>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                📋
            </div>

            <h3>
                <?php echo $total_applications; ?>
            </h3>

            <p>
                Total Applications
            </p>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                ⏳
            </div>

            <h3 class="pending">
                <?php echo $pending_applications; ?>
            </h3>

            <p>
                Pending Applications
            </p>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                ✅
            </div>

            <h3 class="approved">
                <?php echo $approved_applications; ?>
            </h3>

            <p>
                Approved Applications
            </p>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                ❌
            </div>

            <h3 class="rejected">
                <?php echo $rejected_applications; ?>
            </h3>

            <p>
                Rejected Applications
            </p>

        </div>


    </div>


    <!-- =========================
         QUICK ACTIONS
    ========================= -->

    <div class="section-title">

        <h2>
            Quick Actions
        </h2>

    </div>


    <div class="action-grid">


        <a
            href="applications.php"
            class="action-card"
        >

            <div class="action-icon">
                📋
            </div>

            <h3>
                Manage Applications
            </h3>

            <p>
                Review student scholarship applications
                and update their status.
            </p>

        </a>


        <a
            href="students.php"
            class="action-card"
        >

            <div class="action-icon">
                👨‍🎓
            </div>

            <h3>
                View Students
            </h3>

            <p>
                View registered students and
                their profile information.
            </p>

        </a>


        <a
            href="../scholarships.php"
            class="action-card"
        >

            <div class="action-icon">
                🎓
            </div>

            <h3>
                View Scholarships
            </h3>

            <p>
                Browse available scholarship
                information and criteria.
            </p>

        </a>


        <a
            href="logout.php"
            class="action-card"
        >

            <div class="action-icon">
                🚪
            </div>

            <h3>
                Logout
            </h3>

            <p>
                Securely end the current
                administrator session.
            </p>

        </a>


    </div>


    <!-- =========================
         APPLICATION SUMMARY
    ========================= -->

    <div class="section-title">

        <h2>
            Application Summary
        </h2>

    </div>


    <div class="summary">


        <div class="summary-row">

            <span class="summary-label">
                Total Applications
            </span>

            <span class="summary-value">
                <?php echo $total_applications; ?>
            </span>

        </div>


        <div class="summary-row">

            <span class="summary-label">
                Pending
            </span>

            <span class="summary-value pending">
                <?php echo $pending_applications; ?>
            </span>

        </div>


        <div class="summary-row">

            <span class="summary-label">
                Approved
            </span>

            <span class="summary-value approved">
                <?php echo $approved_applications; ?>
            </span>

        </div>


        <div class="summary-row">

            <span class="summary-label">
                Rejected
            </span>

            <span class="summary-value rejected">
                <?php echo $rejected_applications; ?>
            </span>

        </div>


    </div>


</main>


</body>

</html>