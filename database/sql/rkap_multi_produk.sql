-- =============================================================
-- RKAP multi-produk: 1 baris DASH.RKAP = 1 pengajuan tahunan per
-- manager (bukan 1 baris per produk lagi). Produk pindah jadi
-- baris DASH.RKAP_DETAIL (per produk per bulan).
-- Schema target: DASH
-- =============================================================

-- DASH.RKAP.PRODUK sudah tidak dipakai (satu header sekarang bisa
-- berisi banyak produk) - longgarkan jadi nullable, biarkan kolom
-- mati daripada di-drop.
IF COL_LENGTH('DASH.RKAP', 'PRODUK') IS NOT NULL
    ALTER TABLE DASH.RKAP ALTER COLUMN PRODUK NVARCHAR(200) NULL;

-- Setiap manager hanya boleh punya 1 pengajuan RKAP per tahun.
IF NOT EXISTS (SELECT 1 FROM sys.key_constraints WHERE name = 'UQ_RKAP_MANAGER_TAHUN')
    ALTER TABLE DASH.RKAP ADD CONSTRAINT UQ_RKAP_MANAGER_TAHUN UNIQUE (NIK_MANAGER, TAHUN);

-- DASH.RKAP_DETAIL: tambah STOCKID (referensi dbo.INVENTORY) dan
-- PRODUK (nama produk didenormalisasi) supaya 1 RKAP bisa memuat
-- banyak produk.
IF COL_LENGTH('DASH.RKAP_DETAIL', 'STOCKID') IS NULL
    ALTER TABLE DASH.RKAP_DETAIL ADD STOCKID VARCHAR(10) NOT NULL DEFAULT '';

IF COL_LENGTH('DASH.RKAP_DETAIL', 'PRODUK') IS NULL
    ALTER TABLE DASH.RKAP_DETAIL ADD PRODUK VARCHAR(100) NOT NULL DEFAULT '';

-- Ganti unique constraint lama (RKAP_ID, BULAN) -> (RKAP_ID, STOCKID, BULAN)
-- supaya 1 RKAP bisa punya banyak produk x 12 bulan.
IF EXISTS (SELECT 1 FROM sys.key_constraints WHERE name = 'UQ_RKAP_BULAN')
BEGIN
    ALTER TABLE DASH.RKAP_DETAIL DROP CONSTRAINT UQ_RKAP_BULAN;
    ALTER TABLE DASH.RKAP_DETAIL ADD CONSTRAINT UQ_RKAP_DETAIL UNIQUE (RKAP_ID, STOCKID, BULAN);
END;
