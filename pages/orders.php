<?php
require_once '../includes/db.php';

// UPDATE STATUS
if (isset($_POST['update_status']) && is_numeric($_POST['order_id'])) {
    $allowed_status  = array('enquiry','in_design','sampling','fittings','ready','delivered','cancelled');
    $allowed_payment = array('unpaid','partial','paid');
    $oid = (int)$_POST['order_id'];
    $os  = $_POST['order_status']   ?? '';
    $ps  = $_POST['payment_status'] ?? '';
    if (in_array($os,$allowed_status) && in_array($ps,$allowed_payment)) {
        sqlsrv_query($conn,
            "UPDATE orders SET order_status=?, payment_status=?, updated_at=GETDATE() WHERE order_id=?",
            array($os,$ps,$oid));
        header("Location: orders.php?msg=updated"); exit;
    }
}

// DELETE
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    sqlsrv_query($conn, "DELETE FROM orders WHERE order_id=?", array((int)$_GET['delete']));
    header("Location: orders.php?msg=deleted"); exit;
}

$search  = isset($_GET['search'])  ? trim($_GET['search'])  : '';
$ostatus = isset($_GET['ostatus']) ? trim($_GET['ostatus']) : '';
$pstatus = isset($_GET['pstatus']) ? trim($_GET['pstatus']) : '';

$where  = "WHERE 1=1";
$params = array();

if ($search  !== '') {
    $where .= " AND (c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ?)";
    $s = '%'.$search.'%'; $params[] = $s; $params[] = $s; $params[] = $s;
}
if ($ostatus !== '') { $where .= " AND o.order_status=?";   $params[] = $ostatus; }
if ($pstatus !== '') { $where .= " AND o.payment_status=?"; $params[] = $pstatus; }

$query = "SELECT o.*, CONCAT(c.first_name,' ',c.last_name) AS cname, c.email,
                 h.house_name
          FROM orders o
          JOIN customers c ON c.customer_id = o.customer_id
          LEFT JOIN houses h ON h.house_id  = o.house_id
          $where ORDER BY o.created_at DESC";

$stmt = sqlsrv_query($conn, $query, count($params) ? $params : array());

require_once '../includes/header.php';
?>

<div class="page-header">
    <div><h1>Orders</h1><p>Full order lifecycle management</p></div>
    <a href="order_form.php" class="btn btn-gold">+ New Order</a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg']==='updated'): ?><div class="alert alert-success">Order updated.</div><?php endif; ?>
    <?php if ($_GET['msg']==='deleted'): ?><div class="alert alert-error">Order deleted.</div><?php endif; ?>
    <?php if ($_GET['msg']==='saved'):   ?><div class="alert alert-success">Order created.</div><?php endif; ?>
<?php endif; ?>

<form method="GET" class="search-bar">
    <input type="text" name="search" placeholder="Search customer name or email..." value="<?= htmlspecialchars($search) ?>">
    <select name="ostatus">
        <option value="">All Order Status</option>
        <?php foreach (array('enquiry','in_design','sampling','fittings','ready','delivered','cancelled') as $s): ?>
            <option value="<?= $s ?>" <?= $ostatus===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="pstatus">
        <option value="">All Payment</option>
        <?php foreach (array('unpaid','partial','paid') as $s): ?>
            <option value="<?= $s ?>" <?= $pstatus===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-gold">Search</button>
    <a href="orders.php" class="btn btn-outline">Reset</a>
</form>

<div class="section-card">
    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>#</th><th>Customer</th><th>House</th><th>Order Date</th>
                <th>Expected Ready</th><th>Amount (PKR)</th>
                <th>Order Status</th><th>Payment</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $count = 0;
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)):
        $count++;
        ?>
            <tr>
                <td><?= $row['order_id'] ?></td>
                <td>
                    <strong><?= htmlspecialchars($row['cname']) ?></strong><br>
                    <span style="color:var(--grey4);font-size:11px;"><?= htmlspecialchars($row['email']) ?></span>
                </td>
                <td><?= htmlspecialchars($row['house_name'] ?? '-') ?></td>
                <td><?= $row['order_date'] ? $row['order_date']->format('d M Y') : '-' ?></td>
                <td><?= $row['expected_ready'] ? $row['expected_ready']->format('d M Y') : '-' ?></td>
                <td><?= $row['total_amount'] ? number_format($row['total_amount']) : '-' ?></td>
                <td><span class="badge badge-<?= $row['order_status'] ?>"><?= str_replace('_',' ',$row['order_status']) ?></span></td>
                <td><span class="badge badge-<?= $row['payment_status'] ?>"><?= $row['payment_status'] ?></span></td>
                <td>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                        <button onclick="document.getElementById('modal-<?= $row['order_id'] ?>').style.display='flex'"
                                class="btn btn-outline btn-sm">Update</button>
                        <a href="orders.php?delete=<?= $row['order_id'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete this order?')">Delete</a>
                    </div>
                    <div id="modal-<?= $row['order_id'] ?>"
                         style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;
                                background:rgba(0,0,0,0.8);z-index:999;align-items:center;justify-content:center;">
                        <div style="background:var(--grey1);border:1px solid var(--gold);padding:28px;border-radius:4px;min-width:340px;">
                            <h3 style="font-family:var(--font-display);color:var(--gold);margin-bottom:20px;">Update Order #<?= $row['order_id'] ?></h3>
                            <form method="POST">
                                <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                                <input type="hidden" name="update_status" value="1">
                                <div class="form-group" style="margin-bottom:14px;">
                                    <label>Order Status</label>
                                    <select name="order_status">
                                        <?php foreach (array('enquiry','in_design','sampling','fittings','ready','delivered','cancelled') as $s): ?>
                                            <option value="<?= $s ?>" <?= $row['order_status']===$s?'selected':'' ?>>
                                                <?= ucfirst(str_replace('_',' ',$s)) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group" style="margin-bottom:20px;">
                                    <label>Payment Status</label>
                                    <select name="payment_status">
                                        <?php foreach (array('unpaid','partial','paid') as $s): ?>
                                            <option value="<?= $s ?>" <?= $row['payment_status']===$s?'selected':'' ?>>
                                                <?= ucfirst($s) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div style="display:flex;gap:10px;">
                                    <button type="submit" class="btn btn-gold">Save</button>
                                    <button type="button" class="btn btn-outline"
                                            onclick="document.getElementById('modal-<?= $row['order_id'] ?>').style.display='none'">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>
        <?php endwhile; ?>
        <?php if ($count===0): ?>
            <tr><td colspan="9" style="text-align:center;color:var(--grey4);padding:30px;">No orders found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
