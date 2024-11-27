<script setup>
import Modal from '../Modal.vue'
import PrimaryButton from '../PrimaryButton.vue';
import SecondaryButton from '../SecondaryButton.vue';
import InputLabel from '../InputLabel.vue';
import TextInput from '../TextInput.vue';
import InputError from '../InputError.vue';
import Dropdown from '../Dropdown.vue';
import FileComments from '../FileComments.vue';
import { BarsArrowUpIcon, CheckIcon, PaperClipIcon } from '@heroicons/vue/20/solid';
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({file_id:String, files:Object, for_review:Boolean})

const emit = defineEmits(['refreshData'])

const showFileVersions = ref(false)
const openFileVerionsModal = () => {
    showFileVersions.value = true
    getFileVersions()
}
const closeFileVersionModal = () => {
    showFileVersions.value = false
}

const form = useForm({
    'file_id':props.file_id,
    'latest': false,
    'title': null,
    'file': null
})
const getFile = (e) => {
    form.file = e.target.files[0]
}
const submitFileVersion = () => {
    form.submit('post', route('fileVersion.store'),{
        onSuccess: () => {
            form.reset()
            getFileVersions()
            emit('refreshData')
        },
        onError: (e) => {
            console.log(e)
        }
    })
}

const fileVersions = ref({})
const loading = ref(false)
const getFileVersions = () => {
    loading.value = true
    axios.get(route('fileVersion.index', {id:props.file_id}))
    .then(({data}) => {
        fileVersions.value = data
        loading.value = false
        form.file_id = data.latest[0].id
    })
    .catch(e => {
        console.log(e)
        loading.value = false
    })
}

const setLatest = (id) => {
    axios.patch(route('fileVersion.update', {id:id}))
    .then(e => {
        loading.value = true
        axios.get(route('fileVersion.index', {id:id}))
        .then(({data}) => {
            fileVersions.value = data
            loading.value = false
        })
        .catch(e => {
            console.log(e)
            loading.value = false
        })
        emit('refreshData')
    })
    .catch(e => {
        console.log(e)
    })
}

const deleteFileVersion = (id) => {
    axios.delete(route('fileVersion.destroy', {id:id}))
    .then(e => {
        getFileVersions()
    })
    .catch(e => {
        console.log(e)
    })
}

const path = ref(null)

const pdfModalShow = ref(false)
const pdfModal = (id) => {
    axios.get(`viewBlobPDF/${id}`)
    .then(e => {
        path.value = e.data
        pdfModalShow.value = true
    })
    .catch(e => {
        console.log(e)
    })
}
const pdfModalShowClose = () => {
    pdfModalShow.value = false
    path.value = false
}

</script>

