<?php
// admin/lesson-editor.php
require_once 'header.php';

// 1. GET INPUTS
$lesson_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$module_id = isset($_GET['module_id']) ? (int)$_GET['module_id'] : 0;
$day_num   = isset($_GET['day']) ? (int)$_GET['day'] : 0;

// 2. HANDLE AUTO-CREATION (If coming from Week Detail "Create" button)
if ($lesson_id === 0 && $module_id > 0 && $day_num > 0) {
    // A. Check if it actually exists (prevent duplicates)
    $check = $conn->query("SELECT id FROM lessons WHERE module_id=$module_id AND day_number=$day_num");
    if ($check->num_rows > 0) {
        $lesson_id = $check->fetch_assoc()['id'];
    } else {
        // B. Create Placeholder
        $def_title = "Day $day_num Lesson";
        $stmt = $conn->prepare("INSERT INTO lessons (module_id, day_number, title, description, is_unlocked) VALUES (?, ?, ?, '', 0)");
        $stmt->bind_param("iis", $module_id, $day_num, $def_title);
        if ($stmt->execute()) {
            $lesson_id = $stmt->insert_id;
        } else {
            die("Error creating lesson: " . $conn->error);
        }
    }
}

// 3. FINAL VALIDATION
if ($lesson_id === 0) {
    echo "<script>alert('Invalid Request'); window.location.href='modules.php';</script>";
    exit;
}

// 4. FETCH LESSON & MODULE INFO
$sql = "SELECT l.*, m.title as module_title, m.module_number 
        FROM lessons l 
        JOIN modules m ON l.module_id = m.id 
        WHERE l.id = $lesson_id";
$lesson = $conn->query($sql)->fetch_assoc();
if (!$lesson) die("Lesson not found.");

// 5. HANDLE SAVE (Standard Update Logic)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // A. Update Lesson Details
    $l_title = $conn->real_escape_string($_POST['title']);
    $l_desc = $conn->real_escape_string($_POST['description']);
    $conn->query("UPDATE lessons SET title='$l_title', description='$l_desc' WHERE id=$lesson_id");

    // B. Re-create Steps (Wipe old, insert new)
    $conn->query("DELETE FROM module_steps WHERE lesson_id = $lesson_id");

    // 1. WARMUP
    if (!empty($_POST['warmup_intro'])) {
        $vocab = [];
        if(isset($_POST['vocab_word'])) {
            for($i=0; $i<count($_POST['vocab_word']); $i++) {
                if(!empty($_POST['vocab_word'][$i])) {
                    $vocab[] = ['word' => $_POST['vocab_word'][$i], 'def' => $_POST['vocab_def'][$i] ?? ''];
                }
            }
        }
        // SAFETY FIX: Escape the JSON string
        $data = json_encode(['intro' => $_POST['warmup_intro'], 'vocab' => $vocab]);
        $data_safe = $conn->real_escape_string($data); 
        
        $sql = "INSERT INTO module_steps (module_id, lesson_id, step_order, step_type, title, content_data) 
                VALUES ({$lesson['module_id']}, $lesson_id, 1, 'warmup', 'Warm-up', '$data_safe')";
        
        if(!$conn->query($sql)) { die("Error saving Warmup: " . $conn->error); }
    }

    // 2. WATCH
    if (!empty($_POST['media_url']) || !empty($_POST['media_text'])) {
        // SAFETY FIX: Escape the JSON string
        $data = json_encode([
            'type' => $_POST['media_type'],
            'url'  => $_POST['media_url'],
            'text' => $_POST['media_text'],
            'objectives' => $_POST['objectives']
        ]);
        $data_safe = $conn->real_escape_string($data);

        $sql = "INSERT INTO module_steps (module_id, lesson_id, step_order, step_type, title, content_data) 
                VALUES ({$lesson['module_id']}, $lesson_id, 2, 'watch', 'Watch & Learn', '$data_safe')";
        
        if(!$conn->query($sql)) { die("Error saving Watch: " . $conn->error); }
    }

    // 3. QUIZ
    if (isset($_POST['questions'])) {
        $questions = [];
        foreach($_POST['questions'] as $q) {
            if(!empty($q['title'])) $questions[] = $q;
        }
        if(!empty($questions)) {
            // SAFETY FIX: Escape the JSON string
            $data = json_encode(['questions' => $questions]);
            $data_safe = $conn->real_escape_string($data);

            $sql = "INSERT INTO module_steps (module_id, lesson_id, step_order, step_type, title, content_data) 
                    VALUES ({$lesson['module_id']}, $lesson_id, 3, 'practice', 'Practice Quiz', '$data_safe')";
            
            if(!$conn->query($sql)) { die("Error saving Quiz: " . $conn->error); }
        }
    }

    echo "<script>window.location.href='week-detail.php?id={$lesson['module_id']}';</script>";
    exit;
}

