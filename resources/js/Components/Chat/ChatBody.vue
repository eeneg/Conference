<script setup>
    import { ref, onMounted } from 'vue';
    import { usePage } from '@inertiajs/vue3';
    import axios from 'axios';
    import _ from 'lodash';
    import PrimaryButton from '../PrimaryButton.vue';
    import TextInput from '../TextInput.vue';

    const props = defineProps({recipient_id:String})

    const messages = ref([])

    const message = ref('')

    const sending = ref(false)

    const chatPage = ref(1)

    const showLoading = ref(true)

    const getMessages = (page = 1) => {
        showLoading.value = true
        axios.get('/messages/'+props.recipient_id+'?page='+page)
        .then(({data}) => {
            if(data.data.length > 0){
                transformData(data.data)
            }

            showLoading.value = false
        })
        .catch(e => {
            console.log(e)
        })
    }

    const sendMessage = _.debounce(function() {
        sending.value = false
        if(message.value == null || (typeof message.value === "string" && message.value.trim().length === 0)){
            //TODO
        }else{
            pushNewUserMessage(message.value, usePage().props.auth.user.id)
            axios.post(route('messages.store'), {message: message.value, recipient_id: props.recipient_id})
            .then(e => {
                sending.value = true
                message.value = ''
            })
            .catch(e => {
                console.log(e)
            })
        }
    }, 200)

    const transformData = (data) => {
        let groupedMessages = data[0].messages.reduce((acc, curr) => {
            let lastGroup = acc[acc.length - 1];
            if (lastGroup && lastGroup[0].sender_id === curr.sender_id) {
                lastGroup.push(curr);
            } else {
                acc.push([curr]);
            }
            return acc;
        }, []);

        console.log(groupedMessages)

        messages.value.push(...groupedMessages);
    }

    const pushNewUserMessage = (msg, id) => {
        if (messages.value.length === 0) {
            messages.value = [[{ message: msg, sender_id: id }]];
        } else {
            const firstMessageUserId = messages.value[0][0].sender_id;
            if (firstMessageUserId === id) {
                messages.value[0].unshift({ message: msg, sender_id: id });
            } else {
                messages.value.unshift([{ message: msg, sender_id: id }]);
            }
        }
    }

    const setMessageReadStatus = () => {
        axios.post(route('message.set'), {id: props.recipient_id})
        .then(e => {

        })
        .catch(e => {
            console.log(e)
        })
    }

    const onScroll = _.debounce(function({ target: { scrollTop, clientHeight, scrollHeight }}) {
        let res = clientHeight - scrollTop
        if (res == scrollHeight) {
            chatPage.value += 1
            getMessages(chatPage.value)
        }
    }, 200)

    onMounted(() => {
        getMessages()
        setMessageReadStatus()
        window.Echo.private('chat').listen('MessageSentEvent', (e) => {
            if(props.recipient_id == e.sender_id){
                pushNewUserMessage(e.message.message, e.message.sender_id, e.message.recipient_id)
                // setMessageReadStatus()
            }
        });
    })
</script>

<style>
.chat {
  width: 300px;
  border: solid 1px #EEE;
  display: flex;
  flex-direction: column;
  padding: 10px;
}

.messages {
  margin-top: 30px;
  display: flex;
  flex-direction: column;
}

.message {
  border-radius: 20px;
  padding: 8px 15px;
  margin-top: 5px;
  margin-bottom: 5px;
  display: inline-block;
  word-break: break-all;
}

.yours {
  align-items: flex-start;
}

.yours .message {
  margin-right: 25%;
  background-color: #eee;
  position: relative;
}

.yours .message.last:before {
  content: "";
  position: absolute;
  z-index: 0;
  bottom: 0;
  left: -7px;
  height: 20px;
  width: 20px;
  background: #eee;
  border-bottom-right-radius: 15px;
}
.yours .message.last:after {
  content: "";
  position: absolute;
  z-index: 1;
  bottom: 0;
  left: -10px;
  width: 10px;
  height: 20px;
  background: white;
  border-bottom-right-radius: 10px;
}

.mine {
  align-items: flex-end;
}

.mine .message {
  color: white;
  margin-left: 25%;
  background: linear-gradient(to bottom, #00D0EA 0%, #0085D1 100%);
  background-attachment: fixed;
  position: relative;
}

.mine .message.last:before {
  content: "";
  position: absolute;
  z-index: 0;
  bottom: 0;
  right: -8px;
  height: 20px;
  width: 20px;
  background: linear-gradient(to bottom, #00D0EA 0%, #0085D1 100%);
  background-attachment: fixed;
  border-bottom-left-radius: 15px;
}

.mine .message.last:after {
  content: "";
  position: absolute;
  z-index: 1;
  bottom: 0;
  right: -10px;
  width: 10px;
  height: 20px;
  background: white;
  border-bottom-left-radius: 10px;
}
</style>

<template>
        <div class="flex flex-col">
            <div class="flex flex-col flex-col-reverse w-full p-3 overflow-auto h-80" v-on:scroll="onScroll">
                <div v-for="message in messages" :class="{'mine':message[0].sender_id != props.recipient_id,'yours':message[0].sender_id == props.recipient_id, }" class="messages">
                    <!-- {{ message }} -->
                    <div v-for="(msg, index) in message.slice().reverse()" :class="{'last': (index+1) == message.length}" class="message">
                      {{ msg.message }}
                    </div>
                </div>
                <div class="flex justify-center" v-if="showLoading">
                    Loading ...
                </div>
            </div>
            <div class="p-0" v-if="sending">
                <p class="font-thin text-xs float-right pr-2 text-current">Sent</p>
            </div>
            <div class="flex w-full p-2 space-x-2">
                <textarea class="w-full border rounded-full"  style="white-space: pre-wrap; resize: none; overflow: auto; height: auto;" rows="1" @keyup.enter="sendMessage" placeholder="Aa" v-model="message"></textarea>
                <PrimaryButton class="" @click="sendMessage">send</PrimaryButton>
            </div>
        </div>
</template>
