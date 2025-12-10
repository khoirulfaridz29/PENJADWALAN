<?php
// ================= KONEKSI DB =================
$pdo = new PDO("mysql:host=localhost;dbname=db_penjadwalan_simple","root","");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ================= HELPER =================
$page = $_GET['page'] ?? 'teachers';
function getData($pdo,$table){
    return $pdo->query("SELECT * FROM $table")->fetchAll(PDO::FETCH_ASSOC);
}

/* ================= HANDLE INSERT ================= */
if(isset($_POST['action'])){
    switch($_POST['action']){
        case "teacher":
            $pdo->prepare("INSERT INTO teachers(name,code,min_hours,max_hours) VALUES (?,?,?,?)")
                ->execute([$_POST['name'],$_POST['code'],$_POST['min'],$_POST['max']]);
            break;
        case "class":
            $pdo->prepare("INSERT INTO classes(name) VALUES (?)")->execute([$_POST['name']]);
            break;
        case "subject":
            $pdo->prepare("INSERT INTO subjects(name,weekly_hours) VALUES (?,?)")
                ->execute([$_POST['name'],$_POST['hours']]);
            break;
        case "room":
            $pdo->prepare("INSERT INTO rooms(name) VALUES (?)")->execute([$_POST['name']]);
            break;
        case "timeslot":
            $pdo->prepare(
                "INSERT INTO timeslots(day,slot_order,start_time,end_time) VALUES (?,?,?,?)"
            )->execute([$_POST['day'],$_POST['order'],$_POST['start'],$_POST['end']]);
            break;
        case "teacher_subject":
            $pdo->prepare(
                "INSERT IGNORE INTO teacher_subject(teacher_id,subject_id) VALUES (?,?)"
            )->execute([$_POST['teacher_id'],$_POST['subject_id']]);
            break;
    }
    header("Location: index.php?page=".$page); exit;
}

/* ================= DELETE ================= */
if(isset($_GET['del'])){
    $pdo->prepare("DELETE FROM {$_GET['tbl']} WHERE id=?")->execute([$_GET['del']]);
    header("Location: index.php?page=".$page); exit;
}

