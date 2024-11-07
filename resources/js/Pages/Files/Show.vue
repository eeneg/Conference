<script setup>
    import { Head } from '@inertiajs/vue3';
    import FileVersioncontrol from '@/Components/FileVersionControl/FileVersionControl.vue'
    import Modal from '@/Components/Modal.vue';
    import TextInput from '@/Components/TextInput.vue';
    import SecondaryButton from '@/Components/SecondaryButton.vue';
    import ComboBox from '@/Components/ComboBox.vue';
    import DangerButton from '@/Components/DangerButton.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import InputError from '@/Components/InputError.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import Dropdown from '@/Components/Dropdown.vue';
    import DropdownLink from '@/Components/DropdownLink.vue';
    import FileComments from '@/Components/FileComments.vue';
    import { ArrowDownTrayIcon, DocumentIcon, EllipsisVerticalIcon, EyeIcon, CheckIcon } from '@heroicons/vue/20/solid';
    import { useForm } from '@inertiajs/vue3';
    import { ref } from 'vue';
    import { router } from '@inertiajs/vue3'
    import axios from 'axios';
    import _ from 'lodash';

    const props = defineProps({files: Object, storage: Object, category: Object, for_review:Object})

    const files = ref(props.files)

    const combobox = ref([])

    const search_button_text = ref("SEARCH")

    const path = ref(null)

    const loading = ref(false)

    const pdfModalShow = ref(false)

    const responseModal = ref(false)
    var header = ""
    var success = true
    var message = ""

    const closeResModal = () => {
        responseModal.value = false
    }

    const form = useForm({
        search: props.search,
        storage: null,
        category: [],
        page: 1
    })

    const getCategoryId = (id) => {
        form.category = id.value.map(e => e.id)
    }

    const search = () => {
        search_button_text.value = "LOADING..."
        form.page = 1
        axios.get(route('file.search',form))
        .then(({data}) => {
            files.value = data
            search_button_text.value = "SEARCH"
        })
        .catch(e => {
            search_button_text.value = "SEARCH"
        })
    }

    const searchFileOnScroll = () => {
        search_button_text.value = "LOADING..."
        axios.get(route('file.search',form))
        .then(({data}) => {
            search_button_text.value = "SEARCH"
            if(data.data.length == 0){
                form.page = form.page - 1
            }
            data.data.forEach(e => {
                files.value.data.push(e)
            })
        })
        .catch(e => {
            search_button_text.value = "SEARCH"
            console.log(e)
        })
    }

    const onScroll = _.debounce(({ target: { scrollTop, clientHeight, scrollHeight }}) => {
        if ((form.search != null || form.storage || form.category != []) && scrollTop + clientHeight >= scrollHeight) {
            form.page = form.page + 1
            searchFileOnScroll()
        }
    }, 300)

    const reset = () => {
        form.reset()
        combobox.value = []
        router.visit(route('file.index'),{
            onStart: e => loading.value = true,
            onSuccess: e => loading.value = false
        })

    }

    const closeModal = () => {
        pdfModalShow.value = false
    }

    const pdfTitle = ref(null)
    const pdfDetails = ref(null)
    const viewFile = (file) => {

        // axios.get(`/viewBlobPDF/${file.id}`)
        // .then((e) => {
        path.value = file.id
        file_id.value = file.id
        review_status.value = file.for_review == true ? false : true
        pdfTitle.value = file.title
        pdfDetails.value = file.details
        pdfModalShow.value = true
        // })
        // .catch(e => {
        //     console.log(e)
        // })
    }

    const renameForm = useForm({
        id: null,
        file_name: null
    })
    const renameFileIndex = ref(0)
    const renameModal = ref(false)
    const openRenameModal = (id, file_name, index) => {
        renameForm.id = id
        renameForm.file_name = file_name
        renameModal.value = true
        renameFileIndex.value = index
    }

    const closeRenameModal = () => {
        renameModal.value = false
    }

    const resetRenameError = () => {
        renameForm.errors.file_name = null
    }

    const submitRename = () => {
        if(renameForm.file_name.match('^[^+]+\.pdf$')){
            renameForm.submit('patch', route('file.rename', {id: renameForm.id}), {
                onSuccess: () => {
                    files.value.data[renameFileIndex.value]['file_name'] = renameForm.file_name
                },
                onError: () => {

                }
            })
        }else{
            renameForm.errors.file_name = "Invalid File Name (add .pdf at the end of the file name)"
        }
    }

    const file_id = ref(null)
    const review_status = ref(false)
    const reviewForm = useForm({
        status: null
    })
    const setFileForReview = () => {
        reviewForm.submit('patch', route('file.review', file_id.value),{
            onSuccess: () => {
                router.visit(route('file.index'), {
                    only: ['files', 'for_review'],
                })
            },
            onError: () => {

            }
        })
    }
    const onStartForReview = () => {
        reviewForm.status = true
        setFileForReview()
    }
    const endForReview = (id) => {
        file_id.value = id
        reviewForm.status = false
        setFileForReview()
    }
    const reloadLatest = (e) => {
        search()
    }

    const confirmingFileDeletion = ref(false)
    const deleteFileForm = useForm({
        password: null,
        file_id: null,
        safe: false
    })
    const deleteFile = (id) => {
        axios.get(route('file.check', id))
        .then(({data}) => {
            deleteFileForm.file_id = id
            if(data == false){
                confirmingFileDeletion.value = true
                deleteFileForm.safe = false
            }else{
                deleteFileForm.safe = true
                deleteForeverModalOpen()
            }
        })
        .catch(e => {

        })
    }
    const closeFileDeleteModal = () => {
        confirmingFileDeletion.value = false
    }
    const deleteFileConfirmed = () => {
        deleteFileForm.submit('delete', route('files.destroy', deleteFileForm.file_id),{
            preserveScroll: true,
            onSuccess: (e) => {
                header = 'Success'
                message = 'File Deleted Successfully'
                success = true
                responseModal.value = true
                confirmingFileDeletion.value = false
                files.value = e.props.files
                closeDeleteForeverModal()
            },
            onError: (e) => {
                console.log(e)
            }
        })
    }

    const deleteForeverModal = ref(false)
    const deleteForeverModalOpen = () => {
        deleteForeverModal.value = true
    }
    const closeDeleteForeverModal = () => {
        deleteForeverModal.value = false
    }
