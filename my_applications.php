<?php

session_start();

require_once "includes/db.php";

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION["student_id"];

$sql = "
    SELECT
        id,
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

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Applications</title>

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
            background: #dc2626;
            color: white;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 6px;
        }

        .container {
            width: 90%;
            max-width: 1100px;
            margin: 40px auto;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;

            box-shadow:
                0 3px 10px rgba(0, 0, 0, 0.08);
        }

        .card h2 {
            margin-top: 0;
            color: #1e293b;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background: #4f46e5;
            color: white;
            padding: 14px;
            text-align: left;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #e5e7eb;
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
            background: #fef3c7;
            color: #92400e;
        }

        .approved {
            background: #dcfce7;
            color: #166534;
        }

        .rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .empty {
            text-align: center;
            padding: 40px;
            color: #64748b;
        }

        .apply-btn {
            display: inline-block;
            margin-top: 20px;
            background: #16a34a;
            color: white;
            text-decoration: none;
            padding: 11px 18px;
            border-radius: 6px;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            background: #1e3a8a;
            color: white;
            text-decoration: none;
            padding: 10px 18px;
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

    <div class="card">

        <h2>My Applications</h2>

        <p>
            Track all your scholarship applications and their
            current status.
        </p>


        <?php if ($result->num_rows > 0): ?>

            <div class="table-container">

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

                        $serial_no = 1;

                        while ($row = $result->fetch_assoc()):

                            $status = strtolower(
                                trim($row["status"])
                            );

                            if ($status == "approved") {

                                $status_class = "approved";

                            } elseif ($status == "rejected") {

                                $status_class = "rejected";

                            } else {

                                $status_class = "pending";
                            }

                        ?>

                            <tr>

                                <td>
                                    <?php echo $serial_no; ?>
                                </td>

                                <td>
                                    <strong>
                                        <?php
                                        echo htmlspecialchars(
                                            $row["scholarship_name"]
                                        );
                                        ?>
                                    </strong>
                                </td>

                                <td>

                                    <?php

                                    echo date(
                                        "d-m-Y h:i A",
                                        strtotime(
                                            $row["application_date"]
                                        )
                                    );

                                    ?>

                                </td>

                                <td>

                                    <span
                                        class="status
                                        <?php
                                        echo $status_class;
                                        ?>"
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            ucfirst($row["status"])
                                        );
                                        ?>

                                    </span>

                                </td>

                                <td>

                                    <?php

                                    if (
                                        !empty(
                                            $row["remarks"]
                                        )
                                    ) {

                                        echo htmlspecialchars(
                                            $row["remarks"]
                                        );

                                    } else {

                                        echo "No remarks yet.";
                                    }

                                    ?>

                                </td>

                            </tr>

                        <?php

                            $serial_no++;

                        endwhile;

                        ?>

                    </tbody>

                </table>

            </div>


        <?php else: ?>

            <div class="empty">

                <h3>No Applications Yet</h3>

                <p>
                    You have not applied for any scholarship yet.
                </p>

                <a
                    href="apply.php"
                    class="apply-btn"
                >
                    Browse Scholarships
                </a>

            </div>

        <?php endif; ?>


        <a
            href="dashboard.php"
            class="back-btn"
        >
            ← Back to Dashboard
        </a>

    </div>

</div>

</body>

</html>

<?php

$stmt->close();

?>