<script setup lang="ts">
import { HIcon, HLabel, HPopover } from '@hostinger/hcomponents';
import { computed, ref } from 'vue';

import PluginExpansion from '@/components/PluginExpansion.vue';
import { INTEGRATION_TO_FORM_TYPE_MAP, PLUGIN_IDS, PLUGIN_STATUSES, type PluginStatus } from '@/data/pluginData';
import type { Form } from '@/types/models';
import { translate } from '@/utils/translate';

interface Props {
	pluginId: string;
	pluginName: string;
	pluginIcon: string;
	pluginStatus: PluginStatus;
	totalEntries: number;
	showAddForm: boolean;
	forms: Form[];
}

const props = defineProps<Props>();

const emit = defineEmits<{
	toggleFormStatus: [form: Form, status: boolean];
	goToPlugin: [id: string];
	disconnectPlugin: [id: string];
	viewForm: [form: Form];
	editForm: [form: Form];
	addForm: [id: string];
}>();

const isExpanded = ref(false);

const toggleExpansion = () => {
	isExpanded.value = !isExpanded.value;
};

const getStatusLabel = (status: PluginStatus) =>
	status === PLUGIN_STATUSES.ACTIVE
		? translate('hostinger_reach_plugin_entries_table_status_active')
		: translate('hostinger_reach_plugin_entries_table_status_inactive');

const getStatusColor = (status: PluginStatus) => (status === PLUGIN_STATUSES.ACTIVE ? 'success' : 'gray');

const handleToggleFormStatus = (form: Form, status: boolean) => {
	emit('toggleFormStatus', form, status);
};

const handleViewForm = (form: Form) => {
	emit('viewForm', form);
};

const handleEditForm = (form: Form) => {
	emit('editForm', form);
};

const expandButtonAriaLabel = computed(() => {
	const translationKey = isExpanded.value
		? 'hostinger_reach_plugin_entries_table_collapse_aria'
		: 'hostinger_reach_plugin_entries_table_expand_aria';

	return translate(translationKey).replace('{pluginName}', props.pluginName);
});
</script>

<template>
	<div class="plugin-entry-row">
		<div class="plugin-entry-row__main">
			<div class="plugin-entry-row__cell plugin-entry-row__cell--plugin">
				<div class="plugin-entry-row__plugin-content" @click="toggleExpansion">
					<button
						class="plugin-entry-row__expand-button"
						:aria-expanded="isExpanded"
						:aria-controls="`plugin-expansion-${pluginId}`"
						:aria-label="expandButtonAriaLabel"
						@click.stop="toggleExpansion"
					>
						<HIcon :name="isExpanded ? 'ic-chevron-down-16' : 'ic-chevron-right-16'" dimensions="16px" />
					</button>
					<div class="plugin-entry-row__plugin-info">
						<div class="plugin-entry-row__plugin-icon">
							<img :src="pluginIcon" :alt="pluginName" />
						</div>
						<span class="plugin-entry-row__plugin-name">{{ pluginName }}</span>
					</div>
				</div>
			</div>
			<div class="plugin-entry-row__cell plugin-entry-row__cell--entries">
				<span class="plugin-entry-row__mobile-label">
					{{ translate('hostinger_reach_plugin_entries_table_entries_header') }}:
				</span>
				<span class="plugin-entry-row__entries-count">{{ totalEntries }}</span>
			</div>
			<div class="plugin-entry-row__cell plugin-entry-row__cell--status">
				<span class="plugin-entry-row__mobile-label">
					{{ translate('hostinger_reach_plugin_entries_table_status_header') }}:
				</span>
				<div class="plugin-entry-row__status-content">
					<HLabel variant="outline" :color="getStatusColor(pluginStatus)" class="plugin-entry-row__status-label">
						{{ getStatusLabel(pluginStatus) }}
					</HLabel>
				</div>
			</div>
			<div class="plugin-entry-row__cell plugin-entry-row__cell--actions">
				<HPopover
					v-if="INTEGRATION_TO_FORM_TYPE_MAP[pluginId] !== PLUGIN_IDS.HOSTINGER_REACH"
					placement="bottom-end"
					:show-arrow="false"
					background-color="neutral--0"
					border-radius="12px"
					:outside-click-enabled="true"
				>
					<template #trigger>
						<button class="plugin-entry-row__action-button">
							<HIcon name="ic-dots-vertical-16" />
						</button>
					</template>
					<div class="plugin-entry-row__popover-menu">
						<div v-if="props.showAddForm" class="plugin-entry-row__menu-item" @click="emit('addForm', props.pluginId)">
							<HIcon name="ic-plus-16" />
							<span>{{ translate('hostinger_reach_plugin_entries_table_add_form') }}</span>
							<HIcon name="ic-arrow-up-right-square-16" />
						</div>
						<div class="plugin-entry-row__menu-item" @click="emit('goToPlugin', props.pluginId)">
							<HIcon name="ic-blocks-plus-16" />
							<span>{{ translate('hostinger_reach_plugin_entries_table_go_to_plugin') }}</span>
							<HIcon name="ic-arrow-up-right-square-16" />
						</div>
						<div class="plugin-entry-row__menu-item" @click="emit('disconnectPlugin', props.pluginId)">
							<HIcon name="ic-cross-circle-16" />
							<span>{{ translate('hostinger_reach_plugin_entries_table_disconnect_plugin') }}</span>
						</div>
					</div>
				</HPopover>
			</div>
		</div>
		<div v-if="isExpanded" class="plugin-entry-row__expansion">
			<PluginExpansion
				:plugin-id="pluginId"
				:forms="forms"
				@toggle-form-status="handleToggleFormStatus"
				@view-form="handleViewForm"
				@edit-form="handleEditForm"
			/>
		</div>
	</div>
</template>

<style scoped lang="scss">
.plugin-entry-row {
	&__main {
		display: flex;
		padding: 16px 16px 16px 0;
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
			order: 4;
		}
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

	&__mobile-label {
		font-weight: 500;
		font-size: 14px;
		color: var(--neutral--600);
		margin-right: 8px;
		display: none;
	}

	&__expansion {
		width: 100%;
		height: 100%;
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
		&__main {
			flex-direction: column;
			gap: 12px;
			padding: 16px;
			border-radius: 12px;
			background: var(--neutral--100);
			margin-bottom: 12px;
		}

		&__cell--plugin,
		&__cell--entries,
		&__cell--status,
		&__cell--actions {
			width: 100%;
			justify-content: flex-start;
		}

		&__cell--entries,
		&__cell--status {
			align-items: flex-start;
		}

		&__cell--actions {
			padding-right: 0;
			justify-content: flex-end;
		}

		&__mobile-label {
			display: inline-block;
		}
	}
}
</style>
