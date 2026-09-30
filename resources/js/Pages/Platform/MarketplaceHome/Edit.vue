<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import {
    Check,
    ChevronDown,
    ChevronLeft,
    ChevronUp,
    Clock,
    Copy,
    ExternalLink,
    Eye,
    EyeOff,
    Flame,
    GripVertical,
    Image as ImageIcon,
    LayoutGrid,
    Layers,
    ListOrdered,
    Loader2,
    Megaphone,
    MessageCircleQuestion,
    Monitor,
    Palette,
    Plus,
    Redo2,
    RefreshCw,
    RotateCcw,
    ShieldCheck,
    Smartphone,
    Sparkles,
    Tablet,
    Timer,
    Trash2,
    Undo2,
    Users,
} from 'lucide-vue-next';
import LayoutPlatform from '@/Layouts/LayoutPlatform.vue';
import EditorField from '@/components/platform/home-editor/EditorField.vue';
import ImageField from '@/components/platform/home-editor/ImageField.vue';
import IconPicker from '@/components/platform/home-editor/IconPicker.vue';
import Segmented from '@/components/platform/home-editor/Segmented.vue';
import SwitchRow from '@/components/platform/home-editor/SwitchRow.vue';

const props = defineProps({
    draft: { type: Object, required: true },
    published: { type: Object, required: true },
    defaults: { type: Object, required: true },
    bannerDefaults: { type: Object, required: true },
    icons: { type: Array, default: () => [] },
    publishedAt: { type: String, default: null },
    maxBanners: { type: Number, default: 6 },
});

const clone = (v) => JSON.parse(JSON.stringify(v));
const snap = (v) => JSON.stringify(v);

const content = ref(clone(props.draft));
const savedSnap = ref(snap(props.draft));
const publishedSnap = ref(snap(props.published));
const publishedAtLocal = ref(props.publishedAt);

const dirty = computed(() => snap(content.value) !== savedSnap.value);
const hasUnpublished = computed(() => dirty.value || savedSnap.value !== publishedSnap.value);

/* ---------- Metadados das seções ---------- */
const META = {
    hero: { label: 'Destaque principal', desc: 'Headline, busca e vitrine', icon: Sparkles },
    trust: { label: 'Faixa de garantias', desc: 'Até 4 selos com ícone', icon: ShieldCheck },
    categories: { label: 'Categorias', desc: 'Blocos quadrados das categorias', icon: LayoutGrid },
    deals: { label: 'Ofertas do dia', desc: 'Bloco escuro com contagem', icon: Timer },
    popular: { label: 'Mais vendidos', desc: 'Grade de anúncios', icon: Flame },
    steps: { label: 'Como funciona', desc: 'Passo a passo numerado', icon: ListOrdered },
    recent: { label: 'Recém-chegados', desc: 'Últimos anúncios publicados', icon: Clock },
    sellers: { label: 'Vendedores', desc: 'Vendedores em destaque', icon: Users },
    questions: { label: 'Perguntas', desc: 'Perguntas respondidas', icon: MessageCircleQuestion },
    cta: { label: 'Chamada para vendedores', desc: 'Bloco final de conversão', icon: Megaphone },
    banner: { label: 'Banner', desc: 'Bloco promocional livre', icon: ImageIcon },
};

const tab = ref('sections');
const selected = ref(null);
const current = computed(() => content.value.sections.find((s) => s.id === selected.value) || null);
const bannerCount = computed(() => content.value.sections.filter((s) => s.type === 'banner').length);

function selectSection(id) {
    if (id === 'theme') {
        tab.value = 'theme';
        selected.value = null;
        return;
    }
    tab.value = 'sections';
    selected.value = id;
    postToFrame({ type: 'mk-home-focus', id });
}

function openTab(id) {
    tab.value = id;
    selected.value = null;
}

/* ---------- Reordenação (arrastar) ---------- */
const dragIndex = ref(null);
function onDragStart(i, e) {
    dragIndex.value = i;
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', String(i));
}
function onDragOver(i) {
    if (dragIndex.value === null || dragIndex.value === i) return;
    const list = content.value.sections;
    const [item] = list.splice(dragIndex.value, 1);
    list.splice(i, 0, item);
    dragIndex.value = i;
}
function onDragEnd() {
    dragIndex.value = null;
}
function moveSection(i, dir) {
    const list = content.value.sections;
    const j = i + dir;
    if (j < 0 || j >= list.length) return;
    [list[i], list[j]] = [list[j], list[i]];
}

/* ---------- Banners ---------- */
const bannerPresets = computed(() => [
    { key: 'dark', label: 'Escuro', swatch: '#1A1410', data: { bg_color: '#1A1410', tone: 'light', layout: 'split' } },
    { key: 'accent', label: 'Destaque', swatch: content.value.theme.accent, data: { bg_color: content.value.theme.accent, tone: 'light', layout: 'center' } },
    { key: 'light', label: 'Claro', swatch: '#F1EAE2', data: { bg_color: '#F1EAE2', tone: 'dark', layout: 'split' } },
    { key: 'cover', label: 'Foto de fundo', swatch: '#3D332B', data: { bg_color: '#1A1410', tone: 'light', layout: 'cover' } },
]);

function randomId() {
    return 'banner-' + Math.random().toString(36).slice(2, 10).padEnd(8, '0');
}
function addBanner(preset) {
    if (bannerCount.value >= props.maxBanners) return;
    const list = content.value.sections;
    const ctaIndex = list.findIndex((s) => s.type === 'cta');
    const section = { id: randomId(), type: 'banner', enabled: true, data: { ...clone(props.bannerDefaults), ...(preset?.data || {}) } };
    list.splice(ctaIndex === -1 ? list.length : ctaIndex, 0, section);
    addMenuOpen.value = false;
    nextTick(() => selectSection(section.id));
}
function duplicateBanner(section) {
    if (bannerCount.value >= props.maxBanners) return;
    const list = content.value.sections;
    const idx = list.findIndex((s) => s.id === section.id);
    const copy = { ...clone(section), id: randomId() };
    list.splice(idx + 1, 0, copy);
    nextTick(() => selectSection(copy.id));
}
function removeBanner(section) {
    if (!confirm('Remover este banner?')) return;
    content.value.sections = content.value.sections.filter((s) => s.id !== section.id);
    selected.value = null;
}
function applyPreset(section, preset) {
    Object.assign(section.data, preset.data);
}
const addMenuOpen = ref(false);

