<script setup>
import Modal from './Modal.vue';
import { useForm } from '@inertiajs/vue3';
import InputLabel from './InputLabel.vue';
import SecondaryButton from './SecondaryButton.vue';
import Pagination from './Pagination.vue';
import TextInput from './TextInput.vue';
import { ref } from 'vue';
import { EyeIcon, PaperClipIcon } from '@heroicons/vue/20/solid';
import axios from 'axios';


const references = ref({})

const form = useForm({
    search: null
})

const search = () => {
    axios.post(route('reference.search'), form)
    .then(({data}) => {
        references.value = data
        document.getElementById('search').blur()
    })
    .catch(e => {
        console.log(e)
    })
}

const pdfModalShow = ref(false)
const path = ref(null)
const viewFile = (file_path) => {
    console.log(file_path)
    path.value = file_path
    pdfModalShow.value = true
}
const pdfModalShowClose = () => {
    pdfModalShow.value = false
}
</script>

<template>
    <div class="w-full">
        <div class="basis-full">
            <form @submit.prevent="search" class="flex flex-row mt-1 space-y-6">
                <div class="grow">
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
        </div>
        <div class="w-full mt-4">
            <div class="flex flex-row rounded p-1" :class="{'bg-gray-200':i % 2 ===0}" v-for="(ref, i) in references.data">
                <div class="flex items-center justify-center">
                    <div class="h-8 w-8 flex items-center justify-center rounded-full bg-indigo-300 hover:bg-indigo-400 text-black-900">
                        <PaperClipIcon class="w-4 h-4 fill-black " aria-hidden="true" />
                    </div>
                </div>
                <div class="flex basis-1/2">
                    <div class="ml-2 pr-5">
                        <div>
                            <p class="text-lg float-left">{{ref.file_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400 float-left" :class="{'text-gray-600':i % 2 ===0}">{{ref.details}}</p>
                        </div>
                    </div>
                </div>
                <div class="flex justify-center items-center basis-1/2">
                    {{ ref.title }}
                </div>
                <div class="flex justify-center items-center basis-1/2">
                    {{ref.date}}
                </div>
                <div class="flex items-center justify-center float-right mr-1 space-x-1">
                    <div @click="viewFile(ref.path)" class="h-8 w-8 flex items-center justify-center rounded-full bg-indigo-400 hover:bg-indigo-500 text-black-900 cursor-pointer">
                        <EyeIcon class="w-4 h-4 fill-black " aria-hidden="true"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <Modal :show="pdfModalShow" :maxWidth="'4xl'" @close="pdfModalShowClose">
        <div class="p-6">

            <div class="mt-6 h-64" style="height: 50rem;">
                <embed :src="'/storage/'+path" style="width: 100%; height: 100%;"  type="application/pdf">
            </div>

            <div class="mt-6 flex">
                <SecondaryButton @click="pdfModalShowClose"> Close </SecondaryButton>
            </div>
        </div>
    </Modal>
</template>
