<?php
session_start();
include('../config.php');

if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor')) {
    header('Location: /AdminDashboard/auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipmentname = $_POST['equipmentname'] ?? '';
    $category = $_POST['category'] ?? '';
    $quantity = $_POST['quantity'] ?? 0;
    $photo = $_POST['photo'] ?? '';
    $description = $_POST['description'] ?? '';

    $category_prefixes = [
        'Football' => 'F',
        'Basketball' => 'B',
        'Volleyball' => 'VB',
        'Takraw' => 'T',
        'Badminton' => 'BD',
        'TableTennis' => 'TT',
        'Checkers' => 'C',
        'Bingo' => 'BG',
    ];

    $category_prefix = $category_prefixes[$category] ?? '';

    $stmt = $pdo->prepare("SELECT equipmentid FROM equipment WHERE equipmentid LIKE ? ORDER BY equipmentid DESC LIMIT 1");
    $stmt->execute([$category_prefix . '%']);
    $last_equipmentid = $stmt->fetchColumn();

    if ($last_equipmentid) {
        $last_number = (int)substr($last_equipmentid, strlen($category_prefix));
        $new_number = $last_number + 1;
        $equipmentid = $category_prefix . str_pad($new_number, 4, '0', STR_PAD_LEFT);
    } else {
        $equipmentid = $category_prefix . '0001';
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM equipment WHERE equipmentname = ?");
    $stmt->execute([$equipmentname]);

    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Equipment Name already exists.";
    }

    $errors = [];
    $photo = '';

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $newPhoto = $_FILES['photo'];
        $allowedTypes = ['image/jpg', 'image/jpeg', 'image/png'];

        if (in_array($newPhoto['type'], $allowedTypes)) {
            $fileName = $equipmentid . '-' . basename($newPhoto['name']);
            $targetDirectory = '/AdminDashboard/assets/img/';
            $targetFilePath = $targetDirectory . $fileName;

            if (move_uploaded_file($newPhoto['tmp_name'], $targetFilePath)) {
                $photo = $fileName;
            } else {
                $errors[] = "Error uploading the photo.";
            }
        } else {
            $errors[] = "Invalid file type. Only JPG and PNG files are allowed.";
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO equipment (equipmentid, equipmentname, category, quantity, photo, description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$equipmentid, $equipmentname, $category, $quantity, $photo, $description]);

        header('Location: equipment_management.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Equipment</title>
    <link rel="icon" type="image/x-icon" href="/AdminDashboard/assets/img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/AdminDashboard/assets/style.css">
</head>

<body>
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <strong class="navbar-brand"> Admin Dashboard</strong>
    </header>

    <div class="container">
        <h2 class="my-4">Add Equipment</h2>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="/AdminDashboard/pages/admin_dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active"><a href="equipment_management.php">Equipment Management</a></li>
            <li class="breadcrumb-item active">Add Equipment</li>
        </ol>
        <form action="add_equipment.php" method="POST" enctype="multipart/form-data">
            <div class="container d-flex justify-content-center align-items-center">
                <div class="card" style="width: 800px;padding: 1rem;">
                    <h4><i class="bi bi-tools"></i> Equipment Information</h4>

                    <label for="equipmentname" class="form-label">Equipment Name</label>
                    <div class="input-group input-group-sm mb-3 ">
                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-cart2"></i></span>
                        <input type="text" class="form-control" id="equipmentname" name="equipmentname" placeholder="Equipment Name" aria-label="equipmentname" aria-describedby="basic-addon1" required oninput="limitText(this, 25, 'equipmentWarning')">
                        <div id="equipmentWarning" class="invalid-feedback" style="display: none;">
                            You have reached the limit of 25 characters!
                        </div>
                    </div>

                    <label for="category" class="form-label">Category</label>
                    <div class="input-group input-group-sm mb-3 ">
                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-bookmark"></i></span>
                        <select class="form-select" id="category" name="category" required>
                            <option value="Football">Football (ฟุตบอล)</option>
                            <option value="Basketball">Basketball (บาสเก็ตบอล)</option>
                            <option value="Volleyball">Volleyball (วอลเลย์บอล)</option>
                            <option value="Takraw">Takraw (ตะกร้อ)</option>
                            <option value="Badminton">Badminton (แบดมินตัน)</option>
                            <option value="TableTennis">TableTennis (ปิงปอง)</option>
                            <option value="Checkers">Checkers (หมากฮอส)</option>
                            <option value="Bingo">Bingo (บิงโก)</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <div class="input-group input-group-sm mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-123" viewBox="0 0 16 16">
                                        <path d="M2.873 11.297V4.142H1.699L0 5.379v1.137l1.64-1.18h.06v5.961zm3.213-5.09v-.063c0-.618.44-1.169 1.196-1.169.676 0 1.174.44 1.174 1.106 0 .624-.42 1.101-.807 1.526L4.99 10.553v.744h4.78v-.99H6.643v-.069L8.41 8.252c.65-.724 1.237-1.332 1.237-2.27C9.646 4.849 8.723 4 7.308 4c-1.573 0-2.36 1.064-2.36 2.15v.057zm6.559 1.883h.786c.823 0 1.374.481 1.379 1.179.01.707-.55 1.216-1.421 1.21-.77-.005-1.326-.419-1.379-.953h-1.095c.042 1.053.938 1.918 2.464 1.918 1.478 0 2.642-.839 2.62-2.144-.02-1.143-.922-1.651-1.551-1.714v-.063c.535-.09 1.347-.66 1.326-1.678-.026-1.053-.933-1.855-2.359-1.845-1.5.005-2.317.88-2.348 1.898h1.116c.032-.498.498-.944 1.206-.944.703 0 1.206.435 1.206 1.07.005.64-.504 1.106-1.2 1.106h-.75z" />
                                    </svg>
                                </span>
                                <input type="text" class="form-control" id="quantity" name="quantity" placeholder="Quantity" aria-label="quantity" aria-describedby="basic-addon1" required oninput="validateQuantity(this)">
                                <div id="quantityWarning" class="invalid-feedback" style="display: none;">
                                    Quantity cannot be zero!
                                </div>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <label for="photo" class="form-label">Photo</label>
                            <div class="input-group input-group-sm mb-3 ">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-image"></i></span>
                                <input type="file" class="form-control" id="photo" name="photo" aria-label="photo" aria-describedby="basic-addon1">
                            </div>
                        </div>
                    </div>

                    <div class="form-floating" style="margin-bottom: 1rem;">
                        <textarea class="form-control" id="description" name="description" placeholder="Leave a comment here" oninput="limitText(this, 50, 'descriptionWarning')"></textarea>
                        <label for="description">Description</label>
                        <div id="descriptionWarning" class="invalid-feedback" style="display: none;">
                            You have reached the limit of 50 characters!
                        </div>
                    </div>

                    <?php
                    if (!empty($errors)) {
                        foreach ($errors as $error) {
                            echo "<p class='text-danger'>$error</p>";
                        }
                    }
                    ?>

                    <div class="input-group input-group-sm mb-3 d-flex justify-content-end">
                        <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-cart-plus"></i> Add Equipment</button>
                        <a href="equipment_management.php" class="btn btn-secondary btn-sm"><i class="bi bi-x-square"></i> Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <script>
        function limitText(field, maxChar, warningId) {
            if (field.value.length > maxChar) {
                field.value = field.value.substring(0, maxChar);
                field.classList.add('is-invalid');
                document.getElementById(warningId).style.display = 'block';
            } else {
                field.classList.remove('is-invalid');
                document.getElementById(warningId).style.display = 'none';
            }
        }

        function validateQuantity(field) {
            const warningMessage = document.getElementById('quantityWarning');
            const value = field.value;

            if (!/^\d+$/.test(value)) {
                field.classList.add('is-invalid');
                warningMessage.innerText = 'Please enter a valid number!';
                warningMessage.style.display = 'block';
            } else if (value === '0') {
                field.classList.add('is-invalid');
                warningMessage.innerText = 'Quantity cannot be zero!';
                warningMessage.style.display = 'block';
            } else {
                field.classList.remove('is-invalid');
                warningMessage.style.display = 'none';
            }
        }
    </script>
</body>

</html>