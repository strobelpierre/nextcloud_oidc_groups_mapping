<!--
  - SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
  -->

<template>
	<div class="rule-editor">
		<h3>{{ isNew ? 'Add rule' : 'Edit rule' }}</h3>

		<div class="form-row">
			<label for="rule-type">Type</label>
			<select id="rule-type" v-model="local.type" @change="onTypeChange">
				<option value="direct">direct</option>
				<option value="prefix">prefix</option>
				<option value="map">map</option>
				<option value="conditional">conditional</option>
				<option value="template">template</option>
			</select>
		</div>

		<div class="form-row">
			<label for="rule-claim">Claim path</label>
			<input id="rule-claim"
				v-model="local.claimPath"
				type="text"
				placeholder="e.g. roles, department, extended_attrs.groups" />
		</div>

		<div class="form-row">
			<label>
				<input v-model="local.enabled" type="checkbox" />
				Enabled
			</label>
		</div>

		<!-- direct: no extra config -->

		<!-- prefix -->
		<div v-if="local.type === 'prefix'" class="form-row">
			<label for="cfg-prefix">Prefix</label>
			<input id="cfg-prefix" v-model="local.config.prefix" type="text" placeholder="e.g. role_" />
		</div>

		<!-- map -->
		<template v-if="local.type === 'map'">
			<div class="form-row">
				<label for="cfg-unmapped">Unmapped policy</label>
				<select id="cfg-unmapped" v-model="local.config.unmappedPolicy">
					<option value="ignore">ignore</option>
					<option value="passthrough">passthrough</option>
				</select>
			</div>
			<div class="form-row">
				<label>Value mappings</label>
				<div v-for="(val, key) in local.config.values" :key="key" class="mapping-row">
					<input :value="key" type="text" placeholder="claim value" readonly class="mapping-key" />
					<span class="mapping-arrow">&rarr;</span>
					<input :value="val" type="text" placeholder="group name"
						@input="updateMapping(key, $event.target.value)" />
					<button class="action-btn action-btn--danger" @click="removeMapping(key)">
						&times;
					</button>
				</div>
				<div class="mapping-row mapping-row--new">
					<input v-model="newMappingKey" type="text" placeholder="claim value" />
					<span class="mapping-arrow">&rarr;</span>
					<input v-model="newMappingValue" type="text" placeholder="group name" />
					<button class="action-btn" :disabled="!newMappingKey" @click="addMapping">
						Add
					</button>
				</div>
			</div>
		</template>

		<!-- conditional -->
		<template v-if="local.type === 'conditional'">
			<div class="form-row">
				<label for="cfg-operator">Operator</label>
				<select id="cfg-operator" v-model="local.config.operator">
					<option value="equals">equals</option>
					<option value="contains">contains</option>
					<option value="regex">regex</option>
				</select>
			</div>
			<div class="form-row">
				<label for="cfg-value">Value</label>
				<input id="cfg-value" v-model="local.config.value" type="text" placeholder="expected value" />
			</div>
			<div class="form-row">
				<label for="cfg-groups">Groups (comma-separated)</label>
				<input id="cfg-groups"
					:value="(local.config.groups || []).join(', ')"
					type="text"
					placeholder="group1, group2"
					@input="local.config.groups = $event.target.value.split(',').map(s => s.trim()).filter(Boolean)" />
			</div>
		</template>

		<!-- template -->
		<div v-if="local.type === 'template'" class="form-row">
			<label for="cfg-template">Template</label>
			<input id="cfg-template"
				v-model="local.config.template"
				type="text"
				placeholder="e.g. dept_{value}" />
		</div>

		<div class="editor-actions">
			<button class="primary" :disabled="!local.claimPath" @click="onSubmit">
				{{ isNew ? 'Add' : 'Update' }}
			</button>
			<button @click="$emit('cancel')">
				Cancel
			</button>
		</div>
	</div>
</template>

<script>
export default {
	name: 'RuleEditor',
	props: {
		rule: {
			type: Object,
			required: true,
		},
		isNew: {
			type: Boolean,
			default: false,
		},
	},
	data() {
		return {
			local: JSON.parse(JSON.stringify(this.rule)),
			newMappingKey: '',
			newMappingValue: '',
		}
	},
	watch: {
		rule: {
			handler(val) {
				this.local = JSON.parse(JSON.stringify(val))
			},
			deep: true,
		},
	},
	methods: {
		onTypeChange() {
			// Reset config to defaults for the new type
			const defaults = {
				direct: {},
				prefix: { prefix: '' },
				map: { values: {}, unmappedPolicy: 'ignore' },
				conditional: { operator: 'equals', value: '', groups: [] },
				template: { template: '{value}' },
			}
			this.local.config = defaults[this.local.type] || {}
		},
		addMapping() {
			if (!this.newMappingKey) {
				return
			}
			if (!this.local.config.values) {
				this.$set(this.local.config, 'values', {})
			}
			this.$set(this.local.config.values, this.newMappingKey, this.newMappingValue)
			this.newMappingKey = ''
			this.newMappingValue = ''
		},
		removeMapping(key) {
			this.$delete(this.local.config.values, key)
		},
		updateMapping(key, value) {
			this.$set(this.local.config.values, key, value)
		},
		onSubmit() {
			this.$emit('save', JSON.parse(JSON.stringify(this.local)))
		},
	},
}
</script>

<style scoped>
.rule-editor {
	border: 2px solid var(--color-primary-element, #0082c9);
	border-radius: var(--border-radius-large, 10px);
	padding: 16px;
	margin-top: 12px;
	background-color: var(--color-main-background, #fff);
}

.rule-editor h3 {
	margin-top: 0;
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

.form-row input[type="text"],
.form-row select {
	width: 100%;
	max-width: 400px;
	padding: 6px 8px;
	border: 1px solid var(--color-border, #ededed);
	border-radius: var(--border-radius, 3px);
	font-size: 14px;
	box-sizing: border-box;
}

.form-row input[type="checkbox"] {
	margin-right: 6px;
}

.mapping-row {
	display: flex;
	align-items: center;
	gap: 8px;
	margin-bottom: 6px;
}

.mapping-row input {
	max-width: 180px !important;
}

.mapping-key {
	background-color: var(--color-background-dark, #f5f5f5);
}

.mapping-arrow {
	font-size: 16px;
	color: var(--color-text-maxcontrast, #999);
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

.editor-actions {
	display: flex;
	gap: 8px;
	margin-top: 16px;
}
</style>
