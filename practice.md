# PHP 엔진 실무 실습 가이드 🚀

> **목표**: 실제 프로젝트에서 성능 개선을 달성할 수 있는 실무적 실습

---

## 📊 1. 실제 프로젝트 성능 분석 실습

### 🎯 목표: 현재 담당 프로젝트의 성능 병목 지점 발견

#### 실습 1-1: 현재 프로젝트 성능 베이스라인 측정
```php
<?php
// project-benchmark.php
class ProjectBenchmark {
    private $results = [];
    
    public function measureCurrentPerformance() {
        $this->measurePageLoadTimes();
        $this->measureMemoryUsage();
        $this->measureDatabaseQueries();
        $this->generateReport();
    }
    
    private function measurePageLoadTimes() {
        $pages = [
            '/main',
            '/product/list', 
            '/user/profile',
            '/admin/dashboard'
        ];
        
        foreach ($pages as $page) {
            $times = [];
            for ($i = 0; $i < 5; $i++) {
                $start = microtime(true);
                // 실제 페이지 호출 시뮬레이션
                $this->simulatePageRequest($page);
                $times[] = microtime(true) - $start;
            }
            
            $this->results['pages'][$page] = [
                'average' => array_sum($times) / count($times),
                'min' => min($times),
                'max' => max($times)
            ];
        }
    }
    
    private function measureMemoryUsage() {
        $this->results['memory'] = [
            'current' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'limit' => ini_get('memory_limit')
        ];
    }
    
    private function generateReport() {
        echo "=== 현재 프로젝트 성능 분석 ===\n";
        
        echo "\n📄 페이지별 성능:\n";
        foreach ($this->results['pages'] as $page => $metrics) {
            $avg_ms = round($metrics['average'] * 1000, 2);
            echo "  {$page}: {$avg_ms}ms (평균)\n";
            
            if ($avg_ms > 500) {
                echo "    ⚠️  개선 필요 (500ms 초과)\n";
            }
        }
        
        echo "\n💾 메모리 사용량:\n";
        $memory_mb = round($this->results['memory']['peak'] / 1024 / 1024, 2);
        echo "  최대 사용량: {$memory_mb}MB\n";
    }
    
    private function simulatePageRequest($page) {
        // 실제 프로젝트의 페이지 로직으로 대체
        usleep(rand(100000, 800000)); // 0.1~0.8초 시뮬레이션
    }
}

$benchmark = new ProjectBenchmark();
$benchmark->measureCurrentPerformance();
?>
```

**실습 과제**: 
- 위 코드를 실제 프로젝트에 맞게 수정
- 실제 성능 데이터 수집 및 분석
- 개선이 필요한 부분 3개 이상 식별

---

## ⚡ 2. OPcache 실무 최적화 실습

### 🎯 목표: 실제 서비스에 OPcache 적용하여 성능 개선 달성

#### 실습 2-1: OPcache 설정 최적화
```ini
# opcache-production.ini
; 프로덕션 환경 최적화 설정
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.revalidate_freq=0
opcache.validate_timestamps=0
opcache.fast_shutdown=1
opcache.enable_file_override=1
```

#### 실습 2-2: OPcache 효과 측정 도구
```php
<?php
// opcache-impact-analyzer.php
class OPcacheImpactAnalyzer {
    
    public function measureImpact() {
        echo "=== OPcache 영향 분석 ===\n\n";
        
        // OPcache 비활성화 상태 측정
        $this->disableOPcache();
        $without_opcache = $this->runPerformanceTest();
        
        // OPcache 활성화 상태 측정  
        $this->enableOPcache();
        $with_opcache = $this->runPerformanceTest();
        
        $this->compareResults($without_opcache, $with_opcache);
    }
    
    private function runPerformanceTest() {
        $iterations = 100;
        $start_time = microtime(true);
        $start_memory = memory_get_usage();
        
        for ($i = 0; $i < $iterations; $i++) {
            // 실제 비즈니스 로직 호출
            $this->executeBusinessLogic();
        }
        
        return [
            'execution_time' => microtime(true) - $start_time,
            'memory_used' => memory_get_peak_usage() - $start_memory,
            'iterations' => $iterations
        ];
    }
    
    private function executeBusinessLogic() {
        // 실제 프로젝트의 핵심 로직으로 대체
        $data = [];
        for ($i = 0; $i < 1000; $i++) {
            $data[] = "Processing item " . $i;
        }
        return array_filter($data, function($item) {
            return strpos($item, '5') !== false;
        });
    }
    
    private function compareResults($without, $with) {
        $time_improvement = (($without['execution_time'] - $with['execution_time']) / $without['execution_time']) * 100;
        $memory_improvement = (($without['memory_used'] - $with['memory_used']) / $without['memory_used']) * 100;
        
        echo "📈 성능 개선 결과:\n";
        echo "실행 시간: " . round($time_improvement, 1) . "% 개선\n";
        echo "메모리 사용: " . round($memory_improvement, 1) . "% 개선\n";
        
        if ($time_improvement > 50) {
            echo "✅ 뛰어난 개선 효과!\n";
        } elseif ($time_improvement > 20) {
            echo "✅ 좋은 개선 효과\n";
        } else {
            echo "⚠️  추가 최적화 필요\n";
        }
    }
}
?>
```

