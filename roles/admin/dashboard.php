<?php
// dashboard.php - Admin Dashboard
require_once $_SERVER['DOCUMENT_ROOT'] . '/project1/bootstrap.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header('Location: ' . url('public/login.php'));
    exit;
}

$id         = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'] ?? 'Admin';
$last_name  = $_SESSION['last_name'] ?? '';
$role       = $_SESSION['role'] ?? 'admin';
$active_page = 'home';

// --- KPI Stats (single query) ---
$stats_row = $conn->query("
    SELECT
        (SELECT COUNT(*) FROM users WHERE is_deleted = FALSE) AS total_users,
        (SELECT COUNT(*) FROM student)                        AS students,
        (SELECT COUNT(*) FROM teacher)                        AS faculty,
        (SELECT COUNT(*) FROM department WHERE is_deleted = FALSE) AS departments,
        (SELECT COUNT(*) FROM course WHERE is_deleted = FALSE)    AS courses,
        (SELECT COUNT(*) FROM enrollment WHERE status = 'enrolled') AS enrollments
")->fetch_assoc();
$stats = $stats_row ?: [
    'total_users' => 0, 'students' => 0, 'faculty' => 0,
    'departments' => 0, 'courses' => 0, 'enrollments' => 0
];

// --- Recent Users ---
$recent_result = $conn->query("
    SELECT u.id, u.user_code, u.first_name, u.last_name, u.email, u.status, u.created_at,
           GROUP_CONCAT(r.role_name SEPARATOR ', ') AS roles
    FROM users u
    LEFT JOIN user_roles ur ON u.id = ur.user_id
    LEFT JOIN roles r ON ur.role_id = r.role_id
    WHERE u.is_deleted = FALSE
    GROUP BY u.id
    ORDER BY u.created_at DESC
    LIMIT 8
");
$recent_users = ($recent_result !== false) ? $recent_result->fetch_all(MYSQLI_ASSOC) : [];

// --- Recent Notifications ---
$stmt = $conn->prepare("
    SELECT n.title, n.message, n.created_at, un.is_read
    FROM user_notification un
    JOIN notification n ON un.notification_id = n.notification_id
    WHERE un.user_id = ?
    ORDER BY n.created_at DESC
    LIMIT 5
");
$stmt->bind_param("i", $id);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

ob_start();
?>

<!-- Welcome Banner -->
<div class="card mb-4 shadow-sm border-0 bg-primary text-white">
    <div class="card-body py-3">
        <h4 class="mb-0">Welcome back, <?= htmlspecialchars($first_name . ' ' . $last_name) ?>!</h4>
        <small class="opacity-75"><?= ucfirst($role) ?> &mdash; <?= date('l, F j, Y') ?></small>
    </div>
</div>

<!-- KPI Cards -->
<div class="row mb-4 g-3">
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card text-center shadow-sm h-100">
            <div class="card-body">
                <div class="fs-1 text-primary">👥</div>
                <h3 class="mb-0"><?= $stats['total_users'] ?></h3>
                <small class="text-muted">Total Users</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card text-center shadow-sm h-100">
            <div class="card-body">
                <div class="fs-1 text-success">🎓</div>
                <h3 class="mb-0"><?= $stats['students'] ?></h3>
                <small class="text-muted">Students</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card text-center shadow-sm h-100">
            <div class="card-body">
                <div class="fs-1 text-warning">👨‍🏫</div>
                <h3 class="mb-0"><?= $stats['faculty'] ?></h3>
                <small class="text-muted">Faculty</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card text-center shadow-sm h-100">
            <div class="card-body">
                <div class="fs-1 text-info">🏢</div>
                <h3 class="mb-0"><?= $stats['departments'] ?></h3>
                <small class="text-muted">Departments</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card text-center shadow-sm h-100">
            <div class="card-body">
                <div class="fs-1 text-secondary">📚</div>
                <h3 class="mb-0"><?= $stats['courses'] ?></h3>
                <small class="text-muted">Courses</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card text-center shadow-sm h-100">
            <div class="card-body">
                <div class="fs-1 text-danger">📋</div>
                <h3 class="mb-0"><?= $stats['enrollments'] ?></h3>
                <small class="text-muted">Enrollments</small>
            </div>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="row mb-4 g-3">
    <div class="col-12">
        <h5 class="mb-3"><i class="bi bi-lightning-charge"></i> Quick Actions</h5>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= url('admin_tools/User/create_user.php') ?>" class="btn btn-outline-primary w-100">
            <i class="bi bi-person-plus"></i><br>Add User
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= url('admin_tools/Academic/Department/create_department.php') ?>" class="btn btn-outline-info w-100">
            <i class="bi bi-building-add"></i><br>Add Department
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= url('admin_tools/Academic/Courses/create_course.php') ?>" class="btn btn-outline-success w-100">
            <i class="bi bi-journal-plus"></i><br>Add Course
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= url('public/notification/create_notification.php') ?>" class="btn btn-outline-warning w-100">
            <i class="bi bi-bell-fill"></i><br>Send Notification
        </a>
    </div>
</div>

<!-- Recent Users -->
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-people"></i> Recent Users</h5>
        <a href="<?= url('admin_tools/User/user_list.php') ?>" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($recent_users)): ?>
            <div class="p-3 text-muted">No users found.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>User Code</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_users as $u): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($u['user_code']) ?></code></td>
                                <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                                <td><?= htmlspecialchars($u['email'] ?? '-') ?></td>
                                <td>
                                    <?php if (!empty($u['roles'])): ?>
                                        <?php foreach (explode(', ', $u['roles']) as $r): ?>
                                            <span class="badge bg-secondary"><?= ucfirst(htmlspecialchars(trim($r))) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $u['status'] === 'active' ? 'success' : ($u['status'] === 'suspended' ? 'warning' : 'secondary') ?>">
                                        <?= ucfirst($u['status']) ?>
                                    </span>
                                </td>
                                <td><small class="text-muted"><?= date('M d, Y', strtotime($u['created_at'])) ?></small></td>
                                <td>
                                    <a href="<?= url('admin_tools/User/view_user.php') ?>?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Notifications -->
<?php if (!empty($notifications)): ?>
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-bell"></i> My Notifications</h5>
        <a href="<?= url('public/notification/notification_list.php') ?>" class="btn btn-sm btn-outline-secondary">View All</a>
    </div>
    <div class="list-group list-group-flush">
        <?php foreach ($notifications as $note): ?>
            <div class="list-group-item <?= $note['is_read'] ? '' : 'list-group-item-primary' ?>">
                <div class="d-flex justify-content-between">
                    <strong><?= htmlspecialchars($note['title']) ?></strong>
                    <small class="text-muted"><?= date('M d, Y', strtotime($note['created_at'])) ?></small>
                </div>
                <small><?= htmlspecialchars($note['message']) ?></small>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
$page_title = 'Admin Dashboard - LMS';
require_once include_file('templates/layout/master_base.php');
$conn->close();
?>
