<?php
// admin/users.php

// 1. INCLUDE AUTH & CONFIG MANUALLY FIRST
// We need the database connection ($conn) and session to process forms 
// BEFORE we output any HTML.
require_once '../app/auth.php';
requireRole('admin');

// --- 2. INITIALIZE VARIABLES ---
$msg = "";
$edit_mode = false;
$edit_data = ['name' => '', 'email' => '', 'group_id' => '', 'id' => ''];
$filter_role = isset($_GET['role']) ? $_GET['role'] : 'student';
$current_user_id = $_SESSION['user_id']; 

// =================================================================================
//  LOGIC BLOCK: HANDLE ACTIONS (Must be before 'header.php')
// =================================================================================

// --- A. HANDLE DELETE ACTION ---
if (isset($_GET['delete_id'])) {
    $did = (int)$_GET['delete_id'];
    
    // GUARDRAIL: Prevent Self-Deletion
    if ($did == $current_user_id) {
        $msg = "<div class='alert error'>Security Alert: You cannot delete your own admin account.</div>";
    } else {
        $conn->query("DELETE FROM users WHERE id=$did");
        $msg = "<div class='alert success'>User removed successfully.</div>";
        // Optional: Redirect to clear the URL query
        // header("Location: users.php?role=$filter_role&msg=deleted");
        // exit;
    }
}

// --- B. HANDLE FORM SUBMISSION (Create / Update) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = $_POST['role'];
    $group_id = !empty($_POST['group_id']) ? (int)$_POST['group_id'] : 'NULL';
    
    // 1. UPDATE USER
    if (isset($_POST['update_user'])) {
        $uid = (int)$_POST['user_id'];
        
        $pass_sql = "";
        if (!empty($_POST['password'])) {
            $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $pass_sql = ", password='$hashed'";
        }

        $group_sql_part = ($role == 'student') ? ", group_id=$group_id" : "";
        $sql = "UPDATE users SET name='$name', email='$email', role='$role' $group_sql_part $pass_sql WHERE id=$uid";
        
        if ($conn->query($sql)) {
            // SUCCESS: Redirect immediately (Before HTML output)
            header("Location: users.php?role=$role&msg=updated");
            exit(); 
        } else {
            $msg = "<div class='alert error'>Error: " . $conn->error . "</div>";
        }

    // 2. CREATE NEW USER
    } elseif (isset($_POST['create_user'])) {
        $password = $_POST['password'];
        $check = $conn->query("SELECT id FROM users WHERE email='$email'");
        
        if ($check->num_rows > 0) {
            $msg = "<div class='alert error'>Error: Email '$email' is already registered.</div>";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_group = ($role == 'student') ? $group_id : 'NULL';

            $sql = "INSERT INTO users (name, email, password, role, status, group_id) 
                    VALUES ('$name', '$email', '$hashed_password', '$role', 'active', $insert_group)";
            
            if ($conn->query($sql)) {
                $msg = "<div class='alert success'>User <strong>$name</strong> created successfully!</div>";
                // Optional: Redirect to avoid re-submission on refresh
            } else {
                $msg = "<div class='alert error'>Database Error: " . $conn->error . "</div>";
            }
        }
    }
}

// --- C. HANDLE STATUS TOGGLE ---
if (isset($_GET['toggle_id'])) {
    $id = (int)$_GET['toggle_id'];
    
    // GUARDRAIL: Prevent Self-Deactivation
    if ($id == $current_user_id) {
        header("Location: users.php?role=$filter_role&msg=self_lockout_prevented");
        exit();
    }

    $conn->query("UPDATE users SET status = IF(status='active', 'inactive', 'active') WHERE id=$id");
    
    // SUCCESS: Redirect immediately
    header("Location: users.php?role=$filter_role");
    exit();
}

// =================================================================================
//  VIEW BLOCK: NOW WE CAN OUTPUT HTML
// =================================================================================

// 3. START THE VIEW
// This file outputs the HTML <head>, Sidebar, etc.
require_once 'header.php'; 

// --- D. HANDLE FETCH DATA (For Edit Form & Table) ---
// This doesn't redirect, so it's safe to do here or above.

// 1. Check for success message in URL
if(isset($_GET['msg']) && $_GET['msg'] == 'updated') {
    $msg = "<div class='alert success'>User updated successfully!</div>";
}

// 2. Fetch User for Editing
if (isset($_GET['edit_id'])) {
    $eid = (int)$_GET['edit_id'];
    $res = $conn->query("SELECT * FROM users WHERE id=$eid");
    if ($res->num_rows > 0) {
        $edit_mode = true;
        $edit_data = $res->fetch_assoc();
    }
}

// 3. Fetch Table List
$sort_logic = "ORDER BY (u.id = $current_user_id) DESC, u.id DESC"; // Put ME first, then new users

if ($filter_role == 'tutor') {
    $sql = "SELECT u.*, g.name as group_name 
            FROM users u 
            LEFT JOIN groups g ON g.tutor_id = u.id 
            WHERE u.role = 'tutor' 
            $sort_logic";
} else {
    $sql = "SELECT u.*, g.name as group_name 
            FROM users u 
            LEFT JOIN groups g ON u.group_id = g.id 
            WHERE u.role = '$filter_role' 
            $sort_logic";
}
$result = $conn->query($sql);

// 4. Fetch Groups for Dropdown
$groups_list = $conn->query("SELECT id, name FROM groups ORDER BY id DESC");
$groups_options = [];
while($g = $groups_list->fetch_assoc()) { $groups_options[] = $g; }
?>

