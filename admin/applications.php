<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

require_once "../includes/db.php";


$message = "";
$error = "";


/* =========================================================
   UPDATE APPLICATION
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $application_id = isset($_POST["application_id"])
        ? (int)$_POST["application_id"]
        : 0;

    $status = isset($_POST["status"])
        ? trim($_POST["status"])
        : "";

    $remarks = isset($_POST["remarks"])
        ? trim($_POST["remarks"])
        : "";

    $allowed_statuses = [
        "Pending",
        "Approved",
        "Rejected"
    ];


    if ($application_id <= 0) {

        $error = "Invalid application.";

    } elseif (!in_array(
        $status,
        $allowed_statuses,
        true
    )) {

        $error = "Invalid application status.";

    } else {

        $sql = "
            UPDATE applications
            SET status = ?, remarks = ?
            WHERE id = ?
        ";

        $stmt_update = $conn->prepare($sql);

        if ($stmt_update) {

            $stmt_update->bind_param(
                "ssi",
                $status,
                $remarks,
                $application_id
            );

            if ($stmt_update->execute()) {

                $message =
                    "Application updated successfully.";

            } else {

                $error =
                    "Unable to update application.";

            }

            $stmt_update->close();

        } else {

            $error =
                "Database error.";

        }

    }

}


/* =========================================================
   FILTER VALUES
   ========================================================= */

$search = isset($_GET["search"])
    ? trim($_GET["search"])
    : "";

$status_filter = isset($_GET["status"])
    ? trim($_GET["status"])
    : "";

$scholarship_filter = isset($_GET["scholarship"])
    ? trim($_GET["scholarship"])
    : "";


/* =========================================================
   SCHOLARSHIP LIST
   ========================================================= */

$scholarship_list = [];

$sql = "
    SELECT DISTINCT scholarship_name
    FROM applications
    ORDER BY scholarship_name ASC
";

$list_result = $conn->query($sql);

if ($list_result) {

    while ($row = $list_result->fetch_assoc()) {

        $scholarship_list[] =
            $row["scholarship_name"];

    }

}


/* =========================================================
   APPLICATION QUERY
   ========================================================= */

$sql = "
    SELECT

        a.id,
        a.student_id,
        a.scholarship_id,
        a.scholarship_name,
        a.application_date,
        a.status,
        a.remarks,

        s.name AS student_name,
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

    FROM applications a

    INNER JOIN students s
        ON a.student_id = s.id

    LEFT JOIN student_profiles sp
        ON a.student_id = sp.student_id

    WHERE 1=1
";


$params = [];

$types = "";


/* SEARCH */

if ($search !== "") {

    $sql .= "
        AND (
            s.name LIKE ?
            OR s.email LIKE ?
            OR sp.full_name LIKE ?
            OR a.scholarship_name LIKE ?
        )
    ";

    $search_value =
        "%" . $search . "%";

    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;

    $types .= "ssss";

}


/* STATUS */

if (
    $status_filter !== "" &&
    in_array(
        $status_filter,
        [
            "Pending",
            "Approved",
            "Rejected"
        ],
        true
    )
) {

    $sql .= "
        AND a.status = ?
    ";

    $params[] =
        $status_filter;

    $types .= "s";

}


/* SCHOLARSHIP */

if ($scholarship_filter !== "") {

    $sql .= "
        AND a.scholarship_name = ?
    ";

    $params[] =
        $scholarship_filter;

    $types .= "s";

}


$sql .= "
    ORDER BY a.application_date DESC
";


/* =========================================================
   EXECUTE
   ========================================================= */

$stmt = $conn->prepare($sql);

if (!$stmt) {

    die("Database query error.");

}


if (!empty($params)) {

    $stmt->bind_param(
        $types,
        ...$params
    );

}


$stmt->execute();

$result =
    $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Applications | Admin
</title>


<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}


body {

    font-family: Arial, sans-serif;

    background: #f4f7fb;

    color: #222;

}


/* SIDEBAR */

.sidebar {

    position: fixed;

    left: 0;
    top: 0;

    width: 240px;

    height: 100vh;

    background: linear-gradient(
        180deg,
        #312e81,
        #4f46e5
    );

    color: white;

    padding: 25px 15px;

}