// 6. FETCH EXISTING STEPS FOR DISPLAY
$steps = [];
$res = $conn->query("SELECT * FROM module_steps WHERE lesson_id = $lesson_id ORDER BY step_order ASC");
while($row = $res->fetch_assoc()) {
    $steps[$row['step_type']] = json_decode($row['content_data'], true);
}

$s_warmup = $steps['warmup'] ?? ['intro'=>'', 'vocab'=>[]];
$s_watch  = $steps['watch'] ?? ['type'=>'video', 'url'=>'', 'text'=>'', 'objectives'=>''];
$s_quiz   = $steps['practice'] ?? ['questions'=>[]];
?>

<div class="lesson-builder-wrapper">
    
    <form method="POST">
        <div class="lb-sticky-header">
            <div>
                <div class="lb-breadcrumb">
                    Week <?php echo $lesson['module_number']; ?> · Day <?php echo $lesson['day_number']; ?>
                </div>
                <h2 class="lb-page-title">Edit Lesson Content</h2>
            </div>
            <div class="lb-header-actions">
                <a href="week-detail.php?id=<?php echo $lesson['module_id']; ?>" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </div>

        <div class="lb-card">
            <div class="lb-card-header"><h3 class="lb-title">Lesson Overview</h3></div>
            <div class="lb-card-body">
                <div class="lb-form-group">
                    <label class="lb-label">Lesson Title</label>
                    <input type="text" name="title" class="lb-input" value="<?php echo htmlspecialchars($lesson['title']); ?>" required>
                </div>
                <div class="lb-form-group">
                    <label class="lb-label">Short Description</label>
                    <input type="text" name="description" class="lb-input" value="<?php echo htmlspecialchars($lesson['description']); ?>">
                </div>
            </div>
        </div>

        <div class="lb-card" id="card-warmup">
            <div class="lb-card-header" onclick="toggleCard('card-warmup')">
                <div class="lb-header-left">
                    <span class="lb-badge badge-green">1</span>
                    <h3 class="lb-title">Warm-up & Vocabulary</h3>
                </div>
                <i class="fa-solid fa-chevron-down lb-toggle-btn"></i>
            </div>
            <div class="lb-card-body">
                <div class="lb-form-group">
                    <label class="lb-label">Introduction Text</label>
                    <textarea name="warmup_intro" class="lb-textarea" placeholder="Welcome students to this lesson..."><?php echo htmlspecialchars($s_warmup['intro']); ?></textarea>
                </div>
                
                <label class="lb-label">Key Vocabulary</label>
                <div id="vocab-container">
                    <?php if(empty($s_warmup['vocab'])): ?>
                        <div class="lb-input-row">
                            <input type="text" name="vocab_word[]" class="lb-input" placeholder="Word (e.g. Synergize)">
                            <input type="text" name="vocab_def[]" class="lb-input" placeholder="Definition">
                            <button type="button" class="lb-icon-btn delete" onclick="removeRow(this)"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    <?php else: ?>
                        <?php foreach($s_warmup['vocab'] as $v): ?>
                        <div class="lb-input-row">
                            <input type="text" name="vocab_word[]" class="lb-input" value="<?php echo htmlspecialchars($v['word']); ?>">
                            <input type="text" name="vocab_def[]" class="lb-input" value="<?php echo htmlspecialchars($v['def']); ?>">
                            <button type="button" class="lb-icon-btn delete" onclick="removeRow(this)"><i class="fa-solid fa-trash"></i></button>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn-secondary btn-small" onclick="addVocab()">+ Add Word</button>
            </div>
        </div>

        <div class="lb-card" id="card-watch">
            <div class="lb-card-header" onclick="toggleCard('card-watch')">
                <div class="lb-header-left">
                    <span class="lb-badge badge-purple">2</span>
                    <h3 class="lb-title">Core Content (Watch/Read)</h3>
                </div>
                <i class="fa-solid fa-chevron-down lb-toggle-btn"></i>
            </div>
            <div class="lb-card-body">
                <div class="lb-form-group">
                    <label class="lb-label">Content Type</label>
                    <div class="lb-radio-group">
                        <label class="lb-radio"><input type="radio" name="media_type" value="video" onchange="updateMediaUI()" <?php echo ($s_watch['type']=='video')?'checked':''; ?>> Video</label>
                        <label class="lb-radio"><input type="radio" name="media_type" value="reading" onchange="updateMediaUI()" <?php echo ($s_watch['type']=='reading')?'checked':''; ?>> Reading/Article</label>
                    </div>
                </div>

                <div class="lb-form-group" id="group-media-url">
                    <label class="lb-label" id="label-media-url">Video URL (YouTube/Vimeo)</label>
                    <input type="text" name="media_url" class="lb-input" value="<?php echo htmlspecialchars($s_watch['url']); ?>" placeholder="https://...">
                </div>

                <div class="lb-form-group" id="group-media-text" class="hidden-group">
                    <label class="lb-label">Article Text / Reading Content</label>
                    <textarea name="media_text" class="lb-textarea lb-textarea-large"><?php echo htmlspecialchars($s_watch['text']??''); ?></textarea>
                </div>

                <div class="lb-form-group">
                    <label class="lb-label">Learning Objectives (Bulleted)</label>
                    <textarea name="objectives" class="lb-textarea lb-textarea-small" placeholder="- Student will learn..."><?php echo htmlspecialchars($s_watch['objectives']); ?></textarea>
                </div>
            </div>
        </div>

        <div class="lb-card" id="card-quiz">
            <div class="lb-card-header" onclick="toggleCard('card-quiz')">
                <div class="lb-header-left">
                    <span class="lb-badge badge-orange">3</span>
                    <h3 class="lb-title">Comprehension Quiz</h3>
                </div>
                <i class="fa-solid fa-chevron-down lb-toggle-btn"></i>
            </div>
            <div class="lb-card-body">
                <div id="quiz-container">
                    <?php 
                    $q_idx = 0;
                    $questions = $s_quiz['questions'] ?? [];
                    if(empty($questions)) $questions = [['title'=>'', 'options'=>['','',''], 'correct'=>0]];
                    ?>
                    
                    <?php foreach($questions as $q): ?>
                    <div class="lb-quiz-item">
                        <div class="lb-quiz-header">
                            <span class="lb-quiz-title">Question <?php echo $q_idx+1; ?></span>
                            <button type="button" class="lb-icon-btn delete" onclick="removeRow(this.closest('.lb-quiz-item'))"><i class="fa-solid fa-trash"></i></button>
                        </div>
                        <input type="text" name="questions[<?php echo $q_idx; ?>][title]" class="lb-input mb-3" value="<?php echo htmlspecialchars($q['title']); ?>" placeholder="Enter question here...">
                        
                        <?php 
                        $opts = $q['options'] ?? ['','',''];
                        foreach($opts as $oid => $oval): ?>
                        <div class="lb-option-row">
                            <input type="radio" name="questions[<?php echo $q_idx; ?>][correct]" value="<?php echo $oid; ?>" <?php echo ($q['correct']==$oid)?'checked':''; ?>>
                            <input type="text" name="questions[<?php echo $q_idx; ?>][options][]" class="lb-input" value="<?php echo htmlspecialchars($oval); ?>" placeholder="Option Answer">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php $q_idx++; endforeach; ?>
                </div>
                <button type="button" class="btn-secondary btn-small" onclick="addQuestion()">+ Add Question</button>
            </div>
        </div>

    </form>
