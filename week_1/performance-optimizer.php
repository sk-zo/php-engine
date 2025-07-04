<?php
// performance-optimizer.php
class PerformanceOptimizer {
    private $metrics = [];
    
    public function optimizeFullFlow() {
        echo "=== 전체 플로우 최적화 ===<br>";
        
        // 1. 네트워크 레벨 최적화
        $this->optimizeNetworkLevel();
        
        // 2. 웹서버 레벨 최적화
        $this->optimizeWebServerLevel();
        
        // 3. PHP 레벨 최적화
        $this->optimizePHPLevel();
        
        $this->generateOptimizationReport();
    }
    
    private function optimizeNetworkLevel() {
        echo "1. 네트워크 레벨 최적화:<br>";
        
        // Keep-Alive 확인
        $connection = $_SERVER['HTTP_CONNECTION'] ?? '';
        echo "   Keep-Alive: " . ($connection === 'keep-alive' ? '활성화' : '비활성화') . "<br>";
        
        // 압축 확인
        $accept_encoding = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';
        echo "   압축 지원: " . (strpos($accept_encoding, 'gzip') !== false ? 'gzip' : '없음') . "<br>";
        
        $this->metrics['network'] = [
            'keep_alive' => $connection === 'keep-alive',
            'gzip_support' => strpos($accept_encoding, 'gzip') !== false
        ];
    }
    
    private function optimizeWebServerLevel() {
        echo "<br>2. 웹서버 레벨 최적화:<br>";
        
        // 정적 파일 캐싱
        $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
        echo "   정적 파일 여부: " . (pathinfo($script_name, PATHINFO_EXTENSION) === 'php' ? 'PHP' : '정적파일') . "<br>";
        
        // 로드 확인
        $load_avg = sys_getloadavg();
        echo "   시스템 로드: " . round($load_avg[0], 5) . "<br>";
        
        $this->metrics['webserver'] = [
            'load_average' => $load_avg[0],
            'is_php' => pathinfo($script_name, PATHINFO_EXTENSION) === 'php'
        ];
    }
    
    private function optimizePHPLevel() {
        echo "<br>3. PHP 레벨 최적화:<br>";
        
        // OPcache 확인
        $opcache_enabled = extension_loaded('opcache');
        echo "   OPcache: " . ($opcache_enabled ? '활성화' : '비활성화') . "<br>";
        
        // 메모리 최적화
        $memory_usage = memory_get_usage(true);
        $memory_limit = ini_get('memory_limit');
        echo "   메모리 사용: " . round($memory_usage / 1024 / 1024, 2) . "MB / $memory_limit<br>";
        
        $this->metrics['php'] = [
            'opcache_enabled' => $opcache_enabled,
            'memory_usage_mb' => round($memory_usage / 1024 / 1024, 2)
        ];
    }
    
    private function generateOptimizationReport() {
        echo "<br>=== 최적화 권장사항 ===<br>";
        
        $recommendations = [];
        
        if (!$this->metrics['network']['keep_alive']) {
            $recommendations[] = "Apache Keep-Alive 활성화 권장";
        }
        
        if (!$this->metrics['network']['gzip_support']) {
            $recommendations[] = "Gzip 압축 활성화 권장";
        }
        
        if (!$this->metrics['php']['opcache_enabled']) {
            $recommendations[] = "OPcache 활성화 필수";
        }
        
        if ($this->metrics['webserver']['load_average'] > 2.0) {
            $recommendations[] = "시스템 로드 높음 - 리소스 증설 검토";
        }
        
        if (empty($recommendations)) {
            echo "✅ 최적화 상태 양호<br>";
        } else {
            foreach ($recommendations as $i => $rec) {
                echo ($i + 1) . ". $rec<br>";
            }
        }
    }
}

$optimizer = new PerformanceOptimizer();
$optimizer->optimizeFullFlow();
?>