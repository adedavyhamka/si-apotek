<?php
/**
 * Akses kontrol berbasis peran (RBAC).
 * Key   = modul (nilai $_GET['m'])
 * Value = daftar role yang boleh mengakses.
 */
declare(strict_types=1);

return [
    'dashboard'   => ['super_admin', 'pemilik', 'apoteker', 'kasir'],
    'pemilik'     => ['super_admin', 'pemilik'],
    'apotek'      => ['super_admin', 'pemilik'],
    'apoteker'    => ['super_admin', 'pemilik'],
    'kasir'       => ['super_admin', 'pemilik'],
    'obat'        => ['super_admin', 'pemilik', 'apoteker'],
    'pelanggan'   => ['super_admin', 'pemilik', 'kasir', 'apoteker'],
    'resep'       => ['super_admin', 'pemilik', 'apoteker'],
    'transaksi'   => ['super_admin', 'pemilik', 'kasir'],
    'laporan'     => ['super_admin', 'pemilik'],
    'user'        => ['super_admin'],
    'profil'      => ['super_admin', 'pemilik', 'apoteker', 'kasir'],
    // 'auth' sengaja tidak ada di sini: selalu publik.
];
