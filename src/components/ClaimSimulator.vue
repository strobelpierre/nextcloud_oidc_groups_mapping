<!--
  - SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
  -->

<template>
	<div class="claim-simulator">
		<h3>Claim simulator</h3>
		<p class="simulator-hint">
			Paste a sample JWT token payload to test your rules.
		</p>

		<div class="form-row">
			<label for="sim-token">Token claims (JSON)</label>
			<textarea id="sim-token"
				v-model="tokenJson"
				placeholder='{"department":"Engineering","roles":["admin","editor"]}'
				rows="6" />
		</div>

		<div class="form-row">
			<label for="sim-existing">Existing groups (JSON array, optional)</label>
			<input id="sim-existing"
				v-model="existingJson"
				type="text"
				placeholder='["users"]' />
		</div>

		<div class="sim-actions">
			<button class="primary" :disabled="!tokenJson.trim() || simulating" @click="onSimulate">
				{{ simulating ? 'Simulating…' : 'Simulate' }}
			</button>
		</div>

		<div v-if="error" class="sim-error">
			{{ error }}
		</div>

		<div v-if="result" class="sim-results">
			<div class="result-section">
				<h4>Final groups ({{ result.mode }} mode)</h4>
				<div v-if="result.finalGroups.length" class="group-list">
					<span v-for="g in result.finalGroups" :key="g" class="group-badge">{{ g }}</span>
				</div>
				<p v-else class="empty-state">
					No groups produced.
				</p>
			</div>

			<div class="result-section">
				<h4>Rule details</h4>
				<div v-for="r in result.ruleResults" :key="r.ruleId" class="rule-result">
					<span class="rule-result-id">{{ r.ruleId }}</span>
					<span :class="['badge', r.matched ? 'badge-matched' : 'badge-unmatched']">
						{{ r.matched ? 'matched' : 'no match' }}
					</span>
					<span v-if="r.matched && r.groups.length" class="rule-result-groups">
						→ {{ r.groups.join(', ') }}
					</span>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import { generateOcsUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

export default {
	name: 'ClaimSimulator',
	data() {
		return {
			tokenJson: '',
			existingJson: '[]',
			simulating: false,
			result: null,
			error: '',
		}
	},
	methods: {
		async onSimulate() {
			this.error = ''
			this.result = null
			this.simulating = true

			try {
				JSON.parse(this.tokenJson)
			} catch {
				this.error = 'Invalid JSON in token field'
				this.simulating = false
				return
			}

			try {
				const url = generateOcsUrl('/apps/oidc_groups_mapping/api/v1/simulate')
				const response = await axios.post(url, {
					token: this.tokenJson,
					existing: this.existingJson || '[]',
				})
				this.result = response.data.ocs?.data || response.data
			} catch (e) {
				this.error = e.response?.data?.ocs?.data?.message
					|| e.message
					|| 'Simulation failed'
			} finally {
				this.simulating = false
			}
		},
	},
}
</script>

<style scoped>
.claim-simulator {
	border: 1px solid var(--color-border, #ededed);
	border-radius: var(--border-radius-large, 10px);
	padding: 16px;
	margin-top: 20px;
}

.simulator-hint {
	font-size: 13px;
	color: var(--color-text-maxcontrast, #999);
	margin-bottom: 12px;
}

.form-row {
	margin-bottom: 12px;
}

.form-row label {
	display: block;
	font-weight: 600;
	font-size: 13px;
	margin-bottom: 4px;
}

.form-row textarea,
.form-row input[type="text"] {
	width: 100%;
	max-width: 500px;
	padding: 6px 8px;
	border: 1px solid var(--color-border, #ededed);
	border-radius: var(--border-radius, 3px);
	font-family: monospace;
	font-size: 13px;
	box-sizing: border-box;
}

.sim-actions {
	margin-bottom: 12px;
}

.sim-error {
	color: var(--color-error, #e9322d);
	font-size: 13px;
	margin-bottom: 12px;
}

.sim-results {
	margin-top: 16px;
}

.result-section {
	margin-bottom: 16px;
}

.result-section h4 {
	margin: 0 0 8px;
	font-size: 14px;
}

.group-list {
	display: flex;
	flex-wrap: wrap;
	gap: 6px;
}

.group-badge {
	display: inline-block;
	padding: 2px 10px;
	border-radius: 12px;
	font-size: 12px;
	font-weight: 600;
	background-color: var(--color-primary-element-light, #e8f0fe);
	color: var(--color-primary-element, #0082c9);
}

.rule-result {
	display: flex;
	align-items: center;
	gap: 8px;
	margin-bottom: 4px;
	font-size: 13px;
}

.rule-result-id {
	font-family: monospace;
	font-weight: 600;
}

.badge {
	display: inline-block;
	padding: 1px 8px;
	border-radius: 10px;
	font-size: 11px;
	font-weight: 600;
	text-transform: uppercase;
}

.badge-matched {
	background-color: #e6f4ea;
	color: var(--color-success, #46ba61);
}

.badge-unmatched {
	background-color: var(--color-background-dark, #ededed);
	color: var(--color-text-maxcontrast, #999);
}

.rule-result-groups {
	color: var(--color-main-text, #222);
}

.empty-state {
	color: var(--color-text-maxcontrast, #999);
	font-style: italic;
}
</style>
