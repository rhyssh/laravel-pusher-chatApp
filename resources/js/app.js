import './bootstrap';
import { createApp } from 'vue';
import axios from 'axios';

import ExampleComponent from './components/ExampleComponent.vue';
import ChatMessages from './components/ChatMessage.vue';
import ChatForm from './components/ChatForm.vue';

const app = createApp({
    data() {
        return {
            messages: [],
            isTyping: false,
            typingUser: null,
            currentUser: window.currentUser || null // misal dari Blade
        };
    },

    created() {
        this.fetchMessages();

        if (window.Echo) {
            window.Echo.channel('chat')
                .listen('MessageSent', (e) => {
                    this.messages.push({
                        message: e.message.message,
                        user: e.user
                    });
                });

            window.Echo.channel('typing')
                .listen('StatusTyping', (e) => {
                    // Jangan tampilkan indikator jika user-nya adalah diri sendiri
                    if (this.currentUser && e.user.id !== this.currentUser.id) {
                        this.typingUser = e.user;
                        this.isTyping = e.isTyping;

                        if (e.isTyping) {
                            // Reset typing timeout agar indikator hilang setelah 2 detik
                            if (this.typingTimeout) {
                                clearTimeout(this.typingTimeout);
                            }
                            this.typingTimeout = setTimeout(() => {
                                this.isTyping = false;
                                this.typingUser = null;
                            }, 1000);
                        }
                    }
                });
        } else {
            console.error('Echo is not defined');
        }
    },

    methods: {
        fetchMessages() {
            axios.get('/messages').then(response => {
                this.messages = response.data;
            });
        },

        addMessage(message) {
            this.messages.push(message);
            axios.post('/messages', message).then(response => {
                console.log(response.data);
            });
        },

        handleTyping({ user, isTyping }) {
            axios.post('/typing', {
                user_id: user.id,
                isTyping: isTyping
            }).catch(err => {
                console.error("Failed to send typing event", err);
            });
        }
    }
});

// Register komponen
app.component('example-component', ExampleComponent);
app.component('chat-messages', ChatMessages);
app.component('chat-form', ChatForm);

app.mount('#app');
