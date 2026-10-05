<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit();
}

require_once "includes/db.php";


/* =========================================================
   LOAD XML FILE
   ========================================================= */

$xml_file = __DIR__ . "/xml/scholarships.xml";

if (!file_exists($xml_file)) {
    die("ERROR: scholarships.xml file not found.");
}

$xml = simplexml_load_file($xml_file);

if ($xml === false) {
    die("ERROR: Unable to read scholarships.xml.");
}


/* =========================================================
   GET FILTER VALUES
   ========================================================= */

$search = "";

if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
}

$course_filter = "";

if (isset($_GET["course"])) {
    $course_filter = trim($_GET["course"]);
}

$department_filter = "";

if (isset($_GET["department"])) {
    $department_filter = trim($_GET["department"]);
}

$community_filter = "";

if (isset($_GET["community"])) {
    $community_filter = trim($_GET["community"]);
}


/* =========================================================
   FILTER SCHOLARSHIPS
   ========================================================= */

$filtered_scholarships = [];


foreach ($xml->scholarship as $scholarship) {

    $name = trim((string)$scholarship->name);
    $provider = trim((string)$scholarship->provider);
    $course = trim((string)$scholarship->course);
    $department = trim((string)$scholarship->department);
    $community = trim((string)$scholarship->community);


    /* Search condition */

    $search_match = true;

    if ($search !== "") {

        $search_text =
            strtolower($name) . " " .
            strtolower($provider) . " " .
            strtolower($course) . " " .
            strtolower($department) . " " .
            strtolower($community);

        if (strpos($search_text, strtolower($search)) === false) {
            $search_match = false;
        }
    }


    /* Course condition */

    $course_match = true;

    if ($course_filter !== "") {

        if (
            strtoupper($course) !== "ALL" &&
            strtoupper($course) !== strtoupper($course_filter)
        ) {
            $course_match = false;
        }
    }


    /* Department condition */

    $department_match = true;

    if ($department_filter !== "") {

        if (
            strtoupper($department) !== "ALL" &&
            strtoupper($department) !== strtoupper($department_filter)
        ) {
            $department_match = false;
        }
    }


    /* Community condition */

    $community_match = true;

    if ($community_filter !== "") {

        if (
            strtoupper($community) !== "ALL" &&
            strtoupper($community) !== strtoupper($community_filter)
        ) {
            $community_match = false;
        }
    }


    /* Final condition */

    if (
        $search_match &&
        $course_match &&
        $department_match &&
        $community_match
    ) {

        $filtered_scholarships[] = $scholarship;
    }
}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Scholarships | Smart Scholarship</title>


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
           NAVBAR
        ========================= */

        .navbar {

            background: white;

            padding: 18px 7%;

            display: flex;

            justify-content: space-between;

            align-items: center;

            box-shadow: 0 2px 10px rgba(0,0,0,0.08);

        }


        .logo {

            font-size: 22px;

            font-weight: bold;

            color: #4f46e5;

        }


        .nav-links {

            display: flex;

            gap: 8px;

            flex-wrap: wrap;

        }


        .nav-links a {

            text-decoration: none;

            color: #374151;

            padding: 9px 13px;

            border-radius: 7px;

            font-size: 14px;

        }


        .nav-links a:hover {

            background: #eef2ff;

            color: #4f46e5;

        }


        .logout {

            background: #ef4444 !important;

            color: white !important;

        }


        /* =========================
           HEADER
        ========================= */

        .header {

            text-align: center;

            padding: 45px 20px 30px;

        }


        .header h1 {

            font-size: 36px;

            color: #111827;

            margin-bottom: 10px;

        }


        .header p {

            color: #6b7280;

            font-size: 16px;

        }


        /* =========================
           FILTER BOX
        ========================= */

        .filter-box {

            width: 86%;

            margin: 0 auto 30px;

            background: white;

            padding: 25px;

            border-radius: 15px;

            box-shadow: 0 5px 20px rgba(0,0,0,0.06);

        }


        .filter-form {

            display: grid;

            grid-template-columns: 2fr 1fr 1fr 1fr auto auto;

            gap: 12px;

        }


        .filter-form input,

        .filter-form select {

            width: 100%;

            padding: 12px;

            border: 1px solid #d1d5db;

            border-radius: 8px;

            font-size: 14px;

            outline: none;

        }


        .filter-form input:focus,

        .filter-form select:focus {

            border-color: #4f46e5;

        }


        .search-button {

            border: none;

            background: #4f46e5;

            color: white;

            padding: 12px 20px;

            border-radius: 8px;

            cursor: pointer;

            font-weight: bold;

        }


        .search-button:hover {

            background: #4338ca;

        }


        .clear-button {

            text-decoration: none;

            background: #e5e7eb;

            color: #374151;

            padding: 12px 18px;

            border-radius: 8px;

            text-align: center;

            font-size: 14px;

        }


        /* =========================
           RESULT
        ========================= */

        .result {

            width: 86%;

            margin: 0 auto 20px;

            color: #6b7280;

            font-size: 14px;

        }


        /* =========================
           SCHOLARSHIP GRID
        ========================= */

        .scholarship-grid {

            width: 86%;

            margin: auto;

            display: grid;

            grid-template-columns: repeat(3, 1fr);

            gap: 25px;

            padding-bottom: 60px;

        }


        /* =========================
           CARD
        ========================= */

        .card {

            background: white;

            border-radius: 16px;

            padding: 25px;

            border: 1px solid #e5e7eb;

            box-shadow: 0 5px 18px rgba(0,0,0,0.05);

            transition: 0.3s;

        }


        .card:hover {

            transform: translateY(-5px);

            box-shadow: 0 12px 28px rgba(0,0,0,0.10);

        }


        .icon {

            width: 52px;

            height: 52px;

            background: #eef2ff;

            border-radius: 12px;

            display: flex;

            justify-content: center;

            align-items: center;

            font-size: 26px;

            margin-bottom: 15px;

        }


        .card h2 {

            font-size: 20px;

            color: #111827;

            margin-bottom: 8px;

        }


        .provider {

            color: #6b7280;

            font-size: 14px;

            margin-bottom: 18px;

        }


        .details {

            margin-bottom: 15px;

        }


        .detail {

            display: flex;

            justify-content: space-between;

            padding: 9px 0;

            border-bottom: 1px solid #f1f5f9;

            font-size: 14px;

            gap: 10px;

        }


        .detail span:first-child {

            color: #6b7280;

        }


        .detail span:last-child {

            font-weight: bold;

            text-align: right;

        }


        .benefit {

            background: #ecfdf5;

            color: #047857;

            padding: 11px;

            border-radius: 8px;

            text-align: center;

            font-weight: bold;

            margin-bottom: 15px;

        }


        .description {

            color: #6b7280;

            font-size: 14px;

            line-height: 1.6;

            margin-bottom: 20px;

        }


        .apply-button {

            display: block;

            width: 100%;

            background: #4f46e5;

            color: white;

            text-decoration: none;

            text-align: center;

            padding: 12px;

            border-radius: 8px;

            font-weight: bold;

        }


        .apply-button:hover {

            background: #4338ca;

        }


        /* =========================
           NO RESULT
        ========================= */

        .no-result {

            grid-column: 1 / -1;

            background: white;

            padding: 60px 20px;

            text-align: center;

            border-radius: 15px;

        }


        .no-result h2 {

            margin-bottom: 10px;

        }


        .no-result p {

            color: #6b7280;

        }


        .no-result-icon {

            font-size: 45px;

            margin-bottom: 15px;

        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1100px) {

            .scholarship-grid {

                grid-template-columns: repeat(2, 1fr);

            }


            .filter-form {

                grid-template-columns: 1fr 1fr;

            }

        }


        @media (max-width: 700px) {

            .navbar {

                flex-direction: column;

                gap: 15px;

            }


            .filter-form {

                grid-template-columns: 1fr;

            }


            .scholarship-grid {

                grid-template-columns: 1fr;

            }


            .filter-box,

            .result,

            .scholarship-grid {

                width: 92%;

            }


            .header h1 {

                font-size: 30px;

            }

        }

    </style>

