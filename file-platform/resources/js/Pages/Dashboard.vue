<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useTrans } from '@/lib/i18n';

const props = defineProps({
    metrics: {
        type: Object,
        required: true,
    },
    quota: {
        type: Object,
        required: true,
    },
    recentUploads: {
        type: Array,
        required: true,
    },
});

const { trans } = useTrans();
const page = usePage();

const statCards = computed(() => [
    {
        key: 'files',
        eyebrow: trans('dashboard.stats_files'),
        value: formatNumber(props.metrics.total_files),
        hint: trans('dashboard.stats_files_hint'),
    },
    {
        key: 'folders',
        eyebrow: trans('dashboard.stats_folders'),
        value: formatNumber(props.metrics.total_folders),
        hint: trans('dashboard.stats_folders_hint'),
    },
    {
        key: 'storage',
        eyebrow: trans('dashboard.stats_storage'),
        value: props.metrics.used_bytes_label,
        hint: trans('dashboard.stats_storage_hint'),
    },
]);

function formatNumber(value) {
    return new Intl.NumberFormat().format(value ?? 0);
}

function formatFileSize(bytes) {
    if (!bytes) {
        return '0 B';
    }

    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    let size = bytes;
    let unitIndex = 0;

    while (size >= 1024 && unitIndex < units.length - 1) {
        size /= 1024;
        unitIndex += 1;
    }

    const digits = size >= 10 || unitIndex === 0 ? 0 : 1;

    return `${size.toFixed(digits)} ${units[unitIndex]}`;
}

function formatDate(value) {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat(undefined, {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value));
}

function resolveFolderLabel(upload) {
    return upload.folder_path ?? trans('files.root');
}
</script>

