<?php

session_start();

require_once "includes/db.php";

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION["student_id"];

// =====================================================
// GET STUDENT PROFILE
// =====================================================

$stmt = $conn->prepare(
    "SELECT * FROM student_profiles WHERE student_id = ?"
);

$stmt->bind_param("i", $student_id);
$stmt->execute();

$result = $stmt->get_result();
$student = $result->fetch_assoc();

$stmt->close();

if (!$student) {
    die("Please complete your student profile before checking eligibility.");
}


// =====================================================
// CONVERT CGPA TO PERCENTAGE
// =====================================================

$academic_value = (float)$student["percentage"];

if ($academic_value <= 10) {
    $student_percentage = $academic_value * 10;
} else {
    $student_percentage = $academic_value;
}


// =====================================================
// NORMALIZE COURSE
// B.E / B.E. / BE → BE
// =====================================================

$student_course = strtoupper(trim($student["course"]));

$student_course = str_replace(
    [".", " "],
    "",
    $student_course
);


// =====================================================
// READ SCHOLARSHIP XML
// =====================================================

$xml_file = "xml/scholarships.xml";

if (!file_exists($xml_file)) {
    die("Scholarship XML file not found.");
}

$xml = simplexml_load_file($xml_file);

if ($xml === false) {
    die("Unable to read scholarship XML file.");
}


// =====================================================
// ARRAYS
// =====================================================

$eligible_scholarships = [];
$not_eligible_scholarships = [];


// =====================================================
// CHECK EACH SCHOLARSHIP
// =====================================================

foreach ($xml->scholarship as $scholarship) {

    $total_rules = 0;
    $passed_rules = 0;
    $reasons = [];


    // =================================================
    // PERCENTAGE
    // =================================================

    $min_percentage =
        (float)$scholarship->minPercentage;

    $total_rules++;

    if ($student_percentage >= $min_percentage) {

        $passed_rules++;

    } else {

        $reasons[] =
            "Your academic percentage is "
            . number_format($student_percentage, 2)
            . "%, but this scholarship requires at least "
            . number_format($min_percentage, 2)
            . "%.";
    }


    // =================================================
    // FAMILY INCOME
    // =================================================

    $max_income =
        (float)$scholarship->maxIncome;

    $student_income =
        (float)$student["income"];

    $total_rules++;

    if ($student_income <= $max_income) {

        $passed_rules++;

    } else {

        $reasons[] =
            "Your family income is ₹"
            . number_format($student_income)
            . ", but the maximum allowed income is ₹"
            . number_format($max_income)
            . ".";
    }


    // =================================================
    // COURSE
    // =================================================

    $required_course =
        strtoupper(
            trim(
                (string)$scholarship->course
            )
        );

    $required_course = str_replace(
        [".", " "],
        "",
        $required_course
    );

    if ($required_course != "ALL") {

        $total_rules++;

        if ($student_course == $required_course) {

            $passed_rules++;

        } else {

            $display_course =
                (string)$scholarship->course;

            $reasons[] =
                "This scholarship is available for "
                . $display_course
                . " students.";
        }
    }


    // =================================================
    // DEPARTMENT
    // =================================================

    $required_department =
        strtoupper(
            trim(
                (string)$scholarship->department
            )
        );

    if ($required_department != "ALL") {

        $total_rules++;

        $student_department =
            strtoupper(
                trim(
                    $student["department"]
                )
            );

        if ($student_department == $required_department) {

            $passed_rules++;

        } else {

            $reasons[] =
                "This scholarship requires the "
                . $required_department
                . " department.";
        }
    }


    // =================================================
    // COMMUNITY
    // =================================================

    $required_community =
        strtoupper(
            trim(
                (string)$scholarship->community
            )
        );

    if ($required_community != "ALL") {

        $total_rules++;

        $student_community =
            strtoupper(
                trim(
                    $student["community"]
                )
            );

        if ($student_community == $required_community) {

            $passed_rules++;

        } else {

            $reasons[] =
                "This scholarship is intended for "
                . $required_community
                . " community students.";
        }
    }


    // =================================================
    // GENDER
    // =================================================

    if (isset($scholarship->gender)) {

        $required_gender =
            strtoupper(
                trim(
                    (string)$scholarship->gender
                )
            );

        $student_gender =
            strtoupper(
                trim(
                    $student["gender"]
                )
            );

        $total_rules++;

        if ($student_gender == $required_gender) {

            $passed_rules++;

        } else {

            $reasons[] =
                "This scholarship is available for "
                . ucfirst(
                    strtolower(
                        $required_gender
                    )
                )
                . " students.";
        }
    }


    // =================================================
    // DISABILITY
    // =================================================

    if (isset($scholarship->disability)) {

        $required_disability =
            strtoupper(
                trim(
                    (string)$scholarship->disability
                )
            );

        $student_disability =
            strtoupper(
                trim(
                    $student["disability"]
                )
            );

        $total_rules++;

        if ($student_disability == $required_disability) {

            $passed_rules++;

        } else {

            $reasons[] =
                "This scholarship requires disability status: "
                . ucfirst(
                    strtolower(
                        $required_disability
                    )
                )
                . ".";
        }
    }


    // =================================================
    // MATCH PERCENTAGE
    // =================================================

    if ($total_rules > 0) {

        $match_percentage =
            round(
                ($passed_rules / $total_rules) * 100
            );

    } else {

        $match_percentage = 0;
    }


    // =================================================
    // STORE RESULT
    // =================================================

    $scholarship_data = [

        "id" =>
            (string)$scholarship->id,

        "name" =>
            (string)$scholarship->name,

        "provider" =>
            (string)$scholarship->provider,

        "benefit" =>
            (float)$scholarship->benefit,

        "description" =>
            (string)$scholarship->description,

        "match" =>
            $match_percentage,

        "reasons" =>
            $reasons
    ];


    if ($match_percentage == 100) {

        $eligible_scholarships[] =
            $scholarship_data;

    } else {

        $not_eligible_scholarships[] =
            $scholarship_data;
    }
}