/* ---------- Listas ---------- */
function moveItem(arr, i, dir) {
    const j = i + dir;
    if (j < 0 || j >= arr.length) return;
    [arr[i], arr[j]] = [arr[j], arr[i]];
}

/* ---------- Tema ---------- */
const accentPresets = ['#FF5A1F', '#E11D48', '#DB2777', '#7C3AED', '#2563EB', '#0891B2', '#059669', '#CA8A04'];
const hexInput = ref(content.value.theme.accent);
watch(() => content.value.theme.accent, (v) => (hexInput.value = v));
function commitHex() {
    const v = hexInput.value.trim();
    if (/^#[0-9A-Fa-f]{6}$/.test(v)) content.value.theme.accent = v.toUpperCase();
    else hexInput.value = content.value.theme.accent;
}

/* ---------- Histórico (desfazer / refazer) ---------- */
const history = ref([snap(content.value)]);
const hIndex = ref(0);
let historyTimer = null;
watch(
    content,
    () => {
        clearTimeout(historyTimer);
        historyTimer = setTimeout(() => {
            const s = snap(content.value);
            if (s === history.value[hIndex.value]) return;
            history.value = history.value.slice(0, hIndex.value + 1);
            history.value.push(s);
            if (history.value.length > 80) history.value.shift();
            hIndex.value = history.value.length - 1;
        }, 400);
    },
    { deep: true }
);
const canUndo = computed(() => hIndex.value > 0);
const canRedo = computed(() => hIndex.value < history.value.length - 1);
function undo() {
    if (!canUndo.value) return;
    hIndex.value--;
    content.value = JSON.parse(history.value[hIndex.value]);
}
function redo() {
    if (!canRedo.value) return;
    hIndex.value++;
    content.value = JSON.parse(history.value[hIndex.value]);
}

/* ---------- Salvamento automático / publicação ---------- */
const saveState = ref('idle');
const publishing = ref(false);
const toast = ref('');
let toastTimer = null;
function showToast(msg) {
    toast.value = msg;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => (toast.value = ''), 2600);
}

let saveTimer = null;
watch(
    content,
    () => {
        if (!dirty.value) return;
        clearTimeout(saveTimer);
        saveTimer = setTimeout(saveDraft, 1200);
    },
    { deep: true }
);

async function saveDraft() {
    clearTimeout(saveTimer);
    if (!dirty.value) return;
    const payload = clone(content.value);
    const sent = snap(payload);
    saveState.value = 'saving';
    try {
        await window.axios.put('/plataforma/pagina-inicial/rascunho', { content: payload });
        savedSnap.value = sent;
        saveState.value = 'saved';
    } catch {
        saveState.value = 'error';
    }
}

async function publish() {
    publishing.value = true;
    try {
        const { data } = await window.axios.post('/plataforma/pagina-inicial/publicar', { content: clone(content.value) });
        content.value = data.content;
        savedSnap.value = snap(data.content);
        publishedSnap.value = snap(data.content);
        publishedAtLocal.value = data.publishedAt;
        saveState.value = 'idle';
        showToast('Página inicial publicada');
    } catch {
        showToast('Não foi possível publicar. Tente novamente.');
    } finally {
        publishing.value = false;
    }
}

async function discard() {
    if (!confirm('Descartar todas as alterações não publicadas?')) return;
    clearTimeout(saveTimer);
    const { data } = await window.axios.post('/plataforma/pagina-inicial/descartar');
    content.value = data.content;
    savedSnap.value = snap(data.content);
    selected.value = null;
    showToast('Rascunho descartado');
}

function resetDefaults() {
    if (!confirm('Restaurar o conteúdo padrão? Seus banners serão removidos (você pode desfazer).')) return;
    content.value = clone(props.defaults);
    selected.value = null;
}

const statusLabel = computed(() => {
    if (saveState.value === 'saving') return { text: 'Salvando…', tone: 'zinc' };
    if (saveState.value === 'error') return { text: 'Erro ao salvar', tone: 'red' };
    if (dirty.value) return { text: 'Alterações não salvas', tone: 'zinc' };
    if (hasUnpublished.value) return { text: 'Rascunho não publicado', tone: 'amber' };
    return { text: 'Publicado', tone: 'emerald' };
});

function formatDate(iso) {
    if (!iso) return '';
    return new Date(iso).toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' });
}

/* ---------- Prévia ---------- */
const DEVICES = {
    desktop: { w: 1280, icon: Monitor, label: 'Computador' },
    tablet: { w: 820, icon: Tablet, label: 'Tablet' },
    mobile: { w: 390, icon: Smartphone, label: 'Celular' },
};
const device = ref('desktop');
const frame = ref(null);
const frameKey = ref(0);
const frameReady = ref(false);
const box = ref(null);
const boxSize = ref({ w: 1000, h: 700 });
let ro = null;

const deviceW = computed(() => DEVICES[device.value].w);
const scale = computed(() => Math.min(1, (boxSize.value.w - 48) / deviceW.value));
const frameStyle = computed(() => ({
    width: `${deviceW.value}px`,
    height: `${(boxSize.value.h - 48) / scale.value}px`,
    transform: `scale(${scale.value})`,
    transformOrigin: 'top left',
}));
const wrapStyle = computed(() => ({
    width: `${deviceW.value * scale.value}px`,
    height: `${boxSize.value.h - 48}px`,
}));

