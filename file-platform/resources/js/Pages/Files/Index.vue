<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
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

const showUploadModal = ref(false);
const showFolderModal = ref(false);

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
const selectedUploadFileSize = computed(() => uploadForm.file?.size ? formatSize(uploadForm.file.size) : '');
const currentLocationLabel = computed(() => props.currentFolder?.path ?? trans('files.root'));
const totalEntries = computed(() => (props.folders?.length ?? 0) + (props.files?.length ?? 0));
const hasActiveSearch = computed(() => Boolean((props.query.search ?? '').trim()));

const setUploadFile = (file) => {
    uploadForm.file = file ?? null;
};

const createFolder = () => {
    folderForm.post(route('folders.store'), {
        preserveScroll: true,
        onSuccess: () => {
            folderForm.reset('name');
            showFolderModal.value = false;
        },
    });
};

const uploadFile = () => {
    uploadForm.post(route('files.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            uploadForm.reset('file');
            showUploadModal.value = false;
        },
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

const deleteFolder = (folderId) => {
    if (!window.confirm(trans('files.delete_folder_confirm'))) return;
    useForm({}).delete(route('folders.destroy', folderId), { preserveScroll: true });
};

const renameFolder = (folder) => {
    const name = window.prompt(trans('files.rename_folder_prompt'), folder.name);
    if (name === null) return;
    useForm({ name }).patch(route('folders.update', folder.id), { preserveScroll: true });
};

const deleteFile = (fileId) => {
    if (!window.confirm(trans('files.delete_file_confirm'))) return;
    useForm({}).delete(route('files.destroy', fileId), { preserveScroll: true });
};

const renameFile = (file) => {
    const name = window.prompt(trans('files.rename_file_prompt'), file.original_name);
    if (name === null) return;
    useForm({ name }).patch(route('files.update', file.id), { preserveScroll: true });
};

const formatSize = (size) => {
    if (!size) return '-';
    const units = ['B', 'KB', 'MB', 'GB'];
    let value = size;
    let unitIndex = 0;
    while (value >= 1024 && unitIndex < units.length - 1) {
        value /= 1024;
        unitIndex += 1;
    }
    return `${value.toFixed(value >= 10 || unitIndex === 0 ? 0 : 1)} ${units[unitIndex]}`;
};

function getFileIcon(extension) {
    const ext = (extension || '').toLowerCase();
    if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].includes(ext)) return 'image';
    if (['pdf'].includes(ext)) return 'pdf';
    if (['zip', 'rar', '7z', 'tar', 'gz'].includes(ext)) return 'archive';
    return 'file';
}

const handleFileDrop = (event) => {
    isDraggingFile.value = false;
    setUploadFile(event.dataTransfer?.files?.[0] ?? null);
};
</script>

