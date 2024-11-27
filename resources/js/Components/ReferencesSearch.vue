<script setup>
import Modal from './Modal.vue';
import { useForm } from '@inertiajs/vue3';
import InputLabel from './InputLabel.vue';
import SecondaryButton from './SecondaryButton.vue';
import Pagination from './Pagination.vue';
import TextInput from './TextInput.vue';
import { onMounted, ref } from 'vue';
import { EyeIcon, PaperClipIcon } from '@heroicons/vue/20/solid';
import axios from 'axios';
import _ from 'lodash';

const props = defineProps({reference_category:Object})

const references = ref({})

const form = useForm({
    search: null,
    category_id: null,
    page: 1
})

const search = () => {
    form.page = 1
    axios.post(route('reference.search'), form)
    .then(({data}) => {
        references.value = data
        document.getElementById('search').blur()
    })
    .catch(e => {
        console.log(e)
    })
}

const searchFileOnScroll = () => {
    axios.post(route('reference.search'), form)
    .then(({data}) => {
        if(data.data.length == 0){
            form.page = form.page - 1
        }
        data.data.forEach(e => {
            references.value.data.push(e)
        })
    })
    .catch(e => {

    })
}

const onScroll = _.debounce(({ target: { scrollTop, clientHeight, scrollHeight }}) => {
    if (scrollTop + clientHeight >= scrollHeight) {
        form.page = form.page + 1
        searchFileOnScroll()
    }
}, 300)

const pdfModalShow = ref(false)
const path = ref(null)
const viewFile = (id) => {
    path.value = id
    pdfModalShow.value = true
}
const pdfModalShowClose = () => {
    pdfModalShow.value = false
}

onMounted(() => {
    search()
})
</script>

<template>
    <div class="w-full">
        <div class="basis-full">
            <form @submit.prevent="search" class="flex flex-row mt-1 space-y-6 space-x-2">
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

                                <TextInput id="search" type="search" class="block w-full mt-1 pl-9" v-model="form.search" />
                            </div>
                        </div>
                    </div>
                    <div class="basis-1/3 pt-6">
                        <select name="cat" @change="search" v-model="form.category_id" id="cat" class="border min-[300px]:w-full rounded text-gray-700 border-gray-300">
                            <option :value="null" selected>Select Reference Category</option>
                            <option :value="category.id" v-for="category in props.reference_category">{{ category.title.charAt(0).toUpperCase() + category.title.slice(1) }}</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <div class="w-full mt-4 max-h-[40rem] overflow-auto" @scroll="onScroll">
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
                    <div @click="viewFile(ref.id)" class="h-8 w-8 flex items-center justify-center rounded-full bg-indigo-400 hover:bg-indigo-500 text-black-900 cursor-pointer">
                        <EyeIcon class="w-4 h-4 fill-black " aria-hidden="true"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <Modal :show="pdfModalShow" :maxWidth="'4xl'" @close="pdfModalShowClose">
        <div class="p-6">

            <div class="mt-2 flex items-center justify-center" style="height: 40rem;">
                <iframe class="z-50" :src="`/viewReferenceBlobPDF/`+path" style="width: 100%; height: 100%;"  type="application/pdf"></iframe>
                <div class="absolute flex flex-col items-center justify-center">
                    <div>
                        <svg aria-hidden="true" class="w-8 h-8 text-gray-200 animate-spin dark:text-gray-600 fill-indigo-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/><path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/></svg>
                    </div>
                    <div>
                        Loading...
                    </div>
                </div>
            </div>

            <div class="mt-6 flex">
                <SecondaryButton @click="pdfModalShowClose"> Close </SecondaryButton>
            </div>
        </div>
    </Modal>
</template>