.logo {

    text-align: center;

    font-size: 21px;

    font-weight: bold;

    margin-bottom: 35px;

}


.nav-link {

    display: block;

    color: white;

    text-decoration: none;

    padding: 13px 15px;

    border-radius: 8px;

    margin-bottom: 8px;

}


.nav-link:hover,
.nav-link.active {

    background:
        rgba(255,255,255,0.18);

}


/* MAIN */

.main {

    margin-left: 240px;

    padding: 30px;

}


.topbar {

    background: white;

    padding: 20px 25px;

    border-radius: 13px;

    box-shadow:
        0 4px 15px
        rgba(0,0,0,0.06);

    margin-bottom: 20px;

}


.topbar h1 {

    color: #312e81;

    margin-bottom: 5px;

}


.topbar p {

    color: #777;

}


/* EXPORT */

.export-bar {

    background: white;

    padding: 16px 20px;

    border-radius: 12px;

    margin-bottom: 20px;

    box-shadow:
        0 4px 15px
        rgba(0,0,0,0.06);

}


.export-btn {

    display: inline-block;

    background: #4f46e5;

    color: white;

    text-decoration: none;

    padding: 11px 18px;

    border-radius: 7px;

    font-weight: bold;

}


.export-btn:hover {

    background: #3730a3;

}


/* MESSAGE */

.message {

    padding: 14px 18px;

    border-radius: 8px;

    margin-bottom: 20px;

    font-weight: bold;

}


.success {

    background: #d1e7dd;

    color: #0f5132;

}


.error {

    background: #f8d7da;

    color: #842029;

}


/* FILTER */

.filter-card {

    background: white;

    padding: 22px;

    border-radius: 14px;

    box-shadow:
        0 5px 18px
        rgba(0,0,0,0.07);

    margin-bottom: 25px;

}


.filter-title {

    color: #312e81;

    font-size: 18px;

    margin-bottom: 15px;

}


.filter-form {

    display: grid;

    grid-template-columns:
        2fr 1fr 1.5fr auto auto;

    gap: 12px;

    align-items: end;

}


.filter-group {

    display: flex;

    flex-direction: column;

}


.filter-group label {

    font-size: 12px;

    font-weight: bold;

    color: #555;

    margin-bottom: 6px;

}


.filter-group input,
.filter-group select {

    padding: 11px;

    border:
        1px solid #d1d5db;

    border-radius: 7px;

    font-size: 14px;

    background: white;

}


.search-btn {

    border: none;

    background: #4f46e5;

    color: white;

    padding: 11px 18px;

    border-radius: 7px;

    cursor: pointer;

    font-weight: bold;

}


.clear-btn {

    background: #64748b;

    color: white;

    text-decoration: none;

    padding: 11px 18px;

    border-radius: 7px;

    text-align: center;

}


/* RESULT */

.results-info {

    background: #eef2ff;

    color: #3730a3;

    padding: 12px 15px;

    border-radius: 8px;

    margin-bottom: 20px;

}


/* CARD */

.application-card {

    background: white;

    border-radius: 15px;

    margin-bottom: 25px;

    box-shadow:
        0 5px 18px
        rgba(0,0,0,0.07);

    overflow: hidden;

}


.application-header {

    background: #f8fafc;

    padding: 20px 25px;

    border-bottom:
        1px solid #e5e7eb;

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 15px;

}


.application-header h2 {

    color: #312e81;

    font-size: 20px;

}


.application-id {

    color: #777;

    font-size: 13px;

    margin-top: 5px;

}


.application-body {

    padding: 25px;

}


.section-title {

    color: #4f46e5;

    font-size: 17px;

    margin-bottom: 15px;

    padding-bottom: 8px;

    border-bottom:
        2px solid #eee;

}


.details-grid {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 15px;

    margin-bottom: 25px;

}


.detail-box {

    background: #f8fafc;

    padding: 14px;

    border-radius: 9px;

}


.detail-box strong {

    display: block;

    color: #666;

    font-size: 12px;

    margin-bottom: 5px;

}


.detail-box span {

    font-weight: 600;

    font-size: 14px;

}


/* STATUS */

.status {

    display: inline-block;

    padding: 6px 12px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: bold;

}


