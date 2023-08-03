<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue'
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { PlusCircleIcon, FolderIcon } from '@heroicons/vue/20/solid'
import { router } from '@inertiajs/vue3'
import NavLink from '@/Components/NavLink.vue';
import moment from 'moment'
import { nextTick } from 'vue';

const props = defineProps({
    upcoming: Object,
    finished: Object,
    search: String,
})

const form = useForm({
    search: props.search,
})

const search = () => {
    form.get(route('conferences.index'), {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => nextTick(() => document.getElementById('search').focus())

    })
}

const conferenceForm = () => {
    router.get(route('conferences.create'))
}

const formatDate = (date) => {
    return moment(date).format('L')
}
</script>

<template>
    <Head title="Conferences" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Conferences</h2>
        </template>

        <div class="py-5">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="flex flex-row">
                        <div class="pl-6 pr-6 mt-3 grow">
                            <header>
                                <h2 class="text-lg font-medium text-gray-900">Upcoming Conferences</h2>

                                <p class="mt-1 text-sm text-gray-600">
                                    List of upcoming conferences.
                                </p>
                            </header>
                        </div>
                        <div class="pl-6 pr-6 mt-3">
                            <PrimaryButton @click="conferenceForm()">
                                Add
                                <PlusCircleIcon class="w-5 h-5" aria-hidden="true" />
                            </PrimaryButton>
                        </div>
                    </div>

                    <div class="pl-6 pr-6 mt-3">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th class="" style="width: 30%;">
                                        Title
                                    </th>
                                    <th class="" style="width: 30%;">
                                        Date
                                    </th>
                                    <th class="" style="width: 30%;">
                                        Input Minutes
                                    </th>
                                    <th class="" style="width: 30%;">

                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-slate-700">
                                <tr v-for="conf in props.upcoming.data">
                                    <td class="p-2 text-center border-b border-slate-100">
                                        {{conf.title}}
                                    </td>
                                    <td class="p-2 text-center border-b border-slate-100">
                                        {{formatDate(conf.date)}}
                                    </td>
                                    <td class="p-2 text-center border-b border-slate-100">
                                        <NavLink :href="route('minutes.create', {'id' : conf.id})">Minutes</NavLink>
                                    </td>
                                    <td class="p-2 text-center border-b border-slate-100">
                                        <NavLink :href="route('conferences.show', {'id' : conf.id})">Action</NavLink>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-6">
                        <Pagination :data="props.upcoming"></Pagination>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-5">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="pl-6 pr-6 mt-3">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">Finished Conferences</h2>

                            <p class="mt-1 text-sm text-gray-600">
                                List of finished conferences.
                            </p>
                        </header>
                    </div>
                    <form @submit.prevent="search" class="flex flex-row mt-1 space-y-6">
                        <div class="p-6 grow">
                            <InputLabel value="Search" for="search" />
                            <div class="relative mt-2 rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">
                                        <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512">
                                            <path d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z"/>
                                        </svg>
                                    </span>
                                </div>

                                <TextInput id="search" type="search" class="block w-full mt-1 pl-9" v-model="form.search" />
                            </div>
                        </div>
                    </form>
                    <div class="pl-6 pr-6 mt-3">
                        <table class="w-full">
                            <thead class="divide-slate-700">
                                <tr>
                                    <th style="width: 30%;">Title</th>
                                    <th style="width: 30%;">Date</th>
                                    <th style="width: 30%;">Input Minutes</th>
                                    <th style="width: 30%;"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-slate-700">
                                <tr v-for="conf in props.finished.data">
                                    <td class="p-2 text-center border-b border-slate-100">
                                        <Link :href="route('conferences.show', conf.id)">
                                            {{conf.title}}
                                        </Link>
                                    </td>
                                    <td class="p-2 text-center border-b border-slate-100">{{conf.date}}</td>
                                    <td class="p-2 text-center border-b border-slate-100">Minutes</td>
                                    <td class="p-2 text-center border-b border-slate-100">
                                        Action
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-6">
                        <Pagination :data="props.finished"></Pagination>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