</head>


<body>


<!-- =========================
     NAVIGATION
========================= -->

<nav class="navbar">

    <div class="logo">

        🎓 Smart Scholarship

    </div>


    <div class="nav-links">

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="profile.php">
            Profile
        </a>

        <a href="eligibility.php">
            Eligibility
        </a>

        <a href="my_applications.php">
            My Applications
        </a>

        <a href="logout.php" class="logout">
            Logout
        </a>

    </div>

</nav>


<!-- =========================
     HEADER
========================= -->

<section class="header">

    <h1>
        Available Scholarships
    </h1>

    <p>
        Search and explore scholarships available for students.
    </p>

</section>


<!-- =========================
     FILTER
========================= -->

<div class="filter-box">

    <form method="GET" class="filter-form">


        <!-- Search -->

        <input
            type="text"
            name="search"
            placeholder="Search scholarship or provider..."
            value="<?php echo htmlspecialchars($search); ?>"
        >


        <!-- Course -->

        <select name="course">

            <option value="">
                All Courses
            </option>

            <option
                value="BE"
                <?php
                if ($course_filter === "BE") {
                    echo "selected";
                }
                ?>
            >
                B.E
            </option>

        </select>


        <!-- Department -->

        <select name="department">

            <option value="">
                All Departments
            </option>

            <option
                value="CSE"
                <?php
                if ($department_filter === "CSE") {
                    echo "selected";
                }
                ?>
            >
                CSE
            </option>

            <option
                value="IT"
                <?php
                if ($department_filter === "IT") {
                    echo "selected";
                }
                ?>
            >
                IT
            </option>

            <option
                value="ECE"
                <?php
                if ($department_filter === "ECE") {
                    echo "selected";
                }
                ?>
            >
                ECE
            </option>

            <option
                value="EEE"
                <?php
                if ($department_filter === "EEE") {
                    echo "selected";
                }
                ?>
            >
                EEE
            </option>

            <option
                value="MECH"
                <?php
                if ($department_filter === "MECH") {
                    echo "selected";
                }
                ?>
            >
                Mechanical
            </option>

        </select>


        <!-- Community -->

        <select name="community">

            <option value="">
                All Communities
            </option>

            <option
                value="MBC"
                <?php
                if ($community_filter === "MBC") {
                    echo "selected";
                }
                ?>
            >
                MBC
            </option>

            <option
                value="BC"
                <?php
                if ($community_filter === "BC") {
                    echo "selected";
                }
                ?>
            >
                BC
            </option>

            <option
                value="SC"
                <?php
                if ($community_filter === "SC") {
                    echo "selected";
                }
                ?>
            >
                SC
            </option>

            <option
                value="ST"
                <?php
                if ($community_filter === "ST") {
                    echo "selected";
                }
                ?>
            >
                ST
            </option>

            <option
                value="OC"
                <?php
                if ($community_filter === "OC") {
                    echo "selected";
                }
                ?>
            >
                OC
            </option>

        </select>


        <!-- Search button -->

        <button
            type="submit"
            class="search-button"
        >
            Search
        </button>


        <!-- Clear button -->

        <a
            href="scholarships.php"
            class="clear-button"
        >
            Clear
        </a>


    </form>

