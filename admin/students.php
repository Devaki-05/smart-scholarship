<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

require_once "../includes/db.php";


/* =========================================================
   SEARCH / FILTER VALUES
   ========================================================= */

$search = isset($_GET["search"])
    ? trim($_GET["search"])
    : "";

$course_filter = isset($_GET["course"])
    ? trim($_GET["course"])
    : "";

$department_filter = isset($_GET["department"])
    ? trim($_GET["department"])
    : "";

$community_filter = isset($_GET["community"])
    ? trim($_GET["community"])
    : "";


/* =========================================================
   COURSE LIST
   ========================================================= */

$courses = [];

$sql = "
    SELECT DISTINCT course
    FROM student_profiles
    WHERE course IS NOT NULL
      AND course != ''
    ORDER BY course
";

$result_courses = $conn->query($sql);

if ($result_courses) {

    while ($row = $result_courses->fetch_assoc()) {
        $courses[] = $row["course"];
    }

}


/* =========================================================
   DEPARTMENT LIST
   ========================================================= */

$departments = [];

$sql = "
    SELECT DISTINCT department
    FROM student_profiles
    WHERE department IS NOT NULL
      AND department != ''
    ORDER BY department
";

$result_departments = $conn->query($sql);

if ($result_departments) {

    while ($row = $result_departments->fetch_assoc()) {
        $departments[] = $row["department"];
    }

}


/* =========================================================
   COMMUNITY LIST
   ========================================================= */

$communities = [];

$sql = "
    SELECT DISTINCT community
    FROM student_profiles
    WHERE community IS NOT NULL
      AND community != ''
    ORDER BY community
";

$result_communities = $conn->query($sql);

if ($result_communities) {

    while ($row = $result_communities->fetch_assoc()) {
        $communities[] = $row["community"];
    }

}


/* =========================================================
   STUDENT QUERY
   ========================================================= */

$sql = "
    SELECT
        s.id,
        s.name,
        s.email,
        s.created_at,

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

    WHERE 1=1
";


$params = [];
$types = "";


/* =========================================================
   SEARCH
   ========================================================= */

if ($search !== "") {

    $sql .= "
        AND (
            s.name LIKE ?
            OR s.email LIKE ?
            OR sp.full_name LIKE ?
            OR sp.college LIKE ?
        )
    ";

    $search_value = "%" . $search . "%";

    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;

    $types .= "ssss";
}


/* =========================================================
   COURSE FILTER
   ========================================================= */

if ($course_filter !== "") {

    $sql .= "
        AND sp.course = ?
    ";

    $params[] = $course_filter;

    $types .= "s";
}


/* =========================================================
   DEPARTMENT FILTER
   ========================================================= */

if ($department_filter !== "") {

    $sql .= "
        AND sp.department = ?
    ";

    $params[] = $department_filter;

    $types .= "s";
}


/* =========================================================
   COMMUNITY FILTER
   ========================================================= */

if ($community_filter !== "") {

    $sql .= "
        AND sp.community = ?
    ";

    $params[] = $community_filter;

    $types .= "s";
}


$sql .= "
    ORDER BY s.id DESC
";


/* =========================================================
   EXECUTE QUERY
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

$result = $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Students | Admin</title>


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

    background: rgba(255,255,255,0.18);

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
        0 4px 15px rgba(0,0,0,0.06);

    margin-bottom: 20px;

}


.topbar h1 {

    color: #312e81;

    margin-bottom: 5px;

}


.topbar p {

    color: #777;

}


/* EXPORT BAR */

.export-bar {

    background: white;

    padding: 16px 20px;

    border-radius: 12px;

    margin-bottom: 20px;

    box-shadow:
        0 4px 15px rgba(0,0,0,0.06);

}


.export-btn {

    display: inline-block;

    background: #4f46e5;

    color: white;

    text-decoration: none;

    padding: 11px 18px;

    border-radius: 7px;

    font-weight: bold;

    margin-right: 10px;

}


.export-btn:hover {

    background: #3730a3;

}


/* FILTER */

