<?php

session_start();

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit();
}

require_once "includes/db.php";

$student_id = $_SESSION["student_id"];

/* Get student profile */
$sql = "
    SELECT 
        s.name,
        s.email,
        sp.full_name,
        sp.dob,
        sp.gender,
        sp.course,
        sp.department,
        sp.year,
        sp.percentage,
        sp.income,
        sp.community,
        sp.disability,
        sp.college
    FROM students s
    LEFT JOIN student_profiles sp
        ON s.id = sp.student_id
    WHERE s.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Student profile not found.");
}

$student = $result->fetch_assoc();
$stmt->close();


/* Convert CGPA to percentage */
$academic_value = (float)$student["percentage"];

if ($academic_value <= 10) {
    $student_percentage = $academic_value * 10;
    $academic_display = number_format($academic_value, 2) .
        " CGPA (" . number_format($student_percentage, 2) . "%)";
} else {
    $student_percentage = $academic_value;
    $academic_display = number_format($student_percentage, 2) . "%";
}


/* Get student's applications */
$sql = "
    SELECT
        scholarship_name,
        application_date,
        status,
        remarks
    FROM applications
    WHERE student_id = ?
    ORDER BY application_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();

$applications = $stmt->get_result();

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Scholarship Eligibility Report</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f7fb;
    color: #222;
}

.container {
    width: 90%;
    max-width: 1100px;
    margin: 30px auto;
}

.header {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: white;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 25px;
}

.header h1 {
    margin: 0 0 8px;
    font-size: 30px;
}

.header p {
    margin: 0;
    opacity: 0.9;
}

.card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    margin-bottom: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.card h2 {
    margin-top: 0;
    color: #4f46e5;
    border-bottom: 2px solid #eee;
    padding-bottom: 10px;
}

.details {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.detail {
    background: #f8fafc;
    padding: 14px;
    border-radius: 10px;
}

.detail strong {
    display: block;
    color: #555;
    font-size: 13px;
    margin-bottom: 5px;
}

.detail span {
    font-size: 16px;
    font-weight: 600;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

th {
    background: #4f46e5;
    color: white;
    padding: 13px;
    text-align: left;
}

td {
    padding: 13px;
    border-bottom: 1px solid #ddd;
}

tr:hover {
    background: #f8fafc;
}

.status {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: bold;
}

.pending {
    background: #fff3cd;
    color: #856404;
}

.approved {
    background: #d1e7dd;
    color: #0f5132;
}

.rejected {
    background: #f8d7da;
    color: #842029;
}

.buttons {
    text-align: center;
    margin: 25px 0;
}

button,
.button {
    display: inline-block;
    padding: 12px 22px;
    border: none;
    border-radius: 8px;
    background: #4f46e5;
    color: white;
    text-decoration: none;
    font-size: 15px;
    cursor: pointer;
    margin: 5px;
}

.button:hover,
button:hover {
    background: #3730a3;
}

.back {
    background: #64748b;
}

.back:hover {
    background: #475569;
}

.footer {
    text-align: center;
    color: #777;
    font-size: 13px;
    margin: 30px 0;
}

@media print {

    body {
        background: white;
    }

    .buttons {
        display: none;
    }

    .container {
        width: 100%;
        margin: 0;
    }

    .card {
        box-shadow: none;
        border: 1px solid #ddd;
    }

}

@media (max-width: 700px) {

    .details {
        grid-template-columns: 1fr;
    }

    table {
        font-size: 13px;
    }

    th,
    td {
        padding: 8px;
    }

}

</style>

</head>

<body>

<div class="container">

    <div class="header">

        <h1>Scholarship Eligibility Report</h1>

        <p>
            Smart Scholarship Eligibility & Recommendation System
        </p>

    </div>


    <!-- Student Information -->

    <div class="card">

        <h2>Student Information</h2>

        <div class="details">

            <div class="detail">
                <strong>Full Name</strong>
                <span>
                    <?= htmlspecialchars($student["full_name"] ?: $student["name"]) ?>
                </span>
            </div>

            <div class="detail">
                <strong>Email</strong>
                <span>
                    <?= htmlspecialchars($student["email"]) ?>
                </span>
            </div>

            <div class="detail">
                <strong>Date of Birth</strong>
                <span>
                    <?= htmlspecialchars($student["dob"]) ?>
                </span>
            </div>

            <div class="detail">
                <strong>Gender</strong>
                <span>
                    <?= htmlspecialchars($student["gender"]) ?>
                </span>
            </div>

            <div class="detail">
                <strong>Course</strong>
                <span>
                    <?= htmlspecialchars($student["course"]) ?>
                </span>
            </div>

            <div class="detail">
                <strong>Department</strong>
                <span>
                    <?= htmlspecialchars($student["department"]) ?>
                </span>
            </div>

            <div class="detail">
                <strong>Year</strong>
                <span>
                    <?= htmlspecialchars($student["year"]) ?>
                </span>
            </div>

            <div class="detail">
                <strong>Academic Performance</strong>
                <span>
                    <?= htmlspecialchars($academic_display) ?>
                </span>
            </div>

            <div class="detail">
                <strong>Annual Family Income</strong>
                <span>
                    ₹<?= number_format((float)$student["income"], 2) ?>
                </span>
            </div>

            <div class="detail">
                <strong>Community</strong>
                <span>
                    <?= htmlspecialchars($student["community"]) ?>
                </span>
            </div>

            <div class="detail">
                <strong>Disability</strong>
                <span>
                    <?= htmlspecialchars($student["disability"]) ?>
                </span>
            </div>

            <div class="detail">
                <strong>College</strong>
                <span>
                    <?= htmlspecialchars($student["college"]) ?>
                </span>
            </div>

        </div>

    </div>


    <!-- Application Details -->

    <div class="card">

        <h2>Scholarship Applications</h2>

        <?php if ($applications->num_rows > 0): ?>

            <table>

                <thead>

                    <tr>
                        <th>S.No</th>
                        <th>Scholarship</th>
                        <th>Application Date</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>

                </thead>

                <tbody>

                <?php

                $serial = 1;

                while ($application = $applications->fetch_assoc()):

                    $status_class = strtolower(
                        $application["status"]
                    );

                ?>

                    <tr>

                        <td>
                            <?= $serial++ ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $application["scholarship_name"]
                            ) ?>
                        </td>

                        <td>
                            <?= date(
                                "d-m-Y",
                                strtotime($application["application_date"])
                            ) ?>
                        </td>

                        <td>

                            <span class="status <?= $status_class ?>">

                                <?= htmlspecialchars(
                                    $application["status"]
                                ) ?>

                            </span>

                        </td>

                        <td>

                            <?= htmlspecialchars(
                                $application["remarks"] ?: "No remarks"
                            ) ?>

                        </td>

                    </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

        <?php else: ?>

            <p>
                No scholarship applications found.
            </p>

        <?php endif; ?>

    </div>


    <!-- Buttons -->

    <div class="buttons">

        <button onclick="window.print()">
            🖨 Print / Save as PDF
        </button>

        <a href="dashboard.php" class="button back">
            ← Back to Dashboard
        </a>

    </div>


    <div class="footer">

        Smart Scholarship Eligibility & Recommendation System

    </div>

</div>

</body>

</html>