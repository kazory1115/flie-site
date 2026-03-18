<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import LocaleSwitcher from '@/Components/LocaleSwitcher.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gradient-to-b from-slate-100 via-slate-50 to-white px-6 py-10 sm:justify-center"
    >
        <div class="mb-6">
            <Link href="/">
                <ApplicationLogo class="h-16 w-16 fill-current text-slate-500" />
            </Link>
        </div>

        <div
            class="w-full overflow-hidden rounded-2xl border border-slate-200 bg-white/95 px-6 py-5 shadow-xl shadow-slate-200/60 backdrop-blur sm:max-w-md"
        >
            <div class="mb-5 flex items-center justify-end border-b border-slate-100 pb-4">
                <LocaleSwitcher compact />
            </div>

            <slot />
        </div>
    </div>
</template>