// =====================================================
// SORT NOT ELIGIBLE BY MATCH SCORE
// =====================================================

usort(
    $not_eligible_scholarships,
    function ($a, $b) {

        return $b["match"] - $a["match"];

    }
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Smart Eligibility Results</title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            font-family: Arial, sans-serif;

            background: #f4f7fb;

            color: #333;
        }


        /* HEADER */

        .header {

            background: #4f46e5;

            color: white;

            padding: 20px 40px;

            display: flex;

            justify-content: space-between;

            align-items: center;
        }


        .header h2 {

            margin: 0;
        }


        .logout {

            color: white;

            text-decoration: none;

            background: #dc2626;

            padding: 10px 18px;

            border-radius: 6px;
        }


        /* CONTAINER */

        .container {

            width: 90%;

            max-width: 1150px;

            margin: 40px auto;
        }


        /* TITLE */

        .title {

            text-align: center;

            margin-bottom: 30px;
        }


        .title h1 {

            margin-bottom: 10px;

            color: #333;
        }


        .title p {

            color: #666;
        }


        /* PROFILE SUMMARY */

        .profile-summary {

            background: white;

            padding: 25px;

            border-radius: 12px;

            margin-bottom: 30px;

            box-shadow:
                0 4px 15px rgba(0,0,0,0.08);
        }


        .profile-summary h2 {

            margin-top: 0;

            color: #4f46e5;
        }


        .profile-grid {

            display: grid;

            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(180px, 1fr)
                );

            gap: 15px;
        }


        .profile-item {

            background: #f8fafc;

            padding: 15px;

            border-radius: 8px;
        }


        .profile-item strong {

            display: block;

            margin-bottom: 5px;

            color: #555;
        }


        /* SECTION */

        .section-title {

            margin-top: 35px;

            margin-bottom: 20px;

            color: #333;
        }


        /* CARD */

        .card {

            background: white;

            padding: 25px;

            border-radius: 12px;

            margin-bottom: 20px;

            box-shadow:
                0 4px 15px rgba(0,0,0,0.08);
        }


        .card-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;
        }


        .card h2 {

            color: #4f46e5;

            margin-top: 0;
        }


        .provider {

            color: #777;
        }


        .description {

            color: #555;

            line-height: 1.6;
        }


        /* MATCH */

        .match {

            min-width: 120px;

            text-align: center;

            padding: 12px;

            border-radius: 8px;

            font-weight: bold;

            font-size: 18px;
        }


        .match-good {

            background: #dcfce7;

            color: #166534;
        }


        .match-bad {

            background: #fee2e2;

            color: #991b1b;
        }


        /* BENEFIT */

        .benefit {

            background: #eef2ff;

            color: #3730a3;

            padding: 12px;

            border-radius: 6px;

            margin-top: 15px;

            font-weight: bold;
        }


        /* REASONS */

        .reasons {

            background: #fff7ed;

            padding: 15px;

            border-radius: 8px;

            margin-top: 15px;
        }


        .reasons h4 {

            margin-top: 0;

            color: #9a3412;
        }


        .reasons ul {

            margin-bottom: 0;
        }


        .reasons li {

            margin-bottom: 8px;

            color: #7c2d12;
        }


        /* SUCCESS */

        .success-box {

            background: #dcfce7;

            color: #166534;

            padding: 20px;

            border-radius: 10px;

            margin-bottom: 25px;

            text-align: center;
        }


        .success-box h2 {

            margin-top: 0;
        }


        /* NO RESULT */

        .no-result {

            background: #fee2e2;

            color: #991b1b;

            padding: 20px;

            border-radius: 10px;

            text-align: center;
        }


        /* BUTTON */

        .btn {

            display: inline-block;

            margin-top: 20px;

            padding: 12px 20px;

            background: #4f46e5;

            color: white;

            text-decoration: none;

            border-radius: 6px;
        }


        @media (max-width: 700px) {

            .card-header {

                flex-direction: column;

                align-items: flex-start;
            }
        }

    </style>

