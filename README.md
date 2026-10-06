# 33½

A small Laravel storefront scaffold for vinyl records and music related merchandise. It is intentionally a foundation: the catalog, layout, components, and interactions are ready to edit, while checkout and other launch features remain placeholders.

## Stack

- Laravel 12 / PHP 8.2+
- Blade templates
- MySQL schema (SQLite also works for local demos)
- Plain CSS and small JavaScript modules, built with Vite

## Start the project

1. Install PHP dependencies: `composer install`
2. Install frontend dependencies: `npm install`
3. Copy `.env.example` to `.env`, then run `php artisan key:generate`.
4. Create a MySQL database named `33half` (or change `DB_DATABASE` in `.env`) and set your MySQL credentials there.
5. Run `php artisan migrate --seed`.
6. Run `npm run build` and `php artisan serve`.
7. Open the URL printed by Laravel (usually `http://127.0.0.1:8000`).

For CSS/JS development, use `npm run dev` in a second terminal instead of rebuilding after each change. To use SQLite locally, change `DB_CONNECTION=sqlite`, create `database/database.sqlite`, and run the same migration command.

## Main folders

| Path | Purpose |
| --- | --- |
| `app/Models` | Product, Artist, Genre, Label and their relationships |
| `app/Http/Controllers` | Page, catalog, and session cart logic |
| `database/migrations` | Catalog tables and Laravel's default support tables |
| `database/seeders/CatalogSeeder.php` | Fictional demo records and merchandise |
| `resources/views/layouts` | Shared HTML shell |
| `resources/views/components` | Reusable storefront pieces |
| `resources/views/pages` | Home, catalog, directory, crate, cart, and information pages |
| `resources/views/products` | Shared product detail page |
| `resources/css/app.css` | Neutral, responsive starter design |
| `resources/js` | Audio, crate, and cart modules |
| `public/images/demo`, `public/audio` | Local placeholder covers and original demo sound |
| `scripts/generate_demo_assets.py` | Recreates demo artwork and sound |

## Routes

| Route | Page |
| --- | --- |
| `/` | Home |
| `/shop` | All products |
| `/vinyl` | Vinyl |
| `/records/{slug}` | Vinyl detail |
| `/merch`, `/merch/{slug}` | Merchandise and detail |
| `/artists`, `/artists/{slug}` | Artist directory and collection |
| `/genres`, `/genres/{slug}` | Genre directory and collection |
| `/new-arrivals`, `/pre-orders` | Curated catalog views |
| `/crate-digging` | Experimental record browser |
| `/cart` | Session cart |
| `/about`, `/contact` | Editable information pages |

The catalog already accepts simple query parameters: `q`, `genre`, `artist`, `label`, `min_price`, `max_price`, `availability`, and `sort`.

## Data model

`products` holds both vinyl and merchandise via `product_type`. Core fields include name, slug, description, price, stock, artwork path, featured/preorder flags, and optional merch category. Record specific fields (release date, format, label, audio preview URL/start time) are nullable where appropriate. Artists and genres are many-to-many with products; labels are one-to-many. Merchandise sizes and variants are an extension point, not inventory managed yet.

The seeded content is fictional and uses original local placeholder assets. Replace it before launch. Prices display in USD as a demo convention; change the currency formatting in the Blade components if needed.

## Interactive features

- Product card hover previews work on desktop after a page interaction. The play button works by tap or click. A single audio manager ensures that clips never overlap and limits each preview to ten seconds.
- Crate Digging displays one record at a time. Buttons, arrow keys, scrolling, and swiping move through the stack; a random button shuffles it. Audio starts only after an allowed interaction.
- The cart stores product IDs and quantities in the Laravel session. It supports add, update, and remove, but no checkout or payment processing.

## Next steps

Replace demo catalog data and art; add real contact details; design merchandise variants; implement newsletter storage and checkout only when those workflows are decided. Authentication, selling by users, wish lists, recommendations, and music app connections are future ideas recorded in `IDEAS.md`.

