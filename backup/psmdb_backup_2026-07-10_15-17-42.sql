

CREATE TABLE `amprahan_approval` (
  `bulan` int(11) NOT NULL,
  `tahun` int(11) NOT NULL,
  `tipe_approval` varchar(50) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `nama_lengkap` varchar(150) DEFAULT NULL,
  `waktu_approval` datetime DEFAULT NULL,
  PRIMARY KEY (`bulan`,`tahun`,`tipe_approval`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO amprahan_approval VALUES("7","2026","dicek","wulan","Wulan Ria Fatmawati","2026-07-10 19:32:02");
INSERT INTO amprahan_approval VALUES("7","2026","mengetahui","1107560","RhofiahAmd","2026-07-10 18:28:34");
INSERT INTO amprahan_approval VALUES("7","2026","menyetujui","dr Monda","dr Evameinonda MMRSMQMFISQua","2026-07-10 19:05:11");



CREATE TABLE `amprahan_keterangan` (
  `bulan` int(11) NOT NULL,
  `tahun` int(11) NOT NULL,
  `id_psm` int(11) NOT NULL,
  `keterangan` text DEFAULT NULL,
  PRIMARY KEY (`bulan`,`tahun`,`id_psm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




CREATE TABLE `antri_rujukan_psm` (
  `tanggal` date NOT NULL,
  `no_rawat` varchar(17) NOT NULL,
  PRIMARY KEY (`no_rawat`,`tanggal`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;




CREATE TABLE `bukti_pelaksanaan_rujukan_psm` (
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` date NOT NULL,
  `bukti` varchar(200) NOT NULL,
  PRIMARY KEY (`no_rawat`,`tanggal`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO bukti_pelaksanaan_rujukan_psm VALUES("2026/07/05/000027","2026-07-08","pages/upload/2026070500002720260708183316.jpeg");
INSERT INTO bukti_pelaksanaan_rujukan_psm VALUES("2026/07/05/000044","2026-07-08","pages/upload/2026070500004420260708182952.jpeg");
INSERT INTO bukti_pelaksanaan_rujukan_psm VALUES("2026/07/06/000237","2026-07-08","pages/upload/2026070600023720260708181423.jpeg");
INSERT INTO bukti_pelaksanaan_rujukan_psm VALUES("2026/07/06/000237","2026-07-09","pages/upload/2026070600023720260709092812.jpeg");
INSERT INTO bukti_pelaksanaan_rujukan_psm VALUES("2026/07/07/000217","2026-07-08","pages/upload/2026070700021720260708184449.jpeg");
INSERT INTO bukti_pelaksanaan_rujukan_psm VALUES("2026/07/08/000086","2026-07-08","pages/upload/2026070800008620260708204016.jpeg");
INSERT INTO bukti_pelaksanaan_rujukan_psm VALUES("2026/07/08/000089","2026-07-08","pages/upload/2026070800008920260708203816.jpeg");
INSERT INTO bukti_pelaksanaan_rujukan_psm VALUES("2026/07/08/000102","2026-07-08","pages/upload/2026070800010220260708185625.jpeg");
INSERT INTO bukti_pelaksanaan_rujukan_psm VALUES("2026/07/08/000105","2026-07-08","pages/upload/2026070800010520260708190424.jpeg");
INSERT INTO bukti_pelaksanaan_rujukan_psm VALUES("2026/07/09/000100","2026-07-09","pages/upload/2026070900010020260709092906.jpeg");
INSERT INTO bukti_pelaksanaan_rujukan_psm VALUES("2026/07/09/000156","2026-07-10","pages/upload/2026070900015620260710090339.jpeg");
INSERT INTO bukti_pelaksanaan_rujukan_psm VALUES("2026/07/09/000266","2026-07-10","pages/upload/2026070900026620260710085330.jpeg");
INSERT INTO bukti_pelaksanaan_rujukan_psm VALUES("2026/07/09/000269","2026-07-10","pages/upload/2026070900026920260710093112.jpeg");
INSERT INTO bukti_pelaksanaan_rujukan_psm VALUES("2026/07/10/000175","2026-07-10","pages/upload/2026071000017520260710093333.jpeg");



CREATE TABLE `log_aktivitas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `waktu` datetime DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `aktivitas` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4;

INSERT INTO log_aktivitas VALUES("1","2026-07-10 17:56:15","admin","","Logout dari sistem");
INSERT INTO log_aktivitas VALUES("2","2026-07-10 17:56:20","admin","","Login ke dalam sistem");
INSERT INTO log_aktivitas VALUES("3","2026-07-10 18:04:01","admin","","Melakukan validasi (mengetahui) laporan Amprahan bulan 7 tahun 2026");
INSERT INTO log_aktivitas VALUES("4","2026-07-10 18:04:07","admin","","Melakukan validasi (dicek) laporan Amprahan bulan 7 tahun 2026");
INSERT INTO log_aktivitas VALUES("5","2026-07-10 18:04:11","admin","","Melakukan validasi (menyetujui) laporan Amprahan bulan 7 tahun 2026");
INSERT INTO log_aktivitas VALUES("6","2026-07-10 18:04:20","admin","","Membatalkan validasi (menyetujui) laporan Amprahan bulan 7 tahun 2026");
INSERT INTO log_aktivitas VALUES("7","2026-07-10 18:04:21","admin","","Membatalkan validasi (dicek) laporan Amprahan bulan 7 tahun 2026");
INSERT INTO log_aktivitas VALUES("8","2026-07-10 18:04:22","admin","","Membatalkan validasi (mengetahui) laporan Amprahan bulan 7 tahun 2026");
INSERT INTO log_aktivitas VALUES("9","2026-07-10 18:05:00","admin","","Melakukan validasi (mengetahui) laporan Amprahan bulan 7 tahun 2026");
INSERT INTO log_aktivitas VALUES("10","2026-07-10 18:17:38","admin","","Melakukan validasi (dicek) laporan Amprahan bulan 7 tahun 2026");
INSERT INTO log_aktivitas VALUES("11","2026-07-10 18:17:40","admin","","Melakukan validasi (menyetujui) laporan Amprahan bulan 7 tahun 2026");
INSERT INTO log_aktivitas VALUES("12","2026-07-10 18:17:48","admin","","Membatalkan validasi (menyetujui) laporan Amprahan bulan 7 tahun 2026");
INSERT INTO log_aktivitas VALUES("13","2026-07-10 18:17:50","admin","","Membatalkan validasi (dicek) laporan Amprahan bulan 7 tahun 2026");
INSERT INTO log_aktivitas VALUES("14","2026-07-10 18:17:51","admin","","Membatalkan validasi (mengetahui) laporan Amprahan bulan 7 tahun 2026");
INSERT INTO log_aktivitas VALUES("15","2026-07-10 18:20:21","admin","","Melakukan validasi (mengetahui) laporan Amprahan bulan 7 tahun 2026");
INSERT INTO log_aktivitas VALUES("16","2026-07-10 18:20:23","admin","","Melakukan validasi (dicek) laporan Amprahan bulan 7 tahun 2026");
INSERT INTO log_aktivitas VALUES("17","2026-07-10 18:20:24","admin","","Melakukan validasi (menyetujui) laporan Amprahan bulan 7 tahun 2026");
INSERT INTO log_aktivitas VALUES("18","2026-07-10 18:20:27","admin","","Membatalkan validasi (menyetujui) laporan Amprahan bulan 7 tahun 2026");
INSERT INTO log_aktivitas VALUES("19","2026-07-10 18:20:28","admin","","Membatalkan validasi (dicek) laporan Amprahan bulan 7 tahun 2026");
INSERT INTO log_aktivitas VALUES("20","2026-07-10 18:20:29","admin","","Membatalkan validasi (mengetahui) laporan Amprahan bulan 7 tahun 2026");
INSERT INTO log_aktivitas VALUES("21","2026-07-10 18:21:39","admin","","Logout dari sistem");
INSERT INTO log_aktivitas VALUES("22","2026-07-10 18:21:51","1107560","","Login ke dalam sistem");
INSERT INTO log_aktivitas VALUES("23","2026-07-10 18:22:05","1107560","","Melakukan validasi (mengetahui) laporan Amprahan bulan 7 tahun 2026");
INSERT INTO log_aktivitas VALUES("24","2026-07-10 18:25:17","1107560","","Logout dari sistem");
INSERT INTO log_aktivitas VALUES("25","2026-07-10 18:25:22","admin","","Login ke dalam sistem");
INSERT INTO log_aktivitas VALUES("26","2026-07-10 18:28:11","admin","127.0.0.1","Logout dari sistem");
INSERT INTO log_aktivitas VALUES("27","2026-07-10 18:28:16","1107560","127.0.0.1","Login ke dalam sistem");
INSERT INTO log_aktivitas VALUES("28","2026-07-10 18:28:33","1107560","127.0.0.1","Membatalkan validasi (mengetahui) laporan Amprahan bulan 7 tahun 2026");
INSERT INTO log_aktivitas VALUES("29","2026-07-10 18:28:34","1107560","127.0.0.1","Melakukan validasi (mengetahui) laporan Amprahan bulan 7 tahun 2026");
INSERT INTO log_aktivitas VALUES("30","2026-07-10 18:36:25","1107560","127.0.0.1","Logout dari sistem");
INSERT INTO log_aktivitas VALUES("31","2026-07-10 18:36:31","admin","127.0.0.1","Login ke dalam sistem");
INSERT INTO log_aktivitas VALUES("32","2026-07-10 18:59:36","admin","127.0.0.1","Logout dari sistem");
INSERT INTO log_aktivitas VALUES("33","2026-07-10 19:00:10","admin","127.0.0.1","Login ke dalam sistem");
INSERT INTO log_aktivitas VALUES("34","2026-07-10 19:01:16","admin","127.0.0.1","Logout dari sistem");
INSERT INTO log_aktivitas VALUES("35","2026-07-10 19:01:47","admin","127.0.0.1","Login ke dalam sistem");
INSERT INTO log_aktivitas VALUES("36","2026-07-10 19:04:31","admin","127.0.0.1","Logout dari sistem");
INSERT INTO log_aktivitas VALUES("37","2026-07-10 19:04:41","dr Monda","127.0.0.1","Login ke dalam sistem");
INSERT INTO log_aktivitas VALUES("38","2026-07-10 19:05:11","dr Monda","127.0.0.1","Melakukan validasi (menyetujui) laporan Amprahan bulan 7 tahun 2026");
INSERT INTO log_aktivitas VALUES("39","2026-07-10 19:06:49","dr Monda","127.0.0.1","Logout dari sistem");
INSERT INTO log_aktivitas VALUES("40","2026-07-10 19:07:29","dr Monda","127.0.0.1","Login ke dalam sistem");
INSERT INTO log_aktivitas VALUES("41","2026-07-10 19:07:49","dr Monda","127.0.0.1","Logout dari sistem");
INSERT INTO log_aktivitas VALUES("42","2026-07-10 19:07:58","admin","127.0.0.1","Login ke dalam sistem");
INSERT INTO log_aktivitas VALUES("43","2026-07-10 19:10:36","admin","127.0.0.1","Logout dari sistem");
INSERT INTO log_aktivitas VALUES("44","2026-07-10 19:10:43","dr Monda","127.0.0.1","Login ke dalam sistem");
INSERT INTO log_aktivitas VALUES("45","2026-07-10 19:10:59","admin","127.0.0.1","Login ke dalam sistem");
INSERT INTO log_aktivitas VALUES("46","2026-07-10 19:11:19","admin","127.0.0.1","Logout dari sistem");
INSERT INTO log_aktivitas VALUES("47","2026-07-10 19:11:25","dr Monda","127.0.0.1","Login ke dalam sistem");
INSERT INTO log_aktivitas VALUES("48","2026-07-10 19:11:51","dr Monda","127.0.0.1","Logout dari sistem");
INSERT INTO log_aktivitas VALUES("49","2026-07-10 19:11:54","admin","127.0.0.1","Login ke dalam sistem");
INSERT INTO log_aktivitas VALUES("50","2026-07-10 19:12:25","admin","127.0.0.1","Logout dari sistem");
INSERT INTO log_aktivitas VALUES("51","2026-07-10 19:12:30","dr Monda","127.0.0.1","Login ke dalam sistem");
INSERT INTO log_aktivitas VALUES("52","2026-07-10 19:13:22","dr Monda","127.0.0.1","Logout dari sistem");
INSERT INTO log_aktivitas VALUES("53","2026-07-10 19:13:29","admin","127.0.0.1","Login ke dalam sistem");
INSERT INTO log_aktivitas VALUES("54","2026-07-10 19:14:55","admin","127.0.0.1","Logout dari sistem");
INSERT INTO log_aktivitas VALUES("55","2026-07-10 19:15:01","fitri","127.0.0.1","Login ke dalam sistem");
INSERT INTO log_aktivitas VALUES("56","2026-07-10 19:15:52","fitri","127.0.0.1","Logout dari sistem");
INSERT INTO log_aktivitas VALUES("57","2026-07-10 19:15:56","admin","127.0.0.1","Login ke dalam sistem");
INSERT INTO log_aktivitas VALUES("58","2026-07-10 19:30:29","admin","127.0.0.1","Logout dari sistem");
INSERT INTO log_aktivitas VALUES("59","2026-07-10 19:30:38","fitri","127.0.0.1","Login ke dalam sistem");
INSERT INTO log_aktivitas VALUES("60","2026-07-10 19:31:46","fitri","127.0.0.1","Logout dari sistem");
INSERT INTO log_aktivitas VALUES("61","2026-07-10 19:31:53","wulan","127.0.0.1","Login ke dalam sistem");
INSERT INTO log_aktivitas VALUES("62","2026-07-10 19:32:02","wulan","127.0.0.1","Melakukan validasi (dicek) laporan Amprahan bulan 7 tahun 2026");
INSERT INTO log_aktivitas VALUES("63","2026-07-10 19:55:33","admin","127.0.0.1","Login ke dalam sistem");
INSERT INTO log_aktivitas VALUES("64","2026-07-10 20:11:11","admin","127.0.0.1","Logout dari sistem");
INSERT INTO log_aktivitas VALUES("65","2026-07-10 20:11:18","admin","127.0.0.1","Login ke dalam sistem");



CREATE TABLE `master_psm` (
  `id_psm` int(11) NOT NULL AUTO_INCREMENT,
  `nama_psm` varchar(100) NOT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `status` enum('Aktif','Nonaktif') DEFAULT 'Aktif',
  `foto` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_psm`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=latin1;

INSERT INTO master_psm VALUES("5","Pendi","085311553872","Dawuan Timur","Aktif","");
INSERT INTO master_psm VALUES("6","Jhony Saputra","085719197028","Dawuan Tengah","Aktif","");
INSERT INTO master_psm VALUES("7","Zaenal Abidin","085692960065","Karangsinom","Aktif","");
INSERT INTO master_psm VALUES("8","Heni","089699784731","Kalihurip","Aktif","");
INSERT INTO master_psm VALUES("9","ACHMAD TATA","085810641184","Purwasari","Aktif","");
INSERT INTO master_psm VALUES("10","AEP SAEPUDIN","083815679560","TAMELANG","Aktif","");
INSERT INTO master_psm VALUES("11","CECEP IRAWAN","087779162915","CIBODAS PURWAKARTA","Aktif","");
INSERT INTO master_psm VALUES("12","DIDIN KOMARUDIN"," 085711238519","PUCUNG KOTA BARU","Aktif","");
INSERT INTO master_psm VALUES("13","FAJAR LAZUARDI","081513414062","BANYUSARI","Aktif","");
INSERT INTO master_psm VALUES("14","HERMAN","082113922356","CIKAMPEK","Aktif","");
INSERT INTO master_psm VALUES("15","IWAN SETIAWAN","089516489072","JOMIN","Aktif","");
INSERT INTO master_psm VALUES("16","KHUSNUL A","085954736207","PANGULAH UTARA","Aktif","");
INSERT INTO master_psm VALUES("17","LUKMAN","089604008463","KOTA BARU","Aktif","");
INSERT INTO master_psm VALUES("18","NENDAH","082116177079","KOTA BARU","Aktif","");
INSERT INTO master_psm VALUES("19","NURHASANAH","085777802035","TIRTAMULYA","Aktif","");
INSERT INTO master_psm VALUES("20","ODING RF","0895348542288","DAWUAN BARAT","Aktif","");
INSERT INTO master_psm VALUES("21","SAIDI","081389586049","KAMOJING CIKAMPEK","Aktif","");
INSERT INTO master_psm VALUES("22","SUNARYA","0895365189347","PURWASARI","Aktif","");
INSERT INTO master_psm VALUES("23","SYAEFUL ","085695245160","TIRTAMULYA","Aktif","");
INSERT INTO master_psm VALUES("24","YATNA","0895621540055","DAWUAN BARAT","Aktif","");
INSERT INTO master_psm VALUES("25","YEPI M YUSUP","0895336822478","CIKAMPEK SELATAN","Aktif","");
INSERT INTO master_psm VALUES("26","WAWAN HERMAWAN","085781557624","PANCAWATI","Aktif","");
INSERT INTO master_psm VALUES("27","ENDAH OKTAENI","081220129825","CIKAMPEK TIMUR","Aktif","");
INSERT INTO master_psm VALUES("28","EVI NOVIANTI","0881010136119","DAWUAN BARAT","Aktif","");
INSERT INTO master_psm VALUES("29","GALIH RAKASIWI","089609102477","KOTA BARU","Aktif","");
INSERT INTO master_psm VALUES("30","USYIN UMBARA","085288286320","KOTA BARU","Aktif","");
INSERT INTO master_psm VALUES("31","HERIYANTO  GONO","081213080091","CIKAMPEK","Aktif","");
INSERT INTO master_psm VALUES("32","ASMITA","085772337545","KOTA BARU","Aktif","");
INSERT INTO master_psm VALUES("33","KARTINI","082128938138","CIASEM","Aktif","");
INSERT INTO master_psm VALUES("34","OTIFAH FAUZIAH","08567801890","CIASEM","Aktif","");
INSERT INTO master_psm VALUES("35","IRMA AGUSTINA","082122879518","CIKAMPEK BARAT","Aktif","");
INSERT INTO master_psm VALUES("36","YUYUN JAMILAH","089687525677","SUKATI JOMIN KOTA BARU","Aktif","");



CREATE TABLE `pelaksanaan_rujukan_psm` (
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` datetime NOT NULL,
  `id_psm` int(11) NOT NULL,
  `keterangan_diberikan_pada` varchar(100) NOT NULL,
  `diberikan_pada` enum('Pasien','Keluarga Pasien') NOT NULL,
  `materi_rujukan` text NOT NULL,
  PRIMARY KEY (`no_rawat`,`tanggal`),
  KEY `tanggal` (`tanggal`),
  KEY `no_rawat` (`no_rawat`),
  KEY `id_psm` (`id_psm`),
  CONSTRAINT `pelaksanaan_rujukan_psm_ibfk_2` FOREIGN KEY (`id_psm`) REFERENCES `master_psm` (`id_psm`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;




CREATE TABLE `setting_aplikasi` (
  `id` int(11) NOT NULL,
  `nama_instansi` varchar(100) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO setting_aplikasi VALUES("1","RSKH","logo.png");



CREATE TABLE `setting_fee_psm` (
  `id` int(11) NOT NULL DEFAULT 1,
  `fee_ranap_op` double DEFAULT NULL,
  `fee_ranap` double DEFAULT NULL,
  `fee_ralan` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO setting_fee_psm VALUES("1","75000","50000","125000");



CREATE TABLE `setting_ttd_amprahan` (
  `id` int(11) NOT NULL DEFAULT 1,
  `mengetahui_nama` varchar(100) DEFAULT NULL,
  `mengetahui_jabatan` varchar(100) DEFAULT NULL,
  `dicek_nama` varchar(100) DEFAULT NULL,
  `dicek_jabatan` varchar(100) DEFAULT NULL,
  `menyetujui_nama` varchar(100) DEFAULT NULL,
  `menyetujui_jabatan` varchar(100) DEFAULT NULL,
  `dibuatkan_nama` varchar(100) DEFAULT NULL,
  `dibuatkan_jabatan` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO setting_ttd_amprahan VALUES("1","RhofiahAmd","Pjs Kabag Marketing","Wulan Ria Fatmawati","Ka.Unit Akutansi & Keuangan","dr Evameinonda MMRSMQMFISQua","Direktur","Fitri Maria Agustina","Staf Marketing");



CREATE TABLE `users` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Petugas') DEFAULT 'Petugas',
  `nama_lengkap` varchar(150) DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `permissions` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Aktif',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

INSERT INTO users VALUES("1","admin","$2y$10$osRGk4AC1.J6VAjwnVVF4OxY171Iu.ReuxGdKMlknwBwVnNOspPL2","Admin","Administrator","superadmin","{\"can_manage_rujukan\":true,\"can_view_laporan\":true,\"can_manage_fee\":true,\"can_manage_users\":true,\"can_backup\":true,\"can_approve_mengetahui\":true,\"can_approve_dicek\":true,\"can_approve_menyetujui\":true}","Aktif");
INSERT INTO users VALUES("5","1107560","$2y$10$/tkWOwg.gtA7GUSkDLFbUe/sXhUMstXRPGK.bPf4zV6QuRKiAUgjm","Petugas","Rhofiah.Amd","Pjs Kabag Marketing","{\"can_manage_rujukan\":true,\"can_view_laporan\":true,\"can_manage_fee\":true,\"can_manage_users\":true,\"can_backup\":true,\"can_view_log\":false,\"can_approve_mengetahui\":true,\"can_approve_dicek\":false,\"can_approve_menyetujui\":false}","Aktif");
INSERT INTO users VALUES("6","wulan","$2y$10$/8x8MLbiI120sYgzVwzbyeSSuJO/8rA7ZfpRpW/4dJUWdfRUAtnv6","Petugas","Wulan Ria Fatmawati","KaUnit Akutansi  Keuangan","{\"can_manage_rujukan\":false,\"can_view_laporan\":true,\"can_manage_fee\":false,\"can_manage_users\":false,\"can_backup\":false,\"can_approve_mengetahui\":false,\"can_approve_dicek\":true,\"can_approve_menyetujui\":false}","Aktif");
INSERT INTO users VALUES("7","dr Monda","$2y$10$1UQQRJ0DdXWFSQBPHM5G5OOPe3w5LQJ2iYd2O/ihHggW2zTkK4jDW","Petugas","dr. Evameinonda., MMRS.MQM.FISQua","Direktur","{\"can_manage_rujukan\":true,\"can_view_laporan\":true,\"can_manage_fee\":false,\"can_manage_users\":false,\"can_backup\":false,\"can_view_log\":false,\"can_approve_mengetahui\":false,\"can_approve_dicek\":false,\"can_approve_menyetujui\":true}","Aktif");
INSERT INTO users VALUES("8","fitri","$2y$10$dUFijKNgF4gdmKZ6ayQfnO2LPoWGPkhoZx90HBuhXeEmxGiuW9STa","Petugas","Fitri Maria Agustina","Staf Marketing","{\"can_manage_rujukan\":true,\"can_view_laporan\":true,\"can_manage_fee\":true,\"can_manage_users\":false,\"can_backup\":false,\"can_view_log\":false,\"can_approve_mengetahui\":false,\"can_approve_dicek\":false,\"can_approve_menyetujui\":false}","Aktif");

