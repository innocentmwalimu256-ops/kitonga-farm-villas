<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    media: Object,
    villas_list: {
        type: Array,
        default: () => [],
    },
    experiences_list: {
        type: Array,
        default: () => [],
    },
    filters: Object,
    counts: Object,
});

// Filters
const activePageFilter = ref(props.filters?.page_filter || 'all');
const activeTypeFilter = ref(props.filters?.type_filter || 'all');
const searchQuery = ref(props.filters?.search || '');

const applyFilters = () => {
    router.get(route('admin.media.index'), {
        page_filter: activePageFilter.value,
        type_filter: activeTypeFilter.value,
        search: searchQuery.value,
    }, { preserveState: true, preserveScroll: true });
};

const setPageFilter = (page) => {
    activePageFilter.value = page;
    applyFilters();
};

const setTypeFilter = (type) => {
    activeTypeFilter.value = type;
    applyFilters();
};

// Modals State
const showUploadModal = ref(false);
const showEditModal = ref(false);
const activePreviewItem = ref(null);
const activeEditItem = ref(null);
const copiedId = ref(null);

// File preview in Upload Modal
const previewUrl = ref(null);
const previewType = ref(null);

const uploadForm = useForm({
    file: null,
    title: '',
    page: 'gallery',
    section: 'gallery_showcase',
    category: 'general',
    target_villa: '',
    target_experience: '',
    target_role: 'featured_cover',
    is_hero: false,
    alt_text: '',
    caption: '',
});

const editForm = useForm({
    title: '',
    page: 'gallery',
    section: 'gallery_showcase',
    category: 'general',
    target_villa: '',
    target_experience: '',
    target_role: 'featured_cover',
    is_hero: false,
    alt_text: '',
    caption: '',
});

const handleFileSelect = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    
    uploadForm.file = file;
    if (!uploadForm.title) {
        uploadForm.title = file.name.replace(/\.[^/.]+$/, "");
    }
    
    if (file.type.startsWith('video/')) {
        previewType.value = 'video';
        previewUrl.value = URL.createObjectURL(file);
    } else if (file.type.startsWith('image/')) {
        previewType.value = 'image';
        previewUrl.value = URL.createObjectURL(file);
    } else {
        previewType.value = null;
        previewUrl.value = null;
    }
};

const openUploadModal = (defaultPage = 'gallery') => {
    uploadForm.reset();
    uploadForm.clearErrors();
    uploadForm.page = defaultPage !== 'all' ? defaultPage : 'gallery';
    if (uploadForm.page === 'villas' && props.villas_list?.length > 0) {
        uploadForm.target_villa = props.villas_list[0].id;
        uploadForm.target_role = 'featured_cover';
    } else if (uploadForm.page === 'experiences' && props.experiences_list?.length > 0) {
        uploadForm.target_experience = props.experiences_list[0].id;
        uploadForm.target_role = 'featured_cover';
    }
    previewUrl.value = null;
    previewType.value = null;
    showUploadModal.value = true;
};

const onPageChange = (targetPage) => {
    if (targetPage === 'villas' && props.villas_list?.length > 0) {
        uploadForm.target_villa = props.villas_list[0].id;
        uploadForm.target_role = 'featured_cover';
    } else if (targetPage === 'experiences' && props.experiences_list?.length > 0) {
        uploadForm.target_experience = props.experiences_list[0].id;
        uploadForm.target_role = 'featured_cover';
    } else if (targetPage === 'home') {
        uploadForm.section = 'hero_video';
        uploadForm.is_hero = true;
    }
};

const closeUploadModal = () => {
    showUploadModal.value = false;
    uploadForm.reset();
    uploadForm.clearErrors();
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }
};

const submitUpload = () => {
    uploadForm.clearErrors();
    uploadForm.post(route('admin.media.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            closeUploadModal();
        },
    });
};