function postToFrame(msg) {
    const win = frame.value?.contentWindow;
    if (win && frameReady.value) win.postMessage(msg, window.location.origin);
}
let raf = null;
watch(
    content,
    () => {
        cancelAnimationFrame(raf);
        raf = requestAnimationFrame(() => postToFrame({ type: 'mk-home-content', content: clone(content.value) }));
    },
    { deep: true }
);

function reloadFrame() {
    frameReady.value = false;
    frameKey.value++;
}

function onMessage(e) {
    if (e.origin !== window.location.origin || !e.data) return;
    if (e.data.type === 'mk-home-ready') {
        frameReady.value = true;
        postToFrame({ type: 'mk-home-content', content: clone(content.value) });
        if (selected.value) postToFrame({ type: 'mk-home-focus', id: selected.value });
    }
    if (e.data.type === 'mk-home-select') selectSection(e.data.id);
}

function onKey(e) {
    const mod = e.ctrlKey || e.metaKey;
    if (!mod) return;
    const k = e.key.toLowerCase();
    if (k === 's') {
        e.preventDefault();
        saveDraft();
        return;
    }
    const inField = ['INPUT', 'TEXTAREA'].includes(document.activeElement?.tagName);
    if (inField) return;
    if (k === 'z' && !e.shiftKey) {
        e.preventDefault();
        undo();
    } else if ((k === 'z' && e.shiftKey) || k === 'y') {
        e.preventDefault();
        redo();
    }
}

function onBeforeUnload(e) {
    if (dirty.value || saveState.value === 'saving') {
        e.preventDefault();
        e.returnValue = '';
    }
}

onMounted(() => {
    window.addEventListener('message', onMessage);
    window.addEventListener('keydown', onKey);
    window.addEventListener('beforeunload', onBeforeUnload);
    ro = new ResizeObserver(([entry]) => {
        boxSize.value = { w: entry.contentRect.width, h: entry.contentRect.height };
    });
    if (box.value) ro.observe(box.value);
});
onUnmounted(() => {
    window.removeEventListener('message', onMessage);
    window.removeEventListener('keydown', onKey);
    window.removeEventListener('beforeunload', onBeforeUnload);
    ro?.disconnect();
    clearTimeout(saveTimer);
    clearTimeout(historyTimer);
});

const rangeClass = 'w-full accent-zinc-900 dark:accent-white';
const listCard = 'rounded-lg border border-zinc-200 bg-zinc-50/60 p-3 dark:border-zinc-800 dark:bg-zinc-900/60';
const miniBtn = 'flex h-6 w-6 items-center justify-center rounded text-zinc-400 hover:bg-zinc-200 hover:text-zinc-900 disabled:opacity-30 dark:hover:bg-zinc-800 dark:hover:text-zinc-100';
const addBtn = 'flex w-full items-center justify-center gap-1.5 rounded-lg border border-dashed border-zinc-300 py-2 text-[12px] font-medium text-zinc-500 hover:border-zinc-900 hover:text-zinc-900 disabled:opacity-40 dark:border-zinc-700 dark:hover:border-zinc-300 dark:hover:text-zinc-100';
</script>