.status-pending {

    background: #fff3cd;

    color: #856404;

}


.status-approved {

    background: #d1e7dd;

    color: #0f5132;

}


.status-rejected {

    background: #f8d7da;

    color: #842029;

}


/* UPDATE */

.update-section {

    background: #f8fafc;

    padding: 20px;

    border-radius: 12px;

}


.form-row {

    display: grid;

    grid-template-columns:
        1fr 2fr auto;

    gap: 15px;

    align-items: end;

}


.form-group {

    display: flex;

    flex-direction: column;

}


.form-group label {

    font-size: 13px;

    font-weight: bold;

    margin-bottom: 7px;

    color: #555;

}


.form-group select,
.form-group textarea {

    width: 100%;

    border:
        1px solid #d1d5db;

    border-radius: 7px;

    padding: 11px;

    font-size: 14px;

    font-family: Arial, sans-serif;

}


.form-group textarea {

    min-height: 45px;

    resize: vertical;

}


.update-btn {

    background: #4f46e5;

    color: white;

    border: none;

    padding: 11px 20px;

    border-radius: 7px;

    cursor: pointer;

    font-weight: bold;

}


/* BACK */

.back-btn {

    display: inline-block;

    background: #64748b;

    color: white;

    text-decoration: none;

    padding: 11px 20px;

    border-radius: 7px;

    margin-top: 5px;

}


/* RESPONSIVE */

@media (max-width: 1100px) {

    .filter-form {

        grid-template-columns:
            1fr 1fr;

    }

    .details-grid {

        grid-template-columns:
            repeat(2, 1fr);

    }

}


@media (max-width: 800px) {

    .sidebar {

        position: relative;

        width: 100%;

        height: auto;

    }

    .main {

        margin-left: 0;

        padding: 20px;

    }

    .filter-form {

        grid-template-columns: 1fr;

    }

    .details-grid {

        grid-template-columns: 1fr;

    }

    .form-row {

        grid-template-columns: 1fr;

    }

    .application-header {

        flex-direction: column;

        align-items: flex-start;

    }

}

</style>

</head>


<body>


<!-- SIDEBAR -->

<div class="sidebar">

    <div class="logo">
        🎓 Smart Scholarship
    </div>


    <a
        href="dashboard.php"
        class="nav-link"
    >
        🏠 Dashboard
    </a>


    <a
        href="applications.php"
        class="nav-link active"
    >
        📋 Applications
    </a>


    <a
        href="students.php"
        class="nav-link"
    >
        👨‍🎓 Students
    </a>


    <a
        href="../scholarships.php"
        class="nav-link"
        target="_blank"
    >
        🎓 Scholarships
    </a>


    <a
        href="logout.php"
        class="nav-link"
    >
        🚪 Logout
    </a>

</div>


<!-- MAIN -->

