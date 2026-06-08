<?php
require_once '../includes/db.php';

$id      = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$editing = $id > 0;
$data    = array('first_name'=>'','last_name'=>'','email'=>'','phone'=>'',
                 'city'=>'','country'=>'Pakistan','date_of_birth'=>'','gender'=>'','notes'=>'');
$errors  = array();

// LOAD existing record for edit
if ($editing) {
    $stmt = sqlsrv_query($conn, "SELECT * FROM customers WHERE customer_id = ?", array($id));
    if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $data = $row;
        $data['date_of_birth'] = $row['date_of_birth'] ? $row['date_of_birth']->format('Y-m-d') : '';
    } else {
        header("Location: customers.php");
        exit;
    }
}

// SUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = array(
        'first_name'    => trim($_POST['first_name']   ?? ''),
        'last_name'     => trim($_POST['last_name']    ?? ''),
        'email'         => trim($_POST['email']        ?? ''),
        'phone'         => trim($_POST['phone']        ?? ''),
        'city'          => trim($_POST['city']         ?? ''),
        'country'       => trim($_POST['country']      ?? 'Pakistan'),
        'date_of_birth' => trim($_POST['date_of_birth']?? ''),
        'gender'        => trim($_POST['gender']       ?? ''),
        'notes'         => trim($_POST['notes']        ?? ''),
    );

    if ($data['first_name'] === '') $errors[] = 'First name is required.';
    if ($data['last_name']  === '') $errors[] = 'Last name is required.';
    if ($data['email']      === '') $errors[] = 'Email is required.';

    if (empty($errors)) {
        $dob = $data['date_of_birth'] !== '' ? $data['date_of_birth'] : null;

        if ($editing) {
            $sql = "UPDATE customers SET
                        first_name=?, last_name=?, email=?, phone=?,
                        city=?, country=?, date_of_birth=?, gender=?,
                        notes=?, updated_at=GETDATE()
                    WHERE customer_id=?";
            $params = array(
                $data['first_name'], $data['last_name'], $data['email'], $data['phone'],
                $data['city'], $data['country'], $dob, $data['gender'],
                $data['notes'], $id
            );
            sqlsrv_query($conn, $sql, $params);
            header("Location: customers.php?msg=updated");
            exit;
        } else {
            $sql = "INSERT INTO customers
                        (first_name,last_name,email,phone,city,country,date_of_birth,gender,notes)
                    VALUES (?,?,?,?,?,?,?,?,?)";
            $params = array(
                $data['first_name'], $data['last_name'], $data['email'], $data['phone'],
                $data['city'], $data['country'], $dob, $data['gender'], $data['notes']
            );
            sqlsrv_query($conn, $sql, $params);
            header("Location: customers.php?msg=saved");
            exit;
        }
    }
}

require_once '../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1><?= $editing ? 'Edit Customer' : 'Add Customer' ?></h1>
        <p><?= $editing ? 'Update client information' : 'Register a new client' ?></p>
    </div>
    <a href="customers.php" class="btn btn-outline">Back to Customers</a>
</div>

<?php foreach ($errors as $e): ?>
    <div class="alert alert-error"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<div class="form-card">
    <form method="POST">
        <div class="form-grid">
            <div class="form-group">
                <label>First Name *</label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($data['first_name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Last Name *</label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($data['last_name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" value="<?= htmlspecialchars($data['email']) ?>" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($data['phone'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>City</label>
                <input type="text" name="city" value="<?= htmlspecialchars($data['city'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Country</label>
                <input type="text" name="country" value="<?= htmlspecialchars($data['country'] ?? 'Pakistan') ?>">
            </div>
            <div class="form-group">
                <label>Date of Birth</label>
                <input type="date" name="date_of_birth" value="<?= htmlspecialchars($data['date_of_birth'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Gender</label>
                <select name="gender">
                    <option value="">Select Gender</option>
                    <option value="Male"   <?= ($data['gender']??'')==='Male'   ? 'selected':'' ?>>Male</option>
                    <option value="Female" <?= ($data['gender']??'')==='Female' ? 'selected':'' ?>>Female</option>
                    <option value="Other"  <?= ($data['gender']??'')==='Other'  ? 'selected':'' ?>>Other</option>
                </select>
            </div>
            <div class="form-group full">
                <label>Notes</label>
                <textarea name="notes"><?= htmlspecialchars($data['notes'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-gold"><?= $editing ? 'Update Customer' : 'Save Customer' ?></button>
            <a href="customers.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
