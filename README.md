# 🖼️ Product Gallery Manager

**Technical Assessment — RazinSoft Limited**
**Candidate:** Mozammel Haq
**Role:** Junior Web Developer
**Deadline:** 30 March 2026

---

## Overview

A full-featured Laravel 12 product gallery management application that allows an admin to create, view, edit, and delete products — each with multiple image uploads. Built with clean architecture, Eloquent ORM relationships, Blade templating, and a premium custom dark UI design system.

---

## ✅ Requirements Checklist

| Requirement                                             | Status |
| ------------------------------------------------------- | ------ |
| Laravel 12                                              | ✅     |
| Add product (name, description, multiple images)        | ✅     |
| View product with images                                | ✅     |
| Edit product + add/remove images                        | ✅     |
| Delete product + all images from DB & storage           | ✅     |
| Eloquent relationships (Product → hasMany ProductImage) | ✅     |
| Images stored in `storage/app/public/products`          | ✅     |
| Laravel Storage facade for all file handling            | ✅     |
| Validation (required fields, JPEG/PNG/WebP, max 2MB)    | ✅     |
| Blade templating (no frontend framework)                | ✅     |
| Migrations                                              | ✅     |
| Model                                                   | ✅     |
| CSRF protection on all forms                            | ✅     |

### 🔥 Bonus Features

| Bonus                                             | Status |
| ------------------------------------------------- | ------ |
| Drag-and-drop image upload (JavaScript)           | ✅     |
| AJAX image removal (existing images on edit page) | ✅     |
| Responsive custom UI (Tailwind CSS)               | ✅     |

---

## 🚀 Installation & Setup

### Prerequisites

- PHP 8.2+
- Composer
- SQLite (default) or MySQL/PostgreSQL

### Step-by-step

```bash
# 1. Clone the repository
git clone https://github.com/Mozammel-Haq/Razinsoft-Task.git
cd Razinsoft-task

# 2. Install PHP dependencies
composer install

# 3. Copy environment file and configure
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Run database migrations
php artisan migrate

# 7. Create the storage symlink (serves uploaded images)
php artisan storage:link

# 8. Start the development server
php artisan serve
```

Open your browser at **http://localhost:8000**

---

## 🗂️ Project Structure

```
product-gallery-manager/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── ProductController.php   # Full CRUD + AJAX endpoints
│   └── Models/
│       ├── Product.php                 # HasMany images
│       └── ProductImage.php            # BelongsTo product, auto-deletes file
│
├── database/
│   ├── migrations/
│   │   ├── ..._create_products_table.php
│   │   └── ..._create_product_images_table.php
│
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php           # Master layout with nav, toast, footer
│       └── products/
│           ├── index.blade.php         # Product grid with stats
│           ├── create.blade.php        # Create form with drop zone
│           ├── edit.blade.php          # Edit form with AJAX image removal
│           └── show.blade.php          # Detail view with gallery lightbox
│
├── routes/
│   └── web.php                         # Resource routes + AJAX image routes
│
└── storage/
    └── app/
        └── public/
            └── products/               # Uploaded images stored here
```

---

## 🏗️ Architecture & Design Decisions

### Eloquent Relationships

```php
// Product hasMany ProductImage
class Product extends Model
{
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }
}

// ProductImage belongsTo Product
class ProductImage extends Model
{
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
```

### Automatic File Cleanup

The `ProductImage` model uses a `boot()` hook to delete the physical file from storage whenever the model record is deleted — ensuring no orphaned files remain:

```php
protected static function boot(): void
{
    parent::boot();
    static::deleting(function (ProductImage $image) {
        Storage::disk('public')->delete('products/' . $image->filename);
    });
}
```

### File Storage

All uploads go to `storage/app/public/products/` and are served via the `storage:link` symlink at `public/storage/products/`. Filenames are UUID-generated to prevent collisions and enumeration attacks.

### Validation Rules

```php
'images.*' => 'required|file|mimes:jpeg,jpg,png,webp|max:2048'
```

- Allowed types: JPEG, JPG, PNG, WebP
- Maximum size: 2MB per file
- At least 1 image required on create

### CSRF Protection

All forms include `@csrf`. The AJAX DELETE endpoint for image removal reads the CSRF token from the `<meta name="csrf-token">` tag and passes it as `X-CSRF-TOKEN` header.

---

## 🎨 UI Design System

Built with Tailwind CSS (via Vite) + minimal custom utilities — no Bootstrap, no heavy frameworks.

Design Language

Primary color: Indigo-600 (#4f46e5) for actions, highlights, active states
Backgrounds: White (#ffffff) cards on light gray-50 (#f9fafb) page background
Text: Gray-900 headings, gray-600 body, gray-400/500 for metadata

Feedback:
✅ Success: Emerald-500 (#10b981)
❌ Error: Red-500 (#ef4444)
⚠️ Warning: Amber-500 (#f59e0b)

Typography:
Body: Inter (Google Fonts) — clean, highly readable sans-serif
Headings: Space Grotesk — distinctive, modern display font
Icons: Phosphor Icons (@phosphor-icons/web) — consistent, lightweight SVG set
Spacing: Tailwind's 4px baseline grid (p-4, gap-6, mb-8, etc.)
Shadows: Subtle shadow-lg on modals, hover lifts on buttons (active:translate-y-px)
Animations: CSS keyframes for toast slide-in, smooth opacity transitions for gallery

---

## 📡 API Routes

| Method | URI                               | Action              |
| ------ | --------------------------------- | ------------------- |
| GET    | `/products`                       | index               |
| GET    | `/products/create`                | create              |
| POST   | `/products`                       | store               |
| GET    | `/products/{id}`                  | show                |
| GET    | `/products/{id}/edit`             | edit                |
| PUT    | `/products/{id}`                  | update              |
| DELETE | `/products/{id}`                  | destroy             |
| DELETE | `/products/{id}/images/{imageId}` | destroyImage (AJAX) |
| POST   | `/products/{id}/images`           | storeImages (AJAX)  |

---

## 📝 Notes

- MySQL is used by default — configure env if needed
- Run `php artisan storage:link` once after install to serve uploaded images
- All validation errors are displayed inline with custom styled error components
- The `destroyImage` AJAX endpoint validates that the image belongs to the given product before deletion.

## 📄 License

This project is created for technical assessment purposes.
© 2026 Mozammel Haq — All rights reserved.
