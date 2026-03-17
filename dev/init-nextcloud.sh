#!/bin/bash
set -e

echo "=== Initializing Nextcloud for OIDC Groups Mapping development ==="

# Wait for NC to be ready
until php occ status 2>/dev/null | grep -q "installed: true"; do
    echo "Waiting for Nextcloud to be installed..."
    sleep 5
done

# Fix permissions
chown -R www-data:www-data /var/www/html/apps /var/www/html/custom_apps

# Install and enable user_oidc
echo "Installing user_oidc..."
php occ app:install user_oidc || true
php occ app:enable user_oidc || true

# Allow insecure HTTP for dev
php occ config:app:set user_oidc allow_insecure_http --value="1" --type=boolean
php occ config:system:set allow_local_remote_servers --value=true --type=boolean

# Configure OIDC provider pointing to local Keycloak
echo "Configuring OIDC provider..."
php occ user_oidc:provider nextcloud-keycloak \
    --clientid="nextcloud" \
    --clientsecret="ff75b7c7-20f9-460b-b27c-16bd5d9b4cd0" \
    --discoveryuri="http://keycloak:8080/realms/nextcloudci/.well-known/openid-configuration" \
    --unique-uid=0 \
    --group-provisioning=1 \
    --mapping-groups="groups"

# Enable our app
echo "Enabling oidc_groups_mapping..."
php occ app:enable oidc_groups_mapping || true

# Configure mapping rules — no prefixes, direct claim values as group names
echo "Setting mapping rules..."
php occ config:app:set oidc_groups_mapping mapping_rules --value='{
  "version": 1,
  "mode": "additive",
  "rules": [
    {
      "id": "dept",
      "type": "direct",
      "enabled": true,
      "claimPath": "department",
      "config": {}
    },
    {
      "id": "roles",
      "type": "direct",
      "enabled": true,
      "claimPath": "roles",
      "config": {}
    },
    {
      "id": "org",
      "type": "direct",
      "enabled": true,
      "claimPath": "organization",
      "config": {}
    },
    {
      "id": "ext-check",
      "type": "conditional",
      "enabled": true,
      "claimPath": "is_external",
      "config": {
        "operator": "equals",
        "value": "true",
        "groups": ["External-Users"]
      }
    }
  ]
}'

echo ""
echo "=== Setup complete ==="
echo "NC:  http://localhost:8080  (admin/admin)"
echo "KC:  http://localhost:8999  (admin/admin)"
echo "Test: Login via 'Log in with nextcloud-keycloak' using testuser1/password"
