<?php
$start_time = microtime(true);

$data = array();
for ($i = 0; $i < 10000; $i++) {
    $data[] = "item-" . $i;
}

$end_time = microtime(true);
$execution_time = $end_time - $start_time;

echo "<p>처리 시간: " . $execution_time . "초</p>";
echo "메모리 사용량: " . number_format(memory_get_usage() / 1024 / 1024, 2) . "MB";
?>