**실습 과제**:
- 개발/스테이징 환경에 OPcache 적용
- 실제 성능 개선율 측정 및 문서화
- 팀에 개선 결과 공유

---

## 🔍 3. 실무 코드 최적화 실습

### 🎯 목표: 실제 프로젝트 코드에서 성능 개선 달성

#### 실습 3-1: 데이터베이스 쿼리 최적화
```php
<?php
// database-optimization.php
class DatabaseOptimizer {
    
    // ❌ 비효율적인 방식
    public function getUsersInefficient() {
        $users = [];
        $user_ids = $this->getUserIds(); // 1번 쿼리
        
        foreach ($user_ids as $id) {
            $user = $this->getUserById($id); // N번 쿼리 (N+1 문제!)
            $user['profile'] = $this->getUserProfile($id); // N번 쿼리
            $users[] = $user;
        }
        
        return $users;
    }
    
    // ✅ 최적화된 방식
    public function getUsersOptimized() {
        // 단일 쿼리로 모든 데이터 조회
        $sql = "
            SELECT u.*, p.avatar, p.bio, p.created_at as profile_created
            FROM users u 
            LEFT JOIN user_profiles p ON u.id = p.user_id 
            WHERE u.active = 1
        ";
        
        return $this->query($sql);
    }
    
    // 성능 비교 테스트
    public function comparePerformance() {
        echo "=== 데이터베이스 쿼리 최적화 비교 ===\n";
        
        // 비효율적 방식 측정
        $start = microtime(true);
        $result1 = $this->getUsersInefficient();
        $time1 = microtime(true) - $start;
        
        // 최적화된 방식 측정
        $start = microtime(true);
        $result2 = $this->getUsersOptimized();
        $time2 = microtime(true) - $start;
        
        $improvement = (($time1 - $time2) / $time1) * 100;
        
        echo "비효율적 방식: " . round($time1 * 1000, 2) . "ms\n";
        echo "최적화된 방식: " . round($time2 * 1000, 2) . "ms\n";
        echo "개선율: " . round($improvement, 1) . "%\n";
    }
}
?>
```

#### 실습 3-2: 메모리 효율적인 파일 처리
```php
<?php
// file-processing-optimizer.php
class FileProcessingOptimizer {
    
    // ❌ 메모리 비효율적 (대용량 파일 시 메모리 부족)
    public function processLargeFileInefficient($filename) {
        $content = file_get_contents($filename); // 전체 파일을 메모리에!
        $lines = explode("\n", $content);
        
        $processed = [];
        foreach ($lines as $line) {
            if (strlen(trim($line)) > 0) {
                $processed[] = strtoupper($line);
            }
        }
        
        return $processed;
    }
    
    // ✅ 메모리 효율적 (스트림 처리)
    public function processLargeFileOptimized($filename) {
        $handle = fopen($filename, 'r');
        if (!$handle) throw new Exception("파일을 열 수 없습니다");
        
        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if (strlen($line) > 0) {
                yield strtoupper($line); // 제너레이터 사용
            }
        }
        
        fclose($handle);
    }
    
    // 실제 성능 테스트
    public function compareFileProcessing() {
        // 테스트 파일 생성 (1MB)
        $testFile = 'test_large_file.txt';
        $this->createTestFile($testFile, 50000); // 5만줄
        
        echo "=== 파일 처리 최적화 비교 ===\n";
        
        // 메모리 사용량 비교
        $initial_memory = memory_get_usage();
        
        // 비효율적 방식
        $start = microtime(true);
        $result1 = $this->processLargeFileInefficient($testFile);
        $time1 = microtime(true) - $start;
        $memory1 = memory_get_peak_usage() - $initial_memory;
        
        // 메모리 리셋
        unset($result1);
        gc_collect_cycles();
        
        // 효율적 방식 (제너레이터)
        $start = microtime(true);
        $count = 0;
        foreach ($this->processLargeFileOptimized($testFile) as $line) {
            $count++;
        }
        $time2 = microtime(true) - $start;
        $memory2 = memory_get_peak_usage() - $initial_memory;
        
        echo "비효율적 방식: " . round($time1 * 1000) . "ms, " . round($memory1/1024) . "KB\n";
        echo "효율적 방식: " . round($time2 * 1000) . "ms, " . round($memory2/1024) . "KB\n";
        echo "메모리 절약: " . round(($memory1-$memory2)/$memory1*100, 1) . "%\n";
        
        // 테스트 파일 정리
        unlink($testFile);
    }
    
    private function createTestFile($filename, $lines) {
        $handle = fopen($filename, 'w');
        for ($i = 0; $i < $lines; $i++) {
            fwrite($handle, "This is test line number $i with some random data\n");
        }
        fclose($handle);
    }
}
?>
```

