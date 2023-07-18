# Self-hosting Eddy Server Management

Instead of using the [hosted version](https://eddy.management) of Eddy, you can also host it yourself. While it's a regular Laravel app, here's a quick guide to get you started.

## Requirements

- PHP 8.1 or higher
- MySQL 8.0
- Redis
- Pusher or a compatible alternative

## Installation

Start by cloning the repository and installing the dependencies:

```bash
git clone https://github.com/protonemedia/eddy-server-management.git
cd eddy-server-management
composer install --no-dev
npm install
```

Next, you'll need to create a database and fill in the credentials in the `.env` file. Check out the [Laravel documentation](https://laravel.com/docs/10.x/database#configuration) for more information on how to do this. You can copy the `.env.example` file to get started and generate an application key:

```bash
cp .env.example .env
php artisan key:generate --force
```

Also in the `.env` file, set the cache and queue driver to `redis`, set the broadcast driver to `pusher`, and fill in the credentials for your Pusher account. Pusher is used to broadcast events to the app, like when a server is created or deleted so the frontend can update in real-time (handled by [Splade](https://splade.dev/docs/x-event)).

After that, everything is ready to compile the assets and run the migrations:

```bash
npm run build
php artisan migrate
```

Start Horizon to process the queue:

```bash
php artisan horizon
```

Lastly, when needed, you can start the development server:

```bash
php artisan serve
```

## Incoming webhooks

The Eddy app needs to be accessible from the internet to receive incoming webhooks. This is used to send status updates to the app from your servers. If the app is already accessible from the internet, you can skip this step.

Otherwise, for example, if you're running the app locally, you can use tools like [Expose](https://expose.dev), [Ngrok](https://ngrok.com) or [Cloudflare Tunnel](https://www.cloudflare.com/products/tunnel/) to make the app accessible from the internet. Make sure to set the `WEBHOOK_URL` environment variable to the URL of the tunnel. You can leave the `APP_URL` environment variable as is so you can still access the app locally.

## Mails

To send mails, you'll need to fill in the `MAIL_*` environment variables. Check out the [Laravel documentation](https://laravel.com/docs/10.x/mail#driver-prerequisites) for more information on how to do this. You can also use a service like [Mailtrap](https://mailtrap.io) for testing.

## GitHub integration

If you want to use the GitHub integration, you'll need to [create a GitHub OAuth app](https://docs.github.com/en/developers/apps/building-oauth-apps/creating-an-oauth-app) and fill in the `GITHUB_CLIENT_ID` and `GITHUB_CLIENT_SECRET` environment variables.

## CSP (Content Security Policy)

By default, Eddy uses a [Content Security Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP). Though not recommended, you can disable this by setting the `CSP_ENABLED` environment variable to `false`.

## SSR (Server-Side Rendering)

Checkout out the documentation on [Splade's built-in SSR Server](https://splade.dev/docs/ssr) to learn how to enable it.