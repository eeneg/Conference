<script setup>
import { Head } from '@inertiajs/vue3';
import { UserGroupIcon, DocumentTextIcon, FolderIcon, BuildingLibraryIcon, ArrowTopRightOnSquareIcon } from '@heroicons/vue/20/solid';
import moment from 'moment';
import _ from 'lodash';

const props = defineProps({
        user_count:Number,
        file_count:Number,
        reference_count:Number,
        conference_count:Number,
        sesssions_today:Object,
        files_review:Object,
        monthly_sessions:Object
    })

</script>

<template>
    <Head title="Dashboard" />
                <!-- Page Heading -->
    <header class="bg-white shadow">
        <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
        </div>
    </header>

    <div class="pt-6 pb-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-center space-x-9">
                        <div class="flex rounded w-80 bg-indigo-600 p-6 text-white space-x-4">
                            <div class="flex items-center">
                                <div class="p-4 bg-blue-900 rounded-full">
                                    <UserGroupIcon class="h-9 w-9"/>
                                </div>
                            </div>
                            <div class="flex flex-col text-justify justify-center">
                                <div class="text-lg font-bold">
                                    {{ user_count }}
                                </div>
                                <div>
                                    Users
                                </div>
                            </div>
                        </div>
                        <div class="flex rounded w-80 bg-indigo-600 p-6 text-white space-x-4">
                            <div class="flex items-center">
                                <div class="p-4 bg-blue-900 rounded-full">
                                    <BuildingLibraryIcon class="h-9 w-9"/>
                                </div>
                            </div>
                            <div class="flex flex-col text-justify justify-center">
                                <div class="text-lg font-bold">
                                    {{ conference_count }}
                                </div>
                                <div>
                                    Sessions
                                </div>
                            </div>
                        </div>
                        <div class="flex rounded w-80 bg-indigo-600 p-6 text-white space-x-4">
                            <div class="flex items-center">
                                <div class="p-4 bg-blue-900 rounded-full">
                                    <DocumentTextIcon class="h-9 w-9"/>
                                </div>
                            </div>
                            <div class="flex flex-col text-justify justify-center">
                                <div class="text-lg font-bold">
                                    {{ file_count }}
                                </div>
                                <div>
                                    Files
                                </div>
                            </div>
                        </div>
                        <div class="flex rounded w-80 bg-indigo-600 p-6 text-white space-x-4">
                            <div class="flex items-center">
                                <div class="p-4 bg-blue-900 rounded-full">
                                    <FolderIcon class="h-9 w-9"/>
                                </div>
                            </div>
                            <div class="flex flex-col text-justify justify-center">
                                <div class="text-lg font-bold">
                                    {{ reference_count }}
                                </div>
                                <div>
                                    References
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="pt-3 pb-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="flex flex-col px-6 pt-6 pb-3 text-gray-900">
                    <div class="text-lg font-bold">
                        Conferences
                    </div>
                    <div class="flex justify-center h-60">
                        <div class="flex flex-col flex-col-reverse text-center" v-for="i in 12">
                            <div class="w-full border-t-2">
                                <div class="md:w-14 lg:w-24">
                                    {{ moment().month(i-1).format('MMM') }}
                                </div>
                            </div>
                            <div class="flex flex-col flex-col-reverse align-bottom pl-4 pr-4 text-center pt-6 h-60">
                                <div
                                    class="relative bg-indigo-200"
                                    :style="`height: ${Math.round((monthly_sessions[moment().month(i-1).format('MMM')]/30)*100)}%;`"
                                >
                                    <span class="absolute bottom-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 font-bold">
                                        {{
                                            monthly_sessions[moment().month(i-1).format('MMM')] ?
                                            monthly_sessions[moment().month(i-1).format('MMM')] :
                                            ''
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="pt-3 pb-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex space-x-2 min-h-[30em]">
                <div class="basis-1/2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="flex flex-col p-6">
                        <div class="grow flex space-x-2 text-lg font-bold">
                            <div>
                                Sessions Today
                            </div>
                            <div>
                                <a :href="route('conferences.index')">
                                    <ArrowTopRightOnSquareIcon class="h-5 w-5"/>
                                </a>
                            </div>
                        </div>
                        <div class="grow flex flex-col mt-3 divide-y divide-slate-700 border-t-2 border-black max-h-[25em] overflow-auto">
                            <div class="w-full py-2" v-for="session in sesssions_today">
                                {{ session.title }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="basis-1/2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="flex flex-col p-6">
                        <div class="grow flex space-x-2 text-lg font-bold">
                            <div>
                                Files For Review
                            </div>
                            <div>
                                <a :href="route('file.index')">
                                    <ArrowTopRightOnSquareIcon class="h-5 w-5"/>
                                </a>
                            </div>
                        </div>
                        <div class="grow flex flex-col mt-3 divide-y divide-slate-700 border-t-2 border-black max-h-[25em] overflow-auto">
                            <div class="w-full flex flex-col py-2" v-for="file in files_review">
                                <div class="">
                                    {{ file.file_name }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    {{ file.title }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</template>
