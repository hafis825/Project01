<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('../config.php');

if (!isset($_SESSION['username'])) {
    header('Location: /AdminDashboard/auth/login.php');
    exit();
}

$currentPage = basename($_SERVER['PHP_SELF'], ".php");


$user_id = $_SESSION['username'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();



?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment List</title>
    <link rel="icon" type="image/x-icon" href="/AdminDashboard/assets/img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/AdminDashboard/assets/style-client.css">
</head>

<body>
    <?php include('../includes/nav.php') ?>

    <form action="setting_user.php" method="POST">
        <div class="container">
            <h2 class="my-4">Setting User</h2>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active"><a href="/AdminDashboard/index.php">Dashboard</a></li>
                <li class="breadcrumb-item">Setting User</li>
            </ol>
            <div class="container d-flex justify-content-center align-items-center mt-5">

                <div class="card" style="width: 100%; padding: 1rem;">
                    <h4><img src="/AdminDashboard/assets/svg/usersetting.svg"> Transaction Details</h4>

                    <div class="card-body">
                        <label for="Username" class="form-label">Username</label>
                        <div class="input-group input-group-sm mb-3 ">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-circle"></i></span>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1" value="<?= $user['username'] ?>" oninput="limitText(this, 25,'usernameWarning')" readonly>
                            <div id="usernameWarning" class="invalid-feedback" style="display: none;">
                                You have reached the limit of 25 characters!
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="firstname" class="form-label">Firstname</label>
                                <div class="input-group input-group-sm mb-3 ">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="firstname" name="firstname" placeholder="firstname" aria-label="firstname" aria-describedby="basic-addon1" value="<?= $user['firstname'] ?>" oninput="limitText(this, 25,'firstnameWarning')">
                                    <div id="firstnameWarning" class="invalid-feedback" style="display: none;">
                                        You have reached the limit of 25 characters!
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="lastname" class="form-label">Lastname</label>
                                <div class="input-group input-group-sm mb-3 ">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" placeholder="Lastname" id="lastname" name="lastname" aria-label="confirm_password" aria-describedby="basic-addon1" value="<?= $user['lastname'] ?>" oninput="limitText(this, 25,'lastnameWarning')">
                                    <div id="lastnameWarning" class="invalid-feedback" style="display: none;">
                                        You have reached the limit of 25 characters!
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="department" class="form-label">Department</label>
                                <div class="input-group input-group-sm mb-3 ">
                                    <span class="input-group-text"><i class="bi bi-house"></i></span>
                                    <select class="form-select" id="department" name="department">
                                        <option value="Business_Computer" <?= $user['department'] == 'Business_Computer' ? 'selected' : '' ?>>Business Computer (คอมพิวเตอร์ธุรกิจ)</option>
                                        <option value="Accounting" <?= $user['department'] == 'Accounting' ? 'selected' : '' ?>>Accounting (การบัญชี)</option>
                                        <option value="Business_Foreign_Language" <?= $user['department'] == 'Business_Foreign_Language' ? 'selected' : '' ?>>Business Foreign Language (ภาษาต่างประเทศธุรกิจ)</option>
                                        <option value="Auto_Mechanic" <?= $user['department'] == 'Auto_Mechanic' ? 'selected' : '' ?>>Auto mechanic (ช่างยนต์)</option>
                                        <option value="Machine_Tool_Technology" <?= $user['department'] == 'Machine_Tool_Technology' ? 'selected' : '' ?>>Machine Tool Technology (ช่างกลโรงงาน)</option>
                                        <option value="Metal_Technology" <?= $user['department'] == 'Metal_Technology' ? 'selected' : '' ?>>Metal Technology (ช่างเชื่อมโลหะ)</option>
                                        <option value="Electrical_Power" <?= $user['department'] == 'Electrical_Power' ? 'selected' : '' ?>>Electrical Power (ไฟฟ้ากำลัง)</option>
                                        <option value="Electronics_Engineering" <?= $user['department'] == 'Electronics_Engineering' ? 'selected' : '' ?>>Electronics Engineering (อิเล็กทรอนิกส์)</option>
                                        <option value="Food_Business_And_Nutrition" <?= $user['department'] == 'Food_Business_And_Nutrition' ? 'selected' : '' ?>>Food Business and Nutrition (อาหารและโภชนาการ)</option>
                                        <option value="Information_Technology" <?= $user['department'] == 'Information_Technology' ? 'selected' : '' ?>>Information Technology (IT)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="role" class="form-label">Role</label>
                                <div class="input-group input-group-sm mb-3 ">
                                    <span class="input-group-text"><i class="bi bi-star"></i></span>
                                    <select class="form-select" id="role" name="role" disabled>
                                        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                        <option value="editor" <?= $user['role'] == 'editor' ? 'selected' : '' ?>>Editor</option>
                                        <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h4><i class="bi bi-key-fill"></i> Change Password</h4>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="password" class="form-label">New Password</label>
                                <div class="input-group input-group-sm mb-3 ">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-key"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="New Password" aria-label="password" aria-describedby="basic-addon1" oninput="limitText(this, 25,'passwordWarning')">
                                    <div id="passwordWarning" class="invalid-feedback" style="display: none;">
                                        You have reached the limit of 25 characters!
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">New Confirm Password</label>
                                <div class="input-group input-group-sm mb-3 ">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-key"></i></span>
                                    <input type="password" class="form-control" placeholder="New Confirm Password" id="confirm_password" name="confirm_password" aria-label="confirm_password" aria-describedby="basic-addon1" oninput="limitText(this, 25,'confirmpasswordWarning')">
                                    <div id="confirmpasswordWarning" class="invalid-feedback" style="display: none;">
                                        You have reached the limit of 25 characters!
                                    </div>
                                </div>
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
                            <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
    </script>
</body>

</html>