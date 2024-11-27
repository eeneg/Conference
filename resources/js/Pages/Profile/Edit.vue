<script setup>
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import AssignRoleFormVue from '@/Layouts/AssignUserRoleLayout.vue'
import { Head, usePage } from '@inertiajs/vue3';

const user = usePage().props.auth.user

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    roles: {
        type: Object
    },
    currentRole: {
        type: Object
    }

});
</script>

<template>
    <Head title="Profile" />

    <header class="bg-white shadow">
        <div class="px-4 py-6 mx-auto max-w-[90rem] sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Profile</h2>
        </div>
    </header>
    <div class="py-12">
        <div class="max-w-[90rem] mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>

            <div v-if="currentRole.title == 'administrator'" class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <AssignRoleFormVue
                    :roles="roles"
                    :currentRole="currentRole"
                    :fromProfile=true
                    :user_id="user.id"
                    class="max-w-xl"
                />
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </div>
</template>
