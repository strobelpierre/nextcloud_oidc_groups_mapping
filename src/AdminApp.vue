<!--
  - SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
  -->

<template>
	<div class="oidc-groups-mapping-admin">
		<h2>OIDC Groups Mapping</h2>

		<div class="tab-bar">
			<button :class="['tab-btn', { active: tab === 'visual' }]" @click="tab = 'visual'">
				Visual editor
			</button>
			<button :class="['tab-btn', { active: tab === 'json' }]" @click="tab = 'json'">
				JSON
			</button>
			<button :class="['tab-btn', { active: tab === 'simulator' }]" @click="tab = 'simulator'">
				Simulator
			</button>
		</div>

		<!-- Visual editor tab -->
		<template v-if="tab === 'visual'">
			<ModeSelector :mode="mode"
				:rules-count="rules.length"
				:enabled-count="enabledCount"
				@update:mode="onModeChange" />

			<RuleList :rules="rules"
				@toggle="onToggleRule"
				@delete="onDeleteRule"
				@edit="onEditRule" />

			<p v-if="rules.length === 0 && editingIndex === null" class="empty-state">
				No mapping rules configured yet.
			</p>

			<RuleEditor v-if="editingIndex !== null"
				:rule="editingRule"
				:is-new="isNewRule"
				@save="onSaveRule"
				@cancel="onCancelEdit" />

			<div class="actions-bar">
				<button class="primary" :disabled="saving" @click="onAddRule">
					+ Add rule
				</button>
				<button class="primary"
					:disabled="!dirty || saving"
					@click="onSave">
					{{ saving ? 'Saving…' : 'Save' }}
				</button>
				<span v-if="statusMessage" :class="['status-message', statusType]">
					{{ statusMessage }}
				</span>
			</div>
		</template>

		<!-- JSON fallback tab -->
		<JsonFallback v-if="tab === 'json'"
			:rules="rules"
			:mode="mode"
			@saved="onJsonSaved" />

		<!-- Simulator tab -->
		<ClaimSimulator v-if="tab === 'simulator'" />
	</div>
</template>

<script>
import { loadState } from '@nextcloud/initial-state'
import { generateOcsUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import ModeSelector from './components/ModeSelector.vue'
import RuleList from './components/RuleList.vue'
import RuleEditor from './components/RuleEditor.vue'
import JsonFallback from './components/JsonFallback.vue'
import ClaimSimulator from './components/ClaimSimulator.vue'

export default {
	name: 'AdminApp',
	components: {
		ModeSelector,
		RuleList,
		RuleEditor,
		JsonFallback,
		ClaimSimulator,
	},
	data() {
		const rulesJson = loadState('oidc_groups_mapping', 'rules', '{}')
		const parsed = typeof rulesJson === 'string' ? JSON.parse(rulesJson) : rulesJson

		return {
			tab: 'visual',
			mode: parsed.mode || loadState('oidc_groups_mapping', 'mode', 'additive'),
			rules: parsed.rules || [],
			dirty: false,
			saving: false,
			statusMessage: '',
			statusType: '',
			editingIndex: null,
			editingRule: null,
			isNewRule: false,
		}
	},
	computed: {
		enabledCount() {
			return this.rules.filter(r => r.enabled !== false).length
		},
	},
	methods: {
		onModeChange(newMode) {
			this.mode = newMode
			this.dirty = true
		},
		onToggleRule(index) {
			this.rules[index].enabled = !this.rules[index].enabled
			this.dirty = true
		},
		onDeleteRule(index) {
			this.rules.splice(index, 1)
			if (this.editingIndex === index) {
				this.editingIndex = null
				this.editingRule = null
			} else if (this.editingIndex !== null && this.editingIndex > index) {
				this.editingIndex--
			}
			this.dirty = true
		},
		onEditRule(index) {
			this.editingIndex = index
			this.editingRule = JSON.parse(JSON.stringify(this.rules[index]))
			this.isNewRule = false
		},
		onAddRule() {
			this.editingRule = {
				id: this.generateId(),
				type: 'direct',
				enabled: true,
				claimPath: '',
				config: {},
			}
			this.editingIndex = this.rules.length
			this.isNewRule = true
		},
		onSaveRule(rule) {
			if (this.isNewRule) {
				this.rules.push(rule)
			} else {
				this.$set(this.rules, this.editingIndex, rule)
			}
			this.editingIndex = null
			this.editingRule = null
			this.isNewRule = false
			this.dirty = true
		},
		onCancelEdit() {
			this.editingIndex = null
			this.editingRule = null
			this.isNewRule = false
		},
		onJsonSaved(data) {
			this.rules = data.rules || this.rules
			this.mode = data.mode || this.mode
			this.dirty = false
		},
		generateId() {
			return 'rule-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 6)
		},
		async onSave() {
			this.saving = true
			this.statusMessage = ''

			const payload = JSON.stringify({
				version: 1,
				mode: this.mode,
				rules: this.rules,
			})

			try {
				const url = generateOcsUrl('/apps/oidc_groups_mapping/api/v1/rules')
				const response = await axios.put(url, { rules: payload })
				const data = response.data.ocs?.data || response.data

				this.rules = data.rules || this.rules
				this.mode = data.mode || this.mode
				this.dirty = false
				this.showStatus('Saved!', 'success')
			} catch (e) {
				const msg = e.response?.data?.ocs?.meta?.message
					|| e.response?.data?.message
					|| e.message
					|| 'Unknown error'
				this.showStatus('Error: ' + msg, 'error')
			} finally {
				this.saving = false
			}
		},
		showStatus(message, type) {
			this.statusMessage = message
			this.statusType = type
			if (type === 'success') {
				setTimeout(() => {
					if (this.statusMessage === message) {
						this.statusMessage = ''
					}
				}, 3000)
			}
		},
	},
}
</script>

<style scoped>
.oidc-groups-mapping-admin {
	padding: 20px;
}

.tab-bar {
	display: flex;
	gap: 0;
	margin-bottom: 20px;
	border-bottom: 2px solid var(--color-border, #ededed);
}

.tab-btn {
	padding: 8px 16px;
	border: none;
	background: none;
	font-size: 14px;
	font-weight: 600;
	cursor: pointer;
	color: var(--color-text-maxcontrast, #999);
	border-bottom: 2px solid transparent;
	margin-bottom: -2px;
	transition: color 0.2s, border-color 0.2s;
}

.tab-btn:hover {
	color: var(--color-main-text, #222);
}

.tab-btn.active {
	color: var(--color-primary-element, #0082c9);
	border-bottom-color: var(--color-primary-element, #0082c9);
}

.empty-state {
	color: var(--color-text-maxcontrast, #999);
	font-style: italic;
	margin-top: 16px;
}

.actions-bar {
	display: flex;
	align-items: center;
	gap: 12px;
	margin-top: 20px;
}

.status-message {
	font-size: 13px;
	font-weight: 500;
}

.status-message.success {
	color: var(--color-success, #46ba61);
}

.status-message.error {
	color: var(--color-error, #e9322d);
}
</style>
