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
# 로컬 디렉토리를 컨테이너에 마운트하여 실행
docker run -dit --name php-engine-learning -v "${pwd}:/app"
```

#### 컨테이너 접속
```bash
docker exec -it php-engine-learning /bin/bash
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