**실습 과제**:
- 현재 프로젝트에서 비효율적인 코드 3개 발견
- 최적화 전후 성능 측정
- 개선 사항을 팀에 공유

---

## 📈 4. 실시간 모니터링 구축 실습

### 🎯 목표: 실제 서비스에 적용할 수 있는 모니터링 시스템 구축

#### 실습 4-1: 성능 모니터링 대시보드
```php
<?php
// performance-dashboard-real.php
class RealTimePerformanceDashboard {
    private $logFile = 'performance.log';
    
    public function startMonitoring() {
        register_shutdown_function([$this, 'recordMetrics']);
        register_tick_function([$this, 'checkMemoryUsage']);
    }
    
    public function recordMetrics() {
        $metrics = [
            'timestamp' => time(),
            'execution_time' => $this->getExecutionTime(),
            'memory_peak' => memory_get_peak_usage(true),
            'opcache_hit_rate' => $this->getOPcacheHitRate(),
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'CLI',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'CLI'
        ];
        
        $this->logMetrics($metrics);
        $this->checkAlerts($metrics);
    }
    
    private function getExecutionTime() {
        if (!defined('APP_START_TIME')) {
            define('APP_START_TIME', microtime(true));
        }
        return microtime(true) - APP_START_TIME;
    }
    
    private function getOPcacheHitRate() {
        if (!function_exists('opcache_get_status')) return null;
        
        $status = opcache_get_status();
        return $status['opcache_statistics']['opcache_hit_rate'] ?? 0;
    }
    
    private function checkAlerts($metrics) {
        $alerts = [];
        
        if ($metrics['execution_time'] > 2.0) {
            $alerts[] = "SLOW_REQUEST: {$metrics['execution_time']}s";
        }
        
        if ($metrics['memory_peak'] > 128 * 1024 * 1024) { // 128MB
            $alerts[] = "HIGH_MEMORY: " . round($metrics['memory_peak']/1024/1024) . "MB";
        }
        
        if ($metrics['opcache_hit_rate'] < 95) {
            $alerts[] = "LOW_OPCACHE_HIT: {$metrics['opcache_hit_rate']}%";
        }
        
        if (!empty($alerts)) {
            $this->sendAlert($alerts, $metrics);
        }
    }
    
    private function sendAlert($alerts, $metrics) {
        $message = "성능 알림: " . implode(', ', $alerts);
        $message .= " | URI: {$metrics['request_uri']}";
        
        // 실제로는 슬랙, 이메일 등으로 전송
        error_log($message);
        echo "🚨 " . $message . "\n";
    }
    
    public function generateDailyReport() {
        // 로그 파일에서 일일 통계 생성
        $logs = $this->readLogs();
        $stats = $this->calculateStats($logs);
        
        echo "=== 일일 성능 리포트 ===\n";
        echo "총 요청: {$stats['total_requests']}\n";
        echo "평균 응답시간: " . round($stats['avg_response_time'] * 1000) . "ms\n";
        echo "최대 메모리: " . round($stats['max_memory']/1024/1024) . "MB\n";
        echo "OPcache 평균 히트율: " . round($stats['avg_hit_rate'], 1) . "%\n";
        
        if ($stats['slow_requests'] > 0) {
            echo "⚠️  느린 요청: {$stats['slow_requests']}개\n";
        }
    }
}

// 애플리케이션 시작 시 자동 실행
$monitor = new RealTimePerformanceDashboard();
$monitor->startMonitoring();
?>
```