<template>
    <Head :title="trans('nav.dashboard')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">
                        {{ trans('dashboard.welcome') }}
                    </p>
                    <h2 class="mt-2 text-2xl font-semibold leading-tight text-slate-900">
                        {{ page.props.auth.user.name }}
                    </h2>
                    <p class="mt-2 max-w-2xl text-sm text-slate-500">
                        {{ trans('dashboard.subtitle') }}
                    </p>
                </div>

                <div class="flex gap-3">
                    <Link
                        :href="route('files.index')"
                        class="inline-flex items-center rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white"
                    >
                        {{ trans('dashboard.go_files') }}
                    </Link>
                    <Link
                        :href="route('profile.edit')"
                        class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700"
                    >
                        {{ trans('dashboard.go_profile') }}
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-600">
                                {{ trans('dashboard.metrics_title') }}
                            </p>
                            <h3 class="mt-2 text-xl font-semibold text-slate-900">
                                {{ trans('dashboard.metrics_description') }}
                            </h3>
                        </div>
                        <p class="text-sm text-slate-500">
                            {{ trans('dashboard.quick_start_title') }}
                        </p>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        <article
                            v-for="card in statCards"
                            :key="card.key"
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-5"
                        >
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">
                                {{ card.eyebrow }}
                            </p>
                            <p class="mt-4 text-3xl font-semibold text-slate-900">
                                {{ card.value }}
                            </p>
                            <p class="mt-2 text-sm text-slate-500">
                                {{ card.hint }}
                            </p>
                        </article>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">
                                    {{ trans('dashboard.quota_title') }}
                                </p>
                                <p class="mt-2 text-sm text-slate-600">
                                    {{ trans('dashboard.quota_description') }}
                                </p>
                            </div>
                            <p class="text-sm font-semibold text-slate-700">
                                {{ trans('dashboard.quota_usage') }} {{ props.quota.usage_percentage }}%
                            </p>
                        </div>

                        <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-200">
                            <div
                                class="h-full rounded-full bg-sky-600 transition-all"
                                :style="{ width: `${Math.min(props.quota.usage_percentage, 100)}%` }"
                            />
                        </div>

                        <div class="mt-4 grid gap-4 md:grid-cols-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                    {{ trans('dashboard.stats_storage') }}
                                </p>
                                <p class="mt-1 text-lg font-semibold text-slate-900">
                                    {{ props.quota.used_bytes_label }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                    {{ trans('dashboard.quota_limit') }}
                                </p>
                                <p class="mt-1 text-lg font-semibold text-slate-900">
                                    {{ props.quota.limit_bytes_label }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                    {{ trans('dashboard.quota_remaining') }}
                                </p>
                                <p class="mt-1 text-lg font-semibold text-slate-900">
                                    {{ props.quota.remaining_bytes_label }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="grid gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(320px,0.9fr)]">
                    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">
                                    {{ trans('dashboard.section_recent_uploads') }}
                                </p>
                                <h3 class="mt-2 text-lg font-semibold text-slate-900">
                                    {{ trans('dashboard.recent_uploads_title') }}
                                </h3>
                            </div>
                            <Link
                                :href="route('files.index')"
                                class="text-sm font-semibold text-slate-700 underline decoration-slate-300 underline-offset-4"
                            >
                                {{ trans('dashboard.go_files') }}
                            </Link>
                        </div>

                        <div
                            v-if="recentUploads.length === 0"
                            class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-5 py-10 text-center text-sm text-slate-500"
                        >
                            {{ trans('dashboard.recent_uploads_empty') }}
                        </div>

                        <div v-else class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                    <tr>
                                        <th class="px-4 py-3">{{ trans('files.name') }}</th>
                                        <th class="px-4 py-3">{{ trans('files.current_location') }}</th>
                                        <th class="px-4 py-3">{{ trans('files.size') }}</th>
                                        <th class="px-4 py-3">{{ trans('files.created_at') }}</th>
                                        <th class="px-4 py-3 text-right">{{ trans('files.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    <tr v-for="upload in recentUploads" :key="upload.id" class="hover:bg-slate-50">
                                        <td class="px-4 py-4">
                                            <div class="font-medium text-slate-900">{{ upload.original_name }}</div>
                                            <div class="mt-1 text-xs uppercase tracking-[0.12em] text-slate-400">
                                                {{ upload.extension || upload.mime_type || trans('dashboard.file_type_unknown') }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-slate-600">
                                            {{ resolveFolderLabel(upload) }}
                                        </td>
                                        <td class="px-4 py-4 text-slate-600">
                                            {{ formatFileSize(upload.size) }}
                                        </td>
                                        <td class="px-4 py-4 text-slate-600">
                                            {{ formatDate(upload.created_at) }}
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <a
                                                :href="upload.download_url"
                                                class="font-semibold text-slate-900 underline decoration-slate-300 underline-offset-4"
                                            >
                                                {{ trans('files.download') }}
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="space-y-6">
                        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">
                                {{ trans('dashboard.section_files') }}
                            </p>
                            <h3 class="mt-3 text-lg font-semibold text-slate-900">
                                {{ trans('dashboard.files_title') }}
                            </h3>
                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                {{ trans('dashboard.files_description') }}
                            </p>
                            <Link
                                :href="route('files.index')"
                                class="mt-5 inline-flex items-center rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white"
                            >
                                {{ trans('dashboard.go_files') }}
                            </Link>
                        </div>

                        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">
                                {{ trans('dashboard.section_account') }}
                            </p>
                            <h3 class="mt-3 text-lg font-semibold text-slate-900">
                                {{ trans('dashboard.account_title') }}
                            </h3>
                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                {{ trans('dashboard.account_description') }}
                            </p>
                            <Link
                                :href="route('profile.edit')"
                                class="mt-5 inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700"
                            >
                                {{ trans('dashboard.go_profile') }}
                            </Link>
                        </div>

                        <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-6 shadow-sm">
                            <h3 class="text-lg font-semibold text-slate-900">
                                {{ trans('dashboard.quick_start_title') }}
                            </h3>
                            <div class="mt-4 space-y-2 text-sm text-slate-600">
                                <p>{{ trans('dashboard.quick_start_step_1') }}</p>
                                <p>{{ trans('dashboard.quick_start_step_2') }}</p>
                                <p>{{ trans('dashboard.quick_start_step_3') }}</p>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