const openEditModal = (item) => {
    activeEditItem.value = item;
    editForm.title = item.title || item.name;
    editForm.page = item.page || 'general';
    editForm.section = item.section || 'general';
    editForm.category = item.category || 'general';
    editForm.target_villa = item.target_villa || '';
    editForm.target_experience = item.target_experience || '';
    editForm.target_role = item.target_role || 'general';
    editForm.is_hero = Boolean(item.is_hero);
    editForm.alt_text = item.alt_text || '';
    editForm.caption = item.caption || '';
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    activeEditItem.value = null;
    editForm.reset();
};

const submitEdit = () => {
    if (!activeEditItem.value) return;
    editForm.post(route('admin.media.update', activeEditItem.value.id), {
        onSuccess: () => {
            closeEditModal();
        },
    });
};

const setAsHero = (item) => {
    router.post(route('admin.media.update', item.id), {
        title: item.title || item.name,
        page: 'home',
        section: item.media_type === 'video' ? 'hero_video' : 'hero_banner',
        category: item.category || 'general',
        is_hero: true,
        alt_text: item.alt_text || '',
        caption: item.caption || '',
    }, {
        preserveScroll: true,
    });
};

const deleteMedia = (id) => {
    if (confirm('Are you sure you want to permanently delete this media file?')) {
        router.delete(route('admin.media.destroy', id), {
            preserveScroll: true,
        });
    }
};

const copyUrl = (item) => {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(item.path);
        copiedId.value = item.id;
        setTimeout(() => {
            copiedId.value = null;
        }, 2500);
    }
};

const openPreview = (item) => {
    activePreviewItem.value = item;
};

const closePreview = () => {
    activePreviewItem.value = null;
};

