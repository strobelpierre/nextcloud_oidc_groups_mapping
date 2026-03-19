<!--
  - SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
  -->

<template>
	<div class="rule-list">
		<RuleCard v-for="(rule, index) in rules"
			:key="rule.id"
			:rule="rule"
			@toggle="$emit('toggle', index)"
			@delete="$emit('delete', index)"
			@edit="$emit('edit', index)" />
	</div>
</template>

<script>
import RuleCard from './RuleCard.vue'

export default {
	name: 'RuleList',
	components: {
		RuleCard,
	},
	props: {
		rules: {
			type: Array,
			required: true,
		},
	},
}
</script>

<style scoped>
.rule-list {
	display: flex;
	flex-direction: column;
	gap: 12px;
}
</style>
