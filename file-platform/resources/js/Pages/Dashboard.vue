<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { useTrans } from '@/lib/i18n';

const { trans } = useTrans();
const page = usePage();
</script>

<template>
    <Head :title="trans('nav.dashboard')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        {{ trans('nav.dashboard') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ trans('dashboard.subtitle') }}
                    </p>
                </div>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm text-slate-500">
                        {{ trans('dashboard.welcome') }}
                    </p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900">
                        {{ page.props.auth.user.name }}
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
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

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
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
                </div>

                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">
                        {{ trans('dashboard.quick_start_title') }}
                    </h3>
                    <div class="mt-4 space-y-2 text-sm text-slate-600">
                        <p>{{ trans('dashboard.quick_start_step_1') }}</p>
                        <p>{{ trans('dashboard.quick_start_step_2') }}</p>
                        <p>{{ trans('dashboard.quick_start_step_3') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
