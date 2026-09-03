<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    media: Object,
});

const uploadForm = useForm({
    file: null,
    alt_text: '',
    caption: '',
});

const showUploadForm = ref(false);

const handleFileChange = (e) => {
    uploadForm.file = e.target.files[0];
};

const submitUpload = () => {
    uploadForm.post(route('admin.media.store'), {
        onSuccess: () => {
            uploadForm.reset();
            showUploadForm.value = false;
            alert('Media file uploaded successfully.');
        }
    });
};

const deleteMedia = (id) => {
    if (confirm('Are you sure you want to delete this media asset permanently? This action cannot be undone.')) {
        uploadForm.delete(route('admin.media.destroy', id), {
            onSuccess: () => {
                alert('Media asset deleted.');
            }
        });
    }
};

const formatSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>

<template>
    <Head title="Media Library" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Media Library & Assets
                </h2>
                <button @click="showUploadForm = !showUploadForm" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded shadow-xs transition">
                    {{ showUploadForm ? 'Close Upload' : 'Upload New File' }}
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- UPLOAD FORM -->
                <div v-if="showUploadForm" class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm space-y-4">
                    <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider">Upload New Image</h3>
                    <form @submit.prevent="submitUpload" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Select File (Max 10MB Images)</label>
                            <input type="file" required @change="handleFileChange" accept="image/*" class="w-full text-xs mt-1" />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Alt Text (Accessibility)</label>
                            <input v-model="uploadForm.alt_text" type="text" placeholder="e.g. Luxury villa interior view" class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Caption / Description</label>
                            <input v-model="uploadForm.caption" type="text" placeholder="Optional caption to display under the image" class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div class="md:col-span-2">
                            <button type="submit" :disabled="uploadForm.processing" class="w-full py-2 bg-emerald-600 text-white font-bold rounded text-xs hover:bg-emerald-700 transition disabled:bg-gray-300">
                                {{ uploadForm.processing ? 'Uploading file...' : 'Start Upload' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- MEDIA GRID -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 space-y-4">
                    <h3 class="font-bold text-gray-800 text-xs uppercase tracking-wider">All Uploaded Assets</h3>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        <div v-for="item in media.data" :key="item.id" class="border rounded-lg overflow-hidden group relative bg-gray-50 flex flex-col justify-between">
                            <div class="aspect-square w-full overflow-hidden bg-gray-100 flex items-center justify-center">
                                <img loading="lazy" decoding="async" :src="item.path" :alt="item.alt_text" class="w-full h-full object-cover group-hover:scale-105 transition duration-200" />
                            </div>
                            <div class="p-2 space-y-1">
                                <div class="text-[10px] font-bold text-gray-900 truncate" :title="item.filename">{{ item.filename }}</div>
                                <div class="text-[9px] text-gray-400 font-mono">{{ formatSize(item.file_size) }}</div>
                            </div>
                            <!-- OVERLAY DELETE -->
                            <div class="absolute inset-0 bg-gray-900/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center space-x-2">
                                <button @click="deleteMedia(item.id)" class="p-2 bg-red-600 hover:bg-red-700 text-white rounded shadow transition text-xs font-bold">
                                    Delete
                                </button>
                            </div>
                        </div>

                        <div v-if="media.data.length === 0" class="col-span-full py-12 text-center text-gray-400 italic">
                            No media assets uploaded yet.
                        </div>
                    </div>

                    <!-- PAGINATION -->
                    <div v-if="media.links.length > 3" class="p-4 border-t flex justify-center space-x-1">
                        <Link v-for="(link, k) in media.links" :key="k" :href="link.url || '#'" 
                            class="px-3 py-1 rounded text-xs transition" 
                            :class="{
                                'bg-emerald-600 text-white font-bold': link.active,
                                'bg-gray-100 hover:bg-gray-200 text-gray-600': !link.active && link.url,
                                'text-gray-300 cursor-not-allowed': !link.url
                            }"
                            v-html="link.label">
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

