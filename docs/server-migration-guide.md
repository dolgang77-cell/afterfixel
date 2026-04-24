# 서버 이전 가이드 (CentOS 7 + Apache + MariaDB)

## 호환성 점검 결과

### 핵심 요약

| 항목 | 현재 서버 | 대상 서버 | 호환성 | 조치 |
|------|----------|----------|--------|------|
| **OS** | Ubuntu 24.04 | CentOS 7 | **EOL** | 운영 리스크 인지 필요 |
| **웹서버** | nginx 1.24 | Apache 2.4.6 | 호환 | .htaccess + mod_rewrite 필요 |
| **PHP** | 8.3.6 | 8.2.20 | **불가** | **PHP 8.3 설치 필수** |
| **DB** | MySQL 8.0 | MariaDB 10.11 | 호환 | 인증 방식 조정 필요 |
| **Laravel** | 13.4.0 | - | PHP ^8.3 필수 | PHP 8.2 불가 |

### 결론
> **PHP 8.2에서는 Laravel 13이 동작하지 않습니다.**
> 새 서버에 반드시 **PHP 8.3 이상**을 설치해야 합니다.
> CentOS 7에서는 Remi 레포를 통해 PHP 8.3 설치가 가능합니다.

---

## 1단계: 새 서버 환경 준비

### PHP 8.3 설치 (Remi 레포)

```bash
# EPEL + Remi 레포 설치
yum install -y epel-release
yum install -y https://rpms.remirepo.net/enterprise/remi-release-7.rpm
yum install -y yum-utils

# PHP 8.3 활성화
yum-config-manager --disable 'remi-php*'
yum-config-manager --enable remi-php83

# PHP 8.3 + 필수 확장 설치
yum install -y \
    php \
    php-fpm \
    php-mysqlnd \
    php-pdo \
    php-gd \
    php-mbstring \
    php-xml \
    php-curl \
    php-zip \
    php-intl \
    php-exif \
    php-opcache \
    php-bcmath \
    php-json \
    php-fileinfo \
    php-openssl

# 설치 확인
php -v   # 8.3.x 확인
php -m   # 확장 확인
```

### 필수 PHP 확장 목록

| 확장 | 용도 | 필수 |
|------|------|------|
| pdo_mysql | DB 연결 | 필수 |
| gd | 이미지 최적화 (ImageOptimizer) | 필수 |
| exif | EXIF 방향 보정 | 필수 |
| mbstring | 다국어 문자열 처리 | 필수 |
| curl | 자동번역 API 호출 | 필수 |
| xml | Laravel 내부 | 필수 |
| zip | Composer | 필수 |
| intl | 다국어 | 필수 |
| fileinfo | 파일 업로드 검증 | 필수 |
| opcache | 성능 (JIT 포함) | 권장 |
| bcmath | 수학 연산 | 권장 |

### Composer 설치

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

---

## 2단계: Apache 설정

### VirtualHost 설정

```apache
# /etc/httpd/conf.d/nightlife.conf

<VirtualHost *:80>
    ServerName nightlife.ive.co.kr
    DocumentRoot /var/www/nightlife/public

    <Directory /var/www/nightlife/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>

    # 업로드 크기 제한
    LimitRequestBody 20971520

    # 로그
    ErrorLog /var/log/httpd/nightlife-error.log
    CustomLog /var/log/httpd/nightlife-access.log combined
</VirtualHost>
```

### HTTPS (mod_ssl)

```apache
<VirtualHost *:443>
    ServerName nightlife.ive.co.kr
    DocumentRoot /var/www/nightlife/public

    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/nightlife.crt
    SSLCertificateKeyFile /etc/ssl/private/nightlife.key

    <Directory /var/www/nightlife/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>

    LimitRequestBody 20971520
</VirtualHost>
```

### 필수 Apache 모듈

```bash
# 확인
httpd -M | grep -E "rewrite|ssl|headers"

# 없으면 설치/활성화
yum install -y mod_ssl
# mod_rewrite는 Apache 기본 포함
```

### .htaccess (이미 존재)

`public/.htaccess`는 Laravel 기본 제공. Apache에서 `AllowOverride All` 필수.

---

## 3단계: MariaDB 설정

### DB 생성

```sql
CREATE DATABASE nightlife_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'nightlife_user'@'localhost' IDENTIFIED BY 'YOUR_PASSWORD';
GRANT ALL PRIVILEGES ON nightlife_db.* TO 'nightlife_user'@'localhost';
FLUSH PRIVILEGES;
```

### 호환성 주의사항

| 항목 | MySQL 8.0 | MariaDB 10.11 | 조치 |
|------|-----------|---------------|------|
| 인증 | caching_sha2_password | mysql_native_password | MariaDB 기본이므로 OK |
| JSON | 네이티브 | LONGTEXT alias | 호환 (Laravel ORM 정상) |
| utf8mb4 | 지원 | 지원 | OK |
| strict mode | 기본 ON | 기본 OFF | `.env`에서 제어 |