<template>
    <Head :title="trans('files.title')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900 leading-tight">
                        {{ trans('files.title') }}
                    </h2>
                    <nav class="mt-2.5 flex items-center gap-1.5 text-[13px] font-bold text-slate-400">
                        <template v-for="(crumb, index) in breadcrumbs" :key="crumb.id ?? 'root'">
                            <span v-if="index > 0" class="opacity-30">/</span>
                            <Link
                                :href="route('files.index', { folder_id: crumb.id ?? undefined })"
                                class="transition hover:text-sky-600 px-1 py-0.5 rounded hover:bg-slate-50"
                                :class="index === breadcrumbs.length - 1 ? 'text-sky-600' : ''"
                            >
                                {{ crumb.name }}
                            </Link>
                        </template>
                    </nav>
                </div>
                <div class="flex gap-3">
                    <button
                        @click="showFolderModal = true"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:border-slate-300 active:scale-[0.97]"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10.5v6m3-3H9m4.062-12.602a2 2 0 01.67 1.113l.318 1.489A2 2 0 0015.46 8H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h4l.562-1.124A2 2 0 0113.337 3h3.725z" />
                        </svg>
                        <span class="hidden sm:inline">{{ trans('files.create_folder') }}</span>
                    </button>
                    <button
                        @click="showUploadModal = true"
                        class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-800 hover:shadow active:scale-[0.97]"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span class="hidden sm:inline">{{ trans('files.upload_file') }}</span>
                    </button>
                </div>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                
                <!-- Main Content Area -->
                <div class="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
                    <!-- Toolbar -->
                    <div class="flex flex-col gap-6 border-b border-slate-100 bg-slate-50/20 px-8 py-6 sm:flex-row sm:items-center sm:justify-between">
                        <form @submit.prevent="submitSearch" class="relative">
                            <svg class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input
                                v-model="searchForm.search"
                                type="text"
                                class="h-11 rounded-xl border-slate-200 pl-12 pr-10 text-[14px] font-medium shadow-inner focus:border-sky-500 focus:ring-sky-500 sm:w-96"
                                :placeholder="trans('files.search_placeholder')"
                            />
                            <button v-if="searchForm.search" @click="clearSearch" type="button" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 hover:text-slate-500 transition">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </form>
                        <div class="flex items-center gap-5">
                             <div class="flex items-center gap-3">
                                <span class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-300">{{ trans('files.sort_title') }}</span>
                                <select v-model="sortForm.sort_by" @change="submitSearch" class="h-10 rounded-xl border-slate-200 bg-white py-0 pl-4 pr-10 text-[13px] font-bold text-slate-600 shadow-sm focus:ring-sky-500 transition hover:border-slate-300">
                                    <option value="name">{{ trans('files.sort_name') }}</option>
                                    <option value="created_at">{{ trans('files.sort_created_at') }}</option>
                                    <option value="size">{{ trans('files.sort_size') }}</option>
                                </select>
                             </div>
                             <button @click="sortForm.sort_direction = (sortForm.sort_direction === 'asc' ? 'desc' : 'asc'); submitSearch()" class="rounded-xl border border-slate-200 bg-white p-2.5 transition hover:bg-slate-50 hover:border-slate-300 shadow-sm">
                                <svg v-if="sortForm.sort_direction === 'asc'" class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" /></svg>
                                <svg v-else class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M3 4h13M3 8h9m-9 4h6m4 0l4 4m0 0l-4-4m4 4v-12" /></svg>
                             </button>
                        </div>
                    </div>

                    <!-- Column Headers -->
                    <div class="hidden lg:flex items-center px-8 py-5 border-b border-slate-50 bg-slate-50/10 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400">
                        <div class="flex-1">{{ trans('files.name') }}</div>
                        <div class="w-64">{{ trans('files.created_at') }}</div>
                        <div class="w-32">{{ trans('files.size') }}</div>
                        <div class="w-32 text-right px-4">{{ trans('files.actions') }}</div>
                    </div>

                    <!-- List Area -->
                    <div class="min-w-full divide-y divide-slate-50">
                        <div v-if="totalEntries === 0" class="flex flex-col items-center justify-center py-40 text-slate-200">
                            <svg class="h-16 w-16 opacity-15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                            </svg>
                            <p class="mt-6 text-[13px] font-bold uppercase tracking-widest text-slate-300">{{ trans('files.empty') }}</p>
                        </div>

                        <div v-else>
                            <!-- Folder Items -->
                            <div v-for="folder in folders" :key="`folder-${folder.id}`" class="group flex items-center px-8 py-7 transition hover:bg-slate-50/70">
                                <div class="flex flex-1 items-center gap-6 min-w-0">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-50 text-slate-400 border border-slate-100 group-hover:bg-white group-hover:border-slate-200 group-hover:shadow-sm transition-all duration-300">
                                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                        </svg>
                                    </div>
                                    <Link :href="route('files.index', { folder_id: folder.id })" class="truncate text-[15px] font-bold text-slate-900 hover:text-sky-600 transition leading-tight decoration-sky-600/30 hover:underline underline-offset-4">
                                        {{ folder.name }}
                                    </Link>
                                </div>
                                <div class="hidden w-64 shrink-0 text-[13px] font-bold text-slate-400 lg:block">{{ folder.created_at }}</div>
                                <div class="hidden w-32 shrink-0 text-[13px] font-bold text-slate-300 lg:block opacity-40">—</div>
                                <div class="flex items-center gap-3 pl-6 shrink-0 lg:w-32 lg:justify-end lg:px-0">
                                    <button @click="renameFolder(folder)" class="rounded-xl p-3 text-slate-400 hover:bg-white hover:text-slate-900 border border-transparent hover:border-slate-200 hover:shadow-sm transition active:scale-95" :title="trans('files.rename')">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button @click="deleteFolder(folder.id)" class="rounded-xl p-3 text-slate-400 hover:bg-rose-50 hover:text-rose-600 border border-transparent hover:border-rose-100 hover:shadow-sm transition active:scale-95" :title="trans('files.delete')">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- File Items -->
                            <div v-for="file in files" :key="`file-${file.id}`" class="group flex items-center px-8 py-7 transition hover:bg-slate-50/70">
                                <div class="flex flex-1 items-center gap-6 min-w-0">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-50 text-slate-400 border border-slate-100 group-hover:bg-white group-hover:border-slate-200 group-hover:shadow-sm transition-all duration-300">
                                        <svg v-if="getFileIcon(file.extension) === 'image'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        <svg v-else-if="getFileIcon(file.extension) === 'pdf'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                        <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="truncate text-[15px] font-bold text-slate-900 leading-tight">{{ file.original_name }}</div>
                                        <div class="mt-1 text-[12px] font-bold text-slate-400 sm:hidden uppercase tracking-tighter">{{ formatSize(file.size) }} <span class="opacity-30">•</span> {{ file.created_at }}</div>
                                    </div>
                                </div>
                                <div class="hidden w-64 shrink-0 text-[13px] font-bold text-slate-400 lg:block">{{ file.created_at }}</div>
                                <div class="hidden w-32 shrink-0 text-[13px] font-bold text-slate-500 lg:block uppercase tracking-tighter">{{ formatSize(file.size) }}</div>
                                <div class="flex items-center gap-3 pl-6 shrink-0 lg:w-32 lg:justify-end lg:px-0">
                                    <a :href="file.download_url" class="rounded-xl p-3 text-slate-400 hover:bg-white hover:text-sky-600 border border-transparent hover:border-slate-200 hover:shadow-sm transition active:scale-95" :title="trans('files.download')">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                    </a>
                                    <button @click="renameFile(file)" class="rounded-xl p-3 text-slate-400 hover:bg-white hover:text-slate-900 border border-transparent hover:border-slate-200 hover:shadow-sm transition active:scale-95" :title="trans('files.rename')">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button @click="deleteFile(file.id)" class="rounded-xl p-3 text-slate-400 hover:bg-rose-50 hover:text-rose-600 border border-transparent hover:border-rose-100 hover:shadow-sm transition active:scale-95" :title="trans('files.delete')">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="pagination.links.length > 3" class="flex items-center justify-between border-t border-slate-100 bg-slate-50/30 px-8 py-6">
                        <p class="text-[12px] font-bold uppercase tracking-widest text-slate-400">
                            {{ trans('files.pagination_summary', { from: pagination.from, to: pagination.to, total: pagination.total }) }}
                        </p>
                        <div class="flex gap-2">
                            <template v-for="link in pagination.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    class="inline-flex h-10 min-w-[40px] items-center justify-center rounded-xl px-3 text-[13px] font-bold transition border shadow-sm active:scale-95"
                                    :class="link.active 
                                        ? 'bg-slate-900 text-white border-slate-900 shadow-md' 
                                        : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 hover:border-slate-300'"
                                    preserve-scroll
                                    v-html="link.label"
                                />
                                <span v-else class="inline-flex h-10 min-w-[40px] items-center justify-center px-3 text-[13px] font-bold text-slate-200" v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Folder Modal -->
        <Modal :show="showFolderModal" @close="showFolderModal = false" max-width="lg">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-slate-900">{{ trans('files.create_folder') }}</h3>
                    <button @click="showFolderModal = false" class="text-slate-400 hover:text-slate-600 transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="createFolder" class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">{{ trans('files.name') }}</label>
                        <TextInput
                            v-model="folderForm.name"
                            type="text"
                            class="w-full rounded-xl border-slate-200 shadow-inner focus:border-sky-500 focus:ring-sky-500"
                            :placeholder="trans('files.create_folder_placeholder')"
                            autofocus
                        />
                        <InputError :message="folderForm.errors.name" class="mt-2" />
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="showFolderModal = false" class="px-5 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-700 transition">
                            {{ trans('profile.cancel') }}
                        </button>
                        <PrimaryButton :disabled="folderForm.processing" class="shadow-md">
                            {{ trans('files.create_folder_submit') }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Upload File Modal -->
        <Modal :show="showUploadModal" @close="showUploadModal = false" max-width="xl">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-slate-900">{{ trans('files.upload_file') }}</h3>
                    <button @click="showUploadModal = false" class="text-slate-400 hover:text-slate-600 transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form @submit.prevent="uploadFile" class="space-y-8">
                    <div class="space-y-2">
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400">{{ trans('files.target_folder_select') }}</label>
                        <select
                            v-model="uploadForm.folder_id"
                            class="w-full rounded-xl border-slate-200 bg-white text-sm font-bold text-slate-700 shadow-inner focus:border-sky-500 focus:ring-sky-500"
                        >
                            <optgroup v-for="group in folderOptionGroups" :key="group.label" :label="group.label">
                                <option v-for="folder in group.options" :key="folder.id" :value="folder.id">{{ folder.label }}</option>
                            </optgroup>
                        </select>
                    </div>

                    <label
                        class="group block cursor-pointer rounded-[24px] border-2 border-dashed p-10 text-center transition-all duration-300"
                        :class="isDraggingFile 
                            ? 'border-sky-500 bg-sky-50' 
                            : 'border-slate-200 bg-slate-50/50 hover:border-slate-300 hover:bg-white'"
                        @dragenter.prevent="isDraggingFile = true"
                        @dragover.prevent="isDraggingFile = true"
                        @dragleave.prevent="isDraggingFile = false"
                        @drop.prevent="handleFileDrop"
                    >
                        <div class="flex flex-col items-center">
                            <div class="mb-5 flex h-16 w-16 items-center justify-center rounded-[20px] bg-slate-900 text-white shadow-lg group-hover:scale-110 transition duration-300">
                                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-slate-900 tracking-tight">{{ trans('files.drop_file_here') }}</p>
                            <p class="mt-1.5 text-xs font-medium text-slate-400">{{ trans('files.or_click_to_choose') }}</p>
                        </div>
                        <input type="file" class="hidden" @change="setUploadFile($event.target.files[0])">
                    </label>

                    <div v-if="uploadForm.file" class="flex items-center justify-between rounded-2xl bg-slate-50 border border-slate-100 p-4 animate-in fade-in slide-in-from-top-2">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-slate-900 leading-tight">{{ selectedUploadFileName }}</p>
                            <p class="mt-0.5 text-[11px] font-black uppercase tracking-tighter text-sky-600">{{ selectedUploadFileSize }}</p>
                        </div>
                        <button type="button" @click="uploadForm.reset('file')" class="ml-4 text-slate-400 hover:text-rose-500 transition">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <InputError :message="uploadForm.errors.file" />

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showUploadModal = false" class="px-5 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-700 transition">
                            {{ trans('profile.cancel') }}
                        </button>
                        <PrimaryButton :disabled="uploadForm.processing || !uploadForm.file" class="shadow-lg min-w-[120px]">
                            {{ trans('files.upload_submit') }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
