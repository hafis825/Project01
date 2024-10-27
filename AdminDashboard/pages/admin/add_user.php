<?php
session_start();
include('../../config.php');

if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor')) {
    header('Location: /AdminDashboard/auth/login.php');
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $department = $_POST['department'];
    $role = $_POST['role'];

    // Admin = A0001
    // Editor = ED0001
    // User = U0001
    $userid_prefix = '';
    if ($role === 'admin') {
        $userid_prefix = 'A';
    } elseif ($role === 'editor') {
        $userid_prefix = 'ED';
    } elseif ($role === 'user') {
        $userid_prefix = 'U';
    }

    $stmt = $pdo->prepare("SELECT userid FROM users WHERE userid LIKE ? ORDER BY userid DESC LIMIT 1");
    $stmt->execute([$userid_prefix . '%']);
    $last_userid = $stmt->fetchColumn();

    if ($last_userid) {
        $last_number = (int)substr($last_userid, 1);
        $new_number = $last_number + 1;
        $userid = $userid_prefix . str_pad($new_number, 4, '0', STR_PAD_LEFT);
    } else {
        $userid = $userid_prefix . '0001';
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);

    if ($stmt->fetch()) {
        $errors[] = "Username already exists.";
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (userid, username, password, firstname, lastname, department, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userid, $username, $hashed_password, $firstname, $lastname, $department, $role]);

        header('Location: user_management.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User</title>
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
                    <h2 class="my-4">Add New User</h2>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="/AdminDashboard/pages/admin/admin_dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="User_management.php">User Management</a></li>
                        <li class="breadcrumb-item active">Add New User</li>
                    </ol>
                    <form action="add_user.php" method="POST">
                        <div class="container d-flex justify-content-center align-items-center">
                            <div class="card" style="width: 800px;padding: 1rem;">
                                <h4><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-person-vcard-fill" viewBox="0 0 16 16">
                                        <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm9 1.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 0-1h-4a.5.5 0 0 0-.5.5M9 8a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 0-1h-4A.5.5 0 0 0 9 8m1 2.5a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 0-1h-3a.5.5 0 0 0-.5.5m-1 2C9 10.567 7.21 9 5 9c-2.086 0-3.8 1.398-3.984 3.181A1 1 0 0 0 2 13h6.96q.04-.245.04-.5M7 6a2 2 0 1 0-4 0 2 2 0 0 0 4 0" />
                                    </svg> User Information</h4>
                                <label for="Username" class="form-label">Username</label>
                                <div class="input-group input-group-sm mb-3 ">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-circle"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1" required oninput="limitText(this, 25, 'usernametWarning')">
                                    <div id="usernametWarning" class="invalid-feedback" style="display: none;">
                                        You have reached the limit of 25 characters!
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group input-group-sm mb-3 ">
                                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-key"></i></span>
                                            <input type="password" class="form-control" id="password" name="password" placeholder="password" aria-label="password" aria-describedby="basic-addon1" required oninput="limitText(this, 25, 'passwordWarning')">
                                            <div id="passwordWarning" class="invalid-feedback" style="display: none;">
                                                You have reached the limit of 25 characters!
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="confirm_password" class="form-label">Confirm Password</label>
                                        <div class="input-group input-group-sm mb-3 ">
                                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-key"></i></span>
                                            <input type="password" class="form-control" placeholder="Confirm Password" id="confirm_password" name="confirm_password" aria-label="confirm_password" aria-describedby="basic-addon1" required oninput="limitText(this, 25, 'comfirmpasswordWarning')">
                                            <div id="comfirmpasswordWarning" class="invalid-feedback" style="display: none;">
                                                You have reached the limit of 25 characters!
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="firstname" class="form-label">Firstname</label>
                                        <div class="input-group input-group-sm mb-3 ">
                                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-person"></i></span>
                                            <input type="text" class="form-control" id="firstname" name="firstname" placeholder="firstname" aria-label="firstname" aria-describedby="basic-addon1" required oninput="limitText(this, 25, 'firstnameWarning')">
                                            <div id="firstnameWarning" class="invalid-feedback" style="display: none;">
                                                You have reached the limit of 25 characters!
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="lastname" class="form-label">Lastname</label>
                                        <div class="input-group input-group-sm mb-3 ">
                                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-person"></i></span>
                                            <input type="text" class="form-control" placeholder="Lastname" id="lastname" name="lastname" aria-label="confirm_password" aria-describedby="basic-addon1" required oninput="limitText(this, 25, 'lastnameWarning')">
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
                                                <option value="" disabled selected hidden>Select Department...</option>
                                                <option value="Business_Computer">Business Computer (คอมพิวเตอร์ธุรกิจ)</option>
                                                <option value="Accounting">Accounting (การบัญชี)</option>
                                                <option value="Business_Foreign_Language">Business Foreign Language (ภาษาต่างประเทศธุรกิจ)</option>
                                                <option value="Auto_Mechanic">Auto mechanic (ช่างยนต์)</option>
                                                <option value="Machine_Tool_Technology">Machine Tool Technology (ช่างกลโรงงาน)</option>
                                                <option value="Metal_Technology">Metal Technology (ช่างเชื่อมโลหะ)</option>
                                                <option value="Electrical_Power">Electrical Power (ไฟฟ้ากำลัง)</option>
                                                <option value="Electronics_Engineering">Electronics Engineering (อิเล็กทรอนิกส์)</option>
                                                <option value="Food_Business_And_Nutrition">Food Business and Nutrition (อาหารและโภชนาการ)</option>
                                                <option value="Information_Technology">Information Technology (IT)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="role" class="form-label">Role</label>
                                        <div class="input-group input-group-sm mb-3 ">
                                            <span class="input-group-text"><i class="bi bi-star"></i></span>
                                            <select class="form-select" id="role" name="role" required>
                                                <option value="" disabled selected hidden>Select Role...</option>
                                                <option value="admin">Admin</option>
                                                <option value="editor">Editor</option>
                                                <option value="user">User</option>
                                            </select>
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
                                    <button type="submit" class="btn btn-success btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-add" viewBox="0 0 16 16">
                                            <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 0 1-1 0v-1h-1a.5.5 0 0 1 0-1h1v-1a.5.5 0 0 1 1 0m-2-6a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4" />
                                            <path d="M8.256 14a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z" />
                                        </svg> Add User</button>
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