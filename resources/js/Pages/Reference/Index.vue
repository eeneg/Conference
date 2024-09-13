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
    import {nextTick, ref} from  'vue';
    import axios from 'axios';
    import { EyeIcon } from '@heroicons/vue/20/solid';
    import _ from 'lodash';

    const props = defineProps({reference: Object, refrenceCategory: Object, refsearch: String, refcategory: String})

    const form = useForm({
        id: null,
        category_id: null,
        file: {},
        title: null,
        date: null,
        details: null
    })

    const reference = ref(props.reference)

    const addCategoryForm = useForm({
        title: null,
        type: 2,
        details: null
    })

    const addCategoryModal = ref(false)

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
                document.getElementById('file').value = null
                modalShow.value = true
                reference.value = props.reference
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
                reference.value = props.reference
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
                reference.value = props.reference
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

    const viewPDF = ref(false)
    const pdfPath = ref(null)
    const pdfTitle = ref(null)
    const pdfDetails = ref(null)
    const view = (i) => {
        console.log(i)
        axios.get(`/viewReferenceBlobPDF/${i.id}`)
        .then(e => {
            viewPDF.value = true
            pdfPath.value = e.data
            pdfTitle.value = i.title
            pdfDetails.value = i. details
        })
        .catch(e => {
            console.log(e)
        })
    }

    const closeView = () => {
        viewPDF.value = false
    }

    const searchForm = useForm({
        search: props.refsearch,
        category_id: props.refcategory,
        page: 1
    })

    const search = () => {
        searchForm.page = 1
        axios.post(route('reference.search'), searchForm)
        .then(({data}) => {
            reference.value = data
        })
        .catch(e => {

        })
    }

    const searchFileOnScroll = () => {
        axios.post(route('reference.search'), searchForm)
        .then(({data}) => {
            if(data.data.length == 0){
                searchForm.page = searchForm.page - 1
            }
            data.data.forEach(e => {
                reference.value.data.push(e)
            })
        })
        .catch(e => {

        })
    }

    const onScroll = _.debounce(({ target: { scrollTop, clientHeight, scrollHeight }}) => {
        if (scrollTop + clientHeight >= scrollHeight) {
            searchForm.page = searchForm.page + 1
            searchFileOnScroll()
        }
    }, 300)

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
                                    <PrimaryButton class="float-right" @click="openInputForm">Upload</PrimaryButton>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 mb-3 pr-6 pl-5">
                            <form @submit.prevent="search">
                                <div class="flex w-full space-x-2">
                                    <div class="grow">
                                        <div class="grow">
                                            <InputLabel value="Search" for="search" />
                                            <div class="relative rounded-md shadow-sm">
                                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                                    <span class="text-gray-500 sm:text-sm">
                                                        <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512">
                                                            <path d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z"/>
                                                        </svg>
                                                    </span>
                                                </div>

                                                <TextInput id="search" type="search" class="block w-full mt-1 pl-9" v-model="searchForm.search" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="basis-1/3 pt-6">
                                        <select name="cat" @change="search" v-model="searchForm.category_id" id="cat" class="border min-[300px]:w-full rounded text-gray-700 border-gray-300">
                                            <option :value="null" selected>Select Reference Category</option>
                                            <option :value="category.id" v-for="category in props.refrenceCategory">{{ category.title.charAt(0).toUpperCase() + category.title.slice(1) }}</option>
                                        </select>
                                    </div>
                                </div>
                            </form>
                            <div class="basis-full p-2 mb-9 max-h-[50rem] overflow-auto mt-3" @scroll="onScroll">
                                <table class="w-full table-auto">
                                    <thead>
                                        <tr>
                                            <th class="border-b border-slate-300" style="width: 50%;">File Name</th>
                                            <th class="border-b border-slate-300" style="width: 20%;">Date</th>
                                            <th class="border-b border-slate-300" style="width: 20%;">View</th>
                                            <th class="border-b border-slate-300" style="width: 10%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="i in reference.data" class="border-b-2 py-2">
                                            <td class="py-2 text-wrap">
                                                <div class="flex flex-col">
                                                    <div class="">
                                                        {{ i.file_name }}
                                                    </div>
                                                    <div class="text-sm text-gray-600">
                                                        {{i.title}}
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-2 text-wrap text-center">{{i.date}}</td>
                                            <td class="py-2 text-wrap text-center">
                                                <button class="p-1 hover:bg-slate-200 rounded-full" @click="view(i)">
                                                    <EyeIcon class="h-6"/>
                                                </button>
                                            </td>
                                            <td class="py-2 text-wrap text-center"><button class="border-b-2 border-b-indigo-400 hover:border-b-indigo-800" @click="fillForm(i)">Edit</button></td>
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

            <Modal :show="viewPDF" :maxWidth="'4xl'" @close="closeView">
                <div class="p-6">

                    <div class="mt-2">
                        {{ pdfTitle }}
                    </div>

                    <div class="mt-3 border" style="height: 40rem;">
                        <embed :src="'data:application/pdf;base64,'+pdfPath" style="width: 100%; height: 100%;"  type="application/pdf">
                    </div>

                    <div class="mt-4">
                        Details:
                    </div>
                    <div class="mt-1 text-gray-800 p-2 max-h-24 overflow-auto border rounded">
                        {{ pdfDetails }}
                    </div>

                    <div class="mt-6 flex">
                        <SecondaryButton @click="closeView"> Close </SecondaryButton>
                    </div>
                </div>
            </Modal>
        </div>
    </SettingsLayout>
</template>
