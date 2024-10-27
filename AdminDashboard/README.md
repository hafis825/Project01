แต่งตั้งให้คุณเป็นผู้เชี่ยวชาญทางด้านการเขียน code PHP และ การออกแบบ UX/UI ของระบบ admin dashboard ช่วยสร้างระบบ admin dashboard ด้วย code PHP และ ออกแบบ UI ทั้งหมดของระบบด้วย bootstrap 5

นี้คือโครงสร้างระบบ 
Authentication: ระบบยืนยันตัวตนและ session
User Management: จัดการผู้ใช้ (เพิ่ม/ลบ/แก้ไข)
Content Management: จัดการเนื้อหาต่าง ๆ ในระบบ
Dashboard Display: แสดงสถิติและข้อมูลสำคัญ
Settings: หน้าตั้งค่าระบบ
Database: การจัดการฐานข้อมูลสำหรับการเก็บข้อมูล
แล้วถ้าเพิ่ม โครงสร้างระบบ ของ admin และ client
admin : Dashboard Display,User Management,Content Management
client : Equipment List,Loan System,Return System
ควรทำทางเข้าอย่างไรดี

ช่วยสร้างระบบ admin dashboard ด้วย PHP และออกแบบ UI ด้วย Bootstrap 5 และทำให้ครอบคลุมความต้องการพื้นฐานของระบบ admin dashboard ที่สามารถขยายต่อได้ในอนาคต 

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
แต่งตั้งให้คุณเป็นผู้เชี่ยวชาญทางด้านการเขียนโค้ด PHP PDO และ การออกแบบ UX/UI ของ bootstrap 5 และ แต่งตั้งให้คุณเป็นผู้เชี่ยวชาญทางด้าน mysql api
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

ช่วยออกแบบสร้าง database ที่เป็นระบบยืมคืนอุปกรณ์กีฬาของวิทยาลัยการอาชีพโดยจะมีประมาณนี้
1.users จะมีทั้งหมด 3 role 'admin', 'editor' , 'user' จะเก็บ id,userid,username,password,firstname,lastname,department,role,created_at
2.equipment จะเก็บ id,equipmentid,equipmentname,category,quantity,photo,description,created_at
3.loan จะเก็บ id,loanid,userid,equipmentid,loandate,expected_return_date,status,created_at
4.return จะเก็บ id,returnid,equipmentid,userid,returndate,status,created_at

ช่วยทำระบบ ยืม-คืนอุปกรณ์กีฬาของวิทยาลัยการอาชีพ
เดียวจะอธิบายระบบคราวๆ เริ่มแรกจากกรอกข้อมูล 
1.(loanid)เลขที่ทำรายการ **ส่วนนี้ไม่ต้องกรอกจะป้อนอัตโนมัติ เริ่มจาก "E0001"
2.(loandate)วันที่ยืม **ส่วนนี้ไม่ต้องกรอกเราจะป้อนอัตโนมัติเป็นวันที่ทำรายการ
3.(expected_return_date)กำหนดคืน **ส่วนนี้ต้องกรอกวันที่ที่ต้องการคืนอุปกรณ์
4.(ช่วยเติมหน่อยว่าต้องใช้อะไร)จำนวน **ส่วนนี้สามารถเลือกได้จะยืมกี่ชิ้น
5.(equipmentid)ชื่ออุปกรณ์กีฬา/หมายเลขอุปกรณ์ **ส่วนนี้จะเป็นช่องกรอก ชื่ออุปกรณ์กีฬา/หมายเลขอุปกรณ์ เพื่อนำมาแสดงข้อมูล เพื่อที่จะได้เลือกอุปกรณ์ที่ต้องการยืมได้
แล้วจะต้องมีเจ้าหน้าที่ role 'admin','editor' มาทำการยืนยันก่อนที่จะได้รับอุปกรณ์และระบบจะบันทึกวันที่ยืม

flex: 1;
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

