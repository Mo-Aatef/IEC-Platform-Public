<?php
// admin/modules.php
require_once 'header.php';

// --- HANDLE DELETE ---
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    // Delete dependent data first
    $conn->query("DELETE FROM module_steps WHERE module_id=$id");
    $conn->query("DELETE FROM lessons WHERE module_id=$id");
    $conn->query("DELETE FROM offline_sessions WHERE module_id=$id");
    $conn->query("DELETE FROM modules WHERE id=$id");
    echo "<script>window.location.href='modules.php';</script>";
    exit();
}

// --- FETCH DATA ---
$sql_modules = "SELECT * FROM modules ORDER BY module_number ASC";
$result_modules = $conn->query($sql_modules);
?>

<div class="module-list-container">
    <div class="page-header">
        <div>
            <h2 class="page-title">Manage Modules</h2>
            <p style="margin:4px 0 0 0; color:#6B7280; font-size:14px;">Control your 28-week curriculum pacing.</p>
        </div>
    </div>

    <div class="module-table-card">
        <table class="module-table">
            <thead>
                <tr>
                    <th width="100">Week #</th>
                    <th>Topic</th>
                    <th width="180">Global Access</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_modules->num_rows > 0): ?>
                    <?php while($row = $result_modules->fetch_assoc()): 
                        // DB: 1=Locked, 0=Open
                        // UI: Checked=Open, Unchecked=Locked
                        $is_open = ($row['is_global_locked'] == 0);
                    ?>
                    <tr class="module-row">
                        <td>
                            <span class="status-badge" style="background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0;">
                                Week <?php echo $row['module_number']; ?>
                            </span>
                        </td>
                        <td>
                            <strong style="color: #1e293b; font-size: 15px; display:block; margin-bottom:4px;">
                                <?php echo htmlspecialchars($row['title']); ?>
                            </strong>
                            <div style="font-size:13px; color:#6b7280;">
                                <?php echo htmlspecialchars(substr($row['description'] ?? '', 0, 75)) . '...'; ?>
                            </div>
                        </td>
                        <td>
                            <label class="pacing-toggle">
                                <input type="checkbox" 
                                       onchange="toggleModule(this, <?php echo $row['id']; ?>)"
                                       <?php echo $is_open ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                                <span class="toggle-label"><?php echo $is_open ? 'OPEN' : 'LOCKED'; ?></span>
                            </label>
                        </td>
                        <td style="text-align: right;">
                            <div class="action-links">
                                <a href="week-detail.php?id=<?php echo $row['id']; ?>" class="btn-icon edit" title="Manage Content">
                                    <i class="fa-solid fa-list-check"></i>
                                </a>
                                </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding: 40px; color: #9CA3AF;">
                            No modules found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    async function toggleModule(checkbox, id) {
        const isChecked = checkbox.checked; // True = OPEN
        const label = checkbox.parentElement.querySelector('.toggle-label');
        
        // 1. Optimistic UI Update
        if (isChecked) {
            label.innerText = "OPEN";
            label.style.color = "#10b981";
        } else {
            label.innerText = "LOCKED";
            label.style.color = "#64748b";
        }

        // 2. Send to Server
        try {
            const res = await fetch('api-toggle-module.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ module_id: id, is_open: isChecked ? 1 : 0 })
            });
            
            const data = await res.json();
            
            if(!data.success) {
                alert("Error: " + data.message);
                checkbox.checked = !isChecked; // Revert
                // Reset label logic here if needed
            }
        } catch(e) {
            console.error(e);
            alert("Connection error.");
            checkbox.checked = !isChecked; // Revert
        }
    }
</script>