-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 24, 2024 at 08:57 AM
-- Server version: 10.6.18-MariaDB-0ubuntu0.22.04.1
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_car_reservation`
--

CREATE TABLE `{prefix}_car_reservation` (
  `id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `department` varchar(10) DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  `detail` text DEFAULT NULL,
  `chauffeur` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `travelers` int(11) NOT NULL,
  `begin` datetime DEFAULT NULL,
  `end` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL,
  `reason` text DEFAULT NULL,
  `approve` tinyint(1) NOT NULL,
  `closed` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_car_reservation_data`
--

CREATE TABLE `{prefix}_car_reservation_data` (
  `reservation_id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  `value` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_category`
--

CREATE TABLE `{prefix}_category` (
  `type` varchar(20) NOT NULL,
  `category_id` varchar(10) DEFAULT '0',
  `language` varchar(2) DEFAULT '',
  `topic` varchar(150) NOT NULL,
  `color` varchar(16) DEFAULT NULL,
  `published` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `{prefix}_category`
--

INSERT INTO `{prefix}_category` (`type`, `category_id`, `topic`, `color`, `published`) VALUES
('department', 1, 'บริหาร', NULL, 1),
('department', 2, 'จัดซื้อจัดจ้าง', NULL, 1),
('department', 3, 'บุคคล', NULL, 1),
('cabinet', 1, 'คำสั่ง', NULL, 1),
('cabinet', 2, 'คู่มือ', NULL, 1),
('cabinet', 3, 'ทรัพย์สิน', NULL, 1),
('repairstatus', 7, 'ส่งมอบเรียบร้อย', '#000000', 1),
('car_brand', 3, 'Hino', NULL, 1),
('car_accessories', 1, 'น้ำมันเต็มถัง', NULL, 1),
('car_type', 2, 'รถยนต์นั่งส่วนบุคคล', NULL, 1),
('car_brand', 2, 'Benz', NULL, 1),
('car_type', 3, 'รถบัส', NULL, 1),
('accessories', 4, 'อาหารว่าง', NULL, 1),
('position', 4, 'เจ้าหน้าที่', NULL, 1),
('position', 3, 'หัวหน้า', NULL, 1),
('position', 1, 'ผู้อำนวยการ', NULL, 1),
('position', 2, 'รองผู้อำนวยการ', NULL, 1),
('accessories', 2, 'จอโปรเจ็คเตอร์', NULL, 1),
('accessories', 1, 'เครื่องคอมพิวเตอร์', NULL, 1),
('accessories', 3, 'เครื่องฉายแผ่นใส', NULL, 1),
('use', 2, 'สัมนา', NULL, 1),
('use', 1, 'ประชุม', NULL, 1),
('use', 3, 'จัดเลี้ยง', NULL, 1),
('unit', 3, 'ชุด', NULL, 1),
('car_brand', 1, 'Toyota', NULL, 1),
('unit', 2, 'เครื่อง', NULL, 1),
('model_id', 5, 'Samsung', NULL, 1),
('model_id', 4, 'ACER', NULL, 1),
('model_id', 3, 'Cannon', NULL, 1),
('model_id', 2, 'Asus', NULL, 1),
('type_id', 4, 'โปรเจ็คเตอร์', NULL, 1),
('type_id', 5, 'จอมอนิเตอร์', NULL, 1),
('type_id', 6, 'วัสดุสิ้นเปลือง', NULL, 1),
('car_type', 1, 'รถกระบะ', NULL, 1),
('type_id', 1, 'อื่นๆ', NULL, 1),
('type_id', 2, 'เครื่องคอมพิวเตอร์', NULL, 1),
('category_id', 3, 'วัสดุสำนักงาน', NULL, 1),
('category_id', 1, 'อุปกรณ์', NULL, 1),
('category_id', 2, 'อุปกรณ์เครือข่าย', NULL, 1),
('model_id', 1, 'ไม่ระบุ', NULL, 1),
('type_id', 3, 'เครื่องพิมพ์', NULL, 1),
('unit', 1, 'ชิ้น', NULL, 1),
('car_accessories', 2, 'เครื่องเสียงโฆษณา', NULL, 1),
('accessories', 4, 'อาหารว่าง', NULL, 1),
('department', 1, 'การเงิน', NULL, 1),
('department', 2, 'บริหาร', NULL, 1),
('department', 3, 'พัสดุ', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_dms`
--

