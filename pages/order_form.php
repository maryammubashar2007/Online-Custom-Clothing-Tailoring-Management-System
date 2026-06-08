<?php
require_once '../includes/db.php';

$errors = array();
$data   = array('customer_id'=>'','house_id'=>'','order_date'=>date('Y-m-d'),
                'expected_ready'=>'','total_amount'=>'','special_notes'=>'',
                'order_status'=>'enquiry','payment_status'=>'unpaid');

$customers = sqlsrv_query($conn, "SELECT customer_id, CONCAT(first_name,' ',last_name) AS cname FROM customers ORDER BY first_name");
$cust_list = array();
while ($c = sqlsrv_fetch_array($customers, SQLSRV_FETCH_ASSOC)) { $cust_list[] = $c; }

$houses = sqlsrv_query($conn, "SELECT house_id, house_name FROM houses ORDER BY display_order");
$house_list = array();
while ($h = sqlsrv_fetch_array($houses, SQLSRV_FETCH_ASSOC)) { $house_list[] = $h; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = array(
        'customer_id'    => trim($_POST['customer_id']    ?? ''),
        'house_id'       => trim($_POST['house_id']       ?? ''),
        'order_date'     => trim($_POST['order_date']     ?? ''),
        'expected_ready' => trim($_POST['expected_ready'] ?? ''),
        'total_amount'   => trim($_POST['total_amount']   ?? ''),
        'special_notes'  => trim($_POST['special_notes']  ?? ''),
        'order_status'   => trim($_POST['order_status']   ?? 'enquiry'),
        'payment_status' => trim($_POST['payment_status'] ?? 'unpaid'),
    );

    if ($data['customer_id'] === '') $errors[] = 'Customer is required.';
    if ($data['order_date']  === '') $errors[] = 'Order date is required.';

    if (empty($errors)) {
        $hid    = $data['house_id']       !== '' ? (int)$data['house_id'] : null;
        $eready = $data['expected_ready'] !== '' ? $data['expected_ready'] : null;
        $amt    = $data['total_amount']   !== '' ? (float)$data['total_amount'] : null;

        $sql = "INSERT INTO orders (customer_id,house_id,order_status,order_date,expected_ready,total_amount,payment_status,special_notes)
                VALUES (?,?,?,?,?,?,?,?)";
        sqlsrv_query($conn, $sql, array(
            (int)$data['customer_id'], $hid, $data['order_status'],
            $data['order_date'], $eready, $amt,
            $data['payment_status'], $data['special_notes']
        ));
        header("Location: orders.php?msg=saved"); exit;
    }
}

require_once '../includes/header.php';
?>

<div class="page-header">
    <div><h1>New Order</h1><p>Create a bespoke order</p></div>
    <a href="orders.php" class="btn btn-outline">Back</a>
</div>

<?php foreach ($errors as $e): ?>
    <div class="alert alert-error"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<div class="form-card">
    <form method="POST">
        <div class="form-grid">
            <div class="form-group">
                <label>Customer *</label>
                <select name="customer_id" required>
                    <option value="">Select Customer</option>
                    <?php foreach ($cust_list as $c): ?>
                        <option value="<?= $c['customer_id'] ?>" <?= ($data['customer_id']??'')==$c['customer_id']?'selected':'' ?>>
                            <?= htmlspecialchars($c['cname']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>House</label>
                <select name="house_id">
                    <option value="">Select House</option>
                    <?php foreach ($house_list as $h): ?>
                        <option value="<?= $h['house_id'] ?>" <?= ($data['house_id']??'')==$h['house_id']?'selected':'' ?>>
                            <?= htmlspecialchars($h['house_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Order Date *</label>
                <input type="date" name="order_date" value="<?= htmlspecialchars($data['order_date']) ?>" required>
            </div>
            <div class="form-group">
                <label>Expected Ready Date</label>
                <input type="date" name="expected_ready" value="<?= htmlspecialchars($data['expected_ready'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Total Amount (PKR)</label>
                <input type="number" name="total_amount" value="<?= htmlspecialchars($data['total_amount'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Order Status</label>
                <select name="order_status">
                    <?php foreach (array('enquiry','in_design','sampling','fittings','ready','delivered','cancelled') as $s): ?>
                        <option value="<?= $s ?>" <?= ($data['order_status']??'enquiry')===$s?'selected':'' ?>>
                            <?= ucfirst(str_replace('_',' ',$s)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Payment Status</label>
                <select name="payment_status">
                    <?php foreach (array('unpaid','partial','paid') as $s): ?>
                        <option value="<?= $s ?>" <?= ($data['payment_status']??'unpaid')===$s?'selected':'' ?>>
                            <?= ucfirst($s) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group full">
                <label>Special Notes</label>
                <textarea name="special_notes"><?= htmlspecialchars($data['special_notes'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-gold">Create Order</button>
            <a href="orders.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
