<!--
  - SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
  -->

<template>
	<div class="rule-card" :class="{ disabled: !rule.enabled }">
		<div class="rule-header">
			<span class="rule-type badge">{{ rule.type }}</span>
			<span class="rule-claim">{{ rule.claimPath }}</span>
			<span v-if="!rule.enabled" class="badge badge-disabled">disabled</span>
			<div class="rule-actions">
				<button class="action-btn" :title="rule.enabled ? 'Disable' : 'Enable'" @click="$emit('toggle')">
					{{ rule.enabled ? 'Disable' : 'Enable' }}
				</button>
				<button class="action-btn" title="Edit" @click="$emit('edit')">
					Edit
				</button>
				<button class="action-btn action-btn--danger" title="Delete" @click="$emit('delete')">
					Delete
				</button>
			</div>
		</div>
		<div class="rule-summary">
			{{ summary }}
		</div>
	</div>
</template>

<script>
export default {
	name: 'RuleCard',
	props: {
		rule: {
			type: Object,
			required: true,
		},
	},
	computed: {
		summary() {
			const config = this.rule.config || {}
			switch (this.rule.type) {
			case 'direct':
				return 'Maps claim values directly as group names'
			case 'prefix':
				return `Adds prefix '${config.prefix || ''}' to claim values`
			case 'map': {
				const mappings = config.values || {}
				const count = Object.keys(mappings).length
				const policy = config.unmappedPolicy || 'ignore'
				return `${count} value mapping${count !== 1 ? 's' : ''} (unmapped: ${policy})`
			}
			case 'conditional': {
				const groups = config.groups || []
				const operator = config.operator || 'equals'
				const value = config.value || ''
				return `If ${operator} '${value}' → assigns ${groups.length} group${groups.length !== 1 ? 's' : ''}`
			}
			case 'template':
				return `Template: ${config.template || '(empty)'}`
			default:
				return `Unknown rule type: ${this.rule.type}`
			}
		},
	},
}
</script>

<style scoped>
.rule-card {
	border: 1px solid var(--color-border, #ededed);
	border-radius: var(--border-radius-large, 10px);
	padding: 16px;
	background-color: var(--color-main-background, #fff);
	transition: opacity 0.2s ease;
}

.rule-card.disabled {
	opacity: 0.6;
}

.rule-header {
	display: flex;
	align-items: center;
	gap: 8px;
	margin-bottom: 8px;
}

.rule-type {
	background-color: var(--color-primary-element-light, #e8f0fe);
	color: var(--color-primary-element, #0082c9);
}

.rule-claim {
	font-family: monospace;
	font-size: 13px;
	color: var(--color-text-maxcontrast, #999);
}

.badge {
	display: inline-block;
	padding: 2px 10px;
	border-radius: 12px;
	font-size: 12px;
	font-weight: 600;
	text-transform: uppercase;
}

.badge-disabled {
	background-color: var(--color-background-dark, #ededed);
	color: var(--color-text-maxcontrast, #999);
}

.rule-actions {
	margin-left: auto;
	display: flex;
	gap: 6px;
}

.action-btn {
	padding: 4px 10px;
	font-size: 12px;
	border: 1px solid var(--color-border, #ededed);
	border-radius: var(--border-radius, 3px);
	background: var(--color-main-background, #fff);
	cursor: pointer;
}

.action-btn:hover {
	background: var(--color-background-hover, #f5f5f5);
}

.action-btn--danger {
	color: var(--color-error, #e9322d);
	border-color: var(--color-error, #e9322d);
}

.action-btn--danger:hover {
	background: var(--color-error, #e9322d);
	color: #fff;
}

.rule-summary {
	font-size: 14px;
	color: var(--color-main-text, #222);
}
</style>