CREATE TABLE `{prefix}_dms` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `create_date` date NOT NULL,
  `document_no` varchar(20) NOT NULL,
  `detail` text NOT NULL,
  `topic` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `{prefix}_edocument`
--

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_edocument`
--

CREATE TABLE `{prefix}_edocument` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `department` text DEFAULT NULL,
  `receiver` text DEFAULT NULL,
  `urgency` tinyint(1) NOT NULL DEFAULT 2,
  `last_update` int(11) NOT NULL,
  `document_no` varchar(20) NOT NULL,
  `detail` text NOT NULL,
  `topic` varchar(255) NOT NULL,
  `ext` varchar(4) NOT NULL,
  `size` double NOT NULL,
  `file` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `{prefix}_edocument`
--

INSERT INTO `{prefix}_edocument` (`id`, `sender_id`, `department`, `urgency`, `last_update`, `document_no`, `detail`, `topic`, `ext`, `size`, `file`) VALUES
(1, 2, ',,', 2, 1632052932, 'DOC-0009', 'ส่งให้แอดมิน', 'คำศัพท์ชื่อป้ายห้องในโรงเรียนเป็นภาษาอังกฤษแนบ', 'pdf', 457639, '1545666283.pdf'),
(2, 1, ',1,2,3,', 2, 1545664264, 'DOC-0008', 'ทดสอบ', 'CanonPixmaMP280-MP287-PrinterDriver', 'jpg', 18795, '1545662500.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_dms_download`
--

