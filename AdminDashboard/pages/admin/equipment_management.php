<?php
session_start();
include('../config.php');

if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor')) {
    header('Location: ../auth/login.php');
    exit();
}

$searchTerm = $_GET['search'] ?? '';
$categoryFilter = $_GET['category'] ?? 'all';
$limit = 5;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$query = "SELECT * FROM equipment WHERE (equipmentid LIKE :searchTerm OR equipmentname LIKE :searchTerm)";
$params = ['searchTerm' => '%' . $searchTerm . '%'];

if ($categoryFilter !== 'all') {
    $query .= " AND category = :category";
    $params['category'] = $categoryFilter;
}

$query .= " LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':searchTerm', $params['searchTerm']);
if ($categoryFilter !== 'all') {
    $stmt->bindParam(':category', $params['category']);
}
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$equipment = $stmt->fetchAll();

$totalQuery = "SELECT COUNT(*) FROM equipment WHERE (equipmentid LIKE :searchTerm OR equipmentname LIKE :searchTerm)";
if ($categoryFilter !== 'all') {
    $totalQuery .= " AND category = :category";
}

$totalStmt = $pdo->prepare($totalQuery);
$totalStmt->bindParam(':searchTerm', $params['searchTerm']);
if ($categoryFilter !== 'all') {
    $totalStmt->bindParam(':category', $params['category']);
}
$totalStmt->execute();
$totalEquipment = $totalStmt->fetchColumn();
$totalPages = ceil($totalEquipment / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Management</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <strong class="navbar-brand"><img src="/AdminDashboard/assets/img/favicon.ico" alt="" width="30"> Admin Dashboard</strong>
    </header>

    <div class="container-fluid" id="wrapper">
        <div class="row">

            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar" id="sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" aria-current="page" href="/AdminDashboard/pages/admin_dashboard.php">
                                <i class="bi bi-house"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/AdminDashboard/pages/user_management.php">
                                <i class="bi bi-person"></i> User Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/AdminDashboard/pages/equipment_management.php">
                                <i class="bi bi-tools"></i> Equipment Management
                            </a>
                        </li>


                    </ul>
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>User</span>
                        <i class="bi bi-plus-circle" id="userToggle" style="cursor: pointer;width: 13px; height: 13px; font-size: 13px;"></i>
                    </h6>

                    <ul class="nav flex-column" id="userMenu">
                        <li class="nav-item">
                            <a class="nav-link text-primary "><strong><?= htmlspecialchars($_SESSION['username']) ?></strong>!</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/AdminDashboard/auth/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <div id="page-content-wrapper" class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="container-fluid">
                    <h2 class="my-4">Equipment Management</h2>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="/AdminDashboard/pages/admin_dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Equipment Management</li>
                    </ol>
                    <form method="GET" class="mb-3 d-flex align-items-center">
                        <div class="input-group input-group-sm me-2" style="width: 250px;">
                            <span class="input-group-text" id="basic-addon1">Category</span>
                            <select name="category" id="category" class="form-select" aria-label="Default select example">
                                <option value="all" <?= $categoryFilter === 'all' ? 'selected' : '' ?>>All</option>
                                <option value="Football" <?= $categoryFilter === 'football' ? 'selected' : '' ?>>Football</option>
                                <option value="Basketball" <?= $categoryFilter === 'Basketball' ? 'selected' : '' ?>>Basketball</option>
                                <option value="Volleyball" <?= $categoryFilter === 'Volleyball' ? 'selected' : '' ?>>Volleyball</option>
                                <option value="Takraw" <?= $categoryFilter === 'Takraw' ? 'selected' : '' ?>>Takraw</option>
                                <option value="Badminton" <?= $categoryFilter === 'Badminton' ? 'selected' : '' ?>>Badminton</option>
                                <option value="TableTennis" <?= $categoryFilter === 'TableTennis' ? 'selected' : '' ?>>TableTennis</option>
                                <option value="Checkers" <?= $categoryFilter === 'Checkers' ? 'selected' : '' ?>>Checkers</option>
                                <option value="Bingo" <?= $categoryFilter === 'Bingo' ? 'selected' : '' ?>>Bingo</option>
                            </select>
                        </div>

                        <div class="input-group input-group-sm me-2" style="width: 250px;">
                            <input type="text" name="search" value="<?= htmlspecialchars($searchTerm) ?>" placeholder="Search" class="form-control">
                            <button type="submit" class="btn btn-primary btn-sm "><i class="bi bi-search"></i></button>
                        </div>
                    </form>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>EquipmentID</th>
                                <th>EquipmentName</th>
                                <th>Category</th>
                                <th>quantity</th>
                                <th class="text-center">photo</th>
                                <th>description</th>
                                <th>Action</th>
                                <th style="width: 150px;"><a href="add_equipment.php" class="btn btn-success btn-sm">Add New Equipment</a></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($equipment) > 0): ?>
                                <?php foreach ($equipment as $item): ?>
                                    <tr>
                                        <td><?= $item['id'] ?></td>
                                        <td><?= $item['equipmentid'] ?></td>
                                        <td><?= $item['equipmentname'] ?></td>
                                        <td><?= $item['category'] ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td class="text-center">
                                            <?php if (!empty($item['photo'])): ?>
                                                <img src="../assets/img/<?= htmlspecialchars($item['photo']) ?>" alt="Current Photo" style="max-width: 60px;max-height: 60px;">
                                            <?php else: ?>
                                                <img src="../assets/img/default-image.png" alt="Default Photo" style="max-width: 60px;max-height: 60px;">
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $item['description'] ?></td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal"
                                                data-id="<?= $item['id'] ?>"
                                                data-name="<?= htmlspecialchars($item['equipmentname']) ?>"
                                                data-category="<?= htmlspecialchars($item['category']) ?>"
                                                data-quantity="<?= $item['quantity'] ?>"
                                                data-description="<?= htmlspecialchars($item['description']) ?>"
                                                data-photo="<?= htmlspecialchars($item['photo']) ?>">
                                                View
                                            </button>
                                            <a href="edit_equipment.php?id=<?= $item['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                data-id="<?= $item['id'] ?>"
                                                data-name="<?= htmlspecialchars($item['equipmentname']) ?>"
                                                data-category="<?= htmlspecialchars($item['category']) ?>">
                                                Delete
                                            </button>
                                        </td>
                                        <td></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">No users found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <nav>
                        <ul class="pagination d-flex">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($searchTerm) ?>&role=<?= $categoryFilter ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
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


            <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete the following equipment?</p>
                            <div class="mb-3">
                                <strong>Equipment Name: </strong> <span id="equipmentName"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Category: </strong> <span id="equipmentCategory"></span>
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
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userToggle = document.getElementById('userToggle');
            const userMenu = document.getElementById('userMenu');

            userMenu.style.display = 'block';
            userToggle.classList.remove('bi-plus-circle');
            userToggle.classList.add('bi-dash-circle');

            userToggle.addEventListener('click', function() {
                if (userMenu.style.display === 'none' || userMenu.style.display === '') {
                    userMenu.style.display = 'block';
                    userToggle.classList.remove('bi-plus-circle');
                    userToggle.classList.add('bi-dash-circle');
                } else {
                    userMenu.style.display = 'none';
                    userToggle.classList.remove('bi-dash-circle');
                    userToggle.classList.add('bi-plus-circle');
                }
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
                modalPhoto.src = "../assets/img/" + equipmentPhoto;
            } else {
                modalPhoto.src = "../assets/img/default-image.png";
            }
        });


        const deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;

            const equipmentName = button.getAttribute('data-name');
            const equipmentCategory = button.getAttribute('data-category');
            const equipmentId = button.getAttribute('data-id');

            const modalTitle = deleteModal.querySelector('.modal-title');
            const modalBodyInput = deleteModal.querySelector('.modal-body #equipmentName');
            const modalBodyCategory = deleteModal.querySelector('.modal-body #equipmentCategory');

            modalBodyInput.textContent = equipmentName;
            modalBodyCategory.textContent = equipmentCategory;

            const form = deleteModal.querySelector('#deleteForm');
            form.action = 'delete_equipment.php?id=' + equipmentId;
        });
    </script>
</body>

</html>