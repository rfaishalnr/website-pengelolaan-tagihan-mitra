<div align="center">

# Website Pengelolaan Tagihan Mitra Pelanggan Fiber Optic

Sistem informasi berbasis web untuk mengelola data, administrasi, dan monitoring tagihan mitra pelanggan fiber optic.

![Laravel](https://img.shields.io/badge/Laravel-12-red?style=flat-square&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2-blue?style=flat-square&logo=php)
![Filament](https://img.shields.io/badge/Filament-v3-orange?style=flat-square)
![MySQL](https://img.shields.io/badge/MySQL-Database-blue?style=flat-square&logo=mysql)

</div>

---

## Features

- Dashboard monitoring
- Manajemen tagihan mitra
- Manajemen data pelanggan
- Manajemen data mitra
- Role & Permission
- Export laporan
- Status monitoring

---

## Tech Stack

- Laravel
- PHP 8.2
- Filament
- Livewire
- MySQL
- Tailwind CSS
- HTML
- CSS
- JavaScript

---

## Installation

Clone repository

```bash
git clone https://github.com/username/repository.git
cd repository
```

Install dependency

```bash
composer install
```

Copy file environment

```bash
cp .env.example .env
```

Generate application key

```bash
php artisan key:generate
```

Configure database pada file `.env`, kemudian jalankan:

```bash
php artisan migrate
```

Buat symbolic link

```bash
php artisan storage:link
```

Jalankan aplikasi

```bash
php artisan serve
```

Akses melalui

```
http://127.0.0.1:8000
```
