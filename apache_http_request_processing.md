# Apache HTTP 요청 처리 과정

## 📌 개요

Apache 웹서버가 커널로부터 **TCP payload**를 받은 후, 이를 HTTP 요청으로 파싱하고 적절한 응답을 생성하기까지의 전체 과정을 상세히 다룹니다.

> **전제 조건**: 커널이 TCP 헤더를 제거하고 payload만 Apache에 전달한 상태

---

## 🔄 전체 처리 흐름

```
[커널로부터 TCP Payload 수신]
           ↓
[1. HTTP 요청 파싱]
           ↓
[2. Virtual Host 선택]
           ↓
[3. Apache 모듈 처리 체인]
           ↓
[4. 핸들러 선택 및 실행]
           ↓
[5. 응답 생성 및 전송]
```

---

## 1. HTTP 요청 파싱 단계

### 🔍 바이트스트림 → HTTP 프로토콜 해석

Apache가 커널로부터 받은 바이너리 데이터를 HTTP 프로토콜 규격에 따라 해석합니다.

#### ▶ 파싱 과정

```
바이트스트림: "GET /index.php HTTP/1.1\r\nHost: localhost\r\n\r\n"
           ↓
1. 요청 라인 분석: GET /index.php HTTP/1.1
2. 헤더 파싱: Host: localhost
3. 본문 분석: (GET 요청의 경우 본문 없음)
```

#### ▶ 파싱 결과

| 구분 | 내용 | 설명 |
|------|------|------|
| **HTTP 메서드** | GET | 요청 유형 |
| **요청 URI** | /index.php | 요청된 리소스 경로 |
| **HTTP 버전** | HTTP/1.1 | 프로토콜 버전 |
| **Host 헤더** | localhost | 요청 대상 호스트 |

#### ⚠️ 주의사항

- **ASCII 외 데이터 처리**: HTTP 헤더는 ASCII이지만, POST 본문은 바이너리 데이터일 수 있음
- **인코딩 처리**: Content-Type 헤더를 통해 데이터 인코딩 방식 결정

---

## 2. Virtual Host 선택 단계

### 🎯 Host 헤더 기반 내부 라우팅

Apache는 클라이언트의 요청을 **외부 도메인으로 전달하는 것이 아니라**, 내부에서 적절한 **Virtual Host 설정**을 선택합니다.

#### ▶ 선택 과정

```
HTTP 요청의 Host 헤더 확인
           ↓
Apache 설정의 VirtualHost 블록들과 매칭
           ↓
매칭되는 VirtualHost 설정 적용
           ↓
해당 DocumentRoot 및 설정 사용
```

#### ▶ Virtual Host 설정 예시

```apache
# /etc/apache2/sites-available/000-default.conf
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /app
    
    <Directory /app>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### ▶ 결정되는 요소들

| 설정 항목 | 설명 | 예시 |
|-----------|------|------|
| **DocumentRoot** | 웹 문서 루트 디렉토리 | /app |
| **ServerName** | 서버 이름 | localhost |
| **Directory 권한** | 파일 접근 권한 | AllowOverride All |

---

## 3. Apache 모듈 처리 체인

### 🔗 순차적 모듈 처리

Apache는 로드된 모듈들을 순차적으로 거치며 요청을 처리합니다.

#### ▶ 주요 모듈 처리 순서

```
1. mod_rewrite    → URL 재작성
2. mod_authz_core → 인증/인가 처리
3. mod_mime       → MIME 타입 결정
4. mod_dir        → 디렉토리 인덱스 처리
5. mod_php        → PHP 핸들러 결정
```

#### ▶ 각 모듈의 역할

| 모듈 | 역할 | 처리 내용 |
|------|------|-----------|
| **mod_rewrite** | URL 재작성 | `/app/index.php` → `/app/index.php` |
| **mod_authz_core** | 접근 제어 | 파일 접근 권한 확인 |
| **mod_mime** | MIME 타입 | `.php` → `application/x-httpd-php` |
| **mod_php** | PHP 처리 | PHP 핸들러 연결 |

#### ▶ 실제 처리 흐름

```bash
# 요청: GET /index.php HTTP/1.1
# Host: localhost

1. mod_rewrite 처리:
   - 입력: /index.php
   - 출력: /index.php (변경 없음)

2. mod_authz_core 처리:
   - 파일 존재 확인: /app/index.php
   - 권한 확인: 읽기 권한 있음

3. mod_mime 처리:
   - 파일 확장자: .php
   - MIME 타입: application/x-httpd-php

4. 핸들러 결정:
   - 핸들러: php-script
   - 처리 모듈: mod_php
