<script setup lang="ts">
import { computed } from 'vue';

import PluginEntry from '@/components/PluginEntry.vue';
import PluginEntrySkeleton from '@/components/skeletons/PluginEntrySkeleton.vue';
import { getPluginInfo, PLUGIN_STATUSES, type PluginStatus } from '@/data/pluginData';
import type { Form, Integration } from '@/types/models';
import { translate } from '@/utils/translate';

interface PluginEntryData {
	id: string;
	name: string;
	icon: string;
	entries: number;
	showAddForm: boolean;
	status: PluginStatus;
	forms: Form[];
}

interface Props {
	integrations?: Integration[];
	isLoading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
	integrations: () => [],
	isLoading: false
});

const emit = defineEmits<{
	goToPlugin: [id: string];
	disconnectPlugin: [id: string];
	toggleFormStatus: [form: Form, status: boolean];
	viewForm: [form: Form];
	editForm: [form: Form];
	addForm: [id: string];
}>();

const SKELETON_COUNT = 3;

const pluginEntries = computed((): PluginEntryData[] => {
	if (props.isLoading) return [];

	return props.integrations
		.filter((integration) => integration.isPluginActive)
		.map((integration) => {
			const pluginInfo = getPluginInfo(integration);
			const forms = integration.forms || [];
			const totalSubmissions = forms.reduce((sum, form) => sum + (form.submissions || 0), 0);

			return {
				id: integration.id,
				name: integration.title,
				icon: pluginInfo.icon || '',
				entries: totalSubmissions,
				status: integration.isActive ? PLUGIN_STATUSES.ACTIVE : PLUGIN_STATUSES.INACTIVE,
				forms,
				showAddForm: !!integration.addFormUrl
			} as PluginEntryData;
		})
		.filter((pluginEntry) => pluginEntry.status === PLUGIN_STATUSES.ACTIVE);
});
</script>

<template>
	<div class="plugin-entries-table">
		<div class="plugin-entries-table__container">
			<div class="plugin-entries-table__header">
				<div class="plugin-entries-table__header-cell plugin-entries-table__header-cell--plugin">
					<span class="plugin-entries-table__column-title">
						{{ translate('hostinger_reach_plugin_entries_table_plugin_header') }}
					</span>
				</div>
				<div class="plugin-entries-table__header-cell plugin-entries-table__header-cell--entries">
					<span class="plugin-entries-table__column-title">
						{{ translate('hostinger_reach_plugin_entries_table_entries_header') }}
					</span>
				</div>
				<div class="plugin-entries-table__header-cell plugin-entries-table__header-cell--status">
					<span class="plugin-entries-table__column-title">
						{{ translate('hostinger_reach_plugin_entries_table_status_header') }}
					</span>
				</div>
				<div class="plugin-entries-table__header-cell plugin-entries-table__header-cell--actions" />
			</div>

			<template v-if="isLoading">
				<PluginEntrySkeleton v-for="i in SKELETON_COUNT" :key="`skeleton-${i}`" />
			</template>

			<template v-else>
				<PluginEntry
					v-for="(pluginEntry, index) in pluginEntries"
					:key="pluginEntry.id"
					:plugin-id="pluginEntry.id"
					:plugin-name="pluginEntry.name"
					:plugin-icon="pluginEntry.icon"
					:plugin-status="pluginEntry.status"
					:show-add-form="pluginEntry.showAddForm"
					:total-entries="pluginEntry.entries"
					:forms="pluginEntry.forms"
					:class="{
						'plugin-entries-table__entry-row--with-spacing':
							pluginEntries.length > 1 && index !== pluginEntries.length - 1
					}"
					@toggle-form-status="(form: Form, status: boolean) => emit('toggleFormStatus', form, status)"
					@view-form="emit('viewForm', $event)"
					@edit-form="emit('editForm', $event)"
					@add-form="emit('addForm', $event)"
					@go-to-plugin="emit('goToPlugin', $event)"
					@disconnect-plugin="emit('disconnectPlugin', $event)"
				/>
			</template>
		</div>
	</div>
</template>

