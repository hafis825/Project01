<style>
    .container {
        max-width: 960px;
    }

    .site-header {
        background-color: rgba(0, 0, 0, .85);
        -webkit-backdrop-filter: saturate(180%) blur(20px);
        backdrop-filter: saturate(180%) blur(20px);
    }

    .site-header a {
        color: #999;
        transition: ease-in-out color .15s;
    }

    .site-header a:hover {
        color: #fff;
        text-decoration: none;
    }

    .nav-link.active b {
        color: #f8f9fa !important;
        border-bottom: 2px solid #f8f9fa;
    }

    .nav-link:hover {
        color: #f8f9fa !important;
    }

    .nav-link.active b {
        display: inline-block;
        padding-bottom: 2px;
    }

    /* จัดลิงก์ให้อยู่ตรงกลางและเว้นระยะห่าง */
    .navbar-nav {
        margin: 0 auto;
        /* จัดให้อยู่ตรงกลาง */
        gap: 25px;
        /* ระยะห่างระหว่างลิงก์ */
    }

    .navbar-nav .nav-item {
        display: inline-block;
        /* แสดงลิงก์แต่ละตัวในบรรทัดเดียวกัน */
    }
</style>

<nav class="navbar navbar-expand-lg site-header sticky-top py-1">
    <div class="container-fluid">

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <strong class="nav-link">
                        <img src="/AdminDashboard/assets/img/favicon.ico" width="24" height="24">
                    </strong>
                </li>

                <?php
                // จัดลำดับเมนูใหม่
                $menuItems = [
                    'home' => 'Home',
                    'equipment_list' => 'Equipment List'
                ];

                // หน้าในระบบการยืม
                $loanPages = ['loan_system', 'equipment_pending', 'equipment_approve', 'equipment_unreturned'];

                // แสดง Home และ Equipment List ก่อน
                foreach ($menuItems as $page => $label) {
                    echo '<li class="nav-item">';
                    echo '<a class="nav-link ' . ($currentPage == $page ? 'active' : '') . '" href="/AdminDashboard/pages/' . $page . '.php">';
                    echo ($currentPage == $page ? '<b>' . $label . '</b>' : $label);
                    echo '</a></li>';
                }

                // Dropdown สำหรับ Loan System
                echo '<li class="nav-item dropdown">';
                echo '<a class="nav-link dropdown-toggle ' . (in_array($currentPage, $loanPages) ? 'active' : '') . '" 
                        href="#" id="loanDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">';
                echo (in_array($currentPage, $loanPages) ? '<b>Loan System</b>' : 'Loan System');
                echo '</a>';
                echo '<ul class="dropdown-menu" aria-labelledby="loanDropdown">';
                foreach ($loanPages as $page) {
                    echo '<li><a class="dropdown-item ' . ($currentPage == $page ? 'active' : '') . '" href="/AdminDashboard/pages/' . $page . '.php">';
                    echo ($currentPage == $page ? '<b>' . ucfirst(str_replace('_', ' ', $page)) . '</b>' : ucfirst(str_replace('_', ' ', $page)));
                    echo '</a></li>';
                }
                echo '</ul></li>';

                // เพิ่ม Return System หลัง Loan System
                echo '<li class="nav-item">';
                echo '<a class="nav-link ' . ($currentPage == 'return_system' ? 'active' : '') . '" href="/AdminDashboard/pages/return_system.php">';
                echo ($currentPage == 'return_system' ? '<b>Return System</b>' : 'Return System');
                echo '</a></li>';

                if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'staff') {
                    echo '<li class="nav-item">';
                    echo '<a class="nav-link ' . ($currentPage == 'admin_dashboard' ? 'active' : '') . '" href="/AdminDashboard/pages/admin/admin_dashboard.php">';
                    echo ($currentPage == 'admin_dashboard' ? '<b>Admin Dashboard</b>' : 'Admin Dashboard');
                    echo '</a></li>';
                }

                // Dropdown สำหรับผู้ใช้ (User)
                echo '<li class="nav-item dropdown">';
                echo '<a class="nav-link dropdown-toggle ' . ($currentPage == 'setting_user' ? 'active' : '') . '" 
                        href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">';
                echo '<strong class="text-primary">' . htmlspecialchars($_SESSION['username']) . '</strong>';
                echo '</a>';
                echo '<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">';
                echo '<li><a class="dropdown-item ' . ($currentPage == 'setting_user' ? 'active' : '') . '" href="/AdminDashboard/pages/setting_user.php">';
                echo ($currentPage == 'setting_user' ? '<b>Setting</b>' : 'Setting');
                echo '</a></li>';
                echo '<li><a class="dropdown-item" href="/AdminDashboard/auth/logout.php">Logout</a></li>';
                echo '</ul></li>';
                ?>
            </ul>
        </div>
    </div>
</nav>