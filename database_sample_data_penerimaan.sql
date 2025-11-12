-- Sample Data untuk Penerimaan Piutang
-- Query ini menambahkan data penerimaan dengan referensi data yang sudah ada di database
-- Pastikan tabel headerpenjualan, mastercustomer, dan mastersales sudah memiliki data

-- ============================================================
-- SAMPLE DATA: Header Penerimaan 1
-- ============================================================
-- Menggunakan data penjualan yang memiliki saldopenjualan > 0
INSERT INTO headerpenerimaan (
    nopenerimaan,
    tanggalpenerimaan,
    statuspkp,
    jenispenerimaan,
    kodesales,
    kodecustomer,
    totalpiutang,
    totalpotongan,
    totallainlain,
    totalnetto,
    status,
    noinkaso,
    userid
)
SELECT 
    'PNR250001' AS nopenerimaan,
    CURDATE() AS tanggalpenerimaan,
    hp.statuspkp AS statuspkp,
    'tunai' AS jenispenerimaan,
    hp.kodesales AS kodesales,
    hp.kodecustomer AS kodecustomer,
    hp.saldopenjualan AS totalpiutang,
    0 AS totalpotongan,
    0 AS totallainlain,
    hp.saldopenjualan AS totalnetto,
    'belumproses' AS status,
    NULL AS noinkaso,
    'admin' AS userid
FROM headerpenjualan hp
WHERE hp.saldopenjualan > 0
  AND hp.kodesales IS NOT NULL
  AND hp.kodecustomer IS NOT NULL
LIMIT 1;

-- Detail Penerimaan untuk Header Penerimaan 1
INSERT INTO detailpenerimaan (
    nopenerimaan,
    nopenjualan,
    nogiro,
    tanggalcair,
    piutang,
    potongan,
    lainlain,
    netto,
    nourut
)
SELECT 
    'PNR250001' AS nopenerimaan,
    hp.nopenjualan AS nopenjualan,
    NULL AS nogiro,
    NULL AS tanggalcair,
    hp.saldopenjualan AS piutang,
    0 AS potongan,
    0 AS lainlain,
    hp.saldopenjualan AS netto,
    1 AS nourut
FROM headerpenjualan hp
WHERE hp.saldopenjualan > 0
  AND hp.kodesales IS NOT NULL
  AND hp.kodecustomer IS NOT NULL
LIMIT 1;

-- ============================================================
-- SAMPLE DATA: Header Penerimaan 2 (Transfer)
-- ============================================================
INSERT INTO headerpenerimaan (
    nopenerimaan,
    tanggalpenerimaan,
    statuspkp,
    jenispenerimaan,
    kodesales,
    kodecustomer,
    totalpiutang,
    totalpotongan,
    totallainlain,
    totalnetto,
    status,
    noinkaso,
    userid
)
SELECT 
    'PNR250002' AS nopenerimaan,
    DATE_SUB(CURDATE(), INTERVAL 1 DAY) AS tanggalpenerimaan,
    hp.statuspkp AS statuspkp,
    'transfer' AS jenispenerimaan,
    hp.kodesales AS kodesales,
    hp.kodecustomer AS kodecustomer,
    hp.saldopenjualan AS totalpiutang,
    50000 AS totalpotongan,
    0 AS totallainlain,
    (hp.saldopenjualan - 50000) AS totalnetto,
    'belumproses' AS status,
    NULL AS noinkaso,
    'admin' AS userid
FROM headerpenjualan hp
WHERE hp.saldopenjualan > 50000
  AND hp.kodesales IS NOT NULL
  AND hp.kodecustomer IS NOT NULL
  AND hp.nopenjualan NOT IN (
      SELECT DISTINCT nopenjualan 
      FROM detailpenerimaan 
      WHERE nopenerimaan = 'PNR250001'
  )
LIMIT 1;

