<script setup>
import { onMounted, ref } from 'vue';
import Logo from '@/Components/Logo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { InboxIcon } from '@heroicons/vue/20/solid';
import ChatBox from '@/Components/Chat/ChatBox.vue';
import Poll from '@/Components/Poll/Poll.vue';
import PollResult from '@/Components/PollResult.vue';

const showingNavigationDropdown = ref(false);

const role = usePage().props.auth.role;
const user_id = usePage().props.auth.user.id;

const showPollModal = ref(false)
const pollID = ref(null)
const initiatorID = ref(null)

const showPollResultModal = ref(false)

const newMessageCount = ref(0)

const chat = ref(false)

const poll = ref(null)

const goChat = (bool) => {
    chat.value = bool
}

const resetMessageCount = () => {
    newMessageCount.value = 0
}

const getNewMessageCount = () => {
    axios.get('/newMessageCount')
    .then(({data}) => {
        newMessageCount.value = data
    })
    .catch(e =>{
        console.log(e)
        console.log('Something Went Wrong!')
    })
}

const openPollModal = (e) => {
    pollID.value = e.poll_id
    initiatorID.value = e.initiatorID
    if(role == 'board member' || initiatorID.value == user_id){
        showPollModal.value = e.value
    }
    if(showPollModal.value == true){
        showPollResultModal.value = false
    }
}

const closeResModal = (e) => {
    showPollResultModal.value = e
}

onMounted(() => {
    getNewMessageCount()
    window.Echo.private('chat').listen('MessageSentEvent', (e) => {
        getNewMessageCount()
    });
    window.Echo.private('poll').listen('PollSetActiveEvent', (e) => {
        openPollModal(e)
    });
    window.Echo.private('poll-result').listen('PollConcludedEvent', (e) => {
        showPollModal.value = false
        poll.value = e.poll
        showPollResultModal.value = true
    });
})

</script>


<style>
.badge {
    position: absolute;
    top: -5px;
    right: -5px;
    width: 25px;
    height: 25px;
    background: red;
    color: #ffffff;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 50%;
}
</style>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100">
                <!-- Primary Navigation Menu -->
                <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex items-center shrink-0">
                                <Link :href="route('dashboard')">
                                    <Logo :w="'w-14'" :h="'h-14'"/>
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Dashboard
                                </NavLink>
                                <NavLink v-if="role == 'administrator'" :href="route('users.index')" :active="route().current('users.*')">
                                    Users
                                </NavLink>
                                <NavLink :href="route('conferences.index')" :active="route().current('conferences.*')">
                                    Conferences
                                </NavLink>
                                <NavLink :href="route('file.index')" :active="route().current('file.index')">
                                    Find Files
                                </NavLink>
                                <NavLink :href="route('files.index')" :active="route().current('files.*')">
                                    File Upload
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <!-- Settings Dropdown -->
                            <div class="relative ml-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border border-transparent rounded-md hover:text-gray-700 focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}

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
                                        <DropdownLink :href="route('profile.edit')"> Profile </DropdownLink>
                                        <DropdownLink :href="route('storage.index')"> Settings </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="flex items-center -mr-2 sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 text-gray-400 transition duration-150 ease-in-out rounded-md hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500"
                            >
                                <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                    class="sm:hidden"
                >
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="role == 'administrator'" :href="route('users.index')" :active="route().current('users.index')">
                            Users
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('conferences.index')" :active="route().current('conferences.index')">
                            Conferences
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('file.index')" :active="route().current('file.index')">
                            Find Files
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('files.index')" :active="route().current('files.index')">
                            File Upload
                        </ResponsiveNavLink>

                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="px-4">
                            <div class="text-base font-medium text-gray-800">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">{{ $page.props.auth.user.email }}</div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')"> Profile </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('storage.index')"> Settings </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
            <ChatBox class="fixed bottom-0 shadow-md right-20" @closeChat="goChat(false)" v-if="chat == true"/>
            <div class="fixed bottom-4 right-4" @click="goChat(true)" v-if="chat == false">
                <button @click="resetMessageCount" class="px-4 py-4 font-bold text-white bg-indigo-500 rounded-full shadow-lg hover:bg-indigo-600">
                    <InboxIcon class="w-6 h-6"/>
                    <span class="badge" v-if="newMessageCount != 0">{{newMessageCount}}</span>
                </button>
            </div>

            <!-- poll modal -->
            <Poll v-if="showPollModal" :show="showPollModal" :pollID="pollID" :initiatorID="initiatorID"/>

            <PollResult @close="closeResModal($event)" v-if="showPollResultModal" :showModal="showPollResultModal" :poll="poll"/>

        </div>
    </div>
</template>
