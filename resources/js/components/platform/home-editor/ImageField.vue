<script setup>
import { ref } from 'vue';
import { ImagePlus, Loader2, Trash2 } from 'lucide-vue-next';

defineProps({
    modelValue: { type: String, default: '' },
    label: { type: String, default: 'Imagem' },
    hint: { type: String, default: '' },
});
const emit = defineEmits(['update:modelValue']);

const input = ref(null);
const uploading = ref(false);
const error = ref('');

async function onFile(e) {
    const file = e.target.files?.[0];
    e.target.value = '';
    if (!file) return;
    error.value = '';
    uploading.value = true;
    try {
        const fd = new FormData();
        fd.append('image', file);
        const { data } = await window.axios.post('/plataforma/pagina-inicial/imagem', fd);
        emit('update:modelValue', data.url);
    } catch (err) {
        error.value = err?.response?.data?.errors?.image?.[0] || err?.response?.data?.message || 'Falha no envio da imagem.';
    } finally {
        uploading.value = false;
    }
}
</script>

<template>
    <div>
        <div class="mb-1.5 text-[12px] font-semibold text-zinc-700 dark:text-zinc-300">{{ label }}</div>
        <div
            v-if="modelValue"
            class="group relative overflow-hidden rounded-lg border border-zinc-200 bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800"
        >
            <img :src="modelValue" alt="" class="aspect-[16/9] w-full object-cover" />
            <div class="absolute inset-0 flex items-center justify-center gap-2 bg-black/50 opacity-0 transition group-hover:opacity-100">
                <button type="button" class="rounded-md bg-white px-2.5 py-1.5 text-[12px] font-semibold text-zinc-900" @click="input?.click()">Trocar</button>
                <button type="button" class="rounded-md bg-red-600 p-1.5 text-white" aria-label="Remover imagem" @click="emit('update:modelValue', '')">
                    <Trash2 class="h-3.5 w-3.5" />
                </button>
            </div>
        </div>
        <button
            v-else
            type="button"
            class="flex w-full flex-col items-center justify-center gap-1.5 rounded-lg border border-dashed border-zinc-300 px-4 py-6 text-[12px] text-zinc-500 transition hover:border-zinc-900 hover:text-zinc-900 dark:border-zinc-700 dark:hover:border-zinc-300 dark:hover:text-zinc-200"
            :disabled="uploading"
            @click="input?.click()"
        >
            <Loader2 v-if="uploading" class="h-5 w-5 animate-spin" />
            <ImagePlus v-else class="h-5 w-5" />
            {{ uploading ? 'Enviando…' : 'Enviar imagem (JPG, PNG ou WebP até 5 MB)' }}
        </button>
        <input ref="input" type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden" @change="onFile" />
        <p v-if="error" class="mt-1 text-[11px] text-red-600">{{ error }}</p>
        <p v-else-if="hint" class="mt-1 text-[11px] text-zinc-500">{{ hint }}</p>
    </div>
</template>
