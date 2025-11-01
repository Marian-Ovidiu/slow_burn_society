<script setup lang="ts">
interface Props {
	value: boolean;
	isDisabled?: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
	toggle: [boolean];
}>();

const handleClick = (event: Event) => {
	if (props.isDisabled) return;

	event.preventDefault();
	event.stopPropagation();
	emit('toggle', !props.value);
};
</script>

<template>
	<div class="controlled-toggle__container">
		<label
			class="controlled-toggle"
			:class="{
				'controlled-toggle--active': value,
				'controlled-toggle--disabled': isDisabled
			}"
			@click="handleClick"
		>
			<input type="checkbox" :checked="value" :disabled="isDisabled" style="display: none" />
			<span class="controlled-toggle__slider"></span>
		</label>
	</div>
</template>

<style scoped lang="scss">
.controlled-toggle {
	position: relative;
	display: inline-block;
	width: 44px;
	height: 24px;
	cursor: pointer;
	margin: 0;

	&--disabled {
		cursor: not-allowed;
		opacity: 0.5;
	}

	&__slider {
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background-color: var(--neutral--300);
		border-radius: 24px;
		transition: all 0.2s ease;

		&::before {
			content: '';
			position: absolute;
			height: 20px;
			width: 20px;
			left: 2px;
			top: 2px;
			background-color: white;
			border-radius: 50%;
			transition: all 0.2s ease;
		}
	}

	&--active &__slider {
		background-color: var(--primary--500);

		&::before {
			transform: translateX(20px);
		}
	}

	&:hover:not(&--disabled) &__slider {
		background-color: var(--neutral--400);
	}

	&--active:hover:not(&--disabled) &__slider {
		background-color: var(--primary--600);
	}
}
</style>
