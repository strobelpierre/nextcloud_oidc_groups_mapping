<?php
/**
 * SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
/** @var array $_ */
?>
<div id="oidc-groups-mapping-settings" class="section">
    <h2>OIDC Groups Mapping</h2>

    <p>
        <strong>Mode:</strong> <?php p($_['mode']); ?> &mdash;
        <strong>Rules:</strong> <?php p($_['rules_count']); ?>
    </p>

    <h3>Current rules (JSON)</h3>
    <textarea id="oidc-groups-mapping-rules"
              style="width:100%; height:300px; font-family:monospace; font-size:13px;"
              placeholder="Paste JSON rules here..."><?php p($_['rules_json']); ?></textarea>

    <br><br>
    <button id="oidc-groups-mapping-save" class="primary">Save</button>
    <span id="oidc-groups-mapping-status" style="margin-left:10px;"></span>
</div>

<script>
document.getElementById('oidc-groups-mapping-save').addEventListener('click', function() {
    var textarea = document.getElementById('oidc-groups-mapping-rules');
    var status = document.getElementById('oidc-groups-mapping-status');
    var json = textarea.value;

    // Validate JSON
    try {
        JSON.parse(json);
    } catch(e) {
        status.textContent = 'Invalid JSON: ' + e.message;
        status.style.color = 'red';
        return;
    }

    // Save via occ (admin only) - use NC OCS API
    var xhr = new XMLHttpRequest();
    xhr.open('POST', OC.generateUrl('/apps/provisioning_api/api/v1/config/apps/oidc_groups_mapping/mapping_rules'));
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('requesttoken', OC.requestToken);
    xhr.onload = function() {
        if (xhr.status === 200) {
            status.textContent = 'Saved!';
            status.style.color = 'green';
        } else {
            status.textContent = 'Error: ' + xhr.status;
            status.style.color = 'red';
        }
    };
    xhr.send('value=' + encodeURIComponent(json));
});
</script>
