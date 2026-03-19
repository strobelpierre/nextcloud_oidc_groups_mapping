<!--
  - SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
  -->

<template>
	<div class="json-fallback">
		<h3>JSON editor</h3>
		<p class="json-hint">
			Edit the raw JSON configuration directly. Changes are applied when you save.
		</p>

		<textarea v-model="jsonText"
			:class="{ 'json-error': !!jsonError }"
			rows="15"
			spellcheck="false" />

		<p v-if="jsonError" class="error-message">
			{{ jsonError }}
		</p>

		<div class="json-actions">
			<button class="primary" :disabled="!dirty || !!jsonError || saving" @click="onSave">
				{{ saving ? 'Saving…' : 'Save JSON' }}
			</button>
			<button :disabled="!dirty" @click="onReset">
				Reset
			</button>
		</div>
	</div>
</template>

<script>
import { generateOcsUrl } from '@nextcloud/router'
import { showSuccess, showError } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'

export default {
	name: 'JsonFallback',
	props: {
		rules: {
			type: Array,
			required: true,
		},
		mode: {
			type: String,
			required: true,
		},
	},
	data() {
		const initial = JSON.stringify({ version: 1, mode: this.mode, rules: this.rules }, null, 2)
		return {
			jsonText: initial,
			savedText: initial,
			saving: false,
		}
	},
	computed: {
		dirty() {
			return this.jsonText !== this.savedText
		},
		jsonError() {
			try {
				const parsed = JSON.parse(this.jsonText)
				if (typeof parsed !== 'object' || parsed === null) {
					return 'Root must be an object'
				}
				return ''
			} catch (e) {
				return e.message
			}
		},
	},
	watch: {
		rules: {
			handler() {
				this.syncFromProps()
			},
			deep: true,
		},
		mode() {
			this.syncFromProps()
		},
	},
	methods: {
		syncFromProps() {
			if (!this.dirty) {
				const text = JSON.stringify({ version: 1, mode: this.mode, rules: this.rules }, null, 2)
				this.jsonText = text
				this.savedText = text
			}
		},
		onReset() {
			this.jsonText = this.savedText
		},
		async onSave() {
			this.saving = true

			try {
				const url = generateOcsUrl('/apps/oidc_groups_mapping/api/v1/rules')
				const response = await axios.put(url, { rules: this.jsonText })
				const data = response.data.ocs?.data || response.data

				this.savedText = this.jsonText
				this.$emit('saved', data)
				showSuccess('Rules saved from JSON editor')
			} catch (e) {
				const msg = e.response?.data?.ocs?.data?.message
					|| e.message
					|| 'Save failed'
				showError('Failed to save: ' + msg)
			} finally {
				this.saving = false
			}
		},
	},
}
</script>

<style scoped>
.json-fallback {
	margin-top: 20px;
}

.json-hint {
	font-size: 13px;
	color: var(--color-text-maxcontrast);
	margin-bottom: 12px;
}

.json-fallback textarea {
	width: 100%;
	font-family: monospace;
	font-size: 13px;
	padding: 8px;
	border: 2px solid var(--color-border);
	border-radius: var(--border-radius, 3px);
	box-sizing: border-box;
	transition: border-color 0.2s ease;
	background: var(--color-main-background);
	color: var(--color-main-text);
}

.json-fallback textarea:focus {
	border-color: var(--color-primary-element);
}

.json-fallback textarea.json-error {
	border-color: var(--color-error);
}

.error-message {
	color: var(--color-error);
	font-size: 12px;
	margin-top: 4px;
}

.json-actions {
	display: flex;
	align-items: center;
	gap: 12px;
	margin-top: 12px;
}
</style>
