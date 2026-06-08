<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$total_customers    = sqlsrv_fetch_array(sqlsrv_query($conn, "SELECT COUNT(*) AS c FROM customers"), SQLSRV_FETCH_ASSOC)['c'];
$total_products     = sqlsrv_fetch_array(sqlsrv_query($conn, "SELECT COUNT(*) AS c FROM products WHERE is_active=1"), SQLSRV_FETCH_ASSOC)['c'];
$total_appointments = sqlsrv_fetch_array(sqlsrv_query($conn, "SELECT COUNT(*) AS c FROM appointment_requests"), SQLSRV_FETCH_ASSOC)['c'];
$total_orders       = sqlsrv_fetch_array(sqlsrv_query($conn, "SELECT COUNT(*) AS c FROM orders"), SQLSRV_FETCH_ASSOC)['c'];
$revenue            = sqlsrv_fetch_array(sqlsrv_query($conn, "SELECT ISNULL(SUM(total_amount),0) AS r FROM orders WHERE payment_status IN ('partial','paid')"), SQLSRV_FETCH_ASSOC)['r'];
$pending_apts       = sqlsrv_fetch_array(sqlsrv_query($conn, "SELECT COUNT(*) AS c FROM appointment_requests WHERE status='pending'"), SQLSRV_FETCH_ASSOC)['c'];

$recent_orders = sqlsrv_query($conn,
    "SELECT TOP 8 o.order_id, CONCAT(c.first_name,' ',c.last_name) AS cname,
            h.house_name, o.order_status, o.total_amount, o.payment_status, o.order_date
     FROM orders o
     JOIN customers c ON c.customer_id = o.customer_id
     LEFT JOIN houses h ON h.house_id = o.house_id
     ORDER BY o.created_at DESC");

$recent_apts = sqlsrv_query($conn,
    "SELECT TOP 6 CONCAT(first_name,' ',last_name) AS cname,
            preferred_date, status, source_page
     FROM appointment_requests
     ORDER BY created_at DESC");
?>

<div class="page-header">
    <div>
        <h1>Dashboard</h1>
        <p>Velvara Couture Atelier &mdash; Admin Panel</p>
    </div>
    <span style="color:var(--grey4);font-size:12px;"><?= date('l, d F Y') ?></span>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="label">Customers</div>
        <div class="value"><?= $total_customers ?></div>
        <div class="sub">Registered clients</div>
    </div>
    <div class="stat-card">
        <div class="label">Products</div>
        <div class="value"><?= $total_products ?></div>
        <div class="sub">Active catalogue items</div>
    </div>
    <div class="stat-card">
        <div class="label">Appointments</div>
        <div class="value"><?= $total_appointments ?></div>
        <div class="sub"><?= $pending_apts ?> pending</div>
    </div>
    <div class="stat-card">
        <div class="label">Revenue (PKR)</div>
        <div class="value"><?= number_format($revenue/1000) ?>K</div>
        <div class="sub"><?= $total_orders ?> total orders</div>
    </div>
</div>

<div class="section-card">
    <h2>Recent Orders</h2>
    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>#</th><th>Customer</th><th>House</th>
                <th>Status</th><th>Amount (PKR)</th><th>Payment</th><th>Date</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = sqlsrv_fetch_array($recent_orders, SQLSRV_FETCH_ASSOC)): ?>
            <tr>
                <td><?= $row['order_id'] ?></td>
                <td><strong><?= htmlspecialchars($row['cname']) ?></strong></td>
                <td><?= htmlspecialchars($row['house_name'] ?? '-') ?></td>
                <td><span class="badge badge-<?= $row['order_status'] ?>"><?= $row['order_status'] ?></span></td>
                <td><?= number_format($row['total_amount']) ?></td>
                <td><span class="badge badge-<?= $row['payment_status'] ?>"><?= $row['payment_status'] ?></span></td>
                <td><?= $row['order_date']->format('d M Y') ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>

<div class="section-card">
    <h2>Recent Appointments</h2>
    <div class="table-wrap">
    <table>
        <thead>
            <tr><th>Customer</th><th>Preferred Date</th><th>Status</th><th>Source</th></tr>
        </thead>
        <tbody>
        <?php while($row = sqlsrv_fetch_array($recent_apts, SQLSRV_FETCH_ASSOC)): ?>
            <tr>
                <td><strong><?= htmlspecialchars($row['cname']) ?></strong></td>
                <td><?= $row['preferred_date'] ? $row['preferred_date']->format('d M Y') : '-' ?></td>
                <td><span class="badge badge-<?= $row['status'] ?>"><?= $row['status'] ?></span></td>
                <td><?= htmlspecialchars($row['source_page'] ?? '-') ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
