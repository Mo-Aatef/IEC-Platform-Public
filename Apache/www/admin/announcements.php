<?php
// admin/announcements.php
require_once 'header.php';

// --- 1. INITIALIZE VARIABLES ---
$msg = "";
$edit_mode = false;
$edit_data = ['title' => '', 'message' => '', 'id' => '', 'is_urgent' => 0];
$current_user_id = $_SESSION['user_id']; 

// --- 2. HANDLE ACTIONS ---

// DELETE
if (isset($_GET['delete_id'])) {
    $did = (int)$_GET['delete_id'];
    if($conn->query("DELETE FROM announcements WHERE id=$did")) {
        $msg = "<div class='alert success'>Announcement deleted successfully.</div>";
    }
}

// FETCH FOR EDIT
if (isset($_GET['edit_id'])) {
    $eid = (int)$_GET['edit_id'];
    $res = $conn->query("SELECT * FROM announcements WHERE id=$eid");
    if ($res->num_rows > 0) {
        $edit_mode = true;
        $edit_data = $res->fetch_assoc();
    }
}

// CREATE OR UPDATE
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $is_urgent = isset($_POST['is_urgent']) ? 1 : 0; // Capture Checkbox
    
    // Default to Global (NULL) per schema
    $group_id = 'NULL'; 

    if (isset($_POST['update_news'])) {
        // Update existing
        $id = (int)$_POST['news_id'];
        $sql = "UPDATE announcements SET title='$title', message='$message', is_urgent=$is_urgent WHERE id=$id";
        if ($conn->query($sql)) {
            $msg = "<div class='alert success'>News updated successfully.</div>";
            $edit_mode = false;
            $edit_data = ['title' => '', 'message' => '', 'id' => '', 'is_urgent' => 0];
        } else {
            $msg = "<div class='alert error'>Error: " . $conn->error . "</div>";
        }
    } else {
        // Create new
        $sql = "INSERT INTO announcements (title, message, author_id, group_id, is_urgent) 
                VALUES ('$title', '$message', $current_user_id, $group_id, $is_urgent)";
        
        if ($conn->query($sql)) {
            $msg = "<div class='alert success'>Announcement posted successfully!</div>";
        } else {
            $msg = "<div class='alert error'>Error: " . $conn->error . "</div>";
        }
    }
}

// --- 3. FETCH DATA ---
$sql = "SELECT a.*, u.name as author_name 
        FROM announcements a 
        LEFT JOIN users u ON a.author_id = u.id 
        WHERE a.group_id IS NULL 
        ORDER BY a.created_at DESC";

$result = $conn->query($sql);
?>

<div class="page-header-mb">
    <h2 style="margin-bottom: 8px;" class="page-header-title">Global News</h2>
    <p class="page-header-desc">Post updates visible to all students and tutors.</p>
</div>

<?php echo $msg; ?>

<div class="card card-compact">
    <div class="card-header-flex">
        <h3 class="card-header-title">
            <?php echo $edit_mode ? "Edit Announcement" : "Post New Announcement"; ?>
        </h3>
        <?php if($edit_mode): ?>
            <a href="announcements.php" class="link-cancel">Cancel Edit</a>
        <?php endif; ?>
    </div>

    <form method="POST" action="">
        <?php if($edit_mode): ?>
            <input type="hidden" name="news_id" value="<?php echo $edit_data['id']; ?>">
        <?php endif; ?>

        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" class="form-input" required 
                   value="<?php echo htmlspecialchars($edit_data['title']); ?>" 
                   placeholder="e.g. System Maintenance Notice">
        </div>

        <div class="form-group">
            <label>Message Body</label>
            <textarea name="message" class="form-input form-textarea" required 
                      placeholder="Type your message here..."><?php echo htmlspecialchars($edit_data['message']); ?></textarea>
        </div>

        <div class="form-group">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:500; color:#374151;">
                <input type="checkbox" name="is_urgent" value="1" 
                       style="width:16px; height:16px; accent-color:#DC2626;"
                       <?php echo ($edit_data['is_urgent'] == 1) ? 'checked' : ''; ?>>
                Mark as "System Alert" / Urgent
            </label>
        </div>

        <div class="form-actions-right">
            <button type="submit" name="<?php echo $edit_mode ? 'update_news' : 'create_news'; ?>" class="btn-create btn-wide">
                <i class="fa-solid <?php echo $edit_mode ? 'fa-save' : 'fa-paper-plane'; ?>"></i> 
                <?php echo $edit_mode ? 'Save Changes' : 'Post News'; ?>
            </button>
        </div>
    </form>
</div>

<div class="card" style="padding: 0;">
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Announcement</th>
                    <th>Posted By</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr style="<?php echo $row['is_urgent'] ? 'background-color:#FEF2F2;' : ''; ?>">
                        <td class="col-date">
                            <?php echo date("M d, Y", strtotime($row['created_at'])); ?><br>
                            <span class="meta-time"><?php echo date("h:i A", strtotime($row['created_at'])); ?></span>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <?php if($row['is_urgent']): ?>
                                    <span class="status-badge active" style="background:#FEE2E2; color:#B91C1C; border:1px solid #FECACA;">URGENT</span>
                                <?php endif; ?>
                                <strong class="announcement-title">
                                    <?php echo htmlspecialchars($row['title']); ?>
                                </strong>
                            </div>
                            <div class="announcement-body">
                                <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge inactive">
                                <i class="fa-solid fa-user-shield" style="margin-right:4px;"></i>
                                <?php echo htmlspecialchars($row['author_name'] ?? 'Unknown'); ?>
                            </span>
                        </td>
                        <td class="col-actions-top">
                            <div class="action-row">
                                <a href="announcements.php?edit_id=<?php echo $row['id']; ?>" 
                                   class="btn-icon edit" 
                                   title="Edit">
                                   <i class="fa-solid fa-pen"></i>
                                </a>
                                                        
                                <a href="announcements.php?delete_id=<?php echo $row['id']; ?>" 
                                   class="btn-icon delete" 
                                   title="Delete"
                                   onclick="return confirm('Delete this announcement?');">
                                   <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center; padding:30px; color:#9CA3AF;">No global announcements found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div> </main>
</div>
</body>
</html>