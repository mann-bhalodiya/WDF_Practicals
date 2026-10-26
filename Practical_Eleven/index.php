
<?php
require_once __DIR__ . "/db.php";

function h($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, "UTF-8");
}

function go($m, $type = "success")
{
    header(
        "Location: index.php?message=" .
        urlencode($m) .
        "&type=" . $type
    );
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    try {
        if ($action === "save") {
            $id = (int)($_POST["id"] ?? 0);
            $name = trim($_POST["student_name"] ?? "");
            $email = trim($_POST["email"] ?? "");
            $course = trim($_POST["course"] ?? "");
            $year = (int)($_POST["year_of_study"] ?? 0);
            $mobile = trim($_POST["mobile"] ?? "");

            // Validate student details
            if ($name === "" || mb_strlen($name) > 100) {
                throw new RuntimeException(
                    "Enter a student name (maximum 100 characters)."
                );
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException("Enter a valid email.");
            }

            if ($course === "" || mb_strlen($course) > 80) {
                throw new RuntimeException("Enter a course.");
            }

            if ($year < 1 || $year > 6) {
                throw new RuntimeException(
                    "Year must be between 1 and 6."
                );
            }

            if (
                $mobile !== "" &&
                !preg_match('/^[0-9+\-\s()]{7,20}$/', $mobile)
            ) {
                throw new RuntimeException(
                    "Enter a valid mobile number."
                );
            }

            // Update an existing student
            if ($id) {
                $s = $conn->prepare(
                    "UPDATE students
                     SET student_name = ?,
                         email = ?,
                         course = ?,
                         year_of_study = ?,
                         mobile = ?
                     WHERE id = ? AND status = 'active'"
                );

                $s->bind_param(
                    "sssisi",
                    $name,
                    $email,
                    $course,
                    $year,
                    $mobile,
                    $id
                );

                $s->execute();
                $s->close();

                go("Student updated successfully.");
            } else {
                // Insert a new student
                $s = $conn->prepare(
                    "INSERT INTO students
                        (student_name, email, course,
                         year_of_study, mobile)
                     VALUES (?, ?, ?, ?, ?)"
                );

                $s->bind_param(
                    "sssis",
                    $name,
                    $email,
                    $course,
                    $year,
                    $mobile
                );

                $s->execute();
                $s->close();

                go("Student added successfully.");
            }
        }

        // Soft delete or restore a student
        if ($action === "delete" || $action === "restore") {
            $id = (int)($_POST["id"] ?? 0);

            if (!$id) {
                throw new RuntimeException("Invalid student ID.");
            }

            if ($action === "delete") {
                $s = $conn->prepare(
                    "UPDATE students
                     SET status = 'deleted'
                     WHERE id = ? AND status = 'active'"
                );
            } else {
                $s = $conn->prepare(
                    "UPDATE students
                     SET status = 'active'
                     WHERE id = ? AND status = 'deleted'"
                );
            }

            $s->bind_param("i", $id);
            $s->execute();

            $n = $s->affected_rows;
            $s->close();

            $message = $n
                ? (
                    $action === "delete"
                    ? "Student moved to deleted records."
                    : "Student restored."
                )
                : "No record changed.";

            go($message, $n ? "success" : "error");
        }
    } catch (mysqli_sql_exception $e) {
        error_log("CRUD error: " . $e->getMessage());

        go(
            $e->getCode() === 1062
                ? "Email already exists. Use a different email."
                : "Database operation failed. Check the input and try again.",
            "error"
        );
    } catch (RuntimeException $e) {
        go($e->getMessage(), "error");
    }
}

// Search, filter and record status
$search = trim($_GET["search"] ?? "");
$course = trim($_GET["course"] ?? "");

$deleted = (($_GET["deleted"] ?? "") === "1");
$status = $deleted ? "deleted" : "active";

// Export records to CSV
if (($_GET["export"] ?? "") === "csv") {
    $s = $conn->prepare(
        "SELECT id, student_name, email, course,
                year_of_study, mobile, created_at
         FROM students
         WHERE status = ?
           AND (
               ? = ''
               OR student_name LIKE CONCAT('%', ?, '%')
               OR email LIKE CONCAT('%', ?, '%')
               OR course LIKE CONCAT('%', ?, '%')
           )
           AND (? = '' OR course = ?)
         ORDER BY student_name"
    );

    $s->bind_param(
        "sssssss",
        $status,
        $search,
        $search,
        $search,
        $search,
        $course,
        $course
    );

    $s->execute();
    $r = $s->get_result();

    header("Content-Type: text/csv; charset=utf-8");
    header('Content-Disposition: attachment; filename="students.csv"');

    $out = fopen("php://output", "w");

    fputcsv(
        $out,
        [
            "ID",
            "Name",
            "Email",
            "Course",
            "Year",
            "Mobile",
            "Created"
        ]
    );

    while ($row = $r->fetch_assoc()) {
        fputcsv($out, $row);
    }

    fclose($out);
    $s->close();
    $conn->close();
    exit;
}

// Pagination
$page = max(1, (int)($_GET["page"] ?? 1));
$per = 8;

