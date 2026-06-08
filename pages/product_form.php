<?php
require_once '../includes/db.php';

$id      = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$editing = $id > 0;
$data    = array('house_id'=>'','category_id'=>'','product_name'=>'','short_desc'=>'',
                 'price_min'=>'','price_max'=>'','lead_time_weeks'=>'','is_featured'=>0);
$errors  = array();

$houses = sqlsrv_query($conn, "SELECT house_id, house_name FROM houses ORDER BY display_order");
$house_list = array();
while ($h = sqlsrv_fetch_array($houses, SQLSRV_FETCH_ASSOC)) { $house_list[] = $h; }

$cats = sqlsrv_query($conn, "SELECT c.category_id, c.category_name, h.house_name FROM categories c JOIN houses h ON h.house_id=c.house_id ORDER BY h.display_order, c.display_order");
$cat_list = array();
while ($c = sqlsrv_fetch_array($cats, SQLSRV_FETCH_ASSOC)) { $cat_list[] = $c; }

if ($editing) {
    $stmt = sqlsrv_query($conn, "SELECT * FROM products WHERE product_id=?", array($id));
    if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { $data = $row; }
    else { header("Location: products.php"); exit; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = array(
        'house_id'        => trim($_POST['house_id']        ?? ''),
        'category_id'     => trim($_POST['category_id']     ?? ''),
        'product_name'    => trim($_POST['product_name']    ?? ''),
        'short_desc'      => trim($_POST['short_desc']      ?? ''),
        'price_min'       => trim($_POST['price_min']       ?? ''),
        'price_max'       => trim($_POST['price_max']       ?? ''),
        'lead_time_weeks' => trim($_POST['lead_time_weeks'] ?? ''),
        'is_featured'     => isset($_POST['is_featured']) ? 1 : 0,
    );

    if ($data['product_name'] === '') $errors[] = 'Product name is required.';
    if ($data['house_id']     === '') $errors[] = 'House is required.';
    if ($data['category_id']  === '') $errors[] = 'Category is required.';
    if ($data['price_min']    === '') $errors[] = 'Minimum price is required.';
    if ($data['price_max']    === '') $errors[] = 'Maximum price is required.';

    if (empty($errors)) {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $data['product_name'])).'-'.time();

        if ($editing) {
            $sql = "UPDATE products SET
                        house_id=?,category_id=?,product_name=?,short_desc=?,
                        price_min=?,price_max=?,lead_time_weeks=?,is_featured=?,updated_at=GETDATE()
                    WHERE product_id=?";
            sqlsrv_query($conn, $sql, array(
                (int)$data['house_id'],(int)$data['category_id'],
                $data['product_name'],$data['short_desc'],
                (float)$data['price_min'],(float)$data['price_max'],
                $data['lead_time_weeks'],$data['is_featured'],$id
            ));
            header("Location: products.php?msg=updated"); exit;
        } else {
            $sql = "INSERT INTO products (house_id,category_id,product_slug,product_name,short_desc,price_min,price_max,lead_time_weeks,is_featured)
                    VALUES (?,?,?,?,?,?,?,?,?)";
            sqlsrv_query($conn, $sql, array(
                (int)$data['house_id'],(int)$data['category_id'],$slug,
                $data['product_name'],$data['short_desc'],
                (float)$data['price_min'],(float)$data['price_max'],
                $data['lead_time_weeks'],$data['is_featured']
            ));
            header("Location: products.php?msg=saved"); exit;
        }
    }
}

require_once '../includes/header.php';
?>

<div class="page-header">
    <div><h1><?= $editing ? 'Edit Product' : 'Add Product' ?></h1></div>
    <a href="products.php" class="btn btn-outline">Back</a>
</div>

<?php foreach ($errors as $e): ?>
    <div class="alert alert-error"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<div class="form-card">
    <form method="POST">
        <div class="form-grid">
            <div class="form-group">
                <label>House *</label>
                <select name="house_id" required>
                    <option value="">Select House</option>
                    <?php foreach ($house_list as $h): ?>
                        <option value="<?= $h['house_id'] ?>" <?= ($data['house_id']??'')==$h['house_id']?'selected':'' ?>>
                            <?= htmlspecialchars($h['house_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Category *</label>
                <select name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($cat_list as $c): ?>
                        <option value="<?= $c['category_id'] ?>" <?= ($data['category_id']??'')==$c['category_id']?'selected':'' ?>>
                            <?= htmlspecialchars($c['house_name'].' - '.$c['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group full">
                <label>Product Name *</label>
                <input type="text" name="product_name" value="<?= htmlspecialchars($data['product_name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Min Price (PKR) *</label>
                <input type="number" name="price_min" value="<?= htmlspecialchars($data['price_min'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Max Price (PKR) *</label>
                <input type="number" name="price_max" value="<?= htmlspecialchars($data['price_max'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Lead Time</label>
                <input type="text" name="lead_time_weeks" placeholder="e.g. 6 - 10 weeks"
                       value="<?= htmlspecialchars($data['lead_time_weeks'] ?? '') ?>">
            </div>
            <div class="form-group" style="justify-content:flex-end;padding-top:22px;">
                <label style="flex-direction:row;align-items:center;gap:10px;cursor:pointer;">
                    <input type="checkbox" name="is_featured" value="1"
                           <?= ($data['is_featured']??0) ? 'checked' : '' ?>
                           style="width:16px;height:16px;accent-color:var(--gold);">
                    Mark as Featured
                </label>
            </div>
            <div class="form-group full">
                <label>Short Description</label>
                <textarea name="short_desc"><?= htmlspecialchars($data['short_desc'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-gold"><?= $editing ? 'Update Product' : 'Save Product' ?></button>
            <a href="products.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
