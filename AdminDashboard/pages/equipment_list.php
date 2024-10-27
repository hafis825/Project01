<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require '../config.php';

$currentPage = basename($_SERVER['PHP_SELF'], ".php");

if (!isset($_SESSION['username'])) {
    header('Location: /AdminDashboard/auth/login.php');
    exit();
}

$stmt = $pdo->query("SELECT * FROM equipment");
$equipment = $stmt->fetchAll();


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
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="/AdminDashboard/assets/style-client.css">
    <link rel="stylesheet" href="/AdminDashboard/assets/style-table.css">
</head>

<body>
    <?php include('../includes/nav.php') ?>

    <div class="container">
        <h2 class="my-4">Equipment List</h2>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item active"><a href="/AdminDashboard/index.php">Dashboard</a></li>
            <li class="breadcrumb-item">Equipment List</li>
        </ol>
        <div class="container d-flex justify-content-center align-items-center mt-5">
            <div class="card mb-4" style="width: 100%;">
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
                                <td><strong>Equipment Name</strong></td>
                                <td><span id="viewEquipmentName"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Category</strong></td>
                                <td><span id="viewEquipmentCategory"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Quantity</strong></td>
                                <td><span id="viewQuantity"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Description</strong></td>
                                <td><span id="viewDescription"></span></td>
                            </tr>
                        </tbody>
                    </table>
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


</body>

</html>