<?php
// apache-flow-tracker.php

/**
 * ApacheFlowTracker 클래스
 * Apache 웹 서버가 HTTP 요청을 받아 PHP 스크립트를 실행하는 과정을 추적하고 분석합니다.
 * 이 트래커는 PHP 스크립트가 실행되기 시작한 시점부터의 내부 과정을 측정합니다.
 * Apache의 TCP Handshake, 모듈 로딩 등 PHP 실행 '이전'의 순수한 Apache 시간은 직접 측정할 수 없습니다.
 */
class ApacheFlowTracker {
    private $stages = []; // 각 처리 단계의 시작 시간을 기록할 배열

    /**
     * Apache 처리 흐름 추적을 시작하는 메인 메서드
     * 이 메서드는 클라이언트 요청이 Apache를 거쳐 이 PHP 스크립트에 도달했을 때 실행됩니다.
     */
    public function trackApacheFlow() {
        // 'request_received' 시점 기록: PHP 스크립트가 Apache에 의해 실행되기 시작하는 첫 순간
        // 엄밀히 말해 Apache가 요청을 받고 PHP 핸들러에 제어를 넘긴 직후입니다.
        $this->stages['request_received'] = microtime(true);

        echo "=== Apache 처리 과정 추적 ===<br>";

        // 1. 요청 수신 단계 분석
        // Apache는 클라이언트로부터 HTTP 요청을 받아 요청 라인(GET /index.php HTTP/1.1),
        // 요청 헤더(Host, User-Agent 등) 등을 파싱합니다.
        $this->analyzeRequestReception();

        // 2. 가상 호스트 선택 단계 분석
        // Apache는 요청된 'Host' 헤더와 IP 주소 등을 기반으로 어떤 'VirtualHost' 설정 블록을
        // 사용하여 요청을 처리할지 결정합니다. 이는 동일한 서버에서 여러 도메인을 호스팅할 때 중요합니다.
        $this->analyzeVirtualHost();

        // 3. Apache 모듈 처리 체인 분석
        // Apache는 로드된 다양한 모듈(mod_rewrite, mod_authz_core 등)을 순차적으로 거치며 요청을 처리합니다.
        // 이 과정에서 URL 재작성, 인증/인가, MIME 타입 결정 등이 이루어집니다.
        // 최종적으로 요청된 파일(`.php` 파일)의 핸들러(PHP SAPI)가 결정됩니다.
        $this->analyzeModuleChain();

        // 4. PHP 핸들러 호출 단계 분석
        // Apache가 요청을 PHP SAPI(Server Application Programming Interface) 모듈(예: mod_php, FPM)에 전달합니다.
        // 이 시점부터 PHP 인터프리터가 PHP 스크립트 코드를 실행하기 시작합니다.
        $this->stages['php_handler_called'] = microtime(true); // PHP 스크립트 코드의 실질적인 시작점
        $this->analyzePHPHandlerCall();

        // 최종 처리 시간 보고서 생성
        $this->generateTimingReport();
    }

    /**
     * 요청 수신 단계의 정보를 분석하고 출력합니다.
     * 이 정보들은 Apache가 클라이언트로부터 받은 HTTP 요청의 기본 정보들입니다.
     */
    private function analyzeRequestReception() {
        echo "1. 요청 수신:<br>";
        // $_SERVER['SERVER_SOFTWARE']: Apache 웹 서버의 버전 정보 (예: Apache/2.4.58 (Ubuntu))
        echo "   웹서버: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
        // $_SERVER['SERVER_PROTOCOL']: 클라이언트가 사용한 HTTP 프로토콜 버전 (예: HTTP/1.1)
        echo "   프로토콜: " . $_SERVER['SERVER_PROTOCOL'] . "<br>";
        // $_SERVER['HTTP_CONNECTION']: 클라이언트와 서버 간의 연결 방식 (예: keep-alive)
        // HTTP/1.1에서 기본적으로 연결을 유지하는지 여부를 나타냅니다.
        echo "   Connection: " . ($_SERVER['HTTP_CONNECTION'] ?? 'N/A') . "<br><br>";
    }

    /**
     * 가상 호스트 선택 단계의 정보를 분석하고 출력합니다.
     * 이 정보들은 Apache가 어떤 VirtualHost 설정을 통해 현재 요청을 처리하는지 보여줍니다.
     */
    private function analyzeVirtualHost() {
        echo "2. 가상 호스트 선택:<br>";
        // $_SERVER['SERVER_NAME']: Apache VirtualHost 설정의 ServerName 지시자에 설정된 값.
        // 클라이언트가 요청한 호스트명과 매칭되어 해당 VirtualHost가 선택됩니다.
        echo "   서버명: " . $_SERVER['SERVER_NAME'] . "<br>";
        // $_SERVER['DOCUMENT_ROOT']: Apache VirtualHost 또는 기본 설정의 DocumentRoot 지시자에 설정된 값.
        // 웹 서버가 웹 문서의 루트 디렉토리로 사용하는 경로입니다.
        echo "   DocumentRoot: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
        // $_SERVER['SCRIPT_FILENAME']: Apache가 실행할 현재 PHP 스크립트의 파일 시스템 상의 전체 경로.
        // DocumentRoot와 요청된 URI를 기반으로 결정됩니다.
        echo "   스크립트 경로: " . $_SERVER['SCRIPT_FILENAME'] . "<br><br>";
    }