/* ================= DATA ================= */
$teachers  = getData($pdo,'teachers');
$classes   = getData($pdo,'classes');
$subjects  = getData($pdo,'subjects');
$rooms     = getData($pdo,'rooms');
$timeslots = getData($pdo,'timeslots');
$teacher_subject = $pdo->query("
    SELECT ts.*, t.name teacher_name, s.name subject_name 
    FROM teacher_subject ts 
    JOIN teachers t ON t.id=ts.teacher_id 
    JOIN subjects s ON s.id=ts.subject_id
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Dashboard Penjadwalan GA</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background:#f4f7fb;
    font-size:14px;
}
.sidebar{
    background:linear-gradient(180deg,#0d6efd,#0b5ed7);
    color:white;
}
.sidebar a{
    color:white;
    text-decoration:none;
    padding:10px 15px;
    border-radius:8px;
    display:block;
    transition:.2s;
}
.sidebar a:hover{
    background:rgba(255,255,255,.2);
}
.card{
    border:none;
    border-radius:14px;
    box-shadow:0 6px 20px rgba(0,0,0,.06);
}
.table{
    background:white;
    border-radius:12px;
    overflow:hidden;
}
.table th{
    background:#eef3ff;
    font-weight:600;
}
.btn{
    border-radius:10px;
    box-shadow:0 3px 8px rgba(0,0,0,.1);
}
</style>
</head>

<body>
<div class="container-fluid">
<div class="row">

<!-- SIDEBAR -->
<div class="col-2 sidebar min-vh-100 p-3">
    <h5 class="fw-bold mb-4">📘 GA Scheduler</h5>
    <a href="?page=teachers">👨‍🏫 Guru</a>
    <a href="?page=classes">🏫 Kelas</a>
    <a href="?page=subjects">📚 Mapel</a>
    <a href="?page=rooms">🏢 Ruang</a>
    <a href="?page=timeslots">⏰ Timeslot</a>
    <hr>
    <a href="?action=run-ga" class="bg-warning text-dark fw-semibold">▶ Jalankan GA</a>
    <a href="?page=show_schedule">📊 Lihat Jadwal</a>
</div>

<!-- CONTENT -->
<div class="col-10 p-4">

<?php
function cardStart($title){
    echo "<div class='card mb-4'><div class='card-body'>";
    echo "<h5 class='mb-3 fw-semibold'>$title</h5>";
}
function cardEnd(){ echo "</div></div>"; }
?>

<!-- GURU -->
<?php if($page=="teachers"): cardStart("Data Guru"); ?>
<form method="post" class="row g-2 mb-3">
<input type="hidden" name="action" value="teacher">
<input class="col form-control" name="name" placeholder="Nama Guru" required>
<input class="col form-control" name="code" placeholder="Kode">
<input class="col form-control" name="min" type="number" placeholder="Min Jam">
<input class="col form-control" name="max" type="number" placeholder="Max Jam">
<button class="col btn btn-primary">Tambah</button>
</form>

<table class="table table-sm">
<tr><th>Kode</th><th>Nama</th><th>Min</th><th>Max</th><th></th></tr>
<?php foreach($teachers as $t): ?>
<tr>
<td><?= $t['code'] ?></td>
<td><?= $t['name'] ?></td>
<td><?= $t['min_hours'] ?></td>
<td><?= $t['max_hours'] ?></td>
<td>
<a class="btn btn-sm btn-danger" href="?page=teachers&tbl=teachers&del=<?=$t['id']?>">Hapus</a>
</td>
</tr>
<?php endforeach ?>
</table>
<?php cardEnd(); endif ?>

<!-- KELAS -->
<?php if($page=="classes"): cardStart("Data Kelas"); ?>
<form method="post" class="row g-2 mb-3">
<input type="hidden" name="action" value="class">
<input class="col-8 form-control" name="name" placeholder="Nama Kelas" required>
<button class="col btn btn-primary">Tambah</button>
</form>
<table class="table table-sm">
<?php foreach($classes as $c): ?>
<tr>
<td><?= $c['name'] ?></td>
<td width="120">
<a class="btn btn-sm btn-danger" href="?page=classes&tbl=classes&del=<?=$c['id']?>">Hapus</a>
</td>
</tr>
<?php endforeach ?>
</table>
<?php cardEnd(); endif ?>

<!-- MAPEL -->
<?php if($page=="subjects"): cardStart("Data Mapel & Kompetensi"); ?>
<form method="post" class="row g-2 mb-3">
<input type="hidden" name="action" value="subject">
<input class="col form-control" name="name" placeholder="Mapel" required>
<input class="col form-control" name="hours" type="number" placeholder="Jam / Minggu">
<button class="col btn btn-primary">Tambah</button>
</form>

<table class="table table-sm mb-4">
<?php foreach($subjects as $s): ?>
<tr>
<td><?= $s['name'] ?></td>
<td><?= $s['weekly_hours'] ?> jam</td>
<td width="120">
<a class="btn btn-sm btn-danger" href="?page=subjects&tbl=subjects&del=<?=$s['id']?>">Hapus</a>
</td>
</tr>
<?php endforeach ?>
</table>

<h6>Kompetensi Guru</h6>
<form method="post" class="row g-2 mb-3">
<input type="hidden" name="action" value="teacher_subject">
<select class="col form-control" name="teacher_id">
<?php foreach($teachers as $t) echo "<option value='{$t['id']}'>{$t['name']}</option>"; ?>
</select>
<select class="col form-control" name="subject_id">
<?php foreach($subjects as $s) echo "<option value='{$s['id']}'>{$s['name']}</option>"; ?>
</select>
<button class="col btn btn-secondary">Tambah</button>
</form>
<?php cardEnd(); endif ?>

<!-- RUANG -->
<?php if($page=="rooms"): cardStart("Data Ruang"); ?>
<form method="post" class="row g-2 mb-3">
<input type="hidden" name="action" value="room">
<input class="col form-control" name="name" placeholder="Nama Ruang" required>
<button class="col btn btn-primary">Tambah</button>
</form>
<?php cardEnd(); endif ?>

<!-- TIMESLOT -->
<?php if($page=="timeslots"): cardStart("Data Timeslot"); ?>
<form method="post" class="row g-2 mb-3">
<input type="hidden" name="action" value="timeslot">
<select class="col form-control" name="day">
<option>Senin</option><option>Selasa</option><option>Rabu</option>
<option>Kamis</option><option>Jumat</option><option>Sabtu</option>
</select>
<input class="col form-control" name="order" type="number" placeholder="Slot">
<input class="col form-control" name="start" type="time">
<input class="col form-control" name="end" type="time">
<button class="col btn btn-primary">Tambah</button>
</form>
<?php cardEnd(); endif ?>

</div>
</div>
</div>
</body>
</html>
