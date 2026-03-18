<script setup>
import Dropdown from '@/Components/Dropdown.vue';
import { useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    compact: {
        type: Boolean,
        default: false,
    },
    mode: {
        type: String,
        default: 'segmented',
    },
});

const page = usePage();

const localeForm = useForm({
    locale: page.props.locale.current,
});

const switchLocale = (locale) => {
    if (locale === page.props.locale.current || localeForm.processing) {
        return;
    }

    localeForm.locale = locale;
    localeForm.post(route('locale.update'), {
        preserveScroll: true,
        preserveState: true,
    });
};

const shortLabelMap = {
    en: 'EN',
    zh_TW: '中',
};
</script>

<template>
    <Dropdown
        v-if="mode === 'dropdown'"
        align="right"
        width="48"
        content-classes="py-1 bg-white"
    >
        <template #trigger>
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:border-slate-300 hover:text-slate-900 focus:outline-none"
            >
                <svg
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <circle cx="12" cy="12" r="9" />
                    <path d="M3 12h18" />
                    <path d="M12 3c2.5 2.7 4 5.8 4 9s-1.5 6.3-4 9c-2.5-2.7-4-5.8-4-9s1.5-6.3 4-9Z" />
                </svg>
                <span>{{ shortLabelMap[page.props.locale.current] ?? page.props.locale.current }}</span>
                <svg
                    class="h-4 w-4 text-slate-400"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.512a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06Z"
                        clip-rule="evenodd"
                    />
                </svg>
            </button>
        </template>

        <template #content>
            <div class="px-1 py-1">
                <button
                    v-for="(label, locale) in page.props.locale.available"
                    :key="locale"
                    type="button"
                    class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition"
                    :class="locale === page.props.locale.current ? 'bg-slate-100 font-semibold text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                    :disabled="localeForm.processing"
                    @click="switchLocale(locale)"
                >
                    <span class="flex items-center gap-2">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-700">
                            {{ shortLabelMap[locale] ?? locale }}
                        </span>
                        <span>{{ label }}</span>
                    </span>
                    <svg
                        v-if="locale === page.props.locale.current"
                        class="h-4 w-4 text-slate-900"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M16.704 5.29a1 1 0 0 1 .006 1.414l-8 8a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 1.414-1.414L8 12.586l7.296-7.296a1 1 0 0 1 1.408 0Z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </button>
            </div>
        </template>
    </Dropdown>

    <div
        v-else
        class="inline-flex items-center rounded-full border border-slate-200 bg-white/90 p-1 shadow-sm ring-1 ring-slate-100 backdrop-blur"
        :class="compact ? 'gap-1' : 'gap-1.5'"
    >
        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500">
            <svg
                class="h-4 w-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <circle cx="12" cy="12" r="9" />
                <path d="M3 12h18" />
                <path d="M12 3c2.5 2.7 4 5.8 4 9s-1.5 6.3-4 9c-2.5-2.7-4-5.8-4-9s1.5-6.3 4-9Z" />
            </svg>
        </span>
        <button
            v-for="(label, locale) in page.props.locale.available"
            :key="locale"
            type="button"
            class="rounded-full font-semibold uppercase tracking-[0.08em] transition-all duration-150"
            :class="[
                compact ? 'px-3 py-1.5 text-xs' : 'px-3.5 py-2 text-sm',
                locale === page.props.locale.current
                    ? 'bg-slate-900 text-white shadow-sm'
                    : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900',
            ]"
            :disabled="localeForm.processing"
            @click="switchLocale(locale)"
        >
            {{ shortLabelMap[locale] ?? label }}
        </button>
    </div>
</template>
