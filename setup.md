# 환경 설정
```
리눅스(Ubuntu 22.04 LTS)환경에서 실습을 진행하기 위해
도커를 활용
```

## 도커 명령어
#### 우분투 이미지 설치
```bash
# docker hub에서 ubutnu 22.04버전 설치
docker pull unbuntu:22.04
```

#### 컨테이너 실행
```bash
# -p: 포트 매핑 -v: 로컬 디렉토리를 컨테이너에 마운트하여 실행
docker run -dit --name php-engine-learning -p 80:80 -p 443:443 -v "${pwd}:/app" php-engine-configured
```

#### 컨테이너 접속
```bash
docker exec -it php-engine-learning /bin/bash
```

#### 컨테이너 재실행
```bash
docker start php-engine-learning
```

#### 컨테이너를 이미지로 커밋
```bash
docker commit php-engine-learning php-engine-configured
```


#### 필요한 도구 설치
```bash
# 패키지 저장소 업데이트
apt update

# Ubuntu/Debian
apt install apache2 openssl tcpdump wireshark-qt
# CentOS/RHEL
yum install httpd openssl tcpdump wireshark

# PHP, Apache PHP 모듈
apt install php libapache2-mod-php
```

#### Apache 설정
```bash
# 1. Apache 서비스 시작 및 활성화
apache2ctl start
systemctl enable apache2  # 부팅 시 자동 시작 설정은 systemctl 사용

# 2. PHP 모듈 활성화 확인 (보통 자동으로 활성화됨)
a2enmod php8.1  # PHP 버전에 따라 조정 필요 (php7.4, php8.0, php8.1 등)

# 3. DocumentRoot 디렉토리 설정 (/app으로 마운트된 디렉토리 사용)
# Apache 기본 사이트 설정 파일 편집
nano /etc/apache2/sites-available/000-default.conf

# 설정 파일에서 다음과 같이 변경:
# DocumentRoot /app
# ServerName localhost  # 또는 사용할 도메인명

# 또는 전역 ServerName 설정 (권장)
echo "ServerName localhost" >> /etc/apache2/apache2.conf

# 4. 디렉토리 권한 설정
chown -R www-data:www-data /app
chmod -R 755 /app

# 5. .htaccess 사용을 위한 설정 (필요시)
# /etc/apache2/apache2.conf에서 해당 디렉토리의 AllowOverride를 All로 변경
# <Directory /app>
#     Options Indexes FollowSymLinks
#     AllowOverride All
#     Require all granted
# </Directory>

# 6. Apache 설정 테스트
apache2ctl configtest

# 7. 설정 적용을 위한 재실행 옵션들:
apache2ctl restart   # 완전 재시작 (권장)
# apache2ctl reload    # 설정만 다시 로드 (더 빠름, 기본 설정 변경시)
# apache2ctl graceful  # 부드러운 재시작 (기존 연결 유지, 운영 환경 권장)

# 8. 상태 확인
apache2ctl status
systemctl status apache2

# 9. PHP 작동 테스트용 파일 생성
echo "<?php phpinfo(); ?>" > /app/info.php

# 10. 방화벽 설정 (필요시)
# ufw allow 80
# ufw allow 443
```

#### 추가 확인사항
```bash
# PHP 모듈이 제대로 로드되었는지 확인
apache2ctl -M | grep php

# 현재 VritualHost 설정 상태 확인
apache2ctl -S

# Apache 오류 로그 확인
tail -f /var/log/apache2/error.log

# 웹 브라우저에서 테스트
# http://localhost/info.php 접속하여 PHP 정보 페이지 확인
```

주요 설정 내용:

1. **DocumentRoot 설정**: `/app` 디렉토리로 설정 (도커 마운트된 디렉토리)
2. **PHP 모듈 등록**: `a2enmod` 명령어로 PHP 모듈 활성화
3. **추가 필수 설정들**:
   - 서비스 시작/활성화
   - 디렉토리 권한 설정 (`www-data` 사용자)
   - `.htaccess` 지원을 위한 `AllowOverride` 설정
   - 설정 테스트 및 재시작
   - PHP 작동 테스트 파일 생성

이 설정을 완료하면 브라우저에서 `http://localhost/info.php`로 접속하여 PHP가 정상적으로 작동하는지 확인할 수 있습니다!

## 개발 환경 관리
#### Dockerfile공유 / 이미지(레지스트리) 공유 전략 비교

| 관점 | Dockerfile 공유 | 이미지(레지스트리) 공유 |
|------|----------------|----------------------|
|**최초 세팅 속도** | 개발자가 로컬에서 ``docker build ..`` → 빌드, 다운로드까지 다소 시간 소요  | ``docker pull ..`` 한 번이면 레이어만 다운로드 → 보통 더 빠름|
|**네트워크 사용량** | 빌드할 때마다 패키지를 원격 저장소에서 새로 내려받을 수도 있음 | 레이어 캐시가 있으면 변경분만 가져오므로 총 트래픽 ↓|
|**스토리지**|로컬에 이미지 캐시 1개만 남음|풀 때마다 태그별로 이미지 저장 → 여러 태그 쓰면 용량 ↑|
|**재현성·투명성**|Dockerfile diff를 Git으로 리뷰 가능 → 무엇을 어떻게 설치했는지 명확| 이진 이미지라 “무엇이 들어있는지”는 docker history, SBOM 스캔 등을 추가로 해야 함|
|**보안·감사**|패키지 버전을 명시하면 누구나 재현·감사 가능 |“빌드 시점”의 패키지가 고정돼 있어 취약점 위치 파악이 쉬움 & 이미지 스캔 편리 |
|**빌드 시간**| 개발자 PC 성능·네트워크에 좌우 → 느릴 수 있음|CI에서 빌드·캐시 후 배포하므로 개발자는 기다릴 필요 ↓ |
|**업데이트 배포**| Dockerfile 수정 → 팀원 전원이 다시 빌드해야 적용| CI가 새 이미지 태그 푸시 → 팀원은 docker pull 만 하면 됨|
|**오프라인 개발**|오프라인이면 빌드 불가 |한번 풀어두면 오프라인에서도 사용 가능|

#### 실무 환경
1. Dockerfile은 Git에 필수로 넣어 버전 관리—“인프라 as 코드”.
2. CI(예: GitHub Actions, GitLab CI)가 빌드 → 레지스트리에 team/app-dev:2025-07-02 푸시
3. 개발자는 기본적으로 docker pull team/app-dev:latest 로 빠르게 받는다.
4. 이미지 크거나 네트워크 제한이 있는 경우엔 옵션으로 docker build(=Dockerfile 경로)도 허용.
5. 이미지 스캔(Trivy, Grype) → 취약점 알림 → Dockerfile 수정 → 새 이미지 태그 방식으로 보안 유지.

#### Apache 설정
```bash

```