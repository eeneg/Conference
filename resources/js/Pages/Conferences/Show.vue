<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { ref } from 'vue';

const props = defineProps({
    conference: Object
})

const tab = ref('agenda')

onMounted(() => {

})

const fullscreen = (id) => {
    document.getElementById(id).requestFullscreen()
}
</script>

<template>
    <Head title="Conferences" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Conference</h2>
        </template>

        <div class="py-5">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="relative py-2 overflow-hidden bg-white shadow-sm sm:rounded-md">
                    <div class="flex px-2 max-w-7xl sm:px-6 lg:px-8">
                        <h2 class="mt-3 text-xl font-bold capitalize">
                            {{ conference.title }}
                            <span class="px-2 py-1 text-xs text-white normal-case bg-green-500 rounded-md tabular-nums">
                                completed
                                ({{ conference.date }})
                            </span>
                        </h2>
                    </div>
                    <nav class="bg-white">
                        <div class="px-2 mx-auto max-w-7xl sm:px-6 lg:px-8">
                            <div class="relative flex items-center justify-between h-16">
                                <div class="flex items-center justify-center flex-1 sm:items-stretch sm:justify-start">
                                    <div class="flex space-x-4">
                                        <button
                                            class="px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-md"
                                        >
                                            Agenda
                                        </button>
                                        <button
                                            class="px-3 py-2 text-sm font-medium text-gray-800 rounded-md hover:bg-gray-700 hover:text-white"
                                        >
                                            Attachments
                                        </button>
                                        <span class="ordinal tabular-nums">
                                            1st
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </nav>

                    <template v-for="files in conference.attachment.files">
                        <div
                            class="relative"
                            v-for="file in files.files"
                        >
                            <span class="font-mono font-bold tracking-wide text-gray-700">
                                {{ file.fileName }}
                            </span>

                            <button
                                class="absolute p-2 text-white bg-black rounded-md opacity-0 top-14 right-10 hover:opacity-100"
                                @click="fullscreen(file.path)"
                            >
                                Fullscreen
                            </button>

                            <iframe
                                :id="file.path"
                                :src="`/storage/${file.path}`"
                                :title="file.fileName"
                                class="w-full h-screen"
                                height="100%"
                                width="100%"
                                frameborder="0"
                                allowfullscreen
                            >
                            </iframe>
                        </div>
                    </template>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
