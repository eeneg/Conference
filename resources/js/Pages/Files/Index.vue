<script setup>
import SingleFileUpload from './partials/SingleFileUpload.vue';
import MultipleFileUpload from './partials/MultipleFileUpload.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({storage:Object, category:Object, file: Object})


</script>
<template>
    <Head title="Files" />
    <header class="bg-white shadow">
        <div class="px-4 py-6 mx-auto max-w-[90rem] sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Files</h2>
        </div>
    </header>

    <div class="py-5">
        <div class="mx-auto max-w-[90rem] sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg flex flex-row">
                <div class="basis-1/2">
                    <div class="flex flex-row">
                        <div class="pl-5 pr-6 mt-3 grow mb-3">
                            <header>
                                <h2 class="text-lg font-medium text-gray-900">Single File Upload</h2>

                                <p class="mt-1 text-sm text-gray-600">
                                    Upload a file with complete details here.
                                </p>
                            </header>
                        </div>
                    </div>
                    <SingleFileUpload :storage="props.storage" :category="props.category"/>
                </div>
                <div class="basis-1/2">
                    <div class="flex flex-row">
                        <div class="pl-5 pr-6 mt-3 grow mb-3">
                            <header>
                                <h2 class="text-lg font-medium text-gray-900">Multiple File Upload</h2>

                                <p class="mt-1 text-sm text-gray-600">
                                    Upload multiple files and set the details later here.
                                </p>
                            </header>
                        </div>
                    </div>
                    <MultipleFileUpload :storage="props.storage" :category="props.category"/>
                </div>
            </div>
        </div>
    </div>
</template>
