<script setup>
    import TextInput from '../TextInput.vue';
    import { usePage } from '@inertiajs/vue3';
    import axios from 'axios';
    import _ from 'lodash';
    import { onMounted, ref } from 'vue'

    const id = usePage().props.auth.user.id;

    const users = ref([])

    const userPage = ref(1)

    const modeMessageList = ref(1)

    const showLoading = ref(false)

    const search = ref("")

    const navLocation = ref(1)

    const emit = defineEmits(['navChat'])

    const goToChat = (id, name) =>{
        markMessageAsRead()
        emit('navChat', {chat: true, recipient_id: id, user_name: name})
    }

    const markMessageAsRead = () => {
        axios.post('/setMessageStatus', {id: id})
        .then(({e}) => {

        })
        .catch(e => {

        })
    }

    const searchUsers = (page, search) => {
        axios.get('/usersToChat?page='+page+'&search='+search)
        .then(({data}) => {
            data.data.forEach(e => {
                users.value.push(e)
            })
            showLoading.value = false
        })
        .catch(e =>{
            console.log(e)
            console.log('Something Went Wrong!')
        })
    }

    const getUsersChatList = (page) => {
        axios.get('/userChatList?page='+page)
        .then(({data}) => {
            data.data.forEach(e => {
                users.value.push(
                    {
                        id: e.id,
                        name: e.latest_message.user.name,
                        message: e.latest_message.message,
                        read: e.latest_message.read,
                    }
                )
                console.log(e)
            })
            showLoading.value = false
        })
        .catch(e =>{
            console.log(e)
            console.log('Something Went Wrong!')
        })
    }

    const updateChatList = () => {
        axios.get('/userChatList?page='+1)
        .then(({data}) => {
            users.value = data.data
        })
        .catch(e =>{
            console.log(e)
            console.log('Something Went Wrong!')
        })
    }

    const searchGroups = (page, search) => {
        // axios.get('/groupsToChat?page='+page+'&search='+search)
        // .then(({data}) => {
        //     data.data.forEach(e => {
        //         users.value.push(e)
        //     })
        //     showLoading.value = false
        // })
        // .catch(e =>{
        //     console.log(e)
        //     console.log('Something Went Wrong!')
        // })
    }

    const searchUserFunction = _.debounce(function(){
        users.value = []
        if(navLocation.value == 1){
            getUsersChatList(1)
        }else if(navLocation.value == 2){
            searchUsers(1, search.value)
        }else if(navLocation.value == 3){
            searchGroups()
        }
    }, 300)

    const onScroll = ({ target: { scrollTop, clientHeight, scrollHeight }}) => {
        if (scrollTop + clientHeight >= scrollHeight) {
            userPage.value += 1
            showLoading.value = true
            searchUsers(userPage.value, search.value)
        }
    }

    const navigate = (mode) => {
        users.value = []
        modeMessageList.value = mode
        if(mode == 1){
            navLocation.value = 1
            getUsersChatList(1)
        }else if(mode == 2){
            navLocation.value = 2
            searchUsers(1, search.value)
        }else if(mode == 3){
            navLocation.value = 3
            searchGroups()
        }
    }

    const formatText = (text) => {
        if(text.length > 20){
            text = text.slice(0, 20) + " ..."
        }
        return text
    }

    onMounted(() => {
        getUsersChatList(1)
        window.Echo.private('chat').listen('MessageSentEvent', (e) => {
            updateChatList()
        });
    })
</script>

<style>

</style>

<template>
    <div class="flex flex-col h-96">
        <div class="flex w-full p-2">
            <TextInput class="w-full rounded" placeholder="Search..." @input="searchUserFunction()" v-model="search"></TextInput>
        </div>
        <div class="flex flex-row w-full space-x-1 p-2">
            <button @click="navigate(1)" class="rounded rounded-full w-[6rem] py-1 uppercase border-2 text-xs text-white bg-gray-800" :class="{'border-2 border-indigo-600 bg-indigo-800' : modeMessageList == 1}">Messages</button>
            <button @click="navigate(2)" class="rounded rounded-full w-[6rem] py-1 uppercase border-2 text-xs text-white bg-gray-800" :class="{'border-2 border-indigo-600 bg-indigo-800' : modeMessageList == 2}">Contacts</button>
            <button @click="navigate(3)" class="rounded rounded-full w-[6rem] py-1 uppercase border-2 text-xs text-white bg-gray-800" :class="{'border-2 border-indigo-600 bg-indigo-800' : modeMessageList == 3}">Groups</button>
        </div>
        <div id="userChatList" class="flex w-full mt-2 flex-col overflow-auto" ref="userChatList" v-on:scroll="onScroll($event)">
            <div v-for="user in users" type="button" class="w-full hover:bg-indigo-700 hover:text-white rounded p-2 cursor-pointer" @click="goToChat(user.id, user.name)">
                <div class="flex gap-4">
                    <div class="flex items-center gap-4">
                        <div class="relative inline-flex items-center justify-center w-10 h-10 overflow-hidden bg-gray-100 rounded-full dark:bg-gray-600">
                            <span class="font-medium text-gray-600 dark:text-gray-300">SP</span>
                        </div>
                    </div>
                    <div class="flex flex-col w-full pr-2">
                        <div class=""><p class="float-left font-bold">{{ user.name }}</p></div>
                        <div class="text-sm w-full" :class="{'font-bold text-lg dark:text-black-400' : user.read == false && user.sender_id != id, 'dark:text-black-400' : user.sender_id == id}">
                            <p class="float-left truncate w-24">{{user.message == undefined ? '' : user.message}}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex justify-center" v-if="showLoading">
            Loading ...
        </div>
    </div>
</template>
