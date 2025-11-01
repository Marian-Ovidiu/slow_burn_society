<script setup lang="ts">
import { ref } from 'vue';

import { faqData } from '@/data/faq';
import { translate } from '@/utils/translate';

const expandedFAQ = ref<string | null>(null);

const toggleFAQ = (id: string) => {
	expandedFAQ.value = expandedFAQ.value === id ? null : id;
};
</script>

<template>
	<section class="faq" aria-labelledby="faq-heading">
		<div class="faq__container">
			<div class="faq__list">
				<HCard border-radius="20px" padding="16px 28px">
					<template #header>
						<HText id="faq-heading" as="h2" variant="heading-2">{{ translate('hostinger_reach_faq_title') }}</HText>
					</template>
					<div
						v-for="faq in faqData"
						:key="faq.id"
						class="faq__item h-mb-8"
						:class="{
							'faq__item--expanded': expandedFAQ === faq.id
						}"
					>
						<button
							:id="`faq-question-${faq.id}`"
							class="faq__question"
							:aria-expanded="expandedFAQ === faq.id"
							:aria-controls="`faq-answer-${faq.id}`"
							@click="toggleFAQ(faq.id)"
						>
							<span class="faq__question-text">{{ faq.question }}</span>
							<HIcon
								:name="expandedFAQ === faq.id ? 'ic-chevron-up-24' : 'ic-chevron-down-24'"
								size="24"
								class="faq__icon"
								aria-hidden="true"
							/>
						</button>
						<div
							v-show="expandedFAQ === faq.id"
							:id="`faq-answer-${faq.id}`"
							class="faq__answer"
							:aria-labelledby="`faq-question-${faq.id}`"
							role="region"
						>
							<div v-html="faq.answer"></div>
						</div>
					</div>
				</HCard>
			</div>
		</div>
	</section>
</template>

<style scoped lang="scss">
.faq {
	@media (max-width: 768px) {
		padding: 32px 16px;
	}

	@media (max-width: 480px) {
		padding: 24px 12px;
	}

	&__container {
		max-width: 780px;
		margin: 0 auto;

		@media (max-width: 1024px) {
			max-width: 90%;
		}

		@media (max-width: 768px) {
			max-width: 100%;
		}
	}

	&__list {
		display: flex;
		flex-direction: column;
		gap: 8px;

		@media (max-width: 480px) {
			gap: 6px;
		}
	}

	&__item {
		background: var(--neutral--0);
		border-radius: 16px;
		border: 1px solid var(--neutral--200);
		overflow: hidden;
		transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);

		@media (max-width: 768px) {
			border-radius: 12px;
		}

		@media (max-width: 480px) {
			border-radius: 8px;
		}

		&--expanded {
			border-color: var(--neutral--200);
			box-shadow: 0 4px 12px rgba(103, 58, 183, 0.1);

			@media (max-width: 768px) {
				box-shadow: 0 2px 8px rgba(103, 58, 183, 0.1);
			}

			@media (max-width: 480px) {
				box-shadow: 0 1px 6px rgba(103, 58, 183, 0.1);
			}
		}
	}

	&__question {
		width: 100%;
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 24px;
		background: none;
		border: none;
		text-align: left;
		cursor: pointer;
		transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
		border-bottom: 1px solid var(--neutral--200);

		@media (max-width: 768px) {
			padding: 16px 20px;
		}

		@media (max-width: 480px) {
			padding: 12px 16px;
		}
	}

	&__question-text {
		font-size: 16px;
		font-weight: 600;
		color: var(--neutral--600);
		line-height: 1.4;
		flex: 1;
		margin-right: 16px;

		@media (max-width: 768px) {
			font-size: 15px;
			margin-right: 12px;
		}

		@media (max-width: 480px) {
			font-size: 14px;
			margin-right: 10px;
		}
	}

	&__icon {
		color: var(--neutral--400);
		transition: color 0.3s cubic-bezier(0.4, 0, 0.2, 1);
		flex-shrink: 0;

		@media (max-width: 480px) {
			width: 20px;
			height: 20px;
		}

		.faq__item--expanded & {
			color: var(--neutral--200);
		}
	}

	&__answer {
		padding: 24px;
		animation: fadeIn 0.3s ease-in-out;

		@media (max-width: 768px) {
			padding: 0 20px 20px;
		}

		@media (max-width: 480px) {
			padding: 0 16px 16px;
		}

		p {
			font-size: 15px;
			line-height: 1.6;
			color: var(--neutral--500);
			margin: 0;

			@media (max-width: 768px) {
				font-size: 14px;
			}

			@media (max-width: 480px) {
				font-size: 13px;
			}
		}
	}
}

:deep(.h-card__header) {
	border-bottom: none;
	padding-top: 24px;
	padding-bottom: 0px;

	@media (max-width: 768px) {
		padding-top: 20px;
	}

	@media (max-width: 480px) {
		padding-top: 16px;
	}
}

@keyframes fadeIn {
	from {
		opacity: 0;
		transform: translateY(-10px);
	}
	to {
		opacity: 1;
		transform: translateY(0);
	}
}
</style>
