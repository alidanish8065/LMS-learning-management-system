<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/project1/bootstrap.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin'])) {
    die('Access denied.');
}

$success = '';
$error = '';

// Fetch departments for dropdown
$departments_result = $conn->query("
    SELECT d.*, f.faculty_name 
    FROM department d
    JOIN faculty f ON d.faculty_id = f.faculty_id
    WHERE d.department_status = 'active' AND d.is_deleted = FALSE 
    ORDER BY f.faculty_name, d.department_name
");
$departments = [];
while ($row = $departments_result->fetch_assoc()) {
    $departments[] = $row;
}

// Fetch all courses for prerequisites
$courses_result = $conn->query("
    SELECT course_id, course_code, course_name, department_id 
    FROM course 
    WHERE is_deleted = FALSE 
    ORDER BY course_code
");
$all_courses = [];
while ($row = $courses_result->fetch_assoc()) {
    $all_courses[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_name = trim($_POST['course_name'] ?? '');
    $course_code = strtoupper(trim($_POST['course_code'] ?? ''));
    $department_id = intval($_POST['department_id'] ?? 0);
    $credit_hrs = intval($_POST['credit_hrs'] ?? 0);
    $course_type = $_POST['course_type'] ?? 'core';
    $recommended_semester = intval($_POST['recommended_semester'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $prerequisite_courses = $_POST['prerequisites'] ?? [];
    
    if (empty($course_name) || empty($course_code) || empty($department_id) || $credit_hrs <= 0) {
        $error = "Please fill all required fields with valid values.";
    } else {
        try {
            $conn->begin_transaction();
            
            // Check if course code already exists
            $check = $conn->prepare("SELECT course_id FROM course WHERE course_code = ? AND is_deleted = FALSE");
            $check->bind_param('s', $course_code);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                throw new Exception("Course code already exists.");
            }
            
            $stmt = $conn->prepare("
                INSERT INTO course (course_name, course_code, department_id, credit_hrs, course_type, recommended_semester, description) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param('ssiisis', $course_name, $course_code, $department_id, $credit_hrs, $course_type, $recommended_semester, $description);
            
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
            
            $course_id = $conn->insert_id;
            // Insert prerequisites
            if (!empty($prerequisite_courses) && is_array($prerequisite_courses)) {
    
    $prereq_sql = "INSERT IGNORE INTO course_prerequisite (course_id, prerequisite_course_id, is_mandatory) 
                   VALUES (?, ?, TRUE)";
    $prereq_stmt = $conn->prepare($prereq_sql);
    
    if (!$prereq_stmt) {
        throw new Exception("Failed to prepare prerequisite statement");
    }
    
    foreach ($prerequisite_courses as $prereq_id) {
        $prereq_id = intval($prereq_id);
        
        // Skip invalid or self-reference
        if ($prereq_id <= 0 || $prereq_id == $course_id) {
            continue;
        }
        
        // Verify prerequisite exists (one query)
        $verify = $conn->prepare("SELECT 1 FROM course WHERE course_id = ? AND is_deleted = FALSE");
        $verify->bind_param('i', $prereq_id);
        $verify->execute();
        
        if ($verify->get_result()->num_rows > 0) {
            $prereq_stmt->bind_param('ii', $course_id, $prereq_id);
            $prereq_stmt->execute();
        }
        
        $verify->close();
    }
    
    $prereq_stmt->close();
}
            
            $conn->commit();
            $success = "Course created successfully!";
            echo "<script>setTimeout(function(){ window.location.href = 'course_list.php'; }, 2000);</script>";
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error: " . $e->getMessage();
        }
    }
}

$active_page = 'academic';

ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bi bi-journal-text"></i> Create New Course</h4>
                    <a href="course_list.php" class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class='alert alert-success alert-dismissible fade show'>
                            <?= htmlspecialchars($success) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class='alert alert-danger alert-dismissible fade show'>
                            <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="border rounded p-3 mb-4 bg-light">
                            <h5 class="mb-3 text-primary">Basic Information</h5>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Course Code <span class="text-danger">*</span></label>
                                    <input type="text" name="course_code" class="form-control" required
                                           placeholder="e.g., CS101" maxlength="50" style="text-transform: uppercase;"
                                           value="<?= isset($_POST['course_code']) ? htmlspecialchars($_POST['course_code']) : '' ?>">
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Course Name <span class="text-danger">*</span></label>
                                    <input type="text" name="course_name" class="form-control" required
                                           placeholder="e.g., Introduction to Programming"
                                           value="<?= isset($_POST['course_name']) ? htmlspecialchars($_POST['course_name']) : '' ?>">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Department <span class="text-danger">*</span></label>
                                    <select name="department_id" class="form-select" required id="departmentSelect">
                                        <option value="">-- Select Department --</option>
                                        <?php 
                                        $current_faculty = '';
                                        foreach ($departments as $dept): 
                                            if ($current_faculty !== $dept['faculty_name']) {
                                                if ($current_faculty !== '') echo '</optgroup>';
                                                echo '<optgroup label="' . htmlspecialchars($dept['faculty_name']) . '">';
                                                $current_faculty = $dept['faculty_name'];
                                            }
                                        ?>
                                            <option value="<?= $dept['department_id'] ?>"
                                                    data-dept-id="<?= $dept['department_id'] ?>"
                                                    <?= (isset($_POST['department_id']) && $_POST['department_id'] == $dept['department_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($dept['department_code']) ?> - 
                                                <?= htmlspecialchars($dept['department_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                        <?php if ($current_faculty !== '') echo '</optgroup>'; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Credit Hours <span class="text-danger">*</span></label>
                                    <input type="number" name="credit_hrs" class="form-control" required min="1" max="10"
                                           value="<?= isset($_POST['credit_hrs']) ? $_POST['credit_hrs'] : '3' ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Course Type <span class="text-danger">*</span></label>
                                    <select name="course_type" class="form-select" required>
                                        <option value="core" <?= (isset($_POST['course_type']) && $_POST['course_type'] == 'core') ? 'selected' : 'selected' ?>>Core</option>
                                        <option value="elective" <?= (isset($_POST['course_type']) && $_POST['course_type'] == 'elective') ? 'selected' : '' ?>>Elective</option>
                                    </select>
                                </div>
                            </div>
                                                    <div class="mb-3">
                            <label class="form-label">Recommended Semester</label>
                            <select name="recommended_semester" class="form-select">
                                <option value="0">Not Specified</option>
                                <?php for($i = 1; $i <= 8; $i++): ?>
                                    <option value="<?= $i ?>" <?= (isset($_POST['recommended_semester']) && $_POST['recommended_semester'] == $i) ? 'selected' : '' ?>>
                                        Semester <?= $i ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="border rounded p-3 mb-4 bg-light">
                        <h5 class="mb-3 text-primary">Prerequisites</h5>
                        <p class="text-muted">Select courses that must be completed before taking this course (optional)</p>
                        
                        <select name="prerequisites[]" class="form-select" multiple size="6" id="prerequisiteSelect">
                            <option value="">-- No Prerequisites --</option>
                            <?php foreach ($all_courses as $c): ?>
                                <option value="<?= $c['course_id'] ?>" 
                                        data-dept-id="<?= $c['department_id'] ?>"
                                        <?= (isset($_POST['prerequisites']) && in_array($c['course_id'], $_POST['prerequisites'])) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['course_code']) ?> - <?= htmlspecialchars($c['course_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Hold Ctrl (Cmd on Mac) to select multiple courses</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Course Description</label>
                        <textarea name="description" class="form-control" rows="4"
                                  placeholder="Brief description of the course content and objectives..."><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Create Course
                        </button>
                        <a href="course_list.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
<script>
// Filter prerequisite courses by selected department
document.getElementById('departmentSelect').addEventListener('change', function() {
    const selectedDeptId = this.value;
    const prerequisiteSelect = document.getElementById('prerequisiteSelect');
    const options = prerequisiteSelect.getElementsByTagName('option');
    
    for (let i = 1; i < options.length; i++) {
        const option = options[i];
        const optionDeptId = option.getAttribute('data-dept-id');
        
        if (!selectedDeptId || optionDeptId === selectedDeptId) {
            option.style.display = 'block';
        } else {
            option.style.display = 'none';
            option.selected = false;
        }
    }
});
</script>
<?php
$content = ob_get_clean();
$page_title = "Create Course - LMS";
require_once '../../../templates/layout/master_base.php';
?>
