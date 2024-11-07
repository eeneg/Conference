<script setup>
    import Form from '@/Pages/Conferences/Form.vue';
    import { Head } from '@inertiajs/vue3';
    import Modal from '@/Components/Modal.vue';
    import { ref } from 'vue';
    import SecondaryButton from '@/Components/SecondaryButton.vue';

    var props = defineProps({storage:Object})

    var header = ""
    var message = ""
    var success = true

    const modalShow = ref(false)

    const submit = (form) => {
        form.post(route('conferences.store'), {
            onSuccess: () => {
                header = "Success!"
                success = true
                message = "Confrence Submitted Successfuly"
                modalShow.value = true
            },
            onError: (e) => {
                console.log(e)
                header = "Error!"
                success = false
                message = "Something went wrong"
                modalShow.value = true
            }
        })
    }

    const closeModal = () => {
        modalShow.value = false
    }



</script>

<style>
    .ql-editor{
        min-height:200px;
    }
</style>

<template>
    <Head title="Conference Form" />

    <header class="bg-white shadow">
        <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Conference Form</h2>
        </div>
    </header>

    <Form @passData ="submit($event)" :conf="null" :edit="false" :storage="props.storage">

    </Form>

    <Modal :show="modalShow">
        <div class="p-6">
            <h2 :class="{'text-lg font-medium text-green-500': success == true, 'text-lg font-medium text-red-500': success == false}">
                {{ header }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{message}}
            </p>

            <SecondaryButton
                class="w-full mt-2 place-content-center"
                @click="closeModal">
                    <p>OK</p>
            </SecondaryButton>
        </div>
    </Modal>

</template>
