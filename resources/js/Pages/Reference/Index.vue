<script setup>
    import { useForm } from '@inertiajs/vue3';
    import SettingsLayout from '@/Layouts/SettingsLayout.vue';
    import TextInput from '@/Components/TextInput.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import Modal from '@/Components/Modal.vue';
    import SecondaryButton from '@/Components/SecondaryButton.vue';
    import DangerButton from '@/Components/DangerButton.vue';
    import InputError from '@/Components/InputError.vue';
    import Pagination from '@/Components/Pagination.vue';
    import {ref} from  'vue';
    import axios from 'axios';

    const props = defineProps({reference: Object, refrenceCategory: Object})

    const form = useForm({
        id: null,
        category_id: null,
        file: {},
        title: null,
        date: null,
        details: null
    })

    const addCategoryForm = useForm({
        title: null,
        type: 2,
        details: null
    })

    const addCategoryModal = ref(false)

    var referenceList = ref(null)

    const formModal = ref(false)

    const confirmForm = useForm({
        password: null
    })

    const edit = ref(false)

    const confirmingReferenceDeletion = ref(false)

    var header = ""
    var message = ""
    var success = true

    const modalShow = ref(false)

    const submitCategory = () => {
        addCategoryForm.submit('post', route('category.store'), {
            onSuccess: () => {
                header = "Success!"
                success = true
                message = "Reference Submitted Successfuly"
                modalShow.value = true
                addCategoryForm.reset()
            },
            onError: () => {
                header = "Error!"
                success = false
                message = "Something went wrong"
                modalShow.value = true
            }
        })
    }


    const submit = () => {
        form.submit('post', route('reference.store'),{
            preserveScroll: true,
            onSuccess: () => {
                header = "Success!"
                success = true
                message = "Reference Submitted Successfuly"
                modalShow.value = true
                form.reset()
            },
            onError: () => {
                header = "Error!"
                success = false
                message = "Something went wrong"
                modalShow.value = true
            }
        })
    }

    const update = () => {
        form.submit('patch', route('reference.update', form.id),{
            preserveScroll: true,
            onSuccess: () => {
                edit.value = false
                header = "Success!"
                success = true
                message = "Reference Updated Successfuly"
                modalShow.value = true
                form.reset()
            },
            onError: () => {
                header = "Error!"
                success = false
                message = "Something went wrong!"
                modalShow.value = true
            }
        })
    }

    const checkReference = () => {

        confirmingReferenceDeletion.value = true

    }

    const deleteReference = () => {
        confirmForm.submit('delete', route('reference.destroy', form.id),{
            preserveScroll: true,
            onSuccess: () => {
                edit.value = false
                header = "Success!"
                success = true
                message = "Reference Deleted Successfuly"
                modalShow.value = true
                form.reset()
                closeDeleteModal()
                confirmForm.reset()
            },
            onError: () => {
                edit.value = false
                header = "Error!"
                success = false
                message = "Something went wrong!"
                modalShow.value = true
            }
        })
    }

    const openAddCategoryForm = () => {
        addCategoryForm.reset()
        addCategoryForm.errors = {}
        header = "Category!"
        message = "Add Reference Category"
        addCategoryModal.value = true
    }

    const closeAddCategoryForm = () => {
        addCategoryModal.value = false
    }

    const openInputForm = () => {
        formModal.value = true
        header = "Create!"
        message = "Upload Reference File Here"
        form.reset()
    }

    const closeInputForm = () => {
        formModal.value = false
        form.errors = {}
        form.reset()
        edit.value = false
    }

    const getFile = (e) => {
        form.file = e.target.files[0]
    }

    const fillForm = (i) => {
        Object.assign(form, i)
        formModal.value = true
        edit.value = true
    }

    const closeDeleteModal = () => {
        confirmingReferenceDeletion.value = false
    }

    const closeModal = () => {
        modalShow.value =  false
    }