</script>
<template>

    <Head title="Find Files" />
    <header class="bg-white shadow">
        <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Search Files</h2>
            <p class="mt-1 text-sm text-gray-600">
                Searches Files via File Name, Storage, Content and Category (e.g. Ordinances, Resolutions)
            </p>
        </div>
    </header>

    <div class="py-5">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="flex flex-row-reverse mt-3 pl-5 pr-5 space-x-2">
                    <div class="ml-3">
                        <SecondaryButton @click="reset">Reset</SecondaryButton>
                    </div>
                    <div role="status"  v-if="loading">
                        <svg aria-hidden="true" class="w-8 h-8 text-gray-200 animate-spin dark:text-gray-600 fill-indigo-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/><path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/></svg>
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
                <div class="grow">
                    <form @submit.prevent="search">
                        <div class="pr-6 pl-6 pb-1 flex space-x-2">
                            <div class="relative rounded-md shadow-sm w-1/2">
                                <InputLabel value="File Name/Content/Date" for="search" />
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm mt-5">
                                        <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512">
                                            <path d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z"/>
                                        </svg>
                                    </span>
                                </div>
                                <TextInput id="search_file" @keyup.enter="search" type="search" class="w-full pl-9" v-model="form.search"/>
                            </div>
                            <div class="grow">
                                <InputLabel value="Storage"/>
                                <select v-model="form.storage" class="w-full rounded text-gray-700 border-gray-300">
                                    <option :value="storage.id" v-for="storage in props.storage">{{ storage.title }}</option>
                                </select>
                            </div>
                            <div class="grow">
                                <InputLabel value="Category"/>
                                <ComboBox @passData="getCategoryId($event)" :data="props.category"></ComboBox>
                            </div>
                        </div>
                        <div class="flex items-center justify-center pr-6 pl-6 mt-1">
                            <PrimaryButton class="items-center justify-center w-full">
                                <span class="text-gray-500 sm:text-sm mr-2">
                                    <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512">
                                        <path d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z"/>
                                    </svg>
                                </span>
                                {{ search_button_text }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
                <div class="grow pl-5 pr-5 text-sm mt-2 mb-4" v-if="for_review">
                    <div class="mt-2">
                        <h2 class="text-lg font-bold">For Review</h2>
                        <hr>
                    </div>
                    <div class="max-h-80 overflow-auto rounded">
                        <div class="border rounded p-2 pl-2 mt-2 group bg-indigo-100" v-for="(file, i) in for_review">
                            <div class="flex items-center">
                                <div class="flex items-center p-1 justify-center sm:text-sm">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-300 text-red-900">
                                        <DocumentIcon class="w-5 h-5 stroke-gray-900 fill-none " aria-hidden="true" />
                                    </div>
                                </div>
                                <div class="grow p-1 text-sm">
                                    <div class="flex ml-2">
                                        <p class="text-lg max-w-96 truncate float-left text-black-900">{{ file.file_name }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-center float-right space-x-1">
                                    <div class="hidden group-hover:block">
                                        <FileComments :file_id="file.id"/>
                                    </div>
                                    <div class="hidden group-hover:block">
                                        <FileVersioncontrol @refreshData="reloadLatest($event)" :file_id="file.id"/>
                                    </div>
                                    <a class="hidden group-hover:block" :href="route('file.download',{ id:file.id })">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-400 hover:bg-indigo-500 text-red-900">
                                            <ArrowDownTrayIcon class="w-5 h-5 stroke-gray-900 fill-black " aria-hidden="true" />
                                        </div>
                                    </a>
                                    <button class="hidden group-hover:block" @click="viewFile(file)">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-400 hover:bg-indigo-500 text-black-900">
                                            <EyeIcon class="w-5 h-5 fill-black aria-hidden" aria-hidden="true" />
                                        </div>
                                    </button>
                                    <button class="" @click="endForReview(file.id)">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-400 hover:bg-indigo-500 text-black-900">
                                            <CheckIcon class="w-5 h-5 fill-black aria-hidden" aria-hidden="true" />
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grow pl-5 pr-5 pb-40 text-sm min-h-20 max-h-screen overflow-auto" @scroll="onScroll" group="file">
                    <div>
                        <h2 class="text-lg font-bold">Files</h2>
                        <hr>
                    </div>
                    <div class="border rounded pr-2 pl-2 mt-2 group" v-for="(file, i) in files.data">
                        <div class="flex">
                            <div>
                                <img :src="'data:image/png;base64,'+file.thumbnail?.base64_thumbnail" alt="">
                            </div>
                            <div class="flex items-center p-1 justify-center sm:text-sm">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-300 text-red-900">
                                    <DocumentIcon class="w-5 h-5 stroke-gray-900 fill-none " aria-hidden="true" />
                                </div>
                            </div>
                            <div class="grow p-1 text-sm">
                                <div class="flex ml-2">
                                    <p class="text-lg max-w-96 truncate float-left text-indigo-800">{{ file.file_name }}</p>
                                </div>
                                <div class="flex ml-2">
                                    <p class="text-md truncate first-letter:uppercase text-gray-500">
                                        {{
                                            file.category.map(e => e.title.charAt(0).toUpperCase() + e.title.slice(1)).join(', ')
                                        }}
                                    </p>
                                </div>
                                <div class="flex ml-2">
                                    <p class="text-md truncate first-letter:uppercase text-gray-500">{{ file.storage.title }}</p>
                                </div>
                            </div>
                            <div class="mt-2">
                                <div class="flex items-center justify-center float-right space-x-1">
                                    <div class="hidden group-hover:block">
                                        <FileComments :file_id="file.id"/>
                                    </div>
                                    <div class="hidden group-hover:block">
                                        <FileVersioncontrol @refreshData="reloadLatest($event)" :file_id="file.id"/>
                                    </div>
                                    <a class="hidden group-hover:block" :href="route('file.download',{ id:file.id })">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-400 hover:bg-indigo-500 text-red-900">
                                            <ArrowDownTrayIcon class="w-5 h-5 stroke-gray-900 fill-black " aria-hidden="true" />
                                        </div>
                                    </a>
                                    <button class="hidden group-hover:block" @click="viewFile(file)">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-400 hover:bg-indigo-500 text-black-900">
                                            <EyeIcon class="w-5 h-5 fill-black aria-hidden" aria-hidden="true" />
                                        </div>
                                    </button>

                                    <Dropdown class="absolute" align="right" width="48">
                                        <template #trigger>
                                            <button>
                                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-400 hover:bg-indigo-500 text-black-900">
                                                    <EllipsisVerticalIcon class="w-5 h-5 fill-black aria-hidden" aria-hidden="true" />
                                                </div>
                                            </button>
                                        </template>

                                        <template #content>
                                            <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownMenuIconButton">
                                                <li>
                                                    <DropdownLink :href="route('files.edit', {id: file.id})"> Edit </DropdownLink>
                                                </li>
                                                <li>
                                                    <div class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out cursor-pointer" @click="openRenameModal(file.id, file.file_name, i)">Rename</div>
                                                </li>
                                                <li>
                                                    <div class="block w-full px-4 py-2 text-left text-sm leading-5 text-red-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out cursor-pointer" @click="deleteFile(file.id)">Delete</div>
                                                </li>
                                            </ul>
                                        </template>
                                    </Dropdown>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <Modal :show="pdfModalShow" :maxWidth="'4xl'" @close="closeModal">
        <div class="p-6">
            <div class="mt-4">
                {{ pdfTitle }}
            </div>

            <div class="mt-2" style="height: 40rem;">
                <iframe :src="`/viewBlobPDF/`+path" style="width: 100%; height: 100%;"  type="application/pdf"></iframe>
            </div>

            <div class="mt-4">
                Details:
            </div>
            <div class="mt-1 text-gray-800 p-2 max-h-24 overflow-auto border rounded">
                {{ pdfDetails }}
            </div>

            <div class="mt-6 flex justify-between ">
                <PrimaryButton @click="onStartForReview()">For Review</PrimaryButton>
                <SecondaryButton @click="closeModal"> Close </SecondaryButton>
            </div>
        </div>
    </Modal>

    <Modal :show="responseModal">
        <div class="p-6">
            <h2 :class="{'text-lg font-medium text-green-500': success == true, 'text-lg font-medium text-red-500': success == false}">
                {{ header }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{message}}
            </p>


            <SecondaryButton
                class="w-full mt-2 place-content-center bg-red-400"
                @click="closeResModal">
                            <p>OK</p>
            </SecondaryButton>
        </div>
    </Modal>

    <Modal :show="renameModal" @close="closeRenameModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Rename File
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Click submit to rename file
            </p>

            <div class="flex flex-col mt-4">
                <div class="basis-full">
                    <InputLabel for="file_name">File Name</InputLabel>
                    <TextInput @input="resetRenameError()" name="file_name" id="file_name" class="w-full" v-model="renameForm.file_name"/>
                    <InputError :message="renameForm.errors.file_name" class="mt-2" />
                    <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition ease-in-out float-right">
                        <p v-if="renameForm.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                    </Transition>
                </div>
            </div>

            <div class="mt-6 flex space-x-2 justify-end">
                <SecondaryButton @click="closeRenameModal"> Cancel </SecondaryButton>
                <PrimaryButton @click="submitRename">Save</PrimaryButton>
            </div>
        </div>
    </Modal>

    <Modal :show="deleteForeverModal" @close="closeDeleteForeverModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Delete File Forever!
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                This file will be deleted forever and you wont be able to restore it.
            </p>

            <div class="mt-6 flex space-x-2 justify-end">
                <SecondaryButton @click="closeDeleteForeverModal()"> Cancel </SecondaryButton>
                <DangerButton @click="deleteFileConfirmed()">Delete Forever</DangerButton>
            </div>
        </div>
    </Modal>

    <Modal :show="confirmingFileDeletion" @close="closeFileDeleteModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                This file is from a previous conference and has older versions. <br>
                Are you sure you want to delete it?
            </h2>
            <br>
            <p class="mt-1 text-sm text-gray-600">
                Once deleted, all resources and file versions will be permanently removed and no longer viewable in the conference view.
                Please enter your password to confirm deletion.
            </p>

            <div class="mt-6">
                <InputLabel for="password" value="Password" class="sr-only" />

                <TextInput
                    id="password"
                    ref="passwordInput"
                    v-model="deleteFileForm.password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="Password"
                    @keyup.enter="deleteFileConfirmed"
                />

                <InputError :message="deleteFileForm.errors.password" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="closeFileDeleteModal"> Cancel </SecondaryButton>

                <DangerButton
                    class="ml-3"
                    :class="{ 'opacity-25': deleteFileForm.processing }"
                    :disabled="deleteFileForm.processing"
                    @click="deleteFileConfirmed"
                >
                    Delete File
                </DangerButton>
            </div>
        </div>
    </Modal>

</template>
