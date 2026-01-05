<?php
// admin/groups.php
require_once 'header.php';

// --- 1. INITIALIZE VARIABLES ---
$msg = "";
$edit_mode = false;
$edit_data = ['name' => '', 'tutor_id' => '', 'id' => ''];

// --- 2. HANDLE ACTIONS ---

// DELETE GROUP
if (isset($_GET['delete_id'])) {
    $did = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM groups WHERE id=$did");
}

// FETCH FOR EDIT
if (isset($_GET['edit_id'])) {
    $eid = (int)$_GET['edit_id'];
    $res = $conn->query("SELECT * FROM groups WHERE id=$eid");
    if ($res->num_rows > 0) {
        $edit_mode = true;
        $edit_data = $res->fetch_assoc();
    }
}

// CREATE OR UPDATE GROUP
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $tutor_id = !empty($_POST['tutor_id']) ? (int)$_POST['tutor_id'] : 'NULL';

    if (isset($_POST['update_group'])) {
        $gid = (int)$_POST['group_id'];
        $sql = "UPDATE groups SET name='$name', tutor_id=$tutor_id WHERE id=$gid";
        if ($conn->query($sql)) {
            $edit_mode = false;
            $edit_data = ['name' => '', 'tutor_id' => '', 'id' => ''];
        } else {
            $msg = "<div class='alert error'>Error: " . $conn->error . "</div>";
        }
    } else {
        $sql = "INSERT INTO groups (name, tutor_id) VALUES ('$name', $tutor_id)";
        if ($conn->query($sql)) {
        } else {
            $msg = "<div class='alert error'>Error: " . $conn->error . "</div>";
        }
    }
}

// --- 3. FETCH DATA ---
// Get Groups with Tutor Name and Student Count
$sql = "SELECT g.id, g.name, u.name as tutor_name, 
        (SELECT COUNT(*) FROM users WHERE group_id = g.id AND role='student') as student_count
        FROM groups g
        LEFT JOIN users u ON g.tutor_id = u.id
        ORDER BY g.id DESC";
$result = $conn->query($sql);

// Get Tutors for Dropdown
$tutors = $conn->query("SELECT id, name FROM users WHERE role='tutor' ORDER BY name ASC");
$tutor_options = [];
while($t = $tutors->fetch_assoc()) { $tutor_options[] = $t; }
?>

<div style="margin-bottom: 32px;">
    <h2 style="margin:0; margin-bottom: 8px; font-size:24px; font-weight:700; color:var(--text-main);">Group Management</h2>
    <p class="page-header-desc">Create groups, assign tutors, and manage student rosters.</p>
</div>

<?php echo $msg; ?>

<div class="card card-compact">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <h3 class="text-xl" style="margin:0;">
            <?php echo $edit_mode ? "Edit Group" : "Create New Group"; ?>
        </h3>
        <?php if($edit_mode): ?>
            <a href="groups.php" style="font-size:12px; color:#6b7280;">Cancel Edit</a>
        <?php endif; ?>
    </div>

    <form method="POST" action="">
        <?php if($edit_mode): ?>
            <input type="hidden" name="group_id" value="<?php echo $edit_data['id']; ?>">
        <?php endif; ?>

        <div class="form-grid">
            <div class="form-group">
                <label>Group Name</label>
                <input type="text" name="name" class="form-input" required 
                       value="<?php echo htmlspecialchars($edit_data['name']); ?>" 
                       placeholder="e.g. IEC Group 12">
            </div>

            <div class="form-group">
                <label>Assign Tutor</label>
                <select name="tutor_id" class="form-select">
                    <option value="">-- Select Available Tutor --</option>
                    <?php foreach($tutor_options as $t): ?>
                        <option value="<?php echo $t['id']; ?>" 
                            <?php echo ($edit_mode && $edit_data['tutor_id'] == $t['id']) ? 'selected' : ''; ?>>
                            <?php echo $t['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <button type="submit" name="<?php echo $edit_mode ? 'update_group' : 'create_group'; ?>" class="btn-create">
                    <i class="fa-solid <?php echo $edit_mode ? 'fa-save' : 'fa-plus'; ?>"></i> 
                    <?php echo $edit_mode ? 'Update Group' : 'Create Group'; ?>
                </button>
            </div>
        </div>
    </form>
</div>

<div class="card" style="padding: 0;">
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Group Name</th>
                    <th>Assigned Tutor</th>
                    <th>Students</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td style="color:#9CA3AF;">#<?php echo $row['id']; ?></td>
                        <td><strong><?php echo $row['name']; ?></strong></td>
                        <td>
                            <?php if($row['tutor_name']): ?>
                                <span class="status-badge active" style="background:#EEF2FF; color:#4F46E5;">
                                    <i class="fa-solid fa-user-tie" style="margin-right:4px;"></i>
                                    <?php echo $row['tutor_name']; ?>
                                </span>
                            <?php else: ?>
                                <span class="status-badge inactive">No Tutor</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge active" style="background:#ECFDF5; color:#065F46;">
                                <?php echo $row['student_count']; ?> Students
                            </span>
                        </td>
                        <td style="text-align:right;">
                            <div class="action-row">
                                <a href="group-detail.php?id=<?php echo $row['id']; ?>" 
                                   class="btn-icon view" 
                                   title="View Details / Roster">
                                   <i class="fa-solid fa-eye"></i>
                                </a>
                                                        
                                <a href="groups.php?edit_id=<?php echo $row['id']; ?>" 
                                   class="btn-icon edit" 
                                   title="Edit Group">
                                   <i class="fa-solid fa-pen"></i>
                                </a>
                                                        
                                <a href="groups.php?delete_id=<?php echo $row['id']; ?>" 
                                   class="btn-icon delete" 
                                   title="Delete Group"
                                   onclick="return confirm('Delete this group? Students will become Unassigned.');">
                                   <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center; padding:20px;">No groups found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div> 
</main>
</div>
</body>
</html>