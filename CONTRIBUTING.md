<!--
  - SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Contributing to OIDC Groups Mapping

Thanks for your interest in contributing! This guide will help you get started.

## Reporting bugs

Open a [bug report](https://github.com/strobelpierre/nextcloud_oidc_groups_mapping/issues/new?template=bug_report.md) and include:

- Nextcloud version, PHP version, user_oidc version
- Your identity provider (Keycloak, Azure AD, etc.)
- Steps to reproduce
- Relevant logs from `data/nextcloud.log`

## Suggesting features

Open a [feature request](https://github.com/strobelpierre/nextcloud_oidc_groups_mapping/issues/new?template=feature_request.md) describing your use case and proposed solution.

## Development setup

See the [Development section](README.md#development) of the README for environment setup.

```bash
composer install
```

## Code requirements

Before submitting a PR, ensure all checks pass:

```bash
composer test:unit   # PHPUnit tests
composer cs:check    # Code style (Nextcloud coding standard)
composer psalm       # Static analysis
```

## Pull request process

1. **Fork** the repository
2. **Create a branch** from `main` (`feat/my-feature` or `fix/my-fix`)
3. **Make your changes** with clear, focused commits
4. **Run all checks** (see above)
5. **Open a PR** with a description of what and why

Keep PRs focused on a single change. If you're fixing a bug and want to refactor nearby code, submit separate PRs.

## Code style

This project follows the [Nextcloud coding standard](https://github.com/nextcloud/coding-standard). Run `composer cs:fix` to auto-format.

## License

By contributing, you agree that your contributions will be licensed under [AGPL-3.0-or-later](LICENSE).
