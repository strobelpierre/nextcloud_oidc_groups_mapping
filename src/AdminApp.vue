<!--
  - SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
  -->

<template>
	<div class="oidc-groups-mapping-admin">
		<h2>OIDC Groups Mapping</h2>
		<ModeSelector :mode="mode" :rules-count="rules.length" :enabled-count="enabledCount" />
		<RuleList :rules="rules" />
		<p v-if="rules.length === 0" class="empty-state">
			No mapping rules configured yet.
		</p>
	</div>
</template>

<script>
import { loadState } from '@nextcloud/initial-state'
import ModeSelector from './components/ModeSelector.vue'
import RuleList from './components/RuleList.vue'

export default {
	name: 'AdminApp',
	components: {
		ModeSelector,
		RuleList,
	},
	data() {
		const rulesJson = loadState('oidc_groups_mapping', 'rules', '{}')
		const parsed = typeof rulesJson === 'string' ? JSON.parse(rulesJson) : rulesJson

		return {
			mode: parsed.mode || loadState('oidc_groups_mapping', 'mode', 'additive'),
			rules: parsed.rules || [],
		}
	},
	computed: {
		enabledCount() {
			return this.rules.filter(r => r.enabled !== false).length
		},
	},
}
</script>

<style scoped>
.oidc-groups-mapping-admin {
	padding: 20px;
}

.empty-state {
	color: var(--color-text-maxcontrast, #999);
	font-style: italic;
	margin-top: 16px;
}
</style>