Database : admin_dashboard

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'editor') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE equipment (
    id INT PRIMARY KEY AUTO_INCREMENT,
    e_ID VARCHAR(13) NOT NULL,
    e_Name VARCHAR(50) NOT NULL,
    e_category ENUM('football', 'volleyball', 'table_tennis') NOT NULL,
    title VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

โครงสร้างโฟลเดอร์:

/admin-dashboard
   /assets (เก็บไฟล์ CSS, JS, รูปภาพ)
   /includes (ไฟล์ที่ใช้ร่วมกัน เช่น header, footer, navigation)
   /pages (ไฟล์เพจหลัก เช่น dashboard, user management)
   /auth (ไฟล์เกี่ยวกับการยืนยันตัวตน เช่น login, register)
   /database (การเชื่อมต่อฐานข้อมูล)
   /config.php (การตั้งค่าเบื้องต้น)
   /index.php (หน้าหลัก)

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

Database : equipment_loan_system

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userid VARCHAR(50) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    department VARCHAR(50),
    role ENUM('admin', 'editor', 'user') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipmentid VARCHAR(50) NOT NULL UNIQUE,
    equipmentname VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    quantity INT NOT NULL DEFAULT 1,
    photo VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE loan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loanid VARCHAR(50) UNIQUE NOT NULL,
    userid INT NOT NULL,
    loandate DATE DEFAULT CURRENT_DATE,
    expected_return_date DATE NOT NULL,
    quantity INT NOT NULL, -- จำนวนอุปกรณ์ที่ยืม
    status ENUM('pending', 'borrowed', 'returned', 'overdue') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    confirmed_by INT, -- เก็บ userid ของเจ้าหน้าที่ที่ยืนยัน
    FOREIGN KEY (userid) REFERENCES users(id),
    FOREIGN KEY (confirmed_by) REFERENCES users(id)
);


CREATE TABLE return (
    id INT AUTO_INCREMENT PRIMARY KEY,
    returnid VARCHAR(50) NOT NULL UNIQUE,
    loanid INT NOT NULL,  -- เชื่อมโยงไปยังตาราง loan
    equipmentid INT NOT NULL,
    userid INT NOT NULL,
    returndate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('returned', 'damaged', 'lost') DEFAULT 'returned',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (loanid) REFERENCES loan(id),
    FOREIGN KEY (equipmentid) REFERENCES equipment(id),
    FOREIGN KEY (userid) REFERENCES users(id)
);

CREATE TABLE loan_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id INT NOT NULL,
    equipmentid VARCHAR(50) NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (loan_id) REFERENCES loan(id) ON DELETE CASCADE,
    FOREIGN KEY (equipmentid) REFERENCES equipment(equipmentid)
);



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

Category
ฟุตบอล Football
บาสเก็ตบอล Basketball
วอลเลย์บอล Volleyball
ตะกร้อ Takraw
แบดมินตัน Badminton
ปิงปอง TableTennis
หมากฮอส Checkers
บิงโก Bingo

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


ช่วยทำระบบ ยืม-คืนอุปกรณ์กีฬาของวิทยาลัยการอาชีพ
เริ่มจากหน้าแรก 'loan_system.php' 
นี้คือ database : CREATE TABLE loan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loanid VARCHAR(50) UNIQUE NOT NULL,
    userid INT NOT NULL,
    equipmentid INT NOT NULL,
    loandate DATE DEFAULT CURRENT_DATE,
    expected_return_date DATE NOT NULL,
    quantity INT NOT NULL, -- จำนวนอุปกรณ์ที่ยืม
    status ENUM('pending', 'borrowed', 'returned', 'overdue') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    confirmed_by INT, -- เก็บ userid ของเจ้าหน้าที่ที่ยืนยัน
    FOREIGN KEY (userid) REFERENCES users(id),
    FOREIGN KEY (equipmentid) REFERENCES equipment(id),
    FOREIGN KEY (confirmed_by) REFERENCES users(id) -- เชื่อมกับเจ้าหน้าที่ที่ยืนยันการยืม
);

เดียวจะอธิบายระบบคราวๆเริ่มแรกจากกรอก รายละเอียดการทำรายการ 
• (loanid)เลขที่ทำรายการ **ตรงนี้ไม่ต้องกรอก ระบบจะป้อนอัตโนมัติ เริ่มจาก Lตามด้วย ปี, เดือน แล้วก็ ลำดับการยืม เช่น "L1024-0001"
• (loandate)วันที่ยืม **สามารถเลือกได้ว่าจะยืมวันอะไร
• (expected_return_date)กำหนดคืน **สามารถเลือกได้ว่าจะคืนวันอะไร แต่ห้ามเลือกก่อนวันที่ยืม
แล้วก็ถึงวิธีการหาอุปกรณ์ จะมีช่องสำหรับค้นหาโดยจะสามารถค้นหาได้จากการเขียน [equipmentid] และ [equipmentname] แล้วข้อมูลก็จะขึ้นโชว์เป็น select มาให้เลือกภายในช่องค้นหา พอได้เลือกแล้วข้อมูลจะโชว์เป็น ตาราง โดยจะโชว์ equipmentname , equipmentid , quantity(สามารถเลือกได้ว่าจะยืมกี่ชิ้น) ,  button Delete(เป็นการลบข้อมูลที่โชว์ในตาราง)