<div class="tabs-nav">
    <a href="users.php?role=student" class="tab-link <?php echo $filter_role == 'student' ? 'active' : ''; ?>">Students</a>
    <a href="users.php?role=tutor" class="tab-link <?php echo $filter_role == 'tutor' ? 'active' : ''; ?>">Tutors</a>
    <a href="users.php?role=admin" class="tab-link <?php echo $filter_role == 'admin' ? 'active' : ''; ?>">Admins</a>
</div>

<?php echo $msg; ?>

<?php if($filter_role !== 'admin' || $edit_mode): ?>
<div class="card card-compact">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <h3 class="text-xl" style="margin:0;">
            <?php echo $edit_mode ? "Edit " . ucfirst($filter_role) : "Add New " . ucfirst($filter_role); ?>
        </h3>
        <?php if($edit_mode): ?>
            <a href="users.php?role=<?php echo $filter_role; ?>" class="btn-cancel">
                <i class="fa-solid fa-xmark"></i> Cancel Edit
            </a>
        <?php endif; ?>
    </div>

    <form method="POST" action="">
        <input type="hidden" name="role" value="<?php echo $filter_role; ?>">
        <?php if($edit_mode): ?>
            <input type="hidden" name="user_id" value="<?php echo $edit_data['id']; ?>">
        <?php endif; ?>
        
        <div class="form-grid">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" class="form-input" required 
                       value="<?php echo htmlspecialchars($edit_data['name']); ?>" 
                       placeholder="e.g. Sarah Smith">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-input" required 
                       value="<?php echo htmlspecialchars($edit_data['email']); ?>" 
                       placeholder="sarah@example.com">
            </div>
            <div class="form-group">
                <label><?php echo $edit_mode ? "New Password (Optional)" : "Password"; ?></label>
                <input type="text" name="password" class="form-input" 
                       <?php echo $edit_mode ? '' : 'required value="IEC2025"'; ?> 
                       placeholder="<?php echo $edit_mode ? 'Leave empty to keep current' : ''; ?>">
            </div>

            <?php if($filter_role == 'student'): ?>
            <div class="form-group">
                <label>Assign Group</label>
                <select name="group_id" class="form-select">
                    <option value="">-- Unassigned --</option>
                    <?php foreach($groups_options as $g): ?>
                        <option value="<?php echo $g['id']; ?>" 
                            <?php echo ($edit_mode && $edit_data['group_id'] == $g['id']) ? 'selected' : ''; ?>>
                            <?php echo $g['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <button type="submit" name="<?php echo $edit_mode ? 'update_user' : 'create_user'; ?>" class="btn-create">
                    <i class="fa-solid <?php echo $edit_mode ? 'fa-save' : 'fa-plus'; ?>"></i> 
                    <?php echo $edit_mode ? 'Update User' : 'Create'; ?>
                </button>
            </div>
        </div>
    </form>
</div>
<?php endif; ?>


<div class="card" style="padding: 0;">
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <?php if($filter_role == 'student' || $filter_role == 'tutor'): ?><th>Group</th><?php endif; ?>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <?php 
                        // Logic to detect if this row is the logged-in admin
                        $is_me = ($row['id'] == $current_user_id); 
                    ?>
                    <tr style="<?php echo $is_me ? 'background-color:#fafafa;' : ''; ?>">
                        <td style="color:#9CA3AF;">#<?php echo $row['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                            
                            <?php if($is_me): ?>
                                <span class="badge-current">You</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        
                        <?php if($filter_role == 'student' || $filter_role == 'tutor'): ?>
                        <td>
                            <?php if($row['group_name']): ?>
                                <span class="status-badge active" style="background:#EEF2FF; color:#4F46E5; border:1px solid #C7D2FE;">
                                    <?php if($filter_role == 'tutor') echo '<i class="fa-solid fa-chalkboard-user" style="margin-right:6px;"></i>'; ?>
                                    <?php echo htmlspecialchars($row['group_name']); ?>
                                </span>
                            <?php else: ?>
                                <span class="status-badge inactive" style="border:1px dashed #9ca3af;">Unassigned</span>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>

                        <td>
                            <span class="status-badge <?php echo $row['status'] == 'active' ? 'active' : 'inactive'; ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </td>
                        
                        <td style="text-align:right;">
                            <div class="action-row">
                                
                                <?php if($is_me): ?>
                                    <span style="font-size:12px; color:#9CA3AF; font-style:italic; margin-right:8px;">
                                        Active Session
                                    </span>
                                    <a href="users.php?edit_id=<?php echo $row['id']; ?>&role=<?php echo $filter_role; ?>" 
                                       class="btn-icon edit" 
                                       title="Edit My Profile">
                                       <i class="fa-solid fa-pen"></i>
                                    </a>
                                
                                <?php else: ?>
                                    <a href="users.php?toggle_id=<?php echo $row['id']; ?>&role=<?php echo $filter_role; ?>" 
                                       class="btn-status-link"
                                       style="color: <?php echo $row['status'] == 'active' ? '#EF4444' : '#10B981'; ?>;">
                                       <?php echo $row['status'] == 'active' ? 'Deactivate' : 'Activate'; ?>
                                    </a>
                                    
                                    <a href="users.php?edit_id=<?php echo $row['id']; ?>&role=<?php echo $filter_role; ?>" 
                                       class="btn-icon edit" 
                                       title="Edit">
                                       <i class="fa-solid fa-pen"></i>
                                    </a>
                                    
                                    <a href="users.php?delete_id=<?php echo $row['id']; ?>&role=<?php echo $filter_role; ?>" 
                                       class="btn-icon delete" 
                                       title="Permanently Remove"
                                       onclick="return confirm('Are you sure you want to PERMANENTLY delete this user?');">
                                       <i class="fa-solid fa-trash"></i>
                                    </a>
                                <?php endif; ?>

                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center; padding:20px; color:#999;">No users found.</td></tr>
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