### MariaDB strict mode

```ini
# /etc/my.cnf.d/server.cnf
[mysqld]
sql_mode = STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION
```

---

## 4단계: 프로젝트 배포

### 파일 복사

```bash
# 서버에 프로젝트 복사 (git clone 또는 rsync)
rsync -avz --exclude=vendor --exclude=node_modules --exclude=storage/logs/*.log \
    /var/www/nightlife/ user@new-server:/var/www/nightlife/

# 또는 tar로 패키징
cd /var/www
tar czf nightlife.tar.gz --exclude=vendor --exclude=node_modules nightlife/
scp nightlife.tar.gz user@new-server:/var/www/
```

### 의존성 설치

```bash
cd /var/www/nightlife
composer install --no-dev --optimize-autoloader
```

### 환경 설정

```bash
# .env 수정
cp .env.example .env  # 또는 기존 .env 복사 후 수정
php artisan key:generate

# .env에서 수정할 항목:
# APP_URL=https://nightlife.ive.co.kr
# DB_HOST=127.0.0.1
# DB_DATABASE=nightlife_db
# DB_USERNAME=nightlife_user
# DB_PASSWORD=YOUR_PASSWORD
# SESSION_DRIVER=file
```

### 권한 설정

```bash
chown -R apache:apache /var/www/nightlife/storage
chown -R apache:apache /var/www/nightlife/bootstrap/cache
chmod -R 775 /var/www/nightlife/storage
chmod -R 775 /var/www/nightlife/bootstrap/cache

# storage 심볼릭 링크
php artisan storage:link

# 업로드 디렉토리 권한
chown -R apache:apache /var/www/nightlife/storage/app/public
chmod -R 775 /var/www/nightlife/storage/app/public
```

**주의**: CentOS + Apache는 `apache` 사용자, Ubuntu + nginx는 `www-data` 사용자.

### DB 마이그레이션

```bash
php artisan migrate --force
```

### 캐시 생성

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 5단계: PHP 설정

### php.ini

```ini
# /etc/php.ini 수정
upload_max_filesize = 10M
post_max_size = 20M
memory_limit = 256M
max_execution_time = 60

# OPcache + JIT
opcache.enable = 1
opcache.jit = 1255
opcache.jit_buffer_size = 64M
opcache.memory_consumption = 128
opcache.max_accelerated_files = 10000

# 시간대
date.timezone = UTC
```

---

## 6단계: 크론/스케줄러 설정

```bash
# crontab -e (apache 사용자)
* * * * * cd /var/www/nightlife && php artisan schedule:run >> /dev/null 2>&1
```

등록된 스케줄:
- `nite:send-party-reminders` — 매 10분
- `nite:send-tonight` — 금/토 18:00
- `nite:send-scheduled-push` — 매분

---

## 7단계: 검증

```bash
# 1. 사이트 접속
curl -s -o /dev/null -w "%{http_code}" http://localhost/
# 200이면 성공

# 2. DB 연결
php artisan tinker --execute="echo DB::connection()->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION);"

# 3. 이미지 업로드 테스트
php artisan tinker --execute="
\$img = imagecreatetruecolor(100,100);
imagejpeg(\$img, '/tmp/test.jpg');
\$file = new Illuminate\Http\UploadedFile('/tmp/test.jpg','test.jpg','image/jpeg',null,true);
\$r = App\Services\ImageOptimizer::process(\$file, 'uploads');
echo \$r['url'];
"

# 4. 스케줄러
php artisan schedule:list
```

---

## CentOS 7 EOL 리스크

| 항목 | 상태 |
|------|------|
| 공식 지원 종료 | **2024-06-30** (이미 종료) |
| 보안 업데이트 | **없음** |
| PHP 8.3 공식 지원 | Remi 레포 통해 가능 (비공식) |
| glibc 버전 | 2.17 (매우 오래됨) |
| OpenSSL | 1.0.2 (보안 취약) |

### 권장사항
- **장기 운영 시 Rocky Linux 8/9 또는 AlmaLinux 9로 업그레이드 권장**
- CentOS 7은 임시 운영 목적으로만 사용
- 방화벽/SELinux로 보안 강화 필수
- 외부 접근 최소화

---

## 체크리스트

- [ ] PHP 8.3 설치 확인 (`php -v`)
- [ ] 필수 확장 설치 확인 (`php -m`)
- [ ] Apache mod_rewrite 활성화
- [ ] VirtualHost 설정
- [ ] SSL 인증서 설치
- [ ] DB 생성 + 사용자 권한
- [ ] 프로젝트 파일 복사
- [ ] `composer install`
- [ ] `.env` 설정
- [ ] 권한 설정 (apache:apache)
- [ ] `php artisan storage:link`
- [ ] `php artisan migrate`
- [ ] 캐시 생성
- [ ] 크론 설정
- [ ] 이미지 업로드 테스트
- [ ] 전체 페이지 접속 테스트
