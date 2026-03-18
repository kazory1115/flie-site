<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { useTrans } from '@/lib/i18n';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});

const { trans } = useTrans();
</script>

<template>
    <Head :title="trans('common.welcome')" />

    <div class="min-h-screen bg-slate-100 px-6 py-10 text-slate-900">
        <div class="mx-auto flex max-w-5xl justify-end gap-3">
            <Link
                v-if="canLogin"
                :href="route('login')"
                class="rounded-md bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm"
            >
                {{ trans('nav.login') }}
            </Link>
            <Link
                v-if="canRegister"
                :href="route('register')"
                class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-sm"
            >
                {{ trans('auth.register') }}
            </Link>
        </div>

        <div class="mx-auto mt-20 max-w-5xl rounded-3xl bg-white p-10 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-sky-700">
                {{ trans('app.name') }}
            </p>
            <h1 class="mt-4 text-4xl font-bold text-slate-900">
                {{ trans('common.welcome') }}
            </h1>
            <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">
                {{ trans('files.subtitle') }}
            </p>
            <div class="mt-8 flex gap-3">
                <Link
                    v-if="canLogin"
                    :href="route('login')"
                    class="rounded-md bg-slate-900 px-5 py-3 text-sm font-semibold text-white"
                >
                    {{ trans('nav.login') }}
                </Link>
                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="rounded-md border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700"
                >
                    {{ trans('auth.register') }}
                </Link>
            </div>
        </div>
    </div>
</template>
