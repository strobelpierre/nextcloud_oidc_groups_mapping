/**
 * SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Vue from 'vue'
import AdminApp from './AdminApp.vue'

const mountEl = document.getElementById('oidc-groups-mapping-app')
if (mountEl) {
	new Vue({
		el: mountEl,
		render: h => h(AdminApp),
	})
}
