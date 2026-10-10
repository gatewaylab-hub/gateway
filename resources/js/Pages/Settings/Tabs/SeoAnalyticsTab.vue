<script setup>
import { Search, BarChart3, Share2 } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    form: { type: Object, required: true },
    sitemapUrl: { type: String, default: '' },
});

const sitemap = computed(() => props.sitemapUrl || (typeof window !== 'undefined' ? `${window.location.origin}/sitemap.xml` : '/sitemap.xml'));
</script>

<template>
    <section class="space-y-8">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                <Search class="h-5 w-5" />
            </div>
            <div>
                <h2 class="text-base font-semibold text-zinc-900 dark:text-white">SEO e Analytics do marketplace</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Configure título/descrição da home, imagem de compartilhamento, Google Analytics e redes do rodapé.
                    O sitemap público fica em
                    <a :href="sitemap" target="_blank" rel="noopener" class="font-medium text-[var(--color-primary)] underline">{{ sitemap }}</a>.
                </p>
            </div>
        </div>

        <div class="space-y-4 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Metadados da página inicial</h3>
            <div>
                <label class="mb-1.5 block text-xs font-medium uppercase text-zinc-500">Title da home</label>
                <input
                    v-model="form.seo_home_title"
                    type="text"
                    maxlength="120"
                    placeholder="Gamkon — Compre e venda com segurança"
                    class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-950 dark:text-white"
                />
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium uppercase text-zinc-500">Meta description da home</label>
                <textarea
                    v-model="form.seo_home_description"
                    rows="3"
                    maxlength="320"
                    placeholder="Descrição institucional curta para Google e compartilhamento."
                    class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-950 dark:text-white"
                />
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium uppercase text-zinc-500">Imagem social padrão (path ou URL salva)</label>
                <input
                    v-model="form.seo_default_og_image"
                    type="text"
                    placeholder="Ex.: branding/og-default.png (mesmo storage da plataforma)"
                    class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-950 dark:text-white"
                />
                <p class="mt-1 text-xs text-zinc-500">
                    Usada como fallback quando a página não tem imagem própria. Anúncios usam a imagem do produto.
                </p>
                <img
                    v-if="form.seo_default_og_image_url"
                    :src="form.seo_default_og_image_url"
                    alt="Preview OG"
                    class="mt-3 h-28 w-auto rounded-xl border border-zinc-200 object-cover dark:border-zinc-700"
                />
            </div>
        </div>

        <div class="space-y-4 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-2">
                <BarChart3 class="h-4 w-4 text-zinc-500" />
                <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Google Analytics / Tag Manager</h3>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium uppercase text-zinc-500">Google Analytics (Measurement ID)</label>
                <input
                    v-model="form.seo_google_analytics_id"
                    type="text"
                    maxlength="32"
                    placeholder="G-XXXXXXXXXX"
                    class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-950 dark:text-white"
                />
                <p class="mt-1 text-xs text-zinc-500">Cole o ID do GA4. O código gtag é injetado automaticamente nas páginas públicas.</p>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium uppercase text-zinc-500">Google Tag Manager (opcional)</label>
                <input
                    v-model="form.seo_google_tag_manager_id"
                    type="text"
                    maxlength="32"
                    placeholder="GTM-XXXXXXX"
                    class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-950 dark:text-white"
                />
            </div>
        </div>

        <div class="space-y-4 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-2">
                <Share2 class="h-4 w-4 text-zinc-500" />
                <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Redes sociais no rodapé</h3>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-medium uppercase text-zinc-500">Instagram</label>
                    <input v-model="form.seo_social_instagram" type="url" placeholder="https://instagram.com/..." class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-950 dark:text-white" />
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium uppercase text-zinc-500">Facebook</label>
                    <input v-model="form.seo_social_facebook" type="url" placeholder="https://facebook.com/..." class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-950 dark:text-white" />
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium uppercase text-zinc-500">X (Twitter)</label>
                    <input v-model="form.seo_social_x" type="url" placeholder="https://x.com/..." class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-950 dark:text-white" />
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium uppercase text-zinc-500">YouTube</label>
                    <input v-model="form.seo_social_youtube" type="url" placeholder="https://youtube.com/..." class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-950 dark:text-white" />
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium uppercase text-zinc-500">TikTok</label>
                    <input v-model="form.seo_social_tiktok" type="url" placeholder="https://tiktok.com/..." class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-950 dark:text-white" />
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium uppercase text-zinc-500">Discord</label>
                    <input v-model="form.seo_social_discord" type="url" placeholder="https://discord.gg/..." class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-950 dark:text-white" />
                </div>
            </div>
        </div>
    </section>
</template>
