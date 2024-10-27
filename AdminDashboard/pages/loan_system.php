<?php

require '../config.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username'])) {
    header('Location: /AdminDashboard/auth/login.php');
    exit();
}

$currentPage = basename($_SERVER['PHP_SELF'], ".php");

// Determine action from request
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if ($action === 'search') {
    // Handle equipment search
    header('Content-Type: application/json');
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';

    if ($q === '') {
        echo json_encode([]);
        exit;
    }

    try {
        $sql = "SELECT id, equipmentid, equipmentname, quantity FROM equipment WHERE equipmentid LIKE :query OR equipmentname LIKE :query LIMIT 10";
        $stmt = $pdo->prepare($sql);
        $searchTerm = "%$q%";
        $stmt->bindParam(':query', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($results);
    } catch (PDOException $e) {
        echo json_encode([]);
    }
    exit;
}

if ($action === 'save') {
    // Handle saving the loan
    header('Content-Type: application/json');

    // Retrieve loan data from POST
    $loanData = isset($_POST['loan']) ? $_POST['loan'] : [];
    $borrowedDate = isset($_POST['borrowedDate']) ? $_POST['borrowedDate'] : date('Y-m-d');
    $expectedReturnDate = isset($_POST['expectedReturnDate']) ? $_POST['expectedReturnDate'] : date('Y-m-d', strtotime('+7 days'));

    if (empty($loanData)) {
        echo json_encode(['success' => false, 'message' => 'No loan data provided']);
        exit;
    }

    // Example of retrieving userid from session or authentication system
    // Adjust this according to your actual authentication mechanism
    $userid = $_SESSION['user_id'];

    try {
        // Begin Transaction
        $pdo->beginTransaction();

        // Create new loanid in the format 'LNMMYY-XXXX'
        $currentMonth = date('m');
        $currentYear = date('y'); // Two-digit year
        $prefix = 'LN' . $currentMonth . $currentYear . '-';

        // Find the last loanid for the current month and year
        $sql = "SELECT loanid FROM loan WHERE loanid LIKE :prefix ORDER BY loanid DESC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':prefix' => $prefix . '%']);
        $lastLoan = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($lastLoan) {
            // Extract the sequence number from the last loanid and increment by 1
            $lastNumber = intval(substr($lastLoan['loanid'], strlen($prefix)));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        // Pad the sequence number to 4 digits with leading zeros
        $newNumberPadded = str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        // Create the new loanid
        $loanid = $prefix . $newNumberPadded;

        // Insert the main loan record (ไม่ต้องเพิ่มฟิลด์ status)
        $sql = "INSERT INTO loan (loanid, userid, loandate, expected_return_date) VALUES (:loanid, :userid, :loandate, :expected_return_date)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':loanid' => $loanid,
            ':userid' => $userid,
            ':loandate' => $borrowedDate,
            ':expected_return_date' => $expectedReturnDate
        ]);

        // Get the ID of the newly created loan
        $loan_id = $pdo->lastInsertId();

        // Insert each loan detail
        $sql_detail = "INSERT INTO loan_details (loan_id, equipmentid, num_requests, amount, status) VALUES (:loan_id, :equipmentid, :num_requests, :amount, :status)";
        $stmt_detail = $pdo->prepare($sql_detail);

        foreach ($loanData as $item) {
            $equipmentid = $item['equipmentid'];
            $num_requests = intval($item['quantity']); // เปลี่ยนชื่อจาก quantity เป็น num_requests
            $amount = 0; // กำหนดค่าเริ่มต้น หรือสามารถคำนวณได้ตามความต้องการ

            // Check available quantity of the equipment
            $sql_check = "SELECT quantity FROM equipment WHERE equipmentid = :equipmentid";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([':equipmentid' => $equipmentid]);
            $equipment = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if (!$equipment) {
                throw new Exception("Equipment with ID: $equipmentid not found");
            }

            if ($equipment['quantity'] < $num_requests) {
                throw new Exception("Equipment $equipmentid has insufficient quantity (Available: {$equipment['quantity']}, Requested: $num_requests)");
            }

            // *** ลบส่วนการลดจำนวนในตาราง equipment ***
            // ไม่ต้องลดจำนวนในตาราง equipment เพราะคุณต้องการแค่บันทึกข้อมูลการยืม

            // Insert loan detail with status 'pending'
            $stmt_detail->execute([
                ':loan_id' => $loan_id,
                ':equipmentid' => $equipmentid,
                ':num_requests' => $num_requests,
                ':amount' => $amount,
                ':status' => 'pending'
            ]);
        }

        // Commit Transaction
        $pdo->commit();

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Rollback Transaction in case of error
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}



if ($action === 'generateLoanId') {
    // Handle generating Loan ID in the format 'LMMYY-XXXX'
    header('Content-Type: application/json');
    try {
        // Get current month and year
        $currentMonth = date('m');
        $currentYear = date('y'); // Two-digit year
        $prefix = 'LN' . $currentMonth . $currentYear . '-';

        // Find the last loanid for the current month-year
        $sql = "SELECT loanid FROM loan WHERE loanid LIKE :prefix ORDER BY loanid DESC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':prefix' => $prefix . '%']);
        $lastLoan = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($lastLoan) {
            // Extract the number part and increment
            $lastNumber = intval(substr($lastLoan['loanid'], strlen($prefix)));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        // Pad the number with leading zeros
        $newNumberPadded = str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        // Create the new loanid
        $newLoanId = $prefix . $newNumberPadded;

        echo json_encode(['success' => true, 'loanid' => $newLoanId]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to generate Loan ID']);
    }
    exit;
}

// If no action is specified, display the HTML frontend
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan System</title>
    <link rel="icon" type="image/x-icon" href="/AdminDashboard/assets/img/favicon.ico">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Bootstrap Datepicker CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="/AdminDashboard/assets/style-client.css">

    <!-- Bootstrap 5 JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap Datepicker JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

    <style>
        /* Adjust the position of the search results */
        #searchResults {
            z-index: 1000;
            max-height: 200px;
            overflow-y: auto;
        }

        /* Style for no results and error messages */
        .no-results,
        .error-message {
            color: #6c757d;
            /* Gray color */
            cursor: default;
        }
    </style>
</head>

<body>
    <?php include('../includes/nav.php') ?>

    <div class="container">
        <h2 class="my-4">Loan System</h2>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item active"><a href="/AdminDashboard/index.php">Dashboard</a></li>
            <li class="breadcrumb-item">Loan System</li>
        </ol>
        <div class="container d-flex justify-content-center align-items-center mt-5">
            <div class="card" style="width: 100%; padding: 1rem;">
                <h4><i class="bi bi-cart4"></i> Transaction Details</h4>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label for="loanid" class="form-label">Transaction No. <span class="badge text-bg-warning">Generate Auto</span></label>
                        <div class="input-group input-group-sm mb-3">
                            <span class="input-group-text" id="basic-addon1"><img src="/AdminDashboard/assets/svg/123.svg"></span>
                            <input type="text" class="form-control" id="loanid" name="loanid" placeholder="Leave empty for auto generation" aria-label="loanid" aria-describedby="basic-addon1" disabled>
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
                            <input type="text" class="form-control" id="transactionDate" name="transactionDate" value="<?php echo formatDateEnglish(date("Y-m-d")); ?>" aria-label="transactionDate" aria-describedby="basic-addon2" disabled>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Borrowed Date -->
                    <div class="col-md-6">
                        <label for="borrowedDate" class="form-label">Borrowed Date</label>
                        <div class="input-group input-group-sm mb-3">
                            <span class="input-group-text" id="basic-addon3"><i class="bi bi-calendar3"></i></span>
                            <input type="text" class="form-control" id="borrowedDate" name="borrowedDate" value="<?php echo formatDateEnglish(date('Y-m-d')); ?>" aria-label="borrowedDate" aria-describedby="basic-addon3" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="expectedReturnDate" class="form-label">Return Date <span class="badge text-bg-warning">+3 days</span></label>
                        <div class="input-group input-group-sm mb-3">
                            <span class="input-group-text" id="basic-addon4"><i class="bi bi-calendar3"></i></span>
                            <input type="text" class="form-control" id="expectedReturnDate" name="expectedReturnDate" value="<?php echo formatDateEnglish(date('Y-m-d', strtotime('+3 days'))); ?>" aria-label="expectedReturnDate" aria-describedby="basic-addon4" readonly>
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

                <!-- Save Loan Button -->
                <div class="input-group mb-3 d-flex justify-content-end">
                    <button class="btn btn-success" id="saveLoan">Save Loan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom JS -->
    <script>
        $(document).ready(function() {
            // Initialize Bootstrap Datepicker with desired format
            $('#borrowedDate, #expectedReturnDate').datepicker({
                format: 'd M yyyy',
                startDate: new Date(),
                autoclose: true,
                todayHighlight: true
            });

            // Function to generate Loan ID when the page loads
            function generateLoanId() {
                $.ajax({
                    url: 'loan_system.php',
                    method: 'GET',
                    data: {
                        action: 'generateLoanId'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#loanid').val(response.loanid);
                        } else {
                            $('#loanid').val('Error');
                        }
                    },
                    error: function() {
                        $('#loanid').val('Error');
                    }
                });
            }

            // Call the function to generate Loan ID on page load
            generateLoanId();

            // Function to format date to "d M Y"
            function formatDateEnglish(dateStr) {
                const date = new Date(dateStr);
                const options = {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                };
                return date.toLocaleDateString('en-GB', options).replace(/,/g, '');
            }

            // Function to convert "d M yyyy" to "Y-m-d"
            function convertDateToYMD(dateStr) {
                const [day, monthStr, year] = dateStr.split(' ');
                const month = new Date(Date.parse(monthStr + " 1, 2020")).getMonth() + 1;
                const monthPadded = month < 10 ? '0' + month : month;
                return `${year}-${monthPadded}-${day < 10 ? '0' + day : day}`;
            }

            // Handle Equipment Search
            $('#searchEquipment').on('input', function() {
                let query = $(this).val().trim();
                if (query.length < 1) {
                    $('#searchResults').empty();
                    return;
                }
                $.ajax({
                    url: '?action=search',
                    method: 'GET',
                    data: {
                        q: query
                    },
                    dataType: 'json',
                    success: function(data) {
                        $('#searchResults').empty();
                        if (data.length > 0) {
                            data.forEach(function(equipment) {
                                $('#searchResults').append(`
                            <button type="button" class="list-group-item list-group-item-action" data-id="${equipment.equipmentid}" data-name="${equipment.equipmentname}" data-quantity="${equipment.quantity}">
                                ${equipment.equipmentid} - ${equipment.equipmentname}
                            </button>
                        `);
                            });
                        } else {
                            $('#searchResults').append('<div class="list-group-item no-results">No equipment found</div>');
                        }
                    },
                    error: function() {
                        $('#searchResults').empty();
                        $('#searchResults').append('<div class="list-group-item error-message">An error occurred while searching</div>');
                    }
                });
            });

            // Handle Selecting Equipment from Search Results
            $('#searchResults').on('click', '.list-group-item-action', function() {
                let equipmentName = $(this).data('name');
                let equipmentId = $(this).data('id');
                let maxQuantity = $(this).data('quantity');

                // Check if the equipment is already in the table
                if ($('#selectedEquipmentTable tbody').find(`tr[data-id="${equipmentId}"]`).length > 0) {
                    alert('This equipment has already been added');
                    return;
                }

                // Add a new row to the table
                $('#selectedEquipmentTable tbody').append(`
            <tr data-id="${equipmentId}">
                <td>${equipmentId}</td>
                <td>${equipmentName}</td>
                <td>
                    <input type="number" class="form-control quantity-input" min="1" max="${maxQuantity}" value="1">
                </td>
                <td class="text-center">
                    <button class="btn btn-danger btn-sm delete-btn"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
        `);

                // Clear the search results and search input
                $('#searchResults').empty();
                $('#searchEquipment').val('');
            });

            // Handle Deleting Equipment from the Table
            $('#selectedEquipmentTable').on('click', '.delete-btn', function() {
                $(this).closest('tr').remove();
            });

            // Handle Saving the Loan
            $('#saveLoan').on('click', function() {
                let loanData = [];
                $('#selectedEquipmentTable tbody tr').each(function() {
                    let equipmentId = $(this).data('id');
                    let quantity = $(this).find('.quantity-input').val();
                    loanData.push({
                        equipmentid: equipmentId,
                        quantity: quantity
                    });
                });

                if (loanData.length === 0) {
                    alert('No equipment selected for loan');
                    return;
                }

                // Get dates from fields and convert to "Y-m-d"
                let borrowedDateStr = $('#borrowedDate').val();
                let expectedReturnDateStr = $('#expectedReturnDate').val();

                // Convert to "Y-m-d" format for backend
                let borrowedDate = convertDateToYMD(borrowedDateStr);
                let expectedReturnDate = convertDateToYMD(expectedReturnDateStr);

                // Send data to Backend to save the loan
                $.ajax({
                    url: '?action=save',
                    method: 'POST',
                    data: {
                        loan: loanData,
                        borrowedDate: borrowedDate,
                        expectedReturnDate: expectedReturnDate
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Loan saved successfully');
                            $('#selectedEquipmentTable tbody').empty();
                            // Refresh Transaction No.
                            generateLoanId();
                            // Reset dates
                            $('#transactionDate').val(formatDateEnglish('<?php echo date("Y-m-d"); ?>'));
                            $('#borrowedDate').val(formatDateEnglish('<?php echo date("Y-m-d"); ?>'));
                            $('#expectedReturnDate').val(formatDateEnglish('<?php echo date("Y-m-d", strtotime("+7 days")); ?>'));
                            // Redirect to equipment_pending.php
                            window.location.href = '/AdminDashboard/pages/equipment_pending.php';
                        } else {
                            alert('Error saving loan: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('An error occurred while saving the loan');
                    }
                });
            });
        });
    </script>
</body>

</html>