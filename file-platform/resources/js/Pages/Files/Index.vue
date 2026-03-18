<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    currentFolder: {
        type: Object,
        default: null,
    },
    breadcrumbs: {
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
    query: {
        type: Object,
        required: true,
    },
});

const page = usePage();

const folderForm = useForm({
    parent_id: props.query.folder_id,
    name: '',
});

const uploadForm = useForm({
    folder_id: props.query.folder_id,
    file: null,
});

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

const deleteFolder = (folderId) => {
    if (!window.confirm('只允許刪除空資料夾，確定要刪除嗎？')) {
        return;
    }

    useForm({}).delete(route('folders.destroy', folderId), {
        preserveScroll: true,
    });
};

const deleteFile = (fileId) => {
    if (!window.confirm('確定要刪除這個檔案嗎？')) {
        return;
    }

    useForm({}).delete(route('files.destroy', fileId), {
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
    <Head title="檔案空間" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        檔案空間
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        第一版先提供資料夾、上傳、下載、刪除。
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
                            :href="route('files.index', breadcrumb.id ? { folder_id: breadcrumb.id } : {})"
                            class="rounded-md px-2 py-1 hover:bg-slate-100 hover:text-slate-900"
                        >
                            {{ breadcrumb.name }}
                        </Link>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-slate-900">
                            建立資料夾
                        </h3>
                        <form class="mt-4 space-y-3" @submit.prevent="createFolder">
                            <TextInput
                                v-model="folderForm.name"
                                type="text"
                                class="block w-full"
                                placeholder="例如：合約文件"
                            />
                            <InputError :message="folderForm.errors.name || folderForm.errors.parent_id" />
                            <PrimaryButton :disabled="folderForm.processing">
                                新增資料夾
                            </PrimaryButton>
                        </form>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-slate-900">
                            上傳檔案
                        </h3>
                        <form class="mt-4 space-y-3" @submit.prevent="uploadFile">
                            <input
                                type="file"
                                class="block w-full rounded-lg border border-slate-300 text-sm file:mr-4 file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-slate-700"
                                @input="uploadForm.file = $event.target.files[0]"
                            >
                            <InputError :message="uploadForm.errors.file || uploadForm.errors.folder_id" />
                            <p class="text-xs text-slate-500">
                                目前限制單檔 100 MB。
                            </p>
                            <PrimaryButton :disabled="uploadForm.processing">
                                上傳檔案
                            </PrimaryButton>
                        </form>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-4">
                        <h3 class="text-lg font-semibold text-slate-900">
                            目前位置
                        </h3>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ currentFolder?.path ?? '根目錄' }}
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3 text-left font-medium text-slate-500">名稱</th>
                                    <th class="px-6 py-3 text-left font-medium text-slate-500">類型</th>
                                    <th class="px-6 py-3 text-left font-medium text-slate-500">大小</th>
                                    <th class="px-6 py-3 text-left font-medium text-slate-500">建立時間</th>
                                    <th class="px-6 py-3 text-right font-medium text-slate-500">操作</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <tr v-if="folders.length === 0 && files.length === 0">
                                    <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                                        目前沒有任何資料。
                                    </td>
                                </tr>

                                <tr v-for="folder in folders" :key="`folder-${folder.id}`" class="hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <Link
                                            :href="route('files.index', { folder_id: folder.id })"
                                            class="font-medium text-slate-900 hover:text-sky-700"
                                        >
                                            {{ folder.name }}
                                        </Link>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500">資料夾</td>
                                    <td class="px-6 py-4 text-slate-500">-</td>
                                    <td class="px-6 py-4 text-slate-500">{{ folder.created_at }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <DangerButton @click="deleteFolder(folder.id)">
                                            刪除
                                        </DangerButton>
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
                                                下載
                                            </a>
                                            <DangerButton @click="deleteFile(file.id)">
                                                刪除
                                            </DangerButton>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
