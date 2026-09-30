<script setup>
defineProps({
    name: { type: String, required: true },
    size: { type: [Number, String], default: 20 },
    stroke: { type: [Number, String], default: 1.75 },
});
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        :width="size"
        :height="size"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        :stroke-width="stroke"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
    >
        <template v-if="name === 'search'">
            <circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" />
        </template>
        <template v-else-if="name === 'arrow-right'">
            <path d="M5 12h14" /><path d="m13 6 6 6-6 6" />
        </template>
        <template v-else-if="name === 'arrow-up-right'">
            <path d="M7 17 17 7" /><path d="M8 7h9v9" />
        </template>
        <template v-else-if="name === 'bolt'">
            <path d="M13 3 5 14h6l-1 7 8-11h-6l1-7Z" />
        </template>
        <template v-else-if="name === 'shield'">
            <path d="M12 3 5 6v5c0 4.5 3 8.3 7 10 4-1.7 7-5.5 7-10V6l-7-3Z" /><path d="m9 12 2 2 4-4" />
        </template>
        <template v-else-if="name === 'chat'">
            <path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12Z" />
        </template>
        <template v-else-if="name === 'trophy'">
            <path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4Z" /><path d="M17 5h3v2a3 3 0 0 1-3 3M7 5H4v2a3 3 0 0 0 3 3" />
        </template>
        <template v-else-if="name === 'user'">
            <circle cx="12" cy="8" r="4" /><path d="M4 21a8 8 0 0 1 16 0" />
        </template>
        <template v-else-if="name === 'store'">
            <path d="M4 9 5.5 4h13L20 9M4 9v11h16V9M4 9h16M9 20v-6h6v6" />
        </template>
        <template v-else-if="name === 'clock'">
            <circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />
        </template>
        <template v-else-if="name === 'check'">
            <path d="m5 12 5 5L20 7" />
        </template>
        <template v-else-if="name === 'star'">
            <path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9L12 3Z" fill="currentColor" stroke="none" />
        </template>
        <template v-else-if="name === 'question'">
            <circle cx="12" cy="12" r="9" /><path d="M9.5 9a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .9-1 1.6V14M12 17.5v.01" />
        </template>
        <template v-else-if="name === 'box'">
            <path d="M21 8 12 3 3 8v8l9 5 9-5V8Z" /><path d="M3 8l9 5 9-5M12 13v8" />
        </template>
        <template v-else-if="name === 'expand'">
            <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" />
        </template>
        <template v-else-if="name === 'chevron-left'">
            <path d="m15 18-6-6 6-6" />
        </template>
        <template v-else-if="name === 'chevron-right'">
            <path d="m9 18 6-6-6-6" />
        </template>
        <template v-else-if="name === 'link'">
            <path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1" /><path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1" />
        </template>
        <template v-else-if="name === 'mail'">
            <rect x="3" y="5" width="18" height="14" rx="2" /><path d="m3 7 9 6 9-6" />
        </template>
        <template v-else-if="name === 'phone'">
            <rect x="7" y="2.5" width="10" height="19" rx="2" /><path d="M11 18.5h2" />
        </template>
        <template v-else-if="name === 'document'">
            <path d="M14 3H6v18h12V7l-4-4Z" /><path d="M14 3v4h4M9 13h6M9 17h4" />
        </template>
        <template v-else-if="name === 'x'">
            <path d="M18 6 6 18M6 6l12 12" />
        </template>
        <template v-else-if="name === 'wallet'">
            <path d="M3 7a2 2 0 0 1 2-2h13v4" /><path d="M3 7v11a2 2 0 0 0 2 2h15V9H5a2 2 0 0 1-2-2Z" /><path d="M16 14.5h.01" />
        </template>
    </svg>
</template>
