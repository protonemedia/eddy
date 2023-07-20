# Eddy Server Management 🛡️

[![run-tests](https://github.com/protonemedia/eddy-server-management/actions/workflows/run-tests.yml/badge.svg?branch=main)](https://github.com/protonemedia/eddy-server-management/actions/workflows/run-tests.yml)
[![Splade Discord Server](https://dcbadge.vercel.app/api/server/qGJ4MkMQWm?style=flat&theme=default-inverted)](https://discord.gg/qGJ4MkMQWm)
[![GitHub Sponsors](https://img.shields.io/github/sponsors/pascalbaljet)](https://github.com/sponsors/pascalbaljet)
[![MadeWithLaravel.com shield](https://madewithlaravel.com/storage/repo-shields/4467-shield.svg)](https://madewithlaravel.com/p/eddy-server-management/shield-link)

Eddy is an open-source deployment tool built with [Splade](https://splade.dev) that allows users to deploy PHP applications with zero downtime using the Caddy web server.

Eddy supports easy server provisioning using Ubuntu 22.04 LTS and can start servers quickly from popular providers such as DigitalOcean, Hetzner, or any custom provider. Eddy also features automatic SSL certificate renewal, automatic security updates, and the ability to manage cron jobs, daemon processes, and firewall rules. It supports Database and File backups to S3, FTP, and SFTP destinations with custom schedules, automatic cleanup, and notifications. Additionally, users can manage MySQL databases and run multiple PHP versions on a single server.

[Eddy Server Management](https://eddy.management) is a [Protone Media](https://protone.media) product.

## Features ✨

- Deploy PHP apps with zero downtime and powered by Caddy web server.
- Deploy from Github or any Git repository with ease.
- Provision servers using Ubuntu 22.04 LTS.
- Start servers quickly from DigitalOcean, Hetzner, or your preferred provider.
- Keep your SSL certificates up-to-date with automatic renewal.
- Keep your servers secure with automatic updates.
- Run multiple PHP versions on a single server.
- Easily manage your MySQL databases and users.
- Manage cron jobs, daemon processes, and firewall rules.
- Database and File backups to S3, FTP, and SFTP destinations.
- Customize your deployment process with support for custom deployment scripts.
- Edit configuration files directly from the web interface.
- Quickly access both your server and application logs directly from the web interface.

Eddy is built with the [Jetstream starter kit](https://splade.dev/docs/jetstream), so it also comes with:

- Team management
- Two-factor authentication
- Email verification
- A fully responsive design built with Tailwind CSS

## Why is this open-source? 🔓

Eddy was created to show off the capabilities of [Splade](https://splade.dev). As a real-life app, Eddy perfectly showcases some of Splade's key features, including the ability to build Single-Page Applications with Blade templates, Modal and Event capabilities, and Form and Table components.

Although Eddy is an open-source project, you have the option to use the hosted service at [eddy.management](https://eddy.management). Choosing the hosted service not only provides you with the convenience of not having to host it yourself, but it also supports the continued development of Eddy and Splade. However, hosting it yourself is also an option.

Since Eddy is open-source, you can easily contribute to the project by submitting pull requests. This gives you the added benefit of being able to see exactly how the app works and interacts with your server. Don't hesitate to get involved and help make Eddy and Splade even better!

## Requirements ⚙️

Eddy is a regular Laravel 10 application, so it can be installed on any server that meets the [Laravel server requirements](https://laravel.com/docs/10.x/deployment#server-requirements). In addition to the Laravel requirements, Eddy requires the following:

- [Redis](https://laravel.com/docs/10.x/redis), as Eddy uses Laravel Horizon
- [`timeout`](https://manpages.ubuntu.com/manpages/trusty/man1/timeout.1.html) to run commands with a timeout
- (optional) [`beautysh`](https://pypi.org/project/beautysh/) to format shell scripts
- (optional) [`caddy`](https://caddyserver.com/) to format a Caddyfile

## Local development 💻

Eddy features built-in support for [Vagrant](https://www.vagrantup.com), allowing for easy local server provisioning without the need to start up servers with a cloud provider. To make Eddy publicly accessible for server connections, you can use tools like [Expose](https://expose.dev), [Ngrok](https://ngrok.com) or [Cloudflare Tunnel](https://www.cloudflare.com/products/tunnel/).

## How is Eddy different from alternatives? 🤔

Eddy sets itself apart from other solutions by being completely open-source, giving you the freedom to host it yourself and see exactly how it works. In fact, the hosted version of Eddy runs on the exact same code as the open-source version. While Eddy is still relatively new and may not be as feature-rich as some alternatives, our goal is to rapidly expand its capabilities over time.

## Contributing 🤝

Thank you for considering contributing to Eddy! Our project follows the [Laravel coding style](https://laravel.com/docs/10.x/contributions#coding-style) and conventions as closely as possible. Here are some guidelines to help you get started:

### Structure and Maintanability

- Avoid adding new dependencies unless absolutely necessary.
- Use the `__()` helper function instead of hardcoding translations.
- Each Eloquent model should have a sensible [Database Factory](https://laravel.com/docs/10.x/database-testing#factories).
- Use [Events](https://laravel.com/docs/10.x/events) primarily to broadcast changes to the frontend, and avoid Listeners for simple tasks.
- Use [Queued Jobs](https://laravel.com/docs/10.x/queues) to perform long-running tasks. Notify users that a task is running.
- Use [Notifications](https://laravel.com/docs/10.x/notifications) to send emails to users, or a [Mailable](https://laravel.com/docs/10.x/mail) when it's unimaginable that a notification would be sent to anything other than the mail channel.
- Don't worry too much about striving for slim controllers. Controllers can have a few extra lines of code to improve code readability, but try to stick to CRUD actions unless necessary.
- Don't bring HTTP request logic into areas other than the controller. For example, don't use the `Auth` or `Request` facade in a model or a service class.
- Don't use inline Validation rules, but create a dedicated [Rule](https://laravel.com/docs/10.x/validation#custom-validation-rules) class instead.
- Validate requests in the controller when possible, and use [Form Requests](https://laravel.com/docs/10.x/validation#form-request-validation) when validation rules are more complex.
- Prefer enums over constants.

### Security and Performance

- Encrypt all sensitive data in Eloquent models.
- Each Eloquent model should have a corresponding [Policy](https://laravel.com/docs/10.x/authorization#creating-policies) to handle authorization.
- Each Eloquent model should have a corresponding [Resource](https://laravel.com/docs/10.x/eloquent-resources) to handle serialization.
- Although we're not a fan of Eloquent's [Mass Assignment protection](https://laravel.com/docs/10.x/eloquent#mass-assignment), we do use them in Eddy as it's generally considered a best practice.
- All non-GET routes should insert an `ActivityLog` entry.
- Always use pagination on index pages.
- The following Eloquent protections are enabled by default
  - Prevent Lazy Loading to avoid N+1 queries
  - Prevent silently discarding attributes
  - Prevents accessing missing attributes
  - Require a morph map when using polymorphic relations

### Frontend

- Write sensible error messages.
- Use ULIDs instead of auto-incrementing IDs.
- Avoid creating custom Vue components unless necessary, and use Splade's components as much as possible.

## Security Vulnerabilities

If you discover a security vulnerability within Eddy, please send an e-mail to Eddy via [info@eddy.management](mailto:info@eddy.management). All security vulnerabilities will be promptly addressed.

## License

Eddy is open-sourced software licensed under the [AGPL-3.0 License](https://opensource.org/licenses/AGPL-3.0).