    /**
     * Apache 모듈 처리 체인 정보를 간접적으로 분석하고 출력합니다.
     * 이 단계에서 Apache는 요청된 파일의 종류(MIME 타입)를 결정하고,
     * 해당 파일을 처리할 적절한 핸들러를 찾습니다.
     */
    private function analyzeModuleChain() {
        echo "3. Apache 모듈 체인:<br>";
        // $_SERVER['CONTENT_TYPE']: 클라이언트가 POST 요청 등으로 보낸 데이터의 MIME 타입 (예: application/x-www-form-urlencoded).
        // GET 요청의 경우 이 값은 없을 수 있습니다.
        echo "   Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'N/A') . "<br>";
        // function_exists('apache_get_modules'): Apache 모듈로 PHP가 로드된 경우(mod_php)에만 이 함수가 존재합니다.
        // FastCGI (PHP-FPM) 등으로 PHP가 실행될 때는 이 함수가 존재하지 않습니다.
        echo "   Handler: " . (function_exists('apache_get_modules') ? 'PHP Handler (mod_php)' : 'PHP Handler (FPM/CLI or other SAPI)') . "<br><br>";
    }

    /**
     * PHP 핸들러 호출 단계의 정보를 분석하고 출력합니다.
     * 이 정보들은 PHP 인터프리터 자체의 실행 환경에 대한 내용입니다.
     */
    private function analyzePHPHandlerCall() {
        echo "4. PHP 핸들러 호출:<br>";
        // php_sapi_name(): 현재 PHP가 어떤 Server API(SAPI)로 실행되고 있는지 반환합니다.
        // 예: 'apache2handler' (mod_php), 'fpm-fcgi' (PHP-FPM), 'cli' (명령줄 인터페이스)
        echo "   SAPI: " . php_sapi_name() . "<br>";
        // PHP_VERSION: 현재 실행 중인 PHP의 버전 문자열.
        echo "   PHP 버전: " . PHP_VERSION . "<br>";
        // ini_get('memory_limit'): PHP 스크립트가 사용할 수 있는 최대 메모리 양을 설정 (php.ini).
        // 이 제한을 초과하면 스크립트가 오류와 함께 중단됩니다.
        echo "   메모리 한계: " . ini_get('memory_limit') . "<br><br>";
    }

    /**
     * 각 단계별 처리 시간을 분석하고 보고서를 생성합니다.
     * 여기서 계산되는 'Apache 처리 시간'은 PHP 스크립트가 로딩되고 초기화되는 시간을 대략적으로 추정한 것입니다.
     * Apache가 네트워크 요청을 받고 PHP에 넘겨주기까지의 순수한 시간은 PHP 스크립트 내에서 직접 측정 불가능합니다.
     */
    private function generateTimingReport() {
        $total_time = microtime(true) - $this->stages['request_received'];
        $php_execution_time = microtime(true) - $this->stages['php_handler_called'];

        echo "=== 처리 시간 분석 ===<br>";
        // 총 처리 시간: PHP 스크립트가 Apache에 의해 실행 시작된 시점부터 이 스크립트가 완료될 때까지의 총 시간.
        echo "총 처리 시간 (PHP 스크립트 시작부터 완료까지): " . number_format($total_time * 1000, 2) . "ms<br>";
        // PHP 처리 시간: 'php_handler_called' 시점부터 스크립트 완료까지의 시간.
        // 이는 이 PHP 스크립트의 실질적인 로직 실행 시간을 나타냅니다.
        echo "PHP 스크립트 자체 실행 시간: " . number_format($php_execution_time * 1000, 2) . "ms<br>";
        // Apache 처리 시간 (추정): 'request_received' 시점부터 'php_handler_called' 시점까지의 시간.
        // PHP 스크립트가 로딩되고 PHP 환경이 초기화되는 데 걸리는 아주 짧은 시간으로 해석할 수 있습니다.
        // 이는 Apache가 요청을 받아 PHP에 전달하기 전의 모든 시간을 포함하지 않습니다.
        echo "PHP 스크립트 로딩 및 초기화 시간 (Apache 추정): " . number_format(($total_time - $php_execution_time) * 1000, 2) . "ms<br>";
    }
}

// ApacheFlowTracker 인스턴스 생성 및 추적 시작
$tracker = new ApacheFlowTracker();
$tracker->trackApacheFlow();
?>