จะอธิบายให้นะ คือ จะค้นหาจาก equipmentid และ equipmentname ซึ่งมาจาก ตาราง equipment
แล้วพอค้นหาข้อมูลก็จะขึ้นโชว์เป็น select มาให้เลือกภายในช่องค้นหา พอได้เลือกแล้วข้อมูลจะโชว์เป็น ตาราง โดยจะโชว์ equipmentname , equipmentid , quantity(สามารถเลือกได้ว่าจะยืมกี่ชิ้น) ,  button Delete(เป็นการลบข้อมูลที่โชว์ในตาราง)

เปลี่ยนรูปแบบใหม่ให้หมด เดียวจะอธิบายระบบคราวๆเริ่มแรกจากกรอก รายละเอียดการทำรายการ 
• (loanid)เลขที่ทำรายการ **ตรงนี้ไม่ต้องกรอก ระบบจะป้อนอัตโนมัติ เริ่มจาก Lตามด้วย ปี, เดือน แล้วก็ ลำดับการยืม เช่น "L1024-0001"
• (loandate)วันที่ยืม **สามารถเลือกได้ว่าจะยืมวันอะไร
• (expected_return_date)กำหนดคืน **สามารถเลือกได้ว่าจะคืนวันอะไร แต่ห้ามเลือกก่อนวันที่ยืม
แล้วก็ถึงวิธีการหาอุปกรณ์ จะมีช่องสำหรับค้นหาโดยจะสามารถค้นหาได้จากการเขียน [equipmentid] และ [equipmentname] ซึ่งมาจาก ตาราง equipment  แล้วพอค้นหาข้อมูลก็จะขึ้นโชว์เป็น select ขิ้นมาให้เลือกภายในช่องค้นหา พอได้เลือกแล้วข้อมูลจะโชว์เป็น ตาราง โดยจะโชว์ equipmentname , equipmentid , quantity(สามารถเลือกได้ว่าจะยืมกี่ชิ้น) ,  button Delete(เป็นการลบข้อมูลที่โชว์ในตาราง)

<tr data-id="${equipmentId}">
                <td><span class="badge bg-dark fs-6"> ${equipmentId}</span></td>
                <td><span class="badge bg-dark fs-6"> ${equipmentName}</span></td>
                <td class="text-center" style="width: 8%;">
                    <input type="number" class="form-control quantity-input" min="1" max="${maxQuantity}" value="1">
                </td>
                <td class="text-center" style="width: 8%;">
                    <button class="btn btn-danger btn-sm delete-btn">ลบ</button>
                </td>
            </tr>

INSERT INTO `users` (`id`, `userid`, `username`, `password`, `firstname`, `lastname`, `department`, `role`, `created_at`) VALUES
(1, 'A0001', 'admin', '$2y$10$HajBiddLJbGHY06nx72k1eeux4Zcb/LLAOVeBl8xRqYtfLpP.DsSK', 'admin', 'admin', 'Information_Technology', 'admin', '2024-09-25 18:50:32'),
(2, 'U0001', 'user', '$2y$10$RNHVhXlUfzpSpb4Vjq2gDuK3TbGtM.Daa.aq5/XY3Jka35YUtDJXK', 'user', 'user', 'Machine_Tool_Technology', 'user', '2024-09-26 14:20:46'),
(3, 'A0003', 'demo', '$2y$10$q9Z9OJ12FIHoZX5Mmk9iY.ExYdyMwdlXuGhMKwIwg4gs59Ny7cxVq', 'demo', 'demo', 'Accounting', 'editor', '2024-09-26 14:36:02'),
(4, 'A0004', 'io', '$2y$10$NqXSzje46h8mcAhUTMjWRO4k4GZ6x2Ja61axltGnPY1YrFRq9VYze', 'io', 'io', 'Business_Computer', 'admin', '2024-09-26 16:49:05');

