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


// Check whether profile already exists

$stmt = $conn->prepare(
    "SELECT * FROM student_profiles WHERE student_id = ?"
);

$stmt->bind_param("i", $student_id);
$stmt->execute();

$result = $stmt->get_result();

$profile = $result->fetch_assoc();

$stmt->close();


// Save profile

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = trim($_POST["full_name"]);
    $dob = $_POST["dob"];
    $gender = $_POST["gender"];
    $course = trim($_POST["course"]);
    $department = trim($_POST["department"]);
    $year = $_POST["year"];
    $percentage = $_POST["percentage"];
    $income = $_POST["income"];
    $community = $_POST["community"];
    $disability = $_POST["disability"];
    $college = trim($_POST["college"]);


    if (
        empty($full_name) ||
        empty($dob) ||
        empty($gender) ||
        empty($course) ||
        empty($department) ||
        empty($year) ||
        empty($percentage) ||
        empty($income) ||
        empty($community) ||
        empty($disability) ||
        empty($college)
    ) {

        $message = "Please fill all the fields.";
        $message_type = "error";

    } else {

        if ($profile) {

            // Update existing profile

            $stmt = $conn->prepare(
                "UPDATE student_profiles
                 SET full_name = ?,
                     dob = ?,
                     gender = ?,
                     course = ?,
                     department = ?,
                     year = ?,
                     percentage = ?,
                     income = ?,
                     community = ?,
                     disability = ?,
                     college = ?
                 WHERE student_id = ?"
            );

            $stmt->bind_param(
                "sssssidisssi",
                $full_name,
                $dob,
                $gender,
                $course,
                $department,
                $year,
                $percentage,
                $income,
                $community,
                $disability,
                $college,
                $student_id
            );

        } else {

            // Insert new profile

            $stmt = $conn->prepare(
                "INSERT INTO student_profiles
                (
                    student_id,
                    full_name,
                    dob,
                    gender,
                    course,
                    department,
                    year,
                    percentage,
                    income,
                    community,
                    disability,
                    college
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "isssssdiisss",
                $student_id,
                $full_name,
                $dob,
                $gender,
                $course,
                $department,
                $year,
                $percentage,
                $income,
                $community,
                $disability,
                $college
            );
        }


        if ($stmt->execute()) {

            $message = "Profile saved successfully!";

            $message_type = "success";

            // Reload profile

            $stmt->close();

            $stmt = $conn->prepare(
                "SELECT * FROM student_profiles WHERE student_id = ?"
            );

            $stmt->bind_param("i", $student_id);

            $stmt->execute();

            $result = $stmt->get_result();

            $profile = $result->fetch_assoc();

            $stmt->close();

        } else {

            $message = "Error saving profile: " . $stmt->error;

            $message_type = "error";

            $stmt->close();
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

    <title>Student Profile</title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
        }


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


        .container {
            width: 90%;
            max-width: 900px;
            margin: 40px auto;
        }


        .form-box {
            background: white;
            padding: 35px;
            border-radius: 12px;

            box-shadow:
                0 4px 15px rgba(0,0,0,0.08);
        }


        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }


        .message {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }


        .success {
            background: #dcfce7;
            color: #166534;
        }


        .error {
            background: #fee2e2;
            color: #991b1b;
        }


        .form-grid {
            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 20px;
        }


        .form-group {
            display: flex;
            flex-direction: column;
        }


        .full {
            grid-column: 1 / -1;
        }


        label {
            margin-bottom: 7px;
            font-weight: bold;
            color: #444;
        }


        input,
        select {
            padding: 12px;

            border: 1px solid #ccc;

            border-radius: 6px;

            font-size: 15px;
        }


        input:focus,
        select:focus {
            outline: none;

            border-color: #4f46e5;
        }


        .save-btn {
            width: 100%;

            margin-top: 25px;

            padding: 14px;

            background: #4f46e5;

            color: white;

            border: none;

            border-radius: 6px;

            font-size: 16px;

            cursor: pointer;
        }


        .save-btn:hover {
            background: #3730a3;
        }


        .back {
            display: block;

            text-align: center;

            margin-top: 20px;

            color: #4f46e5;

            text-decoration: none;
        }


        @media (max-width: 700px) {

            .form-grid {
                grid-template-columns: 1fr;
            }

            .full {
                grid-column: auto;
            }

        }

    </style>

</head>


<body>


<div class="header">

    <h2>Smart Scholarship System</h2>

    <a href="logout.php"
       class="logout">
        Logout
    </a>

</div>



<div class="container">

    <div class="form-box">

        <h1>Student Profile</h1>


        <?php if (!empty($message)) { ?>

            <div class="message <?php echo $message_type; ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php } ?>


        <form method="POST"
              action="profile.php">


            <div class="form-grid">


                <!-- Full Name -->

                <div class="form-group full">

                    <label>
                        Full Name
                    </label>

                    <input
                        type="text"
                        name="full_name"
                        placeholder="Enter your full name"
                        value="<?php
                            echo htmlspecialchars(
                                $profile["full_name"] ?? $_SESSION["student_name"]
                            );
                        ?>"
                        required
                    >

                </div>



                <!-- Date of Birth -->

                <div class="form-group">

                    <label>
                        Date of Birth
                    </label>

                    <input
                        type="date"
                        name="dob"
                        value="<?php
                            echo htmlspecialchars(
                                $profile["dob"] ?? ""
                            );
                        ?>"
                        required
                    >

                </div>



                <!-- Gender -->

                <div class="form-group">

                    <label>
                        Gender
                    </label>

                    <select name="gender" required>

                        <option value="">
                            Select Gender
                        </option>

                        <option value="Female"
                            <?php
                            if (($profile["gender"] ?? "") == "Female")
                                echo "selected";
                            ?>>
                            Female
                        </option>

                        <option value="Male"
                            <?php
                            if (($profile["gender"] ?? "") == "Male")
                                echo "selected";
                            ?>>
                            Male
                        </option>

                        <option value="Other"
                            <?php
                            if (($profile["gender"] ?? "") == "Other")
                                echo "selected";
                            ?>>
                            Other
                        </option>

                    </select>

                </div>



                <!-- Course -->

                <div class="form-group">

                    <label>
                        Course
                    </label>

                    <select name="course" required>

                        <option value="">
                            Select Course
                        </option>

                        <option value="BE"
                            <?php
                            if (($profile["course"] ?? "") == "BE")
                                echo "selected";
                            ?>>
                            B.E
                        </option>

                        <option value="BTech"
                            <?php
                            if (($profile["course"] ?? "") == "BTech")
                                echo "selected";
                            ?>>
                            B.Tech
                        </option>

                        <option value="BSc"
                            <?php
                            if (($profile["course"] ?? "") == "BSc")
                                echo "selected";
                            ?>>
                            B.Sc
                        </option>

                        <option value="BCA"
                            <?php
                            if (($profile["course"] ?? "") == "BCA")
                                echo "selected";
                            ?>>
                            BCA
                        </option>

                    </select>

                </div>



                <!-- Department -->

                <div class="form-group">

                    <label>
                        Department
                    </label>

                    <input
                        type="text"
                        name="department"
                        placeholder="Example: CSE"
                        value="<?php
                            echo htmlspecialchars(
                                $profile["department"] ?? ""
                            );
                        ?>"
                        required
                    >

                </div>



                <!-- Year -->

                <div class="form-group">

                    <label>
                        Current Year
                    </label>

                    <select name="year" required>

                        <option value="">
                            Select Year
                        </option>

                        <option value="1"
                            <?php
                            if (($profile["year"] ?? "") == "1")
                                echo "selected";
                            ?>>
                            1st Year
                        </option>

                        <option value="2"
                            <?php
                            if (($profile["year"] ?? "") == "2")
                                echo "selected";
                            ?>>
                            2nd Year
                        </option>

                        <option value="3"
                            <?php
                            if (($profile["year"] ?? "") == "3")
                                echo "selected";
                            ?>>
                            3rd Year
                        </option>

                        <option value="4"
                            <?php
                            if (($profile["year"] ?? "") == "4")
                                echo "selected";
                            ?>>
                            4th Year
                        </option>

                    </select>

                </div>



                <!-- Percentage -->

                <div class="form-group">

                    <label>
                        Academic Percentage / CGPA
                    </label>

                    <input
                        type="number"
                        name="percentage"
                        step="0.01"
                        min="0"
                        max="100"
                        placeholder="Example: 85.50"
                        value="<?php
                            echo htmlspecialchars(
                                $profile["percentage"] ?? ""
                            );
                        ?>"
                        required
                    >

                </div>



                <!-- Family Income -->

                <div class="form-group">

                    <label>
                        Annual Family Income (₹)
                    </label>

                    <input
                        type="number"
                        name="income"
                        min="0"
                        placeholder="Example: 250000"
                        value="<?php
                            echo htmlspecialchars(
                                $profile["income"] ?? ""
                            );
                        ?>"
                        required
                    >

                </div>



                <!-- Community -->

                <div class="form-group">

                    <label>
                        Community
                    </label>

                    <select name="community" required>

                        <option value="">
                            Select Community
                        </option>

                        <option value="OC"
                            <?php
                            if (($profile["community"] ?? "") == "OC")
                                echo "selected";
                            ?>>
                            OC
                        </option>

                        <option value="BC"
                            <?php
                            if (($profile["community"] ?? "") == "BC")
                                echo "selected";
                            ?>>
                            BC
                        </option>

                        <option value="MBC"
                            <?php
                            if (($profile["community"] ?? "") == "MBC")
                                echo "selected";
                            ?>>
                            MBC
                        </option>

                        <option value="SC"
                            <?php
                            if (($profile["community"] ?? "") == "SC")
                                echo "selected";
                            ?>>
                            SC
                        </option>

                        <option value="ST"
                            <?php
                            if (($profile["community"] ?? "") == "ST")
                                echo "selected";
                            ?>>
                            ST
                        </option>

                    </select>

                </div>



                <!-- Disability -->

                <div class="form-group">

                    <label>
                        Disability Status
                    </label>

                    <select name="disability" required>

                        <option value="">
                            Select
                        </option>

                        <option value="No"
                            <?php
                            if (($profile["disability"] ?? "") == "No")
                                echo "selected";
                            ?>>
                            No
                        </option>

                        <option value="Yes"
                            <?php
                            if (($profile["disability"] ?? "") == "Yes")
                                echo "selected";
                            ?>>
                            Yes
                        </option>

                    </select>

                </div>



                <!-- College -->

                <div class="form-group full">

                    <label>
                        College Name
                    </label>

                    <input
                        type="text"
                        name="college"
                        placeholder="Enter your college name"
                        value="<?php
                            echo htmlspecialchars(
                                $profile["college"] ?? ""
                            );
                        ?>"
                        required
                    >

                </div>


            </div>


            <button
                type="submit"
                class="save-btn">

                Save Profile

            </button>


        </form>


        <a href="dashboard.php"
           class="back">

            ← Back to Dashboard

        </a>

    </div>

</div>


</body>

</html>