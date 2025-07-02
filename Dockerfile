FROM ubuntu:22.04

# 비대화형 모드 설정
ENV DEBIAN_FRONTEND=noninteractive

# 패키지 업데이트
RUN apt-get update && apt-get upgrade -y

# 필요한 패키지 설치
RUN apt-get install -y apache2 openssl tcpdump wireshark-qt php libapache2-mod-php

WORKDIR /app
