<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_606 extends CI_Migration
{
    public function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        $this->db->query("INSERT INTO `tbl_menu` (`menu_id`, `label`, `link`, `icon`, `parent`, `sort`, `time`, `status`) VALUES (NULL, 'accounting', '#', 'fa fa-money', '0', '4', '2022-11-20 17:56:37', '1');");
        // after adding the menu, we need to get the menu id
        $menu_id = $this->db->insert_id();
        $this->db->query("INSERT INTO `tbl_menu` (`menu_id`, `label`, `link`, `icon`, `parent`, `sort`, `time`, `status`) VALUES (NULL, 'journal_entry', 'admin/accounting/journal_entry', 'fa fa-circle-o', '$menu_id', '1', '2022-11-20 17:56:37', '1');");
        $this->db->query("INSERT INTO `tbl_menu` (`menu_id`, `label`, `link`, `icon`, `parent`, `sort`, `time`, `status`) VALUES (NULL, 'chart_of_accounts', 'admin/accounting/chart_of_accounts', 'fa fa-circle-o', '$menu_id', '2', '2022-11-20 17:56:37', '1');");
        $this->db->query("INSERT INTO `tbl_menu` (`menu_id`, `label`, `link`, `icon`, `parent`, `sort`, `time`, `status`) VALUES (NULL, 'payment_voucher', 'admin/accounting/payment_voucher', 'fa fa-circle-o', '$menu_id', '3', '2022-11-20 17:56:37', '1');");
        $this->db->query("INSERT INTO `tbl_menu` (`menu_id`, `label`, `link`, `icon`, `parent`, `sort`, `time`, `status`) VALUES (NULL, 'receipt_voucher', 'admin/accounting/receipt_voucher', 'fa fa-circle-o', '$menu_id', '4', '2022-11-20 17:56:37', '1');");
        $this->db->query("INSERT INTO `tbl_menu` (`menu_id`, `label`, `link`, `icon`, `parent`, `sort`, `time`, `status`) VALUES (NULL, 'reports', 'admin/accounting/reports', 'fa fa-circle-o', '$menu_id', '5', '2022-11-20 17:56:37', '1');");
        $this->db->query("INSERT INTO `tbl_menu` (`menu_id`, `label`, `link`, `icon`, `parent`, `sort`, `time`, `status`) VALUES (NULL, 'settings', 'admin/accounting/settings', 'fa fa-circle-o', '$menu_id', '6', '2022-11-20 17:56:37', '1');");

        $this->db->query("DROP TABLE IF EXISTS `tbl_account_type`;");
        $this->db->query("CREATE TABLE IF NOT EXISTS `tbl_account_type` (
  `account_type_id` int NOT NULL AUTO_INCREMENT,
  `account_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`account_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
        $this->db->query("INSERT INTO `tbl_account_type` (`account_type_id`, `account_type`, `status`) VALUES
(1, 'Assets', 1),
(2, 'Liabilities', 1),
(3, 'Expenses', 1),
(4, 'Income', 1),
(5, 'Equity', 1);");

        $this->db->query("DROP TABLE IF EXISTS `tbl_account_sub_type`;");
        $this->db->query("CREATE TABLE IF NOT EXISTS `tbl_account_sub_type` (
  `account_sub_type_id` int NOT NULL AUTO_INCREMENT,
  `account_type_id` int NOT NULL,
  `account_sub_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`account_sub_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
        $this->db->query("INSERT INTO `tbl_account_sub_type` (`account_sub_type_id`, `account_type_id`, `account_sub_type`, `status`) VALUES
(1, 1, 'Current Asset', 1),
(2, 1, 'Fixed Asset', 1),
(3, 1, 'Inventory', 1),
(4, 1, 'Non-current Asset', 1),
(5, 1, 'Prepayment', 1),
(7, 1, 'Depreciation', 1),
(8, 2, 'Current Liability', 1),
(9, 2, 'Liability', 1),
(10, 2, 'Non-current Liability', 1),
(11, 3, 'Direct Costs', 1),
(12, 3, 'Expense', 1),
(13, 3, 'Overhead Costs', 1),
(14, 4, 'Revenue', 1),
(15, 4, 'Sales', 1),
(16, 4, 'Other Income', 1),
(17, 4, 'Other Revenue', 1),
(18, 4, 'Cost of Sales', 1),
(19, 5, 'Equity', 1),
(20, 5, 'Retained Earnings', 1),
(21, 5, 'Share Capital', 1);");

        $this->db->query("DROP TABLE IF EXISTS `tbl_chart_of_accounts`;");
        $this->db->query("CREATE TABLE IF NOT EXISTS `tbl_chart_of_accounts` (
  `chart_of_account_id` int NOT NULL AUTO_INCREMENT,
  `account_type_id` int NOT NULL,
  `account_sub_type_id` int NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `notes` text COLLATE utf8mb4_general_ci,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`chart_of_account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
        $this->db->query("INSERT INTO `tbl_chart_of_accounts` (`chart_of_account_id`, `account_type_id`, `account_sub_type_id`, `code`, `name`, `notes`, `status`) VALUES
(1, 1, 1, '10200', 'Accounts Receivable', NULL, 1),
(2, 1, 2, '10201', 'Computer Equipment', NULL, 1),
(3, 1, 2, '10202', 'Office Equipment', NULL, 1),
(4, 2, 8, '10203', 'Clearing Account', NULL, 1),
(5, 2, 8, '10204', 'Employee Benefits Payable', NULL, 1),
(6, 2, 8, '10205', 'Employee Deductions payable', NULL, 1),
(7, 2, 9, '10206', 'Historical Adjustments', NULL, 1),
(8, 2, 9, '10207', 'Revenue Received in Advance', NULL, 1),
(9, 2, 9, '10208', 'Rounding', NULL, 1),
(10, 3, 11, '10209', 'Costs of Goods Sold', NULL, 1),
(11, 3, 12, '10210', 'Advertising', NULL, 1),
(12, 3, 12, '10211', 'Automobile Expenses', NULL, 1),
(13, 3, 12, '10212', 'Bad Debts', NULL, 1),
(14, 3, 12, '10213', 'Bank Revaluations', NULL, 1),
(15, 3, 12, '10214', 'Bank Service Charges', NULL, 1),
(16, 3, 12, '10215', 'Employeer ICF Expense', NULL, 1),
(17, 3, 12, '10216', 'Insurance', NULL, 1),
(18, 3, 12, '10217', 'Interest Expense', NULL, 1),
(19, 3, 12, '10218', 'Legal & Professional Fees', NULL, 1),
(20, 3, 12, '10219', 'Postage', NULL, 1),
(21, 3, 12, '10220', 'Purchase Account', NULL, 1),
(22, 3, 12, '10221', 'Repairs', NULL, 1),
(23, 3, 12, '10222', 'State Income Tax', NULL, 1),
(24, 3, 12, '10223', 'Tools and Macchnery', NULL, 1),
(25, 3, 12, '10224', 'Electic Bill', NULL, 1),
(26, 3, 12, '10225', 'Wages and Salaries', NULL, 1),
(27, 3, 12, '10226', 'House Rent', NULL, 1),
(28, 3, 12, '10228', 'Consulting & Accounting', NULL, 1),
(29, 3, 12, '10229', 'General Expenses', NULL, 1),
(30, 4, 16, '10230', 'Interest Income', NULL, 1),
(31, 4, 17, '10231', 'Other Revenue', NULL, 1),
(32, 4, 17, '10232', 'Purchase Discount', NULL, 1),
(33, 4, 15, '10233', 'Sales', NULL, 1),
(34, 5, 21, '10234', 'Common Stock', NULL, 1),
(35, 5, 21, '10235', 'Owners Contribution', NULL, 1),
(36, 5, 20, '10236', 'Owners Draw', NULL, 1),
(37, 5, 20, '10237', 'Retained Earnings', NULL, 1),
(38, 1, 1, '10238', 'Other Receivable', NULL, 1),
(39, 1, 3, '10239', 'Inventory', NULL, 1),
(40, 1, 4, '10240', 'Accumulated Depreciation', NULL, 1),
(41, 1, 5, '10241', 'Prepayment', NULL, 1),
(42, 1, 6, '10242', 'Bank & Cash', NULL, 1),
(43, 1, 7, '10243', 'Depreciation', NULL, 1),
(44, 2, 8, '10244', 'Accounts Payable', NULL, 1),
(45, 2, 9, '10245', 'Accruals', NULL, 1),
(46, 2, 8, '10246', 'Other Payable', NULL, 1),
(47, 2, 10, '10247', 'Loan', NULL, 1),
(48, 3, 11, '10248', 'Direct Costs', NULL, 1),
(49, 3, 12, '10249', 'Expense', NULL, 1),
(50, 3, 13, '10250', 'Overhead Costs', NULL, 1),
(51, 4, 14, '10251', 'Revenue', NULL, 1),
(52, 4, 15, '10252', 'Sales', NULL, 1),
(53, 4, 16, '10253', 'Other Income', NULL, 1),
(54, 4, 17, '10254', 'Other Revenue', NULL, 1),
(55, 4, 18, '10255', 'Cost of Sales', NULL, 1),
(56, 5, 19, '10256', 'Equity', NULL, 1),
(57, 5, 20, '10257', 'Retained Earnings', NULL, 1),
(58, 5, 21, '10258', 'Share Capital', NULL, 1);");
        $this->db->query("DROP TABLE IF EXISTS `tbl_journals`;");
        $this->db->query("CREATE TABLE IF NOT EXISTS `tbl_journals` (
  `journal_id` int NOT NULL AUTO_INCREMENT,
  `reference_no` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `total_debit` decimal(18,5) DEFAULT '0.00000',
  `total_credit` decimal(18,5) DEFAULT '0.00000',
  `total_amount` decimal(18,5) DEFAULT '0.00000',
  `created_by` int NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`journal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

        $this->db->query("DROP TABLE IF EXISTS `tbl_journal_items`;");
        $this->db->query("CREATE TABLE IF NOT EXISTS `tbl_journal_items` (
  `journal_item_id` int NOT NULL AUTO_INCREMENT,
  `journal_id` int NOT NULL,
  `chart_of_account_id` int NOT NULL,
  `debit` decimal(18,5) NOT NULL DEFAULT '0.00000',
  `credit` decimal(18,5) NOT NULL DEFAULT '0.00000',
  `description` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`journal_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

        $this->db->query("DROP TABLE IF EXISTS `tbl_receipt_vouchers`;");
        $this->db->query("CREATE TABLE IF NOT EXISTS `tbl_receipt_vouchers` (
  `voucher_id` int NOT NULL AUTO_INCREMENT,
  `account_id` int NOT NULL,
  `reference_no` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `total_amount` decimal(18,5) NOT NULL DEFAULT '0.00000',
  `created_by` int NOT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `approved_by` int NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`voucher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

        $this->db->query("DROP TABLE IF EXISTS `tbl_voucher_items`;");
        $this->db->query("CREATE TABLE IF NOT EXISTS `tbl_voucher_items` (
  `voucher_item_id` int NOT NULL AUTO_INCREMENT,
  `module` varchar(40) COLLATE utf8mb4_general_ci NOT NULL,
  `module_id` int NOT NULL,
  `supplier_client_id` int NOT NULL,
  `amount` decimal(18,5) NOT NULL DEFAULT '0.00000',
  `description` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`voucher_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
        $this->db->query("DROP TABLE IF EXISTS `tbl_payment_vouchers`;");
        $this->db->query("CREATE TABLE IF NOT EXISTS `tbl_payment_vouchers` (
  `voucher_id` int NOT NULL AUTO_INCREMENT,
  `account_id` int NOT NULL,
  `reference_no` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `date` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `total_amount` decimal(18,5) NOT NULL DEFAULT '0.00000',
  `created_by` int NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `approved_by` int NOT NULL,
  `notes` text COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`voucher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

        $this->db->query("ALTER TABLE `tbl_salary_allowance` ADD `allowance_percent` FLOAT(18,2) NULL DEFAULT NULL AFTER `allowance_value`, ADD `allowance_type` VARCHAR(50) NULL DEFAULT NULL AFTER `allowance_percent`;");
        $this->db->query("ALTER TABLE `tbl_salary_allowance` ADD `allowance_percent` FLOAT(18,2) NULL DEFAULT NULL AFTER `allowance_value`, ADD `allowance_type` VARCHAR(50) NULL DEFAULT NULL AFTER `allowance_percent`;");
        $this->db->query("UPDATE `tbl_config` SET `value` = '6.0.6' WHERE `tbl_config`.`config_key` = 'version';");
    }
}
