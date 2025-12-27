<?php
// Mengatur header agar browser membacanya sebagai file CSV yang bisa didownload
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="template_import_barang.csv"');

// Membuka output stream
$output = fopen('php://output', 'w');

// 1. Tulis Baris Judul (Header) - JANGAN DIUBAH URUTANNYA
fputcsv($output, array('Kode Barang', 'Nama Barang', 'Satuan', 'Stok', 'Keterangan'));

// 2. Tulis Contoh Data (Dummy) sebagai panduan user
fputcsv($output, array('BRG-001', 'Contoh Laptop ASUS', 'Unit', '10', 'Laptop Admin'));
fputcsv($output, array('BRG-002', 'Contoh Kertas A4', 'Rim', '50', 'Kertas Paperone 80gr'));
fputcsv($output, array('BRG-003', 'Contoh Spidol', 'Pcs', '25', 'Warna Hitam'));

// Tutup file
fclose($output);
exit;
?>