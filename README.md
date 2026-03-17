<!--
  - SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

# OIDC Groups Mapping

[![PHPUnit](https://github.com/strobelpierre/oidc_groups_mapping/actions/workflows/phpunit.yml/badge.svg)](https://github.com/strobelpierre/oidc_groups_mapping/actions/workflows/phpunit.yml)
[![Psalm](https://github.com/strobelpierre/oidc_groups_mapping/actions/workflows/psalm.yml/badge.svg)](https://github.com/strobelpierre/oidc_groups_mapping/actions/workflows/psalm.yml)
[![REUSE](https://github.com/strobelpierre/oidc_groups_mapping/actions/workflows/reuse.yml/badge.svg)](https://github.com/strobelpierre/oidc_groups_mapping/actions/workflows/reuse.yml)
[![License: AGPL-3.0-or-later](https://img.shields.io/badge/License-AGPL--3.0--or--later-blue.svg)](https://www.gnu.org/licenses/agpl-3.0)

A Nextcloud app that maps multiple OIDC token claims to Nextcloud groups via configurable rules.

## Requirements

- **Nextcloud** 29 – 32
- **PHP** 8.1+
- **user_oidc** app — must be installed and enabled. This app listens to events dispatched by user_oidc and will not function without it.

## Problem

The `user_oidc` app supports only **one** claim for group mapping (`mappingGroups`). Some identity providers expose group-relevant data across multiple claims with no server-side transformation available. The mapping must happen on the Nextcloud side.

## How it works

This app listens to the `AttributeMappedEvent` dispatched by `user_oidc` during login. When the `mappingGroups` attribute is being processed, it:

1. Loads mapping rules from `IAppConfig`
2. Resolves claim values from the token using dot-notation paths
3. Applies each enabled rule to produce groups
4. Merges (additive mode) or replaces the group list
5. Calls `setValue()` and `stopPropagation()` on the event

## Rule types

| Type | Description | Example |
|------|-------------|---------|
| `direct` | Claim value becomes group name | `department` = `"IT"` → group `IT` |
| `prefix` | Each value is prefixed | `roles[]` = `["admin"]` → `role_admin` |
| `map` | Lookup table | `domain` = `"corp.example.com"` → `Staff` |
| `conditional` | If claim matches → assign group(s) | `userType` = `"EXTERNAL"` → `External-Users` |
| `template` | String template | `department` = `"IT"` → `dept_IT` |

## Configuration

Rules are stored as JSON in `IAppConfig` under key `mapping_rules`:

```json
{
  "version": 1,
  "mode": "additive",
  "rules": [
    {
      "id": "departments",
      "type": "template",
      "enabled": true,
      "claimPath": "department",
      "config": { "template": "dept_{value}" }
    },
    {
      "id": "user-roles",
      "type": "prefix",
      "enabled": true,
      "claimPath": "roles",
      "config": { "prefix": "role_" }
    },
    {
      "id": "org-mapping",
      "type": "map",
      "enabled": true,
      "claimPath": "organization",
      "config": {
        "values": { "corp.example.com": "Staff", "partner.example.com": "Partners" },
        "unmappedPolicy": "ignore"
      }
    }
  ]
}
```

### Modes

- **additive** (default): New groups are merged with existing ones from the native `mappingGroups` claim
- **replace**: Only rule-produced groups are kept. If rules produce nothing, falls back to existing groups (safety net)

### Claim paths

Dot-notation paths resolve nested token claims:
- `department` → `token.department`
- `extended_attributes.auth.permissions` → `token.extended_attributes.auth.permissions`

URL-style claim keys are also supported (e.g., `https://idp.example.com/claims/domain`).

## OCC commands

```bash
# List configured rules
php occ oidc-groups:list

# Set rules from JSON
php occ oidc-groups:set '{"version":1,"mode":"additive","rules":[...]}'

# Test rules against a sample token
php occ oidc-groups:test --token '{"department":"IT","roles":["admin","editor"]}'

# Test with existing groups
php occ oidc-groups:test --token '{"department":"IT"}' --existing '["users"]'
```

## Installation

### From release tarball

```bash
cd /var/www/html/custom_apps/
wget https://github.com/strobelpierre/oidc_groups_mapping/releases/download/v1.0.0/oidc_groups_mapping.tar.gz
tar xzf oidc_groups_mapping.tar.gz
php occ app:enable oidc_groups_mapping
```

### Manual

```bash
cd /var/www/html/custom_apps/
git clone https://github.com/strobelpierre/oidc_groups_mapping.git
cd oidc_groups_mapping && composer install --no-dev
php occ app:enable oidc_groups_mapping
```

## Development

```bash
composer install
composer test:unit    # PHPUnit
composer psalm        # Static analysis
composer cs:check     # Code style check
composer cs:fix       # Fix code style
composer lint         # PHP syntax check
```

### Dev environment

A Docker Compose setup with Keycloak is available in `dev/`:

```bash
cd dev && docker compose up -d
```

### Building the App Store tarball

```bash
make appstore
# Output: build/oidc_groups_mapping.tar.gz
```

## Troubleshooting

### Groups not being mapped
- Ensure `user_oidc` is installed and enabled
- Verify claim paths match your IdP token structure using `php occ oidc-groups:test`
- Check Nextcloud logs for `oidc_groups_mapping` messages

### Rules not applying
- Verify rules are enabled (`"enabled": true`)
- Ensure the JSON is valid via the admin settings UI or `php occ oidc-groups:list`
- For conditional rules with `regex` operator, ensure the regex pattern is valid (including delimiters)

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Ensure tests pass: `composer test:unit`
4. Ensure code style: `composer cs:check`
5. Submit a pull request

## License

AGPL-3.0-or-later — see [LICENSE](LICENSE) for details.
