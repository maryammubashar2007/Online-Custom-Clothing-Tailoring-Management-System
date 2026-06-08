<?php
require_once '../includes/db.php';

// DELETE
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    sqlsrv_query($conn, "UPDATE products SET is_active=0 WHERE product_id=?", array((int)$_GET['delete']));
    header("Location: products.php?msg=deleted"); exit;
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$house  = isset($_GET['house_filter'])    ? trim($_GET['house_filter'])    : '';
$cat    = isset($_GET['category_filter']) ? trim($_GET['category_filter']) : '';

$where  = "WHERE p.is_active=1";
$params = array();

if ($search !== '') {
    $where .= " AND (p.product_name LIKE ? OR p.short_desc LIKE ?)";
    $s = '%'.$search.'%'; $params[] = $s; $params[] = $s;
}
if ($house !== '') { $where .= " AND p.house_id=?";    $params[] = (int)$house; }
if ($cat   !== '') { $where .= " AND p.category_id=?"; $params[] = (int)$cat;   }

$query = "SELECT p.*, h.house_name, c.category_name
          FROM products p
          JOIN houses     h ON h.house_id    = p.house_id
          JOIN categories c ON c.category_id = p.category_id
          $where ORDER BY h.display_order, c.display_order, p.display_order";

$stmt   = sqlsrv_query($conn, $query, count($params) ? $params : array());
$houses = sqlsrv_query($conn, "SELECT house_id, house_name FROM houses ORDER BY display_order");
$house_list = array();
while ($h = sqlsrv_fetch_array($houses, SQLSRV_FETCH_ASSOC)) { $house_list[] = $h; }

$cats = sqlsrv_query($conn, "SELECT c.category_id, c.category_name, h.house_name FROM categories c JOIN houses h ON h.house_id=c.house_id ORDER BY h.display_order, c.display_order");
$cat_list = array();
while ($c = sqlsrv_fetch_array($cats, SQLSRV_FETCH_ASSOC)) { $cat_list[] = $c; }

require_once '../includes/header.php';
?>

<div class="page-header">
    <div><h1>Products</h1><p>Bespoke product catalogue</p></div>
    <a href="product_form.php" class="btn btn-gold">+ Add Product</a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg']==='saved'):   ?><div class="alert alert-success">Product saved.</div><?php endif; ?>
    <?php if ($_GET['msg']==='updated'): ?><div class="alert alert-success">Product updated.</div><?php endif; ?>
    <?php if ($_GET['msg']==='deleted'): ?><div class="alert alert-error">Product deactivated.</div><?php endif; ?>
<?php endif; ?>

<form method="GET" class="search-bar">
    <input type="text" name="search" placeholder="Search product name..." value="<?= htmlspecialchars($search) ?>">
    <select name="house_filter">
        <option value="">All Houses</option>
        <?php foreach ($house_list as $h): ?>
            <option value="<?= $h['house_id'] ?>" <?= $house==(string)$h['house_id']?'selected':'' ?>>
                <?= htmlspecialchars($h['house_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <select name="category_filter">
        <option value="">All Categories</option>
        <?php foreach ($cat_list as $c): ?>
            <option value="<?= $c['category_id'] ?>" <?= $cat==(string)$c['category_id']?'selected':'' ?>>
                <?= htmlspecialchars($c['house_name'].' - '.$c['category_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-gold">Search</button>
    <a href="products.php" class="btn btn-outline">Reset</a>
</form>

<div class="section-card">
    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>#</th><th>Product Name</th><th>House</th><th>Category</th>
                <th>Price Min</th><th>Price Max</th><th>Lead Time</th><th>Featured</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $count = 0;
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)):
        $count++;
        ?>
            <tr>
                <td><?= $row['product_id'] ?></td>
                <td><strong><?= htmlspecialchars($row['product_name']) ?></strong><br>
                    <span style="color:var(--grey4);font-size:11px;"><?= htmlspecialchars(substr($row['short_desc']??'',0,50)) ?>...</span>
                </td>
                <td><?= htmlspecialchars($row['house_name']) ?></td>
                <td><?= htmlspecialchars($row['category_name']) ?></td>
                <td><?= number_format($row['price_min']) ?></td>
                <td><?= number_format($row['price_max']) ?></td>
                <td><?= htmlspecialchars($row['lead_time_weeks'] ?? '-') ?></td>
                <td><?= $row['is_featured'] ? '<span class="badge badge-confirmed">Yes</span>' : '<span style="color:var(--grey4)">No</span>' ?></td>
                <td style="display:flex;gap:6px;">
                    <a href="product_form.php?id=<?= $row['product_id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                    <a href="products.php?delete=<?= $row['product_id'] ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Deactivate this product?')">Remove</a>
                </td>
            </tr>
        <?php endwhile; ?>
        <?php if ($count===0): ?>
            <tr><td colspan="9" style="text-align:center;color:var(--grey4);padding:30px;">No products found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