<style scoped lang="scss">
.plugin-entries-table {
	width: inherit;

	&__container {
		background: var(--neutral--0);
		border-radius: 20px;
		padding: 24px;
		border: 1px solid var(--neutral--200);
		overflow: hidden;
	}

	&__header {
		display: flex;
	}

	&__header-cell {
		padding: 12px 0px;

		&--plugin {
			width: 49%;
			order: 1;
			padding-right: 16px;
		}

		&--entries {
			width: 19%;
			order: 2;
		}

		&--status {
			width: 21%;
			order: 3;
		}

		&--actions {
			width: 10%;
			order: 4;
		}
	}

	&__column-title {
		font-weight: 700;
		font-size: 14px;
		color: var(--neutral--600);
	}

	&__row {
		display: flex;
		padding: 16px 0px;
	}

	&__cell {
		display: flex;
		align-items: center;

		&--plugin {
			width: 50%;
			order: 1;
		}

		&--entries {
			width: 20%;
			order: 2;
		}

		&--status {
			width: 20%;
			order: 3;
		}

		&--actions {
			width: 10%;
			display: flex;
			justify-content: flex-end;
			padding-right: 16px;
			order: 4;
		}
	}

	&__plugin-content {
		display: flex;
		align-items: center;
		gap: 8px;
		cursor: pointer;
		width: 100%;
	}

	&__expand-button {
		background: none;
		border: none;
		cursor: pointer;
		padding: 0;
		display: flex;
		align-items: center;
		justify-content: center;
		width: 16px;
		height: 16px;
	}

	&__plugin-info {
		display: flex;
		align-items: center;
		gap: 12px;
	}

	&__plugin-icon {
		width: 28px;
		height: 28px;
		border-radius: 7px;
		overflow: hidden;
		display: flex;
		align-items: center;
		justify-content: center;
		background: var(--neutral--100);

		img {
			width: 100%;
			height: 100%;
			object-fit: contain;
		}
	}

	&__plugin-name {
		font-weight: 700;
		font-size: 14px;
		color: var(--neutral--500);
	}

	&__entries-count {
		font-weight: 400;
		font-size: 14px;
		color: var(--neutral--500);
	}

	&__status-content {
		display: flex;
		align-items: center;
	}

	&__status-label {
		font-size: 12px;
	}

	&__action-button {
		background: var(--neutral--0);
		border: 1px solid var(--neutral--200);
		border-radius: 8px;
		padding: 0 8px;
		height: 32px;
		display: flex;
		align-items: center;
		justify-content: center;
		cursor: pointer;
		transition: all 0.2s ease;

		&:hover {
			border-color: var(--neutral--300);
		}
	}

	&__expansion-row {
		width: 100%;
	}

	&__expansion-content {
		background-color: var(--neutral--50);
		border-radius: 12px;
		padding: 16px 0;
		display: flex;
		flex-direction: column;
		gap: 8px;
	}

	&__plugin-toggle-row {
		display: flex;
		align-items: center;

		.plugin-entries-table__cell--plugin {
			width: 50%;
			padding-left: 16px;
		}

		.plugin-entries-table__cell--entries {
			width: 20%;
		}

		.plugin-entries-table__cell--status {
			width: 20%;
		}

		.plugin-entries-table__cell--actions {
			width: 10%;
			display: flex;
			justify-content: flex-end;
			padding-right: 16px;
		}
	}

	&__forms-list {
		display: flex;
		flex-direction: column;
		gap: 4px;
	}

	&__form-item {
		display: flex;
		align-items: center;
		padding: 16px 0px;
		border-top: 1px solid var(--neutral--200);

		&:first-child {
			border-top: none;
		}

		.plugin-entries-table__cell--plugin {
			width: 50%;
			padding-left: 32px;
		}

		.plugin-entries-table__cell--entries {
			width: 20%;
		}

		.plugin-entries-table__cell--status {
			width: 20%;
		}

		.plugin-entries-table__cell--actions {
			width: 10%;
			display: flex;
			justify-content: flex-end;
			padding-right: 16px;
		}
	}

	&__form-info {
		display: flex;
		flex-direction: column;
		gap: 2px;
	}

	&__form-id {
		font-weight: 500;
		font-size: 12px;
		color: var(--neutral--600);
		font-family: monospace;
	}

	&__form-title {
		font-weight: 400;
		font-size: 11px;
		color: var(--neutral--500);
		opacity: 0.8;
	}

	&__toggle-container {
		display: flex;
		align-items: center;
		gap: 16px;
	}

	&__toggle-label {
		font-weight: 400;
		font-size: 14px;
		color: var(--neutral--500);
	}

	&__entries-text {
		font-weight: 400;
		font-size: 14px;
		color: var(--neutral--500);
	}

	&__popover-menu {
		padding: 4px;
		min-width: 180px;
	}

	&__menu-item {
		display: flex;
		align-items: center;
		gap: 8px;
		padding: 12px;
		cursor: pointer;
		border-radius: 8px;
		font-weight: 500;
		font-size: 14px;
		color: var(--neutral--600);
		transition: background-color 0.2s ease;

		&:hover {
			background-color: var(--neutral--50);
		}

		span {
			flex: 1;
		}
	}

	@media (max-width: 1023px) {
		&__header {
			display: none;
		}
	}
}
</style>