</div>

<script>
    // Keep a counter for unique ID generation (for form names), 
    // separate from visual numbering
    let uniqueIdCounter = <?php echo ($q_idx > 0) ? $q_idx : 1; ?>;

    function toggleCard(id) { 
        document.getElementById(id).classList.toggle('collapsed'); 
    }

    function removeRow(btn) { 
        if(confirm('Remove this item?')) {
            // Check if this is a quiz item before we remove it
            const isQuizItem = btn.closest('.lb-quiz-item');
            
            // Remove the element
            btn.closest('.lb-input-row, .lb-quiz-item').remove(); 
            
            // If we just deleted a quiz question, re-calculate the numbers
            if (isQuizItem) {
                updateNumbers();
            }
        }
    }

    // Recalculates "Question 1", "Question 2", etc. based on screen order
    function updateNumbers() {
        const titles = document.querySelectorAll('.lb-quiz-item .lb-quiz-title');
        titles.forEach((el, index) => {
            el.innerText = `Question ${index + 1}`;
        });
    }

    function updateMediaUI() {
        const type = document.querySelector('input[name="media_type"]:checked').value;
        const urlDiv = document.getElementById('group-media-url');
        const txtDiv = document.getElementById('group-media-text');
        const lbl = document.getElementById('label-media-url');
        const inp = urlDiv.querySelector('input');

        if(type === 'reading') {
            lbl.textContent = "PDF/Doc Link"; inp.placeholder="https://file.pdf";
            txtDiv.style.display='block';
        } else if(type === 'audio') {
            lbl.textContent = "Audio Link"; inp.placeholder="https://audio.mp3";
            txtDiv.style.display='none';
        } else {
            lbl.textContent = "Video URL"; inp.placeholder="https://youtube.com...";
            txtDiv.style.display='none';
        }
    }

    function addVocab() {
        const d = document.createElement('div'); d.className='lb-input-row';
        d.innerHTML = `
            <input type="text" name="vocab_word[]" class="lb-input" placeholder="Word">
            <input type="text" name="vocab_def[]" class="lb-input" placeholder="Definition">
            <button type="button" class="lb-icon-btn delete" onclick="removeRow(this)"><i class="fa-solid fa-trash"></i></button>
        `;
        document.getElementById('vocab-container').appendChild(d);
    }

    function addQuestion() {
        uniqueIdCounter++;
        const d = document.createElement('div'); d.className='lb-quiz-item';
        
        // We set the title to just "Question" initially, then call updateNumbers() to fix it
        d.innerHTML=`
            <div class="lb-quiz-header">
                <span class="lb-quiz-title">Question</span>
                <button type="button" class="lb-icon-btn delete" onclick="removeRow(this)"><i class="fa-solid fa-trash"></i></button>
            </div>
            <input type="text" name="questions[${uniqueIdCounter}][title]" class="lb-input mb-3" placeholder="Enter question here...">
            
            <div class="lb-option-row">
                <input type="radio" name="questions[${uniqueIdCounter}][correct]" value="0" class="lb-radio-circle" checked>
                <input type="text" name="questions[${uniqueIdCounter}][options][]" class="lb-input" placeholder="Option A">
            </div>
            <div class="lb-option-row">
                <input type="radio" name="questions[${uniqueIdCounter}][correct]" value="1" class="lb-radio-circle">
                <input type="text" name="questions[${uniqueIdCounter}][options][]" class="lb-input" placeholder="Option B">
            </div>
            <div class="lb-option-row">
                <input type="radio" name="questions[${uniqueIdCounter}][correct]" value="2" class="lb-radio-circle">
                <input type="text" name="questions[${uniqueIdCounter}][options][]" class="lb-input" placeholder="Option C">
            </div>
        `;
        document.getElementById('quiz-container').appendChild(d);
        
        // Re-number everything immediately
        updateNumbers();
    }

    document.addEventListener("DOMContentLoaded", function() {
        updateMediaUI();
        updateNumbers(); // Ensure numbers are correct on initial load
    });
</script>