<template>
    <Head title="Página inicial" />
    <LayoutPlatform>
        <div class="-mx-4 -mb-12 -mt-4 flex h-[calc(100vh-8.5rem)] min-h-[640px] flex-col overflow-hidden bg-zinc-50 md:-mx-6 md:-mt-6 lg:-mb-8 dark:bg-zinc-950">
            <!-- Barra superior -->
            <header class="flex shrink-0 flex-wrap items-center gap-3 border-b border-zinc-200 bg-white px-4 py-2.5 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="mr-auto flex min-w-0 items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-900 text-white dark:bg-white dark:text-zinc-900">
                        <Layers class="h-4 w-4" />
                    </div>
                    <div class="min-w-0">
                        <div class="text-[14px] font-semibold text-zinc-900 dark:text-white">Página inicial</div>
                        <div class="flex items-center gap-1.5 text-[11px]">
                            <span
                                class="h-1.5 w-1.5 rounded-full"
                                :class="{
                                    'bg-emerald-500': statusLabel.tone === 'emerald',
                                    'bg-amber-500': statusLabel.tone === 'amber',
                                    'bg-red-500': statusLabel.tone === 'red',
                                    'bg-zinc-400': statusLabel.tone === 'zinc',
                                }"
                            />
                            <span class="text-zinc-500">{{ statusLabel.text }}</span>
                            <span v-if="publishedAtLocal && statusLabel.tone === 'emerald'" class="text-zinc-400">· {{ formatDate(publishedAtLocal) }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-0.5 rounded-lg bg-zinc-100 p-0.5 dark:bg-zinc-800">
                    <button
                        v-for="(d, key) in DEVICES"
                        :key="key"
                        type="button"
                        class="flex h-7 w-8 items-center justify-center rounded-md transition"
                        :class="device === key ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-950 dark:text-white' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-white'"
                        :title="d.label"
                        @click="device = key"
                    ><component :is="d.icon" class="h-4 w-4" /></button>
                </div>

                <div class="flex items-center gap-1">
                    <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900 disabled:opacity-30 dark:hover:bg-zinc-800 dark:hover:text-white" :disabled="!canUndo" title="Desfazer (Ctrl+Z)" @click="undo"><Undo2 class="h-4 w-4" /></button>
                    <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900 disabled:opacity-30 dark:hover:bg-zinc-800 dark:hover:text-white" :disabled="!canRedo" title="Refazer (Ctrl+Shift+Z)" @click="redo"><Redo2 class="h-4 w-4" /></button>
                    <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900 dark:hover:bg-zinc-800 dark:hover:text-white" title="Recarregar prévia" @click="reloadFrame"><RefreshCw class="h-4 w-4" /></button>
                    <a href="/" target="_blank" rel="noopener" class="flex h-8 w-8 items-center justify-center rounded-lg text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900 dark:hover:bg-zinc-800 dark:hover:text-white" title="Abrir site publicado"><ExternalLink class="h-4 w-4" /></a>
                </div>

                <button
                    v-if="hasUnpublished && savedSnap !== publishedSnap"
                    type="button"
                    class="rounded-lg px-3 py-1.5 text-[13px] font-medium text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800"
                    @click="discard"
                >Descartar</button>
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-1.5 text-[13px] font-semibold text-white hover:bg-black disabled:opacity-40 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                    :disabled="publishing || !hasUnpublished"
                    @click="publish"
                >
                    <Loader2 v-if="publishing" class="h-3.5 w-3.5 animate-spin" />
                    <Check v-else-if="!hasUnpublished" class="h-3.5 w-3.5" />
                    {{ hasUnpublished ? 'Publicar' : 'Publicado' }}
                </button>
            </header>

            <div class="flex min-h-0 flex-1">
                <!-- Painel lateral -->
                <aside class="flex w-[360px] shrink-0 flex-col border-r border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex shrink-0 border-b border-zinc-200 px-2 dark:border-zinc-800">
                        <button
                            v-for="t in [{ id: 'sections', label: 'Seções', icon: Layers }, { id: 'theme', label: 'Tema', icon: Palette }]"
                            :key="t.id"
                            type="button"
                            class="relative flex items-center gap-1.5 px-3 py-3 text-[13px] font-medium transition"
                            :class="tab === t.id ? 'text-zinc-900 dark:text-white' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-white'"
                            @click="openTab(t.id)"
                        >
                            <component :is="t.icon" class="h-3.5 w-3.5" /> {{ t.label }}
                            <span v-if="tab === t.id" class="absolute inset-x-2 -bottom-px h-0.5 rounded-full bg-zinc-900 dark:bg-white" />
                        </button>
                    </div>

                    <div class="min-h-0 flex-1 overflow-y-auto">
                        <!-- LISTA DE SEÇÕES -->
                        <div v-if="tab === 'sections' && !current" class="p-3">
                            <p class="mb-3 px-1 text-[12px] leading-relaxed text-zinc-500">
                                Arraste para reordenar, use o olho para ocultar e clique para editar. Também dá para clicar direto na prévia.
                            </p>
                            <ul class="space-y-1">
                                <li
                                    v-for="(s, i) in content.sections"
                                    :key="s.id"
                                    draggable="true"
                                    class="group flex items-center gap-2 rounded-lg border px-2 py-2 transition"
                                    :class="[
                                        dragIndex === i ? 'border-zinc-900 bg-zinc-100 dark:border-white dark:bg-zinc-800' : 'border-transparent hover:border-zinc-200 hover:bg-zinc-50 dark:hover:border-zinc-700 dark:hover:bg-zinc-800/60',
                                        s.enabled ? '' : 'opacity-50',
                                    ]"
                                    @dragstart="onDragStart(i, $event)"
                                    @dragover.prevent="onDragOver(i)"
                                    @dragend="onDragEnd"
                                    @drop.prevent="onDragEnd"
                                >
                                    <GripVertical class="h-4 w-4 shrink-0 cursor-grab text-zinc-300 group-hover:text-zinc-500 dark:text-zinc-600" />
                                    <button type="button" class="flex min-w-0 flex-1 items-center gap-2.5 text-left" @click="selectSection(s.id)">
                                        <span
                                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md"
                                            :class="s.type === 'banner' ? 'text-white' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300'"
                                            :style="s.type === 'banner' ? { background: s.data.bg_color } : {}"
                                        ><component :is="META[s.type].icon" class="h-4 w-4" /></span>
                                        <span class="min-w-0">
                                            <span class="block truncate text-[13px] font-medium text-zinc-900 dark:text-white">{{ META[s.type].label }}</span>
                                            <span class="block truncate text-[11px] text-zinc-500">{{ s.type === 'banner' ? s.data.title : META[s.type].desc }}</span>
                                        </span>
                                    </button>
                                    <div class="flex items-center opacity-0 transition group-hover:opacity-100">
                                        <button type="button" :class="miniBtn" :disabled="i === 0" title="Subir" @click="moveSection(i, -1)"><ChevronUp class="h-3.5 w-3.5" /></button>
                                        <button type="button" :class="miniBtn" :disabled="i === content.sections.length - 1" title="Descer" @click="moveSection(i, 1)"><ChevronDown class="h-3.5 w-3.5" /></button>
                                    </div>
                                    <button
                                        type="button"
                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-zinc-400 hover:bg-zinc-200 hover:text-zinc-900 dark:hover:bg-zinc-700 dark:hover:text-white"
                                        :title="s.enabled ? 'Ocultar' : 'Mostrar'"
                                        @click="s.enabled = !s.enabled"
                                    >
                                        <Eye v-if="s.enabled" class="h-4 w-4" />
                                        <EyeOff v-else class="h-4 w-4" />
                                    </button>
                                </li>
                            </ul>

                            <div class="relative mt-3">
                                <button type="button" :class="addBtn" :disabled="bannerCount >= maxBanners" @click="addMenuOpen = !addMenuOpen">
                                    <Plus class="h-3.5 w-3.5" /> Adicionar banner
                                    <span class="text-zinc-400">({{ bannerCount }}/{{ maxBanners }})</span>
                                </button>
                                <div v-if="addMenuOpen" class="mt-2 grid grid-cols-2 gap-2">
                                    <button
                                        v-for="p in bannerPresets"
                                        :key="p.key"
                                        type="button"
                                        class="group overflow-hidden rounded-lg border border-zinc-200 text-left transition hover:border-zinc-900 dark:border-zinc-700 dark:hover:border-zinc-300"
                                        @click="addBanner(p)"
                                    >
                                        <div class="flex h-14 items-end p-2" :style="{ background: p.swatch }">
                                            <span class="h-1.5 w-10 rounded-full" :class="p.data.tone === 'light' ? 'bg-white/80' : 'bg-zinc-900/70'" />
                                        </div>
                                        <div class="px-2 py-1.5 text-[12px] font-medium text-zinc-700 dark:text-zinc-300">{{ p.label }}</div>
                                    </button>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="mt-6 flex w-full items-center justify-center gap-1.5 py-2 text-[12px] text-zinc-400 hover:text-red-600"
                                @click="resetDefaults"
                            ><RotateCcw class="h-3.5 w-3.5" /> Restaurar conteúdo padrão</button>
                        </div>

                        <!-- EDITOR DE SEÇÃO -->
                        <div v-else-if="tab === 'sections' && current">
                            <div class="sticky top-0 z-10 flex items-center gap-2 border-b border-zinc-200 bg-white/95 px-3 py-2.5 backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/95">
                                <button type="button" class="flex h-7 w-7 items-center justify-center rounded-md text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900 dark:hover:bg-zinc-800 dark:hover:text-white" @click="selected = null">
                                    <ChevronLeft class="h-4 w-4" />
                                </button>
                                <component :is="META[current.type].icon" class="h-4 w-4 text-zinc-500" />
                                <span class="flex-1 truncate text-[13px] font-semibold text-zinc-900 dark:text-white">{{ META[current.type].label }}</span>
                                <button
                                    type="button"
                                    class="flex items-center gap-1 rounded-md px-2 py-1 text-[11px] font-medium"
                                    :class="current.enabled ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800'"
                                    @click="current.enabled = !current.enabled"
                                >
                                    <Eye v-if="current.enabled" class="h-3 w-3" /><EyeOff v-else class="h-3 w-3" />
                                    {{ current.enabled ? 'Visível' : 'Oculta' }}
                                </button>
                            </div>

                            <div class="space-y-5 p-4">
                                <!-- HERO -->
                                <template v-if="current.type === 'hero'">
                                    <Segmented
                                        v-model="content.hero.badge_mode"
                                        label="Selo acima do título"
                                        :options="[{ value: 'live', label: 'Contador' }, { value: 'custom', label: 'Texto' }, { value: 'off', label: 'Oculto' }]"
                                    />
                                    <EditorField v-if="content.hero.badge_mode === 'custom'" v-model="content.hero.badge_text" label="Texto do selo" :max="60" />
                                    <p v-else-if="content.hero.badge_mode === 'live'" class="-mt-3 text-[11px] text-zinc-500">Mostra a quantidade real de anúncios ativos.</p>

                                    <EditorField v-model="content.hero.title" label="Título" :max="120" multiline :rows="2" />
                                    <EditorField v-model="content.hero.highlight" label="Destaque colorido" :max="80" hint="Aparece logo após o título, na cor de destaque. Deixe vazio para não usar." />
                                    <EditorField v-model="content.hero.subtitle" label="Subtítulo" :max="300" multiline />

                                    <div class="grid grid-cols-[1fr_110px] gap-2">
                                        <EditorField v-model="content.hero.search_placeholder" label="Texto da busca" :max="80" />
                                        <EditorField v-model="content.hero.search_button" label="Botão" :max="24" />
                                    </div>

                                    <div :class="listCard" class="space-y-3">
                                        <SwitchRow v-model="content.hero.show_quick_tags" label="Atalhos de categorias" hint="Chips com as 5 primeiras categorias" />
                                        <EditorField v-if="content.hero.show_quick_tags" v-model="content.hero.quick_tags_label" label="Rótulo" :max="30" />
                                    </div>

                                    <Segmented
                                        v-model="content.hero.visual"
                                        label="Lado direito"
                                        :options="[{ value: 'listings', label: 'Anúncios' }, { value: 'image', label: 'Imagem' }, { value: 'none', label: 'Só texto' }]"
                                    />
                                    <ImageField v-if="content.hero.visual === 'image'" v-model="content.hero.image" label="Imagem do destaque" hint="Formato vertical ou quadrado funciona melhor." />
                                    <p v-else-if="content.hero.visual === 'listings'" class="-mt-3 text-[11px] text-zinc-500">Mostra os 3 anúncios mais recentes em mosaico.</p>
                                    <p v-else class="-mt-3 text-[11px] text-zinc-500">O texto fica centralizado, ocupando a largura toda.</p>

                                    <div v-if="content.hero.visual !== 'none'" :class="listCard" class="space-y-3">
                                        <SwitchRow v-model="content.hero.trust_card_enabled" label="Selo flutuante" hint="Cartão sobre a vitrine" />
                                        <template v-if="content.hero.trust_card_enabled">
                                            <EditorField v-model="content.hero.trust_card_title" label="Título" :max="40" />
                                            <EditorField v-model="content.hero.trust_card_text" label="Texto" :max="60" />
                                        </template>
                                    </div>
                                </template>

                                <!-- GARANTIAS -->
                                <template v-else-if="current.type === 'trust'">
                                    <div v-for="(item, i) in content.trust.items" :key="i" :class="listCard" class="space-y-2.5">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[11px] font-semibold uppercase tracking-wide text-zinc-400">Selo {{ i + 1 }}</span>
                                            <div class="flex">
                                                <button type="button" :class="miniBtn" :disabled="i === 0" @click="moveItem(content.trust.items, i, -1)"><ChevronUp class="h-3.5 w-3.5" /></button>
                                                <button type="button" :class="miniBtn" :disabled="i === content.trust.items.length - 1" @click="moveItem(content.trust.items, i, 1)"><ChevronDown class="h-3.5 w-3.5" /></button>
                                                <button type="button" :class="miniBtn" @click="content.trust.items.splice(i, 1)"><Trash2 class="h-3.5 w-3.5" /></button>
                                            </div>
                                        </div>
                                        <IconPicker v-model="item.icon" :icons="icons" />
                                        <EditorField v-model="item.title" label="Título" :max="40" />
                                        <EditorField v-model="item.text" label="Texto" :max="60" />
                                    </div>
                                    <button type="button" :class="addBtn" :disabled="content.trust.items.length >= 4" @click="content.trust.items.push({ icon: 'check', title: 'Novo selo', text: '' })">
                                        <Plus class="h-3.5 w-3.5" /> Adicionar selo
                                    </button>
                                </template>

                                <!-- CATEGORIAS -->
                                <template v-else-if="current.type === 'categories'">
                                    <EditorField v-model="content.categories.title" label="Título" :max="60" />
                                    <EditorField v-model="content.categories.link_label" label="Texto do link" :max="30" hint="Deixe vazio para esconder o link." />
                                    <EditorField label="Quantidade exibida">
                                        <div class="flex items-center gap-3">
                                            <input v-model.number="content.categories.limit" type="range" min="4" max="16" step="4" :class="rangeClass" />
                                            <span class="w-6 text-right text-[13px] font-semibold tabular-nums">{{ content.categories.limit }}</span>
                                        </div>
                                    </EditorField>
                                    <div class="rounded-lg bg-zinc-100 p-3 text-[12px] text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                        Nome, ordem e imagem de cada categoria ficam em
                                        <Link href="/plataforma/categorias-marketplace" class="font-semibold underline">Categorias Marketplace</Link>.
                                    </div>
                                </template>

                                <!-- OFERTAS -->
                                <template v-else-if="current.type === 'deals'">
                                    <EditorField v-model="content.deals.eyebrow" label="Texto acima do título" :max="40" />
                                    <EditorField v-model="content.deals.title" label="Título" :max="60" />
                                    <Segmented
                                        v-model="content.deals.source"
                                        label="Quais anúncios mostrar"
                                        :options="[{ value: 'popular', label: 'Mais vendidos' }, { value: 'featured', label: 'Mais recentes' }]"
                                    />
                                    <EditorField label="Quantidade">
                                        <div class="flex items-center gap-3">
                                            <input v-model.number="content.deals.limit" type="range" min="2" max="8" step="2" :class="rangeClass" />
                                            <span class="w-6 text-right text-[13px] font-semibold tabular-nums">{{ content.deals.limit }}</span>
                                        </div>
                                    </EditorField>
                                    <SwitchRow v-model="content.deals.show_countdown" label="Contagem regressiva" hint="Conta até a meia-noite" />
                                </template>

                                <!-- MAIS VENDIDOS / RECENTES -->
                                <template v-else-if="current.type === 'popular' || current.type === 'recent'">
                                    <EditorField v-model="content[current.type].title" label="Título" :max="60" />
                                    <EditorField v-model="content[current.type].subtitle" label="Subtítulo" :max="140" />
                                    <EditorField v-if="current.type === 'recent'" v-model="content.recent.badge" label="Etiqueta nos cards" :max="16" hint="Ex.: Novo. Deixe vazio para não mostrar." />
                                    <EditorField label="Quantidade de anúncios">
                                        <div class="flex items-center gap-3">
                                            <input v-model.number="content[current.type].limit" type="range" min="2" max="20" :class="rangeClass" />
                                            <span class="w-6 text-right text-[13px] font-semibold tabular-nums">{{ content[current.type].limit }}</span>
                                        </div>
                                    </EditorField>
                                    <Segmented
                                        v-model="content[current.type].columns"
                                        label="Colunas no computador"
                                        :options="[3, 4, 5, 6].map((n) => ({ value: n, label: String(n) }))"
                                    />
                                </template>

                                <!-- COMO FUNCIONA -->
                                <template v-else-if="current.type === 'steps'">
                                    <EditorField v-model="content.steps.title" label="Título" :max="60" />
                                    <EditorField v-model="content.steps.subtitle" label="Subtítulo" :max="140" multiline :rows="2" />
                                    <div v-for="(item, i) in content.steps.items" :key="i" :class="listCard" class="space-y-2.5">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[11px] font-semibold uppercase tracking-wide text-zinc-400">Etapa {{ String(i + 1).padStart(2, '0') }}</span>
                                            <div class="flex">
                                                <button type="button" :class="miniBtn" :disabled="i === 0" @click="moveItem(content.steps.items, i, -1)"><ChevronUp class="h-3.5 w-3.5" /></button>
                                                <button type="button" :class="miniBtn" :disabled="i === content.steps.items.length - 1" @click="moveItem(content.steps.items, i, 1)"><ChevronDown class="h-3.5 w-3.5" /></button>
                                                <button type="button" :class="miniBtn" @click="content.steps.items.splice(i, 1)"><Trash2 class="h-3.5 w-3.5" /></button>
                                            </div>
                                        </div>
                                        <EditorField v-model="item.title" label="Título" :max="50" />
                                        <EditorField v-model="item.text" label="Texto" :max="200" multiline :rows="2" />
                                    </div>
                                    <button type="button" :class="addBtn" :disabled="content.steps.items.length >= 4" @click="content.steps.items.push({ title: 'Nova etapa', text: '' })">
                                        <Plus class="h-3.5 w-3.5" /> Adicionar etapa
                                    </button>
                                </template>

                                <!-- VENDEDORES -->
                                <template v-else-if="current.type === 'sellers'">
                                    <EditorField v-model="content.sellers.title" label="Título" :max="60" />
                                    <EditorField v-model="content.sellers.subtitle" label="Subtítulo" :max="140" />
                                    <EditorField label="Quantidade de vendedores">
                                        <div class="flex items-center gap-3">
                                            <input v-model.number="content.sellers.limit" type="range" min="2" max="12" :class="rangeClass" />
                                            <span class="w-6 text-right text-[13px] font-semibold tabular-nums">{{ content.sellers.limit }}</span>
                                        </div>
                                    </EditorField>
                                </template>

                                <!-- PERGUNTAS -->
                                <template v-else-if="current.type === 'questions'">
                                    <EditorField v-model="content.questions.title" label="Título" :max="60" />
                                    <EditorField label="Quantidade de perguntas">
                                        <div class="flex items-center gap-3">
                                            <input v-model.number="content.questions.limit" type="range" min="3" max="12" step="3" :class="rangeClass" />
                                            <span class="w-6 text-right text-[13px] font-semibold tabular-nums">{{ content.questions.limit }}</span>
                                        </div>
                                    </EditorField>
                                    <p class="text-[11px] text-zinc-500">Na página publicada, a seção só aparece quando existem perguntas respondidas.</p>
                                </template>

                                <!-- CTA -->
                                <template v-else-if="current.type === 'cta'">
                                    <Segmented
                                        v-model="content.cta.style"
                                        label="Estilo"
                                        :options="[{ value: 'accent', label: 'Cor destaque' }, { value: 'dark', label: 'Escuro' }, { value: 'light', label: 'Claro' }]"
                                    />
                                    <EditorField v-model="content.cta.title" label="Título" :max="120" multiline :rows="2" hint="Use Enter para quebrar a linha." />
                                    <div class="space-y-2">
                                        <div class="text-[12px] font-semibold text-zinc-700 dark:text-zinc-300">Benefícios</div>
                                        <div v-for="(b, i) in content.cta.bullets" :key="i" class="flex items-center gap-1.5">
                                            <input
                                                v-model="content.cta.bullets[i]"
                                                maxlength="80"
                                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-[13px] outline-none focus:border-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
                                            />
                                            <button type="button" :class="miniBtn" @click="content.cta.bullets.splice(i, 1)"><Trash2 class="h-3.5 w-3.5" /></button>
                                        </div>
                                        <button type="button" :class="addBtn" :disabled="content.cta.bullets.length >= 5" @click="content.cta.bullets.push('')">
                                            <Plus class="h-3.5 w-3.5" /> Adicionar benefício
                                        </button>
                                    </div>
                                    <EditorField v-model="content.cta.note" label="Texto acima dos botões" :max="60" />
                                    <div :class="listCard" class="space-y-2.5">
                                        <div class="text-[11px] font-semibold uppercase tracking-wide text-zinc-400">Botão principal</div>
                                        <EditorField v-model="content.cta.primary_label" label="Texto" :max="40" />
                                        <EditorField v-model="content.cta.primary_url" label="Link" placeholder="/cadastro" />
                                    </div>
                                    <div :class="listCard" class="space-y-2.5">
                                        <div class="text-[11px] font-semibold uppercase tracking-wide text-zinc-400">Botão secundário</div>
                                        <EditorField v-model="content.cta.secondary_label" label="Texto" :max="40" hint="Deixe vazio para esconder." />
                                        <EditorField v-model="content.cta.secondary_url" label="Link" placeholder="/login" />
                                    </div>
                                </template>

                                <!-- BANNER -->
                                <template v-else-if="current.type === 'banner'">
                                    <div>
                                        <div class="mb-1.5 text-[12px] font-semibold text-zinc-700 dark:text-zinc-300">Estilo rápido</div>
                                        <div class="grid grid-cols-4 gap-1.5">
                                            <button
                                                v-for="p in bannerPresets"
                                                :key="p.key"
                                                type="button"
                                                class="overflow-hidden rounded-md border border-zinc-200 text-[10px] font-medium text-zinc-600 hover:border-zinc-900 dark:border-zinc-700 dark:text-zinc-300"
                                                @click="applyPreset(current, p)"
                                            >
                                                <div class="h-6" :style="{ background: p.swatch }" />
                                                <div class="truncate px-1 py-1">{{ p.label }}</div>
                                            </button>
                                        </div>
                                    </div>
                                    <Segmented
                                        v-model="current.data.layout"
                                        label="Formato"
                                        :options="[{ value: 'split', label: 'Texto + imagem' }, { value: 'center', label: 'Centralizado' }, { value: 'cover', label: 'Foto de fundo' }]"
                                    />
                                    <EditorField v-model="current.data.eyebrow" label="Texto acima do título" :max="40" />
                                    <EditorField v-model="current.data.title" label="Título" :max="100" multiline :rows="2" />
                                    <EditorField v-model="current.data.text" label="Texto" :max="240" multiline />
                                    <div class="grid grid-cols-2 gap-2">
                                        <EditorField v-model="current.data.button_label" label="Botão" :max="30" />
                                        <EditorField v-model="current.data.button_url" label="Link do botão" placeholder="/buscar" />
                                    </div>
                                    <ImageField
                                        v-if="current.data.layout !== 'center'"
                                        v-model="current.data.image"
                                        label="Imagem"
                                        :hint="current.data.layout === 'cover' ? 'Imagem larga, fica atrás do texto.' : 'Aparece ao lado do texto.'"
                                    />
                                    <div class="grid grid-cols-[1fr_auto] items-end gap-2">
                                        <EditorField label="Cor de fundo">
                                            <div class="flex items-center gap-2">
                                                <input v-model="current.data.bg_color" type="color" class="h-9 w-12 cursor-pointer rounded-md border border-zinc-300 bg-white p-0.5 dark:border-zinc-700" />
                                                <span class="font-mono text-[12px] uppercase text-zinc-500">{{ current.data.bg_color }}</span>
                                            </div>
                                        </EditorField>
                                        <Segmented
                                            v-model="current.data.tone"
                                            :options="[{ value: 'light', label: 'Texto claro' }, { value: 'dark', label: 'Texto escuro' }]"
                                        />
                                    </div>
                                    <div class="flex gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-800">
                                        <button
                                            type="button"
                                            class="flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-zinc-200 py-2 text-[12px] font-medium text-zinc-700 hover:border-zinc-900 disabled:opacity-40 dark:border-zinc-700 dark:text-zinc-300"
                                            :disabled="bannerCount >= maxBanners"
                                            @click="duplicateBanner(current)"
                                        ><Copy class="h-3.5 w-3.5" /> Duplicar</button>
                                        <button
                                            type="button"
                                            class="flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-red-200 py-2 text-[12px] font-medium text-red-600 hover:bg-red-50 dark:border-red-500/30 dark:hover:bg-red-500/10"
                                            @click="removeBanner(current)"
                                        ><Trash2 class="h-3.5 w-3.5" /> Remover</button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- TEMA -->
                        <div v-else-if="tab === 'theme'" class="space-y-6 p-4">
                            <div>
                                <div class="mb-1 text-[12px] font-semibold text-zinc-700 dark:text-zinc-300">Cor de destaque</div>
                                <p class="mb-3 text-[11px] text-zinc-500">Usada em botões, preços em destaque, logo e detalhes de todo o marketplace.</p>
                                <div class="grid grid-cols-8 gap-1.5">
                                    <button
                                        v-for="color in accentPresets"
                                        :key="color"
                                        type="button"
                                        class="relative aspect-square rounded-lg ring-offset-2 transition hover:scale-105 dark:ring-offset-zinc-900"
                                        :class="content.theme.accent === color ? 'ring-2 ring-zinc-900 dark:ring-white' : ''"
                                        :style="{ background: color }"
                                        :title="color"
                                        @click="content.theme.accent = color"
                                    >
                                        <Check v-if="content.theme.accent === color" class="absolute inset-0 m-auto h-3.5 w-3.5 text-white" />
                                    </button>
                                </div>
                                <div class="mt-3 flex items-center gap-2">
                                    <input v-model="content.theme.accent" type="color" class="h-9 w-12 cursor-pointer rounded-md border border-zinc-300 bg-white p-0.5 dark:border-zinc-700" />
                                    <input
                                        v-model="hexInput"
                                        maxlength="7"
                                        class="w-28 rounded-lg border border-zinc-300 bg-white px-3 py-2 font-mono text-[13px] uppercase outline-none focus:border-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
                                        @blur="commitHex"
                                        @keydown.enter.prevent="commitHex"
                                    />
                                </div>
                            </div>

                            <div class="space-y-3 border-t border-zinc-200 pt-5 dark:border-zinc-800">
                                <SwitchRow v-model="content.theme.topbar_enabled" label="Faixa superior" hint="Barra escura acima do menu, em todas as páginas" />
                                <template v-if="content.theme.topbar_enabled">
                                    <div v-for="(item, i) in content.theme.topbar_items" :key="i" :class="listCard" class="space-y-2.5">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[11px] font-semibold uppercase tracking-wide text-zinc-400">Mensagem {{ i + 1 }}</span>
                                            <button type="button" :class="miniBtn" @click="content.theme.topbar_items.splice(i, 1)"><Trash2 class="h-3.5 w-3.5" /></button>
                                        </div>
                                        <IconPicker v-model="item.icon" :icons="icons" />
                                        <EditorField v-model="item.text" label="Texto" :max="80" />
                                    </div>
                                    <button type="button" :class="addBtn" :disabled="content.theme.topbar_items.length >= 3" @click="content.theme.topbar_items.push({ icon: 'check', text: '' })">
                                        <Plus class="h-3.5 w-3.5" /> Adicionar mensagem
                                    </button>
                                    <p class="text-[11px] text-zinc-500">No celular só a primeira mensagem aparece.</p>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="shrink-0 border-t border-zinc-200 px-4 py-2.5 text-[11px] text-zinc-400 dark:border-zinc-800">
                        Salvo automaticamente como rascunho. Visitantes só veem após <strong class="font-semibold text-zinc-500">Publicar</strong>.
                    </div>
                </aside>

                <!-- Prévia -->
                <main ref="box" class="relative min-w-0 flex-1 overflow-hidden bg-[radial-gradient(circle_at_1px_1px,rgba(0,0,0,0.07)_1px,transparent_0)] [background-size:18px_18px] dark:bg-[radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.06)_1px,transparent_0)]">
                    <div class="absolute inset-0 flex justify-center p-6">
                        <div
                            class="relative overflow-hidden bg-white shadow-[0_20px_60px_-20px_rgba(0,0,0,0.35)] ring-1 ring-black/10 transition-[width] duration-300"
                            :class="device === 'desktop' ? 'rounded-lg' : 'rounded-[22px]'"
                            :style="wrapStyle"
                        >
                            <iframe
                                ref="frame"
                                :key="frameKey"
                                src="/?preview=1"
                                title="Prévia da página inicial"
                                class="block border-0 bg-white"
                                :style="frameStyle"
                            />
                            <div v-if="!frameReady" class="absolute inset-0 flex items-center justify-center bg-white/80 dark:bg-zinc-900/80">
                                <Loader2 class="h-6 w-6 animate-spin text-zinc-400" />
                            </div>
                        </div>
                    </div>
                    <div class="pointer-events-none absolute bottom-3 left-1/2 -translate-x-1/2 rounded-full bg-zinc-900/80 px-3 py-1 text-[11px] font-medium text-white backdrop-blur">
                        {{ DEVICES[device].label }} · {{ deviceW }}px · {{ Math.round(scale * 100) }}%
                    </div>
                </main>
            </div>

            <transition
                enter-active-class="transition duration-200"
                enter-from-class="translate-y-2 opacity-0"
                leave-active-class="transition duration-200"
                leave-to-class="translate-y-2 opacity-0"
            >
                <div v-if="toast" class="fixed bottom-6 left-1/2 z-[80] -translate-x-1/2 rounded-lg bg-zinc-900 px-4 py-2.5 text-[13px] font-medium text-white shadow-xl dark:bg-white dark:text-zinc-900">
                    {{ toast }}
                </div>
            </transition>
        </div>
    </LayoutPlatform>
</template>
