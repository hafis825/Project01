<?php
session_start();
include('../../config.php');

if (!isset($_GET['id'])) {
    header('Location: user_management.php');
    exit();
}

$user_id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $department = $_POST['department'];
    $role = $_POST['role'];

    $errors = [];

    $update_stmt = $pdo->prepare("UPDATE users SET username = ?, firstname = ?, lastname = ?, department = ?, role = ? WHERE id = ?");
    $update_stmt->execute([$username, $firstname, $lastname, $department, $role, $user_id]);

    if (!empty($password) && !empty($confirm_password)) {
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $password_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $password_stmt->execute([$hashed_password, $user_id]);
        }
    }

    if (empty($errors)) {
        header('Location: user_management.php');
        exit();
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: user_management.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="icon" type="image/x-icon" href="/AdminDashboard/assets/img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/AdminDashboard/assets/style-client.css">
    <link rel="stylesheet" href="/AdminDashboard/assets/style-table.css">
</head>

<body>
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
        <strong class="navbar-brand ps-3"><img src="/AdminDashboard/assets/img/favicon.ico" width="24" height="24"> Admin Dashboard</strong>
        <!-- Sidebar Toggle-->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="bi bi-list"></i></button>

        <!-- Navbar-->
        <ul class="navbar-nav ms-auto me-0 me-md-3 my-2 my-md-0">
            <li class="py-2">
                <a href="/AdminDashboard/index.php" class="text-decoration-none badge bg-primary text-wrap">Client Systems.</a>
            </li>
            <li>
                <div class="vr d-none d-lg-flex h-100 mx-lg-2 text-white"></div>
                <hr class="d-lg-none my-2 text-white-50">
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-fill"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="#!">Settings</a></li>
                    <li><a class="dropdown-item" href="#!">Activity Log</a></li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li><a class="dropdown-item" href="#!">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <a class="nav-link" href="/AdminDashboard/pages/admin/admin_dashboard.php">
                            <div class="sb-nav-link-icon"><i class="bi bi-columns-gap"></i></div>
                            Dashboard
                        </a>
                        <div class="sb-sidenav-menu-heading">Management</div>
                        <a class="nav-link" href="/AdminDashboard/pages/admin/user_management.php">
                            <div class="sb-nav-link-icon"><i class="bi bi-person"></i></div>
                            User
                        </a>
                        <a class="nav-link" href="#">
                            <div class="sb-nav-link-icon"><i class="bi bi-tools"></i></div>
                            Equipment
                        </a>
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                            <div class="sb-nav-link-icon"><i class="bi bi-pencil-square"></i></div>
                            Loan Systems
                            <div class="sb-sidenav-collapse-arrow"><i class="bi bi-chevron-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="#">Equipment</a>
                                <a class="nav-link" href="#">Equipment</a>
                                <a class="nav-link" href="#">Equipment</a>
                                <a class="nav-link" href="#">Equipment</a>
                            </nav>
                        </div>
                    </div>
                </div>
            </nav>
        </div>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h2 class="my-4">Edit User</h2>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="/AdminDashboard/pages/admin/admin_dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active"><a href="User_management.php">User Management</a></li>
                        <li class="breadcrumb-item active">Edit User</li>
                    </ol>
                    <form action="edit_user.php?id=<?= $user['id'] ?>" method="POST">
                        <div class="container d-flex justify-content-center align-items-center">
                            <div class="card" style="width: 800px;padding: 1rem;">

                                <h4><i class="bi bi-pencil-square"></i> Detail User <span class="badge bg-dark"><?= $user['id'] ?></span></h4>

                                <label for="Username" class="form-label">Username</label>
                                <div class="input-group input-group-sm mb-3 ">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-circle"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1" value="<?= $user['username'] ?>" oninput="limitText(this, 25,'usernameWarning')">
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
                                            <select class="form-select" id="department" name="department" required>
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
                                            <select class="form-select" id="role" name="role" required>
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
                                    <a href="user_management.php" class="btn btn-secondary btn-sm"><i class="bi bi-x-square"></i> Cancel</a>
                                </div>

                            </div>
                        </div>

                    </form>
                </div>
            </main>
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="small">
                        <div class="text-muted">Copyright &copy; BorrowEquipmentSystems</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener('DOMContentLoaded', event => {

            const sidebarToggle = document.body.querySelector('#sidebarToggle');
            if (sidebarToggle) {

                sidebarToggle.addEventListener('click', event => {
                    event.preventDefault();
                    document.body.classList.toggle('sb-sidenav-toggled');
                    localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
                });
            }

        });

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