
<?php
require_once "db.php";

$course = isset($_GET["course"])
    ? trim($_GET["course"])
    : "";

$rows = [];

try {
    $sql = "
        SELECT
            s.student_id,
            s.full_name,
            s.email,
            s.course,
            s.year_of_study,
            e.event_name,
            r.registration_date,
            r.status
        FROM students AS s
        LEFT JOIN registrations AS r
            ON s.student_id = r.student_id
        LEFT JOIN events AS e
            ON r.event_id = e.event_id
        WHERE (? = '' OR s.course = ?)
        ORDER BY s.student_id ASC
    ";

    // Prepare the SQL statement
    $stmt = $conn->prepare($sql);

    // Bind user input safely
    $stmt->bind_param("ss", $course, $course);

    // Execute the statement
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    // Fetch all records
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $stmt->close();

    $connected = true;

} catch (mysqli_sql_exception $e) {
    error_log("Query error: " . $e->getMessage());

    $connected = false;
    $errorMessage = "Unable to retrieve database records.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Practical 8 - MySQLi Connection</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            padding: 25px;
            color: #1f2937;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 25px;
            background: white;
            border-radius: 10px;
        }

        h1 {
            color: #1e3a8a;
        }

        .success {
            background: #dcfce7;
            color: #166534;
            padding: 14px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 14px;
            border-radius: 6px;
        }

        form {
            margin: 20px 0;
        }

        select, button {
            padding: 10px;
            margin-right: 8px;
        }

        button {
            background: #2563eb;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }

        th, td {
            border: 1px solid #cbd5e1;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #1e3a8a;
            color: white;
        }

        tr:nth-child(even) {
            background: #f8fafc;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Practical 8: MySQLi Database Connection</h1>

    <?php if ($connected): ?>

        <div class="success">
            Database connection successful!
            Records fetched using a MySQLi prepared statement.
        </div>

    <?php else: ?>

        <div class="error">
            <?= htmlspecialchars($errorMessage, ENT_QUOTES, "UTF-8") ?>
        </div>

    <?php endif; ?>


    <p><strong>Database:</strong> practical8_db</p>

    <form method="get">

        <label for="course">Filter by Course:</label>

        <select name="course" id="course">

            <option value="">All Courses</option>

            <?php
            $courses = ["BCA", "BBA", "B.Tech", "B.Sc", "B.Com"];

            foreach ($courses as $option):
            ?>

                <option
                    value="<?= htmlspecialchars($option, ENT_QUOTES, "UTF-8") ?>"
                    <?= $course === $option ? "selected" : "" ?>
                >
                    <?= htmlspecialchars($option, ENT_QUOTES, "UTF-8") ?>
                </option>

            <?php endforeach; ?>

        </select>

        <button type="submit">Show Records</button>

    </form>


    <p>
        <strong>Total rows returned:</strong>
        <?= count($rows) ?>
    </p>


    <div class="table-wrap">

        <table>

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>Year</th>
                    <th>Event</th>
                    <th>Registration Date</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>

                <?php if (empty($rows)): ?>

                    <tr>
                        <td colspan="8">No records found.</td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($rows as $row): ?>

                        <tr>
                            <td>
                                <?= htmlspecialchars((string)$row["student_id"], ENT_QUOTES, "UTF-8") ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row["full_name"], ENT_QUOTES, "UTF-8") ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row["email"], ENT_QUOTES, "UTF-8") ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row["course"], ENT_QUOTES, "UTF-8") ?>
                            </td>

                            <td>
                                <?= htmlspecialchars((string)$row["year_of_study"], ENT_QUOTES, "UTF-8") ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row["event_name"] ?? "Not registered", ENT_QUOTES, "UTF-8") ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row["registration_date"] ?? "-", ENT_QUOTES, "UTF-8") ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row["status"] ?? "-", ENT_QUOTES, "UTF-8") ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>

<?php
$conn->close();
?>
