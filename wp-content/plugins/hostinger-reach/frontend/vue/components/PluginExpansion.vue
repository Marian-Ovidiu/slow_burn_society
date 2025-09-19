<script setup lang="ts">
import FormItem from '@/components/FormItem.vue';
import type { Form } from '@/types/models';
import { toKebabCase } from '@/utils/caseConversion';
import { translate } from '@/utils/translate';

interface Props {
	pluginId: string;
	forms: Form[];
}

const { pluginId, forms } = defineProps<Props>();

const emit = defineEmits<{
	toggleFormStatus: [form: Form, status: boolean];
	viewForm: [form: Form];
	editForm: [form: Form];
}>();
</script>

<template>
	<div :id="`plugin-expansion-${toKebabCase(pluginId)}`" class="plugin-expansion-content">
		<div v-if="forms.length > 0" class="plugin-expansion-content__forms-list">
			<FormItem
				v-for="(form, index) in forms"
				:key="form.formId"
				:form="form"
				:class="{
					'plugin-expansion-content__form-item--with-top-spacing': forms.length > 1 && index !== 0,
					'plugin-expansion-content__form-item--with-bottom-spacing': forms.length > 1 && index !== forms.length - 1
				}"
				@toggle-status="(form, status) => emit('toggleFormStatus', form, status)"
				@view-form="(form) => emit('viewForm', form)"
				@edit-form="(form) => emit('editForm', form)"
			/>
		</div>
		<div v-else class="plugin-expansion-content__no-forms">
			<span class="plugin-expansion-content__no-forms-text">
				{{ translate('hostinger_reach_plugin_expansion_no_forms') }}
			</span>
		</div>
	</div>
</template>

<style scoped lang="scss">
.plugin-expansion-content {
	background-color: var(--neutral--50);
	border-radius: 12px;
	padding: 16px;
	display: flex;
	flex-direction: column;
	gap: 4px;
	box-sizing: border-box;

	&__forms-list {
		display: flex;
		flex-direction: column;
		gap: 4px;
	}

	&__form-item--with-top-spacing {
		padding-top: 16px;
	}

	&__form-item--with-bottom-spacing {
		padding-bottom: 16px;
	}

	&__no-forms {
		display: flex;
		justify-content: center;
		align-items: center;
	}

	&__no-forms-text {
		font-size: 14px;
		color: var(--neutral--500);
		text-align: center;
	}
}
</style>
