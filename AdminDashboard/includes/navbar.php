<style>
    /* เพิ่มสไตล์สำหรับเมนูที่ active */
    .nav-link.active b {
        color: #f8f9fa !important;
        /* สีข้อความหลัก */
        border-bottom: 2px solid #f8f9fa;
        /* เส้นใต้ */
    }

    /* เปลี่ยนสีข้อความเมื่อ hover */
    .nav-link:hover {
        color: #f8f9fa !important;
    }

    /* ทำให้เส้นใต้อยู่กับ <b> */
    .nav-link.active b {
        display: inline-block;
        padding-bottom: 2px;
        /* เว้นระยะห่างระหว่างข้อความกับเส้นใต้ */
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <strong class="navbar-brand">
            <img src="/AdminDashboard/assets/img/favicon.ico" alt="" width="30"> <!-- BorrowEquipmentSystems -->
        </strong>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <!-- เมนู Home -->
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage == 'index') ? 'active' : ''; ?>" href="/AdminDashboard/index.php">
                        <?php echo ($currentPage == 'index') ? '<b>Home</b>' : 'Home'; ?>
                    </a>
                </li>
                <!-- เมนู Equipment List -->
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage == 'equipment_list') ? 'active' : ''; ?>" href="/AdminDashboard/pages/equipment_list.php">
                        <?php echo ($currentPage == 'equipment_list') ? '<b>Equipment List</b>' : 'Equipment List'; ?>
                    </a>
                </li>
                <!-- เมนู Loan System -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php
                                                        // ตรวจสอบว่าเป็นหน้าใดใน Loan System หรือไม่
                                                        $loanPages = ['loan_system', 'add_borrow', 'equipment_pending', 'equipment_approve', 'equipment_unreturned'];
                                                        $isLoanActive = false;
                                                        foreach ($loanPages as $page) {
                                                            if ($currentPage == $page) {
                                                                $isLoanActive = true;
                                                                break;
                                                            }
                                                        }
                                                        echo ($isLoanActive) ? 'active' : '';
                                                        ?>" href="#" id="loanDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo ($isLoanActive) ? '<b>Loan System</b>' : 'Loan System'; ?>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="loanDropdown">
                        <li>
                            <a class="dropdown-item <?php echo ($currentPage == 'loan_system') ? 'active' : ''; ?>" href="/AdminDashboard/pages/loan_system.php">
                                <?php echo ($currentPage == 'loan_system') ? '<b>Add Borrow</b>' : 'Add Borrow'; ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?php echo ($currentPage == 'equipment_pending') ? 'active' : ''; ?>" href="/AdminDashboard/pages/equipment_pending.php">
                                <?php echo ($currentPage == 'equipment_pending') ? '<b>Equipment pending</b>' : 'Equipment pending'; ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?php echo ($currentPage == 'equipment_approve') ? 'active' : ''; ?>" href="/AdminDashboard/pages/equipment_approve.php">
                                <?php echo ($currentPage == 'equipment_approve') ? '<b>Equipment Approve</b>' : 'Equipment Approve'; ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?php echo ($currentPage == 'equipment_unreturned') ? 'active' : ''; ?>" href="/AdminDashboard/pages/equipment_unreturned.php">
                                <?php echo ($currentPage == 'equipment_unreturned') ? '<b>Equipment Un-Returned Items</b>' : 'Equipment Un-Returned Items'; ?>
                            </a>
                        </li>
                    </ul>
                </li>
                <!-- เมนู Return System -->
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage == 'return_system') ? 'active' : ''; ?>" href="/AdminDashboard/pages/return_system.php">
                        <?php echo ($currentPage == 'return_system') ? '<b>Return System</b>' : 'Return System'; ?>
                    </a>
                </li>
                <!-- เมนูผู้ใช้ -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo (in_array($currentPage, ['setting_user'])) ? 'active' : ''; ?>" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <strong class="text-primary"><?= htmlspecialchars($_SESSION['username']) ?></strong>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item <?php echo ($currentPage == 'setting_user') ? 'active' : ''; ?>" href="/AdminDashboard/pages/setting_user.php">
                                <?php echo ($currentPage == 'setting_user') ? '<b>Setting</b>' : 'Setting'; ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/AdminDashboard/auth/logout.php">Logout</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>