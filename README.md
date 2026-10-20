# Cattr Redmine Integration — Backend

A Laravel module that connects [Cattr Server](https://github.com/cattr-app/server-application) to Redmine. It synchronizes projects, tasks, groups, priorities, statuses, and time entries, and provides API endpoints for the integration.

For instance management, user authorization, and interface setup, see the [frontend README](frontend/README.md). The frontend is distributed separately as `@amazingcat/cattr-redmine-integration` on npm.

## Requirements

This module runs inside Cattr, using its models, services, database, and scheduler. It has no standalone application or build. The backend manifest specifies a minimum core version of `4.0`; choose a module release compatible with your server version.

Use the PHP and Composer versions required by your Cattr server. PHP SimpleXML is required; Composer installs `kbsali/redmine-api`. Configure a reachable Redmine instance with REST API access. Redmine 7.0.2 has built-in webhooks. Native webhook mode requires an instance-specific secret (at least 16 characters). Existing older Redmine installations may continue using their installed webhook plugin and legacy shared signature.

## Installation from a Published Composer Package

After the repository has been registered on Packagist and a compatible release is available, run from the **server-application** root:

```bash
composer require cattr/redmine-integration-module
```

The Composer package is rooted at this repository's `composer.json`, with PHP sources under `backend/`. Cattr must scan Composer modules under `vendor/*/*` (as configured in its `config/modules.php`). Enable the module using the configuration below. Choose an explicit version constraint when maintaining a deployment.

## Installation from Source

Run from the **server-application** root:

```bash
git clone https://github.com/cattr-app/redmine-integration.git modules/CattrRedmineIntegration
composer update --lock
```

Cattr's Composer merge configuration includes `modules/*/composer.json`. The module maps `Modules\RedmineIntegration\` to `backend/`. Install either the source checkout or the Composer package to avoid duplicate module discovery.

## Enable and Configure

Add this entry to the server's `modules.json`, preserving existing entries:

```json
{
  "CattrRedmineIntegration": true
}
```

`CattrRedmineIntegration` is the backend module name; `cattr_redmine_integration` is its alias. If it was disabled through Cattr's module configuration, enable it explicitly:

```bash
php artisan module:enable CattrRedmineIntegration
php artisan migrate
```

Restart the Cattr processes, including Octane and the scheduler, after configuration changes. Install and register the [frontend module](frontend/README.md), then configure Redmine instances, synchronization intervals, and mappings in the interface. Users authorize their Redmine accounts through their settings.

For Redmine 7 native webhooks, configure the same secret (at least 16 characters) in both the Redmine webhook and the corresponding Cattr instance. Secrets are encrypted at rest and are never included in the instance API response; updating with an empty field keeps the existing secret. Configure these Redmine events: `issue.created`, `issue.updated`, and `issue.deleted`. The endpoint is `/api/integration/redmine/{instance}/webhook`, where `{instance}` is the Cattr Redmine instance ID. Redmine signs the raw JSON body with HMAC-SHA256 and sends `X-Redmine-Signature-256: sha256=<hex digest>`. The endpoint rejects invalid or missing signatures with HTTP 403. Existing legacy webhook plugin deliveries remain supported with their configured static signature token; see the backward-compatibility section below. Deleting an issue soft-deletes its linked Cattr task by setting `tasks.deleted_at`; it retains task mapping and recorded time history. Repeat deletion events are idempotent.

### Backward compatibility with legacy Redmine webhook plugins

The existing endpoint `/api/integration/redmine/{instance}/webhook` accepts **both** formats without changing the sender URL:

- **Native Redmine 7:** `X-Redmine-Signature-256: sha256=<HMAC-SHA256>`, computed over the exact request body using the encrypted per-instance `webhook_secret`. Events use `type` and `data.issue`; creation, updates and deletion are supported.
- **Legacy Redmine plugin:** `X-REDMINE-SIGNATURE: <shared static token>`, using `REQUEST_SIGNATURE` from the Cattr server environment and the `payload.issue` envelope. Older `assignee` fields are mapped to `assigned_to`; as before, legacy events create or update tasks, not delete them.

To keep existing deployments working, retain their old `REQUEST_SIGNATURE` and leave `webhook_secret` unset for those Redmine instances. The old token is shared across legacy instances, whereas the native HMAC secret is configured per instance. `REQUEST_SIGNATURE` must be a nonempty, non-default secret; `DEFAULT` is always rejected.

For **new native Redmine 7 installations**, configure the per-instance HMAC secret in the Cattr UI and in Redmine. When an instance has a native secret, the legacy shared token is **not** accepted for that instance. A request bearing a native signature is never retried with legacy authentication if HMAC verification fails. This maintains native authentication without downgrade, and lets older instances keep using their existing URLs and settings. To migrate an old instance to native Redmine webhooks, save its new per-instance secret and switch the sender in Redmine; the old legacy token will then stop working for that instance.

Legacy static-token authentication is provided only for backward compatibility. Protect the endpoint with HTTPS, rotate exposed tokens, and prefer native HMAC authentication for new deployments.

Use Cattr's existing Laravel scheduler. If none is configured, run it once per minute:

```cron
* * * * * cd /path/to/server-application && php artisan schedule:run >> /dev/null 2>&1
```

Projects and tasks are synchronized for instances using cron mode. Priorities and statuses use each instance's configured intervals; time synchronization runs every five minutes.

## Verification

From the server root:

```bash
php artisan module:list
php artisan list redmine
php artisan schedule:list
```

Check instance connectivity and mappings in the interface, then verify synchronization against a test Redmine instance. The native webhook test in `backend/Tests/NativeRedmineWebhookTest.php` requires Cattr's application test harness; this repository does not provide a standalone PHPUnit setup.

## Repository and Publishing

```text
backend/                     # PHP code, configuration, migrations, and tests
frontend/                    # npm package and its own README
composer.json                # Composer metadata and backend autoloading
module.json                  # Cattr backend manifest
.github/workflows/npm-publish.yml
```

Register `https://github.com/cattr-app/redmine-integration` on Packagist as `cattr/redmine-integration-module` and configure the GitHub integration/webhook for updates. Composer versions come from Git tags; no hardcoded Composer version is needed. The obsolete GitLab Composer publication job has been removed.

The initial npm version `0.0.0` is published manually by the maintainer. Subsequent npm releases use GitHub Releases and OIDC; see the [frontend publishing instructions](frontend/README.md#publishing).

## License

[Server Side Public License 1.0](LICENSE) (`SSPL-1.0`).
