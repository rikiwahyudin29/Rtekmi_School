CREATE TABLE `tbl_tujuan_pembelajaran` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `mapel_id` bigint(20) UNSIGNED NOT NULL,
  `guru_id` bigint(20) UNSIGNED NOT NULL,
  `kode_tp` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `semester` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tingkat` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`mapel_id`) REFERENCES `tbl_mapel` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`guru_id`) REFERENCES `tbl_guru` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tbl_nilai_formatif` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tp_id` bigint(20) UNSIGNED NOT NULL,
  `siswa_id` bigint(20) UNSIGNED NOT NULL,
  `nilai` double(8,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`tp_id`) REFERENCES `tbl_tujuan_pembelajaran` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`siswa_id`) REFERENCES `tbl_siswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tbl_nilai_sumatif` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `mapel_id` bigint(20) UNSIGNED NOT NULL,
  `guru_id` bigint(20) UNSIGNED NOT NULL,
  `siswa_id` bigint(20) UNSIGNED NOT NULL,
  `jenis` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nilai` double(8,2) NOT NULL DEFAULT '0.00',
  `semester` int(11) NOT NULL,
  `tahun_ajaran_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`mapel_id`) REFERENCES `tbl_mapel` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`guru_id`) REFERENCES `tbl_guru` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`siswa_id`) REFERENCES `tbl_siswa` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tbl_tahun_ajaran` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tbl_rapor_akhir` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `siswa_id` bigint(20) UNSIGNED NOT NULL,
  `mapel_id` bigint(20) UNSIGNED NOT NULL,
  `guru_id` bigint(20) UNSIGNED NOT NULL,
  `tahun_ajaran_id` bigint(20) UNSIGNED NOT NULL,
  `semester` int(11) NOT NULL,
  `nilai_akhir` double(8,2) NOT NULL DEFAULT '0.00',
  `deskripsi_tertinggi` text COLLATE utf8mb4_unicode_ci,
  `deskripsi_terendah` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`siswa_id`) REFERENCES `tbl_siswa` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`mapel_id`) REFERENCES `tbl_mapel` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`guru_id`) REFERENCES `tbl_guru` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tbl_tahun_ajaran` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tbl_rapor_kehadiran` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `siswa_id` bigint(20) UNSIGNED NOT NULL,
  `tahun_ajaran_id` bigint(20) UNSIGNED NOT NULL,
  `semester` int(11) NOT NULL,
  `sakit` int(11) NOT NULL DEFAULT '0',
  `izin` int(11) NOT NULL DEFAULT '0',
  `tanpa_keterangan` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`siswa_id`) REFERENCES `tbl_siswa` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tbl_tahun_ajaran` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tbl_rapor_catatan_wali` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `siswa_id` bigint(20) UNSIGNED NOT NULL,
  `guru_id` bigint(20) UNSIGNED NOT NULL,
  `tahun_ajaran_id` bigint(20) UNSIGNED NOT NULL,
  `semester` int(11) NOT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`siswa_id`) REFERENCES `tbl_siswa` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`guru_id`) REFERENCES `tbl_guru` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tbl_tahun_ajaran` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tbl_rapor_prestasi` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `siswa_id` bigint(20) UNSIGNED NOT NULL,
  `tahun_ajaran_id` bigint(20) UNSIGNED NOT NULL,
  `semester` int(11) NOT NULL,
  `jenis_prestasi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_prestasi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`siswa_id`) REFERENCES `tbl_siswa` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tbl_tahun_ajaran` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tbl_rapor_pkl` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `siswa_id` bigint(20) UNSIGNED NOT NULL,
  `dudi_id` bigint(20) UNSIGNED NOT NULL,
  `tahun_ajaran_id` bigint(20) UNSIGNED NOT NULL,
  `semester` int(11) NOT NULL,
  `lokasi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lama_bulan` int(11) NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `nilai` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`siswa_id`) REFERENCES `tbl_siswa` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`dudi_id`) REFERENCES `tbl_dudi` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tbl_tahun_ajaran` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
