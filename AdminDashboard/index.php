<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'config.php';
$currentPage = basename($_SERVER['PHP_SELF'], ".php");

if (!isset($_SESSION['username'])) {
    header('Location: /AdminDashboard/auth/login.php');
    exit();
}

// *********************************************COUNT****************************************************

$equipmentCountQuery = "SELECT COUNT(*) FROM loan_details WHERE status = 'pending'";
$equipmentCountStmt = $pdo->prepare($equipmentCountQuery);
$equipmentCountStmt->execute();
$equipmentCountPending = $equipmentCountStmt->fetchColumn();

$equipmentCountQuery = "SELECT COUNT(*) FROM loan_details WHERE status = 'borrowed'";
$equipmentCountStmt = $pdo->prepare($equipmentCountQuery);
$equipmentCountStmt->execute();
$equipmentCountBorrowed = $equipmentCountStmt->fetchColumn();

$equipmentCountQuery = "SELECT COUNT(*) FROM loan_details WHERE status = 'returned'";
$equipmentCountStmt = $pdo->prepare($equipmentCountQuery);
$equipmentCountStmt->execute();
$equipmentCountReturned = $equipmentCountStmt->fetchColumn();

// $stmt = $pdo->query("SELECT * FROM equipment");
// $equipment = $stmt->fetchAll();

// *********************************************COUNT****************************************************

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BorrowEquipment System</title>
    <link rel="icon" type="image/x-icon" href="/AdminDashboard/assets/img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/AdminDashboard/assets/style-client.css">

</head>

<body>
    <?php include('includes/nav.php') ?>

    <div id="page-content-wrapper" class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="container-fluid">
            <h1 class="my-4">Dashboard</h1>
            <p>Welcome, <strong class="text-primary"><?= htmlspecialchars($_SESSION['username']) ?></strong>!</p>
            <div class="row">
                <div class="col-md-3 col-lg-3 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><strong class="text-primary"><?= htmlspecialchars($_SESSION['username']) ?></strong></h5>
                            <span class="badge bg-primary fs-4"><?= $equipmentCountPending ?></span>
                            <hr>
                            <a href="/AdminDashboard/pages/equipment_management.php" class="btn btn-primary">Equipment Pending</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-lg-3 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><strong class="text-primary"><?= htmlspecialchars($_SESSION['username']) ?></strong></h5>
                            <span class="badge bg-primary fs-4"><?= $equipmentCountPending ?></span>
                            <hr>
                            <a href="/AdminDashboard/pages/equipment_management.php" class="btn btn-primary">Equipment Borrowed</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-lg-3 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><strong class="text-primary"><?= htmlspecialchars($_SESSION['username']) ?></strong></h5>
                            <span class="badge bg-primary fs-4"><?= $equipmentCountPending ?></span>
                            <hr>
                            <a href="/AdminDashboard/pages/equipment_management.php" class="btn btn-primary">Equipment Returned</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>