// Count matching records
$count = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM students
     WHERE status = ?
       AND (
           ? = ''
           OR student_name LIKE CONCAT('%', ?, '%')
           OR email LIKE CONCAT('%', ?, '%')
           OR course LIKE CONCAT('%', ?, '%')
       )
       AND (? = '' OR course = ?)"
);

$count->bind_param(
    "sssssss",
    $status,
    $search,
    $search,
    $search,
    $search,
    $course,
    $course
);

$count->execute();

$total = (int)$count->get_result()->fetch_assoc()["total"];
$count->close();

$pages = max(1, (int)ceil($total / $per));
$page = min($page, $pages);
$offset = ($page - 1) * $per;

// Fetch records for the current page
$s = $conn->prepare(
    "SELECT id, student_name, email, course,
            year_of_study, mobile, created_at
     FROM students
     WHERE status = ?
       AND (
           ? = ''
           OR student_name LIKE CONCAT('%', ?, '%')
           OR email LIKE CONCAT('%', ?, '%')
           OR course LIKE CONCAT('%', ?, '%')
       )
       AND (? = '' OR course = ?)
     ORDER BY id DESC
     LIMIT ? OFFSET ?"
);

$s->bind_param(
    "sssssssii",
    $status,
    $search,
    $search,
    $search,
    $search,
    $course,
    $course,
    $per,
    $offset
);

$s->execute();
$result = $s->get_result();

// Fetch available courses for the filter
$courseRows = $conn->query(
    "SELECT DISTINCT course
     FROM students
     WHERE status = 'active'
     ORDER BY course"
);

$courses = [];

while ($x = $courseRows->fetch_assoc()) {
    $courses[] = $x["course"];
}

// Fetch student details for editing
$edit = null;

if (
    isset($_GET["edit"]) &&
    ctype_digit((string)$_GET["edit"])
) {
    $id = (int)$_GET["edit"];

    $e = $conn->prepare(
        "SELECT *
         FROM students
         WHERE id = ? AND status = 'active'"
    );

    $e->bind_param("i", $id);
    $e->execute();

    $edit = $e->get_result()->fetch_assoc();
    $e->close();
}

