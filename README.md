<!--
  - SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

# OIDC Groups Mapping

[![PHPUnit](https://github.com/strobelpierre/nextcloud_oidc_groups_mapping/actions/workflows/phpunit.yml/badge.svg)](https://github.com/strobelpierre/nextcloud_oidc_groups_mapping/actions/workflows/phpunit.yml)
[![Lint](https://github.com/strobelpierre/nextcloud_oidc_groups_mapping/actions/workflows/lint.yml/badge.svg)](https://github.com/strobelpierre/nextcloud_oidc_groups_mapping/actions/workflows/lint.yml)
[![Psalm](https://github.com/strobelpierre/nextcloud_oidc_groups_mapping/actions/workflows/psalm.yml/badge.svg)](https://github.com/strobelpierre/nextcloud_oidc_groups_mapping/actions/workflows/psalm.yml)
[![REUSE](https://github.com/strobelpierre/nextcloud_oidc_groups_mapping/actions/workflows/reuse.yml/badge.svg)](https://github.com/strobelpierre/nextcloud_oidc_groups_mapping/actions/workflows/reuse.yml)
[![License: AGPL-3.0-or-later](https://img.shields.io/badge/License-AGPL--3.0--or--later-blue.svg)](https://www.gnu.org/licenses/agpl-3.0)
![Nextcloud: 29-32](https://img.shields.io/badge/Nextcloud-29--32-blue?logo=nextcloud)
![PHP: 8.1+](https://img.shields.io/badge/PHP-8.1%2B-purple?logo=php)

A Nextcloud app that maps **multiple** OIDC token claims to Nextcloud groups via configurable rules. Works with any identity provider through the [user_oidc](https://github.com/nextcloud/user_oidc) app.

## The problem

Your identity provider sends a JWT token like this:

```json
{
  "sub": "jdoe",
  "email": "jdoe@example.com",
  "department": "Engineering",
  "roles": ["admin", "editor"],
  "organization": "corp.example.com",
  "userType": "INTERNAL"
}
```

With `user_oidc` alone, you can map **one** claim to groups (`mappingGroups`). But what if you need groups from `department`, `roles`, `organization`, and `userType` all at once?

**This app solves that.** Configure rules to map any number of claims to Nextcloud groups:

| Without this app | With this app |
|:---|:---|
| 1 claim &rarr; groups | **N claims** &rarr; groups via configurable rules |
| `roles` &rarr; `["admin", "editor"]` | `department` &rarr; `Engineering` |
| | `roles` &rarr; `role_admin`, `role_editor` |
| | `organization` &rarr; `Staff` (via lookup table) |
| | `userType == INTERNAL` &rarr; `Internal-Users` |

## Requirements

- **Nextcloud** 29 -- 32
- **PHP** 8.1+
- **[user_oidc](https://github.com/nextcloud/user_oidc)** app installed and enabled

## Quick start

```bash
# Download and extract
cd /var/www/html/custom_apps/
wget https://github.com/strobelpierre/nextcloud_oidc_groups_mapping/releases/latest/download/oidc_groups_mapping.tar.gz
tar xzf oidc_groups_mapping.tar.gz

# Enable
php occ app:enable oidc_groups_mapping

# Configure rules
php occ oidc-groups:set '{
  "version": 1,
  "mode": "additive",
  "rules": [
    {"id": "departments", "type": "direct", "enabled": true, "claimPath": "department", "config": {}},
    {"id": "user-roles", "type": "prefix", "enabled": true, "claimPath": "roles", "config": {"prefix": "role_"}}
  ]
}'

# Test with a sample token
php occ oidc-groups:test --token '{"department":"Engineering","roles":["admin","editor"]}'
```

## Rule types

Using the JWT token example above:

| Type | What it does | Example | Result |
|:---|:---|:---|:---|
| `direct` | Claim value becomes group name | `department` | `Engineering` |
| `prefix` | Prefix each value | `roles` with prefix `role_` | `role_admin`, `role_editor` |
| `map` | Lookup table | `organization`: `corp.example.com` &rarr; `Staff` | `Staff` |
| `conditional` | If claim matches condition &rarr; assign groups | `userType` equals `INTERNAL` | `Internal-Users` |
| `template` | String template with `{value}` placeholder | `department` with `dept_{value}` | `dept_Engineering` |

### Conditional operators

| Operator | Description | Example |
|:---|:---|:---|
| `equals` | Exact string match | `userType` equals `"EXTERNAL"` |
| `contains` | Array contains value | `roles` contains `"admin"` |
| `regex` | Regex match (with delimiters) | `email` matches `/@example\.com$/` |

## Configuration

Rules are stored as JSON in `IAppConfig`. You can configure them via **Admin Settings** or **OCC commands**.

### Full example

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
        "values": {
          "corp.example.com": "Staff",
          "partner.example.com": "Partners"
        },
        "unmappedPolicy": "ignore"
      }
    },
    {
      "id": "internal-flag",
      "type": "conditional",
      "enabled": true,
      "claimPath": "userType",
      "config": {
        "operator": "equals",
        "value": "INTERNAL",
        "groups": ["Internal-Users"]
      }
    }
  ]
}
```

### Modes

| Mode | Behavior |
|:---|:---|
| `additive` (default) | Rule-produced groups are **merged** with existing groups from `mappingGroups` |
| `replace` | Only rule-produced groups are kept. If rules produce nothing, falls back to existing groups (safety net) |

### Claim paths

Dot-notation paths resolve nested token claims:

- `department` &rarr; `token.department`
- `extended_attributes.auth.permissions` &rarr; `token.extended_attributes.auth.permissions`

URL-style claim keys are also supported (e.g., `https://idp.example.com/claims/domain`).

### Map unmapped policies

When a `map` rule encounters a value not in the lookup table:

| Policy | Behavior |
|:---|:---|
| `ignore` | Value is silently skipped |
| `passthrough` | Original claim value is used as group name |

## Admin settings

Configure rules through the Nextcloud admin panel under **Administration &rarr; OIDC Groups Mapping**.

<!-- TODO: Add screenshots of the admin UI -->

## OCC commands

```bash
# List configured rules
php occ oidc-groups:list

# Set rules from JSON
php occ oidc-groups:set '{"version":1,"mode":"additive","rules":[...]}'

# Test rules against a sample token
php occ oidc-groups:test --token '{"department":"IT","roles":["admin","editor"]}'

# Test with existing groups (to see merge behavior)
php occ oidc-groups:test --token '{"department":"IT"}' --existing '["users"]'
```

## How it works

This app listens to the `AttributeMappedEvent` dispatched by `user_oidc` during login. When the `mappingGroups` attribute is being processed, it:

1. Loads mapping rules from `IAppConfig`
2. Resolves claim values from the token using dot-notation paths
3. Applies each enabled rule to produce groups
4. Merges or replaces the group list depending on the mode
5. Calls `setValue()` and `stopPropagation()` on the event

## Installation

### From release tarball (recommended)

```bash
cd /var/www/html/custom_apps/
wget https://github.com/strobelpierre/nextcloud_oidc_groups_mapping/releases/latest/download/oidc_groups_mapping.tar.gz
tar xzf oidc_groups_mapping.tar.gz
php occ app:enable oidc_groups_mapping
```

### From source

```bash
cd /var/www/html/custom_apps/
git clone https://github.com/strobelpierre/nextcloud_oidc_groups_mapping.git oidc_groups_mapping
cd oidc_groups_mapping && composer install --no-dev
php occ app:enable oidc_groups_mapping
```

## Development

```bash
composer install
composer test:unit    # PHPUnit
composer psalm        # Static analysis
composer cs:check     # Code style check (requires vendor-bin setup)
composer cs:fix       # Fix code style
composer lint         # PHP syntax check
```

### Setting up php-cs-fixer

```bash
mkdir -p vendor-bin/cs-fixer
cd vendor-bin/cs-fixer
composer require nextcloud/coding-standard
cd ../..
```

### Dev environment

A Docker Compose setup with Keycloak is available in `dev/`:

```bash
cd dev && docker compose up -d
```

### Building the release tarball

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

AGPL-3.0-or-later -- see [LICENSE](LICENSE) for details.
