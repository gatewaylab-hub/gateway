const THEMES = {
    'league-of-legends': { bg: '#0B2540', accent: '#C9A45C', mark: 'LoL', label: 'League of Legends' },
    roblox: { bg: '#1B1D22', accent: '#E8E8E8', mark: 'RBX', label: 'Roblox' },
    steam: { bg: '#15202E', accent: '#66C0F4', mark: 'STM', label: 'Steam' },
    'free-fire': { bg: '#E4570F', accent: '#FFD24A', mark: 'FF', label: 'Free Fire' },
    valorant: { bg: '#FF4655', accent: '#0F1923', mark: 'VAL', label: 'Valorant' },
    minecraft: { bg: '#35702A', accent: '#A3E36B', mark: 'MC', label: 'Minecraft' },
    'assinaturas-e-premium': { bg: '#3C1D7A', accent: '#E9B8FF', mark: 'PRO', label: 'Premium' },
    outros: { bg: '#2B2420', accent: '#FF5A1F', mark: '+', label: 'Outros' },
};

const FALLBACK_BGS = ['#1F3A5F', '#5B2A86', '#8A3B12', '#0F5E4C', '#6B1D2F', '#2F3E46'];

function hash(str) {
    let h = 0;
    for (let i = 0; i < str.length; i++) h = (h * 31 + str.charCodeAt(i)) | 0;
    return Math.abs(h);
}

export function categoryTheme(category) {
    if (!category) return THEMES.outros;
    const known = THEMES[category.slug];
    if (known) return known;
    const name = category.name || 'Outros';
    return {
        bg: FALLBACK_BGS[hash(name) % FALLBACK_BGS.length],
        accent: '#FFFFFF',
        mark: name.replace(/[^A-Za-z0-9]/g, '').slice(0, 3).toUpperCase() || '+',
        label: name,
    };
}

export const categoryOrder = [
    'league-of-legends',
    'roblox',
    'steam',
    'free-fire',
    'valorant',
    'minecraft',
    'assinaturas-e-premium',
    'outros',
];
