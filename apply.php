<?php

session_start();

require_once "includes/db.php";

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION["student_id"];

$message = "";
$message_type = "";


/*
|--------------------------------------------------------------------------
| Handle Application
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $scholarship_id = intval($_POST["scholarship_id"]);
    $scholarship_name = $_POST["scholarship_name"];

    // Check duplicate application
    $check_sql = "
        SELECT id
        FROM applications
        WHERE student_id = ?
        AND scholarship_id = ?
    ";

    $check_stmt = $conn->prepare($check_sql);

    $check_stmt->bind_param(
        "ii",
        $student_id,
        $scholarship_id
    );

    $check_stmt->execute();

    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {

        $message = "You have already applied for this scholarship.";
        $message_type = "error";

    } else {

        $insert_sql = "
            INSERT INTO applications
            (student_id, scholarship_id, scholarship_name)
            VALUES (?, ?, ?)
        ";

        $insert_stmt = $conn->prepare($insert_sql);

        $insert_stmt->bind_param(
            "iis",
            $student_id,
            $scholarship_id,
            $scholarship_name
        );

        if ($insert_stmt->execute()) {

            $message = "Application submitted successfully!";
            $message_type = "success";

        } else {

            $message = "Application failed. Please try again.";
            $message_type = "error";
        }

        $insert_stmt->close();
    }

    $check_stmt->close();
}


/*
|--------------------------------------------------------------------------
| Load Student Profile
|--------------------------------------------------------------------------
*/

$profile_sql = "
    SELECT *
    FROM student_profiles
    WHERE student_id = ?
";

$profile_stmt = $conn->prepare($profile_sql);

$profile_stmt->bind_param(
    "i",
    $student_id
);

$profile_stmt->execute();

$profile_result = $profile_stmt->get_result();

if ($profile_result->num_rows == 0) {

    echo "
        <h2>Profile Required</h2>
        <p>Please complete your profile before applying for scholarships.</p>
        <a href='profile.php'>Complete Profile</a>
    ";

    exit();
}

$student = $profile_result->fetch_assoc();

$profile_stmt->close();


/*
|--------------------------------------------------------------------------
| Convert CGPA to Percentage
|--------------------------------------------------------------------------
|
| If value is <= 10, treat it as CGPA.
|
| Example:
| 8.00 CGPA = 80%
| 8.50 CGPA = 85%
|
*/

$academic_value = (float)$student["percentage"];

if ($academic_value <= 10) {

    $student_percentage = $academic_value * 10;

} else {

    $student_percentage = $academic_value;
}


/*
|--------------------------------------------------------------------------
| Normalize Course
|--------------------------------------------------------------------------
|
| B.E and BE should be treated as the same.
|
*/

$student_course = strtoupper(trim($student["course"]));

$student_course = str_replace(
    [".", " "],
    "",
    $student_course
);


/*
|--------------------------------------------------------------------------
| Normalize Department
|--------------------------------------------------------------------------
*/

$student_department = strtoupper(
    trim($student["department"])
);


/*
|--------------------------------------------------------------------------
| Normalize Community
|--------------------------------------------------------------------------
*/

$student_community = strtoupper(
    trim($student["community"])
);


/*
|--------------------------------------------------------------------------
| Load Scholarships XML
|--------------------------------------------------------------------------
*/

$xml_file = "xml/scholarships.xml";

$eligible_scholarships = [];

