<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import { useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue'
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import { ref } from 'vue';
import axios, { all } from 'axios';
import { XCircleIcon } from '@heroicons/vue/20/solid';
import Combobox  from '@/Components/ComboBox.vue';

const props = defineProps({file: Object, storage: Object, category: Object})

var header = ""
var message = ""
var success = true

const modalShow = ref(false)

const form = useForm({
    storage_id: null,
    category_id: [],
    files: null
})

const errors = ref({files: null, storage_id: null})

const duplicates = ref([])

const processFiles = (e) => {
    form.files = e.target.files
}

const resetDuplicateFileList = () => {
    duplicates.value = []
}

const duplicateCheck = () => {
    axios.post(route('upload.check'), form, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    }).then((response) => {
        duplicates.value = response.data
        console.log(duplicates.value)
        if(duplicates.value.length == 0){
            uploadFiles()
        }
    }).catch((error) => {
        errors.value.files = error.response.data.errors.files ? error.response.data.errors.files[0] : null
        errors.value.storage_id = error.response.data.errors.storage_id ? error.response.data.errors.storage_id[0] : null
    })
}

const uploadFiles = () => {
    form.post(route('multipleFileUpload.store'), {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    }).then((response) => {
        modalShow.value = true
        header = "Success!"
        success = true
        form.reset()
        message = "Files Uploaded Successfuly"
        form.reset()
    }).catch((error) => {
        console.log(error)
    })
}

const getCategoryId = (id) => {
    form.category_id = id
}

const removeCategoryId = (category) => {
    form.category_id.splice(category, 1)
}


</script>

<template>
    <div class="mb-3 pr-6 pl-5 space-y-6">
        <div>
            <InputLabel>Multiple Files</InputLabel>
            <input class="mb-3" type="file" @change="processFiles($event)" @click="resetDuplicateFileList()" multiple accept="application/pdf">
            <InputError :message="errors.files" class="mt-2" />
        </div>
        <div class="">
            <InputLabel>Storage Location</InputLabel>
            <select v-model="form.storage_id" name="storage_id" id="storage_id" class="border w-full rounded text-gray-700 border-gray-300">
                <option :value="null" selected>---</option>
                <option :value="storage.id" v-for="storage in props.storage">{{ storage.title.charAt(0).toUpperCase() + storage.title.slice(1) }}</option>
            </select>
            <InputError :message="errors.storage_id" class="mt-2" />
        </div>
        <div class="flex flex-col">
            <div class="flex flex-row justify-between">
                <InputLabel>Category (e.g. Ordinances, Resolutions)</InputLabel>
                <p style="line-height: 2; font-size: 11px;" class="float-right">
                    Not Enough Categories?
                    <a class="text-indigo-900 underline" style="font-size: 11px;" :href="'/settings/category'" :active="route().current('files.*')">
                        Add Here
                    </a>
                </p>
            </div>
            <div class="flex flex-row space-x-2">
                <div class="w-full">
                    <Combobox @passData ="getCategoryId($event)" :data="props.category"></Combobox>
                </div>
            </div>
            <InputError :message="form.errors.category_id" class="mt-2" />
            <div class="flex flex-nowrap overflow-x-auto p-2 space-x-2 w-full">
                <div v-for="(cat, i) in form.category_id" class="flex flex-shrink-0 rounded-full space-x-2 px-3 py-1 text-white bg-indigo-500 mt-2">
                    <span>{{cat.title}}</span>
                    <button class="rounded-full p-0" @click="removeCategoryId(i)"><XCircleIcon class="h-5 rounded-full hover:bg-indigo-900"></XCircleIcon></button>
                </div>
            </div>
        </div>
        <div class="">
            <PrimaryButton class="" @click="duplicateCheck">Upload</PrimaryButton>
        </div>
    </div>
    <div class="mt-3 mb-3 pr-6 pl-5">
        <div class="text-lg text-red-600 p-0" v-if="duplicates.length > 0">
            <div>
                Files Already Uploaded!
            </div>
            <div>
                <small>Please remove the following files from the list and try again.</small>
            </div>
        </div>
        <div class="overflow-auto max-h-96">
            <div class="border rounded p-2 mb-1" v-for="files in duplicates">
                {{ files.file_name }}
            </div>
        </div>
    </div>

    <Modal :show="modalShow">
        <div class="p-6">
            <h2 :class="{'text-lg font-medium text-green-500': success == true, 'text-lg font-medium text-red-500': success == false}">
                {{ header }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{message}}
            </p>

            <div class="flex">
                <div class="flex basis-full mt-2 space-x-2" v-if="editMode">
                    <SecondaryButton
                        class="w-full mt-2 place-content-center"
                        @click="redirectBack()">
                            <p>Back</p>
                    </SecondaryButton>
                    <PrimaryButton
                        class="w-full mt-2 place-content-center"
                        @click="closeModal">
                            <p>OK</p>
                    </PrimaryButton>
                </div>
                <div class="basis-full mt-2" v-else="editMode == false">
                    <PrimaryButton
                        class="w-full mt-2 place-content-center"
                        @click="closeModal">
                            <p>OK</p>
                    </PrimaryButton>
                </div>
            </div>
        </div>
    </Modal>
</template>
