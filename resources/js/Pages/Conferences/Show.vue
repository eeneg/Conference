<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue'
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import { QuillEditor } from '@vueup/vue-quill';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import PollBody from '@/Components/Poll/PollBody.vue';
import ReferencesSearch from '@/Components/ReferencesSearch.vue';
import { ChevronUpIcon } from '@heroicons/vue/20/solid'
import { ref, computed } from 'vue'
import '@vueup/vue-quill/dist/vue-quill.snow.css'
import _ from 'lodash'
import axios from 'axios';

const props = defineProps({
    conf: Object,
    attachments: Object,
    note: Object,
    reference_category: Object
})


const tabs = [
    'Agenda',
    'Attachments',
    'Notes',
    'References'
]

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

const URL = window.URL

const host = location.origin

const embed = ref(null)

const url = computed(() => new URL((embed.value?.path ?? '').replace('public/', ''), host + '/storage/').href)

const action = computed(() => embed.value?.is_video || embed.value?.is_audio ? 'Playing' : 'Previewing')

const title = computed(() => embed.value?.is_previewable ? `${action.value} ${embed.value.file_name}` : `Conferences ${props.conf.title}`)

const reembed = (attachment) =>{
    if(embed.value == null || embed.value.id != attachment.id)
    {
        axios.get(`/viewBlobPDF/${attachment.id}`)
        .then(e => {
            embed.value = embed.value === attachment ? null : attachment
            embed.value.pdf = e.data
        })
        .catch(e => {
            console.log(e)
        })
    }
}

const form = useForm({
    conference_id: props.conf.id,
    note: props.note == null ? '<p>Sample Note</p><p><br></p><ul><li>Sample Note Item 1</li><li>Sample Note item 2</li></ul>' : props.note.note
})

const saveNote = () => {
    form.post(route('note.store'),{
        onSuccess: () => {

        },
        onError: () => {

        }
    })
}

const conf_url = ref(props.conf.video_conf_url)
const getURL = () => {
    axios.get(route('conf.url', props.conf.id))
    .then(({data}) => {
        conf_url.value = data
    })
    .catch(e => {
        console.log('fail')
    })
}
const inputSave = _.debounce(() => {
    saveNote()
}, 400)
</script>

<template>
    <Head :title="title" />

    <header class="bg-white shadow">
        <div class="flex px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="grow">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{conf.title}}</h2>
                <div class="flex">
                    <p class="text-sm text-blue-900 cursor-pointer" @click="getURL()">
                        Conference URL:
                    </p>
                    <p class="text-sm text-blue-900 ml-2" @click="getURL()">
                        {{ conf_url }}
                    </p>
                </div>
            </div>
            <div class="flex flex-row-reverse space-x-2 items-center">
                <PollBody :conference="props.conf"/>
            </div>
        </div>
    </header>

    <div class="py-5">
        <div class="mx-auto max-w-14xl sm:px-12 lg:px-16">
            <div class="overflow-hidden sm:rounded-lg">
                <TabGroup>
                    <TabList class="flex flex-row py-3 space-x-3 bg-white rounded-lg p">
                        <Tab v-for="tab in tabs" v-slot="{ selected }">
                            <button
                                :class="[
                                    'w-full mx-3 py-0.5 text-sm font-medium leading-5',
                                    selected
                                        ? 'border-b-2 border-indigo-400'
                                        : 'hover:text-gray-500 border-b-2 hover:border-indigo-200',
                                ]"
                            >
                                {{ tab }}
                            </button>
                        </Tab>
                    </TabList>

                    <TabPanels>
                        <TabPanel class="rounded-xl">
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-4">
                                <div class="py-6 px-4">
                                    <div class="prose max-w-none" v-html="conf.agenda"></div>
                                </div>
                            </div>
                        </TabPanel>

                        <TabPanel class="rounded-xl">
                            <div class="flex gap-3">
                                <div class="w-full max-w-xs rounded-2xl">
                                    <Disclosure v-for="(attachments, category) in props.attachments" v-slot="{ open }">
                                        <DisclosureButton
                                            class="flex justify-between w-full px-4 py-2 my-2 text-sm font-medium text-left bg-white rounded-lg hover:bg-indigo-100"
                                        >
                                            <span> {{ category }} </span>
                                            <ChevronUpIcon
                                                :class="open ? 'rotate-180 transform' : ''"
                                                class="w-5 h-5 text-indigo-500"
                                            />
                                        </DisclosureButton>

                                        <DisclosurePanel class="pt-1 pb-2 text-sm text-gray-500">
                                            <div class="pl-3 space-y-1">
                                                <button
                                                    v-for="attachment in attachments"
                                                    @click="reembed(attachment.file)"
                                                    aria-current="page"
                                                    :class="['block px-3 py-1 text-sm font-medium rounded-md w-full text-left text-gray-700 hover:bg-indigo-100',
                                                        attachment.id === embed?.id ? 'bg-white' : ''
                                                    ]"
                                                >
                                                    {{ attachment.file.file_name }}
                                                </button>
                                            </div>
                                        </DisclosurePanel>
                                    </Disclosure>
                                </div>

                                <div class="flex-1 h-screen rounded-lg">

                                    <div class="w-full py-2">
                                        <template v-if="! embed">
                                            <p class="italic">Select an attachment from categories in the right to preview...</p>
                                        </template>

                                        <template v-else-if="! embed.is_previewable">
                                            <p class="">
                                                File is not previewable in browser. You can download it by clicking the link below.
                                            </p>

                                            <a :href="url" class="italic text-indigo-700 underline">
                                                {{ embed.file_name }}
                                            </a>
                                        </template>

                                        <template v-else>
                                            <div class="flex flex-row">
                                                <div class="flex-1">
                                                    <p>
                                                        {{ action }} <span class="font-bold">{{ embed.file_name }}</span> from <strong> category {{ embed.category }} </strong>.
                                                    </p>
                                                    <p>
                                                        Download:
                                                        <a :href="route('attachment.download', embed.id)" class="text-indigo-700 underline">
                                                            <span class="italic">click here</span>
                                                        </a>
                                                    </p>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <embed
                                        v-if="embed != null"
                                        class="w-full h-full rounded-lg"
                                        :class="[]"
                                        :src="'data:application/pdf;base64,'+embed.pdf"
                                    >
                                </div>
                            </div>
                        </TabPanel>

                        <TabPanel class="rounded-xl">
                            <div class="mt-4 w-full mx-auto">
                                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                    <div class="p-6 mt-3">
                                        <div class="flex flex-col">
                                            <div class="flex justify-between">
                                                <InputLabel>Note</InputLabel>
                                                <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition ease-in-out">
                                                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                                                </Transition>
                                            </div>
                                            <div>
                                                <QuillEditor @input="inputSave()" theme="snow" v-model:content="form.note" :toolbar="toolbarOptions" contentType="html" style="min-height: 500px;max-height: 500px; overflow-y: auto;"/>
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <PrimaryButton @click="saveNote()">Save</PrimaryButton>
                                            <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition ease-in-out">
                                                <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                                            </Transition>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </TabPanel>


                        <TabPanel class="rounded-xl">
                            <div class="mt-4 w-full mx-auto">
                                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                    <div class="p-6 mt-3">
                                        <div class="flex flex-col">
                                            <div class="flex justify-between">
                                                <ReferencesSearch :reference_category="props.reference_category"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </TabPanel>
                    </TabPanels>
                </TabGroup>
            </div>
        </div>
    </div>

</template>

<style scoped>
.ql-container {
    border: none;
}
</style>