const formatSize = (bytes) => {
    if (!bytes || bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
};

const pageTabs = [
    { id: 'all', label: 'All Media' },
    { id: 'home', label: 'Home Page' },
    { id: 'villas', label: 'Villas & Suites' },
    { id: 'experiences', label: 'Experiences' },
    { id: 'gallery', label: 'Gallery' },
    { id: 'farm', label: 'Our Farm' },
    { id: 'about', label: 'About & Location' },
];
</script>

<template>
    <Head title="Media & Video Studio" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Media & Video Studio</span>
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Upload photos and videos that publish instantly to specific villas, experiences, or website pages.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button 
                        @click="openUploadModal(activePageFilter)" 
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm hover:shadow transition transform active:scale-95 cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Upload Photo or Video</span>
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-5">

                <!-- 1. PAGE TABS NAVIGATION -->
                <div class="bg-white p-2 rounded-xl border border-gray-200/80 shadow-xs flex items-center gap-1.5 overflow-x-auto scrollbar-none">
                    <button
                        v-for="tab in pageTabs"
                        :key="tab.id"
                        @click="setPageFilter(tab.id)"
                        class="px-3.5 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap cursor-pointer"
                        :class="activePageFilter === tab.id 
                            ? 'bg-[#14231C] text-white shadow-xs' 
                            : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                    >
                        <span>{{ tab.label }}</span>
                    </button>
                </div>

                <!-- 2. SEARCH & TYPE FILTERS -->
                <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-xs flex flex-col md:flex-row gap-3 items-center justify-between">
                    <!-- Search Input -->
                    <div class="relative w-full md:w-80">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input 
                            v-model="searchQuery" 
                            @input="applyFilters"
                            type="text" 
                            placeholder="Search by title, caption, villa, file..." 
                            class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500"
                        />
                    </div>

                    <!-- Type Filter Buttons (All, Photos, Videos) -->
                    <div class="flex items-center gap-1 w-full md:w-auto">
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mr-2 hidden sm:inline">Type:</span>
                        <button 
                            @click="setTypeFilter('all')"
                            class="flex-1 md:flex-none px-3 py-1.5 rounded-md text-xs font-bold transition cursor-pointer"
                            :class="activeTypeFilter === 'all' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        >
                            All ({{ counts?.total_all || media.total }})
                        </button>
                        <button 
                            @click="setTypeFilter('image')"
                            class="flex-1 md:flex-none px-3 py-1.5 rounded-md text-xs font-bold transition cursor-pointer flex items-center gap-1.5"
                            :class="activeTypeFilter === 'image' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>Photos</span>
                        </button>
                        <button 
                            @click="setTypeFilter('video')"
                            class="flex-1 md:flex-none px-3 py-1.5 rounded-md text-xs font-bold transition cursor-pointer flex items-center justify-center gap-1.5"
                            :class="activeTypeFilter === 'video' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            <span>Videos</span>
                            <span v-if="counts?.total_videos" class="text-[10px] bg-emerald-500 text-white px-1.5 py-0.2 rounded-full font-mono font-bold">{{ counts.total_videos }}</span>
                        </button>
                    </div>
                </div>

                <!-- 3. MEDIA ASSETS GRID -->
                <div class="bg-white p-5 rounded-xl shadow-xs border border-gray-200/80 space-y-4">
                    
                    <div v-if="media.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <div 
                            v-for="item in media.data" 
                            :key="item.id" 
                            class="group relative bg-gray-50 border border-gray-200 rounded-xl overflow-hidden hover:shadow-md hover:border-emerald-500/50 transition duration-200 flex flex-col justify-between"
                        >
                            <!-- Thumbnail / Video Box -->
                            <div 
                                @click="openPreview(item)"
                                class="relative aspect-video w-full bg-gray-900 overflow-hidden cursor-pointer flex items-center justify-center select-none"
                            >
                                <!-- Image Tag -->
                                <img 
                                    v-if="item.media_type === 'image'"
                                    :src="item.path" 
                                    :alt="item.alt_text || item.title" 
                                    class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                    loading="lazy"
                                />

                                <!-- Video Tag -->
                                <div v-else class="relative w-full h-full flex items-center justify-center bg-black">
                                    <video 
                                        :src="item.path" 
                                        class="w-full h-full object-cover opacity-90 group-hover:opacity-100 group-hover:scale-105 transition duration-300"
                                        preload="metadata"
                                        muted
                                        loop
                                        onmouseover="this.play()"
                                        onmouseout="this.pause()"
                                    ></video>
                                    
                                    <!-- Center Video Play Icon -->
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/30 group-hover:bg-transparent transition pointer-events-none">
                                        <div class="w-10 h-10 rounded-full bg-emerald-600/90 text-white flex items-center justify-center shadow-lg group-hover:scale-110 transition">
                                            <svg class="w-5 h-5 ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8 5v14l11-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Badges Overlay (Top) -->
                                <div class="absolute top-2 left-2 right-2 flex items-center justify-between gap-1 pointer-events-none">
                                    <!-- Page Badge -->
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-black/70 text-white backdrop-blur-xs border border-white/10 shadow-xs">
                                        {{ item.page }}
                                    </span>

                                    <!-- Media Type Badge -->
                                    <span 
                                        class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider backdrop-blur-xs shadow-xs flex items-center gap-1"
                                        :class="item.media_type === 'video' ? 'bg-amber-500 text-white' : 'bg-emerald-600 text-white'"
                                    >
                                        {{ item.media_type === 'video' ? 'Video' : 'Photo' }}
                                    </span>
                                </div>

                                <!-- Hero Badge (if applicable) -->
                                <div v-if="item.is_hero" class="absolute bottom-2 left-2 pointer-events-none">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wider bg-amber-400 text-amber-950 shadow-md flex items-center gap-1">
                                        Featured Hero
                                    </span>
                                </div>
                            </div>

                            <!-- Card Info & Details -->
                            <div class="p-3 space-y-1.5 flex-1 flex flex-col justify-between">
                                <div>
                                    <h4 class="text-xs font-bold text-gray-900 truncate" :title="item.title || item.name">
                                        {{ item.title || item.name }}
                                    </h4>
                                    <p v-if="item.caption" class="text-[11px] text-gray-500 line-clamp-2 mt-0.5" :title="item.caption">
                                        {{ item.caption }}
                                    </p>
                                    <div class="flex items-center gap-2 text-[10px] text-gray-400 font-mono mt-1">
                                        <span>{{ formatSize(item.file_size) }}</span>
                                        <span>•</span>
                                        <span>{{ item.created_at_human || 'Recent' }}</span>
                                    </div>
                                </div>

                                <!-- Action Buttons Bar -->
                                <div class="pt-2 border-t border-gray-100 flex items-center justify-between gap-1.5">
                                    <!-- Set as Hero Quick Button -->
                                    <button 
                                        v-if="!item.is_hero"
                                        @click.stop="setAsHero(item)"
                                        type="button"
                                        class="px-2 py-1 rounded-md bg-amber-50 hover:bg-amber-100 text-amber-800 text-[10px] font-bold transition flex items-center gap-1 cursor-pointer border border-amber-200"
                                        :title="item.media_type === 'video' ? 'Set as Home Hero Video' : 'Set as Home Hero Banner'"
                                    >
                                        <span>Set Hero</span>
                                    </button>
                                    <span v-else class="px-2 py-0.5 rounded-md bg-amber-400 text-amber-950 text-[10px] font-extrabold flex items-center gap-0.5 shadow-xs">
                                        Active Hero
                                    </span>

                                    <!-- Copy Link Button -->
                                    <button 
                                        @click.stop="copyUrl(item)" 
                                        type="button"
                                        class="flex-1 py-1 px-2 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 text-[11px] font-bold transition flex items-center justify-center gap-1 cursor-pointer"
                                        :title="item.path"
                                    >
                                        <svg v-if="copiedId !== item.id" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                        </svg>
                                        <svg v-else class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>{{ copiedId === item.id ? 'Copied' : 'Copy' }}</span>
                                    </button>

                                    <!-- Edit Button -->
                                    <button 
                                        @click.stop="openEditModal(item)" 
                                        type="button"
                                        class="p-1.5 rounded-md bg-gray-100 hover:bg-emerald-50 hover:text-emerald-700 text-gray-600 transition cursor-pointer"
                                        title="Edit details"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>

                                    <!-- Delete Button -->
                                    <button 
                                        @click.stop="deleteMedia(item.id)" 
                                        type="button"
                                        class="p-1.5 rounded-md bg-gray-100 hover:bg-red-50 hover:text-red-700 text-gray-600 transition cursor-pointer"
                                        title="Delete file"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="py-16 text-center space-y-3">
                        <div class="w-16 h-16 mx-auto rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-800">No media assets found</h3>
                        <p class="text-xs text-gray-500 max-w-sm mx-auto">
                            No photos or videos uploaded for this section yet. Upload your first high-definition photo or video to display on the website!
                        </p>
                        <button 
                            @click="openUploadModal(activePageFilter)" 
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition cursor-pointer"
                        >
                            Upload Now
                        </button>
                    </div>

                    <!-- PAGINATION -->
                    <div v-if="media.links && media.links.length > 3" class="pt-4 border-t border-gray-100 flex justify-center space-x-1">
                        <Link 
                            v-for="(link, k) in media.links" 
                            :key="k" 
                            :href="link.url || '#'" 
                            class="px-3 py-1.5 rounded-lg text-xs transition" 
                            :class="{
                                'bg-[#14231C] text-white font-bold shadow-xs': link.active,
                                'bg-gray-100 hover:bg-gray-200 text-gray-700': !link.active && link.url,
                                'text-gray-300 cursor-not-allowed': !link.url
                            }"
                            v-html="link.label"
                        />
                    </div>
                </div>

            </div>
        </div>

        <!-- 4. UPLOAD MODAL (PHOTOS & VIDEOS) -->
        <div v-if="showUploadModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200 my-8">
                <!-- Modal Header -->
                <div class="p-5 bg-[#14231C] text-white flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <span>Upload Media Asset & Instant Publish</span>
                        </h3>
                        <p class="text-xs text-gray-300 mt-0.5">Supports high-res Photos (JPG, PNG, WEBP) and Videos (MP4, WEBM, MOV) with instant auto web compression.</p>
                    </div>
                    <button @click="closeUploadModal" class="text-gray-400 hover:text-white transition p-1 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Form Content -->
                <form @submit.prevent="submitUpload" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                    
                    <!-- Validation Errors Alert -->
                    <div v-if="Object.keys(uploadForm.errors).length > 0" class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700 space-y-1">
                        <div class="font-bold flex items-center gap-1.5">
                            <span>Notice:</span> Upload Warning / Error:
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 pl-1">
                            <li v-for="(err, key) in uploadForm.errors" :key="key">{{ err }}</li>
                        </ul>
                    </div>

                    <!-- File Selector / Drop Area -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-700 uppercase tracking-wider block">
                            Select Photo or Video File <span class="text-red-500">*</span>
                        </label>
                        <div class="border-2 border-dashed border-gray-300 hover:border-emerald-500 rounded-xl p-4 text-center transition bg-gray-50 relative group">
                            <input 
                                type="file" 
                                required 
                                @change="handleFileSelect" 
                                accept="image/*,video/*" 
                                class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10" 
                            />
                            
                            <!-- Live Preview if selected -->
                            <div v-if="previewUrl" class="space-y-2">
                                <div v-if="previewType === 'image'" class="max-h-48 mx-auto overflow-hidden rounded-lg">
                                    <img :src="previewUrl" class="max-h-48 mx-auto object-contain rounded-lg shadow-xs" />
                                </div>
                                <div v-else-if="previewType === 'video'" class="max-h-48 mx-auto overflow-hidden rounded-lg bg-black">
                                    <video :src="previewUrl" controls class="max-h-48 mx-auto rounded-lg shadow-xs"></video>
                                </div>
                                <p class="text-xs font-bold text-emerald-700">{{ uploadForm.file?.name }} ({{ formatSize(uploadForm.file?.size) }})</p>
                                <span class="text-[11px] text-gray-500 underline">Click to choose a different file</span>
                            </div>

                            <div v-else class="space-y-2 py-4">
                                <div class="w-12 h-12 mx-auto rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                </div>
                                <div class="text-xs text-gray-600">
                                    <span class="font-bold text-emerald-600">Click to upload</span> or drag and drop photo/video here
                                </div>
                                <p class="text-[10px] text-gray-400">JPG, PNG, WEBP, GIF, MP4, WEBM, MOV (Max 512MB)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Target Page Selector -->
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-gray-700 block uppercase tracking-wider">Target Page / Destination</label>
                        <select 
                            v-model="uploadForm.page" 
                            @change="onPageChange(uploadForm.page)" 
                            class="w-full text-xs rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 font-medium"
                        >
                            <option value="villas">Villas & Luxury Suites (Luxury Villa, Semi-Luxury, Family Villa)</option>
                            <option value="experiences">Experiences & Farm Tours (Coffee Tour, Dairy Walk, Gastronomy)</option>
                            <option value="home">Home Page (Hero Video, Story, Highlights)</option>
                            <option value="gallery">Visual Gallery (Showcase)</option>
                            <option value="farm">Our Farm (Dairy, Orchard, Apiary, Livestock)</option>
                            <option value="about">About & Location</option>
                            <option value="products">Farm Produce / Shop</option>
                            <option value="general">General / All Pages</option>
                        </select>
                    </div>

                    <!-- 1. DYNAMIC SUB-OPTIONS: WHEN "VILLAS" IS SELECTED -->
                    <div v-if="uploadForm.page === 'villas'" class="p-4 bg-emerald-50/60 rounded-xl border border-emerald-200/80 space-y-3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs font-bold text-emerald-950 block">Select Target Villa</label>
                                <select v-model="uploadForm.target_villa" class="w-full text-xs rounded-lg border-emerald-300 mt-1 focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="">All Villas / Header Banner</option>
                                    <option v-for="v in villas_list" :key="v.id" :value="v.id">
                                        {{ v.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-emerald-950 block">Media Role / Placement</label>
                                <select v-model="uploadForm.target_role" class="w-full text-xs rounded-lg border-emerald-300 mt-1 focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="featured_cover">Main Villa Cover Photo (Directly updates card on Home & Villas page)</option>
                                    <option value="gallery_item">Villa Gallery Image (Shows inside Villa Detail page)</option>
                                    <option value="hero_banner">Villas Page Hero Header Visual</option>
                                </select>
                            </div>
                        </div>
                        <p class="text-[11px] text-emerald-800">
                            Selecting <strong>Main Villa Cover Photo</strong> will automatically update the cover photo of that villa across the entire website immediately upon publishing.
                        </p>
                    </div>

                    <!-- 2. DYNAMIC SUB-OPTIONS: WHEN "EXPERIENCES" IS SELECTED -->
                    <div v-if="uploadForm.page === 'experiences'" class="p-4 bg-emerald-50/60 rounded-xl border border-emerald-200/80 space-y-3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs font-bold text-emerald-950 block">Select Specific Experience / Tour</label>
                                <select v-model="uploadForm.target_experience" class="w-full text-xs rounded-lg border-emerald-300 mt-1 focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="">All Experiences / Header Banner</option>
                                    <option v-for="exp in experiences_list" :key="exp.id" :value="exp.id">
                                        {{ exp.name || exp.title }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-emerald-950 block">Role</label>
                                <select v-model="uploadForm.target_role" class="w-full text-xs rounded-lg border-emerald-300 mt-1 focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="featured_cover">Main Experience Cover Photo</option>
                                    <option value="gallery_item">Experience Photo Gallery</option>
                                    <option value="hero_banner">Experiences Page Header Background</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- 3. DYNAMIC SUB-OPTIONS: WHEN "HOME PAGE" IS SELECTED -->
                    <div v-if="uploadForm.page === 'home'" class="p-4 bg-amber-50/60 rounded-xl border border-amber-200/80 space-y-3">
                        <div>
                            <label class="text-xs font-bold text-amber-950 block">Home Page Section</label>
                            <select v-model="uploadForm.section" class="w-full text-xs rounded-lg border-amber-300 mt-1 focus:ring-amber-500 focus:border-amber-500">
                                <option value="hero_video">Hero Video (Top Autoplay 4K Background)</option>
                                <option value="hero_banner">Hero Banner Poster</option>
                                <option value="story_section">Brand Story & Philosophy</option>
                                <option value="farm_highlights">Farm Highlights Spotlight</option>
                            </select>
                        </div>
                    </div>

                    <!-- 4. DYNAMIC SUB-OPTIONS: WHEN "GALLERY" IS SELECTED -->
                    <div v-if="uploadForm.page === 'gallery'" class="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-3">
                        <div>
                            <label class="text-xs font-bold text-gray-800 block">Gallery Filter Category</label>
                            <select v-model="uploadForm.category" class="w-full text-xs rounded-lg border-gray-300 mt-1 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="general">All Perspectives / General</option>
                                <option value="villas">Luxury Villas & Suites</option>
                                <option value="farm">Shamba & Dairy Livestock</option>
                                <option value="experiences">Farm Tours & Experiences</option>
                                <option value="food">Produce & Dining</option>
                                <option value="nature">Nature & Scenic Views</option>
                                <option value="videos">Cinematic Videos</option>
                            </select>
                        </div>
                    </div>

                    <!-- 5. DYNAMIC SUB-OPTIONS: WHEN "OUR FARM" IS SELECTED -->
                    <div v-if="uploadForm.page === 'farm'" class="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-3">
                        <div>
                            <label class="text-xs font-bold text-gray-800 block">Farm Zone</label>
                            <select v-model="uploadForm.section" class="w-full text-xs rounded-lg border-gray-300 mt-1 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="farm_hero">Farm Header Banner</option>
                                <option value="dairy_pasture">Dairy Cattle & Pasture</option>
                                <option value="coffee_orchard">Coffee & Fruit Trees</option>
                                <option value="greenhouse">Greenhouse & Organic Crops</option>
                                <option value="poultry_apiary">Poultry & Apiary</option>
                            </select>
                        </div>
                    </div>

                    <!-- Title & Caption -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold text-gray-700 block">Title / Caption Label</label>
                            <input 
                                v-model="uploadForm.title" 
                                type="text" 
                                placeholder="e.g. Master Bedroom Suite Sunset" 
                                class="w-full text-xs rounded-lg border-gray-300 mt-1 focus:ring-emerald-500 focus:border-emerald-500" 
                            />
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-700 block">Description (Optional)</label>
                            <input 
                                v-model="uploadForm.caption" 
                                type="text" 
                                placeholder="Short description..." 
                                class="w-full text-xs rounded-lg border-gray-300 mt-1 focus:ring-emerald-500 focus:border-emerald-500" 
                            />
                        </div>
                    </div>

                    <!-- Set as Hero Toggle -->
                    <div class="flex items-center gap-3 p-3 bg-amber-50 rounded-xl border border-amber-200">
                        <input 
                            type="checkbox" 
                            id="is_hero" 
                            v-model="uploadForm.is_hero" 
                            class="rounded border-amber-300 text-amber-600 focus:ring-amber-500 h-4 w-4"
                        />
                        <label for="is_hero" class="text-xs text-amber-900 font-semibold cursor-pointer">
                            Mark as Priority / Hero Media (Top priority display on selected page)
                        </label>
                    </div>

                    <!-- Upload Progress Bar -->
                    <div v-if="uploadForm.progress" class="space-y-1">
                        <div class="flex justify-between text-xs font-bold text-emerald-700">
                            <span>Uploading and optimizing for web speed...</span>
                            <span>{{ uploadForm.progress.percentage }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                            <div class="bg-emerald-600 h-2 transition-all duration-150" :style="{ width: uploadForm.progress.percentage + '%' }"></div>
                        </div>
                    </div>

                    <!-- Submit & Cancel Buttons -->
                    <div class="flex items-center gap-3 pt-3 border-t border-gray-100">
                        <button 
                            type="submit" 
                            :disabled="uploadForm.processing || !uploadForm.file" 
                            class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-sm disabled:opacity-50 cursor-pointer flex items-center justify-center gap-2"
                        >
                            <svg v-if="uploadForm.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ uploadForm.processing ? 'Optimizing & Publishing...' : 'Publish Media Asset' }}</span>
                        </button>
                        <button 
                            type="button" 
                            @click="closeUploadModal" 
                            class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition cursor-pointer"
                        >
                            Cancel
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <!-- 5. EDIT METADATA MODAL -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="p-5 bg-[#14231C] text-white flex items-center justify-between">
                    <h3 class="text-sm font-bold flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Edit Media Details</span>
                    </h3>
                    <button @click="closeEditModal" class="text-gray-400 hover:text-white transition p-1 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitEdit" class="p-6 space-y-4">
                    <div>
                        <label class="text-xs font-bold text-gray-700 block">Title / Heading</label>
                        <input v-model="editForm.title" type="text" class="w-full text-xs rounded-lg border-gray-300 mt-1" required />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-bold text-gray-700 block">Target Page</label>
                            <select v-model="editForm.page" class="w-full text-xs rounded-lg border-gray-300 mt-1">
                                <option value="villas">Villas</option>
                                <option value="experiences">Experiences</option>
                                <option value="home">Home Page</option>
                                <option value="gallery">Gallery</option>
                                <option value="farm">Our Farm</option>
                                <option value="about">About & Location</option>
                                <option value="general">General</option>
                            </select>
                        </div>
                        <div v-if="editForm.page === 'villas'">
                            <label class="text-xs font-bold text-gray-700 block">Target Villa</label>
                            <select v-model="editForm.target_villa" class="w-full text-xs rounded-lg border-gray-300 mt-1">
                                <option value="">None / General</option>
                                <option v-for="v in villas_list" :key="v.id" :value="v.id">{{ v.name }}</option>
                            </select>
                        </div>
                        <div v-else-if="editForm.page === 'experiences'">
                            <label class="text-xs font-bold text-gray-700 block">Target Experience</label>
                            <select v-model="editForm.target_experience" class="w-full text-xs rounded-lg border-gray-300 mt-1">
                                <option value="">None / General</option>
                                <option v-for="exp in experiences_list" :key="exp.id" :value="exp.id">{{ exp.name || exp.title }}</option>
                            </select>
                        </div>
                        <div v-else>
                            <label class="text-xs font-bold text-gray-700 block">Category</label>
                            <select v-model="editForm.category" class="w-full text-xs rounded-lg border-gray-300 mt-1">
                                <option value="general">General</option>
                                <option value="villas">Villas</option>
                                <option value="farm">Farm & Shamba</option>
                                <option value="experiences">Experiences</option>
                                <option value="food">Dining & Produce</option>
                                <option value="nature">Nature</option>
                                <option value="videos">Videos</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-gray-700 block">Caption</label>
                        <textarea v-model="editForm.caption" rows="2" class="w-full text-xs rounded-lg border-gray-300 mt-1"></textarea>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="edit_is_hero" v-model="editForm.is_hero" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500" />
                        <label for="edit_is_hero" class="text-xs text-gray-700 font-semibold cursor-pointer">
                            Mark as Priority / Hero
                        </label>
                    </div>

                    <div class="flex items-center gap-3 pt-3 border-t border-gray-100">
                        <button 
                            type="submit" 
                            :disabled="editForm.processing" 
                            class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-sm cursor-pointer"
                        >
                            {{ editForm.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                        <button type="button" @click="closeEditModal" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition cursor-pointer">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 6. HIGH-RES LIGHTBOX / FULLSCREEN PREVIEW -->
        <div 
            v-if="activePreviewItem" 
            @click="closePreview" 
            class="fixed inset-0 z-50 bg-black/90 backdrop-blur-md flex items-center justify-center p-4 cursor-pointer"
        >
            <div class="relative max-w-5xl w-full max-h-[90vh] flex flex-col items-center" @click.stop>
                <!-- Close Button -->
                <button 
                    @click="closePreview" 
                    class="absolute -top-10 right-0 text-white/80 hover:text-white p-2 rounded-full bg-white/10 hover:bg-white/20 transition cursor-pointer"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <!-- Media Display -->
                <div class="w-full flex items-center justify-center overflow-hidden rounded-2xl bg-black/50 shadow-2xl">
                    <img 
                        v-if="activePreviewItem.media_type === 'image'" 
                        :src="activePreviewItem.path" 
                        :alt="activePreviewItem.title" 
                        class="max-h-[75vh] w-auto object-contain rounded-xl"
                    />
                    <video 
                        v-else 
                        :src="activePreviewItem.path" 
                        controls 
                        autoplay 
                        class="max-h-[75vh] w-full max-w-4xl rounded-xl"
                    ></video>
                </div>

                <!-- Media Details Bottom Bar -->
                <div class="w-full bg-[#14231C]/90 text-white p-4 rounded-xl mt-3 flex items-center justify-between border border-white/10">
                    <div>
                        <h4 class="text-sm font-bold">{{ activePreviewItem.title || activePreviewItem.filename }}</h4>
                        <p v-if="activePreviewItem.caption" class="text-xs text-gray-300 mt-0.5">{{ activePreviewItem.caption }}</p>
                    </div>
                    <button 
                        @click="copyUrl(activePreviewItem)" 
                        class="px-3 py-1.5 bg-[#C98A3E] hover:bg-[#b07835] text-white text-xs font-bold rounded-lg transition flex items-center gap-1.5 cursor-pointer"
                    >
                        <span>{{ copiedId === activePreviewItem.id ? 'Copied' : 'Copy Direct URL' }}</span>
                    </button>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>


