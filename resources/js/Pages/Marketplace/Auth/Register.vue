<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import MkIcon from '@/components/marketplace/MkIcon.vue';
import { useViaCep } from '@/composables/useViaCep';
import { isValidCpf } from '@/utils/brazilianDocuments.js';
import { useMarketplaceBranding } from '@/composables/useMarketplaceBranding';

defineProps({
    login_turnstile: { type: Object, default: () => ({}) },
});

const { appName } = useMarketplaceBranding();

const { fetchCep, loading: cepLoading, error: cepError } = useViaCep();
const showPassword = ref(false);
const step = ref(1);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    document: '',
    birth_date: '',
    address_zip: '',
    address_street: '',
    address_number: '',
    address_complement: '',
    address_neighborhood: '',
    address_city: '',
    address_state: '',
    password: '',
    password_confirmation: '',
    accept_terms: false,
});

function digits(v) {
    return String(v || '').replace(/\D/g, '');
}
function maskPhone(v) {
    const d = digits(v).slice(0, 11);
    if (d.length <= 2) return d.length ? `(${d}` : '';
    if (d.length <= 7) return `(${d.slice(0, 2)}) ${d.slice(2)}`;
    return `(${d.slice(0, 2)}) ${d.slice(2, 7)}-${d.slice(7)}`;
}
function maskCpf(v) {
    const d = digits(v).slice(0, 11);
    return d
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
}
function maskCep(v) {
    const d = digits(v).slice(0, 8);
    return d.length > 5 ? `${d.slice(0, 5)}-${d.slice(5)}` : d;
}

watch(
    () => digits(form.address_zip),
    async (cep) => {
        if (cep.length !== 8) return;
        const data = await fetchCep(cep);
        if (!data) return;
        form.address_street = data.street || form.address_street;
        form.address_neighborhood = data.neighborhood || form.address_neighborhood;
        form.address_city = data.city || form.address_city;
        form.address_state = data.uf || form.address_state;
    }
);

const cpfOk = computed(() => isValidCpf(digits(form.document)));
const step1Ok = computed(() =>
    form.name.trim().length >= 3
    && form.email.includes('@')
    && digits(form.phone).length >= 10
    && cpfOk.value
    && !!form.birth_date
);
const step2Ok = computed(() =>
    digits(form.address_zip).length === 8
    && form.address_street.trim()
    && form.address_number.trim()
    && form.address_neighborhood.trim()
    && form.address_city.trim()
    && form.address_state.trim().length === 2
);

function goStep2() {
    if (!step1Ok.value) {
        if (!cpfOk.value) form.setError('document', 'CPF inválido.');
        return;
    }
    form.clearErrors();
    step.value = 2;
}
function goStep3() {
    if (!step2Ok.value) return;
    form.clearErrors();
    step.value = 3;
}

function submit() {
    form
        .transform((data) => ({
            ...data,
            phone: digits(data.phone),
            document: digits(data.document),
            address_zip: digits(data.address_zip),
            address_state: String(data.address_state || '').toUpperCase(),
            accept_terms: data.accept_terms ? 1 : 0,
        }))
        .post('/criar-conta');
}

const field =
    'w-full rounded-[12px] border border-[#E2D7CB] bg-white px-4 py-3 text-[14px] text-[#1A1410] outline-none transition placeholder:text-[#A3958A] focus:border-[#1A1410]';
const label = 'mb-1.5 block text-[12px] font-semibold text-[#3D332B]';
</script>