// Display status messages
$msg = $_GET["message"] ?? "";
$err = (($_GET["type"] ?? "") === "error");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Practical 11 - Student CRUD</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px;
            background: #f1f5f9;
            color: #172033;
            font: 15px Arial, sans-serif;
        }

        .wrap {
            max-width: 1200px;
            margin: auto;
        }

        .top {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        h1 {
            color: #1e3a8a;
        }

        h2 {
            margin-top: 0;
        }

        .card {
            background: white;
            padding: 20px;
            margin: 18px 0;
            border: 1px solid #dbe3ef;
            border-radius: 12px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 6px;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }

        button,
        .btn {
            display: inline-block;
            padding: 10px 13px;
            border: 0;
            border-radius: 6px;
            background: #1d4ed8;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font-weight: bold;
        }

        .green {
            background: #15803d;
        }

        .red {
            background: #b91c1c;
        }

        .gray {
            background: #475569;
        }

        .light {
            background: #e2e8f0;
            color: #172033;
        }

        .toolbar {
            display: grid;
            grid-template-columns: 2fr 1fr auto auto;
            gap: 10px;
            align-items: end;
        }

        .actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-top: 14px;
        }

        .alert {
            padding: 12px;
            border-radius: 7px;
            background: #dcfce7;
            color: #166534;
        }

        .alert.error {
            background: #fee2e2;
            color: #991b1b;
        }

        .tablewrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
        }

        th,
        td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #eff6ff;
            color: #1e3a8a;
        }

        td form {
            display: inline;
        }

        .muted {
            color: #64748b;
            font-size: 13px;
        }

        .pager {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        @media (max-width: 720px) {
            body {
                padding: 10px;
            }

            .grid,
            .toolbar {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<main class="wrap">

    <div class="top">
        <div>
            <h1>Student Management CRUD</h1>
            <p class="muted">
                Practical 11 · MySQLi · Search, filter, pagination, CSV export
            </p>
        </div>

        <a
            class="btn green"
            href="?export=csv&amp;search=<?= urlencode($search) ?>&amp;course=<?= urlencode($course) ?>&amp;deleted=<?= $deleted ? 1 : 0 ?>"
        >
            Export CSV
        </a>
    </div>

    <?php if ($msg !== ""): ?>
        <p
            class="alert <?= $err ? "error" : "" ?>"
            role="status"
        >
            <?= h($msg) ?>
        </p>
    <?php endif; ?>

    <!-- Add or Edit Student -->
    <section class="card">

        <h2><?= $edit ? "Edit Student" : "Add Student" ?></h2>

        <form method="post">

            <input type="hidden" name="action" value="save">

            <input
                type="hidden"
                name="id"
                value="<?= h($edit["id"] ?? "") ?>"
            >

            <div class="grid">

                <div>
                    <label for="student_name">Student name *</label>
                    <input
                        id="student_name"
                        name="student_name"
                        required
                        maxlength="100"
                        value="<?= h($edit["student_name"] ?? "") ?>"
                    >
                </div>

                <div>
                    <label for="email">Email *</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        required
                        maxlength="150"
                        value="<?= h($edit["email"] ?? "") ?>"
                    >
                </div>

                <div>
                    <label for="course_input">Course *</label>
                    <input
                        id="course_input"
                        name="course"
                        required
                        maxlength="80"
                        placeholder="e.g. BCA"
                        value="<?= h($edit["course"] ?? "") ?>"
                    >
                </div>

                <div>
                    <label for="year">Year (1–6) *</label>
                    <input
                        id="year"
                        name="year_of_study"
                        type="number"
                        min="1"
                        max="6"
                        required
                        value="<?= h($edit["year_of_study"] ?? 1) ?>"
                    >
                </div>

                <div>
                    <label for="mobile">Mobile (optional)</label>
                    <input
                        id="mobile"
                        name="mobile"
                        maxlength="20"
                        value="<?= h($edit["mobile"] ?? "") ?>"
                    >
                </div>

            </div>

            <div class="actions">

                <button>
                    <?= $edit ? "Update student" : "Add student" ?>
                </button>

                <?php if ($edit): ?>
                    <a class="btn light" href="index.php">
                        Cancel
                    </a>
                <?php endif; ?>

            </div>

        </form>
    </section>

    <!-- Search and Filter -->
    <section class="card">

        <h2>Search and Filter</h2>

        <form class="toolbar" method="get">

            <div>
                <label for="search">Search name, email or course</label>
                <input
                    id="search"
                    name="search"
                    value="<?= h($search) ?>"
                    placeholder="Type to search"
                >
            </div>

            <div>
                <label for="course">Course</label>

                <select id="course" name="course">

                    <option value="">All courses</option>

                    <?php foreach ($courses as $c): ?>
                        <option
                            value="<?= h($c) ?>"
                            <?= $course === $c ? "selected" : "" ?>
                        >
                            <?= h($c) ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <button>Apply</button>

            <a class="btn light" href="index.php">
                Reset
            </a>

        </form>

        <div class="actions">

            <a
                class="btn gray"
                href="?deleted=<?= $deleted ? 0 : 1 ?>"
            >
                <?= $deleted ? "View active students" : "View deleted records" ?>
            </a>

        </div>
    </section>

    <!-- Student Records -->
    <section class="card">

        <h2>
            <?= $deleted ? "Deleted records" : "Student records" ?>
            <span class="muted">(<?= $total ?> records)</span>
        </h2>

        <div class="tablewrap">

            <table>

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Mobile</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($result->num_rows === 0): ?>

                        <tr>
                            <td colspan="8">No records found.</td>
                        </tr>

                    <?php else: ?>

                        <?php while ($row = $result->fetch_assoc()): ?>

                            <tr>

                                <td><?= h($row["id"]) ?></td>
                                <td><?= h($row["student_name"]) ?></td>
                                <td><?= h($row["email"]) ?></td>
                                <td><?= h($row["course"]) ?></td>
                                <td><?= h($row["year_of_study"]) ?></td>
                                <td><?= h($row["mobile"]) ?></td>
                                <td><?= h($row["created_at"]) ?></td>

                                <td>

                                    <?php if (!$deleted): ?>

                                        <a
                                            class="btn gray"
                                            href="?edit=<?= $row["id"] ?>"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="post"
                                            onsubmit="return confirm('Move this student to deleted records?')"
                                        >
                                            <input
                                                type="hidden"
                                                name="action"
                                                value="delete"
                                            >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= $row["id"] ?>"
                                            >

                                            <button class="red">
                                                Delete
                                            </button>
                                        </form>

                                    <?php else: ?>

                                        <form
                                            method="post"
                                            onsubmit="return confirm('Restore this student?')"
                                        >
                                            <input
                                                type="hidden"
                                                name="action"
                                                value="restore"
                                            >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= $row["id"] ?>"
                                            >

                                            <button class="green">
                                                Restore
                                            </button>
                                        </form>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

        <!-- Pagination -->
        <div class="pager">

            <span class="muted">
                Page <?= $page ?> of <?= $pages ?> · 8 records per page
            </span>

            <div class="actions">

                <?php
                $p = [
                    "search" => $search,
                    "course" => $course,
                    "deleted" => $deleted ? 1 : 0
                ];

                if ($page > 1):
                    $p["page"] = $page - 1;
                ?>

                    <a
                        class="btn light"
                        href="?<?= h(http_build_query($p)) ?>"
                    >
                        Previous
                    </a>

                <?php endif; ?>

                <?php if ($page < $pages): ?>

                    <?php $p["page"] = $page + 1; ?>

                    <a
                        class="btn light"
                        href="?<?= h(http_build_query($p)) ?>"
                    >
                        Next
                    </a>

                <?php endif; ?>

            </div>

        </div>

    </section>

    <p class="muted">
        Delete is implemented as soft delete.
        Records can be restored from “View deleted records”.
    </p>

</main>

</body>
</html>

<?php
$s->close();
$conn->close();
?>
