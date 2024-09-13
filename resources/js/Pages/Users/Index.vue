<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue'
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { PlusCircleIcon, XCircleIcon } from '@heroicons/vue/20/solid';
import Modal from '@/Components/Modal.vue';
import ResponseModal from '@/Components/ResponseModal.vue';
import { nextTick, ref } from 'vue';
import InputError from '@/Components/InputError.vue';

    const props = defineProps({users: Object, search:String})

    const form = useForm({
        search: props.search
    })

    const roleForm = useForm({
        password: null,
        user_id: null,
        role_id: null
    })

    const search = () => {
        form.get(route('users.index'), {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => nextTick(() => document.getElementById('search').focus())
        })
    }

    const reset = () => {
        form.search = ""
        form.get(route('users.index'), {
            preserveScroll: true,
            preserveState: true,
        })
    }

    const formatRole = (role) => {
        if(role != null || role != ''){
            return role.charAt(0).toUpperCase() + role.slice(1)
        }else{
            return ''
        }
    }

    const actionBtn = (id) => {
        router.visit(route('users.edit', id))
    }

    const modalShow = ref(false)
    const userForm = useForm({
        email:null,
        name:null,
        password:null,
        password_confirmation: null
    })

    const headers = ref(null)
    const message = ref(null)
    const success = ref(false)
    const responseModalProp = ref(false)
    const registerUser = () => {
        userForm.submit('post', route('users.store'), {
            onSuccess: (e) => {
                headers.value = "Success"
                message.value = "User Successfully Registered!"
                success.value = true
                responseModalProp.value = true
                userForm.reset()
            },
            onError: (e) => {
                headers.value = "Error"
                message.value = "Something went wrong!"
                success.value = false
                responseModalProp.value = true
                console.log(e)
            }
        })
    }
    const addUserModal = () => {
        userForm.reset()
        modalShow.value = true
    }
    const closeAdduserModal = () => {
        userForm.reset()
        modalShow.value = false
    }
    const closeResponseModal = () => {
        responseModalProp.value = false
    }

</script>

<template>
    <Head title="Users" />

    <header class="bg-white shadow">
        <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Users</h2>
        </div>
    </header>

    <div class="py-5">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="pl-6 pr-6 mt-3 grow flex justify-between">
                    <div>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">Users</h2>

                            <p class="mt-1 text-sm text-gray-600">
                                List of all users.
                            </p>
                        </header>
                    </div>
                    <div class="py-2">
                        <PrimaryButton @click="addUserModal()">Add <PlusCircleIcon class="h-5 w-5"/></PrimaryButton>
                    </div>
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
                <div class="pl-6 pr-6 text-gray-900">
                    <table class="w-full table-auto">
                        <thead>
                            <tr>
                                <th class="border-b border-slate-300" style="width: 30%;">Name</th>
                                <th class="border-b border-slate-300" style="width: 30%;">Email</th>
                                <th class="border-b border-slate-300" style="width: 20%;">Role</th>
                                <th class="border-b border-slate-300" style="width: 20%;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in props.users.data">
                                <td class="p-2 pl-8 border-b border-slate-100">{{ user.name }}</td>
                                <td class="p-2 pl-8 border-b border-slate-100">{{ user.email }}</td>
                                <td class="p-2 pl-8 text-center border-b border-slate-100 relative">
                                    {{ formatRole(user.roles[0].title) }}
                                </td>
                                <td class="p-2 pl-8 text-center border-b border-slate-100">
                                    <!-- <NavLink :href="route('users.edit', user.id)">Action</NavLink> -->
                                    <PrimaryButton @click="actionBtn(user.id)">Action</PrimaryButton>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="p-6">
                    <Pagination :data="props.users"></Pagination>
                </div>
            </div>
        </div>

        <Modal :show="modalShow">
            <div class="p-6">
                <div class="flex justify-between">
                    <div>
                        <h2 class="text-lg font-medium">
                            Add User
                        </h2>

                        <p class="mt-1 text-sm text-gray-600">
                            Fill in the form to Register a new User
                        </p>
                    </div>
                    <div class="hover:cursor-pointer" @click="closeAdduserModal()">
                        <XCircleIcon class="h-5"/>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="flex mt-2">
                        <div class="w-full">
                            <InputLabel>Email</InputLabel>
                            <TextInput v-model="userForm.email" class="w-full"/>
                            <InputError :message="userForm.errors.email" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex mt-2">
                        <div class="w-full">
                            <InputLabel>Name</InputLabel>
                            <TextInput v-model="userForm.name" class="w-full"/>
                            <InputError :message="userForm.errors.name" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex mt-2">
                        <div class="w-full">
                            <InputLabel>Password</InputLabel>
                            <TextInput
                                v-model="userForm.password"
                                class="mt-2 block w-full"
                                type="password"
                                autocomplete="new-password"
                            />
                            <InputError :message="userForm.errors.password" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex mt-2">
                        <div class="w-full">
                            <InputLabel>Confirm Password</InputLabel>
                            <TextInput
                                v-model="userForm.password_confirmation"
                                class="mt-2 block w-full"
                                type="password"
                                autocomplete="new-password"
                            />
                        </div>
                    </div>
                </div>

                <PrimaryButton
                    class="w-full mt-6 place-content-center"
                    @click="registerUser()">
                                <p>Submit</p>
                </PrimaryButton>
            </div>
        </Modal>

        <ResponseModal @closeResponseModal="closeResponseModal()" :header="headers" :message="message" :success="success" :responseModalProp="responseModalProp"/>

    </div>
</template>
