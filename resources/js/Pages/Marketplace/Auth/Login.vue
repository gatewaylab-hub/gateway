<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import MkIcon from '@/components/marketplace/MkIcon.vue';
import AuthTurnstileField from '@/components/auth/AuthTurnstileField.vue';
import { useMarketplaceBranding } from '@/composables/useMarketplaceBranding';

const props = defineProps({
    login_turnstile: { type: Object, default: () => ({ enabled: false, site_key: '' }) },
});

const page = usePage();
const { appName } = useMarketplaceBranding();
const flashError = computed(() => page.props.flash?.error ?? null);
const turnstileToken = ref('');
const showPassword = ref(false);
const turnstileActive = computed(
    () => Boolean(props.login_turnstile?.enabled && props.login_turnstile?.site_key)
);

const form = useForm({
    email: '',
    password: '',
    remember: true,
    turnstile_token: '',
});

function submit() {
    if (turnstileActive.value && !turnstileToken.value) {
        form.setError('turnstile_token', 'Aguarde a verificação de segurança.');
        return;
    }
    form.turnstile_token = turnstileToken.value;
    form.post('/login', { onFinish: () => form.reset('password') });
}

const field =
    'w-full rounded-[12px] border border-[#E2D7CB] bg-white px-4 py-3 text-[14px] text-[#1A1410] outline-none transition placeholder:text-[#A3958A] focus:border-[#1A1410]';
</script>

<template>
    <Head title="Entrar" />
    <MarketplaceLayout>
        <div class="mx-auto grid max-w-[1240px] gap-10 px-5 py-12 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:py-20">
            <div class="hidden lg:block">
                <div class="text-[12px] font-semibold uppercase tracking-[0.14em] text-[color:var(--mk-accent)]">Conta {{ appName }}</div>
                <h1 class="mt-3 font-display text-[44px] font-extrabold leading-[1.05] tracking-[-0.04em] text-[#1A1410]">
                    Entre para comprar<br />em poucos cliques.
                </h1>
                <p class="mt-5 max-w-[420px] text-[15px] leading-relaxed text-[#5C4F44]">
                    Com a conta, seus dados já ficam no checkout. Você só escolhe o pagamento e confirma.
                </p>
                <ul class="mt-8 space-y-3 text-[14px] text-[#3D332B]">
                    <li class="flex items-center gap-2.5"><MkIcon name="shield" :size="17" class="text-[color:var(--mk-accent)]" /> Pagamento protegido até a entrega</li>
                    <li class="flex items-center gap-2.5"><MkIcon name="bolt" :size="17" class="text-[color:var(--mk-accent)]" /> Checkout sem digitar tudo de novo</li>
                    <li class="flex items-center gap-2.5"><MkIcon name="chat" :size="17" class="text-[color:var(--mk-accent)]" /> Chat e pedidos no mesmo lugar</li>
                </ul>
            </div>

            <div class="mx-auto w-full max-w-[440px]">
                <div class="rounded-[22px] border border-[#1A1410] bg-white p-7 shadow-[6px_6px_0_#1A1410] sm:p-8">
                    <div class="lg:hidden">
                        <div class="text-[12px] font-semibold uppercase tracking-[0.14em] text-[color:var(--mk-accent)]">Conta {{ appName }}</div>
                        <h1 class="mt-2 font-display text-[28px] font-extrabold tracking-[-0.03em]">Entrar</h1>
                        <p class="mt-2 text-[14px] text-[#5C4F44]">Acesse sua conta para comprar mais rápido.</p>
                    </div>
                    <h2 class="hidden font-display text-[26px] font-bold tracking-[-0.03em] lg:block">Entrar</h2>

                    <div
                        v-if="flashError || form.errors.email"
                        class="mt-5 rounded-[12px] border border-red-200 bg-red-50 px-4 py-3 text-[13px] text-red-700"
                    >
                        {{ flashError || form.errors.email }}
                    </div>

                    <form class="mt-6 space-y-4" @submit.prevent="submit">
                        <label class="block">
                            <span class="mb-1.5 block text-[12px] font-semibold text-[#3D332B]">E-mail</span>
                            <input v-model="form.email" type="email" required autocomplete="email" :class="field" placeholder="voce@email.com" />
                        </label>
                        <label class="block">
                            <span class="mb-1.5 flex items-center justify-between text-[12px] font-semibold text-[#3D332B]">
                                Senha
                                <Link href="/esqueci-senha" class="font-medium text-[color:var(--mk-accent)] hover:underline">Esqueci</Link>
                            </span>
                            <div class="relative">
                                <input
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    autocomplete="current-password"
                                    :class="field"
                                    class="pr-12"
                                    placeholder="••••••••"
                                />
                                <button
                                    type="button"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-[12px] font-semibold text-[#8A7B6E]"
                                    @click="showPassword = !showPassword"
                                >{{ showPassword ? 'Ocultar' : 'Mostrar' }}</button>
                            </div>
                            <p v-if="form.errors.password" class="mt-1 text-[12px] text-red-600">{{ form.errors.password }}</p>
                        </label>

                        <label class="flex items-center gap-2 text-[13px] text-[#5C4F44]">
                            <input v-model="form.remember" type="checkbox" class="rounded border-[#E2D7CB] text-[color:var(--mk-accent)] focus:ring-[color:var(--mk-accent)]" />
                            Manter conectado
                        </label>

                        <AuthTurnstileField
                            v-if="turnstileActive"
                            v-model="turnstileToken"
                            :config="login_turnstile"
                            :error="form.errors.turnstile_token"
                        />

                        <button
                            type="submit"
                            class="flex h-12 w-full items-center justify-center gap-2 rounded-[12px] bg-[color:var(--mk-accent)] text-[14px] font-bold text-white hover:brightness-95 disabled:opacity-60"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Entrando…' : 'Entrar' }}
                            <MkIcon v-if="!form.processing" name="arrow-right" :size="16" />
                        </button>
                    </form>

                    <p class="mt-6 text-center text-[14px] text-[#5C4F44]">
                        Ainda não tem conta?
                        <Link href="/criar-conta" class="font-semibold text-[#1A1410] hover:text-[color:var(--mk-accent)]">Criar conta</Link>
                    </p>
                </div>

                <p class="mt-5 text-center text-[13px] text-[#8A7B6E]">
                    Quer vender?
                    <Link href="/cadastro" class="font-semibold text-[#1A1410] hover:underline">Cadastre-se como vendedor</Link>
                </p>
            </div>
        </div>
    </MarketplaceLayout>
</template>
