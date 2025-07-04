<?php
class NetworkAnalyzer {
    public function analyzeRequest() {
        echo "=== HTTP 요청 분석 ===<br>";

        // 1. TCP 연결 정보
        echo "1. TCP 연결:<br>";
        echo "   - 클라이언트 IP: " . $_SERVER['REMOTE_ADDR'] . "<br>";
        echo "   - 클라이언트 포트: " . $_SERVER['REMOTE_PORT'] . "<br>";
        echo "   - 서버 IP: " . $_SERVER['SERVER_ADDR'] . "<br>";
        echo "   - 서버 포트: " . $_SERVER['SERVER_PORT'] . "<br>";

        // 2. HTTP 프로토콜 정보
        echo "2. HTTP 프로토콜:<br>";
        echo "   - 프로토콜: " . $_SERVER['SERVER_PROTOCOL'] . "<br>";
        echo "   - 헤더: " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "<br>";
        echo "   - 메서드: " . $_SERVER['REQUEST_METHOD'] . "<br>";
        echo "   - URI: " . $_SERVER['REQUEST_URI'] . "<br>";
        echo "   - Host: " . $_SERVER['HTTP_HOST'] . "<br>";

        // 3. 타이밍 정보
        echo "3. 타이밍:<br>";
        echo "   - 요청 시작: " . $_SERVER['REQUEST_TIME'] . "<br>";
        echo "   - 정확한 시간: " . $_SERVER['REQUEST_TIME_FLOAT'] . "<br>";
        echo "   - 총 처리 시간: " . (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) . "초<br>";
    }

    private function generateNetworkReport() {
        return [
            'tcp_info' => [
                'client' => $_SERVER['REMOTE_ADDR'] . ':' . $_SERVER['REMOTE_PORT'],
                'server' => $_SERVER['SERVER_ADDR'] . ':' . $_SERVER['SERVER_PORT'],
            ],
            'http_info' => [
                'protocol' => $_SERVER['SERVER_PROTOCOL'],
                'method' => $_SERVER['REQUEST_METHOD'],
                'uri' => $_SERVER['REQUEST_URI'],
            ],
            'timing' => [
                'request_time' => $_SERVER['REQUEST_TIME_FLOAT'],
                'total_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
            ]
        ];
    }
}

$analyzer = new NetworkAnalyzer();
$analyzer->analyzeRequest();
?>