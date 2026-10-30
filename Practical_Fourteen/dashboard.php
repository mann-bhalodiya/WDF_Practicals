<?php
include_once "auth.php";
require_admin($conn);

function count_rows($conn, $sql) {
    $result = $conn->query($sql);
    $row = $result->fetch_row();
    return $row[0];
}

$total_users = count_rows($conn, "SELECT COUNT(*) FROM users");
$active_users = count_rows($conn, "SELECT COUNT(*) FROM users WHERE status = 'active'");
$inactive_users = count_rows($conn, "SELECT COUNT(*) FROM users WHERE status = 'inactive'");
$total_students = count_rows($conn, "SELECT COUNT(*) FROM students");
$total_events = count_rows($conn, "SELECT COUNT(*) FROM events");
$total_registrations = count_rows($conn, "SELECT COUNT(*) FROM registrations");

$latest = $conn->query("SELECT s.name AS student_name, e.title, r.registered_at
                        FROM registrations r
                        JOIN students s ON r.student_id = s.id
                        JOIN events e ON r.event_id = e.id
                        ORDER BY r.registered_at DESC
                        LIMIT 5");

$page_title = "Admin Dashboard";
include "header.php";
?>

<div class="container mt-4">
    <h3 class="mb-4">Admin Dashboard</h3>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-bg-primary">
                <div class="card-body">
                    <h6>Total Users</h6>
                    <h2><?php echo $total_users; ?></h2>
                    <small>Active: <?php echo $active_users; ?> | Inactive: <?php echo $inactive_users; ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-success">
                <div class="card-body">
                    <h6>Total Students</h6>
                    <h2><?php echo $total_students; ?></h2>
                    <small>Enrolled students</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-warning">
                <div class="card-body">
                    <h6>Total Events</h6>
                    <h2><?php echo $total_events; ?></h2>
                    <small>Open and closed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-info">
                <div class="card-body">
                    <h6>Registrations</h6>
                    <h2><?php echo $total_registrations; ?></h2>
                    <small>Event registrations</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Latest Registrations</div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Event</th>
                        <th>Registered On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($row = $latest->fetch_assoc()) {
                    ?>
                        <tr>
                            <td><?php echo $no; ?></td>
                            <td><?php echo htmlspecialchars($row["student_name"]); ?></td>
                            <td><?php echo htmlspecialchars($row["title"]); ?></td>
                            <td><?php echo date("d M Y, h:i A", strtotime($row["registered_at"])); ?></td>
                        </tr>
                    <?php
                        $no++;
                    }
                    if ($no == 1) {
                    ?>
                        <tr><td colspan="4" class="text-center">No registrations yet.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
