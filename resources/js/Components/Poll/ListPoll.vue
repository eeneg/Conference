<script setup>
import Modal from '@/Components/Modal.vue'
import SecondaryButton from '../SecondaryButton.vue';
import DangerButton from '../DangerButton.vue';
import InputError from '../InputError.vue';
import InputLabel from '../InputLabel.vue';
import TextInput from '../TextInput.vue';
import ResponseModal from '../ResponseModal.vue';
import { PencilIcon, TrashIcon, PlayIcon, EyeIcon } from '@heroicons/vue/20/solid';
import { useForm, usePage } from '@inertiajs/vue3';
import { onMounted,ref,defineExpose  } from 'vue';

const props = defineProps({showPollListModal:Boolean,conference_id:String})

const emit = defineEmits(['closePollListModal', 'openEditPollModalEmit', 'openPollModal'])

const header = ref('')
const message = ref('')
const success = ref(false)
const responseModal = ref(false)

const closeResponseModal = (e) => {
    responseModal.value = e
}

const polls = ref([])
const loading = ref(false)
const getPolls = () => {
    loading.value = true
    axios.get('/poll?id='+props.conference_id)
    .then(({data}) => {
        loading.value = false
        polls.value = data
    })
    .catch(e => {
        loading.value = false
        console.log(e)
        console.log('Failed to fetch polls')
    })
}

const closePollListModal = () => {
    emit('closePollListModal', false)
}

//Delete

const deleteForm = useForm({
    password: null,
    id: null
})

const confirmingPollDeletion = ref(false)

const deletePollModal = (id) => {
    confirmingPollDeletion.value = true
    deleteForm.id = id
}

const closeDeleteModal = () => {
    confirmingPollDeletion.value = false
}

const deletePoll = (id) => {
    deleteForm.delete(route('poll.destroy', {id: id}),{
        onSuccess: () => {
            header.value = "Success!"
            success.value = true
            message.value = "Poll deleted successfully"
            confirmingPollDeletion.value = false
            responseModal.value = true
            deleteForm.reset()
            getPolls()
        },
        onErrorCaptured: () => {
            header.value = "Failed!"
            success.value = false
            message.value = "Something went wrong"
            confirmingPollDeletion.value = false
            responseModal.value = true
        }
    })
}

//edit
const openEditPollModal = (e) => {
    emit('openEditPollModalEmit', e)
}

const viewPollModal = (e) => {
    emit('openPollModal', e)
}

//start poll
const user_id = usePage().props.auth.user.id;

const openVotingPollModal = (id, concluded) => {
    if(concluded && !confirm('Poll already concluded. \n\nProceed anyway?')){

    }else{
        axios.post('/setPollActive', {id: id, initiatorID: user_id, value:true})
        .then(({data}) => {

        })
        .catch(e => {
            console.log(e)
        })
    }
}

onMounted(() =>{
    getPolls()
})

defineExpose({
  getPollExpose() {
    getPolls()
  }
})

window.Echo.private('poll-result').listen('PollConcludedEvent', (e) => {
    getPolls()
});
</script>

<template>
    <Modal :show="props.showPollListModal" :maxWidth="'4xl'">
        <div class="p-6">

            <div class="flex flex-row">
                <div class="basis-1/2">
                    <h2 class="text-lg font-medium text-black-500">
                        Poll
                    </h2>

                    <p class="mt-1 text-sm text-gray-600">
                        View Poll List Here
                    </p>
                </div>
                <div class="basis-1/2">
                    <a class="float-right" role="button" @click="closePollListModal">
                        <svg x="0px" y="0px" width="20" height="20" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" opacity=".35"></circle><path d="M14.812,16.215L7.785,9.188c-0.384-0.384-0.384-1.008,0-1.392l0.011-0.011c0.384-0.384,1.008-0.384,1.392,0l7.027,7.027	c0.384,0.384,0.384,1.008,0,1.392l-0.011,0.011C15.82,16.599,15.196,16.599,14.812,16.215z"></path><path d="M7.785,14.812l7.027-7.027c0.384-0.384,1.008-0.384,1.392,0l0.011,0.011c0.384,0.384,0.384,1.008,0,1.392l-7.027,7.027	c-0.384,0.384-1.008,0.384-1.392,0l-0.011-0.011C7.401,15.82,7.401,15.196,7.785,14.812z"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <div class="mt-3 mb-3 mr-3 pr-6 pl-5">
                <div class="flex items-center justify-center grow" role="status"  v-if="loading">
                    <svg aria-hidden="true" class="w-8 h-8 text-gray-200 animate-spin dark:text-gray-600 fill-indigo-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/><path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/></svg>
                    <span class="sr-only">Loading...</span>
                </div>
                <div class="border rounded flex mb-2" v-for="poll in polls">
                    <div class="flex items-center justify-center w-64 border-r p-2">
                        {{ poll.title }}
                    </div>
                    <div class="flex items-center justify-center w-64 border-r p-2 capitalize">
                        {{ poll.type }}
                    </div>
                    <div class="flex items-center justify-center w-64 p-2 border-r">
                        {{ (poll.result == "") || (poll.result == null) ? "No Result" : poll.result }}
                    </div>
                    <div class="flex items-center justify-center w-64 p-2 border-r" :class="{'text-green-900':poll.concluded, 'text-red-900':poll.concluded == false}">
                        {{ poll.concluded == false ? 'Unconcluded' : 'Concluded' }}
                    </div>
                    <div class="flex items-center justify-center float-right p-2">
                        <button @click="viewPollModal(poll.id)">
                            <div class="flex h-5 w-5 items-center justify-center rounded-full bg-indigo-400 hover:bg-indigo-300 text-red-900 mr-1">
                                <EyeIcon class="w-3 h-3 fill-black " aria-hidden="true" />
                            </div>
                        </button>
                        <button @click="openVotingPollModal(poll.id, poll.concluded)">
                            <div class="flex h-5 w-5 items-center justify-center rounded-full bg-green-400 hover:bg-green-300 text-red-900 mr-1">
                                <PlayIcon class="w-3 h-3 fill-black " aria-hidden="true" />
                            </div>
                        </button>
                        <button @click="openEditPollModal(poll)">
                            <div class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-400 hover:bg-blue-300 text-red-900 mr-1">
                                <PencilIcon class="w-3 h-3 fill-black " aria-hidden="true" />
                            </div>
                        </button>
                        <button @click="deletePollModal(poll.id)">
                            <div class="flex h-5 w-5 items-center justify-center rounded-full bg-red-500 hover:bg-red-300 text-red-900">
                                <TrashIcon class="w-3 h-3 stroke-gray-900 fill-none aria-hidden" aria-hidden="true" />
                            </div>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </Modal>

    <Modal :show="confirmingPollDeletion" @close="closeDeleteModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Are you sure you want to delete this Poll?
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Once its deleted, all of its resources and data will be permanently deleted. Please
                enter your password to confirm you would like to permanently delete the Poll.
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
                    @keyup.enter="deletePoll"
                />

                <InputError :message="deleteForm.errors.password" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="closeDeleteModal"> Cancel </SecondaryButton>

                <DangerButton
                    class="ml-3"
                    :class="{ 'opacity-25': deleteForm.processing }"
                    :disabled="deleteForm.processing"
                    @click="deletePoll"
                >
                    Delete Poll
                </DangerButton>
            </div>
        </div>
    </Modal>

    <ResponseModal v-if="responseModal" @closeResponseModal="closeResponseModal($event)" :responseModalProp="responseModal" :message="message" :header="header" :success="success"/>
</template>
