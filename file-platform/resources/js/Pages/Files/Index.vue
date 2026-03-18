<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useTrans } from '@/lib/i18n';

const props = defineProps({
    currentFolder: {
        type: Object,
        default: null,
    },
    breadcrumbs: {
        type: Array,
        required: true,
    },
    folderOptionGroups: {
        type: Array,
        required: true,
    },
    folders: {
        type: Array,
        required: true,
    },
    files: {
        type: Array,
        required: true,
    },
    pagination: {
        type: Object,
        required: true,
    },
    sort: {
        type: Object,
        required: true,
    },
    query: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const { trans } = useTrans();

const folderForm = useForm({
    parent_id: props.query.folder_id,
    name: '',
});

const uploadForm = useForm({
    folder_id: props.query.folder_id,
    file: null,
});
const searchForm = useForm({
    search: props.query.search ?? '',
});
const sortForm = useForm({
    sort_by: props.sort.by,
    sort_direction: props.sort.direction,
});
const isDraggingFile = ref(false);

const selectedUploadFileName = computed(() => uploadForm.file?.name ?? trans('files.no_file_selected'));
const selectedUploadFileSize = computed(() => uploadForm.file?.size ? formatSize(uploadForm.file.size) : trans('files.no_file_size'));
const currentLocationLabel = computed(() => props.currentFolder?.path ?? trans('files.root'));
const uploadTargetLabel = computed(() => {
    const selectedFolder = props.folderOptionGroups
        .flatMap((group) => group.options)
        .find((folder) => folder.id === uploadForm.folder_id);

    return selectedFolder?.label ?? trans('files.root');
});
const hasActiveSearch = computed(() => Boolean((props.query.search ?? '').trim()));

const setUploadFile = (file) => {
    uploadForm.file = file ?? null;
};

const createFolder = () => {
    folderForm.post(route('folders.store'), {
        preserveScroll: true,
        onSuccess: () => folderForm.reset('name'),
    });
};

const uploadFile = () => {
    uploadForm.post(route('files.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => uploadForm.reset('file'),
    });
};

const submitSearch = () => {
    router.get(route('files.index'), {
        folder_id: props.query.folder_id ?? undefined,
        search: searchForm.search?.trim() || undefined,
        sort_by: sortForm.sort_by !== 'name' ? sortForm.sort_by : undefined,
        sort_direction: sortForm.sort_direction !== 'asc' ? sortForm.sort_direction : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearSearch = () => {
    searchForm.search = '';
    submitSearch();
};

const submitSort = () => {
    router.get(route('files.index'), {
        folder_id: props.query.folder_id ?? undefined,
        search: props.query.search ?? undefined,
        sort_by: sortForm.sort_by !== 'name' ? sortForm.sort_by : undefined,
        sort_direction: sortForm.sort_direction !== 'asc' ? sortForm.sort_direction : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const handleFileDrop = (event) => {
    isDraggingFile.value = false;
    setUploadFile(event.dataTransfer?.files?.[0] ?? null);
};

const deleteFolder = (folderId) => {
    if (!window.confirm(trans('files.delete_folder_confirm'))) {
        return;
    }

    useForm({}).delete(route('folders.destroy', folderId), {
        preserveScroll: true,
    });
};

const renameFolder = (folder) => {
    const name = window.prompt(trans('files.rename_folder_prompt'), folder.name);

    if (name === null) {
        return;
    }

    useForm({
        name,
    }).patch(route('folders.update', folder.id), {
        preserveScroll: true,
    });
};

const deleteFile = (fileId) => {
    if (!window.confirm(trans('files.delete_file_confirm'))) {
        return;
    }

    useForm({}).delete(route('files.destroy', fileId), {
        preserveScroll: true,
    });
};

const renameFile = (file) => {
    const name = window.prompt(trans('files.rename_file_prompt'), file.original_name);

    if (name === null) {
        return;
    }

    useForm({
        name,
    }).patch(route('files.update', file.id), {
        preserveScroll: true,
    });
};

const formatSize = (size) => {
    if (!size) {
        return '0 B';
    }

    const units = ['B', 'KB', 'MB', 'GB'];
    let value = size;
    let unitIndex = 0;

    while (value >= 1024 && unitIndex < units.length - 1) {
        value /= 1024;
        unitIndex += 1;
    }

    return `${value.toFixed(value >= 10 || unitIndex === 0 ? 0 : 1)} ${units[unitIndex]}`;
};
</script>

<template>
    <Head :title="trans('files.title')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        {{ trans('files.title') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ trans('files.subtitle') }}
                    </p>
                </div>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div
                    v-if="page.props.flash.success"
                    class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
                >
                    {{ page.props.flash.success }}
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                        <Link
                            v-for="breadcrumb in breadcrumbs"
                            :key="breadcrumb.id ?? 'root'"
                            :href="route('files.index', {
                                folder_id: breadcrumb.id ?? undefined,
                                sort_by: sortForm.sort_by !== 'name' ? sortForm.sort_by : undefined,
                                sort_direction: sortForm.sort_direction !== 'asc' ? sortForm.sort_direction : undefined,
                            })"
                            class="rounded-md px-2 py-1 hover:bg-slate-100 hover:text-slate-900"
                        >
                            {{ breadcrumb.name }}
                        </Link>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-slate-900">
                            {{ trans('files.create_folder') }}
                        </h3>
                        <form class="mt-4 space-y-3" @submit.prevent="createFolder">
                            <TextInput
                                v-model="folderForm.name"
                                type="text"
                                class="block w-full"
                                :placeholder="trans('files.create_folder_placeholder')"
                            />
                            <InputError :message="folderForm.errors.name || folderForm.errors.parent_id" />
                            <PrimaryButton :disabled="folderForm.processing">
                                {{ trans('files.create_folder_submit') }}
                            </PrimaryButton>
                        </form>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-slate-900">
                            {{ trans('files.upload_file') }}
                        </h3>
                        <form class="mt-4 space-y-3" @submit.prevent="uploadFile">
                            <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                        {{ trans('files.upload_target') }}
                                    </p>
                                    <p class="mt-1 text-sm font-medium text-slate-700">
                                        {{ uploadTargetLabel }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                    {{ trans('files.target_folder_select') }}
                                </label>
                                <select
                                    v-model="uploadForm.folder_id"
                                    class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100"
                                >
                                    <optgroup
                                        v-for="group in folderOptionGroups"
                                        :key="group.label"
                                        :label="group.label"
                                    >
                                        <option
                                            v-for="folder in group.options"
                                            :key="`${group.label}-${folder.id ?? 'root'}`"
                                            :value="folder.id"
                                        >
                                            {{ folder.label }}
                                        </option>
                                    </optgroup>
                                </select>
                            </div>

                            <label
                                class="group block cursor-pointer rounded-2xl border-2 border-dashed px-5 py-6 transition"
                                :class="isDraggingFile
                                    ? 'border-sky-500 bg-sky-50'
                                    : 'border-slate-300 bg-slate-50 hover:border-slate-400 hover:bg-slate-100'"
                                @dragenter.prevent="isDraggingFile = true"
                                @dragover.prevent="isDraggingFile = true"
                                @dragleave.prevent="isDraggingFile = false"
                                @drop.prevent="handleFileDrop"
                            >
                                <div class="flex flex-col items-center text-center">
                                    <div
                                        class="inline-flex h-14 w-14 items-center justify-center rounded-2xl transition"
                                        :class="isDraggingFile ? 'bg-sky-100 text-sky-700' : 'bg-slate-900 text-white'"
                                    >
                                        <svg
                                            class="h-7 w-7"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <path d="M14 3H8a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V9l-4-6Z" />
                                            <path d="M14 3v6h6" />
                                            <path d="M12 11v6" />
                                            <path d="M9.5 14.5 12 12l2.5 2.5" />
                                        </svg>
                                    </div>

                                    <p class="mt-4 text-sm font-semibold text-slate-900">
                                        {{ trans('files.drop_file_here') }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ trans('files.or_click_to_choose') }}
                                    </p>

                                    <div class="mt-5 w-full rounded-xl bg-white/80 px-4 py-3 shadow-sm ring-1 ring-slate-200">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="min-w-0 text-left">
                                                <p class="truncate text-sm font-medium text-slate-800">
                                                    {{ selectedUploadFileName }}
                                                </p>
                                                <p class="mt-1 text-xs text-slate-500">
                                                    {{ selectedUploadFileSize }}
                                                </p>
                                            </div>
                                            <span class="inline-flex shrink-0 items-center rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white">
                                                {{ trans('files.choose_file') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <input
                                    type="file"
                                    class="hidden"
                                    @change="setUploadFile($event.target.files[0] ?? null)"
                                >
                            </label>
                            <InputError :message="uploadForm.errors.file || uploadForm.errors.folder_id" />
                            <p class="text-xs text-slate-500">
                                {{ trans('files.upload_hint') }}
                            </p>
                            <PrimaryButton :disabled="uploadForm.processing">
                                {{ trans('files.upload_submit') }}
                            </PrimaryButton>
                        </form>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">
                        {{ trans('files.search_title') }}
                    </h3>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ trans('files.search_description') }}
                    </p>

                    <form class="mt-4 flex flex-col gap-3 md:flex-row" @submit.prevent="submitSearch">
                        <TextInput
                            v-model="searchForm.search"
                            type="text"
                            class="block w-full"
                            :placeholder="trans('files.search_placeholder')"
                        />
                        <div class="flex gap-3">
                            <PrimaryButton :disabled="searchForm.processing">
                                {{ trans('files.search_submit') }}
                            </PrimaryButton>
                            <button
                                v-if="hasActiveSearch"
                                type="button"
                                class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100"
                                @click="clearSearch"
                            >
                                {{ trans('files.search_clear') }}
                            </button>
                        </div>
                    </form>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">
                        {{ trans('files.sort_title') }}
                    </h3>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ trans('files.sort_description') }}
                    </p>

                    <form class="mt-4 grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]" @submit.prevent="submitSort">
                        <select
                            v-model="sortForm.sort_by"
                            class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100"
                        >
                            <option value="name">{{ trans('files.sort_name') }}</option>
                            <option value="created_at">{{ trans('files.sort_created_at') }}</option>
                            <option value="size">{{ trans('files.sort_size') }}</option>
                        </select>
                        <select
                            v-model="sortForm.sort_direction"
                            class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100"
                        >
                            <option value="asc">{{ trans('files.sort_asc') }}</option>
                            <option value="desc">{{ trans('files.sort_desc') }}</option>
                        </select>
                        <PrimaryButton :disabled="sortForm.processing">
                            {{ trans('files.sort_submit') }}
                        </PrimaryButton>
                    </form>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-4">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">
                                    {{ trans('files.current_location') }}
                                </h3>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ currentLocationLabel }}
                                </p>
                            </div>

                            <p class="text-sm text-slate-500">
                                {{ trans('files.pagination_summary', { from: props.pagination.from, to: props.pagination.to, total: props.pagination.total }) }}
                            </p>
                        </div>

                        <p v-if="hasActiveSearch" class="mt-3 text-sm text-sky-700">
                            {{ trans('files.search_results', { keyword: props.query.search }) }}
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3 text-left font-medium text-slate-500">{{ trans('files.name') }}</th>
                                    <th class="px-6 py-3 text-left font-medium text-slate-500">{{ trans('files.type') }}</th>
                                    <th class="px-6 py-3 text-left font-medium text-slate-500">{{ trans('files.size') }}</th>
                                    <th class="px-6 py-3 text-left font-medium text-slate-500">{{ trans('files.created_at') }}</th>
                                    <th class="px-6 py-3 text-right font-medium text-slate-500">{{ trans('files.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <tr v-if="folders.length === 0 && files.length === 0">
                                    <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                                        {{ trans('files.empty') }}
                                    </td>
                                </tr>

                                <tr v-for="folder in folders" :key="`folder-${folder.id}`" class="hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                            <Link
                                                :href="route('files.index', {
                                                    folder_id: folder.id,
                                                    sort_by: sortForm.sort_by !== 'name' ? sortForm.sort_by : undefined,
                                                    sort_direction: sortForm.sort_direction !== 'asc' ? sortForm.sort_direction : undefined,
                                                })"
                                                class="font-medium text-slate-900 hover:text-sky-700"
                                            >
                                                {{ folder.name }}
                                        </Link>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500">{{ trans('files.folder') }}</td>
                                    <td class="px-6 py-4 text-slate-500">-</td>
                                    <td class="px-6 py-4 text-slate-500">{{ folder.created_at }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link
                                                :href="route('files.index', {
                                                    folder_id: folder.id,
                                                    sort_by: sortForm.sort_by !== 'name' ? sortForm.sort_by : undefined,
                                                    sort_direction: sortForm.sort_direction !== 'asc' ? sortForm.sort_direction : undefined,
                                                })"
                                                class="inline-flex items-center rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100"
                                            >
                                                {{ trans('files.open_folder') }}
                                            </Link>
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100"
                                                @click="renameFolder(folder)"
                                            >
                                                {{ trans('files.rename') }}
                                            </button>
                                            <DangerButton @click="deleteFolder(folder.id)">
                                                {{ trans('files.delete') }}
                                            </DangerButton>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-for="file in files" :key="`file-${file.id}`" class="hover:bg-slate-50">
                                    <td class="px-6 py-4 font-medium text-slate-900">
                                        {{ file.original_name }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-500">
                                        {{ file.extension || file.mime_type }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-500">
                                        {{ formatSize(file.size) }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-500">{{ file.created_at }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <a
                                                :href="file.download_url"
                                                class="inline-flex items-center rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100"
                                            >
                                                {{ trans('files.download') }}
                                            </a>
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100"
                                                @click="renameFile(file)"
                                            >
                                                {{ trans('files.rename') }}
                                            </button>
                                            <DangerButton @click="deleteFile(file.id)">
                                                {{ trans('files.delete') }}
                                            </DangerButton>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div
                        v-if="props.pagination.links.length > 0"
                        class="flex flex-col gap-3 border-t border-slate-200 px-6 py-4 md:flex-row md:items-center md:justify-between"
                    >
                        <p class="text-sm text-slate-500">
                            {{ trans('files.pagination_page', { current: props.pagination.current_page, last: props.pagination.last_page }) }}
                        </p>

                        <div class="flex flex-wrap items-center gap-2">
                            <template v-for="link in props.pagination.links" :key="`${link.label}-${link.url ?? 'disabled'}`">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    preserve-scroll
                                    class="inline-flex min-w-10 items-center justify-center rounded-md border px-3 py-2 text-sm font-semibold transition"
                                    :class="link.active
                                        ? 'border-slate-900 bg-slate-900 text-white'
                                        : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-100'"
                                >
                                    {{ link.label }}
                                </Link>

                                <span
                                    v-else
                                    class="inline-flex min-w-10 cursor-not-allowed items-center justify-center rounded-md border border-slate-200 bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-400"
                                >
                                    {{ link.label }}
                                </span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
