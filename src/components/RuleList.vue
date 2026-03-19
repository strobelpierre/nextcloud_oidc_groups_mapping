<!--
  - SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
  -->

<template>
	<div class="rule-list">
		<RuleCard v-for="rule in rules" :key="rule.id" :rule="rule" />
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