if (file_exists($xml_file)) {

    $xml = simplexml_load_file($xml_file);

    if ($xml !== false) {

        foreach ($xml->scholarship as $scholarship) {

            $scholarship_id = (int)$scholarship->id;

            $minPercentage =
                (float)$scholarship->minPercentage;

            $maxIncome =
                (float)$scholarship->maxIncome;

            /*
            |--------------------------------------------------------------------------
            | Scholarship Course
            |--------------------------------------------------------------------------
            */

            $course = strtoupper(
                trim((string)$scholarship->course)
            );

            $course = str_replace(
                [".", " "],
                "",
                $course
            );


            /*
            |--------------------------------------------------------------------------
            | Scholarship Department
            |--------------------------------------------------------------------------
            */

            $department = strtoupper(
                trim((string)$scholarship->department)
            );


            /*
            |--------------------------------------------------------------------------
            | Scholarship Community
            |--------------------------------------------------------------------------
            */

            $community = strtoupper(
                trim((string)$scholarship->community)
            );


            /*
            |--------------------------------------------------------------------------
            | Start as Eligible
            |--------------------------------------------------------------------------
            */

            $eligible = true;


            /*
            |--------------------------------------------------------------------------
            | Percentage Check
            |--------------------------------------------------------------------------
            */

            if ($student_percentage < $minPercentage) {

                $eligible = false;
            }


            /*
            |--------------------------------------------------------------------------
            | Income Check
            |--------------------------------------------------------------------------
            */

            if ((float)$student["income"] > $maxIncome) {

                $eligible = false;
            }


            /*
            |--------------------------------------------------------------------------
            | Course Check
            |--------------------------------------------------------------------------
            */

            if (
                $course != "ALL" &&
                $course != $student_course
            ) {

                $eligible = false;
            }


            /*
            |--------------------------------------------------------------------------
            | Department Check
            |--------------------------------------------------------------------------
            */

            if (
                $department != "ALL" &&
                $department != $student_department
            ) {

                $eligible = false;
            }


            /*
            |--------------------------------------------------------------------------
            | Community Check
            |--------------------------------------------------------------------------
            */

            if (
                $community != "ALL" &&
                $community != $student_community
            ) {

                $eligible = false;
            }


            /*
            |--------------------------------------------------------------------------
            | Gender Check
            |--------------------------------------------------------------------------
            */

            if (isset($scholarship->gender)) {

                $required_gender =
                    trim((string)$scholarship->gender);

                if (
                    $required_gender != "" &&
                    strtolower($required_gender) !=
                    strtolower(trim($student["gender"]))
                ) {

                    $eligible = false;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Disability Check
            |--------------------------------------------------------------------------
            */

            if (isset($scholarship->disability)) {

                $required_disability =
                    trim((string)$scholarship->disability);

                if (
                    $required_disability != "" &&
                    strtolower($required_disability) !=
                    strtolower(trim($student["disability"]))
                ) {

                    $eligible = false;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Add Eligible Scholarship
            |--------------------------------------------------------------------------
            */

            if ($eligible) {

                $eligible_scholarships[] = $scholarship;
            }
        }
    }
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

    <title>Apply for Scholarship</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        header {
            background: #4f46e5;
            color: white;
            padding: 20px 40px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h2 {
            margin: 0;
        }

        header a {
            color: white;
            text-decoration: none;
            background: #dc2626;
            padding: 10px 18px;
            border-radius: 6px;
        }

        .container {
            width: 90%;
            max-width: 1100px;
            margin: 40px auto;
        }

        .welcome {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;

            box-shadow:
                0 3px 10px rgba(0,0,0,0.08);
        }

        .welcome h2 {
            margin-top: 0;
            color: #1e293b;
        }

        .academic-info {
            background: #eef2ff;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

        .scholarship {
            background: white;
            padding: 25px;
            margin-bottom: 20px;
            border-radius: 10px;

            box-shadow:
                0 3px 10px rgba(0,0,0,0.08);
        }

        .scholarship h3 {
            color: #1e3a8a;
            margin-top: 0;
        }

        .scholarship p {
            color: #555;
            line-height: 1.6;
        }

        .details {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(180px, 1fr));

            gap: 15px;
            margin: 20px 0;
        }

        .detail-box {
            background: #f1f5f9;
            padding: 15px;
            border-radius: 8px;
        }

        .detail-box strong {
            display: block;
            color: #334155;
            margin-bottom: 5px;
        }

        .apply-btn {
            background: #16a34a;
            color: white;
            border: none;

            padding: 12px 22px;

            border-radius: 6px;

            cursor: pointer;

            font-size: 16px;
            font-weight: bold;
        }

        .apply-btn:hover {
            background: #15803d;
        }

        .empty {
            background: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px;
        }

        .back-btn {
            display: inline-block;

            margin-top: 20px;

            background: #1e3a8a;

            color: white;

            padding: 10px 18px;

            text-decoration: none;

            border-radius: 6px;
        }

    </style>

</head>

<body>


<header>

    <h2>Smart Scholarship System</h2>

    <a href="logout.php">Logout</a>

</header>


<div class="container">


    <div class="welcome">

        <h2>
            Hello,
            <?php
            echo htmlspecialchars(
                $_SESSION["student_name"]
            );
            ?>
            👋
        </h2>

        <p>
            Based on your profile, the following scholarships
            are currently eligible for you.
        </p>

        <div class="academic-info">

            <strong>
                Academic Performance Used for Eligibility:
            </strong>

            <?php echo number_format(
                $student_percentage,
                2
            ); ?>%

            <br>

            <small>
                Your profile value:
                <?php echo htmlspecialchars(
                    $student["percentage"]
                ); ?>

                <?php
                if ($academic_value <= 10) {
                    echo " CGPA";
                } else {
                    echo " %";
                }
                ?>
            </small>

        </div>

    </div>


    <?php if ($message != ""): ?>

        <div
            class="message
            <?php echo $message_type; ?>"
        >

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php endif; ?>


    <?php if (count($eligible_scholarships) > 0): ?>


        <?php foreach (
            $eligible_scholarships
            as $scholarship
        ): ?>


            <div class="scholarship">


                <h3>

                    <?php
                    echo htmlspecialchars(
                        (string)$scholarship->name
                    );
                    ?>

                </h3>


                <p>

                    <?php
                    echo htmlspecialchars(
                        (string)$scholarship->description
                    );
                    ?>

                </p>


                <div class="details">


                    <div class="detail-box">

                        <strong>Provider</strong>

                        <?php
                        echo htmlspecialchars(
                            (string)$scholarship->provider
                        );
                        ?>

                    </div>


                    <div class="detail-box">

                        <strong>Minimum Percentage</strong>

                        <?php
                        echo htmlspecialchars(
                            (string)$scholarship->minPercentage
                        );
                        ?>%

                    </div>


                    <div class="detail-box">

                        <strong>Maximum Income</strong>

                        ₹<?php
                        echo number_format(
                            (float)$scholarship->maxIncome
                        );
                        ?>

                    </div>


                    <div class="detail-box">

                        <strong>Benefit Amount</strong>

                        ₹<?php
                        echo number_format(
                            (float)$scholarship->benefit
                        );
                        ?>

                    </div>


                </div>


                <form
                    method="POST"
                    action="apply.php"
                >

                    <input
                        type="hidden"
                        name="scholarship_id"
                        value="<?php
                        echo (int)$scholarship->id;
                        ?>"
                    >


                    <input
                        type="hidden"
                        name="scholarship_name"
                        value="<?php
                        echo htmlspecialchars(
                            (string)$scholarship->name
                        );
                        ?>"
                    >


                    <button
                        type="submit"
                        class="apply-btn"
                    >
                        Apply Now
                    </button>

                </form>


            </div>


        <?php endforeach; ?>


    <?php else: ?>


        <div class="empty">

            <h2>No Eligible Scholarships</h2>

            <p>
                Currently, no scholarships match your profile.
            </p>

        </div>


    <?php endif; ?>


    <a
        href="dashboard.php"
        class="back-btn"
    >
        ← Back to Dashboard
    </a>


</div>


</body>

</html>