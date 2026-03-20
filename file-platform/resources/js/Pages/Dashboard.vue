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
const usagePercentage = computed(() => Number(props.quota.usage_percentage ?? 0));

const usageBarClass = computed(() => {
    if (usagePercentage.value >= 90) return 'bg-rose-500';
    if (usagePercentage.value >= 75) return 'bg-amber-500';
    return 'bg-sky-600';
});

const statCards = computed(() => [
    {
        key: 'files',
        label: trans('dashboard.stats_files'),
        value: formatNumber(props.metrics.total_files),
        icon: 'file',
        color: 'text-slate-600 bg-slate-50',
    },
    {
        key: 'folders',
        label: trans('dashboard.stats_folders'),
        value: formatNumber(props.metrics.total_folders),
        icon: 'folder',
        color: 'text-slate-600 bg-slate-50',
    },
    {
        key: 'storage',
        label: trans('dashboard.stats_storage'),
        value: props.metrics.used_bytes_label,
        icon: 'storage',
        color: 'text-slate-600 bg-slate-50',
    },
]);

function formatNumber(value) {
    return new Intl.NumberFormat().format(value ?? 0);
}

function formatFileSize(bytes) {
    if (!bytes) return '0 B';
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    let size = bytes;
    let unitIndex = 0;
    while (size >= 1024 && unitIndex < units.length - 1) {
        size /= 1024;
        unitIndex += 1;
    }
    return `${size.toFixed(size >= 10 || unitIndex === 0 ? 0 : 1)} ${units[unitIndex]}`;
}

function formatDate(value) {
    if (!value) return '-';
    return new Intl.DateTimeFormat(undefined, {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value));
}

function getFileIcon(extension) {
    const ext = (extension || '').toLowerCase();
    if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].includes(ext)) return 'image';
    if (['pdf'].includes(ext)) return 'pdf';
    if (['zip', 'rar', '7z', 'tar', 'gz'].includes(ext)) return 'archive';
    return 'file';
}
</script>

<template>
    <Head :title="trans('nav.dashboard')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-slate-900">
                        {{ trans('common.welcome') }}, {{ page.props.auth.user.name }}
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ trans('dashboard.subtitle') }}
                    </p>
                </div>
                <div class="hidden sm:block">
                    <Link
                        :href="route('files.index')"
                        class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ trans('dashboard.go_files') }}
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <div
                        v-for="card in statCards"
                        :key="card.key"
                        class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:shadow-md"
                    >
                        <div class="flex items-center gap-6">
                            <div :class="['rounded-2xl p-4', card.color]">
                                <svg v-if="card.icon === 'file'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <svg v-if="card.icon === 'folder'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                                <svg v-if="card.icon === 'storage'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ card.label }}</p>
                                <p class="mt-1 text-2xl font-bold text-slate-900">{{ card.value }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Grid -->
                <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-3">
                    <!-- Quota Card -->
                    <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm lg:col-span-1">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-slate-900">{{ trans('dashboard.quota_title') }}</h3>
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-600">{{ usagePercentage }}%</span>
                        </div>
                        
                        <div class="mt-8">
                            <div class="relative h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                                <div
                                    class="h-full rounded-full transition-all duration-700 ease-in-out"
                                    :class="usageBarClass"
                                    :style="{ width: `${Math.min(usagePercentage, 100)}%` }"
                                />
                            </div>
                        </div>

                        <div class="mt-10 space-y-5">
                            <div class="flex items-center justify-between border-b border-slate-50 pb-4">
                                <span class="text-sm font-medium text-slate-500">{{ trans('dashboard.quota_limit') }}</span>
                                <span class="text-sm font-bold text-slate-900">{{ props.quota.limit_bytes_label }}</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-slate-50 pb-4">
                                <span class="text-sm font-medium text-slate-500">{{ trans('dashboard.quota_usage') }}</span>
                                <span class="text-sm font-bold text-slate-900">{{ props.quota.used_bytes_label }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-slate-500">{{ trans('dashboard.quota_remaining') }}</span>
                                <span class="text-sm font-bold text-sky-600">{{ props.quota.remaining_bytes_label }}</span>
                            </div>
                        </div>

                        <div class="mt-10 flex gap-4 rounded-2xl bg-slate-50 p-5 border border-slate-100">
                            <svg class="h-5 w-5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-xs leading-relaxed text-slate-500 font-medium">
                                {{ trans('dashboard.quota_description') }}
                            </p>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
                        <div class="flex items-center justify-between border-b border-slate-100 px-8 py-6">
                            <h3 class="text-lg font-bold text-slate-900">{{ trans('dashboard.recent_uploads_title') }}</h3>
                            <Link
                                :href="route('files.index')"
                                class="text-xs font-bold uppercase tracking-wider text-slate-400 hover:text-slate-900 transition"
                            >
                                {{ trans('dashboard.go_files') }}
                            </Link>
                        </div>

                        <div class="divide-y divide-slate-50 px-8">
                            <div
                                v-if="recentUploads.length === 0"
                                class="py-20 text-center"
                            >
                                <svg class="mx-auto h-16 w-16 text-slate-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                                </svg>
                                <p class="mt-5 text-sm text-slate-400 font-medium">{{ trans('dashboard.recent_uploads_empty') }}</p>
                            </div>

                            <div
                                v-for="upload in recentUploads"
                                :key="upload.id"
                                class="flex items-center justify-between py-6 transition hover:bg-slate-50/70"
                            >
                                <div class="flex items-center gap-5 min-w-0">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-50 text-slate-400 border border-slate-100">
                                        <svg v-if="getFileIcon(upload.extension) === 'image'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <svg v-else-if="getFileIcon(upload.extension) === 'pdf'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="truncate text-[15px] font-bold text-slate-900 leading-tight">{{ upload.original_name }}</h4>
                                        <div class="mt-1 flex items-center gap-2 text-[12px] font-medium text-slate-400">
                                            <span>{{ formatFileSize(upload.size) }}</span>
                                            <span class="opacity-30">•</span>
                                            <span class="truncate">{{ upload.folder_path || trans('files.root') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-6 shrink-0 pl-4">
                                    <span class="hidden text-[13px] font-medium text-slate-400 sm:block">{{ formatDate(upload.created_at) }}</span>
                                    <a
                                        :href="upload.download_url"
                                        class="rounded-xl p-3 text-slate-400 transition hover:bg-white hover:text-slate-900 border border-transparent hover:border-slate-200 hover:shadow-sm"
                                    >
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50/30 px-8 py-5 rounded-b-[24px] border-t border-slate-50">
                            <div class="flex flex-wrap gap-x-8 gap-y-2 text-[11px] font-bold uppercase tracking-widest text-slate-400">
                                <span>{{ trans('dashboard.quick_start_step_1') }}</span>
                                <span>{{ trans('dashboard.quick_start_step_2') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
