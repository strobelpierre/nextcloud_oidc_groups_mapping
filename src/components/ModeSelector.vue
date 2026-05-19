<!--
  - SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
  -->

<template>
	<div class="mode-selector">
		<div class="mode-selector__row">
			<label class="mode-label" for="ogm-mode">Mode:</label>
			<select id="ogm-mode" :value="mode" class="mode-select" @change="$emit('update:mode', $event.target.value)">
				<option value="additive">additive</option>
				<option value="replace">replace</option>
			</select>
			<span class="badge badge-count">{{ enabledCount }} / {{ rulesCount }} rules active</span>
		</div>
		<div class="mode-selector__row preserve-row">
			<input id="ogm-preserve"
				type="checkbox"
				:checked="preserveDisplayName"
				@change="$emit('update:preserveDisplayName', $event.target.checked)">
			<label for="ogm-preserve" class="preserve-label">
				Preserve existing group display names
			</label>
			<span class="preserve-hint">
				Emits <code>{gid}</code> objects so user_oidc skips <code>setDisplayName</code>. Use this if you rename groups in Nextcloud after creation.
			</span>
		</div>
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
		preserveDisplayName: {
			type: Boolean,
			default: false,
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
	flex-direction: column;
	gap: 8px;
	margin-bottom: 16px;
}

.mode-selector__row {
	display: flex;
	align-items: center;
	gap: 12px;
}

.preserve-row {
	font-size: 13px;
}

.preserve-label {
	font-weight: 600;
	color: var(--color-main-text);
	cursor: pointer;
}

.preserve-hint {
	color: var(--color-text-maxcontrast);
	font-size: 12px;
}

.preserve-hint code {
	font-family: monospace;
	font-size: 11px;
	background: var(--color-background-dark);
	padding: 1px 4px;
	border-radius: 3px;
}

.mode-label {
	font-weight: 600;
	font-size: 14px;
	color: var(--color-main-text);
}

.mode-select {
	padding: 4px 8px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius, 3px);
	font-size: 14px;
	text-transform: uppercase;
	font-weight: 600;
	background: var(--color-main-background);
	color: var(--color-main-text);
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
	background-color: var(--color-background-dark);
	color: var(--color-main-text);
}
</style>
