<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    conference: Object
})

</script>

<template>
    <Head title="Conferences" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Conference {{ conference.title }}</h2>
        </template>

        <div class="py-5">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    {{ conference.attachment.files }}

                    <iframe class="w-full h-screen" height="100%" width="100%" v-for="conf in conference.attachment.files" :src="`/storage/DragonsoverMaestro/Test/02-27.pdf`" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
