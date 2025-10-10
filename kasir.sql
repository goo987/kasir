CREATE DATABASE IF NOT EXISTS kasir DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kasir;

DROP TABLE IF EXISTS transaksi_detail;
DROP TABLE IF EXISTS transaksi;
DROP TABLE IF EXISTS barang;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS pelanggan;

CREATE TABLE users (
  id_user INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','kasir') NOT NULL
);

CREATE TABLE barang (
  id_barang INT AUTO_INCREMENT PRIMARY KEY,
  kode_barang VARCHAR(50) NOT NULL UNIQUE,
  nama_barang VARCHAR(255) NOT NULL,
  harga DECIMAL(12,2) NOT NULL DEFAULT 0,
  stok INT NOT NULL DEFAULT 0
);

CREATE TABLE transaksi (
  id_transaksi INT AUTO_INCREMENT PRIMARY KEY,
  tanggal DATETIME NOT NULL,
  id_user INT NOT NULL,
  pelanggan VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (id_user) REFERENCES users(id_user)
);

CREATE TABLE transaksi_detail (
  id_detail INT AUTO_INCREMENT PRIMARY KEY,
  id_transaksi INT NOT NULL,
  id_barang INT NOT NULL,
  qty INT NOT NULL,
  subtotal DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (id_transaksi) REFERENCES transaksi(id_transaksi) ON DELETE CASCADE,
  FOREIGN KEY (id_barang) REFERENCES barang(id_barang)
);

CREATE TABLE pelanggan (
  id_pelanggan INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(255),
  alamat TEXT,
  telepon VARCHAR(50)
);

INSERT INTO users (username,password,role) VALUES
('admin','admin123','admin'),
('kasir1','kasir123','kasir');

INSERT INTO barang (kode_barang,nama_barang,harga,stok) VALUES
('B001','Pulpen',1500.00,100),
('B002','Buku Tulis',5000.00,50),
('B003','Penghapus',1000.00,200);