<template>
   <div>
        <button @click="openFileVerionsModal">
            <div v-if="!props.for_review" class="block w-full px-4 py-1 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out cursor-pointer">
                <p>File Versions</p>
            </div>
            <div v-else class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-400 hover:bg-indigo-500 text-black-900">
                <BarsArrowUpIcon class="w-5 h-5 fill-white aria-hidden" aria-hidden="true" />
            </div>
        </button>

        <Modal :show="showFileVersions">
            <div class="flex flex-col">
                <div class="p-6">
                    <h2 class="text-lg font-medium">
                        File Versions
                    </h2>

                    <p class="mt-1 text-sm text-gray-600">
                        View and Update File Versions
                    </p>
                </div>

                <div class="pl-6 pr-6">
                    <div class="flex flex-col">
                        <div class="">
                            <InputLabel>Upload new file version</InputLabel>
                            <input type="file" v-on:change="getFile($event)" class="mt-1" accept="application/pdf"/>
                            <InputError :message="form.errors.file"/>
                        </div>
                        <div class="mt-4 flex flex-col border-">
                            <InputLabel for="title">File Title</InputLabel>
                            <TextInput id="title" type="search" class="" v-model="form.title"/>
                            <InputError :message="form.errors.title"/>
                        </div>
                        <div class="mt-4 flex justify-self-center">
                            <InputLabel for="latest">Set File as Latest Version?</InputLabel>
                            <input type="checkbox" id="latest" name="latest" v-model="form.latest" class="ml-3 mr-1" accept="application/pdf"/>
                            <InputLabel for="latest">Yes</InputLabel>
                        </div>
                        <div class="mt-4">
                            <PrimaryButton @click="submitFileVersion()" :disabled="form.processing">Upload</PrimaryButton>
                            <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition ease-in-out">
                                <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                            </Transition>
                        </div>
                    </div>
                </div>

                <div class="pl-6 pr-6">
                    <div class="flex flex-col mt-4 mb-10 mt-5">
                        <div class="flex py-2 pl-1 border rounded mt-1 justify-center" v-if="loading">
                            <svg aria-hidden="true" class="w-8 h-8 text-gray-200 animate-spin dark:text-gray-600 fill-indigo-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/><path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/></svg>
                            <span class="sr-only">Loading...</span>
                        </div>
                        <div>
                            <h2 class="text-sm font-medium">
                                Latest
                            </h2>
                        </div>
                        <div class="py-2 pl-1 border rounded mt-1">
                            <div class="flex rounded" v-for="file in fileVersions.latest">
                                <div class="flex items-center justify-center">
                                    <div class="h-8 w-8 flex items-center justify-center rounded-full bg-indigo-400 text-black-900">
                                        <PaperClipIcon class="w-4 h-4 fill-black " aria-hidden="true" />
                                    </div>
                                </div>
                                <div class="flex items-center grow">
                                    <div class="flex flex-col ml-2">
                                        <div>
                                            <p class="text-lg w-60 truncate float-left">{{file.file_name}}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm w-60 truncate text-gray-400 float-left">{{file.title}}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center grow">
                                    {{file.date}}
                                </div>
                                <div class="flex items-center justify-center float-right mr-1 space-x-1">
                                    <Dropdown align="right" width="48">
                                        <template #trigger>
                                            <span class="inline-flex rounded-md">
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border border-transparent rounded-md hover:text-gray-700 focus:outline-none"
                                                >
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
                                            <div
                                                class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
                                                @click="pdfModal(file.id)"
                                                >

                                                View
                                            </div>
                                        </template>
                                    </Dropdown>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <h2 class="text-sm font-medium">
                                Old File Versions
                            </h2>
                        </div>
                        <div class="flex py-2 pl-1 border rounded mt-1" v-for="file in fileVersions.nonLatest">
                            <div class="flex items-center justify-center">
                                <div class="h-8 w-8 flex items-center justify-center rounded-full bg-indigo-300 hover:bg-indigo-400 text-black-900">
                                    <PaperClipIcon class="w-4 h-4 fill-black " aria-hidden="true" />
                                </div>
                            </div>
                            <div class="flex items-center grow">
                                <div class="flex flex-col ml-2">
                                    <div>
                                        <p class="text-lg w-60 truncate float-left">{{file.file_name}}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm w-60 truncate text-gray-400 float-left">{{file.title}}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center grow">
                                {{file.date}}
                            </div>
                            <div class="flex items-center justify-center float-right mr-1 space-x-1">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border border-transparent rounded-md hover:text-gray-700 focus:outline-none"
                                            >
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
                                        <div
                                            class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
                                            @click="pdfModal(file.id)"
                                            >

                                            View
                                        </div>
                                        <div
                                            class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
                                            v-if="file.latest == false"
                                            @click="setLatest(file.id)"
                                            >
                                            Set Latest
                                        </div>
                                        <div
                                            class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
                                            v-if="file.latest == false"
                                            @click="deleteFileVersion(file.id)"
                                            >
                                            Delete
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-6 pl-6 pb-3">
                    <SecondaryButton
                        class="mt-2 place-content-center bg-red-400"
                        @click="closeFileVersionModal">
                            <p>Close</p>
                    </SecondaryButton>
                </div>
            </div>

        </Modal>
        <Modal :show="pdfModalShow" :maxWidth="'4xl'" @close="pdfModalShowClose">
            <div class="p-6">

                <div class="mt-6 h-64" style="height: 50rem;">
                    <embed :src="'data:application/pdf;base64,'+path" style="width: 100%; height: 100%;"  type="application/pdf">
                </div>

                <div class="mt-6 flex justify-between">
                    <div>
                        <SecondaryButton @click="pdfModalShowClose"> Close </SecondaryButton>
                    </div>
                    <div class="flex items-center space-x-2">
                        <p>Comments</p>
                        <FileComments :file_id="props.file_id"/>
                    </div>
                </div>
            </div>
        </Modal>
   </div>
</template>
