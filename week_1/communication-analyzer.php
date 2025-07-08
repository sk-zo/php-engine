<?php
// communication-analyzer.php
class CommunicationAnalyzer {
    public function analyzeCommunicationMethods() {
        echo "=== Apache-PHP 통신 방식 분석 ===<br>";
        
        // 현재 SAPI 방식 확인
        $sapi = php_sapi_name();
        echo "현재 SAPI: $sapi<br><br>";
        
        switch($sapi) {
            case 'apache2handler':
                $this->analyzeModPHP();
                break;
            case 'fpm-fcgi':
                $this->analyzeFPM();
                break;
            case 'cli':
                $this->analyzeCLI();
                break;
            default:
                echo "기타 SAPI: $sapi<br>";
        }
        
        $this->comparePerformance();
    }
    
    private function analyzeModPHP() {
        echo "📋 mod_php 분석:<br>";
        echo "- 장점: 빠른 응답 속도 (임베디드)<br>";
        echo "- 단점: 메모리 사용량 높음<br>";
        echo "- 프로세스 모델: Apache 프로세스 내 실행<br><br>";
        
        // 메모리 사용량 확인
        $memory_usage = memory_get_usage(true);
        echo "현재 메모리 사용량: " . round($memory_usage / 1024 / 1024, 2) . "MB<br><br>";
    }
    
    private function analyzeFPM() {
        echo "📋 PHP-FPM 분석:<br>";
        echo "- 장점: 독립적인 프로세스 관리<br>";
        echo "- 단점: 소켓 통신 오버헤드<br>";
        echo "- 프로세스 모델: 별도 프로세스 풀<br><br>";
    }
    
    private function comparePerformance() {
        echo "=== 성능 비교 테스트 ===<br>";
        
        $iterations = 1000;
        $start = microtime(true);
        
        for($i = 0; $i < $iterations; $i++) {
            $dummy = str_repeat('A', 1000);
            unset($dummy);
        }
        
        $end = microtime(true);
        $time_per_iteration = ($end - $start) / $iterations * 1000;
        
        echo "반복 횟수: $iterations<br>";
        echo "평균 처리 시간: " . number_format($time_per_iteration, 4) . "ms<br>";
        echo "초당 처리 가능: " . number_format(1000 / $time_per_iteration, 0) . "req/sec<br>";
    }
}

$analyzer = new CommunicationAnalyzer();
$analyzer->analyzeCommunicationMethods();
?>