<script setup>
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { Head } from '@inertiajs/vue3';
    import {ref} from  'vue';
    import { router } from '@inertiajs/vue3'

    const props = defineProps({type:Number})

    const changeType = (r) => {
        router.visit(route(r), {
            preserveScroll: true,
        })
    }

</script>
<template>
    <Head title="Attachments" />

    <header class="bg-white shadow">
        <div class="px-4 py-6 mx-auto max-w-[90rem] sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Settings</h2>
        </div>
    </header>

    <div class="mx-auto max-w-[90rem] sm:px-6 lg:px-8 flex md:flex-row sm:flex-col min-[300px]:flex-col">

        <div class="md:basis-1/5 py-5">
            <ul class="space-y-2">
                <li class="">
                    <button @click="changeType('storage.index')" class="border-l-4 pl-4 pt-1 pb-1 border-transparent w-full text-left" :class="{'border-l-4 w-full border-indigo-500 bg-slate-200 border-l-indigo-500' : props.type == 1}">Physical Storage</button>
                </li>
                <li class="">
                    <button @click="changeType('category.index')" class="border-l-4 pl-4 pt-1 pb-1 border-transparent w-full text-left" :class="{'border-l-4 w-full border-indigo-500 bg-slate-200 border-l-indigo-500' : props.type == 2}">Category</button>
                </li>
                <li class="">
                    <button @click="changeType('reference.index')" class="border-l-4 pl-4 pt-1 pb-1 border-transparent w-full text-left" :class="{'border-l-4 w-full border-indigo-500 bg-slate-200 border-l-indigo-500' : props.type == 3}">Reference</button>
                </li>
            </ul>
        </div>

        <div class="basis-full">
            <slot/>
        </div>
    </div>

</template>
