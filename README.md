<p align="center"><img src="https://res.cloudinary.com/dtfbvvkyp/image/upload/v1566331377/laravel-logolockup-cmyk-red.svg" width="400"></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>

## Config laravel project: vào thư mục của project<br/>
<br/>
composer install<br/>
cp .env.example .env<br/>

Tạo database trên mysql<br/><br/>

## Edit file .env<br/>
<br/>
DB_CONNECTION=mysql<br/>
DB_HOST=127.0.0.1<br/>
DB_PORT=(cổng của mysql, mặc định là 3306)<br/>
DB_DATABASE=(tên db)<br/>
DB_USERNAME=(tài khoản)<br/>
DB_PASSWORD=(mật khẩu)<br/>
<br/>

## Quay lại thư mục của project
php artisan key:generate<br/>
php artisan migrate<br/>


## Migrate lại database
php artisan migrate:refresh

## Config croppa
\vendor\bkwld\croppa\src\config\config.php<br/>
uploads -> picture_temp
