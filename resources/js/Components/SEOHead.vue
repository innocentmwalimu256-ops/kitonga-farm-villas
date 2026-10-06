<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: 'Experience luxury private villas nestled within a 150-acre organic agricultural sanctuary in Komkonga, Handeni, Tanga, Tanzania. Book direct for exclusive rates.',
    },
    keywords: {
        type: String,
        default: 'Kitonga Farm Villas, luxury villas Tanga, Handeni resort, agro-tourism Tanzania, organic farm stay, private pool villa Tanzania, eco lodge East Africa, Handeni accommodation',
    },
    canonicalUrl: {
        type: String,
        default: '',
    },
    ogType: {
        type: String,
        default: 'website',
    },
    ogImage: {
        type: String,
        default: '/images/hero_villa_render.webp',
    },
    ogImageAlt: {
        type: String,
        default: 'Kitonga Farm Villas & Agro-Tourism Sanctuary',
    },
    schema: {
        type: [Object, Array],
        default: null,
    },
    noindex: {
        type: Boolean,
        default: false,
    },
});

const defaultSiteName = 'Kitonga Farm Villas';
const siteOrigin = 'https://kitongafarm.com';

const fullTitle = computed(() => {
    const raw = props.title.trim();
    if (raw.includes(defaultSiteName)) return raw;
    return `${raw} | ${defaultSiteName}`;
});

const cleanDescription = computed(() => {
    return props.description.replace(/\s+/g, ' ').trim();
});

const cleanCanonical = computed(() => {
    if (props.canonicalUrl) {
        if (props.canonicalUrl.startsWith('http')) return props.canonicalUrl;
        return `${siteOrigin}${props.canonicalUrl.startsWith('/') ? '' : '/'}${props.canonicalUrl}`;
    }
    if (typeof window !== 'undefined') {
        const path = window.location.pathname;
        return `${siteOrigin}${path === '/' ? '' : path}`;
    }
    return siteOrigin;
});

const fullImage = computed(() => {
    const img = props.ogImage || '/images/hero_villa_render.webp';
    if (img.startsWith('http')) return img;
    return `${siteOrigin}${img.startsWith('/') ? '' : '/'}${img}`;
});

const schemaString = computed(() => {
    if (!props.schema) return null;
    return JSON.stringify(props.schema);
});
</script>

<template>
    <Head>
        <!-- Primary Meta -->
        <title>{{ fullTitle }}</title>
        <meta name="description" :content="cleanDescription" />
        <meta v-if="keywords" name="keywords" :content="keywords" />
        <meta name="robots" :content="noindex ? 'noindex, nofollow' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1'" />
        <link rel="canonical" :href="cleanCanonical" />

        <!-- Open Graph / Facebook -->
        <meta property="og:site_name" content="Kitonga Farm Villas Sanctuary" />
        <meta property="og:locale" content="en_US" />
        <meta property="og:type" :content="ogType" />
        <meta property="og:title" :content="fullTitle" />
        <meta property="og:description" :content="cleanDescription" />
        <meta property="og:url" :content="cleanCanonical" />
        <meta property="og:image" :content="fullImage" />
        <meta property="og:image:alt" :content="ogImageAlt || fullTitle" />
        <meta property="og:image:width" content="1200" />
        <meta property="og:image:height" content="630" />

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="fullTitle" />
        <meta name="twitter:description" :content="cleanDescription" />
        <meta name="twitter:image" :content="fullImage" />

        <!-- JSON-LD Structured Data Schema -->
        <component is="script" v-if="schemaString" type="application/ld+json">
            {{ schemaString }}
        </component>
    </Head>
</template>
