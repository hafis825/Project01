<?php
session_start();
include('../../config.php');

if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    header('Location: /AdminDashboard/auth/login.php');
    exit();
}

$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="icon" type="image/x-icon" href="/AdminDashboard/assets/img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="/AdminDashboard/assets/style-client.css">
    <link rel="stylesheet" href="/AdminDashboard/assets/style-table.css">
</head>

<body class="sb-nav-fixed">
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
                    <h2 class="my-4">User Management</h2>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="/AdminDashboard/pages/admin/admin_dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">User Management</li>
                    </ol>
                    <a href="add_user.php" class="btn btn-success btn-sm">Add New User</a>

                    <div class="card mb-4 mt-3">
                        <div class="card-header">
                            <i class="bi bi-table"></i>
                            Equipment List

                        </div>

                        <div class="card-body">
                            <table id="datatablesSimple" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>UserID</th>
                                        <th>UserName</th>
                                        <th>FirstName</th>
                                        <th>LastName</th>
                                        <th>Department</th>
                                        <th>Role</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($user['id']) ?></td>
                                            <td><?= htmlspecialchars($user['userid']) ?></td>
                                            <td><?= htmlspecialchars($user['username']) ?></td>
                                            <td><?= htmlspecialchars($user['firstname']) ?></td>
                                            <td><?= htmlspecialchars($user['lastname']) ?></td>
                                            <td><?= htmlspecialchars($user['department']) ?></td>
                                            <td><?= htmlspecialchars($user['role']) ?></td>
                                            <td>
                                                <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                    data-id="<?= $user['id'] ?>"
                                                    data-name="<?= htmlspecialchars($user['username']) ?>"
                                                    data-role="<?= htmlspecialchars($user['role']) ?>">
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
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

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the following user?</p>
                    <div class="mb-3">
                        <strong>UserName: </strong> <span id="username"></span>
                    </div>
                    <div class="mb-3">
                        <strong>Role: </strong> <span id="userrole"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" action="" method="POST">
                        <button type="submit" class="btn btn-danger">Delete</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const dataTable = new simpleDatatables.DataTable("#datatablesSimple", {
                searchable: true, // เปิดใช้งานการค้นหา
                fixedHeight: true, // เปิดใช้งานความสูงคงที่
                perPage: 10, // จำนวนแถวต่อหน้าเริ่มต้น
                perPageSelect: [10, 15, 20, 50], // ตัวเลือกจำนวนแถวต่อหน้า
                sortable: false, // เปิดใช้งานการเรียงลำดับ

            });
        });

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

        const deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;

            const username = button.getAttribute('data-name');
            const userrole = button.getAttribute('data-role');
            const userId = button.getAttribute('data-id');

            const modalTitle = deleteModal.querySelector('.modal-title');
            const modalBodyInput = deleteModal.querySelector('.modal-body #username');
            const modalBodyCategory = deleteModal.querySelector('.modal-body #userrole');

            modalBodyInput.textContent = username;
            modalBodyCategory.textContent = userrole;

            const form = deleteModal.querySelector('#deleteForm');
            form.action = 'delete_user.php?id=' + userId;
        });
    </script>



</body>

</html>