<template>
    <Head title="Criar conta" />
    <MarketplaceLayout>
        <div class="mx-auto max-w-[720px] px-5 py-10 sm:py-14">
            <div class="mb-8 text-center">
                <div class="text-[12px] font-semibold uppercase tracking-[0.14em] text-[color:var(--mk-accent)]">Conta {{ appName }}</div>
                <h1 class="mt-2 font-display text-[34px] font-extrabold tracking-[-0.035em] text-[#1A1410] sm:text-[40px]">
                    Criar sua conta
                </h1>
                <p class="mx-auto mt-3 max-w-[480px] text-[15px] text-[#5C4F44]">
                    Preencha seus dados uma vez. Nas próximas compras, é só escolher o pagamento.
                </p>
            </div>

            <div class="mb-6 flex items-center justify-center gap-2">
                <button
                    v-for="n in 3"
                    :key="n"
                    type="button"
                    class="flex items-center gap-2 rounded-full px-3 py-1.5 text-[12px] font-semibold transition"
                    :class="step === n
                        ? 'bg-[#1A1410] text-white'
                        : step > n
                            ? 'bg-[color:var(--mk-accent)]/15 text-[color:var(--mk-accent)]'
                            : 'bg-[#F1EAE2] text-[#8A7B6E]'"
                    @click="step > n && (step = n)"
                >
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white/20 text-[11px]">{{ n }}</span>
                    {{ n === 1 ? 'Você' : n === 2 ? 'Endereço' : 'Senha' }}
                </button>
            </div>

            <div class="rounded-[22px] border border-[#1A1410] bg-white p-6 shadow-[6px_6px_0_#1A1410] sm:p-8">
                <form class="space-y-4" @submit.prevent="step === 3 ? submit() : (step === 1 ? goStep2() : goStep3())">
                    <!-- Passo 1 -->
                    <template v-if="step === 1">
                        <label class="block">
                            <span :class="label">Nome completo</span>
                            <input v-model="form.name" type="text" required autocomplete="name" :class="field" placeholder="Como no documento" />
                            <p v-if="form.errors.name" class="mt-1 text-[12px] text-red-600">{{ form.errors.name }}</p>
                        </label>
                        <label class="block">
                            <span :class="label">E-mail</span>
                            <input v-model="form.email" type="email" required autocomplete="email" :class="field" placeholder="voce@email.com" />
                            <p v-if="form.errors.email" class="mt-1 text-[12px] text-red-600">{{ form.errors.email }}</p>
                        </label>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="block">
                                <span :class="label">Telefone / WhatsApp</span>
                                <input
                                    :value="form.phone"
                                    type="tel"
                                    inputmode="tel"
                                    required
                                    autocomplete="tel"
                                    :class="field"
                                    placeholder="(11) 99999-9999"
                                    @input="form.phone = maskPhone($event.target.value)"
                                />
                                <p v-if="form.errors.phone" class="mt-1 text-[12px] text-red-600">{{ form.errors.phone }}</p>
                            </label>
                            <label class="block">
                                <span :class="label">CPF</span>
                                <input
                                    :value="form.document"
                                    type="text"
                                    inputmode="numeric"
                                    required
                                    autocomplete="off"
                                    :class="field"
                                    placeholder="000.000.000-00"
                                    @input="form.document = maskCpf($event.target.value)"
                                />
                                <p v-if="form.errors.document" class="mt-1 text-[12px] text-red-600">{{ form.errors.document }}</p>
                            </label>
                        </div>
                        <label class="block sm:max-w-[240px]">
                            <span :class="label">Data de nascimento</span>
                            <input v-model="form.birth_date" type="date" required :class="field" />
                            <p v-if="form.errors.birth_date" class="mt-1 text-[12px] text-red-600">{{ form.errors.birth_date }}</p>
                        </label>
                        <button
                            type="submit"
                            class="mt-2 flex h-12 w-full items-center justify-center gap-2 rounded-[12px] bg-[color:var(--mk-accent)] text-[14px] font-bold text-white hover:brightness-95 disabled:opacity-50"
                            :disabled="!step1Ok"
                        >
                            Continuar <MkIcon name="arrow-right" :size="16" />
                        </button>
                    </template>

                    <!-- Passo 2 -->
                    <template v-else-if="step === 2">
                        <label class="block sm:max-w-[200px]">
                            <span :class="label">CEP</span>
                            <div class="relative">
                                <input
                                    :value="form.address_zip"
                                    type="text"
                                    inputmode="numeric"
                                    required
                                    autocomplete="postal-code"
                                    :class="field"
                                    placeholder="00000-000"
                                    @input="form.address_zip = maskCep($event.target.value)"
                                />
                                <span v-if="cepLoading" class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] text-[#8A7B6E]">Buscando…</span>
                            </div>
                            <p v-if="cepError || form.errors.address_zip" class="mt-1 text-[12px] text-red-600">{{ cepError || form.errors.address_zip }}</p>
                        </label>
                        <label class="block">
                            <span :class="label">Rua</span>
                            <input v-model="form.address_street" type="text" required autocomplete="address-line1" :class="field" />
                            <p v-if="form.errors.address_street" class="mt-1 text-[12px] text-red-600">{{ form.errors.address_street }}</p>
                        </label>
                        <div class="grid grid-cols-[1fr_1.4fr] gap-4">
                            <label class="block">
                                <span :class="label">Número</span>
                                <input v-model="form.address_number" type="text" required :class="field" />
                            </label>
                            <label class="block">
                                <span :class="label">Complemento</span>
                                <input v-model="form.address_complement" type="text" :class="field" placeholder="Opcional" />
                            </label>
                        </div>
                        <label class="block">
                            <span :class="label">Bairro</span>
                            <input v-model="form.address_neighborhood" type="text" required :class="field" />
                        </label>
                        <div class="grid grid-cols-[1fr_90px] gap-4">
                            <label class="block">
                                <span :class="label">Cidade</span>
                                <input v-model="form.address_city" type="text" required :class="field" />
                            </label>
                            <label class="block">
                                <span :class="label">UF</span>
                                <input
                                    v-model="form.address_state"
                                    type="text"
                                    required
                                    maxlength="2"
                                    :class="field"
                                    class="uppercase"
                                    @input="form.address_state = $event.target.value.toUpperCase().slice(0, 2)"
                                />
                            </label>
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="button" class="h-12 flex-1 rounded-[12px] border border-[#1A1410] text-[14px] font-semibold" @click="step = 1">Voltar</button>
                            <button
                                type="submit"
                                class="flex h-12 flex-[1.4] items-center justify-center gap-2 rounded-[12px] bg-[color:var(--mk-accent)] text-[14px] font-bold text-white disabled:opacity-50"
                                :disabled="!step2Ok"
                            >
                                Continuar <MkIcon name="arrow-right" :size="16" />
                            </button>
                        </div>
                    </template>

                    <!-- Passo 3 -->
                    <template v-else>
                        <label class="block">
                            <span :class="label">Senha</span>
                            <div class="relative">
                                <input
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    minlength="8"
                                    autocomplete="new-password"
                                    :class="field"
                                    class="pr-12"
                                    placeholder="Mínimo 8 caracteres"
                                />
                                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-[12px] font-semibold text-[#8A7B6E]" @click="showPassword = !showPassword">
                                    {{ showPassword ? 'Ocultar' : 'Mostrar' }}
                                </button>
                            </div>
                            <p v-if="form.errors.password" class="mt-1 text-[12px] text-red-600">{{ form.errors.password }}</p>
                        </label>
                        <label class="block">
                            <span :class="label">Confirmar senha</span>
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                required
                                minlength="8"
                                autocomplete="new-password"
                                :class="field"
                            />
                        </label>
                        <label class="flex items-start gap-2.5 rounded-[12px] border border-[#EBE2D8] bg-[#FBF7F2] p-3.5 text-[13px] leading-relaxed text-[#5C4F44]">
                            <input v-model="form.accept_terms" type="checkbox" required class="mt-0.5 rounded border-[#E2D7CB] text-[color:var(--mk-accent)] focus:ring-[color:var(--mk-accent)]" />
                            <span>
                                Li e aceito os
                                <Link href="/termos-de-uso" target="_blank" class="font-semibold text-[#1A1410] underline">Termos de uso</Link>
                                e a
                                <Link href="/politica-privacidade" target="_blank" class="font-semibold text-[#1A1410] underline">Política de privacidade</Link>.
                            </span>
                        </label>
                        <p v-if="form.errors.accept_terms" class="text-[12px] text-red-600">{{ form.errors.accept_terms }}</p>

                        <div class="flex gap-3 pt-2">
                            <button type="button" class="h-12 flex-1 rounded-[12px] border border-[#1A1410] text-[14px] font-semibold" @click="step = 2">Voltar</button>
                            <button
                                type="submit"
                                class="flex h-12 flex-[1.6] items-center justify-center gap-2 rounded-[12px] bg-[#1A1410] text-[14px] font-bold text-white hover:bg-black disabled:opacity-60"
                                :disabled="form.processing || !form.accept_terms"
                            >
                                {{ form.processing ? 'Criando…' : 'Criar conta' }}
                            </button>
                        </div>
                    </template>
                </form>
            </div>

            <p class="mt-6 text-center text-[14px] text-[#5C4F44]">
                Já tem conta?
                <Link href="/login" class="font-semibold text-[#1A1410] hover:text-[color:var(--mk-accent)]">Entrar</Link>
                <span class="mx-2 text-[#C9BDB1]">·</span>
                <Link href="/cadastro" class="font-semibold text-[#1A1410] hover:underline">Quero vender</Link>
            </p>
        </div>
    </MarketplaceLayout>
</template>