.filter-card {

    background: white;

    padding: 22px;

    border-radius: 14px;

    box-shadow:
        0 5px 18px rgba(0,0,0,0.07);

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
        2fr 1fr 1.5fr 1fr auto auto;

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

    border: 1px solid #d1d5db;

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


/* TABLE */

.table-card {

    background: white;

    border-radius: 15px;

    box-shadow:
        0 5px 18px rgba(0,0,0,0.07);

    overflow: hidden;

}


.table-wrapper {

    width: 100%;

    overflow-x: auto;

}


table {

    width: 100%;

    min-width: 1500px;

    border-collapse: collapse;

}


thead {

    background: #312e81;

    color: white;

}


th {

    padding: 15px 12px;

    text-align: left;

    font-size: 13px;

    white-space: nowrap;

}


td {

    padding: 14px 12px;

    border-bottom: 1px solid #e5e7eb;

    font-size: 13px;

    white-space: nowrap;

}


tbody tr:hover {

    background: #f8fafc;

}


/* BADGES */

.badge {

    display: inline-block;

    padding: 5px 10px;

    border-radius: 15px;

    font-size: 11px;

    font-weight: bold;

}


.badge-community {

    background: #ede9fe;

    color: #5b21b6;

}


.badge-disability {

    background: #fee2e2;

    color: #991b1b;

}


.badge-normal {

    background: #dcfce7;

    color: #166534;

}


/* EMPTY */

.no-students {

    background: white;

    padding: 55px;

    text-align: center;

    border-radius: 15px;

}


.no-students .icon {

    font-size: 50px;

    margin-bottom: 15px;

}


.no-students h2 {

    margin-bottom: 8px;

}


.no-students p {

    color: #777;

}


/* BACK */

.back-btn {

    display: inline-block;

    background: #64748b;

    color: white;

    text-decoration: none;

    padding: 11px 20px;

    border-radius: 7px;

    margin-top: 20px;

}


/* RESPONSIVE */

@media (max-width: 1200px) {

    .filter-form {

        grid-template-columns:
            1fr 1fr 1fr;

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
        class="nav-link"
    >
        📋 Applications
    </a>

    <a
        href="students.php"
        class="nav-link active"
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
            Registered Students
        </h1>

        <p>
            Search, filter and manage registered students.
        </p>

    </div>


    <!-- EXPORT -->

    <div class="export-bar">

        <a
            href="export_students.php"
            class="export-btn"
        >
            📄 Export Students CSV
        </a>

    </div>


    <!-- FILTER -->

    <div class="filter-card">

        <div class="filter-title">

            🔎 Search & Filter Students

        </div>


        <form
            method="GET"
            action="students.php"
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
                    placeholder="Name, email or college..."
                >

            </div>


            <div class="filter-group">

                <label>
                    Course
                </label>

                <select name="course">

                    <option value="">
                        All Courses
                    </option>

                    <?php foreach ($courses as $course): ?>

                        <option
                            value="<?= htmlspecialchars($course) ?>"
                            <?= $course_filter === $course
                                ? "selected"
                                : "" ?>
                        >
                            <?= htmlspecialchars($course) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="filter-group">

                <label>
                    Department
                </label>

                <select name="department">

                    <option value="">
                        All Departments
                    </option>

                    <?php foreach (
                        $departments
                        as $department
                    ): ?>

                        <option
                            value="<?= htmlspecialchars($department) ?>"
                            <?= $department_filter === $department
                                ? "selected"
                                : "" ?>
                        >
                            <?= htmlspecialchars($department) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="filter-group">

                <label>
                    Community
                </label>

                <select name="community">

                    <option value="">
                        All Communities
                    </option>

                    <?php foreach (
                        $communities
                        as $community
                    ): ?>

                        <option
                            value="<?= htmlspecialchars($community) ?>"
                            <?= $community_filter === $community
                                ? "selected"
                                : "" ?>
                        >
                            <?= htmlspecialchars($community) ?>
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
                href="students.php"
                class="clear-btn"
            >
                Clear
            </a>


        </form>

    </div>


    <!-- RESULT -->

    <div class="results-info">

        👨‍🎓 Showing

        <strong>
            <?= $result->num_rows ?>
        </strong>

        student(s)

        <?php if ($search !== ""): ?>

            | Search:
            <strong>
                <?= htmlspecialchars($search) ?>
            </strong>

        <?php endif; ?>

        <?php if ($course_filter !== ""): ?>

            | Course:
            <strong>
                <?= htmlspecialchars($course_filter) ?>
            </strong>

        <?php endif; ?>

        <?php if ($department_filter !== ""): ?>

            | Department:
            <strong>
                <?= htmlspecialchars($department_filter) ?>
            </strong>

        <?php endif; ?>

        <?php if ($community_filter !== ""): ?>

            | Community:
            <strong>
                <?= htmlspecialchars($community_filter) ?>
            </strong>

        <?php endif; ?>

    </div>


    <!-- TABLE -->

    <?php if ($result->num_rows > 0): ?>

        <div class="table-card">

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>ID</th>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>DOB</th>
                            <th>Gender</th>
                            <th>Course</th>
                            <th>Department</th>
                            <th>Year</th>
                            <th>CGPA / Percentage</th>
                            <th>Annual Income</th>
                            <th>Community</th>
                            <th>Disability</th>
                            <th>College</th>
                            <th>Registered</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php while (
                        $student = $result->fetch_assoc()
                    ): ?>

                        <tr>

                            <td>
                                <strong>
                                    #<?= (int)$student["id"] ?>
                                </strong>
                            </td>


                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        !empty($student["full_name"])
                                        ? $student["full_name"]
                                        : $student["name"]
                                    ) ?>

                                </strong>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $student["email"]
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $student["dob"]
                                    ?? "Not filled"
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $student["gender"]
                                    ?? "Not filled"
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $student["course"]
                                    ?? "Not filled"
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $student["department"]
                                    ?? "Not filled"
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $student["year"]
                                    ?? "Not filled"
                                ) ?>

                            </td>


                            <td>

                                <?php

                                $academic =
                                    (float)$student["percentage"];

                                if (
                                    $academic > 0 &&
                                    $academic <= 10
                                ) {

                                    echo number_format(
                                        $academic,
                                        2
                                    ) . " CGPA";

                                } elseif ($academic > 10) {

                                    echo number_format(
                                        $academic,
                                        2
                                    ) . "%";

                                } else {

                                    echo "Not filled";

                                }

                                ?>

                            </td>


                            <td>

                                ₹<?= number_format(
                                    (float)$student["income"],
                                    2
                                ) ?>

                            </td>


                            <td>

                                <?php if (
                                    !empty($student["community"])
                                ): ?>

                                    <span
                                        class="badge badge-community"
                                    >

                                        <?= htmlspecialchars(
                                            $student["community"]
                                        ) ?>

                                    </span>

                                <?php else: ?>

                                    Not filled

                                <?php endif; ?>

                            </td>


                            <td>

                                <?php

                                $disability =
                                    $student["disability"]
                                    ?? "";

                                ?>

                                <?php if (
                                    strtolower($disability)
                                    === "yes"
                                ): ?>

                                    <span
                                        class="badge badge-disability"
                                    >
                                        Yes
                                    </span>

                                <?php elseif (
                                    strtolower($disability)
                                    === "no"
                                ): ?>

                                    <span
                                        class="badge badge-normal"
                                    >
                                        No
                                    </span>

                                <?php else: ?>

                                    Not filled

                                <?php endif; ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $student["college"]
                                    ?? "Not filled"
                                ) ?>

                            </td>


                            <td>

                                <?= !empty(
                                    $student["created_at"]
                                )
                                    ? date(
                                        "d-m-Y",
                                        strtotime(
                                            $student["created_at"]
                                        )
                                    )
                                    : "N/A"
                                ?>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        </div>


    <?php else: ?>

        <div class="no-students">

            <div class="icon">
                📭
            </div>

            <h2>
                No Students Found
            </h2>

            <p>
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