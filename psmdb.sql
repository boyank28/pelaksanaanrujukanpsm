/*
 Navicat Premium Data Transfer

 Source Server         : local
 Source Server Type    : MariaDB
 Source Server Version : 100420 (10.4.20-MariaDB)
 Source Host           : localhost:3306
 Source Schema         : psmdb

 Target Server Type    : MariaDB
 Target Server Version : 100420 (10.4.20-MariaDB)
 File Encoding         : 65001

 Date: 09/07/2026 14:14:50
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for antri_rujukan_psm
-- ----------------------------
DROP TABLE IF EXISTS `antri_rujukan_psm`;
CREATE TABLE `antri_rujukan_psm`  (
  `tanggal` date NOT NULL,
  `no_rawat` varchar(17) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`no_rawat`, `tanggal`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of antri_rujukan_psm
-- ----------------------------

-- ----------------------------
-- Table structure for bukti_pelaksanaan_rujukan_psm
-- ----------------------------
DROP TABLE IF EXISTS `bukti_pelaksanaan_rujukan_psm`;
CREATE TABLE `bukti_pelaksanaan_rujukan_psm`  (
  `no_rawat` varchar(17) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tanggal` date NOT NULL,
  `bukti` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`no_rawat`, `tanggal`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of bukti_pelaksanaan_rujukan_psm
-- ----------------------------
INSERT INTO `bukti_pelaksanaan_rujukan_psm` VALUES ('2026/07/05/000027', '2026-07-08', 'pages/upload/2026070500002720260708183316.jpeg');
INSERT INTO `bukti_pelaksanaan_rujukan_psm` VALUES ('2026/07/05/000044', '2026-07-08', 'pages/upload/2026070500004420260708182952.jpeg');
INSERT INTO `bukti_pelaksanaan_rujukan_psm` VALUES ('2026/07/06/000237', '2026-07-08', 'pages/upload/2026070600023720260708181423.jpeg');
INSERT INTO `bukti_pelaksanaan_rujukan_psm` VALUES ('2026/07/06/000237', '2026-07-09', 'pages/upload/2026070600023720260709083512.jpeg');
INSERT INTO `bukti_pelaksanaan_rujukan_psm` VALUES ('2026/07/07/000217', '2026-07-08', 'pages/upload/2026070700021720260708184449.jpeg');
INSERT INTO `bukti_pelaksanaan_rujukan_psm` VALUES ('2026/07/08/000086', '2026-07-08', 'pages/upload/2026070800008620260708204016.jpeg');
INSERT INTO `bukti_pelaksanaan_rujukan_psm` VALUES ('2026/07/08/000089', '2026-07-08', 'pages/upload/2026070800008920260708203816.jpeg');
INSERT INTO `bukti_pelaksanaan_rujukan_psm` VALUES ('2026/07/08/000102', '2026-07-08', 'pages/upload/2026070800010220260708185625.jpeg');
INSERT INTO `bukti_pelaksanaan_rujukan_psm` VALUES ('2026/07/08/000105', '2026-07-08', 'pages/upload/2026070800010520260708190424.jpeg');

-- ----------------------------
-- Table structure for master_psm
-- ----------------------------
DROP TABLE IF EXISTS `master_psm`;
CREATE TABLE `master_psm`  (
  `id_psm` int(11) NOT NULL AUTO_INCREMENT,
  `nama_psm` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `no_telp` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `alamat` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `status` enum('Aktif','Nonaktif') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'Aktif',
  `foto` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id_psm`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of master_psm
-- ----------------------------
INSERT INTO `master_psm` VALUES (3, 'Abah1', '005', 'dsfd', 'Aktif', '');
INSERT INTO `master_psm` VALUES (4, 'MBG', '051118', 'hbas', 'Aktif', '');

-- ----------------------------
-- Table structure for pelaksanaan_rujukan_psm
-- ----------------------------
DROP TABLE IF EXISTS `pelaksanaan_rujukan_psm`;
CREATE TABLE `pelaksanaan_rujukan_psm`  (
  `no_rawat` varchar(17) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tanggal` datetime NOT NULL,
  `id_psm` int(11) NOT NULL,
  `keterangan_diberikan_pada` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `diberikan_pada` enum('Pasien','Keluarga Pasien') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `materi_rujukan` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`no_rawat`, `tanggal`) USING BTREE,
  INDEX `tanggal`(`tanggal`) USING BTREE,
  INDEX `no_rawat`(`no_rawat`) USING BTREE,
  INDEX `id_psm`(`id_psm`) USING BTREE,
  CONSTRAINT `pelaksanaan_rujukan_psm_ibfk_2` FOREIGN KEY (`id_psm`) REFERENCES `master_psm` (`id_psm`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pelaksanaan_rujukan_psm
-- ----------------------------
INSERT INTO `pelaksanaan_rujukan_psm` VALUES ('2026/07/06/000237', '2026-07-09 08:35:12', 3, 'admin', 'Pasien', '-');

-- ----------------------------
-- Table structure for setting_aplikasi
-- ----------------------------
DROP TABLE IF EXISTS `setting_aplikasi`;
CREATE TABLE `setting_aplikasi`  (
  `id` int(11) NOT NULL,
  `nama_instansi` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `logo` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of setting_aplikasi
-- ----------------------------
INSERT INTO `setting_aplikasi` VALUES (1, 'RSKH', 'logo.png');

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `password` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `role` enum('Admin','Petugas') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'Petugas',
  PRIMARY KEY (`id_user`) USING BTREE,
  UNIQUE INDEX `username`(`username`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'admin', '$2y$10$47rhhH1MWk8x2b6PEWMqseCwW8cukngjwN7DdfNzQK1WxfSR9vS66', 'Admin');
INSERT INTO `users` VALUES (2, 'cri', '50ee4335a5d9f930e185ae47538fecf7', 'Petugas');

SET FOREIGN_KEY_CHECKS = 1;
