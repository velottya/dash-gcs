-- =============================================================
-- DASH GCS — SQL Server migration script
-- Jalankan script ini di SQL Server Management Studio
-- Schema target: DASH
-- =============================================================

-- 1. Update akun demo → superadmin (ID_LEVEL = 1)
--    Ganti 'demo' dengan username yang ingin dijadikan superadmin
-- UPDATE DASH.MST_USER SET USERNAME = 'superadmin', ID_LEVEL = 1 WHERE USERNAME = 'demo';

-- 2. Pastikan kolom ID_LEVEL bertipe INT, jika belum:
-- ALTER TABLE DASH.MST_USER ALTER COLUMN ID_LEVEL INT;

-- =============================================================
-- GM_MANAGER: mapping General Manager → Manager
-- =============================================================
IF NOT EXISTS (SELECT 1 FROM sys.tables t
               JOIN sys.schemas s ON t.schema_id = s.schema_id
               WHERE s.name = 'DASH' AND t.name = 'GM_MANAGER')
BEGIN
    CREATE TABLE DASH.GM_MANAGER (
        ID          INT IDENTITY(1,1) PRIMARY KEY,
        NIK_GM      NVARCHAR(50) NOT NULL,
        NIK_MANAGER NVARCHAR(50) NOT NULL,
        DATE_CREATE DATETIME DEFAULT GETDATE(),
        CONSTRAINT UQ_GM_MANAGER UNIQUE (NIK_GM, NIK_MANAGER)
    );
END;

-- =============================================================
-- RKAP: pengajuan anggaran tahunan
-- =============================================================
IF NOT EXISTS (SELECT 1 FROM sys.tables t
               JOIN sys.schemas s ON t.schema_id = s.schema_id
               WHERE s.name = 'DASH' AND t.name = 'RKAP')
BEGIN
    CREATE TABLE DASH.RKAP (
        ID                  INT IDENTITY(1,1) PRIMARY KEY,
        TAHUN               INT NOT NULL,
        NIK_MANAGER         NVARCHAR(50) NOT NULL,
        PRODUK              NVARCHAR(200) NOT NULL,
        STATUS              NVARCHAR(20) NOT NULL DEFAULT 'draft',
        -- Status: draft | submitted | gm_approved | gm_rejected | approved | rejected

        CATATAN_GM          NVARCHAR(MAX) NULL,
        NIK_GM_VALIDATOR    NVARCHAR(50)  NULL,
        TGL_VALIDASI_GM     DATETIME      NULL,

        CATATAN_DIREKSI     NVARCHAR(MAX) NULL,
        NIK_DIREKSI         NVARCHAR(50)  NULL,
        TGL_VALIDASI_DIREKSI DATETIME     NULL,

        TGL_SUBMIT          DATETIME      NULL,
        DATE_CREATE         DATETIME      DEFAULT GETDATE(),
        DATE_UPDATE         DATETIME      DEFAULT GETDATE()
    );
END;

-- =============================================================
-- RKAP_DETAIL: rincian per bulan (Jan–Des)
-- =============================================================
IF NOT EXISTS (SELECT 1 FROM sys.tables t
               JOIN sys.schemas s ON t.schema_id = s.schema_id
               WHERE s.name = 'DASH' AND t.name = 'RKAP_DETAIL')
BEGIN
    CREATE TABLE DASH.RKAP_DETAIL (
        ID          INT IDENTITY(1,1) PRIMARY KEY,
        RKAP_ID     INT NOT NULL,
        BULAN       TINYINT NOT NULL,   -- 1–12
        QTY_TON     DECIMAL(18,3) NULL DEFAULT 0,
        NILAI_RUPIAH DECIMAL(18,2) NULL DEFAULT 0,
        CONSTRAINT FK_RKAP_DETAIL FOREIGN KEY (RKAP_ID) REFERENCES DASH.RKAP(ID) ON DELETE CASCADE,
        CONSTRAINT UQ_RKAP_BULAN UNIQUE (RKAP_ID, BULAN)
    );
END;

-- =============================================================
-- USER_EXTRA: data tambahan user (phone, email, dll.)
-- =============================================================
IF NOT EXISTS (SELECT 1 FROM sys.tables t
               JOIN sys.schemas s ON t.schema_id = s.schema_id
               WHERE s.name = 'DASH' AND t.name = 'USER_EXTRA')
BEGIN
    CREATE TABLE DASH.USER_EXTRA (
        NIK     NVARCHAR(50) PRIMARY KEY,
        PHONE   NVARCHAR(50)  NULL,
        EMAIL   NVARCHAR(100) NULL,
        BIO     NVARCHAR(500) NULL,
        DATE_UPDATE DATETIME  DEFAULT GETDATE()
    );
END;

-- =============================================================
-- Insert dummy superadmin jika belum ada (contoh)
-- =============================================================
-- Pastikan ada 1 akun superadmin (ID_LEVEL = 1) di DASH.MST_USER
-- Jika akun 'demo' ada, update menjadi superadmin:
-- UPDATE DASH.MST_USER SET ID_LEVEL = 1, USERNAME = 'superadmin' WHERE USERNAME = 'demo';