</div>


<!-- =========================
     RESULT COUNT
========================= -->

<div class="result">

    Showing

    <strong>
        <?php echo count($filtered_scholarships); ?>
    </strong>

    scholarship(s)

</div>


<!-- =========================
     SCHOLARSHIP CARDS
========================= -->

<div class="scholarship-grid">


<?php

if (count($filtered_scholarships) > 0) {

    foreach ($filtered_scholarships as $scholarship) {

?>


        <div class="card">


            <div class="icon">
                🎓
            </div>


            <h2>

                <?php

                echo htmlspecialchars(
                    (string)$scholarship->name
                );

                ?>

            </h2>


            <div class="provider">

                Provided by:

                <strong>

                    <?php

                    echo htmlspecialchars(
                        (string)$scholarship->provider
                    );

                    ?>

                </strong>

            </div>


            <div class="details">


                <div class="detail">

                    <span>
                        Minimum Percentage
                    </span>

                    <span>

                        <?php

                        echo htmlspecialchars(
                            (string)$scholarship->minPercentage
                        );

                        ?>%

                    </span>

                </div>


                <div class="detail">

                    <span>
                        Maximum Income
                    </span>

                    <span>

                        ₹<?php

                        echo number_format(
                            (float)$scholarship->maxIncome
                        );

                        ?>

                    </span>

                </div>


                <div class="detail">

                    <span>
                        Course
                    </span>

                    <span>

                        <?php

                        echo htmlspecialchars(
                            (string)$scholarship->course
                        );

                        ?>

                    </span>

                </div>


                <div class="detail">

                    <span>
                        Department
                    </span>

                    <span>

                        <?php

                        echo htmlspecialchars(
                            (string)$scholarship->department
                        );

                        ?>

                    </span>

                </div>


                <div class="detail">

                    <span>
                        Community
                    </span>

                    <span>

                        <?php

                        echo htmlspecialchars(
                            (string)$scholarship->community
                        );

                        ?>

                    </span>

                </div>


            </div>


            <div class="benefit">

                Scholarship Benefit:

                ₹<?php

                echo number_format(
                    (float)$scholarship->benefit
                );

                ?>

            </div>


            <div class="description">

                <?php

                echo htmlspecialchars(
                    (string)$scholarship->description
                );

                ?>

            </div>


            <a
                href="apply.php?id=<?php echo (int)$scholarship->id; ?>"
                class="apply-button"
            >

                Apply Now →

            </a>


        </div>


<?php

    }

} else {

?>


        <div class="no-result">

            <div class="no-result-icon">
                🔎
            </div>

            <h2>
                No Scholarships Found
            </h2>

            <p>
                Try changing your search or filter options.
            </p>

        </div>


<?php

}

?>


</div>


</body>

</html>