INSERT INTO `equipment` (`id`, `equipmentid`, `equipmentname`, `category`, `quantity`, `photo`, `description`, `created_at`) VALUES
(1, 'F0001', 'ลูกบอล', 'Football', 100, 'F0001-images.jpg', 'เป็นกีฬาประเภททีมที่เล่นระหว่างสองทีม โดยแต่ละทีม', '2024-09-26 17:53:56'),
(5, 'BD0001', 'yonex astrox 100 zz', 'Badminton', 3, 'BD0001-astrox100zz_kurenai.png', 'เป็นซีรี่ย์แร็คเกตหัวหนัก เป็นรุ่นที่เน้นการบุก', '2024-09-27 08:17:01'),
(8, 'B0001', 'ลูกบาสเกตบอล', 'Basketball', 100, 'B0001-220px-Basketball.jpeg', 'ผิวด้านนอกทำด้วยหนังที่มาจากวัสดุสังเคราะห์', '2024-09-27 09:51:23'),
(10, 'F0002', 'ลูกบอลหนัง', 'Football', 1, 'F0002-images.jpg', '', '2024-09-28 06:11:37'),
(11, 'F0003', 'ลูกบอลพลาสติก', 'Football', 2, 'F0003-Untitled design.png', '', '2024-09-28 06:11:43'),
(12, 'F0004', 'ลูกบอลยาง', 'Football', 2, '', '', '2024-09-28 06:11:47'),
(13, 'C0001', 'หมากฮอส', 'Checkers', 3, '', '', '2024-09-28 06:12:41'),
(14, 'VB0001', 'วอลเลย์บอล', 'Volleyball', 33, '', '', '2024-09-28 06:12:48'),
(15, 'T0001', 'ตะกร้อ', 'Takraw', 2, '', '', '2024-09-28 06:29:14'),
(16, 'C0002', 'หมากรุก', 'Checkers', 2, '', '', '2024-09-28 06:29:21'),
(17, 'BG0001', 'บิงโก', 'Bingo', 4, '', '', '2024-09-28 06:29:26');



CREATE DATABASE IF NOT EXISTS equipment_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE equipment_management;

CREATE TABLE `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `userid` VARCHAR(50) NOT NULL UNIQUE,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `firstname` VARCHAR(100) NOT NULL,
    `lastname` VARCHAR(100) NOT NULL,
    `department` VARCHAR(100) NOT NULL,
    `role` ENUM('admin', 'staff', 'user') NOT NULL DEFAULT 'user',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `equipment` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `equipmentid` VARCHAR(50) NOT NULL UNIQUE,
    `equipmentname` VARCHAR(255) NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `quantity` INT UNSIGNED NOT NULL DEFAULT 0,
    `photo` VARCHAR(255) DEFAULT NULL,
    `description` TEXT,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `loan` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `loanid` VARCHAR(50) NOT NULL UNIQUE,
    `userid` INT UNSIGNED NOT NULL,
    `loandate` DATE NOT NULL,
    `expected_return_date` DATE NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`userid`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `loan_details` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `loan_id` INT UNSIGNED NOT NULL,
    `equipmentid` VARCHAR(50) NOT NULL,
    `num_requests` INT NOT NULL,
    `amount` INT NOT NULL DEFAULT 0,
    `status` ENUM('pending', 'borrowed', 'returned', 'disallow','Un-Returned') NOT NULL DEFAULT 'pending',
    FOREIGN KEY (`loan_id`) REFERENCES `loan`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`equipmentid`) REFERENCES `equipment`(`equipmentid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;







CREATE TABLE `returns` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `returnid` VARCHAR(50) NOT NULL UNIQUE,
    `userid` INT UNSIGNED NOT NULL,
    `returndate` DATE NOT NULL,
    `status` ENUM('returned', 'damaged', 'lost') NOT NULL DEFAULT 'returned',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`userid`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




CREATE TABLE `return_details` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `return_id` INT UNSIGNED NOT NULL,
    `equipmentid` VARCHAR(50) NOT NULL,  -- เปลี่ยนเป็น VARCHAR(50)
    `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
    FOREIGN KEY (`return_id`) REFERENCES `returns`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`equipmentid`) REFERENCES `equipment`(`equipmentid`) ON DELETE CASCADE  -- อัปเดตเพื่อชี้ไปที่ equipmentid
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_users_role ON `users`(`role`);
CREATE INDEX idx_loan_status ON `loan`(`status`);
CREATE INDEX idx_returns_status ON `returns`(`status`);

