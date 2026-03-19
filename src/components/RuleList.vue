<!--
  - SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
  -->

<template>
	<div class="rule-list">
		<RuleCard v-for="(rule, index) in rules"
			:key="rule.id"
			:rule="rule"
			:index="index"
			:dragging="dragIndex === index"
			:drag-over="dragOverIndex === index"
			@toggle="$emit('toggle', index)"
			@delete="$emit('delete', index)"
			@edit="$emit('edit', index)"
			@dragstart.native="onDragStart(index, $event)"
			@dragover.native.prevent="onDragOver(index)"
			@dragleave.native="onDragLeave"
			@drop.native.prevent="onDrop(index)"
			@dragend.native="onDragEnd" />
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
	data() {
		return {
			dragIndex: null,
			dragOverIndex: null,
		}
	},
	methods: {
		onDragStart(index, event) {
			this.dragIndex = index
			event.dataTransfer.effectAllowed = 'move'
			event.dataTransfer.setData('text/plain', String(index))
		},
		onDragOver(index) {
			if (this.dragIndex !== null && this.dragIndex !== index) {
				this.dragOverIndex = index
			}
		},
		onDragLeave() {
			this.dragOverIndex = null
		},
		onDrop(index) {
			if (this.dragIndex !== null && this.dragIndex !== index) {
				this.$emit('reorder', { from: this.dragIndex, to: index })
			}
			this.dragIndex = null
			this.dragOverIndex = null
		},
		onDragEnd() {
			this.dragIndex = null
			this.dragOverIndex = null
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
