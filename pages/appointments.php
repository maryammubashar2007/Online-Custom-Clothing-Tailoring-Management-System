<?php
require_once '../includes/db.php';

// DELETE
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    sqlsrv_query($conn, "DELETE FROM appointment_requests WHERE appointment_id = ?", array((int)$_GET['delete']));
    header("Location: appointments.php?msg=deleted");
    exit;
}

// UPDATE STATUS
if (isset($_GET['status']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $allowed = array('pending','confirmed','completed','cancelled');
    if (in_array($_GET['status'], $allowed)) {
        sqlsrv_query($conn,
            "UPDATE appointment_requests SET status=?, updated_at=GETDATE() WHERE appointment_id=?",
            array($_GET['status'], (int)$_GET['id']));
        header("Location: appointments.php?msg=updated");
        exit;
    }
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status_filter']) ? trim($_GET['status_filter']) : '';
$house  = isset($_GET['house_filter'])  ? trim($_GET['house_filter'])  : '';

$where  = "WHERE 1=1";
$params = array();

if ($search !== '') {
    $where .= " AND (a.first_name LIKE ? OR a.last_name LIKE ? OR a.email LIKE ?)";
    $s = '%'.$search.'%';
    $params = array($s,$s,$s);
}
if ($status !== '') { $where .= " AND a.status = ?"; $params[] = $status; }
if ($house  !== '') { $where .= " AND a.house_id = ?"; $params[] = (int)$house; }

$query = "SELECT a.*, CONCAT(a.first_name,' ',a.last_name) AS cname, h.house_name
          FROM appointment_requests a
          LEFT JOIN houses h ON h.house_id = a.house_id
          $where ORDER BY a.preferred_date ASC";

$stmt   = sqlsrv_query($conn, $query, count($params) ? $params : array());
$houses = sqlsrv_query($conn, "SELECT house_id, house_name FROM houses ORDER BY display_order");

require_once '../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Appointments</h1>
        <p>Manage consultation bookings</p>
    </div>
    <a href="appointment_form.php" class="btn btn-gold">+ New Appointment</a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg']==='saved'):   ?><div class="alert alert-success">Appointment saved.</div><?php endif; ?>
    <?php if ($_GET['msg']==='updated'): ?><div class="alert alert-success">Appointment updated.</div><?php endif; ?>
    <?php if ($_GET['msg']==='deleted'): ?><div class="alert alert-error">Appointment deleted.</div><?php endif; ?>
<?php endif; ?>

<form method="GET" class="search-bar">
    <input type="text" name="search" placeholder="Search name or email..." value="<?= htmlspecialchars($search) ?>">
    <select name="status_filter">
        <option value="">All Status</option>
        <?php foreach (array('pending','confirmed','completed','cancelled') as $s): ?>
            <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="house_filter">
        <option value="">All Houses</option>
        <?php while ($h = sqlsrv_fetch_array($houses, SQLSRV_FETCH_ASSOC)): ?>
            <option value="<?= $h['house_id'] ?>" <?= $house==(string)$h['house_id']?'selected':'' ?>>
                <?= htmlspecialchars($h['house_name']) ?>
            </option>
        <?php endwhile; ?>
    </select>
    <button type="submit" class="btn btn-gold">Search</button>
    <a href="appointments.php" class="btn btn-outline">Reset</a>
</form>

<div class="section-card">
    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>#</th><th>Customer</th><th>Email</th><th>House</th>
                <th>Preferred Date</th><th>Status</th><th>Change Status</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $count = 0;
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)):
        $count++;
        ?>
            <tr>
                <td><?= $row['appointment_id'] ?></td>
                <td><strong><?= htmlspecialchars($row['cname']) ?></strong></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['house_name'] ?? '-') ?></td>
                <td><?= $row['preferred_date'] ? $row['preferred_date']->format('d M Y') : '-' ?></td>
                <td><span class="badge badge-<?= $row['status'] ?>"><?= $row['status'] ?></span></td>
                <td>
                    <div style="display:flex;gap:4px;flex-wrap:wrap;">
                    <?php foreach (array('confirmed','completed','cancelled') as $ns): ?>
                        <?php if ($row['status'] !== $ns): ?>
                        <a href="appointments.php?id=<?= $row['appointment_id'] ?>&status=<?= $ns ?>"
                           class="btn btn-outline btn-sm"><?= ucfirst($ns) ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </div>
                </td>
                <td style="display:flex;gap:6px;">
                    <a href="appointment_form.php?id=<?= $row['appointment_id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                    <a href="appointments.php?delete=<?= $row['appointment_id'] ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Delete this appointment?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        <?php if ($count===0): ?>
            <tr><td colspan="8" style="text-align:center;color:var(--grey4);padding:30px;">No appointments found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
