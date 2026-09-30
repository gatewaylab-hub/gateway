<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import MarketplaceAccountLayout from '@/Layouts/MarketplaceAccountLayout.vue';

const props = defineProps({
    profile: { type: Object, required: true },
});

const form = useForm({
    name: props.profile.name || '',
    phone: props.profile.phone || '',
    address_zip: props.profile.address_zip || '',
    address_street: props.profile.address_street || '',
    address_number: props.profile.address_number || '',
    address_complement: props.profile.address_complement || '',
    address_neighborhood: props.profile.address_neighborhood || '',
    address_city: props.profile.address_city || '',
    address_state: props.profile.address_state || '',
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

function saveProfile() {
    form.put('/painel-cliente/conta', { preserveScroll: true });
}

function savePassword() {
    passwordForm.put('/painel-cliente/conta/senha', {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
}

const fieldClass = 'h-11 w-full rounded-[12px] border border-[#E2D7CB] bg-white px-3 text-[14px] text-[#1A1410] outline-none focus:border-[#1A1410]';
const labelClass = 'mb-1.5 block text-[12px] font-semibold uppercase tracking-[0.06em] text-[#8A7B6E]';
</script>

<template>
    <Head title="Minha conta" />
    <MarketplaceAccountLayout title="Minha conta">
        <p class="-mt-2 mb-6 text-[14px] text-[#6B5E54]">Atualize seus dados pessoais e a senha de acesso.</p>

        <div class="grid gap-6 lg:grid-cols-2">
            <form class="rounded-[16px] border border-[#EBE2D8] bg-white p-5 sm:p-6" @submit.prevent="saveProfile">
                <h2 class="font-display text-[18px] font-bold text-[#1A1410]">Dados pessoais</h2>

                <div class="mt-5 space-y-4">
                    <div>
                        <label :class="labelClass">Nome</label>
                        <input v-model="form.name" type="text" :class="fieldClass" required />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label :class="labelClass">E-mail</label>
                        <input :value="profile.email" type="email" :class="[fieldClass, '!bg-[#F1EAE2] !text-[#8A7B6E]']" disabled />
                    </div>
                    <div>
                        <label :class="labelClass">CPF</label>
                        <input :value="profile.document_label || '—'" type="text" :class="[fieldClass, '!bg-[#F1EAE2] !text-[#8A7B6E]']" disabled />
                    </div>
                    <div>
                        <label :class="labelClass">Telefone</label>
                        <input v-model="form.phone" type="tel" :class="fieldClass" required />
                        <p v-if="form.errors.phone" class="mt-1 text-xs text-red-600">{{ form.errors.phone }}</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="sm:col-span-1">
                            <label :class="labelClass">CEP</label>
                            <input v-model="form.address_zip" type="text" :class="fieldClass" required />
                            <p v-if="form.errors.address_zip" class="mt-1 text-xs text-red-600">{{ form.errors.address_zip }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label :class="labelClass">Rua</label>
                            <input v-model="form.address_street" type="text" :class="fieldClass" required />
                            <p v-if="form.errors.address_street" class="mt-1 text-xs text-red-600">{{ form.errors.address_street }}</p>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label :class="labelClass">Número</label>
                            <input v-model="form.address_number" type="text" :class="fieldClass" required />
                            <p v-if="form.errors.address_number" class="mt-1 text-xs text-red-600">{{ form.errors.address_number }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label :class="labelClass">Complemento</label>
                            <input v-model="form.address_complement" type="text" :class="fieldClass" />
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="sm:col-span-1">
                            <label :class="labelClass">Bairro</label>
                            <input v-model="form.address_neighborhood" type="text" :class="fieldClass" required />
                            <p v-if="form.errors.address_neighborhood" class="mt-1 text-xs text-red-600">{{ form.errors.address_neighborhood }}</p>
                        </div>
                        <div>
                            <label :class="labelClass">Cidade</label>
                            <input v-model="form.address_city" type="text" :class="fieldClass" required />
                            <p v-if="form.errors.address_city" class="mt-1 text-xs text-red-600">{{ form.errors.address_city }}</p>
                        </div>
                        <div>
                            <label :class="labelClass">UF</label>
                            <input v-model="form.address_state" type="text" maxlength="2" :class="fieldClass" required />
                            <p v-if="form.errors.address_state" class="mt-1 text-xs text-red-600">{{ form.errors.address_state }}</p>
                        </div>
                    </div>
                </div>

                <button
                    type="submit"
                    class="mt-6 inline-flex rounded-[12px] bg-[color:var(--mk-accent)] px-5 py-2.5 text-[14px] font-semibold text-white hover:brightness-95 disabled:opacity-50"
                    :disabled="form.processing"
                >
                    Salvar dados
                </button>
            </form>

            <form class="h-fit rounded-[16px] border border-[#EBE2D8] bg-white p-5 sm:p-6" @submit.prevent="savePassword">
                <h2 class="font-display text-[18px] font-bold text-[#1A1410]">Alterar senha</h2>
                <div class="mt-5 space-y-4">
                    <div>
                        <label :class="labelClass">Senha atual</label>
                        <input v-model="passwordForm.current_password" type="password" :class="fieldClass" required autocomplete="current-password" />
                        <p v-if="passwordForm.errors.current_password" class="mt-1 text-xs text-red-600">{{ passwordForm.errors.current_password }}</p>
                    </div>
                    <div>
                        <label :class="labelClass">Nova senha</label>
                        <input v-model="passwordForm.password" type="password" :class="fieldClass" required autocomplete="new-password" />
                        <p v-if="passwordForm.errors.password" class="mt-1 text-xs text-red-600">{{ passwordForm.errors.password }}</p>
                    </div>
                    <div>
                        <label :class="labelClass">Confirmar nova senha</label>
                        <input v-model="passwordForm.password_confirmation" type="password" :class="fieldClass" required autocomplete="new-password" />
                    </div>
                </div>
                <button
                    type="submit"
                    class="mt-6 inline-flex rounded-[12px] bg-[#1A1410] px-5 py-2.5 text-[14px] font-semibold text-white hover:bg-black disabled:opacity-50"
                    :disabled="passwordForm.processing"
                >
                    Atualizar senha
                </button>
            </form>
        </div>
    </MarketplaceAccountLayout>
</template>
