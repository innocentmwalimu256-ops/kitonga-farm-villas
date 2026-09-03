<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    experience: Object,
});

const form = useForm({
    _method: 'PUT', // support multipart file upload with PUT in laravel
    name: props.experience.name || '',
    slug: props.experience.slug || '',
    price: props.experience.price || 0,
    capacity_per_slot: props.experience.capacity_per_slot || 20,
    category: props.experience.category || '',
    duration: props.experience.duration || '',
    description: props.experience.description || '',
    good_to_know: props.experience.good_to_know || '',
    featured: !!props.experience.featured,
    sort_order: props.experience.sort_order || 0,
    status: props.experience.status || 'draft',
    seo_title: props.experience.seo_title || '',
    seo_description: props.experience.seo_description || '',
    featured_image: props.experience.featured_image || '',
    featured_image_file: null,
    gallery: props.experience.gallery || [],
    gallery_files: null,
    inclusions: props.experience.inclusions || [],
    highlights: props.experience.highlights || [],
});

const newInclusion = ref('');
const addInclusion = () => {
    if (newInclusion.value.trim()) {
        form.inclusions.push(newInclusion.value.trim());
        newInclusion.value = '';
    }
};
const removeInclusion = (index) => {
    form.inclusions.splice(index, 1);
};

const newHighlight = ref('');
const addHighlight = () => {
    if (newHighlight.value.trim()) {
        form.highlights.push(newHighlight.value.trim());
        newHighlight.value = '';
    }
};
const removeHighlight = (index) => {
    form.highlights.splice(index, 1);
};

const removeGalleryImage = (index) => {
    form.gallery.splice(index, 1);
};

const submitUpdateExperience = () => {
    form.post(route('admin.experiences.update', props.experience.id), {
        onSuccess: () => {
            alert('Experience updated successfully.');
        }
    });
};
</script>