```

---

## 4. 핸들러 선택 및 실행 단계

### 🎯 PHP SAPI 호출

Apache가 `.php` 파일을 처리하기 위해 적절한 PHP 핸들러를 선택하고 실행합니다.

#### ▶ 핸들러 선택 과정

```
파일 확장자 확인 (.php)
           ↓
MIME 타입 결정 (application/x-httpd-php)
           ↓
핸들러 매핑 (php-script)
           ↓
PHP SAPI 호출
```

#### ▶ PHP SAPI 유형

| SAPI 유형 | 설명 | 장점 | 단점 |
|-----------|------|------|------|
| **mod_php** | Apache 모듈 | 빠른 응답 속도 | 높은 메모리 사용량 |
| **PHP-FPM** | FastCGI 프로세스 | 독립적 관리 | 소켓 통신 오버헤드 |
| **CGI** | 전통적 CGI | 간단한 구조 | 프로세스 생성 오버헤드 |

#### ▶ mod_php 실행 흐름

```
Apache 프로세스 내에서 PHP 엔진 초기화
           ↓
PHP 스크립트 파일 로드
           ↓
Zend Engine에 의한 컴파일
           ↓
바이트코드 실행
           ↓
출력 버퍼 수집
```

---

## 5. 응답 생성 및 전송 단계

### 📤 HTTP 응답 생성

PHP 스크립트 실행 결과를 HTTP 응답으로 변환하여 클라이언트에 전송합니다.

#### ▶ 응답 생성 과정

```
PHP 스크립트 출력 수집
           ↓
HTTP 응답 헤더 생성
           ↓
HTTP 응답 본문 구성
           ↓
커널을 통한 클라이언트 전송
```

#### ▶ HTTP 응답 구조

```http
HTTP/1.1 200 OK
Content-Type: text/html; charset=UTF-8
Content-Length: 1024
Connection: keep-alive

<!DOCTYPE html>
<html>
<head>
    <title>PHP 페이지</title>
</head>
<body>
    <h1>Hello, World!</h1>
</body>
</html>
```

---

## 📊 성능 관점에서의 분석

### ⏱️ 각 단계별 소요 시간

| 단계 | 일반적 소요 시간 | 주요 영향 요인 |
|------|------------------|----------------|
| **HTTP 파싱** | 0.1ms 미만 | 요청 크기, 헤더 수 |
| **Virtual Host 선택** | 0.05ms 미만 | VirtualHost 수 |
| **모듈 처리** | 0.1-1ms | 활성 모듈 수 |
| **PHP 실행** | 1-100ms | 스크립트 복잡도 |
| **응답 전송** | 0.1-10ms | 응답 크기, 네트워크 |

### 🔧 최적화 포인트

1. **Apache 모듈 최적화**: 불필요한 모듈 비활성화
2. **PHP 캐싱**: OPcache 활성화
3. **Keep-Alive 설정**: 연결 재사용
4. **압축 설정**: gzip 압축 활성화

---

## 📋 실제 동작 확인 방법

### 🔍 Apache 로그 확인

```bash
# 접근 로그 실시간 모니터링
tail -f /var/log/apache2/access.log

# 에러 로그 확인
tail -f /var/log/apache2/error.log
```

### 🔧 PHP 스크립트를 통한 정보 확인

```php
<?php
// 현재 처리 정보 출력
echo "SAPI: " . php_sapi_name() . "\n";
echo "서버 소프트웨어: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "요청 URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "스크립트 경로: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
?>
```

---

## ✅ 핵심 정리

### 🎯 중요 포인트

1. **바이트스트림 파싱**: 커널로부터 받은 TCP payload를 HTTP 프로토콜로 해석
2. **내부 라우팅**: 외부 전달이 아닌 Apache 내부 Virtual Host 선택
3. **모듈 체인**: 순차적 모듈 처리를 통한 요청 가공
4. **핸들러 실행**: PHP SAPI를 통한 스크립트 실행
5. **응답 생성**: HTTP 응답 형태로 결과 전송

### 🔄 전체 흐름 요약

```
TCP Payload → HTTP 파싱 → VirtualHost 선택 → 모듈 처리 → PHP 실행 → HTTP 응답
```

> **핵심**: Apache는 **단순한 전달자가 아닌**, HTTP 프로토콜을 완전히 이해하고 처리하는 **애플리케이션 서버**입니다.

---

## 📚 참고 자료

- [Apache HTTP Server Documentation](https://httpd.apache.org/docs/)
- [PHP SAPI Documentation](https://www.php.net/manual/en/install.php)
- 프로젝트 내 관련 파일:
  - `kernel_to_webserver_data_flow.md`
  - `week_1/apache-flow-tracker.php`
  - `week_1/network-analyzer.php`
