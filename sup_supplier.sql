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

 Date: 17/02/2018 15:43:03
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

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
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Records of sup_supplier
-- ----------------------------
INSERT INTO `sup_supplier` VALUES (6, '20524719585', '6', '150131', 'CAL. AGUSTIN DE LA TORRE GONZALES NRO. 194', '', 'LIMA', 'LIMA', 'SAN ISIDRO', 'PE', 'AVANZA SOLUCIONES S.A.C.', 'AVSO S.A.C.', '721-2783', 'facturacion.avanzasoluciones@gmail.com', '00098066799', '034-005-0006241/SUNAT', '034-005-0006241/SUNAT', 'visible', '2015-04-13 17:30:50', '2017-12-16 21:01:13');

SET FOREIGN_KEY_CHECKS = 1;
