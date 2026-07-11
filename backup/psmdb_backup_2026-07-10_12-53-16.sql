

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
  `aktivitas` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




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

INSERT INTO pelaksanaan_rujukan_psm VALUES("2026/07/09/000156","2026-07-10 09:03:39","25","admin","Pasien","-");
INSERT INTO pelaksanaan_rujukan_psm VALUES("2026/07/09/000266","2026-07-10 08:53:30","9","admin","Pasien","-");
INSERT INTO pelaksanaan_rujukan_psm VALUES("2026/07/09/000269","2026-07-10 09:31:12","5","admin","Pasien","-");
INSERT INTO pelaksanaan_rujukan_psm VALUES("2026/07/10/000175","2026-07-10 09:33:33","5","admin","Pasien","-");



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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO setting_ttd_amprahan VALUES("1","Rhofiah","Pjs. Kabag. Marketing","Wulan Ria Fatmawati","Ka.Unit Akutansi & Keuangan","dr. Evameinonda, MMRS.,MQM.,FISQua","Direktur");



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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

INSERT INTO users VALUES("1","admin","$2y$10$ZJouIlWDSoZ85uv3a2SXR.GBgLRfADY2dOvFdf.CoWHr7hmqcFF.2","Admin","","","","Aktif");
INSERT INTO users VALUES("5","1107560","$2y$10$/tkWOwg.gtA7GUSkDLFbUe/sXhUMstXRPGK.bPf4zV6QuRKiAUgjm","Petugas","RhofiahAmd","Pjs Kabag Marketing","{\"can_manage_rujukan\":true,\"can_view_laporan\":true,\"can_manage_fee\":true,\"can_manage_users\":true,\"can_backup\":true}","Aktif");

