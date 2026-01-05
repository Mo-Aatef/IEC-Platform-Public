<?php
// admin/group-detail.php
require_once 'header.php';

$group_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg = "";

// 1. Fetch Group Info
$sql = "SELECT g.*, u.name as tutor_name, u.email as tutor_email 
        FROM groups g 
        LEFT JOIN users u ON g.tutor_id = u.id 
        WHERE g.id = $group_id";
$group = $conn->query($sql)->fetch_assoc();

if (!$group) {
    echo "<div class='gd-alert alert error'>Group not found. <a href='groups.php'>Back</a></div>";
    exit;
}

// 2. Handle Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_tutor'])) {
    $new_tutor_id = (int)$_POST['tutor_id'];
    $conn->query("UPDATE groups SET tutor_id = $new_tutor_id WHERE id = $group_id");
    $msg = "<div class='gd-alert alert success'>Tutor reassigned successfully.</div>";
    $group = $conn->query($sql)->fetch_assoc(); 
}

if (isset($_GET['remove_student'])) {
    $sid = (int)$_GET['remove_student'];
    $conn->query("UPDATE users SET group_id = NULL WHERE id = $sid");
    $msg = "<div class='gd-alert alert success'>Student removed from group.</div>";
}

$tutors = $conn->query("SELECT id, name FROM users WHERE role = 'tutor' ORDER BY name ASC");
$students = $conn->query("SELECT * FROM users WHERE group_id = $group_id AND role = 'student' ORDER BY name ASC");
?>

<div class="gd-header-anchor">
    <a href="groups.php" class="gd-back-btn">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    
    <div class="gd-header-titles">
        <h1><?php echo htmlspecialchars($group['name']); ?></h1>
    </div>
</div>

<?php echo $msg; ?>

<div class="gd-card">
    <div class="gd-card-header">
        <h3>Assigned Instructor</h3>
    </div>
    <div class="gd-instructor-row">
        <div class="gd-profile-box">
            <?php if ($group['tutor_id']): ?>
                <div class="gd-avatar assigned">
                    <?php echo strtoupper(substr($group['tutor_name'], 0, 1)); ?>
                </div>
                <div class="gd-tutor-info">
                    <h4><?php echo htmlspecialchars($group['tutor_name']); ?></h4>
                    <p><i class="fa-regular fa-envelope" style="margin-right: 4px;"></i> 
                       <?php echo htmlspecialchars($group['tutor_email']); ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="gd-avatar unassigned"><i class="fa-solid fa-user-xmark"></i></div>
                <div class="gd-tutor-info">
                    <h4>No Instructor</h4>
                    <p style="color: #EF4444;">Action Required</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="gd-reassign-wrapper">
            <form method="POST" action="" class="gd-reassign-form"
                  onsubmit="return confirm('⚠️ REASSIGN GROUP?\n\nChanging the instructor will effectively transfer all student data access to the new user.\n\nProceed?');">
                <select name="tutor_id" class="form-select gd-select-override">
                    <option value="">Select Replacement...</option>
                    <?php 
                    $tutors->data_seek(0);
                    while($t = $tutors->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $t['id']; ?>" <?php echo ($group['tutor_id'] == $t['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($t['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" name="change_tutor" class="btn-create" style="width: auto; padding: 0 20px; height: 42px; font-size: 13px;">
                    Reassign
                </button>
            </form>
        </div>
    </div>
</div>

<div class="gd-card">
    <div class="gd-card-header">
        <h3>Student Roster</h3>
        <span class="gd-count-badge">TOTAL: <?php echo $students->num_rows; ?></span>
    </div>
    <div class="gd-table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($students->num_rows > 0): ?>
                    <?php while($s = $students->fetch_assoc()): ?>
                    <tr>
                        <td style="color: #9CA3AF;">#<?php echo $s['id']; ?></td>
                        <td>
                            <div style="font-weight: 600; color: #111827;">
                                <?php echo htmlspecialchars($s['name']); ?>
                            </div>
                        </td>
                        <td style="color: #6B7280;"><?php echo htmlspecialchars($s['email']); ?></td>
                        <td>
                            <span class="status-badge <?php echo $s['status'] == 'active' ? 'active' : 'inactive'; ?>">
                                <?php echo ucfirst($s['status']); ?>
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <a href="?id=<?php echo $group_id; ?>&remove_student=<?php echo $s['id']; ?>" 
                               class="btn-remove"
                               onclick="return confirm('Remove <?php echo $s['name']; ?> from this group?');">
                                Remove
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align: center; padding: 48px; color: #9CA3AF;">No students currently in this group.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div> </div> </div> </main> </div> </body>
</html>