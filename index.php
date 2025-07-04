<?php
echo "<p>서버 정보: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>PHP 버전: " . PHP_VERSION . "</p>";
echo "<p>현재 파일: " . $_SERVER['SCRIPT_FILENAME'] . "</p>";
echo "<p>현재 디렉토리: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>요청 URI: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>요청 메서드: " . $_SERVER['REQUEST_METHOD'] . "</p>";
echo "<p>요청 헤더: " . print_r($_SERVER, true) . "</p>";
echo "<p>환경 변수: " . print_r($_ENV, true) . "</p>";
echo "<p>서버 이름: " . $_SERVER['SERVER_NAME'] . "</p>";
echo "<p>서버 주소: " . $_SERVER['SERVER_ADDR'] . "</p>";
echo "<p>서버 포트: " . $_SERVER['SERVER_PORT'] . "</p>";
echo "<p>클라이언트 주소: " . $_SERVER['REMOTE_ADDR'] . "</p>";
echo "<p>클라이언트 포트: " . $_SERVER['REMOTE_PORT'] . "</p>";
echo "<p>클라이언트 브라우저: " . $_SERVER['HTTP_USER_AGENT'] . "</p>";
?>