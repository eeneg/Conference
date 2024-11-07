<script setup>
    import { Head, router, useForm } from '@inertiajs/vue3';
    import TextInput from '@/Components/TextInput.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import Combobox  from '@/Components/ComboBox.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import { XCircleIcon } from '@heroicons/vue/20/solid';
    import {ref} from  'vue';
    import SecondaryButton from '@/Components/SecondaryButton.vue'
    import Modal from '@/Components/Modal.vue';
    import InputError from '@/Components/InputError.vue';
    import { onMounted } from 'vue';

    const props = defineProps({storage:Object, category:Object, file: Object})

    const editMode = ref(false)

    const form = useForm({
        id: null,
        file: {},
        title: null,
        storage_id: null,
        category_id: [],
        date: null,
        details: null,
        sorted: true,
    })

    const submitErrorMsg = ref('')
    var fileNames = []

    var header = ""
    var message = ""
    var success = true

    const modalShow = ref(false)

    const submit = () => {
        form.submit('post', route('files.store'),{
            onSuccess: () =>{
                header = "Success!"
                success = true
                message = "File Uploaded Successfuly"
                modalShow.value = true
                // form.reset()
                submitErrorMsg.value = ''
                document.getElementById('files').value = ''
            },
            onError: (e) => {
                header = "Error!"
                success = false
                message = "Something went wrong"
                modalShow.value = true
                console.log(e)
            }
        })
    }

    const getCategoryId = (id) => {
        form.category_id = id
    }

    const removeCategoryId = (category) => {
        form.category_id.splice(category, 1)
    }

    const update = () => {
        form.submit('patch', route('files.update', {id:form.id}),{
            onSuccess: () =>{
                header = "Success!"
                success = true
                message = "File Updated"
                modalShow.value = true
                submitErrorMsg.value = ''
            },
            onError: (e) => {
                header = "Error!"
                success = false
                message = "Something went wrong"
                modalShow.value = true
                console.log(e)
            }
        })
    }

    const deleteFile = () => {
        form.submit('delete', route('files.destroy'),{
            onSuccess: () =>{

            },
            onError: () => {

            }
        })
    }

    const fileCheck = () => {
        if(editMode.value){
            update()
        }else{
            submit()
        }
    }

    const closeModal = () => {
        modalShow.value = false
    }

    const getFiles = (e, i) => {
        form.file = e.target.files[0]
        fileNames = []
        submitErrorMsg.value = ''
        Array.from(e.target.files).forEach(element => {
            fileNames.push(element.name)
        });
    }

    const editData = () => {
        if(props.file){
            return props.file.category
        }
    }

    const redirectBack = () => {
        router.visit(route('file.index'))
    }

    onMounted(() => {
        if(props.file != null){
            editMode.value = true
            form.category_id = props.file.category
            Object.assign(form, props.file)
        }
    })

</script>
<template>
    <div class="mt-3 mb-3 pr-6 pl-5">
        <div class="space-y-6">
            <div class="" v-if="editMode == false">
                <InputLabel>Upload a File</InputLabel>
                <input v-on:change="getFiles($event, i)" type="file" id="files" class="rounded bg-white-200" accept="application/pdf"   />
                <InputError :message="form.errors.file" class="mt-2"/>
                <InputError :message="submitErrorMsg" class="mt-2"/>
            </div>
            <div class="">
                <InputLabel>Title</InputLabel>
                <TextInput type="text" id="title" v-model="form.title" class="w-full" placeholder="Insert Title"/>
                <InputError :message="form.errors.title" class="mt-2"/>
            </div>
            <div class="flex flex-col">
                <div class="flex flex-row justify-between">
                    <InputLabel>Storage Location</InputLabel>
                    <p style="line-height: 2; font-size: 11px;" class="float-right">
                        Not Enough Storage Locations?
                        <a class="text-indigo-900 underline" style="font-size: 11px;" :href="'/settings/storage'" :active="route().current('files.*')">
                            Add Here
                        </a>
                    </p>
                </div>
                <div>
                    <select v-model="form.storage_id" name="storage_id" id="storage_id" class="border w-full rounded text-gray-700 border-gray-300">
                        <option :value="null" selected>---</option>
                        <option :value="storage.id" v-for="storage in props.storage">{{ storage.title.charAt(0).toUpperCase() + storage.title.slice(1) }}</option>
                    </select>
                </div>
                <InputError :message="form.errors.storage_id" class="mt-2" />
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
                        <Combobox @passData ="getCategoryId($event)" :selected="editData()" :data="props.category"></Combobox>
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
                <InputLabel>Date</InputLabel>
                <input type="date" id="date" v-model="form.date" class="rounded border-gray-300 w-full"/>
                <InputError :message="form.errors.date" class="mt-2" />
            </div>
            <div class="">
                <InputLabel>Details</InputLabel>
                <textarea v-model="form.details" class="w-full rounded border-gray-300 h-24" type="text" placeholder="Insert Details"></textarea>
                <InputError :message="form.errors.details" class="mt-2" />
            </div>
            <div class="">
                <PrimaryButton @click="fileCheck">Upload</PrimaryButton>
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
