<?php
// tutor/announcements.php
require_once 'header.php';

$current_user_id = $_SESSION['user_id'];
$tutor_group_id = 0; 

if (isset($_SESSION['group_id'])) {
    $tutor_group_id = (int)$_SESSION['group_id'];
} else {
    $group_check = $conn->query("SELECT id FROM groups WHERE tutor_id = $current_user_id LIMIT 1");
    if ($group_check && $group_check->num_rows > 0) {
        $row = $group_check->fetch_assoc();
        $tutor_group_id = (int)$row['id'];
        $_SESSION['group_id'] = $tutor_group_id; 
    }
}

if ($tutor_group_id == 0) {
    echo "<div class='empty-state'>
            <i class='fa-solid fa-person-chalkboard'></i>
            <h2>No Class Assigned</h2>
            <p>You have not been assigned to a class yet. Please contact an Administrator.</p>
          </div></div></main></div></body></html>";
    exit; 
}

$msg = "";
$edit_mode = false;
$edit_data = ['title' => '', 'message' => '', 'id' => '', 'is_urgent' => 0];

if (isset($_GET['delete_id'])) {
    $did = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ? AND group_id = ?");
    $stmt->bind_param("ii", $did, $tutor_group_id);
    if ($stmt->execute()) {
        $msg = "<div class='status-pill sp-green'>Announcement deleted.</div>";
    }
}

if (isset($_GET['edit_id'])) {
    $eid = (int)$_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM announcements WHERE id = ? AND group_id = ?");
    $stmt->bind_param("ii", $eid, $tutor_group_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $edit_mode = true;
        $edit_data = $res->fetch_assoc();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $is_urgent = isset($_POST['is_urgent']) ? 1 : 0;

    if (isset($_POST['update_news'])) {
        $id = (int)$_POST['news_id'];
        $sql = "UPDATE announcements SET title='$title', message='$message', is_urgent=$is_urgent WHERE id=$id AND group_id=$tutor_group_id";
        if ($conn->query($sql)) {
            $msg = "<div class='status-pill sp-green'>Updated successfully.</div>";
            $edit_mode = false;
            $edit_data = ['title' => '', 'message' => '', 'id' => '', 'is_urgent' => 0];
        }
    } else {
        $sql = "INSERT INTO announcements (title, message, author_id, group_id, is_urgent) VALUES ('$title', '$message', $current_user_id, $tutor_group_id, $is_urgent)";
        if ($conn->query($sql)) {
            $msg = "<div class='status-pill sp-green'>Posted successfully!</div>";
        }
    }
}

$sql = "SELECT * FROM announcements WHERE group_id = $tutor_group_id ORDER BY created_at DESC";
$result = $conn->query($sql);
$post_count = $result->num_rows;
?>

<div class="pacing-hero">
    <div class="ph-content">
        <span class="ph-label">Communication</span>
        <h1 class="ph-title">Class Announcements</h1>
        <p class="ph-subtitle">Manage updates and urgent alerts for your specific group.</p>
    </div>
    <div class="ph-stat">
        <div class="ph-stat-num"><?php echo $post_count; ?></div>
        <div class="ph-stat-label">Total Posts</div>
    </div>
</div>

<?php echo $msg; ?>

<div class="card">
    <div class="card-header-flex">
        <h3 class="card-header-title">
            <?php echo $edit_mode ? "Edit Post" : "Compose New"; ?>
        </h3>
        <?php if($edit_mode): ?>
            <a href="announcements.php" class="link-cancel">Cancel</a>
        <?php endif; ?>
    </div>

    <form method="POST" action="">
        <?php if($edit_mode): ?>
            <input type="hidden" name="news_id" value="<?php echo $edit_data['id']; ?>">
        <?php endif; ?>

        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" class="form-input" required value="<?php echo htmlspecialchars($edit_data['title']); ?>" placeholder="e.g. Homework Reminder">
        </div>

        <div class="form-group">
            <label>Message Body</label>
            <textarea name="message" class="form-textarea" required placeholder="Type your message here..."><?php echo htmlspecialchars($edit_data['message']); ?></textarea>
        </div>

        <div class="form-group">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:500; color:#374151;">
                <input type="checkbox" name="is_urgent" value="1" style="width:16px; height:16px; accent-color:#DC2626;" <?php echo ($edit_data['is_urgent'] == 1) ? 'checked' : ''; ?>>
                Mark as "Action Required" / Urgent
            </label>
        </div>

        <div class="form-actions-right">
            <button type="submit" name="<?php echo $edit_mode ? 'update_news' : 'create_news'; ?>" class="btn-create btn-wide">
                <i class="fa-solid <?php echo $edit_mode ? 'fa-save' : 'fa-paper-plane'; ?>"></i> 
                <?php echo $edit_mode ? 'Save Changes' : 'Post to Group'; ?>
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
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php 
                        $result->data_seek(0); 
                        while($row = $result->fetch_assoc()): 
                    ?>
                    <tr style="<?php echo $row['is_urgent'] ? 'background-color:#FEF2F2;' : ''; ?>">
                        <td class="col-date">
                            <?php echo date("M d, Y", strtotime($row['created_at'])); ?><br>
                            <span class="meta-time"><?php echo date("h:i A", strtotime($row['created_at'])); ?></span>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <?php if($row['is_urgent']): ?>
                                    <span class="status-badge sp-red">URGENT</span>
                                <?php endif; ?>
                                <strong class="announcement-title"><?php echo htmlspecialchars($row['title']); ?></strong>
                            </div>
                            <div class="announcement-body"><?php echo nl2br(htmlspecialchars($row['message'])); ?></div>
                        </td>
                        <td class="col-actions-top">
                            <div class="action-row">
                                <a href="announcements.php?edit_id=<?php echo $row['id']; ?>" class="btn-icon edit" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                <a href="announcements.php?delete_id=<?php echo $row['id']; ?>" class="btn-icon delete" title="Delete" onclick="return confirm('Delete this post?');"><i class="fa-solid fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="empty-state">No announcements posted yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div> </main> </div> </body> </html>