</script>
<template>
    <SettingsLayout :type="3">
        <div>
            <div class="py-5">
                <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="flex flex-row">
                            <div class="pl-5 pr-6 mt-3 grow mb-3">
                                <header>
                                    <h2 class="text-lg font-medium text-gray-900">References</h2>

                                    <p class="mt-1 text-sm text-gray-600">
                                        Management tab for references
                                    </p>
                                </header>
                            </div>
                            <div class="pl-5 pr-6 mt-3 grow mb-3">
                                <div class="flex space-x-2 float-right">
                                    <PrimaryButton class="float-right" @click="openAddCategoryForm">Category</PrimaryButton>
                                    <PrimaryButton class="float-right" @click="openInputForm">Add</PrimaryButton>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 mb-3 mr-3 pr-6 pl-5">
                            <div class="flex w-full md:flex-row-reverse sm:flex-row-reverse">
                                <select name="cat" v-model="referenceList" id="cat" class="border min-[300px]:w-full rounded text-gray-700 border-gray-300">
                                    <option :value="null" selected>Select Reference Category</option>
                                    <option :value="category.reference" v-for="category in props.reference">{{ category.title.charAt(0).toUpperCase() + category.title.slice(1) }}</option>
                                </select>
                            </div>
                            <div class="basis-full p-2 mb-9 max-h-96 overflow-auto mt-3">
                                <table class="w-full table-auto text-center">
                                    <thead>
                                        <tr>
                                            <th class="border-b border-slate-300" style="width: 30%;">Title</th>
                                            <th class="border-b border-slate-300" style="width: 20%;">File Name</th>
                                            <th class="border-b border-slate-300" style="width: 20%;">Date</th>
                                            <th class="border-b border-slate-300" style="width: 10%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="i in referenceList" class="border-b-2 py-2">
                                            <td class="py-2 text-wrap">{{i.title}}</td>
                                            <td class="py-2 text-wrap">{{i.file_name}}</td>
                                            <td class="py-2 text-wrap">{{i.date}}</td>
                                            <td class="py-2 text-wrap"><button class="border-b-2 border-b-indigo-400 hover:border-b-indigo-800" @click="fillForm(i)">Edit</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <Modal :show="addCategoryModal" :maxWidth="'2xl'">
                <div class="p-6">

                    <div class="flex flex-row">
                        <div class="basis-1/2">
                            <h2 class="text-lg font-medium text-black-500">
                                {{ header }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                {{message}}
                            </p>
                        </div>
                        <div class="basis-1/2">
                            <a class="float-right" role="button" @click="closeAddCategoryForm">
                                <svg x="0px" y="0px" width="20" height="20" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" opacity=".35"></circle><path d="M14.812,16.215L7.785,9.188c-0.384-0.384-0.384-1.008,0-1.392l0.011-0.011c0.384-0.384,1.008-0.384,1.392,0l7.027,7.027	c0.384,0.384,0.384,1.008,0,1.392l-0.011,0.011C15.82,16.599,15.196,16.599,14.812,16.215z"></path><path d="M7.785,14.812l7.027-7.027c0.384-0.384,1.008-0.384,1.392,0l0.011,0.011c0.384,0.384,0.384,1.008,0,1.392l-7.027,7.027	c-0.384,0.384-1.008,0.384-1.392,0l-0.011-0.011C7.401,15.82,7.401,15.196,7.785,14.812z"></path>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <div class="mt-3 mb-3 mr-3 pr-6 pl-5">
                        <div class="">
                            <div class="mt-4">
                                <InputLabel>Title</InputLabel>
                                <TextInput class="w-full" type="text" v-model="addCategoryForm.title" placeholder="Title"/>
                                <InputError :message="addCategoryForm.errors.title" class="mt-2" />
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="">
                                <InputLabel>Details</InputLabel>
                                <textarea class="w-full rounded border-gray-300 h-24" type="text" v-model="addCategoryForm.details" placeholder="Details"></textarea>
                                <InputError :message="addCategoryForm.errors.details" class="mt-2" />
                            </div>
                        </div>
                        <div class="flex flex-row">
                            <div class="space-x-3 mt-2">
                                <PrimaryButton @click="submitCategory">Save</PrimaryButton>
                            </div>
                        </div>
                    </div>

                </div>
            </Modal>

            <Modal :show="formModal" :maxWidth="'2xl'">
                <div class="p-6">

                    <div class="flex flex-row">
                        <div class="basis-1/2">
                            <h2 class="text-lg font-medium text-black-500">
                                {{ header }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                {{message}}
                            </p>
                        </div>
                        <div class="basis-1/2">
                            <a class="float-right" role="button" @click="closeInputForm">
                                <svg x="0px" y="0px" width="20" height="20" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" opacity=".35"></circle><path d="M14.812,16.215L7.785,9.188c-0.384-0.384-0.384-1.008,0-1.392l0.011-0.011c0.384-0.384,1.008-0.384,1.392,0l7.027,7.027	c0.384,0.384,0.384,1.008,0,1.392l-0.011,0.011C15.82,16.599,15.196,16.599,14.812,16.215z"></path><path d="M7.785,14.812l7.027-7.027c0.384-0.384,1.008-0.384,1.392,0l0.011,0.011c0.384,0.384,0.384,1.008,0,1.392l-7.027,7.027	c-0.384,0.384-1.008,0.384-1.392,0l-0.011-0.011C7.401,15.82,7.401,15.196,7.785,14.812z"></path>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <div class="mt-3 mb-3 mr-3 pr-6 pl-5">
                        <div class="">
                            <div class="mt-4">
                                <InputLabel>Upload File</InputLabel>
                                <input class="w-full" id="file" type="file" v-on:change="getFile" accept="application/pdf"/>
                                <InputError :message="form.errors.file" class="mt-2" />
                            </div>
                            <div class="mt-4">
                                <InputLabel>Category</InputLabel>
                                <select v-model="form.category_id" name="category_id" id="category_id" class="border w-full rounded text-gray-700 border-gray-300">
                                    <option :value="null" selected>---</option>
                                    <option :value="category.id" v-for="category in props.refrenceCategory">{{ category.title.charAt(0).toUpperCase() + category.title.slice(1) }}</option>
                                </select>
                                <InputError :message="form.errors.category_id" class="mt-2" />
                            </div>
                            <div class="mt-4">
                                <InputLabel>Title</InputLabel>
                                <TextInput class="w-full" type="text" v-model="form.title" placeholder="Title"/>
                                <InputError :message="form.errors.title" class="mt-2" />
                            </div>
                            <div class="mt-4">
                                <InputLabel>Date</InputLabel>
                                <input class="w-full rounded border-gray-300" type="date" v-model="form.date" placeholder="Title"/>
                                <InputError :message="form.errors.date" class="mt-2" />
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="">
                                <InputLabel>Details</InputLabel>
                                <textarea class="w-full rounded border-gray-300 h-24" type="text" v-model="form.details" placeholder="Details"></textarea>
                                <InputError :message="form.errors.details" class="mt-2" />
                            </div>
                        </div>
                        <div class="flex flex-row">
                            <div class="space-x-3 mt-2">
                                <PrimaryButton @click="submit" v-if="edit == false">Save</PrimaryButton>
                                <PrimaryButton @click="update" v-if="edit">Save Changes</PrimaryButton>
                                <SecondaryButton @click="closeInputForm">Cancel</SecondaryButton>
                                <DangerButton v-if="edit" class="float-right" @click="checkReference">Delete</DangerButton>
                            </div>
                        </div>
                    </div>

                </div>
            </Modal>


            <Modal :show="confirmingReferenceDeletion" @close="closeModal">
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-900">
                        Are you sure you want to delete the category?
                    </h2>

                    <p class="mt-1 text-sm text-gray-600">
                        Once the category is deleted, all of its resources and data will be permanently deleted. Please
                        enter your password to confirm you would like to permanently delete the category.
                    </p>

                    <div class="mt-6">
                        <InputLabel for="password" value="Password" class="sr-only" />

                        <TextInput
                            id="password"
                            ref="passwordInput"
                            v-model="confirmForm.password"
                            type="password"
                            class="mt-1 block w-3/4"
                            placeholder="Password"
                            @keyup.enter="deleteReference"
                        />

                        <InputError :message="confirmForm.errors.password" class="mt-2" />
                    </div>

                    <div class="mt-6 flex justify-end">
                        <SecondaryButton @click="closeDeleteModal"> Cancel </SecondaryButton>

                        <DangerButton
                            class="ml-3"
                            :class="{ 'opacity-25': confirmForm.processing }"
                            :disabled="confirmForm.processing"
                            @click="deleteReference"
                        >
                            Delete Reference
                        </DangerButton>
                    </div>
                </div>
            </Modal>


            <Modal :show="modalShow">
                <div class="p-6">
                    <h2 :class="{'text-lg font-medium text-green-500': success == true, 'text-lg font-medium text-red-500': success == false}">
                        {{ header }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-600">
                        {{message}}
                    </p>

                    <SecondaryButton
                        class="w-full mt-2 place-content-center"
                        @click="closeModal">
                            <p>OK</p>
                    </SecondaryButton>
                </div>
            </Modal>
        </div>
    </SettingsLayout>
</template>