CREATE TABLE `{prefix}_dms_download` (
  `id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `dms_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `downloads` int(11) NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_dms_files`
--

CREATE TABLE `{prefix}_dms_files` (
  `id` int(11) NOT NULL,
  `dms_id` int(11) NOT NULL,
  `topic` varchar(150) NOT NULL,
  `name` varchar(150) NOT NULL,
  `ext` varchar(4) NOT NULL,
  `size` int(11) NOT NULL,
  `file` varchar(50) DEFAULT NULL,
  `create_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-----------------------------------------------------
-- Dumping data for table `{prefix}_inventory`
--
------------------------------------------------------
CREATE TABLE `{prefix}_inventory` (
  `id` int(11) NOT NULL,
  `category_id` varchar(10) DEFAULT NULL,
  `model_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `topic` varchar(64) NOT NULL,
  `inuse` tinyint(1) DEFAULT 1,
  `count_stock` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `{prefix}_inventory`
--

INSERT INTO `{prefix}_inventory` (`id`, `topic`, `inuse`, `category_id`, `model_id`, `type_id`, `count_stock`) VALUES
(1, 'จอมอนิเตอร์ ACER S220HQLEBD', 1, 1, 4, 4, 1),
(2, 'ASUS A550JX', 1, 1, 2, 1, 1),
(3, 'Crucial 4GB DDR3L&amp;1600 SODIMM', 1, 3, 4, 1, 1);

-- --------------------------------------------------------


--
-- Table structure for table `{prefix}_dms_meta`
--

CREATE TABLE `{prefix}_dms_meta` (
  `dms_id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-------------------------------------------------------------------------
-- Dumping data for table `{prefix}_inventory_meta`
--
CREATE TABLE `{prefix}_inventory_meta` (
  `inventory_id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `{prefix}_inventory_meta` (`inventory_id`, `name`, `value`) VALUES
(1, 'detail', 'โทรศัพท์ใช้ภายในสำนักงาน ห้ามติตั้งโปรแกรมเพิ่มเติม'),
(3, 'detail', 'สว่านเจาะกระแทก โรตารี่ 10 หุน'),
(5, 'model_id', '1'),
(2, 'model_id', '5'),
(1, 'model_id', '4'),
(2, 'type_id', '1'),
(1, 'type_id', '4'),
(3, 'model_id', '2'),
(2, 'category_id', '1'),
(1, 'category_id', '1'),
(3, 'type_id', '1'),
(5, 'type_id', '6'),
(5, 'category_id', '3'),
(3, 'category_id', '1'),
(4, 'model_id', '3'),
(4, 'type_id', '3'),
(4, 'category_id', '1');

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_inventory_stock`
--

CREATE TABLE `{prefix}_inventory_stock` (
  `id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `product_no` varchar(64) DEFAULT NULL,
  `stock` float NOT NULL DEFAULT 0,
  `create_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `{prefix}_inventory_stock`
--

INSERT INTO `{prefix}_inventory_stock` (`id`, `inventory_id`, `product_no`, `stock`, `create_date`) VALUES
(1, 5, '0001', 1, '2021-01-23'),
(2, 4, '0002', 1, '2021-01-23'),
(3, 2, '0003', 1, '2021-01-23'),
(4, 1, '0004', 1, '2021-01-23');

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_inventory_user`
--

CREATE TABLE `{prefix}_inventory_user` (
  `id` int(11) NOT NULL,
  `stock_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `create_date` date DEFAULT NULL,
  `return_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `{prefix}_inventory_user`
--

INSERT INTO `{prefix}_inventory_user` (`id`, `stock_id`, `member_id`, `create_date`, `return_date`) VALUES
(1, 4, 1, '2021-01-23', NULL),
(2, 3, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_language`
--

CREATE TABLE `{prefix}_language` (
  `id` int(11) NOT NULL,
  `key` text NOT NULL,
  `type` varchar(5) NOT NULL,
  `owner` varchar(20) NOT NULL,
  `js` tinyint(1) NOT NULL,
  `th` text DEFAULT NULL,
  `en` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_line`
--

CREATE TABLE `{prefix}_line` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `department` varchar(10) NOT NULL,
  `token` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_logs`
--

CREATE TABLE `{prefix}_logs` (
  `id` int(11) NOT NULL,
  `src_id` int(11) NOT NULL,
  `module` varchar(20) NOT NULL,
  `action` varchar(20) NOT NULL,
  `create_date` datetime NOT NULL,
  `reason` text DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `topic` text NOT NULL,
  `datas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_number`
--

CREATE TABLE `{prefix}_number` (
  `type` varchar(20) NOT NULL,
  `prefix` varchar(20) NOT NULL,
  `auto_increment` int(11) NOT NULL,
  `last_update` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_repair`
--

CREATE TABLE `{prefix}_repair` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `job_id` varchar(20) NOT NULL,
  `job_description` varchar(1000) NOT NULL,
  `create_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_repair_status`
--

CREATE TABLE `{prefix}_repair_status` (
  `id` int(11) NOT NULL,
  `repair_id` int(11) NOT NULL,
  `status` tinyint(2) NOT NULL,
  `operator_id` int(11) NOT NULL,
  `comment` varchar(1000) DEFAULT NULL,
  `member_id` int(11) NOT NULL,
  `create_date` datetime NOT NULL,
  `cost` float DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_reservation`
--

CREATE TABLE `{prefix}_reservation` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `create_date` datetime DEFAULT NULL,
  `topic` varchar(150) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `attendees` int(11) NOT NULL,
  `begin` datetime DEFAULT NULL,
  `end` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL,
  `reason` varchar(128) DEFAULT NULL,
  `approve` tinyint(1) NOT NULL,
  `closed` tinyint(1) NOT NULL,
  `department` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_reservation_data`
--

CREATE TABLE `{prefix}_reservation_data` (
  `reservation_id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  `value` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_rooms`
--

CREATE TABLE `{prefix}_rooms` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `detail` text NOT NULL,
  `color` varchar(20) NOT NULL,
  `published` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `{prefix}_rooms`
--

INSERT INTO `{prefix}_rooms` (`id`, `name`, `detail`, `color`, `published`) VALUES
(1, 'ห้องประชุม 2', 'ห้องประชุมพร้อมระบบ Video conference\r\nที่นั่งผู้เข้าร่วมประชุม รูปตัว U 2 แถว', '#01579B', 1),
(2, 'ห้องประชุม 1', 'ห้องประชุมขนาดใหญ่\r\nพร้อมสิ่งอำนวยความสะดวกครบครัน', '#1A237E', 1),
(3, 'ห้องประชุมส่วนเทคโนโลยีสารสนเทศ', 'ห้องประชุมขนาดใหญ่ (Hall)\r\nเหมาะสำรับการสัมนาเป็นหมู่คณะ และ จัดเลี้ยง', '#B71C1C', 1);

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_rooms_meta`
--

CREATE TABLE `{prefix}_rooms_meta` (
  `room_id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  `value` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `{prefix}_rooms_meta`
--

INSERT INTO `{prefix}_rooms_meta` (`room_id`, `name`, `value`) VALUES
(2, 'seats', '20 ที่นั่ง'),
(2, 'number', 'R-0001'),
(2, 'building', 'อาคาร 1'),
(1, 'seats', '50 ที่นั่ง รูปตัว U'),
(1, 'number', 'R-0002'),
(1, 'building', 'อาคาร 2'),
(3, 'building', 'โรงอาหาร'),
(3, 'seats', '100 คน');

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_user`
--

CREATE TABLE `{prefix}_user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `salt` varchar(32) DEFAULT '',
  `password` varchar(50) NOT NULL,
  `token` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0,
  `permission` text DEFAULT '',
  `name` varchar(150) NOT NULL,
  `sex` varchar(1) DEFAULT NULL,
  `id_card` varchar(13) DEFAULT NULL,
  `address` varchar(150) DEFAULT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `provinceID` varchar(3) DEFAULT NULL,
  `province` varchar(50) DEFAULT NULL,
  `zipcode` varchar(10) DEFAULT NULL,
  `country` varchar(2) DEFAULT 'TH',
  `create_date` datetime DEFAULT NULL,
  `code` varchar(20) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `social` tinyint(1) DEFAULT 0,
  `line_uid` varchar(33) DEFAULT NULL,
  `activatecode` varchar(32) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_user_meta`
--

CREATE TABLE `{prefix}_user_meta` (
  `value` varchar(10) NOT NULL,
  `name` varchar(20) NOT NULL,
  `member_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_vehicles`
--

CREATE TABLE `{prefix}_vehicles` (
  `id` int(11) NOT NULL,
  `number` varchar(20) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `detail` text DEFAULT NULL,
  `published` int(1) NOT NULL DEFAULT 1,
  `seats` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `{prefix}_vehicles`
--

INSERT INTO `{prefix}_vehicles` (`id`, `number`, `color`, `detail`, `published`, `seats`) VALUES
(1, 'นม 6', '#304FFE', 'พร้อมเครื่องเสียงชุดใหญ่', 1, 50),
(2, 'บจ 888', '#4A148C', '', 1, 13),
(3, 'กข 1234', '#B71C1C', '', 1, 4);

-- --------------------------------------------------------

--
-- Table structure for table `{prefix}_vehicles_meta`
--

CREATE TABLE `{prefix}_vehicles_meta` (
  `vehicle_id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  `value` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `{prefix}_vehicles_meta`
--

INSERT INTO `{prefix}_vehicles_meta` (`vehicle_id`, `name`, `value`) VALUES
(2, 'car_brand', '2'),
(2, 'car_type', '7'),
(1, 'car_brand', '8'),
(1, 'car_type', '9'),
(3, 'car_brand', '12'),
(3, 'car_type', '3');

-- --------------------------------------------------------

--
-- Indexes for table `{prefix}_car_reservation`
--
