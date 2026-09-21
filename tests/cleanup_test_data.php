<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=db_assami', 'root', '');
$db->query("DELETE FROM tb_spm WHERE ID_PENGAJUAN = '000001.09.2026'");
$db->query("DELETE FROM tb_npd WHERE ID_PENGAJUAN = 'NPD.000001.09.2026'");
$db->query("DELETE FROM tb_data_detail WHERE NO_NPD_SPM IN ('000001.09.2026', 'NPD.000001.09.2026')");
echo "Test data cleaned up successfully.\n";