-- Detail Penerimaan untuk Header Penerimaan 2
INSERT INTO detailpenerimaan (
    nopenerimaan,
    nopenjualan,
    nogiro,
    tanggalcair,
    piutang,
    potongan,
    lainlain,
    netto,
    nourut
)
SELECT 
    'PNR250002' AS nopenerimaan,
    hp.nopenjualan AS nopenjualan,
    NULL AS nogiro,
    NULL AS tanggalcair,
    hp.saldopenjualan AS piutang,
    50000 AS potongan,
    0 AS lainlain,
    (hp.saldopenjualan - 50000) AS netto,
    1 AS nourut
FROM headerpenjualan hp
WHERE hp.saldopenjualan > 50000
  AND hp.kodesales IS NOT NULL
  AND hp.kodecustomer IS NOT NULL
  AND hp.nopenjualan NOT IN (
      SELECT DISTINCT nopenjualan 
      FROM detailpenerimaan 
      WHERE nopenerimaan = 'PNR250001'
  )
LIMIT 1;

-- ============================================================
-- SAMPLE DATA: Header Penerimaan 3 (Giro)
-- ============================================================
INSERT INTO headerpenerimaan (
    nopenerimaan,
    tanggalpenerimaan,
    statuspkp,
    jenispenerimaan,
    kodesales,
    kodecustomer,
    totalpiutang,
    totalpotongan,
    totallainlain,
    totalnetto,
    status,
    noinkaso,
    userid
)
SELECT 
    'PNR250003' AS nopenerimaan,
    DATE_SUB(CURDATE(), INTERVAL 2 DAY) AS tanggalpenerimaan,
    hp.statuspkp AS statuspkp,
    'giro' AS jenispenerimaan,
    hp.kodesales AS kodesales,
    hp.kodecustomer AS kodecustomer,
    hp.saldopenjualan AS totalpiutang,
    0 AS totalpotongan,
    0 AS totallainlain,
    hp.saldopenjualan AS totalnetto,
    'belumproses' AS status,
    NULL AS noinkaso,
    'admin' AS userid
FROM headerpenjualan hp
WHERE hp.saldopenjualan > 0
  AND hp.kodesales IS NOT NULL
  AND hp.kodecustomer IS NOT NULL
  AND hp.nopenjualan NOT IN (
      SELECT DISTINCT nopenjualan 
      FROM detailpenerimaan 
      WHERE nopenerimaan IN ('PNR250001', 'PNR250002')
  )
LIMIT 1;

-- Detail Penerimaan untuk Header Penerimaan 3 (dengan Giro)
INSERT INTO detailpenerimaan (
    nopenerimaan,
    nopenjualan,
    nogiro,
    tanggalcair,
    piutang,
    potongan,
    lainlain,
    netto,
    nourut
)
SELECT 
    'PNR250003' AS nopenerimaan,
    hp.nopenjualan AS nopenjualan,
    'GIRO001' AS nogiro,
    DATE_ADD(CURDATE(), INTERVAL 30 DAY) AS tanggalcair,
    hp.saldopenjualan AS piutang,
    0 AS potongan,
    0 AS lainlain,
    hp.saldopenjualan AS netto,
    1 AS nourut
FROM headerpenjualan hp
WHERE hp.saldopenjualan > 0
  AND hp.kodesales IS NOT NULL
  AND hp.kodecustomer IS NOT NULL
  AND hp.nopenjualan NOT IN (
      SELECT DISTINCT nopenjualan 
      FROM detailpenerimaan 
      WHERE nopenerimaan IN ('PNR250001', 'PNR250002')
  )
LIMIT 1;

