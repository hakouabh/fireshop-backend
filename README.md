## Features
- :zap: Fast!!!
- :sparkles: Friendly UI & UX and Has nice looking window.
- :cloud: Cloud data service.
- :inbox_tray: Products Management.
- :busts_in_silhouette: Customers Management.
- :truck: Orders Management and Order Overview.
- :ship: Multiple Orders.
- :chart_with_upwards_trend: Statistics
- :chart_with_downwards_trend: Graph of The Week **(NEW!!!)**.
- :top: Top Selling Products.
- :open_hands: Customers List on Product Overview.
- :bookmark_tabs: Reports for Products, Orders, Customers.
- :key: Profile Settings.
- :jp: Multilingual Translations.
- :yen: Change Currency.
- :clipboard: Save Report as PDF.
- :100: Check for update app.
## Development

#### System Requirements
- NodeJS ^8.
- PHP ^7.
- PHP Composer.

#### Backend
- Clone the project from [hakouabh/fireshop-backend](https://github.com/hakouabh/fireshop-backend/) to your local machine.
- Open terminal and `cd` into the cloned folder, usually `cd fireshop-backend`.
- Run `composer install` to install dependencies.
- Run `cp .env.example .env` to copy the original environtment variables.
- Modify `.env` file, find `DATABASE_` prefix and change it to your database configuration, you can see documentation about this on [Laravel Site](https://laravel.com/docs/configuration#environment-configuration).
- Run `php artisan key:generate` to generate application key.
- Run `php artisan migrate` to create default database schema of fireshop.
- Run `php artisan passport:install` to install default OAuth access token.
- Run `php artisan serve` to run PHP Built-in web server.
- Now your backend is ready to listen request from frontend at `https://localhost:8000/api`.

#### Frontend
- Clone the project from [hakouabh/fireshop-hope-ui](https://github.com/hakouabh/fireshop-hope-ui/) to your local machine.
- Open terminal and `cd` into the cloned folder, usually `cd fireshop-hope-ui`.
- Run `cp .env.example .env` to copy the original environtment variables.
- Run `npm install` to install dependencies.
- Run `npm run serve` to serve the app as web-based at `https://localhost:8000`.
- Now it's ready to use for development.

**When you have make changes and everything works on VueJs, and wanna check on desktop app.**
- Run `npm run gh-publish` to build the app from source and run the app as desktop app.

## License
MIT License © 2020-Present [Hakouabh](https://github.com/hakouabh). All rights reserved.
