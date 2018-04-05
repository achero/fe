/*
 Navicat Premium Data Transfer

 Source Server         : Localhost
 Source Server Type    : MySQL
 Source Server Version : 100129
 Source Host           : localhost:3306
 Source Schema         : facturalo_core

 Target Server Type    : MySQL
 Target Server Version : 100129
 File Encoding         : 65001

 Date: 21/03/2018 18:27:11
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for acc_account
-- ----------------------------
DROP TABLE IF EXISTS `acc_account`;
CREATE TABLE `acc_account`  (
  `n_id_account` int(11) NOT NULL AUTO_INCREMENT,
  `n_id_supplier` int(11) DEFAULT NULL,
  `n_id_account_type` int(11) NOT NULL,
  `c_user` varchar(120) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `password` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_status` enum('visible','hidden','deleted') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `d_date_register_account` datetime(0) NOT NULL,
  `d_date_update_account` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`n_id_account`) USING BTREE,
  INDEX `fk_supplier_account`(`n_id_supplier`) USING BTREE,
  INDEX `fk_account_type_account`(`n_id_account_type`) USING BTREE,
  CONSTRAINT `fk_account_type_account` FOREIGN KEY (`n_id_account_type`) REFERENCES `acc_account_type` (`n_id_account_type`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_supplier_account` FOREIGN KEY (`n_id_supplier`) REFERENCES `sup_supplier` (`n_id_supplier`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 15 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Records of acc_account
-- ----------------------------
INSERT INTO `acc_account` VALUES (7, 7, 2, 'ninosimeon', '$2y$10$mQ60h2bfuUx4dSOLb7SkD.v.vVyUsuxja1uH./V10fSaZI0HAV0Aa', '8ISXYTPS740tf7iyv4oiDyB1Jr2MYZoK39XLzpwgtZDRmyjl5PGwPrNJSoui', 'visible', '2015-02-12 10:38:21', '2016-07-23 12:49:39');
INSERT INTO `acc_account` VALUES (8, NULL, 1, 'admin', '$2y$10$zEg.0msJ73OIUgCZCr8eCu1ylp.k1vmo2fJR2SLLEba9MR/q6UTQS', 'JfVa87qEsb0mHqTrXMHu7Ghgv934HnCSFtXGwTN8C9002HD6I6NAIxioifmB', 'visible', '2015-04-13 18:09:39', '2016-07-06 22:39:51');
INSERT INTO `acc_account` VALUES (14, NULL, 2, 'system', '', NULL, 'visible', '2015-08-14 12:27:59', '2015-08-14 12:28:01');

-- ----------------------------
-- Table structure for acc_account_type
-- ----------------------------
DROP TABLE IF EXISTS `acc_account_type`;
CREATE TABLE `acc_account_type`  (
  `n_id_account_type` int(11) NOT NULL AUTO_INCREMENT,
  `c_name` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_status` enum('visible','hidden','deleted') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `d_date_register_account_type` datetime(0) NOT NULL,
  `d_date_update_account_type` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`n_id_account_type`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Records of acc_account_type
-- ----------------------------
INSERT INTO `acc_account_type` VALUES (1, 'Administrador', 'visible', '2015-02-03 10:25:51', NULL);
INSERT INTO `acc_account_type` VALUES (2, 'Emisor', 'visible', '2015-02-03 10:26:08', NULL);

-- ----------------------------
-- Table structure for acc_account_user
-- ----------------------------
DROP TABLE IF EXISTS `acc_account_user`;
CREATE TABLE `acc_account_user`  (
  `n_id_account` int(11) NOT NULL,
  `c_user_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_user_last_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_telephone` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_email` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `d_date_register_account_user` datetime(0) NOT NULL,
  `d_date_update_account_user` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`n_id_account`) USING BTREE,
  CONSTRAINT `fk_account_account_user` FOREIGN KEY (`n_id_account`) REFERENCES `acc_account` (`n_id_account`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Records of acc_account_user
-- ----------------------------
INSERT INTO `acc_account_user` VALUES (7, 'Nino', 'Simeon', '951717379', 'ninosimeon@gmail.com', '2015-02-12 10:38:21', '2016-07-02 20:23:05');
INSERT INTO `acc_account_user` VALUES (8, 'ADMINISTRADOR', '.', NULL, 'ninosimeon@gmail.com', '2015-04-13 18:11:41', NULL);
INSERT INTO `acc_account_user` VALUES (14, '.', '.', '.', '.', '2015-08-14 12:28:28', '2015-08-14 12:28:30');

-- ----------------------------
-- Table structure for country
-- ----------------------------
DROP TABLE IF EXISTS `country`;
CREATE TABLE `country`  (
  `c_iso` varchar(5) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_name_large` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_name_short` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`c_iso`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Records of country
-- ----------------------------
INSERT INTO `country` VALUES ('PE', NULL, 'PERU');

-- ----------------------------
-- Table structure for cus_customer
-- ----------------------------
DROP TABLE IF EXISTS `cus_customer`;
CREATE TABLE `cus_customer`  (
  `n_id_customer` int(11) NOT NULL AUTO_INCREMENT,
  `n_id_supplier` int(11) NOT NULL,
  `c_customer_assigned_account_id` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_additional_account_id` varchar(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_party_party_legal_entity_registration_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_party_physical_location_description` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_postal_address_id` varchar(6) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_postal_address_street_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_postal_address_city_subdivision_name` varchar(25) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_postal_address_city_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_postal_address_country_subentity` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_postal_address_district` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_postal_address_country_identification_code` varchar(2) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `d_date_register_customer` datetime(0) NOT NULL,
  `d_date_update_customer` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`n_id_customer`) USING BTREE,
  INDEX `fk_supplier_customer`(`n_id_supplier`) USING BTREE,
  CONSTRAINT `fk_supplier_customer` FOREIGN KEY (`n_id_supplier`) REFERENCES `sup_supplier` (`n_id_supplier`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 58 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Records of cus_customer
-- ----------------------------
INSERT INTO `cus_customer` VALUES (54, 7, '10703322274', '6', 'EMPRESA X', 'AVDA. 2 DE MAYO 798 SAN ISIDRO SAN ISIDRO-LIMA', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2018-03-09 17:39:53', '2018-03-09 18:02:59');
INSERT INTO `cus_customer` VALUES (55, 6, '20112273922', '6', 'MAESTRO PERU S.A.', 'JR. SAN LORENZO N° 881 SURQUILLO-LIMA', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2018-03-12 09:35:08', '2018-03-12 09:35:08');
INSERT INTO `cus_customer` VALUES (56, 6, '12345678', '1', 'JOSE PEREZ', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2018-03-16 00:17:45', '2018-03-16 00:17:45');
INSERT INTO `cus_customer` VALUES (57, 6, '87654321', '1', 'JUAN ARNAO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2018-03-16 00:19:34', '2018-03-16 00:19:34');

-- ----------------------------
-- Table structure for doc_cdr_status
-- ----------------------------
DROP TABLE IF EXISTS `doc_cdr_status`;
CREATE TABLE `doc_cdr_status`  (
  `n_id_cdr_status` int(11) NOT NULL AUTO_INCREMENT,
  `n_index` int(11) NOT NULL,
  `c_name` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_description` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `d_date_register` datetime(0) NOT NULL,
  `d_date_update` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`n_id_cdr_status`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Records of doc_cdr_status
-- ----------------------------
INSERT INTO `doc_cdr_status` VALUES (1, 1, 'ACEPTADO', 'CDR sin Nota', '2015-04-23 17:17:38', NULL);
INSERT INTO `doc_cdr_status` VALUES (2, 3, 'RECHAZADO', 'CDR con Error', '2015-04-23 17:17:54', NULL);
INSERT INTO `doc_cdr_status` VALUES (3, 2, 'OBSERVADO', 'CDR con Nota', '2015-04-23 17:18:05', NULL);
INSERT INTO `doc_cdr_status` VALUES (4, 4, 'SIN CDR', NULL, '2015-04-23 17:18:19', NULL);
INSERT INTO `doc_cdr_status` VALUES (5, 5, 'ANULADO', 'Documento que fue anulado por una COMUNICACION DE BAJA', '2015-10-15 12:51:13', NULL);

-- ----------------------------
-- Table structure for doc_document_currency_code_type
-- ----------------------------
DROP TABLE IF EXISTS `doc_document_currency_code_type`;
CREATE TABLE `doc_document_currency_code_type`  (
  `c_document_currency_code` varchar(3) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_description` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_symbol` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`c_document_currency_code`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Records of doc_document_currency_code_type
-- ----------------------------
INSERT INTO `doc_document_currency_code_type` VALUES ('PEN', 'NUEVO SOL', 'S/.');
INSERT INTO `doc_document_currency_code_type` VALUES ('USD', 'US DOLLAR', '$');

-- ----------------------------
-- Table structure for doc_invoice
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice`;
CREATE TABLE `doc_invoice`  (
  `n_id_invoice` int(11) NOT NULL AUTO_INCREMENT,
  `n_id_invoice_related` int(11) DEFAULT NULL,
  `n_id_customer` int(11) DEFAULT NULL,
  `n_id_supplier` int(11) NOT NULL,
  `c_ubl_version_id` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_customization_id` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_note` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_serie` varchar(4) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
  `c_correlative` varchar(8) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `n_correlative` int(11) NOT NULL,
  `c_id` varchar(17) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Numeración conformada por serie y número correlativo F###-NNNNNN',
  `d_issue_date` date NOT NULL,
  `c_invoice_type_code` varchar(2) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Tipo de Documento (Factura)',
  `c_document_currency_code` varchar(3) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `d_reference_date` date DEFAULT NULL,
  `c_additional_information_sunat_transaction_id` varchar(2) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_status_invoice` enum('visible','hidden','deleted') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `d_expiry_date` date DEFAULT NULL,
  `c_order_reference_id` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `d_date_register_invoice` datetime(0) NOT NULL,
  `d_date_update_invoice` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`n_id_invoice`) USING BTREE,
  INDEX `fk_customer_invoice`(`n_id_customer`) USING BTREE,
  INDEX `supplier_invoice_id`(`n_id_supplier`, `c_id`) USING BTREE,
  INDEX `fk_invoice_invoice_type_code`(`c_invoice_type_code`) USING BTREE,
  INDEX `fk_document_currency_code_invoice`(`c_document_currency_code`) USING BTREE,
  INDEX `fk_invoice_invoice`(`n_id_invoice_related`) USING BTREE,
  CONSTRAINT `fk_customer_invoice` FOREIGN KEY (`n_id_customer`) REFERENCES `cus_customer` (`n_id_customer`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_document_currency_code_invoice` FOREIGN KEY (`c_document_currency_code`) REFERENCES `doc_document_currency_code_type` (`c_document_currency_code`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_invoice_invoice` FOREIGN KEY (`n_id_invoice_related`) REFERENCES `doc_invoice` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_invoice_invoice_type_code` FOREIGN KEY (`c_invoice_type_code`) REFERENCES `doc_invoice_type_code` (`c_invoice_type_code`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_supplier_invoice` FOREIGN KEY (`n_id_supplier`) REFERENCES `sup_supplier` (`n_id_supplier`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 468 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_additional_account_id
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_additional_account_id`;
CREATE TABLE `doc_invoice_additional_account_id`  (
  `c_id_invoice_additional_account_id` varchar(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_description` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`c_id_invoice_additional_account_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = 'Contiene el Catalogo No 06.' ROW_FORMAT = Compact;

-- ----------------------------
-- Records of doc_invoice_additional_account_id
-- ----------------------------
INSERT INTO `doc_invoice_additional_account_id` VALUES ('0', 'DOC. TRIB. NO. DOM. SIN. RUC	');
INSERT INTO `doc_invoice_additional_account_id` VALUES ('1', 'DOC. NACIONAL DE IDENTIDAD');
INSERT INTO `doc_invoice_additional_account_id` VALUES ('4', 'CARNET DE EXTRANJERIA');
INSERT INTO `doc_invoice_additional_account_id` VALUES ('6', 'REG. UNICO DE CONTRIBUYENTES');
INSERT INTO `doc_invoice_additional_account_id` VALUES ('7', 'PASAPORTE');
INSERT INTO `doc_invoice_additional_account_id` VALUES ('A', 'CED. DIPLOMATICA DE IDENTIDAD');

-- ----------------------------
-- Table structure for doc_invoice_additional_document_reference
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_additional_document_reference`;
CREATE TABLE `doc_invoice_additional_document_reference`  (
  `n_id_invoice_additional_document_reference` int(11) NOT NULL AUTO_INCREMENT,
  `n_id_invoice` int(11) NOT NULL,
  `c_id` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Numero de documento relacionado',
  `c_document_type_code` varchar(2) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Tipo de documento - Catalogo No 12',
  PRIMARY KEY (`n_id_invoice_additional_document_reference`) USING BTREE,
  INDEX `fk_invoice_invoice_additional_document_reference`(`n_id_invoice`) USING BTREE,
  CONSTRAINT `fk_invoice_invoice_additional_document_reference` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_additional_information_additional_monetary_total
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_additional_information_additional_monetary_total`;
CREATE TABLE `doc_invoice_additional_information_additional_monetary_total`  (
  `n_id_invoice_additional_information_monetary_total` int(11) NOT NULL AUTO_INCREMENT,
  `n_id_invoice` int(11) NOT NULL,
  `c_id` varchar(4) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_payable_amount` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Monto',
  `c_reference_amount` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_total_amount` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_percent` varchar(18) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`n_id_invoice_additional_information_monetary_total`) USING BTREE,
  UNIQUE INDEX `invoice_additional_information_additional_monetary_total_id`(`n_id_invoice`, `c_id`) USING BTREE,
  CONSTRAINT `fk_invoice_invoice_amount_type` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 16 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_additional_information_additional_property
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_additional_information_additional_property`;
CREATE TABLE `doc_invoice_additional_information_additional_property`  (
  `n_id_invoice_additional_information_additional_property` int(11) NOT NULL AUTO_INCREMENT,
  `n_id_invoice` int(11) NOT NULL,
  `c_id` varchar(4) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_value` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`n_id_invoice_additional_information_additional_property`) USING BTREE,
  INDEX `invoice_additional_information_additional_propery`(`n_id_invoice`, `c_id`) USING BTREE,
  CONSTRAINT `fk_invoice_invoice_additional_information_additonal_property` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_anticipos
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_anticipos`;
CREATE TABLE `doc_invoice_anticipos`  (
  `ant_id` int(11) NOT NULL AUTO_INCREMENT,
  `n_id_invoice` int(11) NOT NULL,
  `ant_paid_amount` varchar(18) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `ant_cbc_id` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `ant_cbc_id_scheme_id` varchar(2) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `ant_instruction_id` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `ant_instruction_id_scheme_id` varchar(1) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`ant_id`) USING BTREE,
  INDEX `n_id_invoice`(`n_id_invoice`) USING BTREE,
  CONSTRAINT `fk_invoice_anticipos` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_billing_reference_invoice_document_reference
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_billing_reference_invoice_document_reference`;
CREATE TABLE `doc_invoice_billing_reference_invoice_document_reference`  (
  `n_id_invoice` int(11) NOT NULL,
  `c_id` varchar(13) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_document_type_code` varchar(2) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`n_id_invoice`) USING BTREE,
  CONSTRAINT `fk_invoice_invoice_billing_reference_invoice_document_reference` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_cdr
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_cdr`;
CREATE TABLE `doc_invoice_cdr`  (
  `n_id_invoice` int(11) NOT NULL,
  `c_ubl_version_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_customization_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `d_issue_date` date DEFAULT NULL,
  `d_issue_time` time(0) DEFAULT NULL,
  `d_response_date` date DEFAULT NULL,
  `d_response_time` time(0) DEFAULT NULL,
  `d_date_register` datetime(0) NOT NULL,
  `d_date_update` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`n_id_invoice`) USING BTREE,
  CONSTRAINT `fk_invoice_invoice_cdr` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_cdr_document_response
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_cdr_document_response`;
CREATE TABLE `doc_invoice_cdr_document_response`  (
  `n_id_invoice` int(11) NOT NULL,
  `c_response_reference_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_response_response_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_response_description` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_document_reference_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_recipient_party_party_identification_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`n_id_invoice`) USING BTREE,
  INDEX `c_response_response_code`(`c_response_response_code`) USING BTREE,
  CONSTRAINT `fk_error_code_invoice_cdr_document_response` FOREIGN KEY (`c_response_response_code`) REFERENCES `err_error_code` (`c_id_error_code`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_invoice_cdr_invoice_cdr_document_response` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice_cdr` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_cdr_note
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_cdr_note`;
CREATE TABLE `doc_invoice_cdr_note`  (
  `n_id_invoice_cdr_note` int(11) NOT NULL AUTO_INCREMENT,
  `n_id_invoice` int(11) NOT NULL,
  `c_note` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_code` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_description` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`n_id_invoice_cdr_note`) USING BTREE,
  INDEX `fk_invoice_cdr_invoice_cdr_note`(`n_id_invoice`) USING BTREE,
  INDEX `c_code`(`c_code`) USING BTREE,
  CONSTRAINT `fk_error_code_invoice_cdr_note` FOREIGN KEY (`c_code`) REFERENCES `err_error_code` (`c_id_error_code`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_invoice_cdr_invoice_cdr_note` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice_cdr` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_cdr_receiver_party
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_cdr_receiver_party`;
CREATE TABLE `doc_invoice_cdr_receiver_party`  (
  `n_id_invoice` int(11) NOT NULL,
  `c_party_identification_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`n_id_invoice`) USING BTREE,
  CONSTRAINT `fk_invoice_cdr_invoice_cdr_receiver_party` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice_cdr` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_cdr_sender_party
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_cdr_sender_party`;
CREATE TABLE `doc_invoice_cdr_sender_party`  (
  `n_id_invoice` int(11) NOT NULL,
  `c_party_identification_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`n_id_invoice`) USING BTREE,
  CONSTRAINT `fk_invoice_cdr_invoice_cdr_sender_party` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice_cdr` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_cdr_status
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_cdr_status`;
CREATE TABLE `doc_invoice_cdr_status`  (
  `n_id_invoice` int(11) NOT NULL,
  `n_id_cdr_status` int(11) NOT NULL,
  `d_date_register` datetime(0) NOT NULL,
  `d_date_update` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`n_id_invoice`) USING BTREE,
  INDEX `fk_cdr_status_invoice_cdr_status`(`n_id_cdr_status`) USING BTREE,
  CONSTRAINT `fk_cdr_status_invoice_cdr_status` FOREIGN KEY (`n_id_cdr_status`) REFERENCES `doc_cdr_status` (`n_id_cdr_status`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_invoice_invoice_cdr_status` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_customer
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_customer`;
CREATE TABLE `doc_invoice_customer`  (
  `n_id_invoice` int(11) NOT NULL,
  `n_id_customer` int(11) NOT NULL,
  `c_customer_assigned_account_id` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_additional_account_id` varchar(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_party_party_legal_entity_registration_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_party_physical_location_description` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_postal_address_id` varchar(6) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_postal_address_street_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_postal_address_city_subdivision_name` varchar(25) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_postal_address_city_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_postal_address_country_subentity` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_postal_address_district` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_postal_address_country_identification_code` varchar(2) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`n_id_invoice`) USING BTREE,
  UNIQUE INDEX `fk_invoice_invoice_customer`(`n_id_invoice`) USING BTREE,
  INDEX `fk_customer_invoice_customer`(`n_id_customer`) USING BTREE,
  CONSTRAINT `fk_customer_invoice_customer` FOREIGN KEY (`n_id_customer`) REFERENCES `cus_customer` (`n_id_customer`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_invoice_invoice_customer` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_despatch_document_reference
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_despatch_document_reference`;
CREATE TABLE `doc_invoice_despatch_document_reference`  (
  `n_id_invoice_despatch_document_reference` int(11) NOT NULL AUTO_INCREMENT,
  `n_id_invoice` int(11) NOT NULL,
  `c_id` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Numero de guia',
  `c_document_type_code` varchar(2) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Tipo de documento - Catalogo No 01',
  PRIMARY KEY (`n_id_invoice_despatch_document_reference`) USING BTREE,
  INDEX `fk_invoice_invoice_despatch_document_reference`(`n_id_invoice`) USING BTREE,
  CONSTRAINT `fk_invoice_invoice_despatch_document_reference` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_discrepancy_response
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_discrepancy_response`;
CREATE TABLE `doc_invoice_discrepancy_response`  (
  `n_id_invoice` int(11) NOT NULL,
  `c_reference_id` varchar(13) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_response_code` varchar(2) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_description` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`n_id_invoice`) USING BTREE,
  CONSTRAINT `fk_invoice_invoice_discrepancy_response` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_extra_data
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_extra_data`;
CREATE TABLE `doc_invoice_extra_data`  (
  `n_id_invoice` int(11) NOT NULL,
  `c_customer_email` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_exchange_rate` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_email_was_sent` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'no' COMMENT 'Envio un correo al cliente.',
  PRIMARY KEY (`n_id_invoice`) USING BTREE,
  CONSTRAINT `fk_invoice_invoice_extra_data` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_file
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_file`;
CREATE TABLE `doc_invoice_file`  (
  `n_id_invoice` int(11) NOT NULL,
  `c_has_document` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_document_name` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `d_date_document_created` datetime(0) DEFAULT NULL,
  `c_is_sent` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'no',
  `c_has_cdr` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_cdr_name` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `d_date_cdr_created` datetime(0) DEFAULT NULL,
  `c_has_pdf` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_pdf_name` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_has_sunat_response` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_has_sunat_successfully_passed` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_is_cdr_processed_dispatched` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_cdr_processed_requested_by_account` tinyint(1) NOT NULL DEFAULT 0,
  `c_cdr_processed_requested_account` int(11) NOT NULL DEFAULT 14,
  `c_input_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_input_hash` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `d_date_input_created` date DEFAULT NULL,
  `d_date_register` datetime(0) NOT NULL,
  `d_date_update` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`n_id_invoice`) USING BTREE,
  INDEX `c_cdr_processed_requested_account`(`c_cdr_processed_requested_account`) USING BTREE,
  CONSTRAINT `fk_account_invoice_file` FOREIGN KEY (`c_cdr_processed_requested_account`) REFERENCES `acc_account` (`n_id_account`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_invoice_invoice_file` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_item
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_item`;
CREATE TABLE `doc_invoice_item`  (
  `n_id_invoice_item` int(11) NOT NULL AUTO_INCREMENT,
  `n_id_invoice` int(11) NOT NULL,
  `n_id` int(3) DEFAULT NULL COMMENT 'Número de orden del Item',
  `c_invoiced_quantity_unit_code` varchar(3) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Unidad de medida - Catalogo No 03',
  `c_invoiced_quantity` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Cantidad',
  `c_line_extension_amount` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Valor de venta por item',
  `c_item_sellers_item_identification_id` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Codigo del Producto',
  `c_price_price_amount` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Valor unitario por item',
  `c_document_type_code` varchar(2) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_document_serial_id` varchar(4) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_document_number_id` varchar(8) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_void_reason_description` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_start_document_number_id` varchar(8) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_end_document_number_id` varchar(8) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_customer_assigned_account_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'resumen 1.1',
  `c_total_amount` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`n_id_invoice_item`) USING BTREE,
  INDEX `fk_invoice_invoice_item`(`n_id_invoice`) USING BTREE,
  INDEX `fk_invoice_type_code_invoice_item`(`c_document_type_code`) USING BTREE,
  CONSTRAINT `fk_invoice_invoice_item` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_invoice_type_code_invoice_item` FOREIGN KEY (`c_document_type_code`) REFERENCES `doc_invoice_type_code` (`c_invoice_type_code`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1360 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = 'Invoice Inline' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_item_allowancecharge
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_item_allowancecharge`;
CREATE TABLE `doc_invoice_item_allowancecharge`  (
  `n_id_invoice_item` int(11) NOT NULL,
  `c_charge_indicator` varchar(5) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_amount` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`n_id_invoice_item`) USING BTREE,
  CONSTRAINT `fk_invoice_item_invoice_item_allowancecharge` FOREIGN KEY (`n_id_invoice_item`) REFERENCES `doc_invoice_item` (`n_id_invoice_item`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_item_billing_payment
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_item_billing_payment`;
CREATE TABLE `doc_invoice_item_billing_payment`  (
  `n_id_invoice_item_billing_payment` int(11) NOT NULL AUTO_INCREMENT,
  `n_id_invoice_item` int(11) NOT NULL,
  `c_paid_amount` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `c_instruction_id` varchar(2) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`n_id_invoice_item_billing_payment`) USING BTREE,
  INDEX `fk_invoice_item_invoice_item_billing_payment`(`n_id_invoice_item`) USING BTREE,
  CONSTRAINT `fk_invoice_item_invoice_item_billing_payment` FOREIGN KEY (`n_id_invoice_item`) REFERENCES `doc_invoice_item` (`n_id_invoice_item`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 418 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_item_description
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_item_description`;
CREATE TABLE `doc_invoice_item_description`  (
  `n_id_invoice_item_description` int(11) NOT NULL AUTO_INCREMENT,
  `n_id_invoice_item` int(11) NOT NULL,
  `n_index` int(11) NOT NULL,
  `c_description` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`n_id_invoice_item_description`) USING BTREE,
  INDEX `fk_invoice_item_description`(`n_id_invoice_item`) USING BTREE,
  CONSTRAINT `fk_invoice_item_description` FOREIGN KEY (`n_id_invoice_item`) REFERENCES `doc_invoice_item` (`n_id_invoice_item`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 29 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_item_pricing_reference_alternative_condition_price
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_item_pricing_reference_alternative_condition_price`;
CREATE TABLE `doc_invoice_item_pricing_reference_alternative_condition_price`  (
  `n_id_invoice_item_pricing_reference_alternative_condition_price` int(11) NOT NULL AUTO_INCREMENT,
  `n_id_invoice_item` int(11) NOT NULL,
  `c_price_amount` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Monto de Precio de Venta',
  `c_price_type_code` varchar(2) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Codigo de tipo de precio - Catalogo No 16',
  PRIMARY KEY (`n_id_invoice_item_pricing_reference_alternative_condition_price`) USING BTREE,
  INDEX `fk_invoice_item_invoice_item_pricing_reference`(`n_id_invoice_item`) USING BTREE,
  CONSTRAINT `fk_invoice_item_invoice_item_pricing_reference` FOREIGN KEY (`n_id_invoice_item`) REFERENCES `doc_invoice_item` (`n_id_invoice_item`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 22 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_item_tax_total
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_item_tax_total`;
CREATE TABLE `doc_invoice_item_tax_total`  (
  `n_id_invoice_item_tax_total` int(11) NOT NULL AUTO_INCREMENT,
  `n_id_invoice_item` int(11) NOT NULL,
  `c_tax_amount` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`n_id_invoice_item_tax_total`) USING BTREE,
  INDEX `pk_invoice_item_invoice_item_tax_total`(`n_id_invoice_item`) USING BTREE,
  CONSTRAINT `fk_invoice_item_invoice_item_tax_total` FOREIGN KEY (`n_id_invoice_item`) REFERENCES `doc_invoice_item` (`n_id_invoice_item`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1504 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_item_tax_total_igv
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_item_tax_total_igv`;
CREATE TABLE `doc_invoice_item_tax_total_igv`  (
  `n_id_invoice_item_tax_total` int(11) NOT NULL,
  `c_tax_subtotal_tax_category_tax_exemption_reason_code` varchar(2) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Afectacion al IGV - Catalogo No 07',
  PRIMARY KEY (`n_id_invoice_item_tax_total`) USING BTREE,
  INDEX `pk_invoice_item_tax_total_igv`(`n_id_invoice_item_tax_total`) USING BTREE,
  CONSTRAINT `fk_invoice_item_tax_total_invoice_item_taxa_total_igv` FOREIGN KEY (`n_id_invoice_item_tax_total`) REFERENCES `doc_invoice_item_tax_total` (`n_id_invoice_item_tax_total`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_item_tax_total_isc
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_item_tax_total_isc`;
CREATE TABLE `doc_invoice_item_tax_total_isc`  (
  `n_id_invoice_item_tax_total` int(11) NOT NULL,
  `c_tax_subtotal_tax_category_tier_range` varchar(2) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Tipo de Sistema ISC - Catalogo No 08',
  PRIMARY KEY (`n_id_invoice_item_tax_total`) USING BTREE,
  INDEX `pk_invoice_item_tax_total_isc`(`n_id_invoice_item_tax_total`) USING BTREE,
  CONSTRAINT `fk_invoice_item_tax_total_invoice_item_tax_total_isc` FOREIGN KEY (`n_id_invoice_item_tax_total`) REFERENCES `doc_invoice_item_tax_total` (`n_id_invoice_item_tax_total`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_item_tax_total_tax_subtotal
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_item_tax_total_tax_subtotal`;
CREATE TABLE `doc_invoice_item_tax_total_tax_subtotal`  (
  `n_id_invoice_item_tax_total` int(11) NOT NULL,
  `c_tax_amount` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`n_id_invoice_item_tax_total`) USING BTREE,
  CONSTRAINT `fk_invoice_item_tax_total_invoice_item_tax_total_tax_subtotal` FOREIGN KEY (`n_id_invoice_item_tax_total`) REFERENCES `doc_invoice_item_tax_total` (`n_id_invoice_item_tax_total`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_item_tax_total_tax_subtotal_tax_category_tax_scheme
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_item_tax_total_tax_subtotal_tax_category_tax_scheme`;
CREATE TABLE `doc_invoice_item_tax_total_tax_subtotal_tax_category_tax_scheme`  (
  `n_id_invoice_item_tax_total` int(11) NOT NULL,
  `c_id` varchar(4) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_name` varchar(6) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_tax_type_code` varchar(3) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`n_id_invoice_item_tax_total`) USING BTREE,
  CONSTRAINT `fk_invoice_item_tax_total_tax_category_tax_scheme` FOREIGN KEY (`n_id_invoice_item_tax_total`) REFERENCES `doc_invoice_item_tax_total` (`n_id_invoice_item_tax_total`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_legal_monetary_total
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_legal_monetary_total`;
CREATE TABLE `doc_invoice_legal_monetary_total`  (
  `n_id_invoice` int(11) NOT NULL,
  `c_payable_amount` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Importe total de la venta, sesion en uso o del servicio prestado',
  `c_charge_total_amount` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Sumatoria otros Cargos',
  `c_allowance_total_amount` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Descuentos Globales',
  `c_prepaid_amount` varchar(18) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`n_id_invoice`) USING BTREE,
  CONSTRAINT `fk_invoice_invoice_legal_monetary_total` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_pdf_data
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_pdf_data`;
CREATE TABLE `doc_invoice_pdf_data`  (
  `n_id_invoice` int(11) NOT NULL,
  `c_purchase_order` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `n_terms_of_payment` int(11) DEFAULT NULL,
  `d_expiration_date` date DEFAULT NULL,
  `c_observation` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_customer_address` varchar(300) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`n_id_invoice`) USING BTREE,
  CONSTRAINT `fk_invoice_invoice_pdf_data` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_pdf_data_custom
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_pdf_data_custom`;
CREATE TABLE `doc_invoice_pdf_data_custom`  (
  `n_id_invoice_pdf_data_custom` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `n_id_invoice` int(11) NOT NULL,
  `n_index` int(11) NOT NULL,
  `c_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Nombre de variable presentada en PDF',
  `c_value` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`n_id_invoice_pdf_data_custom`) USING BTREE,
  INDEX `n_id_invoice`(`n_id_invoice`) USING BTREE,
  CONSTRAINT `fk_invoice_pdf_data_invoice_pdf_data_custom` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice_pdf_data` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_signature
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_signature`;
CREATE TABLE `doc_invoice_signature`  (
  `n_id_invoice` int(11) NOT NULL,
  `c_digest_value` varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_signature_value` varchar(400) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`n_id_invoice`) USING BTREE,
  CONSTRAINT `fk_invoice_signature_value` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_sunat_embeded_despatch_advice
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_sunat_embeded_despatch_advice`;
CREATE TABLE `doc_invoice_sunat_embeded_despatch_advice`  (
  `n_id_invoice` int(11) NOT NULL DEFAULT 0,
  `iseda_license_plate_id` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `iseda_transport_authorization_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `iseda_brand_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `iseda_party_identification_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `iseda_customer_assigned_account` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `iseda_additional_account_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `iseda_registration_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `iseda_transport_mode_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `iseda_gross_weight_measure` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `iseda_gross_weight_measure_unit_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`n_id_invoice`) USING BTREE,
  CONSTRAINT `fk_invoice_invoice_sunat_embeded_dispatch_advice` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_sunat_embeded_despatch_advice_origin_delivery
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_sunat_embeded_despatch_advice_origin_delivery`;
CREATE TABLE `doc_invoice_sunat_embeded_despatch_advice_origin_delivery`  (
  `isedaod_id` int(11) NOT NULL AUTO_INCREMENT,
  `n_id_invoice` int(11) NOT NULL,
  `isedaod_type` enum('origin','delivery') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `isedaod_address_id` varchar(6) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `isedaod_street_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `isedaod_city_subdivision_name` varchar(25) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `isedaod_city_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `isedaod_country_subentity` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `isedaod_district` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `isedaod_country_identification_code` varchar(2) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`isedaod_id`) USING BTREE,
  INDEX `n_id_invoice`(`n_id_invoice`) USING BTREE,
  CONSTRAINT `fk_invoice_sunat_embeded_despatch_advice_origin_delivery` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice_sunat_embeded_despatch_advice` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_supplier
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_supplier`;
CREATE TABLE `doc_invoice_supplier`  (
  `n_id_invoice` int(11) NOT NULL,
  `n_id_supplier` int(11) NOT NULL,
  `c_customer_assigned_account_id` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_additional_account_id` varchar(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_party_postal_address_id` varchar(6) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_party_postal_address_street_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_party_postal_address_city_subdivision_name` varchar(25) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_party_postal_address_city_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_party_postal_address_country_subentity` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_party_postal_address_district` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_party_postal_address_country_identification_code` varchar(2) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_party_party_legal_entity_registration_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_party_name_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_telephone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_email` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_detraction_account` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_sunat_bill_resolution` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_sunat_invoice_resolution` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`n_id_invoice`) USING BTREE,
  UNIQUE INDEX `fk_invoice_invoice_supplier`(`n_id_invoice`) USING BTREE,
  INDEX `fk_supplier_invoice_supplier`(`n_id_supplier`) USING BTREE,
  INDEX `fk_country_invoice_supplier`(`c_party_postal_address_country_identification_code`) USING BTREE,
  CONSTRAINT `fk_country_invoice_supplier` FOREIGN KEY (`c_party_postal_address_country_identification_code`) REFERENCES `country` (`c_iso`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_invoice_invoice_supplier` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_supplier_invoice_supplier` FOREIGN KEY (`n_id_supplier`) REFERENCES `sup_supplier` (`n_id_supplier`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_tax_total
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_tax_total`;
CREATE TABLE `doc_invoice_tax_total`  (
  `n_id_invoice_tax_total` int(11) NOT NULL AUTO_INCREMENT,
  `n_id_invoice` int(11) NOT NULL,
  `c_tax_amount` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`n_id_invoice_tax_total`) USING BTREE,
  INDEX `invoice_tax_subtotal_tax_category_tax_scheme_id`(`n_id_invoice`) USING BTREE,
  CONSTRAINT `fk_invoice_invoice_tax_total` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_tax_total_tax_subtotal
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_tax_total_tax_subtotal`;
CREATE TABLE `doc_invoice_tax_total_tax_subtotal`  (
  `n_id_invoice_tax_total` int(11) NOT NULL,
  `c_tax_amount` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Sumatoria de IGV',
  PRIMARY KEY (`n_id_invoice_tax_total`) USING BTREE,
  CONSTRAINT `fk_invoice_invoice_tax_total_tax_subtotal` FOREIGN KEY (`n_id_invoice_tax_total`) REFERENCES `doc_invoice_tax_total` (`n_id_invoice_tax_total`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_tax_total_tax_subtotal_tax_category_tax_scheme
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_tax_total_tax_subtotal_tax_category_tax_scheme`;
CREATE TABLE `doc_invoice_tax_total_tax_subtotal_tax_category_tax_scheme`  (
  `n_id_invoice_tax_total` int(11) NOT NULL,
  `c_id` varchar(4) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Codigo de tributo - Catalogo No 05',
  `c_name` varchar(6) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Nombre de tributo - Catalogo No 05',
  `c_tax_type_code` varchar(3) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Codigo Internacional tributo - Catalogo No 05',
  PRIMARY KEY (`n_id_invoice_tax_total`) USING BTREE,
  CONSTRAINT `fk_invoice_in_tax_total_tax_subtotal_tax_category_tax_scheme` FOREIGN KEY (`n_id_invoice_tax_total`) REFERENCES `doc_invoice_tax_total` (`n_id_invoice_tax_total`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_ticket
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_ticket`;
CREATE TABLE `doc_invoice_ticket`  (
  `n_id_invoice` int(11) NOT NULL,
  `c_has_ticket` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_ticket` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_ticket_code` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `d_date_register` datetime(0) NOT NULL,
  `d_date_update` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`n_id_invoice`) USING BTREE,
  CONSTRAINT `fk_invoice_invoice_ticket` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for doc_invoice_type_code
-- ----------------------------
DROP TABLE IF EXISTS `doc_invoice_type_code`;
CREATE TABLE `doc_invoice_type_code`  (
  `c_invoice_type_code` varchar(2) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_description_type_code` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Catalogo No 01, codigo del tipo de documento',
  `c_status` enum('visible','hidden','deleted') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`c_invoice_type_code`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Records of doc_invoice_type_code
-- ----------------------------
INSERT INTO `doc_invoice_type_code` VALUES ('01', 'FACTURA ELECTRONICA', 'visible');
INSERT INTO `doc_invoice_type_code` VALUES ('03', 'BOLETA DE VENTA', 'visible');
INSERT INTO `doc_invoice_type_code` VALUES ('07', 'NOTA DE CREDITO', 'visible');
INSERT INTO `doc_invoice_type_code` VALUES ('08', 'NOTA DE DEBITO', 'visible');
INSERT INTO `doc_invoice_type_code` VALUES ('09', 'GUIA DE REMISION REMITENTE', 'deleted');
INSERT INTO `doc_invoice_type_code` VALUES ('12', 'TICKET DE MAQUINA REGISTRADORA', 'deleted');
INSERT INTO `doc_invoice_type_code` VALUES ('13', 'DOCUMENTO EMITIDO POR BANCOS, INSTITUCIONES FINANCIERAS, CREDITICIAS Y DE SEGUROS QUE SE ENCUENTREN BAJO EL CONTROL DE LA SUPERINTENDENCIA DE BANCA Y SEGUROS', 'deleted');
INSERT INTO `doc_invoice_type_code` VALUES ('18', 'DOCUMENTOS EMITIDOS POR LAS AFPS', 'deleted');
INSERT INTO `doc_invoice_type_code` VALUES ('31', 'GUIA DE REMISION TRANSPORTISTA', 'deleted');
INSERT INTO `doc_invoice_type_code` VALUES ('56', 'COMPROBANTE DE PAGO SEAE', 'deleted');
INSERT INTO `doc_invoice_type_code` VALUES ('RA', 'COMUNICACION DE BAJA', 'visible');
INSERT INTO `doc_invoice_type_code` VALUES ('RC', 'RESUMEN DIARIO DE BOLETA', 'visible');

-- ----------------------------
-- Table structure for doc_seller_supplier_party
-- ----------------------------
DROP TABLE IF EXISTS `doc_seller_supplier_party`;
CREATE TABLE `doc_seller_supplier_party`  (
  `n_id_invoice` int(11) NOT NULL AUTO_INCREMENT,
  `ssp_party_postal_address_id` varchar(6) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `ssp_party_postal_address_street_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `ssp_party_postal_address_city_subdivision_name` varchar(25) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `ssp_party_postal_address_city_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `ssp_party_postal_address_country_subentity` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `ssp_party_postal_address_district` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `ssp_party_postal_address_country_identification_code` varchar(2) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`n_id_invoice`) USING BTREE,
  CONSTRAINT `fk_invoice_seller_supplier_party` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for err_error_code
-- ----------------------------
DROP TABLE IF EXISTS `err_error_code`;
CREATE TABLE `err_error_code`  (
  `c_id_error_code` varchar(4) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `n_id_error_code_type` int(11) DEFAULT NULL,
  `c_description` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_is_forwardable` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`c_id_error_code`) USING BTREE,
  INDEX `fk_error_code_type_error_code`(`n_id_error_code_type`) USING BTREE,
  CONSTRAINT `fk_error_code_type_error_code` FOREIGN KEY (`n_id_error_code_type`) REFERENCES `err_error_code_type` (`n_id_error_code_type`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Records of err_error_code
-- ----------------------------
INSERT INTO `err_error_code` VALUES ('', 1, '', b'0');
INSERT INTO `err_error_code` VALUES ('0', NULL, '', b'0');
INSERT INTO `err_error_code` VALUES ('0100', 1, 'El sistema no puede responder su solicitud. Intente nuevamente o comuníquese con su Administrador', b'1');
INSERT INTO `err_error_code` VALUES ('0101', 1, 'El encabezado de seguridad es incorrecto', b'0');
INSERT INTO `err_error_code` VALUES ('0102', 1, 'Usuario o contraseña incorrectos', b'0');
INSERT INTO `err_error_code` VALUES ('0103', 1, 'El Usuario ingresado no existe', b'0');
INSERT INTO `err_error_code` VALUES ('0104', 1, 'La Clave ingresada es incorrecta', b'0');
INSERT INTO `err_error_code` VALUES ('0105', 1, 'El Usuario no está activo', b'0');
INSERT INTO `err_error_code` VALUES ('0106', 1, 'El Usuario no es válido', b'0');
INSERT INTO `err_error_code` VALUES ('0109', 1, 'El sistema no puede responder su solicitud. (El servicio de autenticación no está disponible)', b'1');
INSERT INTO `err_error_code` VALUES ('0110', 1, 'No se pudo obtener la informacion del tipo de usuario', b'0');
INSERT INTO `err_error_code` VALUES ('0111', 1, 'No tiene el perfil para enviar comprobantes electronicos', b'0');
INSERT INTO `err_error_code` VALUES ('0112', 1, 'El usuario debe ser secundario', b'0');
INSERT INTO `err_error_code` VALUES ('0113', 1, 'El usuario no esta afiliado a Factura Electronica', b'0');
INSERT INTO `err_error_code` VALUES ('0125', 1, 'No se pudo obtener la constancia', b'0');
INSERT INTO `err_error_code` VALUES ('0126', 1, 'El ticket no le pertenece al usuario', b'0');
INSERT INTO `err_error_code` VALUES ('0127', 1, 'El ticket no existe', b'0');
INSERT INTO `err_error_code` VALUES ('0130', 1, 'El sistema no puede responder su solicitud. (No se pudo obtener el ticket de proceso)', b'1');
INSERT INTO `err_error_code` VALUES ('0131', 1, 'El sistema no puede responder su solicitud. (No se pudo grabar el archivo en el directorio)', b'1');
INSERT INTO `err_error_code` VALUES ('0132', 1, 'El sistema no puede responder su solicitud. (No se pudo grabar escribir en el archivo zip)', b'1');
INSERT INTO `err_error_code` VALUES ('0133', 1, 'El sistema no puede responder su solicitud. (No se pudo grabar la entrada del log)', b'1');
INSERT INTO `err_error_code` VALUES ('0134', 1, 'El sistema no puede responder su solicitud. (No se pudo grabar en el storage)', b'1');
INSERT INTO `err_error_code` VALUES ('0135', 1, 'El sistema no puede responder su solicitud. (No se pudo encolar el pedido)', b'1');
INSERT INTO `err_error_code` VALUES ('0136', 1, 'El sistema no puede responder su solicitud. (No se pudo recibir una respuesta del batch)', b'1');
INSERT INTO `err_error_code` VALUES ('0137', 1, 'El sistema no puede responder su solicitud. (Se obtuvo una respuesta nula)', b'1');
INSERT INTO `err_error_code` VALUES ('0138', 1, 'El sistema no puede responder su solicitud. (Error en Base de Datos)', b'1');
INSERT INTO `err_error_code` VALUES ('0151', 1, 'El nombre del archivo ZIP es incorrecto', b'0');
INSERT INTO `err_error_code` VALUES ('0152', 1, 'No se puede enviar por este método un archivo de resumen', b'0');
INSERT INTO `err_error_code` VALUES ('0153', 1, 'No se puede enviar por este método un archivo por lotes', b'0');
INSERT INTO `err_error_code` VALUES ('0154', 1, 'El RUC del archivo no corresponde al RUC del usuario', b'0');
INSERT INTO `err_error_code` VALUES ('0155', 1, 'El archivo ZIP esta vacio', b'0');
INSERT INTO `err_error_code` VALUES ('0156', 1, 'El archivo ZIP esta corrupto', b'0');
INSERT INTO `err_error_code` VALUES ('0157', 1, 'El archivo ZIP no contiene comprobantes', b'0');
INSERT INTO `err_error_code` VALUES ('0158', 1, 'El archivo ZIP contiene demasiados comprobantes para este tipo de envío', b'0');
INSERT INTO `err_error_code` VALUES ('0159', 1, 'El nombre del archivo XML es incorrecto', b'0');
INSERT INTO `err_error_code` VALUES ('0160', 1, 'El archivo XML esta vacio', b'0');
INSERT INTO `err_error_code` VALUES ('0161', 1, 'El nombre del archivo XML no coincide con el nombre del archivo ZIP', b'0');
INSERT INTO `err_error_code` VALUES ('0200', 1, 'No se pudo procesar su solicitud. (Ocurrio un error en el batch)', b'1');
INSERT INTO `err_error_code` VALUES ('0201', 1, 'No se pudo procesar su solicitud. (Llego un requerimiento nulo al batch)', b'1');
INSERT INTO `err_error_code` VALUES ('0202', 1, 'No se pudo procesar su solicitud. (No llego información del archivo ZIP)', b'1');
INSERT INTO `err_error_code` VALUES ('0203', 1, 'No se pudo procesar su solicitud. (No se encontro archivos en la informacion del archivo ZIP)', b'1');
INSERT INTO `err_error_code` VALUES ('0204', 1, 'No se pudo procesar su solicitud. (Este tipo de requerimiento solo acepta 1 archivo)', b'1');
INSERT INTO `err_error_code` VALUES ('0250', 1, 'No se pudo procesar su solicitud. (Ocurrio un error desconocido al hacer unzip)', b'1');
INSERT INTO `err_error_code` VALUES ('0251', 1, 'No se pudo procesar su solicitud. (No se pudo crear un directorio para el unzip)', b'1');
INSERT INTO `err_error_code` VALUES ('0252', 1, 'No se pudo procesar su solicitud. (No se encontro archivos dentro del zip)', b'1');
INSERT INTO `err_error_code` VALUES ('0253', 1, 'No se pudo procesar su solicitud. (No se pudo comprimir la constancia)', b'1');
INSERT INTO `err_error_code` VALUES ('0300', 1, 'No se encontró la raíz documento xml', b'0');
INSERT INTO `err_error_code` VALUES ('0301', 1, 'Elemento raiz del xml no esta definido', b'0');
INSERT INTO `err_error_code` VALUES ('0302', 1, 'Codigo del tipo de comprobante no registrado', b'0');
INSERT INTO `err_error_code` VALUES ('0303', 1, 'No existe el directorio de schemas', b'0');
INSERT INTO `err_error_code` VALUES ('0304', 1, 'No existe el archivo de schema', b'0');
INSERT INTO `err_error_code` VALUES ('0305', 1, 'El sistema no puede procesar el archivo xml', b'0');
INSERT INTO `err_error_code` VALUES ('0306', 1, 'No se puede leer (parsear) el archivo XML', b'0');
INSERT INTO `err_error_code` VALUES ('0307', 1, 'No se pudo recuperar la constancia', b'0');
INSERT INTO `err_error_code` VALUES ('0400', 1, 'No tiene permiso para enviar casos de pruebas', b'0');
INSERT INTO `err_error_code` VALUES ('0401', 1, 'El caso de prueba no existe', b'0');
INSERT INTO `err_error_code` VALUES ('0402', 1, 'La numeracion o nombre del documento ya ha sido enviado anteriormente', b'0');
INSERT INTO `err_error_code` VALUES ('0403', 1, 'El documento afectado por la nota no existe', b'0');
INSERT INTO `err_error_code` VALUES ('0404', 1, 'El documento afectado por la nota se encuentra rechazado', b'0');
INSERT INTO `err_error_code` VALUES ('1001', 1, 'ID - El dato SERIE-CORRELATIVO no cumple con el formato de acuerdo al tipo de comprobante ', b'0');
INSERT INTO `err_error_code` VALUES ('1002', 1, 'El XML no contiene informacion en el tag ID ', b'0');
INSERT INTO `err_error_code` VALUES ('1003', 1, 'InvoiceTypeCode - El valor del tipo de documento es invalido o no coincide con el nombre del archivo ', b'0');
INSERT INTO `err_error_code` VALUES ('1004', 1, 'El XML no contiene el tag o no existe informacion de InvoiceTypeCode ', b'0');
INSERT INTO `err_error_code` VALUES ('1005', 1, 'CustomerAssignedAccountID - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('1006', 1, 'El XML no contiene el tag o no existe informacion de CustomerAssignedAccountID del emisor del documento ', b'0');
INSERT INTO `err_error_code` VALUES ('1007', 1, 'AdditionalAccountID - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('1008', 1, 'El XML no contiene el tag o no existe informacion de AdditionalAccountID del emisor del documento ', b'0');
INSERT INTO `err_error_code` VALUES ('1009', 1, 'IssueDate - El dato ingresado  no cumple con el patron YYYY-MM-DD', b'0');
INSERT INTO `err_error_code` VALUES ('1010', 1, 'El XML no contiene el tag IssueDate', b'0');
INSERT INTO `err_error_code` VALUES ('1011', 1, 'IssueDate- El dato ingresado no es valido', b'0');
INSERT INTO `err_error_code` VALUES ('1012', 1, 'ID - El dato ingresado no cumple con el patron SERIE-CORRELATIVO', b'0');
INSERT INTO `err_error_code` VALUES ('1013', 1, 'El XML no contiene informacion en el tag ID', b'0');
INSERT INTO `err_error_code` VALUES ('1014', 1, 'CustomerAssignedAccountID - El dato ingresado no cumple con el estándar', b'0');
INSERT INTO `err_error_code` VALUES ('1015', 1, 'El XML no contiene el tag o no existe informacion de CustomerAssignedAccountID del emisor del documento', b'0');
INSERT INTO `err_error_code` VALUES ('1016', 1, 'AdditionalAccountID - El dato ingresado no cumple con el estándar', b'0');
INSERT INTO `err_error_code` VALUES ('1017', 1, 'El XML no contiene el tag AdditionalAccountID del emisor del documento', b'0');
INSERT INTO `err_error_code` VALUES ('1018', 1, 'IssueDate - El dato ingresado no cumple con el patron YYYY-MM-DD', b'0');
INSERT INTO `err_error_code` VALUES ('1019', 1, 'El XML no contiene el tag IssueDate', b'0');
INSERT INTO `err_error_code` VALUES ('1020', 1, 'IssueDate- El dato ingresado no es valido', b'0');
INSERT INTO `err_error_code` VALUES ('1021', 1, 'Error en la validacion de la nota de credito', b'0');
INSERT INTO `err_error_code` VALUES ('1022', 1, 'La serie o numero del documento modificado por la Nota Electrónica no cumple con el formato establecido ', b'0');
INSERT INTO `err_error_code` VALUES ('1023', 1, 'No se ha especificado el tipo de documento modificado por la Nota electronica ', b'0');
INSERT INTO `err_error_code` VALUES ('1024', 1, 'CustomerAssignedAccountID - El dato ingresado no cumple con el estándar', b'0');
INSERT INTO `err_error_code` VALUES ('1025', 1, 'El XML no contiene el tag o no existe informacion de CustomerAssignedAccountID del emisor del documento', b'0');
INSERT INTO `err_error_code` VALUES ('1026', 1, 'AdditionalAccountID - El dato ingresado no cumple con el estándar', b'0');
INSERT INTO `err_error_code` VALUES ('1027', 1, 'El XML no contiene el tag AdditionalAccountID del emisor del documento', b'0');
INSERT INTO `err_error_code` VALUES ('1028', 1, 'IssueDate - El dato ingresado no cumple con el patron YYYY-MM-DD', b'0');
INSERT INTO `err_error_code` VALUES ('1029', 1, 'El XML no contiene el tag IssueDate', b'0');
INSERT INTO `err_error_code` VALUES ('1030', 1, 'IssueDate- El dato ingresado no es valido', b'0');
INSERT INTO `err_error_code` VALUES ('1031', 1, 'Error en la validacion de la nota de debito', b'0');
INSERT INTO `err_error_code` VALUES ('1032', 1, 'El comprobante fue informado previamente en una comunicacion de baja ', b'0');
INSERT INTO `err_error_code` VALUES ('1033', 1, 'El comprobante fue registrado previamente con otros datos ', b'0');
INSERT INTO `err_error_code` VALUES ('1034', 1, 'Número de RUC del nombre del archivo no coincide con el consignado en el contenido del archivo XML ', b'0');
INSERT INTO `err_error_code` VALUES ('1035', 1, 'Numero de Serie del nombre del archivo no coincide con el consignado en el contenido del archivo XML ', b'0');
INSERT INTO `err_error_code` VALUES ('1036', 1, 'Número de documento en el nombre del archivo no coincide con el consignado en el contenido del XML ', b'0');
INSERT INTO `err_error_code` VALUES ('1037', 1, 'El XML no contiene el tag o no existe informacion de RegistrationName del emisor del documento ', b'0');
INSERT INTO `err_error_code` VALUES ('1038', 1, 'RegistrationName - El nombre o razon social del emisor no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('1039', 1, 'Solo se pueden recibir notas electronicas que modifican facturas ', b'0');
INSERT INTO `err_error_code` VALUES ('1040', 1, 'El tipo de documento modificado por la nota electronica no es valido ', b'0');
INSERT INTO `err_error_code` VALUES ('1041', 1, 'cac:PrepaidPayment/cbc:ID - El tag no contiene el atributo @SchemaID. que indica el tipo de documento que realiza el anticipo', b'0');
INSERT INTO `err_error_code` VALUES ('1042', 1, 'cac:PrepaidPayment/cbc:InstructionID – El tag no contiene el atributo @SchemaID. Que indica el tipo de documento del emisor del documento del anticipo', b'0');
INSERT INTO `err_error_code` VALUES ('1043', 1, 'cac:OriginatorDocumentReference/cbc:ID - El tag no contiene el atributo @SchemaID. Que indica el tipo de documento del originador del documento electrónico', b'0');
INSERT INTO `err_error_code` VALUES ('1044', 1, 'cac:PrepaidPayment/cbc:InstructionID – El dato ingresado no cumple con el estándar', b'0');
INSERT INTO `err_error_code` VALUES ('1045', 1, 'cac:OriginatorDocumentReference/cbc:ID – El dato ingresado no cumple con el estándar', b'0');
INSERT INTO `err_error_code` VALUES ('1046', 1, 'cbc:Amount - El dato ingresado no cumple con el estándar', b'0');
INSERT INTO `err_error_code` VALUES ('1047', 1, 'cbc:Quantity - El dato ingresado no cumple con el estándar', b'0');
INSERT INTO `err_error_code` VALUES ('1048', 1, 'El XML no contiene el tag o no existe información de PrepaidAmount para un documento con anticipo', b'0');
INSERT INTO `err_error_code` VALUES ('1049', 1, 'ID - Serie y Número del archivo no coincide con el consignado en el contenido del XML', b'0');
INSERT INTO `err_error_code` VALUES ('1050', 1, 'El XML no contiene información en el tag DespatchAdviceTypeCode', b'0');
INSERT INTO `err_error_code` VALUES ('1051', 1, 'DespatchAdviceTypeCode - El valor del tipo de guía es inválido', b'0');
INSERT INTO `err_error_code` VALUES ('1052', 1, 'DespatchAdviceTypeCode - No coincide con el consignado en el contenido del XML', b'0');
INSERT INTO `err_error_code` VALUES ('1053', 1, 'El XML no contiene información en el tag /DespatchAdvice/cac:OrderReference/cbc:ID', b'0');
INSERT INTO `err_error_code` VALUES ('1054', 1, 'cac:OrderReference/cac:DocumentReference/cbc:ID - El dato SERIE-número no cumple con el formato de acuerdo a la Guía', b'0');
INSERT INTO `err_error_code` VALUES ('1055', 1, 'Serie - No cumple con el formato de acuerdo a guía electrónica (EG01 ó TXXXX)', b'0');
INSERT INTO `err_error_code` VALUES ('1056', 1, 'El XML no contiene información en el tag /DespatchAdvice/cac:OrderReference/cbc:OrderTypeCode', b'0');
INSERT INTO `err_error_code` VALUES ('1057', 1, 'El XML no contiene información en el tag cac:AdditionalDocumentReference/cbc:ID', b'0');
INSERT INTO `err_error_code` VALUES ('1058', 1, 'El XML no contiene información en el tag cac:AdditionalDocumentReference/cbc:DocumentTypeCode', b'0');
INSERT INTO `err_error_code` VALUES ('1059', 1, 'El XML no contiene firma digital', b'0');
INSERT INTO `err_error_code` VALUES ('1060', 1, 'El XML no contiene el tag o no existe información del número de RUC del Remitente', b'0');
INSERT INTO `err_error_code` VALUES ('1061', 1, 'El número de RUC del Remitente no existe', b'0');
INSERT INTO `err_error_code` VALUES ('1062', 1, 'El XML no contiene el tag o no existe información en el tag /DespatchAdvice/cac:Shipment/cbc:HandlingCode', b'0');
INSERT INTO `err_error_code` VALUES ('1063', 1, 'cbc:ShippingPriorityLevelCode: El dato ingresado no es valido', b'0');
INSERT INTO `err_error_code` VALUES ('1064', 1, 'El XML no contiene el tag o no existe información en el tag cac:DespatchLine de bienes a transportar', b'0');
INSERT INTO `err_error_code` VALUES ('1065', 1, 'El XML no contiene información en el tag cbc:TransportModeCode', b'0');
INSERT INTO `err_error_code` VALUES ('1066', 1, 'El XML no contiene el tag o no existe información en el tag cac:CarrierParty de datos del transportista', b'0');
INSERT INTO `err_error_code` VALUES ('1067', 1, 'El XML no contiene el tag o no existe información en el tag cac:TransportMeans de datos del vehículo', b'0');
INSERT INTO `err_error_code` VALUES ('1068', 1, 'El XML no contiene el tag o no existe información en el tag cac:DriverPerson de datos del conductor', b'0');
INSERT INTO `err_error_code` VALUES ('1069', 1, 'StartDate: El XML no contiene fecha de inicio de traslado', b'0');
INSERT INTO `err_error_code` VALUES ('1070', 1, 'StartDate - El dato ingresado  no cumple con el patrón YYYY-MM-DD', b'0');
INSERT INTO `err_error_code` VALUES ('1071', 1, 'StartDate - El dato ingresado no es valido', b'0');
INSERT INTO `err_error_code` VALUES ('1072', 1, 'Starttime - El dato ingresado  no cumple con el patrón HH:mm:ss.SZ', b'0');
INSERT INTO `err_error_code` VALUES ('1073', 1, 'StartTime - El dato ingresado no es valido', b'0');
INSERT INTO `err_error_code` VALUES ('1074', 1, 'No ha consignado punto de llegada', b'0');
INSERT INTO `err_error_code` VALUES ('1075', 1, 'No ha consignado punto de partida', b'0');
INSERT INTO `err_error_code` VALUES ('2010', 2, 'El contribuyente no esta activo ', b'0');
INSERT INTO `err_error_code` VALUES ('2011', 2, 'El contribuyente no esta habido ', b'0');
INSERT INTO `err_error_code` VALUES ('2012', 2, 'El contribuyente no está autorizado a emitir comprobantes electrónicos', b'0');
INSERT INTO `err_error_code` VALUES ('2013', 2, 'El contribuyente no cumple con tipo de empresa o tributos requeridos ', b'0');
INSERT INTO `err_error_code` VALUES ('2014', 2, 'El XML no contiene el tag o no existe informacion de CustomerAssignedAccountID del receptor del documento ', b'0');
INSERT INTO `err_error_code` VALUES ('2015', 2, 'El XML no contiene el tag o no existe informacion de AdditionalAccountID del receptor del documento ', b'0');
INSERT INTO `err_error_code` VALUES ('2016', 2, 'AdditionalAccountID - El dato ingresado en el tipo de documento de identidad del receptor no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2017', 2, 'CustomerAssignedAccountID - El numero de documento de identidad del recepetor debe ser RUC ', b'0');
INSERT INTO `err_error_code` VALUES ('2018', 2, 'CustomerAssignedAccountID -  El dato ingresado no cumple con el estándar', b'0');
INSERT INTO `err_error_code` VALUES ('2019', 2, 'El XML no contiene el tag o no existe informacion de RegistrationName del emisor del documento', b'0');
INSERT INTO `err_error_code` VALUES ('2020', 2, 'RegistrationName - El nombre o razon social del emisor no cumple con el estándar', b'0');
INSERT INTO `err_error_code` VALUES ('2021', 2, 'El XML no contiene el tag o no existe informacion de RegistrationName del receptor del documento ', b'0');
INSERT INTO `err_error_code` VALUES ('2022', 2, 'RegistrationName - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2023', 2, 'El Numero de orden del item no cumple con el formato establecido ', b'0');
INSERT INTO `err_error_code` VALUES ('2024', 2, 'El XML no contiene el tag InvoicedQuantity en el detalle de los Items ', b'0');
INSERT INTO `err_error_code` VALUES ('2025', 2, 'InvoicedQuantity El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2026', 2, 'El XML no contiene el tag cac:Item/cbc:Description en el detalle de los Items', b'0');
INSERT INTO `err_error_code` VALUES ('2027', 2, 'El XML no contiene el tag o no existe informacion de cac:Item/cbc:Description del item ', b'0');
INSERT INTO `err_error_code` VALUES ('2028', 2, 'Debe existir el tag cac:AlternativeConditionPrice con un elemento cbc:PriceTypeCode con valor 01 ', b'0');
INSERT INTO `err_error_code` VALUES ('2029', 2, 'PriceTypeCode El dato ingresado no cumple con el estándar', b'0');
INSERT INTO `err_error_code` VALUES ('2030', 2, 'El XML no contiene el tag cbc:PriceTypeCode', b'0');
INSERT INTO `err_error_code` VALUES ('2031', 2, 'LineExtensionAmount El dato ingresado no cumple con el estándar', b'0');
INSERT INTO `err_error_code` VALUES ('2032', 2, 'El XML no contiene el tag LineExtensionAmount en el detalle de los Items', b'0');
INSERT INTO `err_error_code` VALUES ('2033', 2, 'El dato ingresado en TaxAmount de la linea no cumple con el formato establecido ', b'0');
INSERT INTO `err_error_code` VALUES ('2034', 2, 'TaxAmount es obligatorio', b'0');
INSERT INTO `err_error_code` VALUES ('2035', 2, 'cac:TaxCategory/cac:TaxScheme/cbc:ID El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2036', 2, 'El codigo del tributo es invalido ', b'0');
INSERT INTO `err_error_code` VALUES ('2037', 2, 'El XML no contiene el tag cac:TaxCategory/cac:TaxScheme/cbc:ID del Item', b'0');
INSERT INTO `err_error_code` VALUES ('2038', 2, 'cac:TaxScheme/cbc:Name del item - No existe el tag o el dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2039', 2, 'El XML no contiene el tag cac:TaxCategory/cac:TaxScheme/cbc:Name del Item', b'0');
INSERT INTO `err_error_code` VALUES ('2040', 2, 'El tipo de afectacion del IGV es incorrecto ', b'0');
INSERT INTO `err_error_code` VALUES ('2041', 2, 'El sistema de calculo del ISC es incorrecto ', b'0');
INSERT INTO `err_error_code` VALUES ('2042', 2, 'Debe indicar el IGV. Es un campo obligatorio ', b'0');
INSERT INTO `err_error_code` VALUES ('2043', 2, 'El dato ingresado en PayableAmount no cumple con el formato establecido ', b'0');
INSERT INTO `err_error_code` VALUES ('2044', 2, 'PayableAmount es obligatorio', b'0');
INSERT INTO `err_error_code` VALUES ('2045', 2, 'El valor ingresado en AdditionalMonetaryTotal/cbc:ID es incorrecto ', b'0');
INSERT INTO `err_error_code` VALUES ('2046', 2, 'AdditionalMonetaryTotal/cbc:ID debe tener valor', b'0');
INSERT INTO `err_error_code` VALUES ('2047', 2, 'Es obligatorio al menos un AdditionalMonetaryTotal con codigo 1001, 1002 o 1003 ', b'0');
INSERT INTO `err_error_code` VALUES ('2048', 2, 'El dato ingresado en TaxAmount no cumple con el formato establecido ', b'0');
INSERT INTO `err_error_code` VALUES ('2049', 2, 'TaxAmount es obligatorio', b'0');
INSERT INTO `err_error_code` VALUES ('2050', 2, 'TaxScheme ID - No existe el tag o el dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2051', 2, 'El codigo del tributo es invalido ', b'0');
INSERT INTO `err_error_code` VALUES ('2052', 2, 'El XML no contiene el tag TaxScheme ID de impuestos globales', b'0');
INSERT INTO `err_error_code` VALUES ('2053', 2, 'TaxScheme Name - No existe el tag o el dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2054', 2, 'El XML no contiene el tag TaxScheme Name de impuestos globales', b'0');
INSERT INTO `err_error_code` VALUES ('2055', 2, 'TaxScheme TaxTypeCode - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2056', 2, 'El XML no contiene el tag TaxScheme TaxTypeCode de impuestos globales', b'0');
INSERT INTO `err_error_code` VALUES ('2057', 2, 'El Name o TaxTypeCode debe corresponder con el Id para el IGV ', b'0');
INSERT INTO `err_error_code` VALUES ('2058', 2, 'El Name o TaxTypeCode debe corresponder con el Id para el ISC ', b'0');
INSERT INTO `err_error_code` VALUES ('2059', 2, 'El dato ingresado en TaxSubtotal/cbc:TaxAmount no cumple con el formato establecido ', b'0');
INSERT INTO `err_error_code` VALUES ('2060', 2, 'TaxSubtotal/cbc:TaxAmount es obligatorio', b'0');
INSERT INTO `err_error_code` VALUES ('2061', 2, 'El tag global cac:TaxTotal/cbc:TaxAmount debe tener el mismo valor que cac:TaxTotal/cac:Subtotal/cbc:TaxAmount ', b'0');
INSERT INTO `err_error_code` VALUES ('2062', 2, 'El dato ingresado en PayableAmount no cumple con el formato establecido ', b'0');
INSERT INTO `err_error_code` VALUES ('2063', 2, 'El XML no contiene el tag PayableAmount', b'0');
INSERT INTO `err_error_code` VALUES ('2064', 2, 'El dato ingresado en ChargeTotalAmount no cumple con el formato establecido ', b'0');
INSERT INTO `err_error_code` VALUES ('2065', 2, 'El dato ingresado en el campo Total Descuentos no cumple con el formato establecido ', b'0');
INSERT INTO `err_error_code` VALUES ('2066', 2, 'Debe indicar una descripcion para el tag sac:AdditionalProperty/cbc:Value', b'0');
INSERT INTO `err_error_code` VALUES ('2067', 2, 'cac:Price/cbc:PriceAmount - El dato ingresado no cumple con el estandar', b'0');
INSERT INTO `err_error_code` VALUES ('2068', 2, 'El XML no contiene el tag cac:Price/cbc:PriceAmount en el detalle de los Items ', b'0');
INSERT INTO `err_error_code` VALUES ('2069', 2, 'DocumentCurrencyCode - El dato ingresado no cumple con la estructura ', b'0');
INSERT INTO `err_error_code` VALUES ('2070', 2, 'El XML no contiene el tag o no existe informacion de DocumentCurrencyCode ', b'0');
INSERT INTO `err_error_code` VALUES ('2071', 2, 'La moneda debe ser la misma en todo el documento ', b'0');
INSERT INTO `err_error_code` VALUES ('2072', 2, 'CustomizationID - La versión del documento no es la correcta ', b'0');
INSERT INTO `err_error_code` VALUES ('2073', 2, 'El XML no contiene el tag o no existe informacion de CustomizationID ', b'0');
INSERT INTO `err_error_code` VALUES ('2074', 2, 'UBLVersionID - La versión del UBL no es correcta ', b'0');
INSERT INTO `err_error_code` VALUES ('2075', 2, 'El XML no contiene el tag o no existe informacion de UBLVersionID ', b'0');
INSERT INTO `err_error_code` VALUES ('2076', 2, 'cac:Signature/cbc:ID - Falta el identificador de la firma ', b'0');
INSERT INTO `err_error_code` VALUES ('2077', 2, 'El tag cac:Signature/cbc:ID debe contener informacion ', b'0');
INSERT INTO `err_error_code` VALUES ('2078', 2, 'cac:Signature/cac:SignatoryParty/cac:PartyIdentification/cbc:ID - Debe ser igual al RUC del emisor ', b'0');
INSERT INTO `err_error_code` VALUES ('2079', 2, 'El XML no contiene el tag cac:Signature/cac:SignatoryParty/cac:PartyIdentification/cbc:ID', b'0');
INSERT INTO `err_error_code` VALUES ('2080', 2, 'cac:Signature/cac:SignatoryParty/cac:PartyName/cbc:Name - No cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2081', 2, 'El XML no contiene el tag cac:Signature/cac:SignatoryParty/cac:PartyName/cbc:Name', b'0');
INSERT INTO `err_error_code` VALUES ('2082', 2, 'cac:Signature/cac:DigitalSignatureAttachment/cac:ExternalReference/cbc:URI - No cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2083', 2, 'El XML no contiene el tag cac:Signature/cac:DigitalSignatureAttachment/cac:ExternalReference/cbc:URI ', b'0');
INSERT INTO `err_error_code` VALUES ('2084', 2, 'ext:UBLExtensions/ext:UBLExtension/ext:ExtensionContent/ds:Signature/@Id - No cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2085', 2, 'El XML no contiene el tag ext:UBLExtensions/ext:UBLExtension/ext:ExtensionContent/ds:Signature/@Id ', b'0');
INSERT INTO `err_error_code` VALUES ('2086', 2, 'ext:UBLExtensions/.../ds:Signature/ds:SignedInfo/ds:CanonicalizationMethod/@Algorithm - No cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2087', 2, 'El XML no contiene el tag ext:UBLExtensions/.../ds:Signature/ds:SignedInfo/ds:CanonicalizationMethod/@Algorithm ', b'0');
INSERT INTO `err_error_code` VALUES ('2088', 2, 'ext:UBLExtensions/.../ds:Signature/ds:SignedInfo/ds:SignatureMethod/@Algorithm - No cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2089', 2, 'El XML no contiene el tag ext:UBLExtensions/.../ds:Signature/ds:SignedInfo/ds:SignatureMethod/@Algorithm ', b'0');
INSERT INTO `err_error_code` VALUES ('2090', 2, 'ext:UBLExtensions/.../ds:Signature/ds:SignedInfo/ds:Reference/@URI - Debe estar vacio para id ', b'0');
INSERT INTO `err_error_code` VALUES ('2091', 2, 'El XML no contiene el tag ext:UBLExtensions/.../ds:Signature/ds:SignedInfo/ds:Reference/@URI', b'0');
INSERT INTO `err_error_code` VALUES ('2092', 2, 'ext:UBLExtensions/.../ds:Signature/ds:SignedInfo/.../ds:Transform@Algorithm - No cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2093', 2, 'El XML no contiene el tag ext:UBLExtensions/.../ds:Signature/ds:SignedInfo/ds:Reference/ds:Transform@Algorithm ', b'0');
INSERT INTO `err_error_code` VALUES ('2094', 2, 'ext:UBLExtensions/.../ds:Signature/ds:SignedInfo/ds:Reference/ds:DigestMethod/@Algorithm - No cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2095', 2, 'El XML no contiene el tag ext:UBLExtensions/.../ds:Signature/ds:SignedInfo/ds:Reference/ds:DigestMethod/@Algorithm ', b'0');
INSERT INTO `err_error_code` VALUES ('2096', 2, 'ext:UBLExtensions/.../ds:Signature/ds:SignedInfo/ds:Reference/ds:DigestValue - No cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2097', 2, 'El XML no contiene el tag ext:UBLExtensions/.../ds:Signature/ds:SignedInfo/ds:Reference/ds:DigestValue ', b'0');
INSERT INTO `err_error_code` VALUES ('2098', 2, 'ext:UBLExtensions/.../ds:Signature/ds:SignatureValue - No cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2099', 2, 'El XML no contiene el tag ext:UBLExtensions/.../ds:Signature/ds:SignatureValue ', b'0');
INSERT INTO `err_error_code` VALUES ('2100', 2, 'ext:UBLExtensions/.../ds:Signature/ds:KeyInfo/ds:X509Data/ds:X509Certificate - No cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2101', 2, 'El XML no contiene el tag ext:UBLExtensions/.../ds:Signature/ds:KeyInfo/ds:X509Data/ds:X509Certificate ', b'0');
INSERT INTO `err_error_code` VALUES ('2102', 2, 'Error al procesar la factura', b'0');
INSERT INTO `err_error_code` VALUES ('2103', 2, 'La serie ingresada no es válida', b'0');
INSERT INTO `err_error_code` VALUES ('2104', 2, 'Numero de RUC del emisor no existe', b'0');
INSERT INTO `err_error_code` VALUES ('2105', 2, 'Factura a dar de baja no se encuentra registrada en SUNAT ', b'0');
INSERT INTO `err_error_code` VALUES ('2106', 2, 'Factura a dar de baja ya se encuentra en estado de baja ', b'0');
INSERT INTO `err_error_code` VALUES ('2107', 2, 'Numero de RUC SOL no coincide con RUC emisor', b'0');
INSERT INTO `err_error_code` VALUES ('2108', 2, 'Presentacion fuera de fecha ', b'0');
INSERT INTO `err_error_code` VALUES ('2109', 2, 'El comprobante fue registrado previamente con otros datos', b'0');
INSERT INTO `err_error_code` VALUES ('2110', 2, 'UBLVersionID - La versión del UBL no es correcta', b'0');
INSERT INTO `err_error_code` VALUES ('2111', 2, 'El XML no contiene el tag o no existe informacion de UBLVersionID', b'0');
INSERT INTO `err_error_code` VALUES ('2112', 2, 'CustomizationID - La version del documento no es correcta', b'0');
INSERT INTO `err_error_code` VALUES ('2113', 2, 'El XML no contiene el tag o no existe informacion de CustomizationID', b'0');
INSERT INTO `err_error_code` VALUES ('2114', 2, 'DocumentCurrencyCode -  El dato ingresado no cumple con la estructura', b'0');
INSERT INTO `err_error_code` VALUES ('2115', 2, 'El XML no contiene el tag o no existe informacion de DocumentCurrencyCode', b'0');
INSERT INTO `err_error_code` VALUES ('2116', 2, 'El tipo de documento modificado por la Nota de credito debe ser factura electronica o ticket ', b'0');
INSERT INTO `err_error_code` VALUES ('2117', 2, 'La serie o numero del documento modificado por la Nota de Credito no cumple con el formato establecido ', b'0');
INSERT INTO `err_error_code` VALUES ('2118', 2, 'Debe indicar las facturas relacionadas a la Nota de Credito ', b'0');
INSERT INTO `err_error_code` VALUES ('2119', 2, 'La factura relacionada en la Nota de credito no esta registrada. ', b'0');
INSERT INTO `err_error_code` VALUES ('2120', 2, 'La factura relacionada en la nota de credito se encuentra de baja ', b'0');
INSERT INTO `err_error_code` VALUES ('2121', 2, 'La factura relacionada en la nota de credito esta registrada como rechazada ', b'0');
INSERT INTO `err_error_code` VALUES ('2122', 2, 'El tag cac:LegalMonetaryTotal/cbc:PayableAmount debe tener informacion valida', b'0');
INSERT INTO `err_error_code` VALUES ('2123', 2, 'RegistrationName -  El dato ingresado no cumple con el estandar', b'0');
INSERT INTO `err_error_code` VALUES ('2124', 2, 'El XML no contiene el tag RegistrationName del emisor del documento', b'0');
INSERT INTO `err_error_code` VALUES ('2125', 2, 'ReferenceID - El dato ingresado debe indicar SERIE-CORRELATIVO del documento al que se relaciona la Nota ', b'0');
INSERT INTO `err_error_code` VALUES ('2126', 2, 'El XML no contiene informacion en el tag ReferenceID del documento al que se relaciona la nota ', b'0');
INSERT INTO `err_error_code` VALUES ('2127', 2, 'ResponseCode - El dato ingresado no cumple con la estructura ', b'0');
INSERT INTO `err_error_code` VALUES ('2128', 2, 'El XML no contiene el tag o no existe informacion de ResponseCode ', b'0');
INSERT INTO `err_error_code` VALUES ('2129', 2, 'AdditionalAccountID - El dato ingresado en el tipo de documento de identidad del receptor no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2130', 2, 'El XML no contiene el tag o no existe informacion de AdditionalAccountID del receptor del documento ', b'0');
INSERT INTO `err_error_code` VALUES ('2131', 2, 'CustomerAssignedAccountID - El numero de documento de identidad del receptor debe ser RUC ', b'0');
INSERT INTO `err_error_code` VALUES ('2132', 2, 'El XML no contiene el tag o no existe informacion de CustomerAssignedAccountID del receptor del documento ', b'0');
INSERT INTO `err_error_code` VALUES ('2133', 2, 'RegistrationName - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2134', 2, 'El XML no contiene el tag o no existe informacion de RegistrationName del receptor del documento ', b'0');
INSERT INTO `err_error_code` VALUES ('2135', 2, 'cac:DiscrepancyResponse/cbc:Description - El dato ingresado no cumple con la estructura ', b'0');
INSERT INTO `err_error_code` VALUES ('2136', 2, 'El XML no contiene el tag o no existe informacion de cac:DiscrepancyResponse/cbc:Description ', b'0');
INSERT INTO `err_error_code` VALUES ('2137', 2, 'El Número de orden del item no cumple con el formato establecido ', b'0');
INSERT INTO `err_error_code` VALUES ('2138', 2, 'CreditedQuantity/@unitCode - El dato ingresado no cumple con el estandar', b'0');
INSERT INTO `err_error_code` VALUES ('2139', 2, 'CreditedQuantity - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2140', 2, 'El PriceTypeCode debe tener el valor 01 ', b'0');
INSERT INTO `err_error_code` VALUES ('2141', 2, 'cac:TaxCategory/cac:TaxScheme/cbc:ID - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2142', 2, 'El codigo del tributo es invalido ', b'0');
INSERT INTO `err_error_code` VALUES ('2143', 2, 'cac:TaxScheme/cbc:Name del item - No existe el tag o el dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2144', 2, 'cac:TaxCategory/cac:TaxScheme/cbc:TaxTypeCode El dato ingresado no cumple con el estandar', b'0');
INSERT INTO `err_error_code` VALUES ('2145', 2, 'El tipo de afectacion del IGV es incorrecto ', b'0');
INSERT INTO `err_error_code` VALUES ('2146', 2, 'El Nombre Internacional debe ser VAT', b'0');
INSERT INTO `err_error_code` VALUES ('2147', 2, 'El sistema de calculo del ISC es incorrecto ', b'0');
INSERT INTO `err_error_code` VALUES ('2148', 2, 'El Nombre Internacional debe ser EXC ', b'0');
INSERT INTO `err_error_code` VALUES ('2149', 2, 'El dato ingresado en PayableAmount no cumple con el formato establecido ', b'0');
INSERT INTO `err_error_code` VALUES ('2150', 2, 'El valor ingresado en AdditionalMonetaryTotal/cbc:ID es incorrecto ', b'0');
INSERT INTO `err_error_code` VALUES ('2151', 2, 'AdditionalMonetaryTotal/cbc:ID debe tener valor ', b'0');
INSERT INTO `err_error_code` VALUES ('2152', 2, 'Es obligatorio al menos un AdditionalInformation', b'0');
INSERT INTO `err_error_code` VALUES ('2153', 2, 'Error al procesar la Nota de Credito', b'0');
INSERT INTO `err_error_code` VALUES ('2154', 2, 'TaxAmount - El dato ingresado en impuestos globales no cumple con el estandar', b'0');
INSERT INTO `err_error_code` VALUES ('2155', 2, 'El XML no contiene el tag TaxAmount de impuestos globales', b'0');
INSERT INTO `err_error_code` VALUES ('2156', 2, 'TaxScheme ID - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2157', 2, 'El codigo del tributo es invalido ', b'0');
INSERT INTO `err_error_code` VALUES ('2158', 2, 'El XML no contiene el tag o no existe informacion de TaxScheme ID de impuestos globales ', b'0');
INSERT INTO `err_error_code` VALUES ('2159', 2, 'TaxScheme Name - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2160', 2, 'El XML no contiene el tag o no existe informacion de TaxScheme Name de impuestos globales ', b'0');
INSERT INTO `err_error_code` VALUES ('2161', 2, 'CustomizationID - La version del documento no es correcta', b'0');
INSERT INTO `err_error_code` VALUES ('2162', 2, 'El XML no contiene el tag o no existe informacion de CustomizationID', b'0');
INSERT INTO `err_error_code` VALUES ('2163', 2, 'UBLVersionID - La versión del UBL no es correcta', b'0');
INSERT INTO `err_error_code` VALUES ('2164', 2, 'El XML no contiene el tag o no existe informacion de UBLVersionID', b'0');
INSERT INTO `err_error_code` VALUES ('2165', 2, 'Error al procesar la Nota de Debito', b'0');
INSERT INTO `err_error_code` VALUES ('2166', 2, 'RegistrationName - El dato ingresado no cumple con el estandar', b'0');
INSERT INTO `err_error_code` VALUES ('2167', 2, 'El XML no contiene el tag RegistrationName del emisor del documento', b'0');
INSERT INTO `err_error_code` VALUES ('2168', 2, 'DocumentCurrencyCode -  El dato ingresado no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('2169', 2, 'El XML no contiene el tag o no existe informacion de DocumentCurrencyCode', b'0');
INSERT INTO `err_error_code` VALUES ('2170', 2, 'ReferenceID - El dato ingresado debe indicar SERIE-CORRELATIVO del documento al que se relaciona la Nota ', b'0');
INSERT INTO `err_error_code` VALUES ('2171', 2, 'El XML no contiene informacion en el tag ReferenceID del documento al que se relaciona la nota ', b'0');
INSERT INTO `err_error_code` VALUES ('2172', 2, 'ResponseCode - El dato ingresado no cumple con la estructura ', b'0');
INSERT INTO `err_error_code` VALUES ('2173', 2, 'El XML no contiene el tag o no existe informacion de ResponseCode ', b'0');
INSERT INTO `err_error_code` VALUES ('2174', 2, 'cac:DiscrepancyResponse/cbc:Description - El dato ingresado no cumple con la estructura ', b'0');
INSERT INTO `err_error_code` VALUES ('2175', 2, 'El XML no contiene el tag o no existe informacion de cac:DiscrepancyResponse/cbc:Description ', b'0');
INSERT INTO `err_error_code` VALUES ('2176', 2, 'AdditionalAccountID - El dato ingresado en el tipo de documento de identidad del receptor no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2177', 2, 'El XML no contiene el tag o no existe informacion de AdditionalAccountID del receptor del documento ', b'0');
INSERT INTO `err_error_code` VALUES ('2178', 2, 'CustomerAssignedAccountID - El numero de documento de identidad del receptor debe ser RUC. ', b'0');
INSERT INTO `err_error_code` VALUES ('2179', 2, 'El XML no contiene el tag o no existe informacion de CustomerAssignedAccountID del receptor del documento ', b'0');
INSERT INTO `err_error_code` VALUES ('2180', 2, 'RegistrationName - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2181', 2, 'El XML no contiene el tag o no existe informacion de RegistrationName del receptor del documento ', b'0');
INSERT INTO `err_error_code` VALUES ('2182', 2, 'TaxScheme ID - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2183', 2, 'El codigo del tributo es invalido ', b'0');
INSERT INTO `err_error_code` VALUES ('2184', 2, 'El XML no contiene el tag o no existe informacion de TaxScheme ID de impuestos globales ', b'0');
INSERT INTO `err_error_code` VALUES ('2185', 2, 'TaxScheme Name - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2186', 2, 'El XML no contiene el tag o no existe informacion de TaxScheme Name de impuestos globales ', b'0');
INSERT INTO `err_error_code` VALUES ('2187', 2, 'El Numero de orden del item no cumple con el formato establecido ', b'0');
INSERT INTO `err_error_code` VALUES ('2188', 2, 'DebitedQuantity/@unitCode El dato ingresado no cumple con el estandar', b'0');
INSERT INTO `err_error_code` VALUES ('2189', 2, 'DebitedQuantity El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2190', 2, 'El XML no contiene el tag Price/cbc:PriceAmount en el detalle de los Items ', b'0');
INSERT INTO `err_error_code` VALUES ('2191', 2, 'El XML no contiene el tag Price/cbc:LineExtensionAmount en el detalle de los Items', b'0');
INSERT INTO `err_error_code` VALUES ('2192', 2, 'EL PriceTypeCode debe tener el valor 01 ', b'0');
INSERT INTO `err_error_code` VALUES ('2193', 2, 'cac:TaxCategory/cac:TaxScheme/cbc:ID El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2194', 2, 'El codigo del tributo es invalido ', b'0');
INSERT INTO `err_error_code` VALUES ('2195', 2, 'cac:TaxScheme/cbc:Name del item - No existe el tag o el dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2196', 2, 'cac:TaxCategory/cac:TaxScheme/cbc:TaxTypeCode El dato ingresado no cumple con el estandar', b'0');
INSERT INTO `err_error_code` VALUES ('2197', 2, 'El tipo de afectacion del IGV es incorrecto ', b'0');
INSERT INTO `err_error_code` VALUES ('2198', 2, 'El Nombre Internacional debe ser VAT', b'0');
INSERT INTO `err_error_code` VALUES ('2199', 2, 'El sistema de calculo del ISC es incorrecto ', b'0');
INSERT INTO `err_error_code` VALUES ('2200', 2, 'El Nombre Internacional debe ser EXC', b'0');
INSERT INTO `err_error_code` VALUES ('2201', 2, 'El tag cac:RequestedMonetaryTotal/cbc:PayableAmount debe tener informacion valida', b'0');
INSERT INTO `err_error_code` VALUES ('2202', 2, 'TaxAmount - El dato ingresado en impuestos globales no cumple con el estándar', b'0');
INSERT INTO `err_error_code` VALUES ('2203', 2, 'El XML no contiene el tag TaxAmount de impuestos globales', b'0');
INSERT INTO `err_error_code` VALUES ('2204', 2, 'El tipo de documento modificado por la Nota de Debito debe ser factura electronica o ticket ', b'0');
INSERT INTO `err_error_code` VALUES ('2205', 2, 'La serie o numero del documento modificado por la Nota de Debito no cumple con el formato establecido ', b'0');
INSERT INTO `err_error_code` VALUES ('2206', 2, 'Debe indicar los documentos afectados por la Nota de Debito ', b'0');
INSERT INTO `err_error_code` VALUES ('2207', 2, 'La factura relacionada en la nota de debito se encuentra de baja ', b'0');
INSERT INTO `err_error_code` VALUES ('2208', 2, 'La factura relacionada en la nota de debito esta registrada como rechazada ', b'0');
INSERT INTO `err_error_code` VALUES ('2209', 2, 'La factura relacionada en la Nota de debito no esta registrada ', b'0');
INSERT INTO `err_error_code` VALUES ('2210', 2, 'El dato ingresado no cumple con el formato RC-fecha-correlativo ', b'0');
INSERT INTO `err_error_code` VALUES ('2211', 2, 'El XML no contiene el tag ID ', b'0');
INSERT INTO `err_error_code` VALUES ('2212', 2, 'UBLVersionID - La versión del UBL del resumen de boletas no es correcta', b'0');
INSERT INTO `err_error_code` VALUES ('2213', 2, 'El XML no contiene el tag UBLVersionID', b'0');
INSERT INTO `err_error_code` VALUES ('2214', 2, 'CustomizationID - La versión del resumen de boletas no es correcta', b'0');
INSERT INTO `err_error_code` VALUES ('2215', 2, 'El XML no contiene el tag CustomizationID', b'0');
INSERT INTO `err_error_code` VALUES ('2216', 2, 'CustomerAssignedAccountID - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2217', 2, 'El XML no contiene el tag CustomerAssignedAccountID del emisor del documento ', b'0');
INSERT INTO `err_error_code` VALUES ('2218', 2, 'AdditionalAccountID - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2219', 2, 'El XML no contiene el tag AdditionalAccountID del emisor del documento ', b'0');
INSERT INTO `err_error_code` VALUES ('2220', 2, 'El ID debe coincidir con el nombre del archivo ', b'0');
INSERT INTO `err_error_code` VALUES ('2221', 2, 'El RUC debe coincidir con el RUC del nombre del archivo ', b'0');
INSERT INTO `err_error_code` VALUES ('2222', 2, 'El contribuyente no está autorizado a emitir comprobantes electronicos', b'0');
INSERT INTO `err_error_code` VALUES ('2223', 2, 'El archivo ya fue presentado anteriormente ', b'0');
INSERT INTO `err_error_code` VALUES ('2224', 2, 'Numero de RUC SOL no coincide con RUC emisor', b'0');
INSERT INTO `err_error_code` VALUES ('2225', 2, 'Numero de RUC del emisor no existe', b'0');
INSERT INTO `err_error_code` VALUES ('2226', 2, 'El contribuyente no esta activo', b'0');
INSERT INTO `err_error_code` VALUES ('2227', 2, 'El contribuyente no cumple con tipo de empresa o tributos requeridos', b'0');
INSERT INTO `err_error_code` VALUES ('2228', 2, 'RegistrationName - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2229', 2, 'El XML no contiene el tag RegistrationName del emisor del documento ', b'0');
INSERT INTO `err_error_code` VALUES ('2230', 2, 'IssueDate - El dato ingresado no cumple con el patron YYYY-MM-DD', b'0');
INSERT INTO `err_error_code` VALUES ('2231', 2, 'El XML no contiene el tag IssueDate', b'0');
INSERT INTO `err_error_code` VALUES ('2232', 2, 'IssueDate- El dato ingresado no es valido', b'0');
INSERT INTO `err_error_code` VALUES ('2233', 2, 'ReferenceDate - El dato ingresado no cumple con el patron YYYY-MM-DD ', b'0');
INSERT INTO `err_error_code` VALUES ('2234', 2, 'El XML no contiene el tag ReferenceDate', b'0');
INSERT INTO `err_error_code` VALUES ('2235', 2, 'ReferenceDate- El dato ingresado no es valido', b'0');
INSERT INTO `err_error_code` VALUES ('2236', 2, 'La fecha del IssueDate no debe ser mayor al Today ', b'0');
INSERT INTO `err_error_code` VALUES ('2237', 2, 'La fecha del ReferenceDate no debe ser mayor al Today ', b'0');
INSERT INTO `err_error_code` VALUES ('2238', 2, 'LineID - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2239', 2, 'LineID - El dato ingresado debe ser correlativo mayor a cero ', b'0');
INSERT INTO `err_error_code` VALUES ('2240', 2, 'El XML no contiene el tag LineID de SummaryDocumentsLine ', b'0');
INSERT INTO `err_error_code` VALUES ('2241', 2, 'DocumentTypeCode - El valor del tipo de documento es invalido ', b'0');
INSERT INTO `err_error_code` VALUES ('2242', 2, 'El XML no contiene el tag DocumentTypeCode ', b'0');
INSERT INTO `err_error_code` VALUES ('2243', 2, 'El dato ingresado no cumple con el patron SERIE ', b'0');
INSERT INTO `err_error_code` VALUES ('2244', 2, 'El XML no contiene el tag DocumentSerialID ', b'0');
INSERT INTO `err_error_code` VALUES ('2245', 2, 'El dato ingresado en StartDocumentNumberID debe ser numerico ', b'0');
INSERT INTO `err_error_code` VALUES ('2246', 2, 'El XML no contiene el tag StartDocumentNumberID ', b'0');
INSERT INTO `err_error_code` VALUES ('2247', 2, 'El dato ingresado en sac:EndDocumentNumberID debe ser numerico ', b'0');
INSERT INTO `err_error_code` VALUES ('2248', 2, 'El XML no contiene el tag sac:EndDocumentNumberID ', b'0');
INSERT INTO `err_error_code` VALUES ('2249', 2, 'Los rangos deben ser mayores a cero ', b'0');
INSERT INTO `err_error_code` VALUES ('2250', 2, 'En el rango de comprobantes, el EndDocumentNumberID debe ser mayor o igual al StartInvoiceNumberID ', b'0');
INSERT INTO `err_error_code` VALUES ('2251', 2, 'El dato ingresado en TotalAmount debe ser numerico mayor o igual a cero ', b'0');
INSERT INTO `err_error_code` VALUES ('2252', 2, 'El XML no contiene el tag TotalAmount', b'0');
INSERT INTO `err_error_code` VALUES ('2253', 2, 'El dato ingresado en TotalAmount debe ser numerico mayor a cero', b'0');
INSERT INTO `err_error_code` VALUES ('2254', 2, 'PaidAmount - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2255', 2, 'El XML no contiene el tag PaidAmount ', b'0');
INSERT INTO `err_error_code` VALUES ('2256', 2, 'InstructionID - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2257', 2, 'El XML no contiene el tag InstructionID ', b'0');
INSERT INTO `err_error_code` VALUES ('2258', 2, 'Debe indicar Referencia de Importes asociados a las boletas de venta', b'0');
INSERT INTO `err_error_code` VALUES ('2259', 2, 'Debe indicar 3 Referencias de Importes asociados a las boletas de venta ', b'0');
INSERT INTO `err_error_code` VALUES ('2260', 2, 'PaidAmount - El dato ingresado debe ser mayor o igual a 0.00', b'0');
INSERT INTO `err_error_code` VALUES ('2261', 2, 'cbc:Amount - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2262', 2, 'El XML no contiene el tag cbc:Amount', b'0');
INSERT INTO `err_error_code` VALUES ('2263', 2, 'ChargeIndicator - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2264', 2, 'El XML no contiene el tag ChargeIndicator', b'0');
INSERT INTO `err_error_code` VALUES ('2265', 2, 'Debe indicar Información acerca del Importe Total de Otros Cargos ', b'0');
INSERT INTO `err_error_code` VALUES ('2266', 2, 'Debe indicar cargos mayores o iguales a cero', b'0');
INSERT INTO `err_error_code` VALUES ('2267', 2, 'TaxScheme ID - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2268', 2, 'El codigo del tributo es invalido ', b'0');
INSERT INTO `err_error_code` VALUES ('2269', 2, 'El XML no contiene el tag TaxScheme ID de Información acerca del importe total de un tipo particular de impuesto ', b'0');
INSERT INTO `err_error_code` VALUES ('2270', 2, 'TaxScheme Name - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2271', 2, 'El XML no contiene el tag TaxScheme Name de impuesto ', b'0');
INSERT INTO `err_error_code` VALUES ('2272', 2, 'TaxScheme TaxTypeCode - El dato ingresado no cumple con el estandar', b'0');
INSERT INTO `err_error_code` VALUES ('2273', 2, 'TaxAmount - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2274', 2, 'El XML no contiene el tag TaxAmount', b'0');
INSERT INTO `err_error_code` VALUES ('2275', 2, 'Si el codigo de tributo es 2000, el nombre del tributo debe ser ISC ', b'0');
INSERT INTO `err_error_code` VALUES ('2276', 2, 'Si el codigo de tributo es 1000, el nombre del tributo debe ser IGV ', b'0');
INSERT INTO `err_error_code` VALUES ('2277', 2, 'No se ha consignado ninguna informacion del importe total de tributos ', b'0');
INSERT INTO `err_error_code` VALUES ('2278', 2, 'Debe indicar Información acerca del importe total de ISC e IGV ', b'0');
INSERT INTO `err_error_code` VALUES ('2279', 2, 'Debe indicar Items de consolidado de documentos', b'0');
INSERT INTO `err_error_code` VALUES ('2280', 2, 'Existen problemas con la informacion del resumen de comprobantes', b'0');
INSERT INTO `err_error_code` VALUES ('2281', 2, 'Error en la validacion de los rangos de los comprobantes', b'0');
INSERT INTO `err_error_code` VALUES ('2282', 2, 'Existe documento ya informado anteriormente ', b'0');
INSERT INTO `err_error_code` VALUES ('2283', 2, 'El dato ingresado no cumple con el formato RA-fecha-correlativo ', b'0');
INSERT INTO `err_error_code` VALUES ('2284', 2, 'El XML no contiene el tag ID', b'0');
INSERT INTO `err_error_code` VALUES ('2285', 2, 'El ID debe coincidir con el nombre del archivo ', b'0');
INSERT INTO `err_error_code` VALUES ('2286', 2, 'El RUC debe coincidir con el RUC del nombre del archivo ', b'0');
INSERT INTO `err_error_code` VALUES ('2287', 2, 'AdditionalAccountID - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2288', 2, 'El XML no contiene el tag AdditionalAccountID del emisor del documento ', b'0');
INSERT INTO `err_error_code` VALUES ('2289', 2, 'CustomerAssignedAccountID - El dato ingresado no cumple con el estándar', b'0');
INSERT INTO `err_error_code` VALUES ('2290', 2, 'El XML no contiene el tag CustomerAssignedAccountID del emisor del documento', b'0');
INSERT INTO `err_error_code` VALUES ('2291', 2, 'El contribuyente no esta autorizado a emitir comprobantes electrónicos', b'0');
INSERT INTO `err_error_code` VALUES ('2292', 2, 'Numero de RUC SOL no coincide con RUC emisor', b'0');
INSERT INTO `err_error_code` VALUES ('2293', 2, 'Numero de RUC del emisor no existe', b'0');
INSERT INTO `err_error_code` VALUES ('2294', 2, 'El contribuyente no esta activo', b'0');
INSERT INTO `err_error_code` VALUES ('2295', 2, 'El contribuyente no cumple con tipo de empresa o tributos requeridos', b'0');
INSERT INTO `err_error_code` VALUES ('2296', 2, 'RegistrationName - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2297', 2, 'El XML no contiene el tag RegistrationName del emisor del documento ', b'0');
INSERT INTO `err_error_code` VALUES ('2298', 2, 'IssueDate - El dato ingresado no cumple con el patron YYYY-MM-DD', b'0');
INSERT INTO `err_error_code` VALUES ('2299', 2, 'El XML no contiene el tag IssueDate ', b'0');
INSERT INTO `err_error_code` VALUES ('2300', 2, 'IssueDate - El dato ingresado no es valido', b'0');
INSERT INTO `err_error_code` VALUES ('2301', 2, 'La fecha del IssueDate no debe ser mayor al Today ', b'0');
INSERT INTO `err_error_code` VALUES ('2302', 2, 'ReferenceDate - El dato ingresado no cumple con el patron YYYY-MM-DD ', b'0');
INSERT INTO `err_error_code` VALUES ('2303', 2, 'El XML no contiene el tag ReferenceDate', b'0');
INSERT INTO `err_error_code` VALUES ('2304', 2, 'ReferenceDate - El dato ingresado no es valido', b'0');
INSERT INTO `err_error_code` VALUES ('2305', 2, 'LineID - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2306', 2, 'LineID - El dato ingresado debe ser correlativo mayor a cero ', b'0');
INSERT INTO `err_error_code` VALUES ('2307', 2, 'El XML no contiene el tag LineID de VoidedDocumentsLine ', b'0');
INSERT INTO `err_error_code` VALUES ('2308', 2, 'DocumentTypeCode - El valor del tipo de documento es invalido ', b'0');
INSERT INTO `err_error_code` VALUES ('2309', 2, 'El XML no contiene el tag DocumentTypeCode ', b'0');
INSERT INTO `err_error_code` VALUES ('2310', 2, 'El dato ingresado no cumple con el patron SERIE ', b'0');
INSERT INTO `err_error_code` VALUES ('2311', 2, 'El XML no contiene el tag DocumentSerialID ', b'0');
INSERT INTO `err_error_code` VALUES ('2312', 2, 'El dato ingresado en DocumentNumberID debe ser numerico y como maximo de 8 digitos ', b'0');
INSERT INTO `err_error_code` VALUES ('2313', 2, 'El XML no contiene el tag DocumentNumberID ', b'0');
INSERT INTO `err_error_code` VALUES ('2314', 2, 'El dato ingresado en VoidReasonDescription debe contener información válida ', b'0');
INSERT INTO `err_error_code` VALUES ('2315', 2, 'El XML no contiene el tag VoidReasonDescription ', b'0');
INSERT INTO `err_error_code` VALUES ('2316', 2, 'Debe indicar Items en VoidedDocumentsLine', b'0');
INSERT INTO `err_error_code` VALUES ('2317', 2, 'Error al procesar el resumen de anulados', b'0');
INSERT INTO `err_error_code` VALUES ('2318', 2, 'CustomizationID - La version del documento no es correcta', b'0');
INSERT INTO `err_error_code` VALUES ('2319', 2, 'El XML no contiene el tag CustomizationID', b'0');
INSERT INTO `err_error_code` VALUES ('2320', 2, 'UBLVersionID - La version del UBL  no es la correcta', b'0');
INSERT INTO `err_error_code` VALUES ('2321', 2, 'El XML no contiene el tag UBLVersionID', b'0');
INSERT INTO `err_error_code` VALUES ('2322', 2, 'Error en la validacion de los rangos', b'0');
INSERT INTO `err_error_code` VALUES ('2323', 2, 'Existe documento ya informado anteriormente en una comunicacion de baja ', b'0');
INSERT INTO `err_error_code` VALUES ('2324', 2, 'El archivo de comunicacion de baja ya fue presentado anteriormente ', b'0');
INSERT INTO `err_error_code` VALUES ('2325', 2, 'El certificado usado no es el comunicado a SUNAT ', b'0');
INSERT INTO `err_error_code` VALUES ('2326', 2, 'El certificado usado se encuentra de baja ', b'0');
INSERT INTO `err_error_code` VALUES ('2327', 2, 'El certificado usado no se encuentra vigente ', b'0');
INSERT INTO `err_error_code` VALUES ('2328', 2, 'El certificado usado se encuentra revocado ', b'0');
INSERT INTO `err_error_code` VALUES ('2329', 2, 'La fecha de emision se encuentra fuera del limite permitido ', b'0');
INSERT INTO `err_error_code` VALUES ('2330', 2, 'La fecha de generación de la comunicación debe ser igual a la fecha consignada en el nombre del archivo ', b'0');
INSERT INTO `err_error_code` VALUES ('2331', 2, 'Número de RUC del nombre del archivo no coincide con el consignado en el contenido del archivo XML', b'0');
INSERT INTO `err_error_code` VALUES ('2332', 2, 'Número de Serie del nombre del archivo no coincide con el consignado en el contenido del archivo XML', b'0');
INSERT INTO `err_error_code` VALUES ('2333', 2, 'Número de documento en el nombre del archivo no coincide con el consignado en el contenido del XML', b'0');
INSERT INTO `err_error_code` VALUES ('2334', 2, 'El documento electrónico ingresado ha sido alterado ', b'0');
INSERT INTO `err_error_code` VALUES ('2335', 2, 'El documento electrónico ingresado ha sido alterado ', b'0');
INSERT INTO `err_error_code` VALUES ('2336', 2, 'Ocurrió un error en el proceso de validación de la firma digital ', b'0');
INSERT INTO `err_error_code` VALUES ('2337', 2, 'La moneda debe ser la misma en todo el documento ', b'0');
INSERT INTO `err_error_code` VALUES ('2338', 2, 'La moneda debe ser la misma en todo el documento ', b'0');
INSERT INTO `err_error_code` VALUES ('2339', 2, 'El dato ingresado en PayableAmount no cumple con el formato establecido ', b'0');
INSERT INTO `err_error_code` VALUES ('2340', 2, 'El valor ingresado en AdditionalMonetaryTotal/cbc:ID es incorrecto ', b'0');
INSERT INTO `err_error_code` VALUES ('2341', 2, 'AdditionalMonetaryTotal/cbc:ID debe tener valor ', b'0');
INSERT INTO `err_error_code` VALUES ('2342', 2, 'Fecha de emision de la factura no coincide con la informada en la comunicacion ', b'0');
INSERT INTO `err_error_code` VALUES ('2343', 2, 'cac:TaxTotal/cac:TaxSubtotal/cbc:TaxAmount - El dato ingresado no cumple con el estandar ', b'0');
INSERT INTO `err_error_code` VALUES ('2344', 2, 'El XML no contiene el tag cac:TaxTotal/cac:TaxSubtotal/cbc:TaxAmount', b'0');
INSERT INTO `err_error_code` VALUES ('2345', 2, 'La serie no corresponde al tipo de comprobante ', b'0');
INSERT INTO `err_error_code` VALUES ('2346', 2, 'La fecha de generación del resumen debe ser igual a la fecha consignada en el nombre del archivo ', b'0');
INSERT INTO `err_error_code` VALUES ('2347', 2, 'Los rangos informados en el archivo XML se encuentran duplicados o superpuestos', b'0');
INSERT INTO `err_error_code` VALUES ('2348', 2, 'Los documentos informados en el archivo XML se encuentran duplicados', b'0');
INSERT INTO `err_error_code` VALUES ('2349', 2, 'Debe consignar solo un elemento sac:AdditionalMonetaryTotal con cbc:ID igual a 1001 ', b'0');
INSERT INTO `err_error_code` VALUES ('2350', 2, 'Debe consignar solo un elemento sac:AdditionalMonetaryTotal con cbc:ID igual a 1002 ', b'0');
INSERT INTO `err_error_code` VALUES ('2351', 2, 'Debe consignar solo un elemento sac:AdditionalMonetaryTotal con cbc:ID igual a 1003 ', b'0');
INSERT INTO `err_error_code` VALUES ('2352', 2, 'Debe consignar solo un elemento cac:TaxTotal a nivel global para IGV (cbc:ID igual a 1000) ', b'0');
INSERT INTO `err_error_code` VALUES ('2353', 2, 'Debe consignar solo un elemento cac:TaxTotal a nivel global para ISC (cbc:ID igual a 2000) ', b'0');
INSERT INTO `err_error_code` VALUES ('2354', 2, 'Debe consignar solo un elemento cac:TaxTotal a nivel global para Otros (cbc:ID igual a 9999) ', b'0');
INSERT INTO `err_error_code` VALUES ('2355', 2, 'Debe consignar solo un elemento cac:TaxTotal a nivel de item para IGV (cbc:ID igual a 1000) ', b'0');
INSERT INTO `err_error_code` VALUES ('2356', 2, 'Debe consignar solo un elemento cac:TaxTotal a nivel de item para ISC (cbc:ID igual a 2000) ', b'0');
INSERT INTO `err_error_code` VALUES ('2357', 2, 'Debe consignar solo un elemento sac:BillingPayment a nivel de item con cbc:InstructionID igual a 01 ', b'0');
INSERT INTO `err_error_code` VALUES ('2358', 2, 'Debe consignar solo un elemento sac:BillingPayment a nivel de item con cbc:InstructionID igual a 02 ', b'0');
INSERT INTO `err_error_code` VALUES ('2359', 2, 'Debe consignar solo un elemento sac:BillingPayment a nivel de item con cbc:InstructionID igual a 03 ', b'0');
INSERT INTO `err_error_code` VALUES ('2360', 2, 'Debe consignar solo un elemento sac:BillingPayment a nivel de item con cbc:InstructionID igual a 04', b'0');
INSERT INTO `err_error_code` VALUES ('2361', 2, 'Debe consignar solo un elemento cac:TaxTotal a nivel de item para Otros (cbc:ID igual a 9999) ', b'0');
INSERT INTO `err_error_code` VALUES ('2362', 2, 'Debe consignar solo un tag cac:AccountingSupplierParty/cbc:AdditionalAccountID ', b'0');
INSERT INTO `err_error_code` VALUES ('2363', 2, 'Debe consignar solo un tag cac:AccountingCustomerParty/cbc:AdditionalAccountID ', b'0');
INSERT INTO `err_error_code` VALUES ('2364', 2, 'El comprobante contiene un tipo y número de Guía de Remisión repetido ', b'0');
INSERT INTO `err_error_code` VALUES ('2365', 2, 'El comprobante contiene un tipo y número de Documento Relacionado repetido ', b'0');
INSERT INTO `err_error_code` VALUES ('2366', 2, 'El codigo en el tag sac:AdditionalProperty/cbc:ID debe tener 4 posiciones', b'0');
INSERT INTO `err_error_code` VALUES ('2367', 2, 'El dato ingresado en PriceAmount del Precio de venta unitario por item no cumple con el formato establecido ', b'0');
INSERT INTO `err_error_code` VALUES ('2368', 2, 'El dato ingresado en TaxSubtotal/cbc:TaxAmount del item no cumple con el formato establecido ', b'0');
INSERT INTO `err_error_code` VALUES ('2369', 2, 'El dato ingresado en PriceAmount del Valor de venta unitario por item no cumple con el formato establecido ', b'0');
INSERT INTO `err_error_code` VALUES ('2370', 2, 'El dato ingresado en LineExtensionAmount del item no cumple con el formato establecido ', b'0');
INSERT INTO `err_error_code` VALUES ('2371', 2, 'El XML no contiene el tag cbc:TaxExemptionReasonCode de Afectacion al IGV ', b'0');
INSERT INTO `err_error_code` VALUES ('2372', 2, 'El tag en el item cac:TaxTotal/cbc:TaxAmount debe tener el mismo valor que cac:TaxTotal/cac:TaxSubtotal/cbc:TaxAmount ', b'0');
INSERT INTO `err_error_code` VALUES ('2373', 2, 'Si existe monto de ISC en el ITEM debe especificar el sistema de calculo ', b'0');
INSERT INTO `err_error_code` VALUES ('2374', 2, 'La factura a dar de baja tiene una fecha de recepcion fuera del plazo permitido ', b'0');
INSERT INTO `err_error_code` VALUES ('2375', 2, 'Fecha de emision de la boleta no coincide con la fecha de emision consignada en la comunicacion ', b'0');
INSERT INTO `err_error_code` VALUES ('2376', 2, 'La boleta de venta a dar de baja fue informada en un resumen con fecha de recepcion fuera del plazo permitido ', b'0');
INSERT INTO `err_error_code` VALUES ('2377', 2, 'El Name o TaxTypeCode debe corresponder con el Id para el IGV ', b'0');
INSERT INTO `err_error_code` VALUES ('2378', 2, 'El Name o TaxTypeCode debe corresponder con el Id para el ISC ', b'0');
INSERT INTO `err_error_code` VALUES ('2379', 2, 'La numeracion de boleta de venta a dar de baja fue generada en una fecha fuera del plazo permitido ', b'0');
INSERT INTO `err_error_code` VALUES ('2380', 2, 'El documento tiene observaciones', b'0');
INSERT INTO `err_error_code` VALUES ('2381', 2, 'Comprobante no cumple con el Grupo 1: No todos los items corresponden a operaciones gravadas a IGV ', b'0');
INSERT INTO `err_error_code` VALUES ('2382', 2, 'Comprobante no cumple con el Grupo 2: No todos los items corresponden a operaciones inafectas o exoneradas al IGV ', b'0');
INSERT INTO `err_error_code` VALUES ('2383', 2, 'Comprobante no cumple con el Grupo 3: Falta leyenda con codigo 1002 ', b'0');
INSERT INTO `err_error_code` VALUES ('2384', 2, 'Comprobante no cumple con el Grupo 3: Existe item con operación onerosa ', b'0');
INSERT INTO `err_error_code` VALUES ('2385', 2, 'Comprobante no cumple con el Grupo 4: Debe exitir Total descuentos mayor a cero ', b'0');
INSERT INTO `err_error_code` VALUES ('2386', 2, 'Comprobante no cumple con el Grupo 5: Todos los items deben tener operaciones afectas a ISC ', b'0');
INSERT INTO `err_error_code` VALUES ('2387', 2, 'Comprobante no cumple con el Grupo 6: El monto de percepcion no existe o es cero ', b'0');
INSERT INTO `err_error_code` VALUES ('2388', 2, 'Comprobante no cumple con el Grupo 6: Todos los items deben tener código de Afectación al IGV igual a 10 ', b'0');
INSERT INTO `err_error_code` VALUES ('2389', 2, 'Comprobante no cumple con el Grupo 7: El codigo de moneda no es diferente a PEN ', b'0');
INSERT INTO `err_error_code` VALUES ('2390', 2, 'Comprobante no cumple con el Grupo 8: No todos los items corresponden a operaciones gravadas a IGV ', b'0');
INSERT INTO `err_error_code` VALUES ('2391', 2, 'Comprobante no cumple con el Grupo 9: No todos los items corresponden a operaciones inafectas o exoneradas al IGV ', b'0');
INSERT INTO `err_error_code` VALUES ('2392', 2, 'Comprobante no cumple con el Grupo 10: Falta leyenda con codigo 1002 ', b'0');
INSERT INTO `err_error_code` VALUES ('2393', 2, 'Comprobante no cumple con el Grupo 10: Existe item con operación onerosa ', b'0');
INSERT INTO `err_error_code` VALUES ('2394', 2, 'Comprobante no cumple con el Grupo 11: Debe existir Total descuentos mayor a cero ', b'0');
INSERT INTO `err_error_code` VALUES ('2395', 2, 'Comprobante no cumple con el Grupo 12: El codigo de moneda no es diferente a PEN ', b'0');
INSERT INTO `err_error_code` VALUES ('2396', 2, 'Si el monto total es mayor a S/. 700.00 debe consignar tipo y numero de documento del adquiriente ', b'0');
INSERT INTO `err_error_code` VALUES ('2397', 2, 'El tipo de documento del adquiriente no puede ser Numero de RUC ', b'0');
INSERT INTO `err_error_code` VALUES ('2398', 2, 'El documento a dar de baja se encuentra rechazado ', b'0');
INSERT INTO `err_error_code` VALUES ('2399', 2, 'El tipo de documento modificado por la Nota de credito debe ser boleta electronica', b'0');
INSERT INTO `err_error_code` VALUES ('2400', 2, 'El tipo de documento modificado por la Nota de debito debe ser boleta electronica ', b'0');
INSERT INTO `err_error_code` VALUES ('2401', 2, 'No se puede leer (parsear) el archivo XML ', b'0');
INSERT INTO `err_error_code` VALUES ('2402', 2, 'El caso de prueba no existe', b'0');
INSERT INTO `err_error_code` VALUES ('2403', 2, 'La numeracion o nombre del documento ya ha sido enviado anteriormente', b'0');
INSERT INTO `err_error_code` VALUES ('2404', 2, 'Documento afectado por la nota electronica no se encuentra autorizado ', b'0');
INSERT INTO `err_error_code` VALUES ('2405', 2, 'Contribuyente no se encuentra autorizado como emisor de boletas electronicas ', b'0');
INSERT INTO `err_error_code` VALUES ('2406', 2, 'Existe mas de un tag sac:AdditionalMonetaryTotal con el mismo ID ', b'0');
INSERT INTO `err_error_code` VALUES ('2407', 2, 'Existe mas de un tag sac:AdditionalProperty con el mismo ID ', b'0');
INSERT INTO `err_error_code` VALUES ('2408', 2, 'El dato ingresado en PriceAmount del Valor referencial unitario por item no cumple con el formato establecido ', b'0');
INSERT INTO `err_error_code` VALUES ('2409', 2, 'Existe mas de un tag cac:AlternativeConditionPrice con el mismo cbc:PriceTypeCode ', b'0');
INSERT INTO `err_error_code` VALUES ('2410', 2, 'Se ha consignado un valor invalido en el campo cbc:PriceTypeCode ', b'0');
INSERT INTO `err_error_code` VALUES ('2411', 2, 'Ha consignado mas de un elemento cac:AllowanceCharge con el mismo campo cbc:ChargeIndicator ', b'0');
INSERT INTO `err_error_code` VALUES ('2412', 2, 'Se ha consignado mas de un documento afectado por la nota (tag cac:BillingReference) ', b'0');
INSERT INTO `err_error_code` VALUES ('2413', 2, 'Se ha consignado mas de un motivo o sustento de la nota (tag cac:DiscrepancyResponse/cbc:Description) ', b'0');
INSERT INTO `err_error_code` VALUES ('2414', 2, 'No se ha consignado en la nota el tag cac:DiscrepancyResponse ', b'0');
INSERT INTO `err_error_code` VALUES ('2415', 2, 'Se ha consignado en la nota mas de un tag cac:DiscrepancyResponse ', b'0');
INSERT INTO `err_error_code` VALUES ('2416', 2, 'Si existe leyenda Transferida Gratuita debe consignar Total Valor de Venta de Operaciones Gratuitas', b'0');
INSERT INTO `err_error_code` VALUES ('2417', 2, 'Debe consignar Valor Referencial unitario por ítem en operaciones no onerosas', b'0');
INSERT INTO `err_error_code` VALUES ('2418', 2, 'Si consigna Valor Referencial unitario por ítem en operaciones no onerosas, la operación debe ser no onerosa', b'0');
INSERT INTO `err_error_code` VALUES ('2419', 2, 'El dato ingresado en AllowanceTotalAmount no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('2420', 2, 'Ya transcurrieron mas de 25 dias calendarios para concluir con su proceso de homologacion', b'0');
INSERT INTO `err_error_code` VALUES ('2421', 2, 'Debe indicar  toda la información de  sustento de traslado de bienes', b'0');
INSERT INTO `err_error_code` VALUES ('2422', 2, 'El valor unitario debe ser menor al precio unitario', b'0');
INSERT INTO `err_error_code` VALUES ('2423', 2, 'Si ha consignado monto ISC a nivel de ítem, debe consignar un monto a nivel de total', b'0');
INSERT INTO `err_error_code` VALUES ('2424', 2, 'RC Debe consignar solo un elemento sac:BillingPayment a nivel de ítem con cbc:InstructionID igual a 05', b'0');
INSERT INTO `err_error_code` VALUES ('2425', 2, 'Si la  operación es gratuita PriceTypeCode =02 y cbc:PriceAmount> 0 el código de afectación de igv debe ser  no onerosa es  decir diferente de 10,20,30', b'0');
INSERT INTO `err_error_code` VALUES ('2426', 2, 'Documentos relacionados duplicados en el comprobante', b'0');
INSERT INTO `err_error_code` VALUES ('2427', 2, 'Solo debe de existir un tag AdditionalInformation', b'0');
INSERT INTO `err_error_code` VALUES ('2428', 2, 'Comprobante no cumple con grupo de facturas con detracciones', b'0');
INSERT INTO `err_error_code` VALUES ('2429', 2, 'Comprobante no cumple con grupo de facturas con comercio exterior', b'0');
INSERT INTO `err_error_code` VALUES ('2430', 2, 'Comprobante no cumple con grupo de facturas con tag de factura guía', b'0');
INSERT INTO `err_error_code` VALUES ('2431', 2, 'Comprobante no cumple con grupo de facturas con tags no tributarios', b'0');
INSERT INTO `err_error_code` VALUES ('2432', 2, 'Comprobante no cumple con grupo de boletas con tags no tributarios', b'0');
INSERT INTO `err_error_code` VALUES ('2433', 2, 'Comprobante no cumple con grupo de facturas con tag venta itinerante', b'0');
INSERT INTO `err_error_code` VALUES ('2434', 2, 'Comprobante no cumple con grupo de boletas con tag venta itinerante', b'0');
INSERT INTO `err_error_code` VALUES ('2435', 2, 'Comprobante no cumple con grupo de boletas con ISC', b'0');
INSERT INTO `err_error_code` VALUES ('2436', 2, 'Comprobante no cumple con el grupo de boletas de venta con percepción: El monto de percepción no existe o es cero', b'0');
INSERT INTO `err_error_code` VALUES ('2437', 2, 'Comprobante no cumple con el grupo de boletas de venta con percepción: Todos los ítems deben tener código de Afectación al IGV igual a 10', b'0');
INSERT INTO `err_error_code` VALUES ('2438', 2, 'Comprobante no cumple con grupo de facturas con tag venta anticipada I', b'0');
INSERT INTO `err_error_code` VALUES ('2439', 2, 'Comprobante no cumple con grupo de facturas con tag venta anticipada II', b'0');
INSERT INTO `err_error_code` VALUES ('2500', 2, 'Ingresar descripción y valor venta por ítem para documento de anticipos', b'0');
INSERT INTO `err_error_code` VALUES ('2501', 2, 'Valor venta debe ser mayor a cero', b'0');
INSERT INTO `err_error_code` VALUES ('2502', 2, 'Los valores totales deben ser mayores a cero', b'0');
INSERT INTO `err_error_code` VALUES ('2503', 2, 'PaidAmount: monto anticipado por documento debe ser mayor a cero', b'0');
INSERT INTO `err_error_code` VALUES ('2504', 2, 'Falta referencia de la factura relacionada con anticipo', b'0');
INSERT INTO `err_error_code` VALUES ('2505', 2, 'cac:PrepaidPayment/cbc:ID/@SchemaID: Código de referencia debe ser 02 o 03', b'0');
INSERT INTO `err_error_code` VALUES ('2506', 2, 'cac:PrepaidPayment/cbc:ID: Factura o boleta no existe o comunicada de Baja', b'0');
INSERT INTO `err_error_code` VALUES ('2507', 2, 'Factura relacionada con anticipo no corresponde como factura de anticipo', b'0');
INSERT INTO `err_error_code` VALUES ('2508', 2, 'Ingresar documentos por anticipos', b'0');
INSERT INTO `err_error_code` VALUES ('2509', 2, 'Total de anticipos diferente a los montos anticipados por documento', b'0');
INSERT INTO `err_error_code` VALUES ('2510', 2, 'Nro nombre del documento no tiene el formato correcto', b'0');
INSERT INTO `err_error_code` VALUES ('2511', 2, 'El tipo de documento no es aceptado', b'0');
INSERT INTO `err_error_code` VALUES ('2512', 2, 'No existe información de serie o número', b'0');
INSERT INTO `err_error_code` VALUES ('2513', 2, 'Dato no cumple con formato de acuerdo al número de comprobante', b'0');
INSERT INTO `err_error_code` VALUES ('2514', 2, 'No existe información de receptor de documento', b'0');
INSERT INTO `err_error_code` VALUES ('2515', 2, 'Dato ingresado no cumple con catalogo 6', b'0');
INSERT INTO `err_error_code` VALUES ('2516', 2, 'Debe indicar tipo de documento', b'0');
INSERT INTO `err_error_code` VALUES ('2517', 2, 'Dato no cumple con formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('2518', 2, 'Calculo IGV no es correcto', b'0');
INSERT INTO `err_error_code` VALUES ('2519', 2, 'El importe total no coincide con la sumatoria de los valores de venta mas los tributos mas los cargos', b'0');
INSERT INTO `err_error_code` VALUES ('2520', 2, 'cac:PrepaidPayment/cbc:InstructionID/@SchemaID – El tipo documento debe ser 6 del catálogo de tipo de documento', b'0');
INSERT INTO `err_error_code` VALUES ('2521', 2, 'cac:PrepaidPayment/cbc:ID - El dato ingresado debe indicar SERIE-CORRELATIVO del documento que se realizó el anticipo', b'0');
INSERT INTO `err_error_code` VALUES ('2522', 2, 'No existe información del documento del anticipo.', b'0');
INSERT INTO `err_error_code` VALUES ('2523', 2, 'GrossWeightMeasure – El dato ingresado no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('2524', 2, 'El dato ingresado en Amount no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('2525', 2, 'El dato ingresado en Quantity no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('2526', 2, 'El dato ingresado en Percent no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('2527', 2, 'PrepaidAmount: Monto total anticipado debe ser mayor a cero', b'0');
INSERT INTO `err_error_code` VALUES ('2528', 2, 'cac:OriginatorDocumentReference/cbc:ID/@SchemaID – El tipo documento debe ser 6 del catálogo de tipo de documento', b'0');
INSERT INTO `err_error_code` VALUES ('2529', 2, 'RUC que emitió documento de anticipo, no existe', b'0');
INSERT INTO `err_error_code` VALUES ('2530', 2, 'RUC que solicita la emisión de la factura, no existe', b'0');
INSERT INTO `err_error_code` VALUES ('2531', 2, 'Código del Local Anexo del emisor no existe', b'0');
INSERT INTO `err_error_code` VALUES ('2532', 2, 'No existe información de modalidad de transporte', b'0');
INSERT INTO `err_error_code` VALUES ('2533', 2, 'Si ha consignado Transporte Privado, debe consignar Licencia de conducir, Placa, N constancia de inscripción y marca del vehículo', b'0');
INSERT INTO `err_error_code` VALUES ('2534', 2, 'Si ha consignado Transporte púbico, debe consignar Datos del transportista', b'0');
INSERT INTO `err_error_code` VALUES ('2535', 2, 'La nota de crédito por otros conceptos tributarios debe tener Otros Documentos Relacionados', b'0');
INSERT INTO `err_error_code` VALUES ('2536', 2, 'Serie y número no se encuentra registrado como baja por cambio de destinatario', b'0');
INSERT INTO `err_error_code` VALUES ('2537', 2, 'cac:OrderReference/cac:DocumentReference/cbc:DocumentTypeCode - El tipo de documento de serie y número dado de baja es incorrecta', b'0');
INSERT INTO `err_error_code` VALUES ('2538', 2, 'El contribuyente no se encuentra autorizado como emisor electrónico de Guía o de factura o de boleta Factura GEM', b'0');
INSERT INTO `err_error_code` VALUES ('2539', 2, 'El contribuyente no está activo', b'0');
INSERT INTO `err_error_code` VALUES ('2540', 2, 'El contribuyente no está habido', b'0');
INSERT INTO `err_error_code` VALUES ('2541', 2, 'El XML no contiene el tag o no existe información del tipo de documento identidad del remitente', b'0');
INSERT INTO `err_error_code` VALUES ('2542', 2, 'cac:DespatchSupplierParty/cbc:CustomerAssignedAccountID@schemeID - El valor ingresado como tipo de documento identidad del remitente es incorrecta', b'0');
INSERT INTO `err_error_code` VALUES ('2543', 2, 'El XML no contiene el tag o no existe información de la dirección completa y detallada en domicilio fiscal', b'0');
INSERT INTO `err_error_code` VALUES ('2544', 2, 'El XML no contiene el tag o no existe información de la provincia en domicilio fiscal', b'0');
INSERT INTO `err_error_code` VALUES ('2545', 2, 'El XML no contiene el tag o no existe información del departamento en domicilio fiscal', b'0');
INSERT INTO `err_error_code` VALUES ('2546', 2, 'El XML no contiene el tag o no existe información del distrito en domicilio fiscal', b'0');
INSERT INTO `err_error_code` VALUES ('2547', 2, 'El XML no contiene el tag o no existe información del país en domicilio fiscal', b'0');
INSERT INTO `err_error_code` VALUES ('2548', 2, 'El valor del país inválido', b'0');
INSERT INTO `err_error_code` VALUES ('2549', 2, 'El XML no contiene el tag o no existe información del tipo de documento identidad del destinatario', b'0');
INSERT INTO `err_error_code` VALUES ('2550', 2, 'cac:DeliveryCustomerParty/cbc:CustomerAssignedAccountID@schemeID - El dato ingresado de tipo de documento identidad del destinatario no cumple con el estándar', b'0');
INSERT INTO `err_error_code` VALUES ('2551', 2, 'El XML no contiene el tag o no existe información de CustomerAssignedAccountID del proveedor de servicios', b'0');
INSERT INTO `err_error_code` VALUES ('2552', 2, 'El XML no contiene el tag o no existe información del tipo de documento identidad del proveedor', b'0');
INSERT INTO `err_error_code` VALUES ('2553', 2, 'cac:SellerSupplierParty/cbc:CustomerAssignedAccountID@schemeID - El dato ingresado no es valido', b'0');
INSERT INTO `err_error_code` VALUES ('2554', 2, 'Para el motivo de traslado ingresado el Destinatario debe ser igual al remitente', b'0');
INSERT INTO `err_error_code` VALUES ('2555', 2, 'Destinatario no debe ser igual al remitente', b'0');
INSERT INTO `err_error_code` VALUES ('2556', 2, 'cbc:TransportModeCode -  dato ingresado no es valido', b'0');
INSERT INTO `err_error_code` VALUES ('2557', 2, 'La fecha del StartDate no debe ser menor al Today', b'0');
INSERT INTO `err_error_code` VALUES ('2558', 2, 'El XML no contiene el tag o no existe información en Numero de Ruc del transportista', b'0');
INSERT INTO `err_error_code` VALUES ('2559', 2, '/DespatchAdvice/cac:Shipment/cac:ShipmentStage/cac:CarrierParty/cac:PartyIdentification/cbc:ID  - El dato ingresado no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('2560', 2, 'Transportista  no debe ser igual al remitente o destinatario', b'0');
INSERT INTO `err_error_code` VALUES ('2561', 2, 'El XML no contiene el tag o no existe información del tipo de documento identidad del transportista', b'0');
INSERT INTO `err_error_code` VALUES ('2562', 2, '/DespatchAdvice/cac:Shipment/cac:ShipmentStage/cac:CarrierParty/cac:PartyIdentification/cbc:ID@schemeID  - El dato ingresado no es valido', b'0');
INSERT INTO `err_error_code` VALUES ('2563', 2, 'El XML no contiene el tag o no existe información de Apellido, Nombre o razón social del transportista', b'0');
INSERT INTO `err_error_code` VALUES ('2564', 2, 'Razón social transportista - El dato ingresado no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('2565', 2, 'El XML no contiene el tag o no existe información del tipo de unidad de transporte', b'0');
INSERT INTO `err_error_code` VALUES ('2566', 2, 'El XML no contiene el tag o no existe información del Número de placa del vehículo', b'0');
INSERT INTO `err_error_code` VALUES ('2567', 2, 'Número de placa del vehículo - El dato ingresado no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('2568', 2, 'El XML no contiene el tag o no existe información en el Numero de documento de identidad del conductor', b'0');
INSERT INTO `err_error_code` VALUES ('2569', 2, 'Documento identidad del conductor - El dato ingresado no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('2570', 2, 'El XML no contiene el tag o no existe información del tipo de documento identidad del conductor', b'0');
INSERT INTO `err_error_code` VALUES ('2571', 2, 'cac:DriverPerson/ID@schemeID - El valor ingresado de tipo de documento identidad de conductor es incorrecto', b'0');
INSERT INTO `err_error_code` VALUES ('2572', 2, 'El XML no contiene el tag o no existe información del Numero de licencia del conductor', b'0');
INSERT INTO `err_error_code` VALUES ('2573', 2, 'Numero de licencia del conductor - El dato ingresado no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('2574', 2, 'El XML no contiene el tag o no existe información de dirección detallada de punto de llegada', b'0');
INSERT INTO `err_error_code` VALUES ('2575', 2, 'El XML no contiene el tag o no existe información de CityName', b'0');
INSERT INTO `err_error_code` VALUES ('2576', 2, 'El XML no contiene el tag o no existe información de District', b'0');
INSERT INTO `err_error_code` VALUES ('2577', 2, 'El XML no contiene el tag o no existe información de dirección detallada de punto de partida', b'0');
INSERT INTO `err_error_code` VALUES ('2578', 2, 'El XML no contiene el tag o no existe información de CityName', b'0');
INSERT INTO `err_error_code` VALUES ('2579', 2, 'El XML no contiene el tag o no existe información de District', b'0');
INSERT INTO `err_error_code` VALUES ('2580', 2, 'El XML No contiene el tag o no existe información de la cantidad del ítem', b'0');
INSERT INTO `err_error_code` VALUES ('2600', 2, 'El comprobante fue enviado fuera del plazo permitido.', b'0');
INSERT INTO `err_error_code` VALUES ('2601', 2, 'Señor contribuyente a la fecha no se encuentra registrado ó habilitado con la condición de Agente de percepción.', b'0');
INSERT INTO `err_error_code` VALUES ('2602', 2, 'El régimen percepción enviado no corresponde con su condición de Agente de percepción.', b'0');
INSERT INTO `err_error_code` VALUES ('2603', 2, 'La tasa de percepción enviada no corresponde con el régimen de percepción.', b'0');
INSERT INTO `err_error_code` VALUES ('2604', 2, 'El Cliente no puede ser el mismo que el Emisor del comprobante de percepción.', b'0');
INSERT INTO `err_error_code` VALUES ('2605', 2, 'Número de RUC del Cliente no existe.', b'0');
INSERT INTO `err_error_code` VALUES ('2606', 2, 'Documento de identidad del Cliente no existe.', b'0');
INSERT INTO `err_error_code` VALUES ('2607', 2, 'La moneda del importe de cobro debe ser la misma que la del documento relacionado.', b'0');
INSERT INTO `err_error_code` VALUES ('2608', 2, 'Los montos de pago, percibidos y montos cobrados consignados para el documento relacionado no son correctos.', b'0');
INSERT INTO `err_error_code` VALUES ('2609', 2, 'El comprobante electrónico enviado no se encuentra registrado en la SUNAT.', b'0');
INSERT INTO `err_error_code` VALUES ('2610', 2, 'La fecha de emisión, Importe total del comprobante y la moneda del comprobante electrónico enviado no son los registrados en los Sistemas de SUNAT.', b'0');
INSERT INTO `err_error_code` VALUES ('2611', 2, 'El comprobante electrónico no ha sido emitido al cliente.', b'0');
INSERT INTO `err_error_code` VALUES ('2612', 2, 'La fecha de cobro debe estar entre el primer día calendario del mes al cual corresponde la fecha de emisión del comprobante de percepción o desde la fecha de emisión del comprobante relacionado.', b'0');
INSERT INTO `err_error_code` VALUES ('2613', 2, 'El Nro. de documento con número de cobro ya se encuentra en la Relación de Documentos Relacionados agregados.', b'0');
INSERT INTO `err_error_code` VALUES ('2614', 2, 'El Nro. de documento con el número de cobro ya se encuentra registrado como pago realizado.', b'0');
INSERT INTO `err_error_code` VALUES ('2615', 2, 'Importe total percibido debe ser igual a la suma de los importes percibidos por cada documento relacionado.', b'0');
INSERT INTO `err_error_code` VALUES ('2616', 2, 'Importe total cobrado debe ser igual a la suma de los importe totales cobrados por cada documento relacionado.', b'0');
INSERT INTO `err_error_code` VALUES ('2617', 2, 'Señor contribuyente a la fecha no se encuentra registrado ó habilitado con la condición de Agente de retención.', b'0');
INSERT INTO `err_error_code` VALUES ('2618', 2, 'El régimen retención enviado no corresponde con su condición de Agente de retención.', b'0');
INSERT INTO `err_error_code` VALUES ('2619', 2, 'La tasa de retención enviada no corresponde con el régimen de retención.', b'0');
INSERT INTO `err_error_code` VALUES ('2620', 2, 'El Proveedor no puede ser el mismo que el Emisor del comprobante de retención.', b'0');
INSERT INTO `err_error_code` VALUES ('2621', 2, 'Número de RUC del Proveedor no existe.', b'0');
INSERT INTO `err_error_code` VALUES ('2622', 2, 'La moneda del importe de pago debe ser la misma que la del documento relacionado.', b'0');
INSERT INTO `err_error_code` VALUES ('2623', 2, 'Los montos de pago, retenidos y montos pagados consignados para el documento relacionado no son correctos.', b'0');
INSERT INTO `err_error_code` VALUES ('2624', 2, 'El comprobante electrónico no ha sido emitido por el proveedor.', b'0');
INSERT INTO `err_error_code` VALUES ('2625', 2, 'La fecha de pago debe estar entre el primer día calendario del mes al cual corresponde la fecha de emisión del comprobante de retención o desde la fecha de emisión del comprobante relacionado.', b'0');
INSERT INTO `err_error_code` VALUES ('2626', 2, 'El Nro. de documento con el número de pago ya se encuentra en la Relación de Documentos Relacionados agregados.', b'0');
INSERT INTO `err_error_code` VALUES ('2627', 2, 'El Nro. de documento con el número de pago ya se encuentra registrado como pago realizado.', b'0');
INSERT INTO `err_error_code` VALUES ('2628', 2, 'Importe total retenido debe ser igual a la suma de los importes retenidos por cada documento relacionado.', b'0');
INSERT INTO `err_error_code` VALUES ('2629', 2, 'Importe total pagado debe ser igual a la suma de los importes pagados por cada documento relacionado.', b'0');
INSERT INTO `err_error_code` VALUES ('2630', 2, 'La serie o número del documento(01) modificado por la Nota de Crédito no cumple con el formato establecido para tipo código Nota Crédito 10.', b'0');
INSERT INTO `err_error_code` VALUES ('2631', 2, 'La serie o número del documento(12) modificado por la Nota de Crédito no cumple con el formato establecido para tipo código Nota Crédito 10.', b'0');
INSERT INTO `err_error_code` VALUES ('2632', 2, 'La serie o número del documento(56) modificado por la Nota de Crédito no cumple con el formato establecido para tipo código Nota Crédito 10.', b'0');
INSERT INTO `err_error_code` VALUES ('2633', 2, 'La serie o número del documento(03) modificado por la Nota de Crédito no cumple con el formato establecido para tipo código Nota Crédito 10.', b'0');
INSERT INTO `err_error_code` VALUES ('2634', 2, 'ReferenceID - El dato ingresado debe indicar serie correcta del documento al que se relaciona la Nota tipo 10.', b'0');
INSERT INTO `err_error_code` VALUES ('2635', 2, 'Debe existir DocumentTypeCode de Otros documentos relacionados con valor 99 para un tipo código Nota Crédito 10.', b'0');
INSERT INTO `err_error_code` VALUES ('2636', 2, 'No existe datos del ID de los documentos relacionados con valor 99 para un tipo código Nota Crédito 10.', b'0');
INSERT INTO `err_error_code` VALUES ('2637', 2, 'No existe datos del DocumentType de los documentos relacionados con valor 99 para un tipo código Nota Crédito 10.', b'0');
INSERT INTO `err_error_code` VALUES ('2640', 2, 'Operación gratuita, solo debe consignar un monto referencial', b'0');
INSERT INTO `err_error_code` VALUES ('2641', 2, 'Operación gratuita, debe consignar Total valor venta - operaciones gratuitas mayor a cero', b'0');
INSERT INTO `err_error_code` VALUES ('2642', 2, 'Operaciones de exportación, deben consignar Tipo Afectación igual a 40', b'0');
INSERT INTO `err_error_code` VALUES ('2643', 2, 'Factura de operación sujeta IVAP debe consignar Monto de impuestos por ítem', b'0');
INSERT INTO `err_error_code` VALUES ('2644', 2, 'Factura de operación sujeta IVAP solo debe tener ítems con código afectación IGV 17.', b'0');
INSERT INTO `err_error_code` VALUES ('2645', 2, 'Factura de operación sujeta a IVAP debe consignar ítems con código de tributo 1000', b'0');
INSERT INTO `err_error_code` VALUES ('2646', 2, 'Factura de operación sujeta a IVAP debe consignar ítems con nombre de tributo IVAP', b'0');
INSERT INTO `err_error_code` VALUES ('2647', 2, 'Código tributo UN/ECE debe ser VAT', b'0');
INSERT INTO `err_error_code` VALUES ('2648', 2, 'Factura de operación sujeta al IVAP, solo puede consignar información para operación gravadas', b'0');
INSERT INTO `err_error_code` VALUES ('2649', 2, 'Operación sujeta al IVAP, debe consignar monto en total operaciones gravadas', b'0');
INSERT INTO `err_error_code` VALUES ('2650', 2, 'Factura de operación sujeta al IVAP , no debe consignar valor para ISC o debe ser 0', b'0');
INSERT INTO `err_error_code` VALUES ('2651', 2, 'Factura de operación sujeta al IVAP , no debe consignar valor para IGV o debe ser 0', b'0');
INSERT INTO `err_error_code` VALUES ('2652', 2, 'Factura de operación sujeta al IVAP , debe registrar mensaje 2007', b'0');
INSERT INTO `err_error_code` VALUES ('2653', 2, 'Servicios prestados No domiciliados. Total IGV debe ser mayor a cero', b'0');
INSERT INTO `err_error_code` VALUES ('2654', 2, 'Servicios prestados No domiciliados. Código tributo a consignar debe ser 1000', b'0');
INSERT INTO `err_error_code` VALUES ('2655', 2, 'Servicios prestados No domiciliados. El código de afectación debe ser 40', b'0');
INSERT INTO `err_error_code` VALUES ('2656', 2, 'Servicios prestados No domiciliados. Código tributo UN/ECE debe ser VAT', b'0');
INSERT INTO `err_error_code` VALUES ('2657', 2, 'El Nro. de documento <serie>-<número> ya fue utilizado en la emisión de CPE.', b'0');
INSERT INTO `err_error_code` VALUES ('2658', 2, 'El Nro. de documento <serie>-<número> no se ha informado o no se encuentra en estado Revertido', b'0');
INSERT INTO `err_error_code` VALUES ('2659', 2, 'La fecha de cobro de cada documento relacionado deben ser del mismo Periodo (mm/aaaa), asimismo estas fechas podrán ser menores o iguales a la fecha de emisión del comprobante de percepción', b'0');
INSERT INTO `err_error_code` VALUES ('2660', 2, 'Los datos del CPE revertido no corresponden a los registrados en la SUNAT', b'0');
INSERT INTO `err_error_code` VALUES ('2661', 2, 'La fecha de cobro de cada documento relacionado deben ser del mismo Periodo (mm/aaaa), asimismo estas fechas podrán ser menores o iguales a la fecha de emisión del comprobante de retención', b'0');
INSERT INTO `err_error_code` VALUES ('2662', 2, 'El Nro. de documento <serie>-<número> ya fue utilizado en la emisión de CRE.', b'0');
INSERT INTO `err_error_code` VALUES ('2663', 2, 'El documento indicado no existe no puede ser modificado/eliminado', b'0');
INSERT INTO `err_error_code` VALUES ('2664', 2, 'El cálculo de la base imponible de percepción y el monto de la percepción no coincide con el monto total informado.', b'0');
INSERT INTO `err_error_code` VALUES ('2665', 2, 'El contribuyente no se encuentra autorizado a emitir Tickets', b'0');
INSERT INTO `err_error_code` VALUES ('2666', 2, 'Las percepciones son solo válidas para boletas de venta al contado.', b'0');
INSERT INTO `err_error_code` VALUES ('2667', 2, 'Importe total percibido debe ser igual a la suma de los importes percibidos por cada documento relacionado.', b'0');
INSERT INTO `err_error_code` VALUES ('2668', 2, 'Importe total cobrado debe ser igual a la suma de los importes cobrados por cada documento relacionado.', b'0');
INSERT INTO `err_error_code` VALUES ('2669', 2, 'El dato ingresado en TotalInvoiceAmount debe ser numérico mayor a cero', b'0');
INSERT INTO `err_error_code` VALUES ('2670', 2, 'La razón social no corresponde al ruc informado.', b'0');
INSERT INTO `err_error_code` VALUES ('2671', 2, 'La fecha de generación de la comunicación debe ser mayor o igual a la fecha de generación del documento revertido.', b'0');
INSERT INTO `err_error_code` VALUES ('2672', 2, 'La fecha de generación del documento revertido debe ser menor o igual a la fecha actual.', b'0');
INSERT INTO `err_error_code` VALUES ('2673', 2, 'El dato ingresado no cumple con el formato RR-fecha-correlativo.', b'0');
INSERT INTO `err_error_code` VALUES ('2674', 2, 'El dato ingresado  no cumple con el formato de DocumentSerialID, para DocumentTypeCode con valor 20.', b'0');
INSERT INTO `err_error_code` VALUES ('2675', 2, 'El dato ingresado  no cumple con el formato de DocumentSerialID, para DocumentTypeCode con valor 40.', b'0');
INSERT INTO `err_error_code` VALUES ('2676', 2, 'El XML no contiene el tag o no existe información del número de RUC del emisor', b'0');
INSERT INTO `err_error_code` VALUES ('2677', 2, 'El valor ingresado como número de RUC del emisor es incorrecto', b'0');
INSERT INTO `err_error_code` VALUES ('2678', 2, 'El XML no contiene el atributo o no existe información del tipo de documento del emisor', b'0');
INSERT INTO `err_error_code` VALUES ('2679', 2, 'El XML no contiene el tag o no existe información del número de documento de identidad del cliente', b'0');
INSERT INTO `err_error_code` VALUES ('2680', 2, 'El valor ingresado como documento de identidad del cliente es incorrecto', b'0');
INSERT INTO `err_error_code` VALUES ('2681', 2, 'El XML no contiene el atributo o no existe información del tipo de documento del cliente', b'0');
INSERT INTO `err_error_code` VALUES ('2682', 2, 'El valor ingresado como tipo de documento del cliente es incorrecto', b'0');
INSERT INTO `err_error_code` VALUES ('2683', 2, 'El XML no contiene el tag o no existe información del Importe total Percibido', b'0');
INSERT INTO `err_error_code` VALUES ('2684', 2, 'El XML no contiene el tag o no existe información de la moneda del Importe total Percibido', b'0');
INSERT INTO `err_error_code` VALUES ('2685', 2, 'El valor de la moneda del Importe total Percibido debe ser PEN', b'0');
INSERT INTO `err_error_code` VALUES ('2686', 2, 'El XML no contiene el tag o no existe información del Importe total Cobrado', b'0');
INSERT INTO `err_error_code` VALUES ('2687', 2, 'El dato ingresado en SUNATTotalCashed debe ser numérico mayor a cero', b'0');
INSERT INTO `err_error_code` VALUES ('2689', 2, 'El XML no contiene el tag o no existe información de la moneda del Importe total Cobrado', b'0');
INSERT INTO `err_error_code` VALUES ('2690', 2, 'El valor de la moneda del Importe total Cobrado debe ser PEN', b'0');
INSERT INTO `err_error_code` VALUES ('2691', 2, 'El XML no contiene el tag o no existe información del tipo de documento relacionado', b'0');
INSERT INTO `err_error_code` VALUES ('2692', 2, 'El tipo de documento relacionado no es válido', b'0');
INSERT INTO `err_error_code` VALUES ('2693', 2, 'El XML no contiene el tag o no existe información del número de documento relacionado', b'0');
INSERT INTO `err_error_code` VALUES ('2694', 2, 'El número de documento relacionado no está permitido o no es valido', b'0');
INSERT INTO `err_error_code` VALUES ('2695', 2, 'El XML no contiene el tag o no existe información del Importe total documento Relacionado', b'0');
INSERT INTO `err_error_code` VALUES ('2696', 2, 'El dato ingresado en el importe total documento relacionado debe ser numérico mayor a cero', b'0');
INSERT INTO `err_error_code` VALUES ('2697', 2, 'El XML no contiene el tag o no existe información del número de cobro', b'0');
INSERT INTO `err_error_code` VALUES ('2698', 2, 'El dato ingresado en el número de cobro no es válido', b'0');
INSERT INTO `err_error_code` VALUES ('2699', 2, 'El XML no contiene el tag o no existe información del Importe del cobro', b'0');
INSERT INTO `err_error_code` VALUES ('2700', 2, 'El dato ingresado en el Importe del cobro debe ser numérico mayor a cero', b'0');
INSERT INTO `err_error_code` VALUES ('2701', 2, 'El XML no contiene el tag o no existe información de la moneda del documento Relacionado', b'0');
INSERT INTO `err_error_code` VALUES ('2702', 2, 'El XML no contiene el tag o no existe información de la fecha de cobro del documento Relacionado', b'0');
INSERT INTO `err_error_code` VALUES ('2703', 2, 'La fecha de cobro del documento relacionado no es válido', b'0');
INSERT INTO `err_error_code` VALUES ('2704', 2, 'El XML no contiene el tag o no existe información del Importe percibido', b'0');
INSERT INTO `err_error_code` VALUES ('2705', 2, 'El dato ingresado en el Importe percibido debe ser numérico mayor a cero', b'0');
INSERT INTO `err_error_code` VALUES ('2706', 2, 'El XML no contiene el tag o no existe información de la moneda de importe percibido', b'0');
INSERT INTO `err_error_code` VALUES ('2707', 2, 'El valor de la moneda de importe percibido debe ser PEN', b'0');
INSERT INTO `err_error_code` VALUES ('2708', 2, 'El XML no contiene el tag o no existe información de la Fecha de Percepción', b'0');
INSERT INTO `err_error_code` VALUES ('2709', 2, 'La fecha de percepción no es válido', b'0');
INSERT INTO `err_error_code` VALUES ('2710', 2, 'El XML no contiene el tag o no existe información del Monto total a cobrar', b'0');
INSERT INTO `err_error_code` VALUES ('2711', 2, 'El dato ingresado en el Monto total a cobrar debe ser numérico mayor a cero', b'0');
INSERT INTO `err_error_code` VALUES ('2712', 2, 'El XML no contiene el tag o no existe información de la moneda del Monto total a cobrar', b'0');
INSERT INTO `err_error_code` VALUES ('2713', 2, 'El valor de la moneda del Monto total a cobrar debe ser PEN', b'0');
INSERT INTO `err_error_code` VALUES ('2714', 2, 'El valor de la moneda de referencia para el tipo de cambio no es válido', b'0');
INSERT INTO `err_error_code` VALUES ('2715', 2, 'El valor de la moneda objetivo para la Tasa de Cambio debe ser PEN', b'0');
INSERT INTO `err_error_code` VALUES ('2716', 2, 'El dato ingresado en el tipo de cambio debe ser numérico mayor a cero', b'0');
INSERT INTO `err_error_code` VALUES ('2717', 2, 'La fecha de cambio no es válido', b'0');
INSERT INTO `err_error_code` VALUES ('2718', 2, 'El valor de la moneda del documento Relacionado no es válido', b'0');
INSERT INTO `err_error_code` VALUES ('2719', 2, 'El XML no contiene el tag o no existe información de la moneda de referencia para el tipo de cambio', b'0');
INSERT INTO `err_error_code` VALUES ('2720', 2, 'El XML no contiene el tag o no existe información de la moneda objetivo para la Tasa de Cambio', b'0');
INSERT INTO `err_error_code` VALUES ('2721', 2, 'El XML no contiene el tag o no existe información del tipo de cambio', b'0');
INSERT INTO `err_error_code` VALUES ('2722', 2, 'El XML no contiene el tag o no existe información de la fecha de cambio', b'0');
INSERT INTO `err_error_code` VALUES ('2723', 2, 'El XML no contiene el tag o no existe información del número de documento de identidad del proveedor', b'0');
INSERT INTO `err_error_code` VALUES ('2724', 2, 'El valor ingresado como documento de identidad del proveedor es incorrecto', b'0');
INSERT INTO `err_error_code` VALUES ('2725', 2, 'El XML no contiene el tag o no existe información del Importe total Retenido', b'0');
INSERT INTO `err_error_code` VALUES ('2726', 2, 'El XML no contiene el tag o no existe información de la moneda del Importe total Retenido', b'0');
INSERT INTO `err_error_code` VALUES ('2727', 2, 'El XML no contiene el tag o no existe información de la moneda del Importe total Retenido', b'0');
INSERT INTO `err_error_code` VALUES ('2728', 2, 'El valor de la moneda del Importe total Retenido debe ser PEN', b'0');
INSERT INTO `err_error_code` VALUES ('2729', 2, 'El XML no contiene el tag o no existe información del Importe total Pagado', b'0');
INSERT INTO `err_error_code` VALUES ('2730', 2, 'El dato ingresado en SUNATTotalPaid debe ser numérico mayor a cero', b'0');
INSERT INTO `err_error_code` VALUES ('2731', 2, 'El XML no contiene el tag o no existe información de la moneda del Importe total Pagado', b'0');
INSERT INTO `err_error_code` VALUES ('2732', 2, 'El valor de la moneda del Importe total Pagado debe ser PEN', b'0');
INSERT INTO `err_error_code` VALUES ('2733', 2, 'El XML no contiene el tag o no existe información del número de pago', b'0');
INSERT INTO `err_error_code` VALUES ('2734', 2, 'El dato ingresado en el número de pago no es válido', b'0');
INSERT INTO `err_error_code` VALUES ('2735', 2, 'El XML no contiene el tag o no existe información del Importe del pago', b'0');
INSERT INTO `err_error_code` VALUES ('2736', 2, 'El dato ingresado en el Importe del pago debe ser numérico mayor a cero', b'0');
INSERT INTO `err_error_code` VALUES ('2737', 2, 'El XML no contiene el tag o no existe información de la fecha de pago del documento Relacionado', b'0');
INSERT INTO `err_error_code` VALUES ('2738', 2, 'La fecha de pago del documento relacionado no es válido', b'0');
INSERT INTO `err_error_code` VALUES ('2739', 2, 'El XML no contiene el tag o no existe información del Importe retenido', b'0');
INSERT INTO `err_error_code` VALUES ('2740', 2, 'El dato ingresado en el Importe retenido debe ser numérico mayor a cero', b'0');
INSERT INTO `err_error_code` VALUES ('2741', 2, 'El XML no contiene el tag o no existe información de la moneda de importe retenido', b'0');
INSERT INTO `err_error_code` VALUES ('2742', 2, 'El valor de la moneda de importe retenido debe ser PEN', b'0');
INSERT INTO `err_error_code` VALUES ('2743', 2, 'El XML no contiene el tag o no existe información de la Fecha de Retención', b'0');
INSERT INTO `err_error_code` VALUES ('2744', 2, 'La fecha de retención no es válido', b'0');
INSERT INTO `err_error_code` VALUES ('2745', 2, 'El XML no contiene el tag o no existe información del Importe total a pagar (neto)', b'0');
INSERT INTO `err_error_code` VALUES ('2746', 2, 'El dato ingresado en el Importe total a pagar (neto) debe ser numérico mayor a cero', b'0');
INSERT INTO `err_error_code` VALUES ('2747', 2, 'El XML no contiene el tag o no existe información de la Moneda del monto neto pagado', b'0');
INSERT INTO `err_error_code` VALUES ('2748', 2, 'El valor de la Moneda del monto neto pagado debe ser PEN', b'0');
INSERT INTO `err_error_code` VALUES ('2749', 2, 'La moneda de referencia para el tipo de cambio debe ser la misma que la del documento relacionado ', b'0');
INSERT INTO `err_error_code` VALUES ('2750', 2, 'El comprobante que se informa debe existir y estar en estado emitido', b'0');
INSERT INTO `err_error_code` VALUES ('2751', 2, 'El comprobante que se informa ya se encuentra en estado revertido', b'0');
INSERT INTO `err_error_code` VALUES ('2752', 2, 'El número de ítem no puede estar duplicado', b'0');
INSERT INTO `err_error_code` VALUES ('4000', 3, 'El documento ya fue presentado anteriormente.', b'0');
INSERT INTO `err_error_code` VALUES ('4001', 3, 'El numero de RUC del receptor no existe. ', b'0');
INSERT INTO `err_error_code` VALUES ('4002', 3, 'Para el TaxTypeCode, esta usando un valor que no existe en el catalogo. ', b'0');
INSERT INTO `err_error_code` VALUES ('4003', 3, 'El comprobante fue registrado previamente como rechazado.', b'0');
INSERT INTO `err_error_code` VALUES ('4004', 3, 'El DocumentTypeCode de las guias debe existir y tener 2 posiciones ', b'0');
INSERT INTO `err_error_code` VALUES ('4005', 3, 'El DocumentTypeCode de las guias debe ser 09 o 31 ', b'0');
INSERT INTO `err_error_code` VALUES ('4006', 3, 'El ID de las guias debe tener informacion de la SERIE-NUMERO de guia. ', b'0');
INSERT INTO `err_error_code` VALUES ('4007', 3, 'El XML no contiene el ID de las guias.', b'0');
INSERT INTO `err_error_code` VALUES ('4008', 3, 'El DocumentTypeCode de Otros documentos relacionados no cumple con el estandar. ', b'0');
INSERT INTO `err_error_code` VALUES ('4009', 3, 'El DocumentTypeCode de Otros documentos relacionados tiene valores incorrectos. ', b'0');
INSERT INTO `err_error_code` VALUES ('4010', 3, 'El ID de los documentos relacionados no cumplen con el estandar. ', b'0');
INSERT INTO `err_error_code` VALUES ('4011', 3, 'El XML no contiene el tag ID de documentos relacionados.', b'0');
INSERT INTO `err_error_code` VALUES ('4012', 3, 'El ubigeo indicado en el comprobante no es el mismo que esta registrado para el contribuyente. ', b'0');
INSERT INTO `err_error_code` VALUES ('4013', 3, 'El RUC del receptor no esta activo ', b'0');
INSERT INTO `err_error_code` VALUES ('4014', 3, 'El RUC del receptor no esta habido ', b'0');
INSERT INTO `err_error_code` VALUES ('4015', 3, 'Si el tipo de documento del receptor no es RUC, debe tener operaciones de exportacion ', b'0');
INSERT INTO `err_error_code` VALUES ('4016', 3, 'El total valor venta neta de oper. gravadas IGV debe ser mayor a 0.00 o debe existir oper. gravadas onerosas ', b'0');
INSERT INTO `err_error_code` VALUES ('4017', 3, 'El total valor venta neta de oper. inafectas IGV debe ser mayor a 0.00 o debe existir oper. inafectas onerosas o de export. ', b'0');
INSERT INTO `err_error_code` VALUES ('4018', 3, 'El total valor venta neta de oper. exoneradas IGV debe ser mayor a 0.00 o debe existir oper. exoneradas ', b'0');
INSERT INTO `err_error_code` VALUES ('4019', 3, 'El calculo del IGV no es correcto ', b'0');
INSERT INTO `err_error_code` VALUES ('4020', 3, 'El ISC no esta informado correctamente ', b'0');
INSERT INTO `err_error_code` VALUES ('4021', 3, 'Si se utiliza la leyenda con codigo 2000, el importe de percepcion debe ser mayor a 0.00 ', b'0');
INSERT INTO `err_error_code` VALUES ('4022', 3, 'Si se utiliza la leyenda con código 2001, el total de operaciones exoneradas debe ser mayor a 0.00 ', b'0');
INSERT INTO `err_error_code` VALUES ('4023', 3, 'Si se utiliza la leyenda con código 2002, el total de operaciones exoneradas debe ser mayor a 0.00 ', b'0');
INSERT INTO `err_error_code` VALUES ('4024', 3, 'Si se utiliza la leyenda con código 2003, el total de operaciones exoneradas debe ser mayor a 0.00 ', b'0');
INSERT INTO `err_error_code` VALUES ('4025', 3, 'Si usa la leyenda de Transferencia o Servivicio gratuito, todos los items deben ser no onerosos ', b'0');
INSERT INTO `err_error_code` VALUES ('4026', 3, 'No se puede indicar Guia de remision de remitente y Guia de remision de transportista en el mismo documento ', b'0');
INSERT INTO `err_error_code` VALUES ('4027', 3, 'El importe total no coincide con la sumatoria de los valores de venta mas los tributos mas los cargos ', b'0');
INSERT INTO `err_error_code` VALUES ('4028', 3, 'El monto total de la nota de credito debe ser menor o igual al monto de la factura ', b'0');
INSERT INTO `err_error_code` VALUES ('4029', 3, 'El ubigeo indicado en el comprobante no es el mismo que esta registrado para el contribuyente ', b'0');
INSERT INTO `err_error_code` VALUES ('4030', 3, 'El ubigeo indicado en el comprobante no es el mismo que esta registrado para el contribuyente ', b'0');
INSERT INTO `err_error_code` VALUES ('4031', 3, 'Debe indicar el nombre comercial ', b'0');
INSERT INTO `err_error_code` VALUES ('4032', 3, 'Si el código del motivo de emisión de la Nota de Credito es 03, debe existir la descripción del item ', b'0');
INSERT INTO `err_error_code` VALUES ('4033', 3, 'La fecha de generación de la numeración debe ser menor o igual a la fecha de generación de la comunicación ', b'0');
INSERT INTO `err_error_code` VALUES ('4034', 3, 'El comprobante fue registrado previamente como baja', b'0');
INSERT INTO `err_error_code` VALUES ('4035', 3, 'El comprobante fue registrado previamente como rechazado', b'0');
INSERT INTO `err_error_code` VALUES ('4036', 3, 'La fecha de emisión de los rangos debe ser menor o igual a la fecha de generación del resumen ', b'0');
INSERT INTO `err_error_code` VALUES ('4037', 3, 'El calculo del Total de IGV del Item no es correcto ', b'0');
INSERT INTO `err_error_code` VALUES ('4038', 3, 'El resumen contiene menos series por tipo de documento que el envío anterior para la misma fecha de emisión ', b'0');
INSERT INTO `err_error_code` VALUES ('4039', 3, 'No ha consignado información del ubigeo del domicilio fiscal ', b'0');
INSERT INTO `err_error_code` VALUES ('4040', 3, 'Si el importe de percepcion es mayor a 0.00, debe utilizar una leyenda con codigo 2000 ', b'0');
INSERT INTO `err_error_code` VALUES ('4041', 3, 'El codigo de pais debe ser PE ', b'0');
INSERT INTO `err_error_code` VALUES ('4042', 3, 'Para sac:SUNATTransaction/cbc:ID, se está usando un valor que no existe en el catálogo. Nro. 17', b'0');
INSERT INTO `err_error_code` VALUES ('4043', 3, 'Para el TransportModeCode, se está usando un valor que no existe en el catálogo Nro. 18', b'0');
INSERT INTO `err_error_code` VALUES ('4044', 3, 'PrepaidAmount: Monto total anticipado no coincide con la sumatoria de los montos por documento de anticipo', b'0');
INSERT INTO `err_error_code` VALUES ('4045', 3, 'No debe consignar los datos del transportista para la modalidad de transporte 02 – Transporte Privado', b'0');
INSERT INTO `err_error_code` VALUES ('4046', 3, 'No debe consignar información adicional en la dirección para los locales anexos', b'0');
INSERT INTO `err_error_code` VALUES ('4047', 3, 'sac:SUNATTransaction/cbc:ID debe ser igual a 06 cuando ingrese información para sustentar el traslado', b'0');
INSERT INTO `err_error_code` VALUES ('4048', 3, 'cac:AdditionalDocumentReference/cbc:DocumentTypeCode - Contiene un valor no valido para documentos relacionado', b'0');
INSERT INTO `err_error_code` VALUES ('4049', 3, 'El número de DNI del receptor no existe', b'0');
INSERT INTO `err_error_code` VALUES ('4050', 3, 'El número de RUC del proveedor no existe', b'0');
INSERT INTO `err_error_code` VALUES ('4051', 3, 'El RUC del proveedor no está activo', b'0');
INSERT INTO `err_error_code` VALUES ('4052', 3, 'El RUC del proveedor no está habido', b'0');
INSERT INTO `err_error_code` VALUES ('4053', 3, 'Proveedor no debe ser igual al remitente o destinatario', b'0');
INSERT INTO `err_error_code` VALUES ('4054', 3, 'La guía no debe contener datos del proveedor', b'0');
INSERT INTO `err_error_code` VALUES ('4055', 3, 'El XML no contiene información en el tag cbc:Information', b'0');
INSERT INTO `err_error_code` VALUES ('4056', 3, 'El XML no contiene el tag o no existe información en el tag SplitConsignmentIndicator', b'0');
INSERT INTO `err_error_code` VALUES ('4057', 3, 'GrossWeightMeasure – El dato ingresado no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4058', 3, 'cbc:TotalPackageQuantity - El dato ingresado no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4059', 3, 'Numero de bultos o pallets - información válida para importación', b'0');
INSERT INTO `err_error_code` VALUES ('4060', 3, 'La guía no debe contener datos del transportista', b'0');
INSERT INTO `err_error_code` VALUES ('4061', 3, 'El número de RUC del transportista no existe', b'0');
INSERT INTO `err_error_code` VALUES ('4062', 3, 'El RUC del transportista no está activo', b'0');
INSERT INTO `err_error_code` VALUES ('4063', 3, 'El RUC del transportista no está habido', b'0');
INSERT INTO `err_error_code` VALUES ('4064', 3, '/DespatchAdvice/cac:Shipment/cac:ShipmentStage/cac:TransportMeans/cbc:RegistrationNationalityID - El dato ingresado no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4065', 3, 'cac:TransportMeans/cbc:TransportMeansTypeCode - El valor ingresado como tipo de unidad de transporte es incorrecta', b'0');
INSERT INTO `err_error_code` VALUES ('4066', 3, 'El número de DNI del conductor no existe', b'0');
INSERT INTO `err_error_code` VALUES ('4067', 3, 'El XML no contiene el tag o no existe información del ubigeo del punto de llegada', b'0');
INSERT INTO `err_error_code` VALUES ('4068', 3, 'Dirección de punto de llegada - El dato ingresado no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4069', 3, 'CityName - El dato ingresado no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4070', 3, 'District - El dato ingresado no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4071', 3, 'Numero de Contenedor - El dato ingresado no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4072', 3, 'Numero de contenedor - información válida para importación', b'0');
INSERT INTO `err_error_code` VALUES ('4073', 3, 'TransEquipmentTypeCode - El valor ingresado como tipo de contenedor es incorrecta', b'0');
INSERT INTO `err_error_code` VALUES ('4074', 3, 'Numero Precinto - El dato ingresado no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4075', 3, 'El XML no contiene el tag o no existe información del ubigeo del punto de partida', b'0');
INSERT INTO `err_error_code` VALUES ('4076', 3, 'Dirección de punto de partida - El dato ingresado no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4077', 3, 'CityName - El dato ingresado no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4078', 3, 'District - El dato ingresado no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4079', 3, 'Código de Puerto o Aeropuerto - El dato ingresado no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4080', 3, 'Tipo de Puerto o Aeropuerto - El dato ingresado no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4081', 3, 'El XML No contiene El tag o No existe información del Numero de orden del ítem', b'0');
INSERT INTO `err_error_code` VALUES ('4082', 3, 'Número de Orden del Ítem - El orden del ítem no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4083', 3, 'Cantidad - El dato ingresado no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4084', 3, 'Descripción del Ítem - El dato ingresado no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4085', 3, 'Código del Ítem - El dato ingresado no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4086', 3, 'El emisor y el cliente son Agentes de percepción de combustible en la fecha de emisión.', b'0');
INSERT INTO `err_error_code` VALUES ('4087', 3, 'El Comprobante de Pago Electrónico no está Registrado en los Sistemas de la SUNAT.', b'0');
INSERT INTO `err_error_code` VALUES ('4088', 3, 'El Comprobante de Pago no está autorizado en los Sistemas de la SUNAT.', b'0');
INSERT INTO `err_error_code` VALUES ('4089', 3, 'La operación con este cliente está excluida del sistema de percepción. Es agente de retención.', b'0');
INSERT INTO `err_error_code` VALUES ('4090', 3, 'La operación con este cliente está excluida del sistema de percepción. Es entidad exceptuada de la percepción.', b'0');
INSERT INTO `err_error_code` VALUES ('4091', 3, 'La operación con este proveedor está excluida del sistema de retención. Es agente de percepción, agente de retención o buen contribuyente.', b'0');
INSERT INTO `err_error_code` VALUES ('4092', 3, 'El nombre comercial del emisor no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4093', 3, 'El ubigeo del emisor no cumple con el formato establecido o no es válido', b'0');
INSERT INTO `err_error_code` VALUES ('4094', 3, 'La dirección completa y detallada del domicilio fiscal del emisor no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4095', 3, 'La urbanización del domicilio fiscal del emisor no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4096', 3, 'La provincia del domicilio fiscal del emisor no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4097', 3, 'El departamento del domicilio fiscal del emisor no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4098', 3, 'El distrito del domicilio fiscal del emisor no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4099', 3, 'El nombre comercial del cliente no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4100', 3, 'El ubigeo del cliente no cumple con el formato establecido o no es válido', b'0');
INSERT INTO `err_error_code` VALUES ('4101', 3, 'La dirección completa y detallada del domicilio fiscal del cliente no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4102', 3, 'La urbanización del domicilio fiscal del cliente no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4103', 3, 'La provincia del domicilio fiscal del cliente no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4104', 3, 'El departamento del domicilio fiscal del cliente no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4105', 3, 'El distrito del domicilio fiscal del cliente no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4106', 3, 'El nombre comercial del proveedor no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4107', 3, 'El ubigeo del proveedor no cumple con el formato establecido o no es válido', b'0');
INSERT INTO `err_error_code` VALUES ('4108', 3, 'La dirección completa y detallada del domicilio fiscal del proveedor no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4109', 3, 'La urbanización del domicilio fiscal del proveedor no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4110', 3, 'La provincia del domicilio fiscal del proveedor no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4111', 3, 'El departamento del domicilio fiscal del proveedor no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('4112', 3, 'El distrito del domicilio fiscal del proveedor no cumple con el formato establecido', b'0');
INSERT INTO `err_error_code` VALUES ('98', NULL, 'En proceso', b'0');
INSERT INTO `err_error_code` VALUES ('99', NULL, 'Proceso con errores', b'0');

-- ----------------------------
-- Table structure for err_error_code_type
-- ----------------------------
DROP TABLE IF EXISTS `err_error_code_type`;
CREATE TABLE `err_error_code_type`  (
  `n_id_error_code_type` int(11) NOT NULL AUTO_INCREMENT,
  `c_name` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`n_id_error_code_type`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Records of err_error_code_type
-- ----------------------------
INSERT INTO `err_error_code_type` VALUES (1, 'Excepciones');
INSERT INTO `err_error_code_type` VALUES (2, 'Errores que generan rechazo');
INSERT INTO `err_error_code_type` VALUES (3, 'Observaciones');

-- ----------------------------
-- Table structure for failed_jobs
-- ----------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `connection` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `payload` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `failed_at` timestamp(0) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for log_log
-- ----------------------------
DROP TABLE IF EXISTS `log_log`;
CREATE TABLE `log_log`  (
  `n_id_log` int(11) NOT NULL AUTO_INCREMENT,
  `lgph_id` int(11) UNSIGNED NOT NULL DEFAULT 11,
  `c_id_log_level` varchar(8) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_message` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_context` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `n_id_invoice` int(11) DEFAULT NULL,
  `c_invoice_type_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_id_error_code` varchar(4) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `d_date_register` datetime(0) NOT NULL,
  `d_date_update` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`n_id_log`) USING BTREE,
  INDEX `fk_log_level_log`(`c_id_log_level`) USING BTREE,
  INDEX `lgph_id`(`lgph_id`) USING BTREE,
  INDEX `n_id_invoice`(`n_id_invoice`) USING BTREE,
  INDEX `c_invoice_type_code`(`c_invoice_type_code`) USING BTREE,
  INDEX `c_id_error_code`(`c_id_error_code`) USING BTREE,
  CONSTRAINT `fk_error_code_log` FOREIGN KEY (`c_id_error_code`) REFERENCES `err_error_code` (`c_id_error_code`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_invoice_log` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_log_level_log` FOREIGN KEY (`c_id_log_level`) REFERENCES `log_log_level` (`c_id_log_level`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_phase_log` FOREIGN KEY (`lgph_id`) REFERENCES `log_phase` (`lgph_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_type_code_log` FOREIGN KEY (`c_invoice_type_code`) REFERENCES `doc_invoice_type_code` (`c_invoice_type_code`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for log_log_level
-- ----------------------------
DROP TABLE IF EXISTS `log_log_level`;
CREATE TABLE `log_log_level`  (
  `c_id_log_level` varchar(8) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_name` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_description` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  PRIMARY KEY (`c_id_log_level`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Records of log_log_level
-- ----------------------------
INSERT INTO `log_log_level` VALUES ('critical', 'Crítico', NULL);
INSERT INTO `log_log_level` VALUES ('debug', 'Depurar', NULL);
INSERT INTO `log_log_level` VALUES ('error', 'Error', NULL);
INSERT INTO `log_log_level` VALUES ('info', 'Info', NULL);
INSERT INTO `log_log_level` VALUES ('notice', 'Aviso', NULL);
INSERT INTO `log_log_level` VALUES ('warning', 'Advertencia', NULL);

-- ----------------------------
-- Table structure for log_phase
-- ----------------------------
DROP TABLE IF EXISTS `log_phase`;
CREATE TABLE `log_phase`  (
  `lgph_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lgph_name` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `lgph_index` int(10) UNSIGNED NOT NULL,
  `lpgh_active` tinyint(4) NOT NULL,
  `lgph_fecreg` datetime(0) NOT NULL,
  `lgph_fecmod` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`lgph_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Records of log_phase
-- ----------------------------
INSERT INTO `log_phase` VALUES (1, 'Generación de array', 2, 1, '2016-06-26 19:27:49', NULL);
INSERT INTO `log_phase` VALUES (2, 'Validación de array', 3, 1, '2016-06-26 19:28:06', NULL);
INSERT INTO `log_phase` VALUES (3, 'Almacenamiento en BD', 4, 1, '2016-06-26 19:28:24', NULL);
INSERT INTO `log_phase` VALUES (4, 'Generación XML', 5, 1, '2016-06-26 19:28:39', NULL);
INSERT INTO `log_phase` VALUES (5, 'Envío a SUNAT', 7, 1, '2016-06-26 19:29:02', NULL);
INSERT INTO `log_phase` VALUES (6, 'Lectura CDR, grabar en BD', 8, 1, '2016-06-26 19:29:32', NULL);
INSERT INTO `log_phase` VALUES (7, 'Almacenamiento Input BD/File', 6, 1, '2016-06-29 18:42:14', NULL);
INSERT INTO `log_phase` VALUES (8, 'Generar PDF', 9, 1, '2016-06-29 20:42:46', NULL);
INSERT INTO `log_phase` VALUES (9, 'Envío e-mail', 10, 1, '2016-06-29 22:37:28', NULL);
INSERT INTO `log_phase` VALUES (10, 'Lectura Ticket SUNAT', 7, 1, '2016-06-29 23:34:58', NULL);
INSERT INTO `log_phase` VALUES (11, 'No contemplado', 0, 1, '2016-06-29 23:45:50', NULL);
INSERT INTO `log_phase` VALUES (12, 'Verificar documento', 1, 1, '2016-07-05 07:49:36', NULL);

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations`  (
  `migration` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Records of migrations
-- ----------------------------
INSERT INTO `migrations` VALUES ('2015_05_15_143610_create_failed_jobs_table', 1);

-- ----------------------------
-- Table structure for out_cdr
-- ----------------------------
DROP TABLE IF EXISTS `out_cdr`;
CREATE TABLE `out_cdr`  (
  `cdr_id` int(11) NOT NULL AUTO_INCREMENT,
  `cdr_correlative` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `cdr_quantity_cdr` int(11) NOT NULL DEFAULT 0,
  `cdr_response_success` int(11) NOT NULL DEFAULT 0,
  `cdr_response_observed` int(11) NOT NULL DEFAULT 0,
  `cdr_response_rejected` int(11) NOT NULL DEFAULT 0,
  `cdr_created_at` datetime(0) NOT NULL,
  `cdr_updated_at` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`cdr_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for out_cdr_det
-- ----------------------------
DROP TABLE IF EXISTS `out_cdr_det`;
CREATE TABLE `out_cdr_det`  (
  `cdrd_id` int(11) NOT NULL AUTO_INCREMENT,
  `cdr_id` int(11) NOT NULL,
  `n_id_invoice` int(11) NOT NULL,
  `c_customer_assigned_account_id` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_invoice_type_code` varchar(2) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `cdrd_serie` varchar(4) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `cdrd_correlative` varchar(8) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `n_id_cdr_status` int(11) NOT NULL DEFAULT 0,
  `cdrd_response_description` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `cdrd_date` date NOT NULL COMMENT 'Fecha del CDR (brindado por la SUNAT)',
  `n_id_account` int(11) NOT NULL DEFAULT 14,
  `cdrd_file_name` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`cdrd_id`) USING BTREE,
  INDEX `cdr_id`(`cdr_id`) USING BTREE,
  INDEX `n_id_invoice`(`n_id_invoice`) USING BTREE,
  INDEX `c_invoice_type_code`(`c_invoice_type_code`) USING BTREE,
  INDEX `n_id_cdr_status`(`n_id_cdr_status`) USING BTREE,
  INDEX `n_id_account`(`n_id_account`) USING BTREE,
  CONSTRAINT `fk_account_cdr_det` FOREIGN KEY (`n_id_account`) REFERENCES `acc_account` (`n_id_account`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cdr_cdrd` FOREIGN KEY (`cdr_id`) REFERENCES `out_cdr` (`cdr_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cdr_status_cdr_det` FOREIGN KEY (`n_id_cdr_status`) REFERENCES `doc_cdr_status` (`n_id_cdr_status`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_invoice_cdrd` FOREIGN KEY (`n_id_invoice`) REFERENCES `doc_invoice` (`n_id_invoice`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_invoice_type_code` FOREIGN KEY (`c_invoice_type_code`) REFERENCES `doc_invoice_type_code` (`c_invoice_type_code`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for sup_supplier
-- ----------------------------
DROP TABLE IF EXISTS `sup_supplier`;
CREATE TABLE `sup_supplier`  (
  `n_id_supplier` int(11) NOT NULL AUTO_INCREMENT,
  `c_customer_assigned_account_id` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Número de RUC',
  `c_additional_account_id` varchar(1) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Tipo de Documento - Catálogo No 06',
  `c_party_postal_address_id` varchar(6) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Código de Ubigeo - Catálogo No 13',
  `c_party_postal_address_street_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Dirección completa y detalada',
  `c_party_postal_address_city_subdivision_name` varchar(25) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Urbanización',
  `c_party_postal_address_city_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Provincia',
  `c_party_postal_address_country_subentity` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Departamento',
  `c_party_postal_address_district` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Distrito',
  `c_party_postal_address_country_identification_code` varchar(2) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Código de Paul - Catálogo No 04',
  `c_party_party_legal_entity_registration_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Apellidos y nombres, denominación o razón social',
  `c_party_name_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Nombre Comercial',
  `c_telephone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_email` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `c_detraction_account` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Cuenta de la detraccion (Cuenta corriente Banco de la Nacion), para operaciones que fuesen afectadas.',
  `c_sunat_bill_resolution` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Resolucion otorgada de la SUNAT para la emision de boletas y relacionados.',
  `c_sunat_invoice_resolution` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Resolucion otorgada de la SUNAT para la emision de facturas y relacionados.',
  `c_status_supplier` enum('visible','hidden','deleted') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `d_date_register_supplier` datetime(0) NOT NULL,
  `d_date_update_supplier` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`n_id_supplier`) USING BTREE,
  INDEX `fk_invoice_additional_account_id_supplier`(`c_additional_account_id`) USING BTREE,
  INDEX `c_party_postal_address_country_identification_code`(`c_party_postal_address_country_identification_code`) USING BTREE,
  CONSTRAINT `fk_country_supplier` FOREIGN KEY (`c_party_postal_address_country_identification_code`) REFERENCES `country` (`c_iso`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_invoice_additional_account_id_supplier` FOREIGN KEY (`c_additional_account_id`) REFERENCES `doc_invoice_additional_account_id` (`c_id_invoice_additional_account_id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Records of sup_supplier
-- ----------------------------
INSERT INTO `sup_supplier` VALUES (6, '20524719585', '6', '150131', 'CAL. AGUSTIN DE LA TORRE GONZALES NRO. 194', '', 'LIMA', 'LIMA', 'SAN ISIDRO', 'PE', 'AVANZA SOLUCIONES S.A.C.', NULL, '721-2783', 'facturacion.avanzasoluciones@gmail.com', '00098066799', '034-005-0006241/SUNAT', '034-005-0006241/SUNAT', 'visible', '2015-04-13 17:30:50', '2018-03-21 23:20:26');
INSERT INTO `sup_supplier` VALUES (7, '20320142599', '6', '021809', 'Av. Brasil A-30', 'Los Álamos', 'Santa', 'Ancash', 'Nuevo Chimbote', 'PE', 'CONSORCIO METAL MECANICO S.R.LTDA', 'Comet S.R.L.', '721-2783', 'facturacion.avanzasoluciones@gmail.com', '00098066799', '034-005-0006241/SUNAT', '034-005-0006241/SUNAT', 'visible', '2015-04-13 17:30:50', '2017-12-27 11:09:50');

-- ----------------------------
-- Table structure for sup_supplier_configuration
-- ----------------------------
DROP TABLE IF EXISTS `sup_supplier_configuration`;
CREATE TABLE `sup_supplier_configuration`  (
  `n_id_supplier` int(11) NOT NULL,
  `c_bill_sent_sunat` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'no',
  `c_email_sent_customer` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'yes' COMMENT 'Correo electronico que se envia al usuario final, despues de un proceso de facturacion electronica.',
  `c_email_sent_supplier` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'no',
  `c_public_path_cdr` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_public_path_cdr_processed` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_public_path_document` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_public_path_input` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `c_public_path_pdf` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`n_id_supplier`) USING BTREE,
  CONSTRAINT `fk_supplier_supplier_configuration` FOREIGN KEY (`n_id_supplier`) REFERENCES `sup_supplier` (`n_id_supplier`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Records of sup_supplier_configuration
-- ----------------------------
INSERT INTO `sup_supplier_configuration` VALUES (6, 'yes', 'yes', 'yes', 'cdn/cdr', 'cdn/cdr-processed', 'cdn/document', 'cdn/input', 'cdn/pdf');
INSERT INTO `sup_supplier_configuration` VALUES (7, 'yes', 'yes', 'yes', 'cdn/cdr', 'cdn/cdr-processed', 'cdn/document', 'cdn/input', 'cdn/pdf');

SET FOREIGN_KEY_CHECKS = 1;
