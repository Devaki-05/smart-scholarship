<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

require_once "../includes/db.php";

header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=applications_report.csv");
header("Pragma: no-cache");
header("Expires: 0");

$output = fopen("php://output", "w");


/* UTF-8 BOM for Excel */

fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));


/* CSV HEADER */

fputcsv($output, [
    "Application ID",
    "Student ID",
    "Student Name",
    "Email",
    "Scholarship",
    "Application Date",
    "Status",
    "Remarks",
    "Course",
    "Department",
    "Year",
    "CGPA / Percentage",
    "Annual Income",
    "Community",
    "Disability",
    "College"
]);


/* FETCH APPLICATIONS */

$sql = "
    SELECT
        a.id,
        a.student_id,
        a.scholarship_name,
        a.application_date,
        a.status,
        a.remarks,

        s.name AS student_name,
        s.email,

        sp.full_name,
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

    ORDER BY a.application_date DESC
";

$result = $conn->query($sql);


if ($result) {

    while ($application = $result->fetch_assoc()) {

        $student_name = !empty($application["full_name"])
            ? $application["full_name"]
            : $application["student_name"];


        $academic =
            (float)$application["percentage"];


        if (
            $academic > 0 &&
            $academic <= 10
        ) {

            $academic_value =
                number_format(
                    $academic,
                    2
                ) . " CGPA";

        } elseif ($academic > 10) {

            $academic_value =
                number_format(
                    $academic,
                    2
                ) . "%";

        } else {

            $academic_value =
                "Not filled";

        }


        fputcsv($output, [

            $application["id"],

            $application["student_id"],

            $student_name,

            $application["email"],

            $application["scholarship_name"],

            !empty($application["application_date"])
                ? date(
                    "d-m-Y H:i",
                    strtotime(
                        $application["application_date"]
                    )
                )
                : "N/A",

            $application["status"],

            $application["remarks"] ?? "",

            $application["course"] ?? "Not filled",

            $application["department"]
                ?? "Not filled",

            $application["year"]
                ?? "Not filled",

            $academic_value,

            $application["income"] !== null
                ? "₹" . number_format(
                    (float)$application["income"],
                    2
                )
                : "Not filled",

            $application["community"]
                ?? "Not filled",

            $application["disability"]
                ?? "Not filled",

            $application["college"]
                ?? "Not filled"

        ]);

    }

}


fclose($output);

exit();

?>