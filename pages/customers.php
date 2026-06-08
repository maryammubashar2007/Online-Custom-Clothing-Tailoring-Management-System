<?php
require_once '../includes/db.php';

// DELETE
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $sql = "DELETE FROM customers WHERE customer_id = ?";
    $stmt = sqlsrv_query($conn, $sql, array($id));
    if ($stmt) {
        header("Location: customers.php?msg=deleted");
        exit;
    }
}

// SEARCH
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$gender = isset($_GET['gender']) ? trim($_GET['gender']) : '';

$where = "WHERE 1=1";
$params = array();

if ($search !== '') {
    $where .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR city LIKE ?)";
    $s = '%' . $search . '%';
    $params = array($s, $s, $s, $s);
}
if ($gender !== '') {
    $where .= " AND gender = ?";
    $params[] = $gender;
}

$query = "SELECT * FROM customers $where ORDER BY created_at DESC";
$stmt  = sqlsrv_query($conn, $query, count($params) ? $params : array());

require_once '../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Customers</h1>
        <p>Manage all registered clients</p>
    </div>
    <a href="customer_form.php" class="btn btn-gold">+ Add Customer</a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] === 'saved'):   ?><div class="alert alert-success">Customer saved successfully.</div><?php endif; ?>
    <?php if ($_GET['msg'] === 'updated'): ?><div class="alert alert-success">Customer updated successfully.</div><?php endif; ?>
    <?php if ($_GET['msg'] === 'deleted'): ?><div class="alert alert-error">Customer deleted.</div><?php endif; ?>
<?php endif; ?>

<form method="GET" class="search-bar">
    <input type="text" name="search" placeholder="Search name, email, city..." value="<?= htmlspecialchars($search) ?>">
    <select name="gender">
        <option value="">All Genders</option>
        <option value="Male"   <?= $gender==='Male'   ? 'selected':'' ?>>Male</option>
        <option value="Female" <?= $gender==='Female' ? 'selected':'' ?>>Female</option>
        <option value="Other"  <?= $gender==='Other'  ? 'selected':'' ?>>Other</option>
    </select>
    <button type="submit" class="btn btn-gold">Search</button>
    <a href="customers.php" class="btn btn-outline">Reset</a>
</form>

<div class="section-card">
    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>#</th><th>Name</th><th>Email</th><th>Phone</th>
                <th>City</th><th>Gender</th><th>Joined</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $count = 0;
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)):
        $count++;
        ?>
            <tr>
                <td><?= $row['customer_id'] ?></td>
                <td><strong><?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?></strong></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['phone'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['city'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['gender'] ?? '-') ?></td>
                <td><?= $row['created_at']->format('d M Y') ?></td>
                <td style="display:flex;gap:6px;flex-wrap:wrap;">
                    <a href="customer_form.php?id=<?= $row['customer_id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                    <a href="customers.php?delete=<?= $row['customer_id'] ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Delete this customer?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        <?php if ($count === 0): ?>
            <tr><td colspan="8" style="text-align:center;color:var(--grey4);padding:30px;">No customers found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