<template>
    <Head :title="'Edit - ' + experience.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Edit Experience: {{ experience.name }}
                </h2>
                <Link :href="route('admin.experiences.index')" class="px-4 py-2 border border-gray-200 text-gray-700 text-xs font-bold rounded hover:bg-gray-50 transition">
                    â† Back to Listing
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <form @submit.prevent="submitUpdateExperience" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Left Columns: Core Fields -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Core Card -->
                        <div class="bg-white p-6 rounded-lg border border-gray-100 shadow-xs space-y-4">
                            <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider">Experience Definition</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="font-bold text-gray-400 uppercase text-[9px] block">Name</label>
                                    <input v-model="form.name" type="text" class="w-full text-xs rounded border-gray-300 mt-1" required />
                                </div>
                                <div>
                                    <label class="font-bold text-gray-400 uppercase text-[9px] block">Slug</label>
                                    <input v-model="form.slug" type="text" class="w-full text-xs rounded border-gray-300 mt-1" required />
                                </div>
                                <div>
                                    <label class="font-bold text-gray-400 uppercase text-[9px] block">Price (TZS)</label>
                                    <input v-model="form.price" type="number" class="w-full text-xs rounded border-gray-300 mt-1" required min="0" />
                                </div>
                                <div>
                                    <label class="font-bold text-gray-400 uppercase text-[9px] block">Max Guests Capacity Per Slot</label>
                                    <input v-model="form.capacity_per_slot" type="number" class="w-full text-xs rounded border-gray-300 mt-1" required min="1" />
                                </div>
                                <div>
                                    <label class="font-bold text-gray-400 uppercase text-[9px] block">Category</label>
                                    <input v-model="form.category" type="text" class="w-full text-xs rounded border-gray-300 mt-1" placeholder="e.g. Nature, Farm & Food" />
                                </div>
                                <div>
                                    <label class="font-bold text-gray-400 uppercase text-[9px] block">Duration Description</label>
                                    <input v-model="form.duration" type="text" class="w-full text-xs rounded border-gray-300 mt-1" placeholder="e.g. 2 Hours, Full Day" />
                                </div>
                            </div>

                            <div>
                                <label class="font-bold text-gray-400 uppercase text-[9px] block">Narrative Description</label>
                                <textarea v-model="form.description" rows="5" class="w-full text-xs rounded border-gray-300 mt-1" placeholder="Narrate the journey, feeling, and details of this tour..."></textarea>
                            </div>

                            <div>
                                <label class="font-bold text-gray-400 uppercase text-[9px] block">Good to Know (Policies / Attire / Health guidelines)</label>
                                <textarea v-model="form.good_to_know" rows="3" class="w-full text-xs rounded border-gray-300 mt-1" placeholder="What should guests wear? Any age restriction?"></textarea>
                            </div>
                        </div>

                        <!-- Inclusions and Highlights Card -->
                        <div class="bg-white p-6 rounded-lg border border-gray-100 shadow-xs space-y-6">
                            
                            <!-- Inclusions List -->
                            <div class="space-y-3">
                                <h3 class="font-bold text-gray-800 text-xs uppercase tracking-wider">Confirmed Inclusions</h3>
                                <div class="flex gap-2">
                                    <input v-model="newInclusion" @keydown.enter.prevent="addInclusion" type="text" class="flex-1 text-xs rounded border-gray-300" placeholder="e.g. Fresh coconut juice, swimming access" />
                                    <button @click.prevent="addInclusion" class="px-4 py-2 bg-gray-100 hover:bg-gray-250 text-gray-700 text-xs font-bold rounded">Add</button>
                                </div>
                                <ul class="space-y-1.5 mt-2">
                                    <li v-for="(inc, index) in form.inclusions" :key="index" class="flex justify-between items-center text-xs p-2 bg-gray-50 rounded border border-gray-100">
                                        <span class="text-gray-700">âœ“ {{ inc }}</span>
                                        <button @click.prevent="removeInclusion(index)" class="text-rose-600 hover:underline">Remove</button>
                                    </li>
                                </ul>
                            </div>

                            <!-- Highlights List -->
                            <div class="space-y-3">
                                <h3 class="font-bold text-gray-800 text-xs uppercase tracking-wider">Experience Highlights</h3>
                                <div class="flex gap-2">
                                    <input v-model="newHighlight" @keydown.enter.prevent="addHighlight" type="text" class="flex-1 text-xs rounded border-gray-300" placeholder="e.g. Walking crop fields, poultry barns feed" />
                                    <button @click.prevent="addHighlight" class="px-4 py-2 bg-gray-100 hover:bg-gray-250 text-gray-700 text-xs font-bold rounded">Add</button>
                                </div>
                                <ul class="space-y-1.5 mt-2">
                                    <li v-for="(hl, index) in form.highlights" :key="index" class="flex justify-between items-center text-xs p-2 bg-gray-50 rounded border border-gray-100">
                                        <span class="text-gray-700">â˜… {{ hl }}</span>
                                        <button @click.prevent="removeHighlight(index)" class="text-rose-600 hover:underline">Remove</button>
                                    </li>
                                </ul>
                            </div>

                        </div>

                        <!-- Media Library Card -->
                        <div class="bg-white p-6 rounded-lg border border-gray-100 shadow-xs space-y-4">
                            <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider">Media & Photos</h3>
                            
                            <div>
                                <label class="font-bold text-gray-400 uppercase text-[9px] block">Featured Image URL (Fallback)</label>
                                <input v-model="form.featured_image" type="text" class="w-full text-xs rounded border-gray-300 mt-1" />
                            </div>

                            <div>
                                <label class="font-bold text-gray-400 uppercase text-[9px] block">Upload New Featured Image File</label>
                                <input @input="form.featured_image_file = $event.target.files[0]" type="file" class="w-full text-xs mt-1" accept="image/*" />
                                <p class="text-[10px] text-gray-400 mt-1">Uploading a file will overwrite the URL fallback.</p>
                            </div>

                            <div class="border-t pt-4">
                                <label class="font-bold text-gray-400 uppercase text-[9px] block">Upload New Gallery Files</label>
                                <input @input="form.gallery_files = $event.target.files" type="file" class="w-full text-xs mt-1" multiple accept="image/*" />
                            </div>

                            <div v-if="form.gallery.length > 0" class="space-y-2 mt-4">
                                <label class="font-bold text-gray-400 uppercase text-[9px] block">Current Gallery Images</label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div v-for="(img, idx) in form.gallery" :key="idx" class="relative group h-24 bg-gray-50 rounded overflow-hidden border">
                                        <img loading="lazy" decoding="async" :src="img.startsWith('http') ? img : '/images/' + img" class="w-full h-full object-cover" />
                                        <button @click.prevent="removeGalleryImage(idx)" class="absolute top-1 right-1 p-1 bg-black/60 hover:bg-black text-white rounded text-[10px]">âœ•</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Right Column: Settings, Content Lifecycle, SEO -->
                    <div class="space-y-6">
                        
                        <!-- Publishing Panel -->
                        <div class="bg-white p-6 rounded-lg border border-gray-100 shadow-xs space-y-4">
                            <h3 class="font-bold text-gray-800 text-xs uppercase tracking-wider">Publishing Controls</h3>
                            
                            <div>
                                <label class="font-bold text-gray-400 uppercase text-[9px] block">Lifecycle Status</label>
                                <select v-model="form.status" class="w-full text-xs rounded border-gray-300 mt-1">
                                    <option value="draft">Draft (Admin editing)</option>
                                    <option value="preview">Preview (Available for admin preview check)</option>
                                    <option value="published">Published (Visible on live site)</option>
                                    <option value="unpublished">Unpublished (Hidden from site)</option>
                                    <option value="archived">Archived (Soft deleted)</option>
                                </select>
                            </div>

                            <div class="flex items-center gap-2 pt-2">
                                <input v-model="form.featured" type="checkbox" id="featured_checkbox" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                                <label for="featured_checkbox" class="text-xs font-bold text-gray-600 uppercase tracking-wider">Feature in main list</label>
                            </div>

                            <div>
                                <label class="font-bold text-gray-400 uppercase text-[9px] block">Display Sort Order</label>
                                <input v-model="form.sort_order" type="number" class="w-full text-xs rounded border-gray-300 mt-1" />
                            </div>

                            <div class="pt-2 border-t">
                                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded shadow-xs uppercase tracking-wider transition">
                                    Save Changes
                                </button>
                            </div>
                        </div>

                        <!-- SEO Parameters -->
                        <div class="bg-white p-6 rounded-lg border border-gray-100 shadow-xs space-y-4">
                            <h3 class="font-bold text-gray-800 text-xs uppercase tracking-wider">Search Engine Optimization</h3>
                            
                            <div>
                                <label class="font-bold text-gray-400 uppercase text-[9px] block">Meta SEO Title</label>
                                <input v-model="form.seo_title" type="text" class="w-full text-xs rounded border-gray-300 mt-1" placeholder="Page meta title tag..." />
                            </div>

                            <div>
                                <label class="font-bold text-gray-400 uppercase text-[9px] block">Meta SEO Description</label>
                                <textarea v-model="form.seo_description" rows="4" class="w-full text-xs rounded border-gray-300 mt-1" placeholder="Page meta description tag..."></textarea>
                            </div>
                        </div>

                    </div>

                </form>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

