<?php
require '../config.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username'])) {
    header('Location: /AdminDashboard/auth/login.php');
    exit();
}
// กำหนดหน้าเว็บปัจจุบัน
$currentPage = basename($_SERVER['PHP_SELF'], ".php");

$sql = "
    SELECT 
        loan.loanid, 
        users.username,
        equipment.equipmentname, 
        loan_details.num_requests,
        loan_details.amount,
        loan_details.status, 
        loan.loandate, 
        loan.expected_return_date,
        loan.created_at
    FROM loan
    JOIN loan_details ON loan.id = loan_details.loan_id
    JOIN users ON loan.userid = users.id
    JOIN equipment ON loan_details.equipmentid = equipment.equipmentid
    WHERE loan_details.status = 'pending'
    AND users.username = :username
    ORDER BY loan.loanid ASC
";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR);
$stmt->execute();
$loans = $stmt->fetchAll();

$uniqueLoans = [];
foreach ($loans as $loan) {
    if (!isset($uniqueLoans[$loan['loanid']])) {
        $uniqueLoans[$loan['loanid']] = $loan;
    }
}

if (isset($_GET['action']) || isset($_POST['action'])) {
    // กำหนด action จาก GET หรือ POST
    $action = isset($_GET['action']) ? $_GET['action'] : $_POST['action'];

    // กำหนด Content-Type เป็น JSON
    header('Content-Type: application/json');

    switch ($action) {
        case 'getLoanDetails':
            getLoanDetails($pdo);
            break;
        case 'searchEquipment':
            searchEquipment($pdo);
            break;
        case 'updateLoan':
            updateLoan($pdo);
            break;
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
    exit; // สิ้นสุดการทำงานหลังจากจัดการ API Request
}

// ฟังก์ชันสำหรับดึงรายละเอียดการยืม
function getLoanDetails($pdo)
{
    if (isset($_POST['loanid'])) {
        $loanid = $_POST['loanid'];

        try {
            // เตรียมคำสั่ง SQL
            $stmt = $pdo->prepare("
                SELECT loan.*, loan_details.equipmentid, equipment.equipmentname, loan_details.num_requests 
                FROM loan 
                JOIN loan_details ON loan.id = loan_details.loan_id 
                JOIN equipment ON loan_details.equipmentid = equipment.equipmentid 
                WHERE loan.loanid = :loanid
            ");
            $stmt->execute(['loanid' => $loanid]);
            $results = $stmt->fetchAll();

            if ($results) {
                $loanData = [
                    'loanid' => $results[0]['loanid'],
                    'created_at' => $results[0]['created_at'],
                    'loandate' => $results[0]['loandate'],
                    'expected_return_date' => $results[0]['expected_return_date'],
                    'equipment' => []
                ];

                foreach ($results as $row) {
                    $loanData['equipment'][] = [
                        'equipmentid' => $row['equipmentid'],
                        'equipmentname' => $row['equipmentname'],
                        'quantity' => $row['num_requests']
                    ];
                }

                echo json_encode(['status' => 'success', 'data' => $loanData]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ไม่พบข้อมูลการยืม']);
            }
        } catch (PDOException $e) {
            error_log('getLoanDetails PDOException: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'ข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()]);
        }
    } else {
        error_log('getLoanDetails: No loanid parameter');
        echo json_encode(['status' => 'error', 'message' => 'ไม่มี loanid ที่ส่งมา']);
    }
}

// ฟังก์ชันสำหรับค้นหาอุปกรณ์
function searchEquipment($pdo)
{
    if (isset($_GET['query'])) {
        $query = "%{$_GET['query']}%";

        try {
            $stmt = $pdo->prepare("
                SELECT equipmentid, equipmentname 
                FROM equipment 
                WHERE equipmentid LIKE ? OR equipmentname LIKE ? 
                LIMIT 10
            ");
            $stmt->execute([$query, $query]); // ส่งค่าพารามิเตอร์สองครั้ง
            $results = $stmt->fetchAll();

            echo json_encode(['status' => 'success', 'data' => $results]);
        } catch (PDOException $e) {
            // บันทึกข้อผิดพลาดลงใน log ของเซิร์ฟเวอร์
            error_log('searchEquipment PDOException: ' . $e->getMessage());
            // ส่งข้อความข้อผิดพลาดกลับมาเพื่อการดีบัก
            echo json_encode(['status' => 'error', 'message' => 'ข้อผิดพลาดในการค้นหาอุปกรณ์: ' . $e->getMessage()]);
        }
    } else {
        error_log('searchEquipment: No query parameter');
        echo json_encode(['status' => 'error', 'message' => 'ไม่มีคำค้นหา']);
    }
}

// ฟังก์ชันสำหรับอัปเดตการยืม
function updateLoan($pdo)
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (!isset($_POST['loanid'])) {
            echo json_encode(['status' => 'error', 'message' => 'ไม่มี loan ID']);
            exit;
        }
        // รับข้อมูลจากฟอร์ม
        $loanid = $_POST['loanid'];
        $loandate = $_POST['loandate']; // รูปแบบ 'd M Y'
        $expected_return_date = $_POST['expected_return_date']; // รูปแบบ 'd M Y'
        $equipmentids = $_POST['equipmentid'] ?? [];
        $quantities = $_POST['quantity'] ?? [];

        if (empty($loanid) || empty($loandate) || empty($expected_return_date)) {
            echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
            exit;
        }

        // แปลงวันที่เป็น 'Y-m-d'
        $loandate_db = date('Y-m-d', strtotime($loandate));
        $expected_return_date_db = date('Y-m-d', strtotime($expected_return_date));

        try {
            // เริ่ม transaction
            $pdo->beginTransaction();

            // อัปเดตข้อมูล loan
            $stmt = $pdo->prepare("
                UPDATE loan 
                SET loandate = :loandate, expected_return_date = :expected_return_date 
                WHERE loanid = :loanid
            ");
            $stmt->execute([
                'loandate' => $loandate_db,
                'expected_return_date' => $expected_return_date_db,
                'loanid' => $loanid
            ]);

            // ลบรายละเอียดการยืมเดิม
            $stmt = $pdo->prepare("
                DELETE FROM loan_details 
                WHERE loan_id = (SELECT id FROM loan WHERE loanid = :loanid)
            ");
            $stmt->execute(['loanid' => $loanid]);

            // ดึง loan_id ใหม่
            $stmt = $pdo->prepare("
                SELECT id FROM loan WHERE loanid = :loanid
            ");
            $stmt->execute(['loanid' => $loanid]);
            $loan = $stmt->fetch();
            $loan_id = $loan['id'];

            // เพิ่มรายละเอียดการยืมใหม่
            $stmt = $pdo->prepare("
                INSERT INTO loan_details (loan_id, equipmentid, num_requests) 
                VALUES (:loan_id, :equipmentid, :num_requests)
            ");

            for ($i = 0; $i < count($equipmentids); $i++) {
                $current_equipmentid = $equipmentids[$i];
                $current_quantity = intval($quantities[$i]);

                if ($current_quantity < 1) {
                    throw new Exception('จำนวนอุปกรณ์ต้องมากกว่า 0');
                }

                // ตรวจสอบจำนวนอุปกรณ์ที่มีอยู่
                $checkStmt = $pdo->prepare("
                    SELECT quantity FROM equipment WHERE equipmentid = :equipmentid
                ");
                $checkStmt->execute(['equipmentid' => $current_equipmentid]);
                $equipment = $checkStmt->fetch();

                if (!$equipment || $equipment['quantity'] < $current_quantity) {
                    throw new Exception("อุปกรณ์ ID: $current_equipmentid มีจำนวนไม่เพียงพอ");
                }

                // เพิ่มรายละเอียดการยืมใหม่
                $stmt->execute([
                    'loan_id' => $loan_id,
                    'equipmentid' => $current_equipmentid,
                    'num_requests' => $current_quantity
                ]);
            }

            // ยืนยัน transaction
            $pdo->commit();
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            // ยกเลิก transaction
            $pdo->rollBack();
            error_log('updateLoan Exception: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'การอัปเดตล้มเหลว: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Padding</title>
    <link rel="icon" type="image/x-icon" href="/AdminDashboard/assets/img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <!-- Bootstrap Datepicker CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <!-- Bootstrap Datepicker JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

    <link rel="stylesheet" href="/AdminDashboard/assets/style-client.css">
    <link rel="stylesheet" href="/AdminDashboard/assets/style-table.css">



</head>

<body>
    <?php include('../includes/nav.php') ?>

    <div class="container">
        <h2 class="my-4">Equipment Pending</h2>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item active"><a href="/AdminDashboard/index.php">Dashboard</a></li>
            <li class="breadcrumb-item active"><a href="/AdminDashboard/pages/loan_system.php">Loan System</a></li>
            <li class="breadcrumb-item">Equipment Pending</li>
        </ol>
        <div class="container d-flex justify-content-center align-items-center mt-5">
            <div class="card mb-4" style="width: 90%;">
                <div class="card-header">
                    <i class="bi bi-table"></i>
                    Equipment Pending
                </div>
                <div class="card-body">
                    <table id="datatablesSimple" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>EquipmentName</th>
                                <th>quantity</th>
                                <th>Borrowed Date</th>
                                <th>Date of Return</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($uniqueLoans as $loan): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($loan['loanid']); ?></td>
                                    <td><?php echo htmlspecialchars($loan['equipmentname']); ?></td>
                                    <td><?php echo htmlspecialchars($loan['num_requests']); ?></td>
                                    <td>
                                        <?php
                                        $loanDate = DateTime::createFromFormat('Y-m-d', $loan['loandate']);
                                        echo $loanDate ? $loanDate->format('d M Y') : 'ไม่ระบุ';
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $expectedReturnDate = DateTime::createFromFormat('Y-m-d', $loan['expected_return_date']);
                                        echo $expectedReturnDate ? $expectedReturnDate->format('d M Y') : 'ไม่ระบุ';
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center align-items-center">
                                            <button type="button" class="btn btn-info btn-sm me-1" data-bs-toggle="modal" data-bs-target="#viewModal"
                                                data-id="<?= $loan['loanid'] ?>"
                                                data-username="<?= $loan['username'] ?>"
                                                data-transaction_date="<?php
                                                                        $transaction_date = DateTime::createFromFormat('Y-m-d H:i:s', $loan['created_at']);
                                                                        echo $transaction_date ? $transaction_date->format('d M Y H:i:s') : 'ไม่ระบุ';
                                                                        ?>"
                                                data-loandate="<?php
                                                                $loanDate = DateTime::createFromFormat('Y-m-d', $loan['loandate']);
                                                                echo $loanDate ? $loanDate->format('d M Y') : 'ไม่ระบุ';
                                                                ?>"
                                                data-returndate="<?php
                                                                    $expectedReturnDate = DateTime::createFromFormat('Y-m-d', $loan['expected_return_date']);
                                                                    echo $expectedReturnDate ? $expectedReturnDate->format('d M Y') : 'ไม่ระบุ';
                                                                    ?>"
                                                data-loans='<?= json_encode($loans) ?>'>
                                                <i class="bi bi-info-circle"></i>
                                            </button>

                                            <button type="button" class="btn btn-warning btn-sm me-1 editButton"
                                                data-loanid="<?= htmlspecialchars($loan['loanid']) ?>">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                data-id="<?= $loan['loanid'] ?>">
                                                <i class="bi bi-trash"></i>
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
                    <h5 class="modal-title" id="viewModalLabel">Details of <strong class="badge text-bg-dark" id="viewid"></strong></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <td><strong>Borrower</strong></td>
                                <td><strong class="text-danger" id="viewUsername"></strong></td>
                            </tr>
                            <tr>
                                <td><strong>Transaction date</strong></td>
                                <td><span id="viewtransaction_date"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Borrowed date</strong></td>
                                <td><span id="viewloandate"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Date of return</strong></td>
                                <td><span id="viewreturndate"></span></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="container d-flex justify-content-center align-items-center">
                        <div class="w-100">
                            <table class="table table-bordered text-center">
                                <thead>
                                    <tr>
                                        <td><strong>EquipmentName</strong></td>
                                        <td><strong>Quantity</strong></td>
                                        <td><strong>Amount</strong></td>
                                        <td><strong>Status</strong></td>
                                    </tr>
                                </thead>
                                <tbody id="equipmentList">
                                    <!-- รายการอุปกรณ์จะถูกเติมที่นี่ -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="editLoanForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">แก้ไขการยืมอุปกรณ์</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="container d-flex justify-content-center align-items-center">
                            <div class="card" style="width: 100%; padding: 1rem;">
                                <h4><i class="bi bi-cart4"></i> Transaction Details</h4>

                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <label for="loanid" class="form-label">Transaction No. <span class="badge text-bg-warning">Generate Auto</span></label>
                                        <div class="input-group input-group-sm mb-3">
                                            <span class="input-group-text" id="basic-addon1"><img src="/AdminDashboard/assets/svg/123.svg"></span>
                                            <input type="text" class="form-control" id="loanid" name="loanid" placeholder="Leave empty for auto generation" aria-label="loanid" aria-describedby="basic-addon1" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="transactionDate" class="form-label">Transaction Date</label>
                                        <div class="input-group input-group-sm mb-3">
                                            <span class="input-group-text" id="basic-addon2"><i class="bi bi-calendar3"></i></span>
                                            <?php
                                            // Function to format date to "d M Y"
                                            function formatDateEnglish($dateStr)
                                            {
                                                return date('d M Y', strtotime($dateStr));
                                            }
                                            ?>
                                            <input type="text" class="form-control" id="transactionDate" name="transactionDate" value="<?php echo formatDateEnglish(date("Y-m-d")); ?>" aria-label="transactionDate" aria-describedby="basic-addon2" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Borrowed Date -->
                                    <div class="col-md-6">
                                        <label for="loandate" class="form-label">Borrowed Date</label>
                                        <div class="input-group input-group-sm mb-3">
                                            <span class="input-group-text" id="basic-addon3"><i class="bi bi-calendar3"></i></span>
                                            <input type="text" class="form-control datepicker" id="loandate" name="loandate" value="<?php echo formatDateEnglish(date('Y-m-d')); ?>" aria-label="borrowedDate" aria-describedby="basic-addon3" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="expected_return_date" class="form-label">Return Date </label>
                                        <div class="input-group input-group-sm mb-3">
                                            <span class="input-group-text" id="basic-addon4"><i class="bi bi-calendar3"></i></span>
                                            <input type="text" class="form-control datepicker" id="expected_return_date" name="expected_return_date" value="<?php echo formatDateEnglish(date('Y-m-d', strtotime('+7 days'))); ?>" aria-label="expectedReturnDate" aria-describedby="basic-addon4" readonly>
                                        </div>
                                    </div>
                                </div>

                                <!-- Search Field -->
                                <div class="mb-3 position-relative">
                                    <label for="searchEquipment" class="form-label">Search Equipment</label>
                                    <div class="input-group input-group-sm mb-3">
                                        <span class="input-group-text" id="basic-addon5"><i class="bi bi-search"></i></span>
                                        <input type="text" class="form-control" id="searchEquipment" placeholder="Find Equipment by ID or Name" aria-label="searchEquipment" aria-describedby="basic-addon5">
                                    </div>
                                    <div class="input-group input-group-sm">
                                        <div id="searchResults" class="list-group position-absolute w-100"></div>
                                    </div>

                                </div>

                                <!-- Selected Equipment Table -->
                                <table class="table table-sm" id="selectedEquipmentTable">
                                    <thead>
                                        <tr>
                                            <th>Equipment ID</th>
                                            <th>Equipment Name</th>
                                            <th>Quantity</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Rows will be added here -->
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete of <strong class="badge text-bg-dark" id="deletebyloanid"></strong></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <strong>Are you sure you want to delete the following equipment pending?</strong>
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


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const dataTable = new simpleDatatables.DataTable("#datatablesSimple", {
                searchable: true, // เปิดใช้งานการค้นหา
                fixedHeight: true, // เปิดใช้งานความสูงคงที่
                perPage: 5, // จำนวนแถวต่อหน้าเริ่มต้น
                perPageSelect: [5, 10, 15, 20], // ตัวเลือกจำนวนแถวต่อหน้า
                sortable: false, // เปิดใช้งานการเรียงลำดับ
                textContent: true,
            });
        });

        const viewModal = document.getElementById('viewModal');
        viewModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;

            const id = button.getAttribute('data-id'); // loanid ที่ต้องการแสดง
            const username = button.getAttribute('data-username');
            const transaction_date = button.getAttribute('data-transaction_date');
            const loandate = button.getAttribute('data-loandate');
            const returndate = button.getAttribute('data-returndate');

            // ค้นหา modal element
            const modalid = viewModal.querySelector('#viewid');
            const modalUsername = viewModal.querySelector('#viewUsername');
            const modaltransaction_date = viewModal.querySelector('#viewtransaction_date');
            const modalloandate = viewModal.querySelector('#viewloandate');
            const modalreturndate = viewModal.querySelector('#viewreturndate');
            const equipmentList = viewModal.querySelector('#equipmentList');

            // อัปเดตข้อมูลทั่วไป
            modalid.textContent = id;
            modalUsername.textContent = username;
            modaltransaction_date.textContent = transaction_date;
            modalloandate.textContent = loandate;
            modalreturndate.textContent = returndate;

            // ลบข้อมูลเก่าในรายการอุปกรณ์
            equipmentList.innerHTML = '';

            // ดึงข้อมูลอุปกรณ์ที่เกี่ยวข้องกับ loanid นั้นๆ
            const loans = JSON.parse(button.getAttribute('data-loans'));

            // กรองข้อมูล loan ตาม loanid ที่คลิกมา
            const filteredLoans = loans.filter(loan => loan.loanid === id);

            // แสดงรายการอุปกรณ์ทั้งหมดที่เกี่ยวข้องกับ loanid
            filteredLoans.forEach(loan => {
                const row = document.createElement('tr');
                row.innerHTML = `
            <td>${loan.equipmentname}</td>
            <td>${loan.num_requests}</td>
            <td>${loan.amount}</td>
            <td class="badge text-bg-secondary fs-6">${loan.status}</td>
        `;
                equipmentList.appendChild(row);
            });
        });

        const deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;

            const loanid = button.getAttribute('data-id');

            const modalLoanid = deleteModal.querySelector('#deletebyloanid');

            modalLoanid.textContent = loanid;

            const form = deleteModal.querySelector('#deleteForm');
            form.action = 'delete_equipment_pending.php?loanid=' + loanid;
        });
    </script>

    <script>
        $(document).ready(function() {
            // Initialize datepickers
            $('.datepicker').datepicker({
                format: 'dd M yyyy',
                autoclose: true,
                todayHighlight: true
            });

            // Function to open modal and load data
            function openEditModal(loanid) {
                $.ajax({
                    url: '?action=getLoanDetails',
                    type: 'POST',
                    data: {
                        loanid: loanid
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log('getLoanDetails response:', response); // Debug
                        if (response.status === 'success') {
                            $('#loanid').val(response.data.loanid);
                            $('#created_at').val(formatDateTime(response.data.created_at));
                            $('#loandate').val(formatDate(response.data.loandate));
                            $('#expected_return_date').val(formatDate(response.data.expected_return_date));

                            // Populate selected equipment table
                            $('#selectedEquipmentTable tbody').empty();
                            $.each(response.data.equipment, function(index, item) {
                                addEquipmentRow(item);
                            });

                            $('#editModal').modal('show');
                        } else {
                            alert('ไม่สามารถโหลดข้อมูลได้: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error (getLoanDetails):', status, error);
                        alert('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
                    }
                });
            }

            // Format datetime
            function formatDateTime(datetime) {
                var date = new Date(datetime);
                var options = {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                };
                return date.toLocaleDateString('en-GB', options).replace(',', '');
            }

            // Format date
            function formatDate(date) {
                var d = new Date(date);
                var options = {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                };
                return d.toLocaleDateString('en-GB', options);
            }

            // Search Equipment
            $('#searchEquipment').on('input', function() {
                var query = $(this).val();
                if (query.length >= 1) {
                    $.ajax({
                        url: '?action=searchEquipment',
                        type: 'GET',
                        data: {
                            query: query
                        }, // ส่งเฉพาะ query
                        dataType: 'json',
                        success: function(response) {
                            console.log('searchEquipment response:', response); // Debug
                            if (response.status === 'success') {
                                var searchResults = $('#searchResults');
                                searchResults.empty();
                                if (response.data.length > 0) {
                                    $.each(response.data, function(index, equipment) {
                                        searchResults.append('<button type="button" class="list-group-item list-group-item-action" data-id="' + equipment.equipmentid + '" data-name="' + equipment.equipmentname + '">' + equipment.equipmentid + ' - ' + equipment.equipmentname + '</button>');
                                    });
                                } else {
                                    searchResults.append('<div class="list-group-item">ไม่พบอุปกรณ์</div>');
                                }
                            } else {
                                alert('ค้นหาอุปกรณ์ล้มเหลว: ' + response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error (searchEquipment):', status, error);
                            alert('เกิดข้อผิดพลาดในการค้นหาอุปกรณ์');
                        }
                    });
                } else {
                    $('#searchResults').empty();
                }
            });

            // Select Equipment from list
            $('#searchResults').on('click', '.list-group-item', function() {
                var equipmentid = $(this).data('id');
                var equipmentname = $(this).data('name');
                addEquipmentRow({
                    equipmentid: equipmentid,
                    equipmentname: equipmentname,
                    quantity: 1
                });
                $('#searchResults').empty();
                $('#searchEquipment').val('');
            });

            // Add Equipment to table
            function addEquipmentRow(equipment) {
                var exists = false;
                $('#selectedEquipmentTable tbody tr').each(function() {
                    if ($(this).find('.equipmentid').val() === equipment.equipmentid) {
                        exists = true;
                        alert('อุปกรณ์นี้ถูกเพิ่มไปแล้ว');
                        return false;
                    }
                });
                if (!exists) {
                    var row = '<tr>' +
                        '<td>' + equipment.equipmentname + '</td>' +
                        '<td>' + equipment.equipmentid + '<input type="hidden" class="equipmentid" name="equipmentid[]" value="' + equipment.equipmentid + '"></td>' +
                        '<td><input type="number" class="form-control quantity" name="quantity[]" value="' + equipment.quantity + '" min="1" required></td>' +
                        '<td><button type="button" class="btn btn-danger btn-sm deleteRow">Delete</button></td>' +
                        '</tr>';
                    $('#selectedEquipmentTable tbody').append(row);
                }
            }

            // Delete Equipment Row
            $('#selectedEquipmentTable').on('click', '.deleteRow', function() {
                $(this).closest('tr').remove();
            });

            // Handle form submission
            $('#editLoanForm').on('submit', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();

                $.ajax({
                    url: '?action=updateLoan',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        console.log('updateLoan response:', response); // Debug
                        if (response.status === 'success') {
                            alert('การแก้ไขสำเร็จ');
                            $('#editModal').modal('hide');
                            location.reload(); // Reload to show updated data
                        } else {
                            alert('การแก้ไขล้มเหลว: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error (updateLoan):', status, error);
                        alert('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
                    }
                });
            });

            // Open modal when clicking an edit button
            $(document).on('click', '.editButton', function() {
                var loanid = $(this).data('loanid');
                openEditModal(loanid);
            });
        });
    </script>
</body>

</html>