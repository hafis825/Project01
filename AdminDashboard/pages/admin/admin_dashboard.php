<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('../../config.php');

if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    header('Location: /AdminDashboard/auth/login.php');
    exit();
}

// *********************************************COUNT****************************************************

$userCountQuery = "SELECT COUNT(*) FROM users";
$userCountStmt = $pdo->prepare($userCountQuery);
$userCountStmt->execute();
$userCount = $userCountStmt->fetchColumn();

$equipmentCountQuery = "SELECT COUNT(*) FROM equipment";
$equipmentCountStmt = $pdo->prepare($equipmentCountQuery);
$equipmentCountStmt->execute();
$equipmentCount = $equipmentCountStmt->fetchColumn();

$stmt = $pdo->query("SELECT * FROM equipment");
$equipment = $stmt->fetchAll();

// *********************************************COUNT****************************************************

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
                    <h1 class="mt-4">Dashboard</h1>
                    <p>Welcome, <strong class="text-primary"><?= htmlspecialchars($_SESSION['username']) ?></strong>!</p>

                    <div class="row">
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <h5 class="card-title">Users <span class="badge bg-primary"><?= $userCount ?></span></h5>
                                    <p class="card-text">Manage users in the system.</p>
                                    <a href="/AdminDashboard/pages/user_management.php" class="btn btn-primary">Go to Users</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <h5 class="card-title">Equipment <span class="badge bg-primary"><?= $equipmentCount ?></span></h5>
                                    <p class="card-text">Manage system Equipment.</p>
                                    <a href="/AdminDashboard/pages/equipment_management.php" class="btn btn-primary">Go to Equipment</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-info">
                                <div class="card-body">
                                    <h5 class="card-title">Equipment <span class="badge bg-primary"><?= $equipmentCount ?></span></h5>
                                    <p class="card-text">Manage system Equipment.</p>
                                    <a href="/AdminDashboard/pages/equipment_management.php" class="btn btn-primary">Go to Equipment</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-dark">
                                <div class="card-body">
                                    <h5 class="card-title">Equipment <span class="badge bg-primary"><?= $equipmentCount ?></span></h5>
                                    <p class="card-text">Manage system Equipment.</p>
                                    <a href="/AdminDashboard/pages/equipment_management.php" class="btn btn-primary">Go to Equipment</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-danger">
                                <div class="card-body">
                                    <h5 class="card-title">Equipment <span class="badge bg-primary"><?= $equipmentCount ?></span></h5>
                                    <p class="card-text">Manage system Equipment.</p>
                                    <a href="/AdminDashboard/pages/equipment_management.php" class="btn btn-primary">Go to Equipment</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-warning">
                                <div class="card-body">
                                    <h5 class="card-title">Equipment <span class="badge bg-primary"><?= $equipmentCount ?></span></h5>
                                    <p class="card-text">Manage system Equipment.</p>
                                    <a href="/AdminDashboard/pages/equipment_management.php" class="btn btn-primary">Go to Equipment</a>
                                </div>
                            </div>
                        </div>

                    </div>


                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="bi bi-table"></i>
                            Equipment List

                        </div>

                        <div class="card-body">
                            <table id="datatablesSimple" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>EquipmentID</th>
                                        <th>EquipmentName</th>
                                        <th>Category</th>
                                        <th>Quantity</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($equipment as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['equipmentid']); ?></td>
                                            <td><?php echo htmlspecialchars($item['equipmentname']); ?></td>
                                            <td><?php echo htmlspecialchars($item['category']); ?></td>
                                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center align-items-center">
                                                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal"
                                                        data-id="<?= $item['id'] ?>"
                                                        data-name="<?= htmlspecialchars($item['equipmentname']) ?>"
                                                        data-category="<?= htmlspecialchars($item['category']) ?>"
                                                        data-quantity="<?= $item['quantity'] ?>"
                                                        data-description="<?= htmlspecialchars($item['description']) ?>"
                                                        data-photo="<?= htmlspecialchars($item['photo']) ?>">
                                                        <i class="bi bi-info-circle"></i>
                                                    </button>
                                                </div>
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


    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel">Equipment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <td colspan="2" class="text-center">
                                    <img src="" alt="Equipment Photo" id="viewPhoto" style="max-width: 300px; max-height: 300px;">
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Equipment Name:</strong></td>
                                <td><span id="viewEquipmentName"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Category:</strong></td>
                                <td><span id="viewEquipmentCategory"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Quantity:</strong></td>
                                <td><span id="viewQuantity"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Description:</strong></td>
                                <td><span id="viewDescription"></span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
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

        const viewModal = document.getElementById('viewModal');
        viewModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;

            const equipmentName = button.getAttribute('data-name');
            const equipmentCategory = button.getAttribute('data-category');
            const equipmentQuantity = button.getAttribute('data-quantity');
            const equipmentDescription = button.getAttribute('data-description');
            const equipmentPhoto = button.getAttribute('data-photo');

            const modalName = viewModal.querySelector('#viewEquipmentName');
            const modalCategory = viewModal.querySelector('#viewEquipmentCategory');
            const modalQuantity = viewModal.querySelector('#viewQuantity');
            const modalDescription = viewModal.querySelector('#viewDescription');
            const modalPhoto = viewModal.querySelector('#viewPhoto');

            modalName.textContent = equipmentName;
            modalCategory.textContent = equipmentCategory;
            modalQuantity.textContent = equipmentQuantity;
            modalDescription.textContent = equipmentDescription;

            if (equipmentPhoto) {
                modalPhoto.src = "/AdminDashboard/assets/img/" + equipmentPhoto;
            } else {
                modalPhoto.src = "/AdminDashboard/assets/img/default-image.png";
            }
        });
    </script>
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
    </script>

</body>

</html>