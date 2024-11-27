<script setup>
    import TextInput from '@/Components/TextInput.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import InputError from '@/Components/InputError.vue';
    import { QuillEditor } from '@vueup/vue-quill';
    import '@vueup/vue-quill/dist/vue-quill.snow.css';
    import draggable from 'vuedraggable'
    import { useForm } from '@inertiajs/vue3';
    import { XCircleIcon,EyeIcon } from '@heroicons/vue/20/solid';
    import Modal from '@/Components/Modal.vue';
    import {nextTick, ref} from 'vue';
    import DangerButton from '@/Components/DangerButton.vue';
    import _ from 'lodash';
    import SecondaryButton from '@/Components/SecondaryButton.vue';

    const props = defineProps({conf: Object, edit: Boolean, storage:Object})

    const modalShow = ref(false)
    const confirmingConferenceDeletion = ref(false);
    const passwordInput = ref(null);

    var toolbarOptions = [
        ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
        ['blockquote', 'code-block', 'image'],

        [{ 'header': 1 }, { 'header': 2 }],               // custom button values
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
        [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
        [{ 'direction': 'rtl' }],                         // text direction

        [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

        [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
        [{ 'font': [] }],
        [{ 'align': [] }],

        ['clean']                                         // remove formatting button
    ];


    var header = ""
    var message = ""

    var form = useForm({
        title: '',
        agenda: '',
        date: '',
        status: 'pending',
        video_conf_url: null,
        attachments: []
    })

    const deleteForm = useForm({
        id: null,
        password: null
    })

    if(props.edit){
        Object.assign(form, JSON.parse(JSON.stringify(props.conf)))
    }

    const emit = defineEmits(['passData', 'deleteConf'])

    var category = ''

    var categories = []

    const submitData = (e) => {

        var checkFile = []

        form.attachments.forEach(function(e){
            checkFile.push(e.files.length > 0 ? 1 : 0)
        })

        if(checkFile.includes(0)){
            alert("Category Cannot be Empty")
        }else{
            emit('passData', form);
        }

        category = ''
        categories = []
    }

    const addCategory = (cat) => {
        var order = categories.length
        if (cat && !categories.includes(cat)) {
            form.attachments.push({category: cat, category_order: order, files:[]})
            categories.push(cat)
            category = ''
        }else if(categories.includes(cat)){
            header = "Error!"
            message = "Duplicate Category Title"
            modalShow.value = true
        }else{
            header = "Error!"
            message = "Invalid Category Title"
            modalShow.value = true
        }
    }

    const closeModal = () => {
        modalShow.value = false
    }

    const removeAttachments = (index) => {

       if(confirm("Are you sure?\n\nThis will delete all files under this category!!\n\nYou CANNOT revert this!!")){
            form.attachments.splice(index, 1)

            categories.splice(index, 1)
        }else{

        }

    }

    const formatCategory = (text) => {
        if(text instanceof String){
            return text.toUpperCase()
        }else{
            return text
        }
    }

    const removeFile = (index, number) => {

        form.attachments[index].files.splice(number, 1)

        form.attachments[index].files.map(function($e, $i){
            $e.file_order = $i
            return $e;
        })

    }

    const confirmConferenceDeletion = () => {
        confirmingConferenceDeletion.value = true;

        nextTick(() => passwordInput.value.focus());
    };

    const closeDeleteModal = () => {
        confirmingConferenceDeletion.value = false;

        deleteForm.reset()
    };

    const deleteConference = (e) => {
        deleteForm.id = props.conf.id
        emit('deleteConf', deleteForm);
        passwordInput.value.focus()
    }

    const formSearch = useForm({
        search: null,
        page: 1
    })
    const fileSearchRes = ref([])
    const searchFileOnEnter = () => {
        axios.post(route('file.attachment'), formSearch)
        .then(({data}) => {
            fileSearchRes.value = data.data
        })
        .catch(e => {
            console.log(e)
        })
    }
    const searchFileOnScroll = () => {
        axios.post(route('file.attachment'), formSearch)
        .then(({data}) => {
            data.data.forEach(e => {
                fileSearchRes.value.push(e)
            })
        })
        .catch(e => {
            console.log(e)
        })
    }
    const onScroll = _.debounce(({ target: { scrollTop, clientHeight, scrollHeight }}) => {
        if (scrollTop + clientHeight >= scrollHeight) {
            formSearch.page = formSearch.page + 1
            searchFileOnScroll()
        }
    }, 300)

    const refreshPage = () => {
        formSearch.page = 1
    }

    const pdfModalShow = ref(false)
    const path = ref(null)
    const openPDFModal = (file) => {
        path.value = props.edit ? file.file_id : file.id
        pdfModalShow.value = true
    }
    const closePDFModal = () => {
        pdfModalShow.value =false
    }
</script>

<template>
<div class="py-5">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="pr-6 pl-6 mt-3">
                <div class="pr-6 pl-6 flex flex-row">

                    <div class="basis-full mr-1">

                        <InputLabel for="title" value="Title"/>

                        <TextInput
                            id="title"
                            type="text"
                            v-model="form.title"
                            class="mt-1 block w-full"
                        >
                        </TextInput>

                        <InputError :message="form.errors.title" class="mt-2" />

                    </div>

                    <div class="flex-initial p-1">

                        <InputLabel for="date" value="Schedule:"/>

                        <input type="date" name="date" v-model="form.date" id="date" class="rounded font-medium text-gray-700 border-gray-300">

                        <InputError :message="form.errors.date" class="mt-2" />

                    </div>

                    <div class="flex-initial p-1">

                        <InputLabel for="date" value="Status:"/>

                        <select name="status" id="status" v-model="form.status" class="border rounded text-gray-700 border-gray-300">
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                        </select>

                        <InputError :message="form.errors.status" class="mt-2" />

                    </div>

                </div>
                <div class="pr-6 pl-6 mt-2">
                    <InputLabel for="video_conf_url" value="Video Conference URL"/>

                    <TextInput
                        id="video_conf_url"
                        type="text"
                        v-model="form.video_conf_url"
                        class="mt-1 block w-full"
                    >
                    </TextInput>

                    <InputError :message="form.errors.title" class="mt-2" />
                </div>
                <div class="pr-6 pl-6 mt-2">

                    <InputLabel for="agenda" value="Agenda"/>

                    <InputError :message="form.errors.agenda" class="mt-2" />

                    <QuillEditor theme="snow" v-model:content="form.agenda" :toolbar="toolbarOptions" contentType="html" style="min-height: 500px;max-height: 500px; overflow-y: auto;"/>

                </div>

                <div class="pr-6 pl-6 flex flex-col mt-3">

                    <div class="basis w-full">
                        <InputLabel for="attachments" value="Attachments (ex. Refferals, For Review)"/>
                    </div>

                    <div class="basis basis-full flex flex-row">

                        <div class="basis basis-full">

                            <input
                                type="text"
                                class="rounded font-medium text-gray-700 border-gray-300 mt-2 w-full"
                                placeholder="Category Title"
                                v-model="category"
                            >

                        </div>

                        <div class="basis p-2">
                            <PrimaryButton
                                type="button"
                                class="object-fill h-10"
                                @click="addCategory(category)"
                            >
                                Add
                            </PrimaryButton>
                        </div>

                    </div>

                </div>

                <div class="pr-6 pl-6 mt-5 mb-5">
                    <div class="flex flex-col space-y-5">
                        <div class="basis basis-full">
                            <form @submit.prevent="searchFileOnEnter" class="">
                                <InputLabel value="File Name/Content/Date" for="search" />
                                <div class="basis basis-full flex flex-row space-x-2">
                                    <div class="relative rounded-md shadow-sm basis basis-full">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">
                                                <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512">
                                                    <path d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z"/>
                                                </svg>
                                            </span>
                                        </div>
                                        <TextInput id="search_file" @input="refreshPage()" @keyup.enter="searchFileOnEnter" type="search" class="w-full pl-9" v-model="formSearch.search"/>
                                    </div>
                                    <div class="flex items-center justify-center basis">
                                        <PrimaryButton class="items-center justify-center h-full">
                                            <span class="text-gray-500 sm:text-sm mr-2">
                                                <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512">
                                                    <path d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z"/>
                                                </svg>
                                            </span>
                                            Search
                                        </PrimaryButton>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="basis basis-full">
                            <div class="flex p-1 rounded" v-if="fileSearchRes.length > 0">
                                <div class="basis-1/3 pl-1">
                                    File Name
                                </div>
                                <div class="basis-1/3 pl-1">
                                    Title
                                </div>
                                <div class="basis-1/3 pl-1">
                                    Date
                                </div>
                                <div class="pr-3">
                                    View
                                </div>
                            </div>
                            <draggable
                                @start="drag=true"
                                @end="drag=false"
                                class="max-h-96 overflow-auto cursor-pointer"
                                group="files"
                                :list="fileSearchRes"
                                v-on:scroll="onScroll($event)"
                                itemKey="id">
                                <template #item="{element, index}">
                                    <div class="flex p-1 rounded hover:border hover:border-2 hover:border-slate-950" :class="{'bg-slate-200':index % 2 === 0}">
                                        <div class="basis-1/3 pl-1 content-center">
                                            {{element.file_name}}
                                        </div>
                                        <div class="basis-1/3 pl-1 content-center">
                                            {{ element.title }}
                                        </div>
                                        <div class="basis-1/3 pl-1 content-center">
                                            {{ element.date }}
                                        </div>
                                        <div>
                                            <button
                                                class="p-2 hover:border rounded rounded-full hover:border-2 hover:border-slate-900"
                                                 @click="openPDFModal(element)"
                                            >
                                                <EyeIcon class="h-4"></EyeIcon>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </draggable>
                        </div>
                    </div>
                </div>

                <div class="pr-6 pl-6">
                    <div class="grid grid-cols-2 gap-4">

                        <div class="border" v-for="(attachments, i) in form.attachments">

                            <p class="text-center p-2">{{ (i + 1) + '.' }} {{ formatCategory(attachments.category )}}</p>

                            <hr class="">

                            <div class="p-2">

                                <draggable
                                    v-model="form.attachments[i].files"
                                    @start="drag=true"
                                    @end="drag=false"
                                    :group="{name:'files', pull:false}"
                                    class="max-h-64 overflow-auto"
                                    itemKey="id">
                                <template #item="{element, index}">
                                        <div class="flex flex-row border">
                                            <div class="basis w-10 p-2 border-r-2 text-center">
                                                {{ index + 1}}
                                            </div>

                                            <div class="basis basis-full text-center whitespace-normal break-all cursor-ns-resize p-2">
                                                {{ element.file_name  }}
                                            </div>

                                            <div class="basis flex border-l-2 text-center">
                                                <button
                                                    class="p-2"
                                                    @click="openPDFModal(element)"
                                                >
                                                    <EyeIcon class="h-4"></EyeIcon>
                                                </button>
                                                <button class="p-2" type="button" @click="removeFile(i, index)">
                                                    <XCircleIcon class="h-5 w-5 text-center text-red-500"/>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </draggable>

                                <div class="text-center mt-3" v-if="attachments.files.length == 0">
                                    <p class="text-red-700">Category Files Cannot be Empty</p>
                                </div>

                                <PrimaryButton
                                    type="button"
                                    class="mt-4 w-full p-2 place-content-center bg-rose-600"

                                    @click="removeAttachments(i)"
                                >
                                    Remove
                                </PrimaryButton>
                            </div>

                        </div>

                    </div>
                </div>

                <div class="pr-6 pl-6 mt-3 pb-3 border-t-2">
                    <PrimaryButton
                        @click="submitData"
                        class="mt-4 w-full place-content-center"
                    >
                        Submit
                    </PrimaryButton>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="py-5" v-if="edit">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="pr-6 pl-6 mt-3">
                <div class="pr-6 pl-6 flex flex-col mt-3">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900">Delete Conference</h2>

                        <p class="mt-1 text-sm text-gray-600">
                            Once the Conference is deleted, all of its resources and data will be permanently deleted. Before deleting,
                            please download any data or information that you wish to retain.
                        </p>
                    </header>

                </div>
                <div class="pr-6 pl-6 mt-2">
                    <DangerButton class="mt-2 mb-4 text-center" @click="confirmConferenceDeletion">Delete Conference</DangerButton>
                </div>
            </div>

            <Modal :show="confirmingConferenceDeletion" @close="closeDeleteModal">
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-900">
                        Are you sure you want to delete this Conference data?
                    </h2>

                    <p class="mt-1 text-sm text-gray-600">
                        Once its deleted, all of its resources and data will be permanently deleted. Please
                        enter your password to confirm you would like to permanently delete the Conference data.
                    </p>

                    <div class="mt-6">
                        <InputLabel for="password" value="Password" class="sr-only" />

                        <TextInput
                            id="password"
                            ref="passwordInput"
                            v-model="deleteForm.password"
                            type="password"
                            class="mt-1 block w-3/4"
                            placeholder="Password"
                            @keyup.enter="deleteConference"
                        />

                        <InputError :message="deleteForm.errors.password" class="mt-2" />
                    </div>

                    <div class="mt-6 flex justify-end">
                        <SecondaryButton @click="closeDeleteModal"> Cancel </SecondaryButton>

                        <DangerButton
                            class="ml-3"
                            :class="{ 'opacity-25': deleteForm.processing }"
                            :disabled="deleteForm.processing"
                            @click="deleteConference"
                        >
                            Delete Conference
                        </DangerButton>
                    </div>
                </div>
            </Modal>
        </div>
    </div>
</div>

<Modal :show="pdfModalShow" :maxWidth="'4xl'" @close="closePDFModal">
    <div class="p-6">

        <div class="mt-2 flex items-center justify-center" style="height: 40rem;">
            <iframe class="z-50" :src="`/viewBlobPDF/`+path" style="width: 100%; height: 100%;"  type="application/pdf"></iframe>
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
            <SecondaryButton @click="closePDFModal"> Close </SecondaryButton>
        </div>
    </div>
</Modal>

<Modal :show="modalShow">
    <div class="p-6">
        <h2 class="text-lg font-medium text-red-500">
            {{ header }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{message}}
        </p>


        <SecondaryButton
            class="w-full mt-2 place-content-center bg-red-400"
            @click="closeModal">
                        <p>OK</p>
        </SecondaryButton>
    </div>
</Modal>
</template>