-- ============================================================
-- SAMPLE DATA: Header Penerimaan 4 (Multiple Details)
-- ============================================================
-- Penerimaan dengan beberapa detail penjualan
INSERT INTO headerpenerimaan (
    nopenerimaan,
    tanggalpenerimaan,
    statuspkp,
    jenispenerimaan,
    kodesales,
    kodecustomer,
    totalpiutang,
    totalpotongan,
    totallainlain,
    totalnetto,
    status,
    noinkaso,
    userid
)
SELECT 
    'PNR250004' AS nopenerimaan,
    DATE_SUB(CURDATE(), INTERVAL 3 DAY) AS tanggalpenerimaan,
    hp.statuspkp AS statuspkp,
    'tunai' AS jenispenerimaan,
    hp.kodesales AS kodesales,
    hp.kodecustomer AS kodecustomer,
    (SELECT SUM(saldopenjualan) 
     FROM headerpenjualan hp2 
     WHERE hp2.kodecustomer = hp.kodecustomer 
       AND hp2.saldopenjualan > 0
       AND hp2.nopenjualan NOT IN (
           SELECT DISTINCT nopenjualan 
           FROM detailpenerimaan
       )
     LIMIT 2) AS totalpiutang,
    100000 AS totalpotongan,
    0 AS totallainlain,
    (SELECT SUM(saldopenjualan) 
     FROM headerpenjualan hp2 
     WHERE hp2.kodecustomer = hp.kodecustomer 
       AND hp2.saldopenjualan > 0
       AND hp2.nopenjualan NOT IN (
           SELECT DISTINCT nopenjualan 
           FROM detailpenerimaan
       )
     LIMIT 2) - 100000 AS totalnetto,
    'belumproses' AS status,
    NULL AS noinkaso,
    'admin' AS userid
FROM headerpenjualan hp
WHERE hp.saldopenjualan > 0
  AND hp.kodesales IS NOT NULL
  AND hp.kodecustomer IS NOT NULL
  AND hp.nopenjualan NOT IN (
      SELECT DISTINCT nopenjualan 
      FROM detailpenerimaan
  )
GROUP BY hp.kodecustomer
HAVING COUNT(DISTINCT hp.nopenjualan) >= 2
LIMIT 1;

-- Detail Penerimaan 1 untuk Header Penerimaan 4
INSERT INTO detailpenerimaan (
    nopenerimaan,
    nopenjualan,
    nogiro,
    tanggalcair,
    piutang,
    potongan,
    lainlain,
    netto,
    nourut
)
SELECT 
    'PNR250004' AS nopenerimaan,
    hp.nopenjualan AS nopenjualan,
    NULL AS nogiro,
    NULL AS tanggalcair,
    hp.saldopenjualan AS piutang,
    50000 AS potongan,
    0 AS lainlain,
    (hp.saldopenjualan - 50000) AS netto,
    1 AS nourut
FROM headerpenjualan hp
WHERE hp.saldopenjualan > 0
  AND hp.kodesales IS NOT NULL
  AND hp.kodecustomer IS NOT NULL
  AND hp.nopenjualan NOT IN (
      SELECT DISTINCT nopenjualan 
      FROM detailpenerimaan
  )
  AND hp.kodecustomer = (
      SELECT kodecustomer 
      FROM headerpenerimaan 
      WHERE nopenerimaan = 'PNR250004'
  )
LIMIT 1;

-- Detail Penerimaan 2 untuk Header Penerimaan 4
INSERT INTO detailpenerimaan (
    nopenerimaan,
    nopenjualan,
    nogiro,
    tanggalcair,
    piutang,
    potongan,
    lainlain,
    netto,
    nourut
)
SELECT 
    'PNR250004' AS nopenerimaan,
    hp.nopenjualan AS nopenjualan,
    NULL AS nogiro,
    NULL AS tanggalcair,
    hp.saldopenjualan AS piutang,
    50000 AS potongan,
    0 AS lainlain,
    (hp.saldopenjualan - 50000) AS netto,
    2 AS nourut
FROM headerpenjualan hp
WHERE hp.saldopenjualan > 0
  AND hp.kodesales IS NOT NULL
  AND hp.kodecustomer IS NOT NULL
  AND hp.nopenjualan NOT IN (
      SELECT DISTINCT nopenjualan 
      FROM detailpenerimaan
  )
  AND hp.kodecustomer = (
      SELECT kodecustomer 
      FROM headerpenerimaan 
      WHERE nopenerimaan = 'PNR250004'
  )
  AND hp.nopenjualan NOT IN (
      SELECT nopenjualan 
      FROM detailpenerimaan 
      WHERE nopenerimaan = 'PNR250004' AND nourut = 1
  )
LIMIT 1;

-- ============================================================
-- QUERY ALTERNATIF: Insert dengan data spesifik
-- ============================================================
-- Jika Anda ingin insert dengan data spesifik yang sudah diketahui

