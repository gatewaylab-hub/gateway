<script setup>
import { Link } from '@inertiajs/vue3';
import UserMenu from '@/components/layout/UserMenu.vue';
</script>

<template>
    <div class="min-h-screen bg-zinc-50 text-zinc-900 transition-colors">
        <header class="sticky top-0 z-10 border-b border-zinc-200 bg-white">
            <div class="mx-auto flex h-14 max-w-7xl items-center justify-between gap-4 px-4">
                <div class="flex min-w-0 items-center gap-4">
                    <span class="truncate text-lg font-semibold text-zinc-900">
                        Minha conta
                    </span>
                    <Link
                        href="/painel-cliente"
                        class="hidden text-sm font-medium text-zinc-600 hover:text-zinc-900 sm:inline"
                    >
                        Minhas compras
                    </Link>
                    <Link
                        href="/area-membros"
                        class="hidden text-sm font-medium text-zinc-600 hover:text-zinc-900 sm:inline"
                    >
                        Área de membros
                    </Link>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <UserMenu />
                </div>
            </div>
        </header>
        <main class="mx-auto max-w-7xl px-4 py-8">
            <slot />
        </main>
    </div>
</template>
