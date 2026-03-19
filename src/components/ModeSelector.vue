<!--
  - SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
  -->

<template>
	<div class="mode-selector">
		<span class="badge badge-mode">{{ mode }}</span>
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

.badge {
	display: inline-block;
	padding: 2px 10px;
	border-radius: 12px;
	font-size: 12px;
	font-weight: 600;
	text-transform: uppercase;
}

.badge-mode {
	background-color: var(--color-primary-element-light, #e8f0fe);
	color: var(--color-primary-element, #0082c9);
}

.badge-count {
	background-color: var(--color-background-dark, #ededed);
	color: var(--color-main-text, #222);
}
</style>
