-- =============================================================
-- PEGAWAI_SEKTOR: mapping ID_BAGIAN + ID_DIVISI + UNIT_KERJA ke sektor
-- Sumber: kombinasi unik dari dbo.PEGAWAI_SDM (view), dibatasi ke pegawai
-- JENIS_PEGAWAI1 = Tetap, DPB, atau Mitra Layanan Jasa saja.
-- UNIT_KERJA ikut jadi bagian key karena 1 (ID_BAGIAN, ID_DIVISI) bisa
-- punya beberapa UNIT_KERJA berbeda (mis. level Departemen vs Staf).
-- Kolom ID_SEKTOR (CHAR(10), NULL) diisi manual setelah tabel terbentuk
-- Schema target: dbo
-- =============================================================
-- Tidak ada UNIQUE constraint: id_bagian=0 sengaja punya beberapa baris
-- duplikat per id_divisi (lihat blok "Padding manual" di bawah) supaya
-- tiap baris bisa diisi ID_SEKTOR yang berbeda-beda secara manual.
IF NOT EXISTS (SELECT 1 FROM sys.tables t
               JOIN sys.schemas s ON t.schema_id = s.schema_id
               WHERE s.name = 'dbo' AND t.name = 'PEGAWAI_SEKTOR')
BEGIN
    CREATE TABLE dbo.PEGAWAI_SEKTOR (
        ID          INT IDENTITY(1,1) PRIMARY KEY,
        ID_BAGIAN   INT NOT NULL,
        ID_DIVISI   INT NOT NULL,
        UNIT_KERJA  VARCHAR(100) NOT NULL DEFAULT '',
        ID_SEKTOR   CHAR(10) NULL
    );
END;

-- Tabel sudah ada dari sebelum UNIT_KERJA jadi kolom: tambahkan kolomnya.
IF COL_LENGTH('dbo.PEGAWAI_SEKTOR', 'UNIT_KERJA') IS NULL
    ALTER TABLE dbo.PEGAWAI_SEKTOR ADD UNIT_KERJA VARCHAR(100) NOT NULL DEFAULT '';

-- Longgarkan constraint lama (dari versi sebelumnya) supaya duplikat diperbolehkan.
IF EXISTS (SELECT 1 FROM sys.key_constraints WHERE name = 'UQ_PEGAWAI_SEKTOR' AND type = 'UQ')
    ALTER TABLE dbo.PEGAWAI_SEKTOR DROP CONSTRAINT UQ_PEGAWAI_SEKTOR;
GO

-- Isi kombinasi unik ID_BAGIAN + ID_DIVISI + UNIT_KERJA dari PEGAWAI_SDM,
-- dibatasi ke JENIS_PEGAWAI1 Tetap/DPB/Mitra Layanan Jasa (idempotent)
INSERT INTO dbo.PEGAWAI_SEKTOR (ID_BAGIAN, ID_DIVISI, UNIT_KERJA)
SELECT DISTINCT S.ID_BAGIAN, S.id_divisi, RTRIM(LTRIM(ISNULL(S.UNIT_KERJA, '')))
FROM dbo.PEGAWAI_SDM S
WHERE S.ID_BAGIAN IS NOT NULL AND S.id_divisi IS NOT NULL
  AND LTRIM(RTRIM(S.JENIS_PEGAWAI1)) IN ('Tetap', 'DPB', 'Mitra Layanan Jasa')
  AND NOT EXISTS (
      SELECT 1 FROM dbo.PEGAWAI_SEKTOR P
      WHERE P.ID_BAGIAN = S.ID_BAGIAN AND P.ID_DIVISI = S.id_divisi
        AND P.UNIT_KERJA = RTRIM(LTRIM(ISNULL(S.UNIT_KERJA, '')))
  );

-- =============================================================
-- Padding manual: id_bagian = 0, id_divisi 18/19/20/21 dipecah jadi
-- 3 baris masing-masing (duplikat UNIT_KERJA yang sudah ada) supaya
-- tiap baris bisa diisi ID_SEKTOR yang berbeda-beda secara manual.
-- Idempotent: hanya menambah baris sampai jumlahnya genap 3.
-- =============================================================
;WITH TargetDivisi (ID_DIVISI) AS (
    SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20 UNION ALL SELECT 21
),
Slot (N) AS (
    SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3
)
INSERT INTO dbo.PEGAWAI_SEKTOR (ID_BAGIAN, ID_DIVISI, UNIT_KERJA)
SELECT 0, D.ID_DIVISI,
       ISNULL((SELECT TOP 1 UNIT_KERJA FROM dbo.PEGAWAI_SEKTOR WHERE ID_BAGIAN = 0 AND ID_DIVISI = D.ID_DIVISI), '')
FROM TargetDivisi D
CROSS JOIN Slot
WHERE Slot.N > (SELECT COUNT(*) FROM dbo.PEGAWAI_SEKTOR P WHERE P.ID_BAGIAN = 0 AND P.ID_DIVISI = D.ID_DIVISI);

-- =============================================================
-- Kolom nama: NM_BAGIAN dari BAGIAN_TAB, NM_DIVISI dari DIVISI_TAB
-- Join: PEGAWAI_SEKTOR.ID_BAGIAN = BAGIAN_TAB.ID_BAGIAN
--       PEGAWAI_SEKTOR.ID_DIVISI = DIVISI_TAB.ID_DIVISI
-- =============================================================
IF COL_LENGTH('dbo.PEGAWAI_SEKTOR', 'NM_BAGIAN') IS NULL
    ALTER TABLE dbo.PEGAWAI_SEKTOR ADD NM_BAGIAN VARCHAR(100) NULL;

IF COL_LENGTH('dbo.PEGAWAI_SEKTOR', 'NM_DIVISI') IS NULL
    ALTER TABLE dbo.PEGAWAI_SEKTOR ADD NM_DIVISI VARCHAR(100) NULL;
GO

-- Isi NM_BAGIAN & NM_DIVISI sesuai ID (idempotent, aman diulang)
UPDATE P
SET P.NM_BAGIAN = RTRIM(B.NM_BAGIAN)
FROM dbo.PEGAWAI_SEKTOR P
JOIN dbo.BAGIAN_TAB B ON B.ID_BAGIAN = P.ID_BAGIAN;

UPDATE P
SET P.NM_DIVISI = RTRIM(D.NM_DIVISI)
FROM dbo.PEGAWAI_SEKTOR P
JOIN dbo.DIVISI_TAB D ON D.ID_DIVISI = P.ID_DIVISI;