**실습 과제**:
- 개발 환경에 모니터링 도구 적용
- 1주일간 성능 데이터 수집
- 패턴 분석 및 개선점 도출

---

## 🎯 5. 실무 프로젝트 개선 챌린지

### 🎯 목표: 실제 프로젝트에서 측정 가능한 성능 개선 달성

#### 챌린지 5-1: 30일 성능 개선 프로젝트
```
주차별 목표:
1주차: 현재 성능 측정 및 문제점 파악
2주차: OPcache 최적화 및 코드 개선
3주차: 데이터베이스 및 캐싱 최적화  
4주차: 결과 측정 및 문서화
```

#### 성과 측정 기준
```php
<?php
// improvement-tracker.php
class ImprovementTracker {
    private $baseline = [];
    private $current = [];
    
    public function setBaseline($metrics) {
        $this->baseline = $metrics;
        $this->saveToFile('baseline.json', $metrics);
    }
    
    public function measureImprovement($current_metrics) {
        $this->current = $current_metrics;
        
        $improvements = [
            'response_time' => $this->calculateImprovement('response_time'),
            'memory_usage' => $this->calculateImprovement('memory_usage'),
            'opcache_hit_rate' => $this->calculateImprovement('opcache_hit_rate'),
            'database_queries' => $this->calculateImprovement('db_queries', 'lower_is_better')
        ];
        
        $this->generateImprovementReport($improvements);
        return $improvements;
    }
    
    private function calculateImprovement($metric, $type = 'lower_is_better') {
        if (!isset($this->baseline[$metric]) || !isset($this->current[$metric])) {
            return null;
        }
        
        $baseline = $this->baseline[$metric];
        $current = $this->current[$metric];
        
        if ($type === 'lower_is_better') {
            return (($baseline - $current) / $baseline) * 100;
        } else {
            return (($current - $baseline) / $baseline) * 100;
        }
    }
    
    private function generateImprovementReport($improvements) {
        echo "=== 성능 개선 결과 리포트 ===\n";
        
        foreach ($improvements as $metric => $improvement) {
            if ($improvement === null) continue;
            
            echo "{$metric}: ";
            if ($improvement > 0) {
                echo "✅ " . round($improvement, 1) . "% 개선\n";
            } else {
                echo "❌ " . round(abs($improvement), 1) . "% 악화\n";
            }
        }
        
        // 전체 성과 평가
        $positive_improvements = array_filter($improvements, function($val) {
            return $val !== null && $val > 0;
        });
        
        if (count($positive_improvements) >= 3) {
            echo "\n🎉 목표 달성! 대부분 지표가 개선되었습니다.\n";
        } elseif (count($positive_improvements) >= 2) {
            echo "\n👍 좋은 진전! 추가 최적화를 진행하세요.\n";
        } else {
            echo "\n⚠️  더 많은 최적화가 필요합니다.\n";
        }
    }
}
?>
```

---

## 📚 실습 완료 체크리스트

### ✅ 1주차 실무 실습
- [ ] 현재 프로젝트 성능 베이스라인 측정 완료
- [ ] 성능 문제점 3개 이상 식별
- [ ] 개선 우선순위 정의

### ✅ 2주차 실무 실습  
- [ ] OPcache 최적화 적용 및 효과 측정
- [ ] 코드 레벨 최적화 1개 이상 달성
- [ ] 실제 성능 개선률 문서화

### ✅ 3주차 실무 실습
- [ ] 데이터베이스 쿼리 최적화 완료
- [ ] 파일 처리 로직 개선
- [ ] 메모리 사용량 최적화

### ✅ 4주차 실무 실습
- [ ] 모니터링 시스템 구축 및 운영
- [ ] 전체 개선 결과 측정
- [ ] 팀 공유 및 피드백 수집

---

## 🎯 최종 목표 달성 지표

**정량적 성과**:
- 응답시간 30% 이상 개선
- 메모리 사용량 20% 이상 절약  
- OPcache 히트율 95% 이상 달성

**정성적 성과**:
- 팀 내 PHP 성능 전문가로 인정
- 코드 리뷰 시 성능 피드백 제공 능력
- 신규 프로젝트 아키텍처 설계 참여

이 실습들을 통해 단순한 이론 학습이 아닌, **실제 비즈니스 가치를 창출하는 실무 능력**을 개발할 수 있습니다! 🚀
