<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    seo: { type: Object, default: null },
});

const s = computed(() => props.seo || {});
const jsonLdBlocks = computed(() => {
    const blocks = s.value.json_ld;
    return Array.isArray(blocks) ? blocks.filter(Boolean) : [];
});
</script>

<template>
    <Head v-if="seo">
        <title>{{ s.title }}</title>
        <meta v-if="s.description" head-key="description" name="description" :content="s.description" />
        <meta v-if="s.robots" head-key="robots" name="robots" :content="s.robots" />
        <link v-if="s.canonical" head-key="canonical" rel="canonical" :href="s.canonical" />

        <meta head-key="og:type" property="og:type" :content="s.og_type || 'website'" />
        <meta head-key="og:site_name" property="og:site_name" :content="s.og_site_name || ''" />
        <meta head-key="og:locale" property="og:locale" :content="s.og_locale || 'pt_BR'" />
        <meta head-key="og:title" property="og:title" :content="s.og_title || s.title || ''" />
        <meta v-if="s.og_description || s.description" head-key="og:description" property="og:description" :content="s.og_description || s.description" />
        <meta v-if="s.og_url || s.canonical" head-key="og:url" property="og:url" :content="s.og_url || s.canonical" />
        <meta v-if="s.og_image" head-key="og:image" property="og:image" :content="s.og_image" />

        <meta head-key="twitter:card" name="twitter:card" :content="s.twitter_card || 'summary_large_image'" />
        <meta head-key="twitter:title" name="twitter:title" :content="s.twitter_title || s.og_title || s.title || ''" />
        <meta v-if="s.twitter_description || s.description" head-key="twitter:description" name="twitter:description" :content="s.twitter_description || s.description" />
        <meta v-if="s.twitter_image || s.og_image" head-key="twitter:image" name="twitter:image" :content="s.twitter_image || s.og_image" />

        <component
            :is="'script'"
            v-for="(block, i) in jsonLdBlocks"
            :key="`ld-${i}`"
            type="application/ld+json"
            v-text="JSON.stringify(block)"
        />
    </Head>
</template>
