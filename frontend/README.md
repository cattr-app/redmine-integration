# Cattr Redmine Integration — Frontend

A Vue 2 interface extension for [Cattr Server](https://github.com/cattr-app/server-application). It provides Redmine instance management, user authorization, project selection, and priority/status mappings, with English and Russian translations.

Use it together with the [backend module](https://github.com/cattr-app/redmine-integration#readme), which supplies the integration API and synchronization services.

## Requirements

The package runs inside Cattr's frontend and uses its Vue runtime, frontend aliases, services, and webpack module loader. It ships JavaScript, Vue components, and localization files for compilation by Cattr; it has no standalone build.

This repository uses Node.js 24 (`.nvmrc`) and pnpm 12.8.2 (`packageManager`). Use the Node.js and pnpm versions required by the host Cattr server when building its frontend (Cattr Server 4.1.0 uses Node.js 24 and pnpm 12).

## Installation from npm

From the **server-application** root, install the package:

```bash
pnpm add @amazingcat/cattr-redmine-integration
```

Register it in `resources/frontend/etc/modules.config.json` (or a local configuration overlay), preserving existing entries:

```json
{
  "AmazingCat_RedmineIntegration": {
    "type": "package",
    "ref": "@amazingcat/cattr-redmine-integration"
  }
}
```

The registration key must match `ModuleConfig.moduleName` (`AmazingCat_RedmineIntegration`) so Cattr resolves the correct activation flag. Registration controls which module is bundled; the old `enabled` setting does **not** activate it in Cattr Server 4.1+.

Set the frontend activation flag in the Cattr deployment environment:

```dotenv
MODULE_FRONTEND_AMAZING_CAT_REDMINE_INTEGRATION_ENABLED=true
```

Only the exact lowercase value `true` enables the module. The backend must be installed separately and enabled with `MODULE_BACKEND_CATTR_REDMINE_INTEGRATION_ENABLED=true`; see the [backend installation guide](../README.md#enable-and-configure).

Rebuild the host frontend from the **server-application** root:

```bash
pnpm install
pnpm prod
```

In containers, a package must be present during the image build and registered in the frontend module configuration. The environment flag alone does not install it. See [building a Cattr image with modules](https://github.com/cattr-app/server-application/blob/main/CONTRIBUTING.md#building-an-image-with-modules).

## Installation from Source

For local development, check out the repository alongside **server-application** as described in the [backend README](../README.md#installation-from-source), then link its frontend package:

```bash
pnpm link ../redmine-integration/frontend
```

Run this command from the **server-application** root. Register the package with the same `type: "package"` and `ref: "@amazingcat/cattr-redmine-integration"` configuration shown above, enable the frontend flag, and rebuild with `pnpm prod`. The link supplies local source files; publication to npm is not required.

Use either the published npm package or the local package link, not both.

## Verification

After rebuilding, open company settings and check the Redmine integration section. Create or edit an instance and verify its priority/status mappings. In user settings, check authorization for a Redmine account and project selection. Confirm that the backend API is reachable and that synchronization works with a test instance.

For local package maintenance, run from this repository's `frontend/` directory:

```bash
nvm install
nvm use
corepack enable
pnpm install --frozen-lockfile
pnpm pack --dry-run
```

The npm package includes this README, `LICENSE`, `module.init.js`, `components/`, `locales/`, `sections/`, and `services/`. Backend files and development configuration are excluded by the package's `files` allowlist.

## Publishing

The maintainer publishes `@amazingcat/cattr-redmine-integration@0.0.0` manually as a public package. This establishes the package before configuring Trusted Publishing.

In the package settings on npmjs.com, add a GitHub Actions trusted publisher with:

- Organization: `cattr-app`
- Repository: `redmine-integration`
- Workflow filename: `npm-publish.yml` (without the directory)
- Environment: leave blank; the workflow does not declare an environment
- Allow direct publishing with `npm publish` if the settings expose allowed actions

The npm account must have publication rights in the `@amazingcat` scope. The GitHub repository must be public for provenance. See [npm's Trusted Publishing documentation](https://docs.npmjs.com/trusted-publishers/).

For subsequent releases, publish a GitHub Release for a SemVer tag such as `v0.0.1` or `v0.1.0-rc.1`; tags without the leading `v` are also supported. The `release: published` workflow checks out that tag, derives the npm version from Git, and requires it to match the release tag after removing a leading `v`. The source `package.json` may remain at `0.0.0`: the workflow updates it only in the runner and creates no commit or tag.

Regular releases publish with the npm dist-tag `latest`. GitHub prereleases and versions with a SemVer prerelease suffix publish with `next`. Each version can be published only once, so do not run the release workflow for the manually published `0.0.0` version.

The workflow uses Node 24, npm 11, a GitHub-hosted runner, and `id-token: write`. It invokes `npm publish` directly with OIDC; no `NPM_TOKEN` or `NODE_AUTH_TOKEN` secret is needed. Package contents are checked with `pnpm pack --dry-run` before publication. The old Yarn and GitLab registry settings have been removed.

## License

[Server Side Public License 1.0](https://github.com/cattr-app/redmine-integration/blob/main/frontend/LICENSE) (`SSPL-1.0`).