</head>


<body>


<!-- HEADER -->

<div class="header">

    <h2>
        Smart Scholarship System
    </h2>

    <a
        href="logout.php"
        class="logout"
    >
        Logout
    </a>

</div>



<div class="container">


    <!-- TITLE -->

    <div class="title">

        <h1>
            🎓 Smart Eligibility Analysis
        </h1>

        <p>
            Your profile has been compared
            with all available scholarship rules.
        </p>

    </div>



    <!-- PROFILE SUMMARY -->

    <div class="profile-summary">

        <h2>
            👤 Your Profile
        </h2>


        <div class="profile-grid">


            <div class="profile-item">

                <strong>Name</strong>

                <?php

                echo htmlspecialchars(
                    $student["full_name"]
                );

                ?>

            </div>


            <div class="profile-item">

                <strong>Course</strong>

                <?php

                echo htmlspecialchars(
                    $student["course"]
                );

                ?>

            </div>


            <div class="profile-item">

                <strong>Department</strong>

                <?php

                echo htmlspecialchars(
                    $student["department"]
                );

                ?>

            </div>


            <div class="profile-item">

                <strong>Academic Percentage</strong>

                <?php

                echo number_format(
                    $student_percentage,
                    2
                );

                ?>%

                <small>
                    <?php

                    if ($academic_value <= 10) {

                        echo "(Converted from CGPA "
                            . number_format(
                                $academic_value,
                                2
                            )
                            . ")";

                    }

                    ?>
                </small>

            </div>


            <div class="profile-item">

                <strong>Family Income</strong>

                ₹<?php

                echo number_format(
                    $student["income"]
                );

                ?>

            </div>


            <div class="profile-item">

                <strong>Community</strong>

                <?php

                echo htmlspecialchars(
                    $student["community"]
                );

                ?>

            </div>


            <div class="profile-item">

                <strong>Gender</strong>

                <?php

                echo htmlspecialchars(
                    $student["gender"]
                );

                ?>

            </div>


        </div>

    </div>



    <!-- ELIGIBLE SCHOLARSHIPS -->

    <?php

    if (
        count($eligible_scholarships) > 0
    ) {

    ?>

        <h2 class="section-title">

            ✅ Eligible Scholarships

        </h2>


        <div class="success-box">

            <h2>
                🎉 Great! You are eligible.
            </h2>

            <p>

                You matched

                <strong>

                    <?php

                    echo count(
                        $eligible_scholarships
                    );

                    ?>

                </strong>

                scholarship(s).

            </p>

        </div>


        <?php

        foreach (
            $eligible_scholarships
            as $scholarship
        ) {

        ?>

            <div class="card">


                <div class="card-header">


                    <div>

                        <h2>

                            <?php

                            echo htmlspecialchars(
                                $scholarship["name"]
                            );

                            ?>

                        </h2>


                        <div class="provider">

                            Provider:

                            <?php

                            echo htmlspecialchars(
                                $scholarship["provider"]
                            );

                            ?>

                        </div>

                    </div>


                    <div class="match match-good">

                        100% Match

                    </div>


                </div>


                <p class="description">

                    <?php

                    echo htmlspecialchars(
                        $scholarship["description"]
                    );

                    ?>

                </p>


                <div class="benefit">

                    💰 Scholarship Benefit:

                    ₹<?php

                    echo number_format(
                        $scholarship["benefit"]
                    );

                    ?>

                </div>


            </div>

        <?php

        }

        ?>


    <?php

    } else {

    ?>


        <div class="no-result">

            <h2>
                No 100% Eligible Scholarship
            </h2>

            <p>
                Don't worry. We found the
                closest matching scholarships below.
            </p>

        </div>


    <?php

    }

    ?>


    <!-- RECOMMENDED / CLOSE MATCHES -->

    <?php

    if (
        count($not_eligible_scholarships) > 0
    ) {

    ?>

        <h2 class="section-title">

            🔎 Scholarship Match Analysis

        </h2>


        <?php

        foreach (
            $not_eligible_scholarships
            as $scholarship
        ) {

        ?>

            <div class="card">


                <div class="card-header">


                    <div>

                        <h2>

                            <?php

                            echo htmlspecialchars(
                                $scholarship["name"]
                            );

                            ?>

                        </h2>


                        <div class="provider">

                            Provider:

                            <?php

                            echo htmlspecialchars(
                                $scholarship["provider"]
                            );

                            ?>

                        </div>

                    </div>


                    <div class="match match-bad">

                        <?php

                        echo $scholarship["match"];

                        ?>% Match

                    </div>


                </div>


                <p class="description">

                    <?php

                    echo htmlspecialchars(
                        $scholarship["description"]
                    );

                    ?>

                </p>


                <div class="benefit">

                    💰 Benefit:

                    ₹<?php

                    echo number_format(
                        $scholarship["benefit"]
                    );

                    ?>

                </div>


                <?php

                if (
                    count(
                        $scholarship["reasons"]
                    ) > 0
                ) {

                ?>

                    <div class="reasons">

                        <h4>
                            ❌ Why you are not eligible:
                        </h4>


                        <ul>

                            <?php

                            foreach (
                                $scholarship["reasons"]
                                as $reason
                            ) {

                            ?>

                                <li>

                                    <?php

                                    echo htmlspecialchars(
                                        $reason
                                    );

                                    ?>

                                </li>

                            <?php

                            }

                            ?>

                        </ul>

                    </div>

                <?php

                }

                ?>


            </div>

        <?php

        }

        ?>

    <?php

    }

    ?>


    <a
        href="profile.php"
        class="btn"
    >
        ✏️ Update Profile
    </a>


    <a
        href="dashboard.php"
        class="btn"
    >
        ← Dashboard
    </a>


</div>


</body>

</html>