
# .htaccess 완전 가이드

## ✅ .htaccess란?
Apache 웹 서버에서 디렉토리 단위로 설정을 적용할 수 있게 해주는 구성 파일입니다.  
웹 루트 혹은 특정 하위 디렉토리에 위치하며, 해당 디렉토리와 그 하위 디렉토리에만 영향을 미칩니다.

---

## ✅ .htaccess의 위치
- **웹 루트**: `/var/www/html/.htaccess`
- **특정 디렉토리**: `/var/www/html/uploads/.htaccess`

> `.htaccess`는 그 파일이 위치한 디렉토리 및 하위 디렉토리에만 적용됨.

---

## ✅ httpd.conf와의 관계

- `.htaccess`는 `httpd.conf`의 `<Directory>` 태그 내부에서 사용 가능한 지시어 일부만 쓸 수 있음.
- 다음과 같이 대응됨:

```
# httpd.conf 예시
<Directory "/var/www/html">
    Options -Indexes
</Directory>

# .htaccess (html 디렉토리에 위치한 경우)
Options -Indexes
```

- `.htaccess`에 `<Directory>` 태그를 직접 사용하면 안 됨.

---

## ✅ AllowOverride와의 관계

- `.htaccess`가 동작하려면 Apache 설정 파일에서 해당 디렉토리에 대해 `AllowOverride`가 허용돼야 함.

```
# httpd.conf
<Directory "/var/www/html">
    AllowOverride All
</Directory>
```

- `AllowOverride None`이면 `.htaccess`는 무시됨.

---

## ✅ .htaccess에서 자주 쓰이는 설정 예시

### 🔁 URL Rewrite (mod_rewrite)
```
RewriteEngine On
RewriteRule ^old-page\.html$ new-page.html [R=301,L]
```

### 🔐 기본 인증
```
AuthType Basic
AuthName "Restricted Area"
AuthUserFile /var/www/.htpasswd
Require valid-user
```

### ❌ 특정 파일 실행 차단 (.php 등)
```
<FilesMatch "\.(php|pl|py)$">
    Order Deny,Allow
    Deny from all
</FilesMatch>
```

### 📄 캐시 설정 (정적 파일)
```
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType text/css "access plus 1 week"
</IfModule>
```

---

## ✅ 하위 디렉토리에 .htaccess를 두는 실전 사례

### 1. public 디렉토리 (캐시 설정)
`/project/public/.htaccess`
```
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/png "access plus 30 days"
</IfModule>
```

### 2. admin 디렉토리 (인증)
`/project/admin/.htaccess`
```
AuthType Basic
AuthName "Admin Area"
AuthUserFile /var/www/.htpasswd
Require valid-user
```

### 3. uploads 디렉토리 (실행 차단)
`/project/uploads/.htaccess`
```
php_flag engine off
<FilesMatch "\.(php|pl|py|cgi)$">
    Order Deny,Allow
    Deny from all
</FilesMatch>
```

---

## ✅ 결론

| 질문 | 답변 |
|-------|-------|
| .htaccess는 필수인가? | ❌ 필수는 아님. 다만 디렉토리별 설정이 필요하거나 서버 설정을 못 건드릴 때 매우 유용 |
| httpd.conf 문법과 같나? | ✅ 문법은 같지만, 사용 가능한 지시어(컨텍스트)에 제한 있음 |
| 디렉토리별로 따로 둘 수 있나? | ✅ 자주 그리 함 (보안, 캐시, 인증 등 목적에 따라) |
