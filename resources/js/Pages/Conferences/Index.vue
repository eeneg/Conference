<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue'
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { PlusCircleIcon } from '@heroicons/vue/20/solid'
import { router } from '@inertiajs/vue3'
import NavLink from '@/Components/NavLink.vue';
import moment from 'moment'
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { nextTick, ref } from 'vue';
import axios from 'axios';

const role = usePage().props.auth.role;
const user = usePage().props.auth.user;

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

const reset = () => {
    form.search = ""
    form.get(route('conferences.index'), {
        preserveScroll: true,
        preserveState: true,
    })
}

const conferenceForm = () => {
    router.get(route('conferences.create', {conf: null, edit: false}))
}

const formatDate = (date) => {
    return moment(date).format('L')
}

const addAttendance = (conference_id, date) => {
    if(role == 'administrator' && date == moment().format('yyyy-MM-D').toString()){
        axios.post(route('attendance.store'), {conference_id: conference_id, user_id: user.id})
        .then(e => {
            console.log('attendance added')
        })
        .catch(e => {
            console.log('attendance not added! Something went Wrong!')
        })
    }
}

const userAttendance = ref({})
const attendanceModal = ref(false)
const conf_id = ref(null)
const openAttendanceModal = (conference_id) => {
    attendanceModal.value = true
    conf_id.value = conference_id
    costumAttendanceForm.conf_id = conference_id
    getAttendance()
}
const closeAttendanceModal = () => {
    attendanceModal.value = false
}
const getAttendance = () => {
    axios.get(route('attendance.index', {conference_id: conf_id.value}))
    .then(({data}) => {
        userAttendance.value = data
    })
    .catch(e => {
        console.log('Something went Wrong!')
    })
}

const deleteAttendance = (user_id) => {
    axios.post(route('attendance.delete'), {id: user_id, conference_id:conf_id.value})
    .then(({data}) => {
        getAttendance()
    })
    .catch(e => {
        console.log('Something went Wrong!')
    })
}

const boardMembers = ref({})
const searchBoardMembers = ref(null)
const searching = ref(false)
const searchBM = () => {
    searching.value = true
    axios.get(route('search.bm', {search: searchBoardMembers.value}))
    .then(({data}) => {
        searching.value = false
        boardMembers.value = data
    })
    .catch(e => {
        console.log('Search went Wrong!')
    })
}
const manualAddAttendance = (id) => {
    axios.post(route('attendance.store'), {conference_id: conf_id.value, user_id: id})
    .then(e => {
        getAttendance()
        console.log('attendance added')
    })
    .catch(e => {
        console.log('Attendance not added! Something went Wrong!')
    })
}

const costumAttendanceForm = useForm({
    id: null,
    name:null,
    conf_id: null
})
const customAttendanceAdd = () => {
    costumAttendanceForm.submit('post', route('manualAttendance.store'),{
        onSuccess: (e) => {
            getAttendance()
        },
        onError: (e) => {
            console.log(e)
        }
    })
}

const deleteCustomAttendance = (id) => {
    costumAttendanceForm.submit('delete', route('manualAttendance.destroy', id),{
        onSuccess: (e) => {
            getAttendance()
        },
        onError: (e) => {
            console.log(e)
        }
    })
}
</script>