<div class="main">


    <div class="topbar">

        <h1>
            Scholarship Applications
        </h1>

        <p>
            Search, filter and manage student applications.
        </p>

    </div>


    <!-- EXPORT -->

    <div class="export-bar">

        <a
            href="export_applications.php"
            class="export-btn"
        >
            📋 Export Applications CSV
        </a>

    </div>


    <?php if ($message !== ""): ?>

        <div class="message success">

            ✅
            <?= htmlspecialchars($message) ?>

        </div>

    <?php endif; ?>


    <?php if ($error !== ""): ?>

        <div class="message error">

            ❌
            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <!-- FILTER -->

    <div class="filter-card">

        <div class="filter-title">

            🔎 Search & Filter Applications

        </div>


        <form
            method="GET"
            action="applications.php"
            class="filter-form"
        >


            <div class="filter-group">

                <label>
                    Search
                </label>

                <input
                    type="text"
                    name="search"
                    value="<?= htmlspecialchars($search) ?>"
                    placeholder="Student name, email or scholarship..."
                >

            </div>


            <div class="filter-group">

                <label>
                    Status
                </label>

                <select name="status">

                    <option value="">
                        All Status
                    </option>

                    <option
                        value="Pending"
                        <?= $status_filter === "Pending"
                            ? "selected"
                            : "" ?>
                    >
                        Pending
                    </option>

                    <option
                        value="Approved"
                        <?= $status_filter === "Approved"
                            ? "selected"
                            : "" ?>
                    >
                        Approved
                    </option>

                    <option
                        value="Rejected"
                        <?= $status_filter === "Rejected"
                            ? "selected"
                            : "" ?>
                    >
                        Rejected
                    </option>

                </select>

            </div>


            <div class="filter-group">

                <label>
                    Scholarship
                </label>

                <select name="scholarship">

                    <option value="">
                        All Scholarships
                    </option>

                    <?php foreach (
                        $scholarship_list
                        as $scholarship
                    ): ?>

                        <option
                            value="<?= htmlspecialchars(
                                $scholarship
                            ) ?>"
                            <?= $scholarship_filter === $scholarship
                                ? "selected"
                                : "" ?>
                        >

                            <?= htmlspecialchars(
                                $scholarship
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <button
                type="submit"
                class="search-btn"
            >
                🔎 Search
            </button>


            <a
                href="applications.php"
                class="clear-btn"
            >
                Clear
            </a>


        </form>

    </div>


    <!-- RESULT -->

    <div class="results-info">

        📋 Showing

        <strong>
            <?= $result->num_rows ?>
        </strong>

        application(s)

        <?php if ($search !== ""): ?>

            | Search:
            <strong>
                <?= htmlspecialchars($search) ?>
            </strong>

        <?php endif; ?>

        <?php if ($status_filter !== ""): ?>

            | Status:
            <strong>
                <?= htmlspecialchars($status_filter) ?>
            </strong>

        <?php endif; ?>

    </div>


    <!-- APPLICATIONS -->

    <?php if ($result->num_rows > 0): ?>


        <?php while (
            $application = $result->fetch_assoc()
        ): ?>


            <?php

            $status =
                $application["status"];

            $status_class =
                "status-pending";

            if ($status === "Approved") {

                $status_class =
                    "status-approved";

            } elseif ($status === "Rejected") {

                $status_class =
                    "status-rejected";

            }

            ?>


            <div class="application-card">


                <div class="application-header">

                    <div>

                        <h2>

                            <?= htmlspecialchars(
                                $application[
                                    "scholarship_name"
                                ]
                            ) ?>

                        </h2>

                        <div class="application-id">

                            Application ID:
                            #<?= (int)$application["id"] ?>

                        </div>

                    </div>


                    <span
                        class="status <?= $status_class ?>"
                    >

                        <?= htmlspecialchars($status) ?>

                    </span>

                </div>


                <div class="application-body">


                    <!-- STUDENT -->

                    <h3 class="section-title">

                        👤 Student Information

                    </h3>


                    <div class="details-grid">


                        <div class="detail-box">

                            <strong>
                                Student Name
                            </strong>

                            <span>

                                <?= htmlspecialchars(
                                    !empty(
                                        $application[
                                            "full_name"
                                        ]
                                    )
                                    ? $application[
                                        "full_name"
                                    ]
                                    : $application[
                                        "student_name"
                                    ]
                                ) ?>

                            </span>

                        </div>


                        <div class="detail-box">

                            <strong>
                                Email
                            </strong>

                            <span>

                                <?= htmlspecialchars(
                                    $application["email"]
                                ) ?>

                            </span>

                        </div>


                        <div class="detail-box">

                            <strong>
                                Gender
                            </strong>

                            <span>

                                <?= htmlspecialchars(
                                    $application["gender"]
                                    ?? "Not filled"
                                ) ?>

                            </span>

                        </div>


                        <div class="detail-box">

                            <strong>
                                Date of Birth
                            </strong>

                            <span>

                                <?= htmlspecialchars(
                                    $application["dob"]
                                    ?? "Not filled"
                                ) ?>

                            </span>

                        </div>


                        <div class="detail-box">

                            <strong>
                                College
                            </strong>

                            <span>

                                <?= htmlspecialchars(
                                    $application["college"]
                                    ?? "Not filled"
                                ) ?>

                            </span>

                        </div>


                        <div class="detail-box">

                            <strong>
                                Application Date
                            </strong>

                            <span>

                                <?= date(
                                    "d-m-Y H:i",
                                    strtotime(
                                        $application[
                                            "application_date"
                                        ]
                                    )
                                ) ?>

                            </span>

                        </div>


                    </div>


                    <!-- ACADEMIC -->

                    <h3 class="section-title">

                        🎓 Academic & Eligibility Details

                    </h3>


                    <div class="details-grid">


                        <div class="detail-box">

                            <strong>
                                Course
                            </strong>

                            <span>

                                <?= htmlspecialchars(
                                    $application["course"]
                                    ?? "Not filled"
                                ) ?>

                            </span>

                        </div>


                        <div class="detail-box">

                            <strong>
                                Department
                            </strong>

                            <span>

                                <?= htmlspecialchars(
                                    $application[
                                        "department"
                                    ]
                                    ?? "Not filled"
                                ) ?>

                            </span>

                        </div>


                        <div class="detail-box">

                            <strong>
                                Year
                            </strong>

                            <span>

                                <?= htmlspecialchars(
                                    $application["year"]
                                    ?? "Not filled"
                                ) ?>

                            </span>

                        </div>


                        <div class="detail-box">

                            <strong>
                                CGPA / Percentage
                            </strong>

                            <span>

                                <?php

                                $academic =
                                    (float)
                                    $application[
                                        "percentage"
                                    ];

                                if ($academic <= 10) {

                                    echo number_format(
                                        $academic,
                                        2
                                    ) . " CGPA";

                                } else {

                                    echo number_format(
                                        $academic,
                                        2
                                    ) . "%";

                                }

                                ?>

                            </span>

                        </div>


                        <div class="detail-box">

                            <strong>
                                Annual Income
                            </strong>

                            <span>

                                ₹<?= number_format(
                                    (float)
                                    $application["income"],
                                    2
                                ) ?>

                            </span>

                        </div>


                        <div class="detail-box">

                            <strong>
                                Community
                            </strong>

                            <span>

                                <?= htmlspecialchars(
                                    $application[
                                        "community"
                                    ]
                                    ?? "Not filled"
                                ) ?>

                            </span>

                        </div>


                        <div class="detail-box">

                            <strong>
                                Disability
                            </strong>

                            <span>

                                <?= htmlspecialchars(
                                    $application[
                                        "disability"
                                    ]
                                    ?? "Not filled"
                                ) ?>

                            </span>

                        </div>


                    </div>


                    <!-- UPDATE -->

                    <h3 class="section-title">

                        ⚙️ Update Application

                    </h3>


                    <div class="update-section">


                        <form
                            method="POST"
                            action="applications.php"
                        >


                            <input
                                type="hidden"
                                name="application_id"
                                value="<?= (int)$application["id"] ?>"
                            >


                            <div class="form-row">


                                <div class="form-group">

                                    <label>
                                        Application Status
                                    </label>

                                    <select
                                        name="status"
                                        required
                                    >

                                        <option
                                            value="Pending"
                                            <?= $status === "Pending"
                                                ? "selected"
                                                : "" ?>
                                        >
                                            Pending
                                        </option>

                                        <option
                                            value="Approved"
                                            <?= $status === "Approved"
                                                ? "selected"
                                                : "" ?>
                                        >
                                            Approved
                                        </option>

                                        <option
                                            value="Rejected"
                                            <?= $status === "Rejected"
                                                ? "selected"
                                                : "" ?>
                                        >
                                            Rejected
                                        </option>

                                    </select>

                                </div>


                                <div class="form-group">

                                    <label>
                                        Admin Remarks
                                    </label>

                                    <textarea
                                        name="remarks"
                                        placeholder="Enter remarks for the student..."
                                    ><?= htmlspecialchars(
                                        $application["remarks"]
                                        ?? ""
                                    ) ?></textarea>

                                </div>


                                <div class="form-group">

                                    <button
                                        type="submit"
                                        class="update-btn"
                                    >
                                        Update
                                    </button>

                                </div>


                            </div>


                        </form>


                    </div>


                </div>

            </div>


        <?php endwhile; ?>


    <?php else: ?>


        <div
            style="
                background:white;
                padding:50px;
                text-align:center;
                border-radius:15px;
            "
        >

            <div style="font-size:50px;">
                📭
            </div>

            <h2>
                No Applications Found
            </h2>

            <p style="color:#777;margin-top:8px;">
                Try changing your search or filter.
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

<?php

$stmt->close();

?>