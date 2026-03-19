<!--
  - SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
  -->

<template>
	<div class="mode-selector">
		<label class="mode-label" for="ogm-mode">Mode:</label>
		<select id="ogm-mode" :value="mode" class="mode-select" @change="$emit('update:mode', $event.target.value)">
			<option value="additive">additive</option>
			<option value="replace">replace</option>
		</select>
		<span class="badge badge-count">{{ enabledCount }} / {{ rulesCount }} rules active</span>
	</div>
</template>

<script>
export default {
	name: 'ModeSelector',
	props: {
		mode: {
			type: String,
			required: true,
		},
		rulesCount: {
			type: Number,
			required: true,
		},
		enabledCount: {
			type: Number,
			required: true,
		},
	},
}
</script>

<style scoped>
.mode-selector {
	display: flex;
	align-items: center;
	gap: 12px;
	margin-bottom: 16px;
}

.mode-label {
	font-weight: 600;
	font-size: 14px;
}

.mode-select {
	padding: 4px 8px;
	border: 1px solid var(--color-border, #ededed);
	border-radius: var(--border-radius, 3px);
	font-size: 14px;
	text-transform: uppercase;
	font-weight: 600;
}

.badge {
	display: inline-block;
	padding: 2px 10px;
	border-radius: 12px;
	font-size: 12px;
	font-weight: 600;
	text-transform: uppercase;
}

.badge-count {
	background-color: var(--color-background-dark, #ededed);
	color: var(--color-main-text, #222);
}
</style>