-- Contoh: Insert penerimaan untuk penjualan tertentu
/*
INSERT INTO headerpenerimaan (
    nopenerimaan,
    tanggalpenerimaan,
    statuspkp,
    jenispenerimaan,
    kodesales,
    kodecustomer,
    totalpiutang,
    totalpotongan,
    totallainlain,
    totalnetto,
    status,
    noinkaso,
    userid
)
SELECT 
    'PNR250005' AS nopenerimaan,
    CURDATE() AS tanggalpenerimaan,
    hp.statuspkp,
    'tunai' AS jenispenerimaan,
    hp.kodesales,
    hp.kodecustomer,
    hp.saldopenjualan,
    0,
    0,
    hp.saldopenjualan,
    'belumproses' AS status,
    NULL AS noinkaso,
    'admin' AS userid
FROM headerpenjualan hp
WHERE hp.nopenjualan = 'PNJ240001'  -- Ganti dengan no penjualan yang ada
  AND hp.saldopenjualan > 0;

INSERT INTO detailpenerimaan (
    nopenerimaan,
    nopenjualan,
    nogiro,
    tanggalcair,
    piutang,
    potongan,
    lainlain,
    netto,
    nourut
)
SELECT 
    'PNR250005' AS nopenerimaan,
    hp.nopenjualan,
    NULL AS nogiro,
    NULL AS tanggalcair,
    hp.saldopenjualan,
    0,
    0,
    hp.saldopenjualan,
    1 AS nourut
FROM headerpenjualan hp
WHERE hp.nopenjualan = 'PNJ240001'  -- Ganti dengan no penjualan yang ada
  AND hp.saldopenjualan > 0;
*/

-- ============================================================
-- QUERY UNTUK UPDATE SALDO PENJUALAN SETELAH PENERIMAAN
-- ============================================================
-- Setelah data penerimaan dibuat, update saldo penjualan
-- (Ini biasanya dilakukan oleh aplikasi, tapi bisa juga manual)

/*
UPDATE headerpenjualan hp
INNER JOIN detailpenerimaan dp ON hp.nopenjualan = dp.nopenjualan
SET hp.saldopenjualan = hp.saldopenjualan - dp.netto
WHERE dp.nopenerimaan = 'PNR250001'
  AND hp.saldopenjualan >= dp.netto;
*/

-- ============================================================
-- QUERY UNTUK CEK DATA PENERIMAAN YANG SUDAH DIBUAT
-- ============================================================
/*
SELECT 
    hp.nopenerimaan,
    hp.tanggalpenerimaan,
    hp.jenispenerimaan,
    hp.status,
    mc.namacustomer,
    ms.namasales,
    hp.totalnetto,
    hp.noinkaso,
    COUNT(dp.id) AS jumlah_detail
FROM headerpenerimaan hp
LEFT JOIN mastercustomer mc ON hp.kodecustomer = mc.kodecustomer
LEFT JOIN mastersales ms ON hp.kodesales = ms.kodesales
LEFT JOIN detailpenerimaan dp ON hp.nopenerimaan = dp.nopenerimaan
GROUP BY hp.nopenerimaan
ORDER BY hp.tanggalpenerimaan DESC, hp.nopenerimaan DESC;
*/

-- ============================================================
-- QUERY UNTUK CEK DETAIL PENERIMAAN
-- ============================================================
/*
SELECT 
    dp.nopenerimaan,
    dp.nopenjualan,
    hpj.tanggalpenjualan,
    mc.namacustomer,
    dp.nogiro,
    dp.tanggalcair,
    dp.piutang,
    dp.potongan,
    dp.lainlain,
    dp.netto,
    dp.nourut
FROM detailpenerimaan dp
LEFT JOIN headerpenjualan hpj ON dp.nopenjualan = hpj.nopenjualan
LEFT JOIN mastercustomer mc ON hpj.kodecustomer = mc.kodecustomer
WHERE dp.nopenerimaan = 'PNR250001'
ORDER BY dp.nourut ASC;
*/