<template>
    <Head title="Conferences" />

    <header class="bg-white shadow">
        <div class="px-4 py-6 mx-auto max-w-[90rem] sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Conference</h2>
        </div>
    </header>

    <div class="py-5">
        <div class="mx-auto max-w-[90rem] sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
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
                                <th class="border-b border-slate-300" style="width: 30%;">
                                    Title
                                </th>
                                <th class="border-b border-slate-300" style="width: 30%;">
                                    Date
                                </th>
                                <th class="border-b border-slate-300" style="width: 30%;">
                                    Input Minutes
                                </th>
                                <th class="border-b border-slate-300" style="width: 30%;">

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
                                    <Dropdown align="right" width="48">
                                        <template #trigger>
                                            <span class="inline-flex rounded-md">
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border border-transparent rounded-md hover:text-gray-700 focus:outline-none"
                                                >
                                                    Action
                                                    <svg
                                                        class="ml-2 -mr-0.5 h-4 w-4"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 20 20"
                                                        fill="currentColor"
                                                    >
                                                        <path
                                                            fill-rule="evenodd"
                                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                            clip-rule="evenodd"
                                                        />
                                                    </svg>
                                                </button>
                                            </span>
                                        </template>

                                        <template #content>
                                            <DropdownLink :href="route('conferences.edit', {'id' : conf.id})">Edit</DropdownLink>
                                            <DropdownLink :href="route('agenda.index', {'id' : conf.id})">Agenda</DropdownLink>
                                            <DropdownLink @click="addAttendance(conf.id, conf.date)" :href="route('conferences.show', conf.id)">View</DropdownLink>
                                            <div @click="openAttendanceModal(conf.id)" class="cursor-pointer block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                                                Attendance
                                            </div>
                                        </template>
                                    </Dropdown>
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
        <div class="mx-auto max-w-[90rem] sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg pt-1">
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
                <div class="flex flex-row-reverse pl-6 pr-6">
                    <div class="">
                        <SecondaryButton @click="reset">Reset</SecondaryButton>
                    </div>
                </div>
                <div class="pl-6 pr-6 mt-3">
                    <table class="w-full">
                        <thead class="divide-slate-700">
                            <tr>
                                <th class="border-b border-slate-300" style="width: 30%;">Title</th>
                                <th class="border-b border-slate-300" style="width: 30%;">Date</th>
                                <th class="border-b border-slate-300" style="width: 30%;">Input Minutes</th>
                                <th class="border-b border-slate-300" style="width: 30%;"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-slate-700">
                            <tr v-for="conf in props.finished.data">
                                <td class="p-2 text-center border-b border-slate-100">{{conf.title}}</td>
                                <td class="p-2 text-center border-b border-slate-100">{{conf.date}}</td>
                                <td class="p-2 text-center border-b border-slate-100">
                                    <NavLink :href="route('minutes.create', {'id' : conf.id})">Minutes</NavLink>
                                </td>
                                <td class="p-2 text-center border-b border-slate-100">
                                    <div class="relative">
                                        <Dropdown align="right" width="48">
                                            <template #trigger>
                                                <span class="inline-flex rounded-md">
                                                    <button
                                                        type="button"
                                                        class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border border-transparent rounded-md hover:text-gray-700 focus:outline-none"
                                                    >
                                                        Action
                                                        <svg
                                                            class="ml-2 -mr-0.5 h-4 w-4"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            viewBox="0 0 20 20"
                                                            fill="currentColor"
                                                        >
                                                            <path
                                                                fill-rule="evenodd"
                                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                                clip-rule="evenodd"
                                                            />
                                                        </svg>
                                                    </button>
                                                </span>
                                            </template>

                                            <template #content>
                                                <DropdownLink :href="route('conferences.edit', {'id' : conf.id})">Edit</DropdownLink>
                                                <DropdownLink :href="route('agenda.index', {'id' : conf.id})">Agenda</DropdownLink>
                                                <DropdownLink :href="route('conferences.show', conf.id)">View</DropdownLink>
                                                <div @click="openAttendanceModal(conf.id)" class="cursor-pointer block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                                                    Attendance
                                                </div>
                                            </template>
                                        </Dropdown>
                                    </div>
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

    <Modal :show="attendanceModal" :maxWidth="'4xl'" @close="closeAttendanceModal">
        <div class="p-6">

            <h2 class="text-lg font-medium text-black-500">
                Attendance
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Manage Attendace
            </p>

            <div class="mt-2 flex flex-col">
                <div>
                    <InputLabel for="search">Add Employee to Attendance</InputLabel>
                </div>
                <div class="flex space-x-3">
                    <div class="grow content-center">
                        <TextInput id="search" v-model="searchBoardMembers" name="search" class="w-full" />
                    </div>
                    <div class="content-center">
                        <PrimaryButton class="h-full" @click="searchBM()">Search</PrimaryButton>
                    </div>
                </div>
                <div v-if="searching">
                    Searching...
                </div>
                <div class="flex flex-col min-h-5 max-h-64 mt-2 overflow-y-auto">
                    <div class="flex w-full p-2 mt-2 rounded" :class="{'bg-gray-200' : i % 2 == 0}" v-for="(bm, i) in boardMembers">
                        <div class="basis-1/2 content-center truncate pr-9">
                            {{ bm.name }}
                        </div>
                        <div class="basis-1/2 content-center truncate pr-9">
                            {{ bm.email }}
                        </div>
                        <div class="content-center">
                            <SecondaryButton @click="manualAddAttendance(bm.id)">Add</SecondaryButton>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex flex-col mt-2">
                <div>
                    <InputLabel for="manual">Custom Attendance</InputLabel>
                </div>
                <div class="flex flex-col">
                    <div class="flex space-x-3">
                        <div class="grow content-center">
                            <TextInput class="w-full" v-model="costumAttendanceForm.name" placeholder="Attendance for users without an account"></TextInput>
                        </div>
                        <div class="">
                            <PrimaryButton class="h-full" @click="customAttendanceAdd()">Add</PrimaryButton>
                        </div>
                    </div>
                    <div>
                        <InputError :message="costumAttendanceForm.errors.name" class="mt-2" />
                        <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition ease-in-out">
                            <p v-if="costumAttendanceForm.recentlySuccessful" class="text-sm text-gray-600">Success.</p>
                        </Transition>
                    </div>
                </div>
            </div>
            <div class="flex flex-col max-h-64 overflow-y-auto">
                <div class="flex w-full p-2 mt-2 rounded bg-indigo-100" :class="{'bg-indigo-300' : i % 2 == 0}" v-for="(attendance, i) in userAttendance.custom">
                    <div class="basis-full content-center truncate pr-9">
                        {{ attendance.name }}
                    </div>
                    <div class="content-center">
                        <SecondaryButton @click="deleteCustomAttendance(attendance.id)">Delete</SecondaryButton>
                    </div>
                </div>
            </div>
            <div class="mt-8">
                <h2 for="search" class="text-lg text-green-800">Present</h2>
                <hr>
            </div>
            <div class="flex flex-col max-h-64 overflow-y-auto">
                <div class="flex w-full p-2 mt-2 rounded" :class="{'bg-gray-200' : i % 2 == 0}" v-for="(attendance, i) in userAttendance.present">
                    <div class="basis-1/2 content-center truncate pr-9">
                        {{ attendance.name }}
                    </div>
                    <div class="basis-1/2 content-center pl-2 truncate pr-9">
                        {{ attendance.email }}
                    </div>
                    <div class="content-center">
                        <SecondaryButton @click="deleteAttendance(attendance.id)">Delete</SecondaryButton>
                    </div>
                </div>
            </div>
            <div class="mt-6">
                <h2 for="search" class="text-lg text-red-800">Absent</h2>
                <hr>
            </div>
            <div class="flex flex-col max-h-64 overflow-y-auto">
                <div class="flex w-full p-2 mt-2 rounded" :class="{'bg-gray-200' : i % 2 == 0}" v-for="(attendance, i) in userAttendance.absent">
                    <div class="basis-1/2 content-center">
                        {{ attendance.name }}
                    </div>
                    <div class="basis-1/2 content-center pl-2">
                        {{ attendance.email }}
                    </div>
                    <div class="content-center">
                        <SecondaryButton @click="manualAddAttendance(attendance.id)">Add</SecondaryButton>
                    </div>
                </div>
            </div>


            <div class="mt-6 flex items-center">
                <div class="grow">
                    <SecondaryButton @click="closeAttendanceModal"> Close </SecondaryButton>
                </div>
            </div>
        </div>
    </Modal>

</template>
