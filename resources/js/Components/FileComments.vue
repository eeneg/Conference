<script setup>
import { ChatBubbleBottomCenterIcon } from '@heroicons/vue/20/solid';
import Modal from './Modal.vue';
import PrimaryButton from './PrimaryButton.vue';
import SecondaryButton from './SecondaryButton.vue';
import InputLabel from './InputLabel.vue';
import { ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import moment from 'moment';


const props = defineProps({file_id:String})
const user = usePage().props.auth.user.id
const comments = ref([])

const editMode = ref(false)

const form = useForm({
    user_id:user,
    file_id:props.file_id,
    comment: null
})

const submit = () => {
    if(editMode.value){
        sendUpdate()
    }else{
        sendComment()
    }
}

const sendComment = () => {
    form.submit('post', route('fileComments.store'),{
        onSuccess: () => {
            getComments()
            editMode.value = false
            form.comment = null
        },
        onError: () => {

        }
    })
}

const commentId = ref(null)
const sendUpdate= () => {
    form.submit('patch', route('fileComments.update', commentId.value),{
        onSuccess: () => {
            getComments()
            editMode.value = false
            form.comment = null
        },
        onError: () => {

        }
    })
}

const updateComment = (comment, id) => {
    form.comment = comment
    commentId.value = id
    editMode.value = true
}

const commentModal = ref(false)
const openCommentsModal = () => {
    commentModal.value = true
    getComments()
}
const closeCommentModal = () => {
    commentModal.value = false
}

const container = ref(null)
const getComments = () => {
    axios.get(route('fileComments.index', {file_id:props.file_id}))
    .then(({data}) => {
        comments.value = data
    })
    .catch(e => {
        console.log(e)
    })
}

const deleteComment = (id) => {
    axios.delete(route('fileComments.destroy', {id:id}))
    .then(({data}) => {
        getComments()
    })
    .catch(e => {
        console.log(e)
    })
}
</script>

<template>

<button @click="openCommentsModal()">
    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-400 hover:bg-indigo-500 text-black-900">
        <ChatBubbleBottomCenterIcon class="w-5 h-5 fill-none stroke-black stroke-2 aria-hidden" aria-hidden="true" />
    </div>
</button>

<Modal :show="commentModal" @close="closeCommentModal">
    <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900">
            File Annotations/Comments
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Add Annotations/Comments on this File
        </p>

        <div id="comment" ref="comment" class="w-full mt-4 max-h-96 min-h-20 p-2 overflow-auto">
            <div v-if="comments.length == 0" class="text-gray-400 text-center">
                -- No Annotations/Comments --
            </div>
            <div class="flex p-1 space-x-2" v-for="comment in comments">
                <div class="flex gap-4">
                    <div class="relative inline-flex items-center justify-center w-10 h-10 overflow-hidden bg-gray-100 rounded-full dark:bg-gray-600">
                        <span class="font-medium text-gray-600 dark:text-gray-300">SP</span>
                    </div>
                </div>
                <div class="flex flex-col border rounded bg-slate-200 p-2">
                    <div class="text-sm font-bold border basis-full text-left">
                        {{comment.name}}
                    </div>
                    <div class="text-xs border basis-full">
                        {{comment.comment}}
                    </div>
                    <div class="text-xs flex space-x-2">
                        <div v-if="comment.user_id == user" class="cursor-pointer text-blue-900 hover:underline" @click="updateComment(comment.comment, comment.id)">
                            edit
                        </div>
                        <div v-if="comment.user_id == user" class="cursor-pointer text-red-800 hover:underline hover:text-red-900" @click="deleteComment(comment.id)">
                            delete
                        </div>
                        <div class="text-black-900 text-gray-500">
                            {{moment(comment.created_at).fromNow()}}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full mt-4 mb-4">
            <div class="flex flex-col">
                <div class="flex space-x-4">
                    <textarea class="rounded border-gray-400 w-full" v-model="form.comment"></textarea>
                    <PrimaryButton @click="submit()">Send</PrimaryButton>
                </div>
                <div class="flex">
                    <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition ease-in-out">
                        <InputLabel v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</InputLabel>
                    </Transition>
                </div>
            </div>
        </div>

        <div class="mt-6 flex space-x-2 justify-start">
            <SecondaryButton @click="closeCommentModal"> Close </SecondaryButton>
        </div>
    </div>
</Modal>
</template>
