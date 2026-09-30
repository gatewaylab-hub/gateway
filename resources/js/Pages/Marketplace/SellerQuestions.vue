<script setup>
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import LayoutInfoprodutor from '@/Layouts/LayoutInfoprodutor.vue';

defineProps({
    questions: { type: Object, required: true },
});

const drafts = reactive({});
const errors = reactive({});
const processing = ref({});

function answer(id) {
    const body = String(drafts[id] || '').trim();
    if (!body) {
        errors[id] = 'Escreva uma resposta.';
        return;
    }
    errors[id] = '';
    processing.value = { ...processing.value, [id]: true };
    router.post(`/perguntas/${id}/responder`, { body }, {
        preserveScroll: true,
        onError: (errs) => {
            errors[id] = errs?.body || 'Não foi possível responder.';
        },
        onSuccess: () => {
            drafts[id] = '';
            errors[id] = '';
        },
        onFinish: () => {
            processing.value = { ...processing.value, [id]: false };
        },
    });
}
</script>

<template>
    <Head title="Perguntas" />
    <LayoutInfoprodutor>
        <div class="mx-auto max-w-3xl space-y-4">
            <div>
                <h1 class="font-display text-2xl font-bold tracking-tight text-[#1A1410]">Perguntas</h1>
                <p class="mt-1 text-sm text-[#6B5E54]">Respostas públicas nos anúncios do marketplace.</p>
            </div>

            <div
                v-for="q in questions.data"
                :key="q.id"
                class="rounded-[16px] border border-[#EBE2D8] bg-white p-4"
            >
                <div class="text-xs text-[#8A7B6E]">{{ q.product?.name }} · {{ q.user?.name }}</div>
                <p class="mt-1 font-medium text-[#1A1410]">{{ q.body }}</p>
                <div v-if="q.answer" class="mt-2 border-l-2 border-[var(--color-primary)] pl-3 text-sm text-[#3D332B]">
                    {{ q.answer.body }}
                </div>
                <div v-else class="mt-3 space-y-2">
                    <textarea
                        v-model="drafts[q.id]"
                        rows="2"
                        class="w-full rounded-[12px] border border-[#E2D7CB] bg-white px-3 py-2 text-sm text-[#1A1410] outline-none focus:border-[#1A1410]"
                        placeholder="Sua resposta..."
                    />
                    <p v-if="errors[q.id]" class="text-xs text-red-600">{{ errors[q.id] }}</p>
                    <button
                        type="button"
                        class="rounded-[10px] bg-[var(--color-primary)] px-3.5 py-2 text-sm font-semibold text-white disabled:opacity-50"
                        :disabled="processing[q.id] || !(drafts[q.id] || '').trim()"
                        @click="answer(q.id)"
                    >
                        {{ processing[q.id] ? 'Enviando…' : 'Responder' }}
                    </button>
                </div>
            </div>
            <p v-if="!questions.data?.length" class="rounded-[16px] border border-dashed border-[#E2D7CB] px-4 py-10 text-center text-sm text-[#8A7B6E]">
                Nenhuma pergunta ainda.
            </p>
        </div>
    </LayoutInfoprodutor>
</template>
