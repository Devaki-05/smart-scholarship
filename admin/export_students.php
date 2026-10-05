<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

require_once "../includes/db.php";

header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=students_report.csv");
header("Pragma: no-cache");
header("Expires: 0");

$output = fopen("php://output", "w");


/* UTF-8 BOM for Excel */

fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));


/* CSV HEADER */

fputcsv($output, [
    "Student ID",
    "Student Name",
    "Email",
    "Date of Birth",
    "Gender",
    "Course",
    "Department",
    "Year",
    "CGPA / Percentage",
    "Annual Income",
    "Community",
    "Disability",
    "College",
    "Registered Date"
]);


/* FETCH STUDENTS */

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

    ORDER BY s.id DESC
";

$result = $conn->query($sql);


if ($result) {

    while ($student = $result->fetch_assoc()) {

        $student_name = !empty($student["full_name"])
            ? $student["full_name"]
            : $student["name"];


        $academic = (float)$student["percentage"];

        if ($academic > 0 && $academic <= 10) {

            $academic_value =
                number_format($academic, 2) . " CGPA";

        } elseif ($academic > 10) {

            $academic_value =
                number_format($academic, 2) . "%";

        } else {

            $academic_value = "Not filled";

        }


        fputcsv($output, [

            $student["id"],

            $student_name,

            $student["email"],

            $student["dob"] ?? "Not filled",

            $student["gender"] ?? "Not filled",

            $student["course"] ?? "Not filled",

            $student["department"] ?? "Not filled",

            $student["year"] ?? "Not filled",

            $academic_value,

            $student["income"] !== null
                ? "₹" . number_format(
                    (float)$student["income"],
                    2
                )
                : "Not filled",

            $student["community"] ?? "Not filled",

            $student["disability"] ?? "Not filled",

            $student["college"] ?? "Not filled",

            !empty($student["created_at"])
                ? date(
                    "d-m-Y",
                    strtotime($student["created_at"])
                )
                : "N/A"

        ]);

    }

}


fclose($output);

exit();

?>