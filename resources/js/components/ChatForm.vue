<template>
    <div class="chat-form">
        <div class="form-container">
            <!-- Typing indicator dari user lain -->
            <div
                v-if="
                    isTypingFromOther && typingUser && typingUser.id !== user.id
                "
                class="typing-indicator"
            >
                <div class="typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <span class="typing-text"
                    >{{ typingUser.name }} is typing...</span
                >
            </div>

            <!-- Input form -->
            <form @submit.prevent="sendMessage" class="message-form">
                <div class="input-wrapper">
                    <!-- Message input -->
                    <div class="input-container">
                        <textarea
                            ref="messageInput"
                            v-model="newMessage"
                            @keydown="handleKeyDown"
                            @input="handleInput"
                            placeholder="Type your message..."
                            class="message-input"
                            rows="1"
                            :disabled="isSending"
                        ></textarea>

                        <!-- Emoji button -->
                        <button
                            type="button"
                            @click="toggleEmojiPicker"
                            class="emoji-button"
                            :disabled="isSending"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                            >
                                <circle cx="12" cy="12" r="10" />
                                <path d="m9 9 1.5 1.5L12 9l1.5 1.5L15 9" />
                                <path d="M8 15s1.5 2 4 2 4-2 4-2" />
                            </svg>
                        </button>
                    </div>

                    <!-- Send button -->
                    <button
                        type="submit"
                        :disabled="!canSend"
                        class="send-button"
                        :class="{ sending: isSending }"
                    >
                        <svg
                            v-if="!isSending"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                        >
                            <line x1="22" y1="2" x2="11" y2="13" />
                            <polygon points="22,2 15,22 11,13 2,9 22,2" />
                        </svg>
                        <div v-else class="loading-spinner"></div>
                    </button>
                </div>

                <!-- Character counter -->
                <div v-if="newMessage.length > 0" class="message-info">
                    <span
                        class="character-count"
                        :class="{ warning: newMessage.length > 500 }"
                    >
                        {{ newMessage.length }}/1000
                    </span>
                </div>
            </form>

            <!-- Quick emoji reactions -->
            <div v-if="showEmojiPicker" class="emoji-picker">
                <div class="emoji-grid">
                    <button
                        v-for="emoji in quickEmojis"
                        :key="emoji"
                        @click="addEmoji(emoji)"
                        class="emoji-item"
                    >
                        {{ emoji }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
function debounce(func, delay) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), delay);
    };
}

export default {
    name: "ChatForm",
    props: {
        user: {
            type: Object,
            required: true,
        },
        typingUser: {
            type: Object,
            default: null,
        },
        isTypingFromOther: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            newMessage: "",
            isSending: false,
            showEmojiPicker: false,
            quickEmojis: [
                "😀",
                "😂",
                "😍",
                "🤔",
                "👍",
                "👎",
                "❤️",
                "🎉",
                "😢",
                "😮",
                "🔥",
                "💯",
            ],
        };
    },
    computed: {
        canSend() {
            return (
                this.newMessage.trim().length > 0 &&
                this.newMessage.length <= 1000 &&
                !this.isSending
            );
        },
    },
    methods: {
        async sendMessage() {
            if (!this.canSend) return;

            this.isSending = true;

            try {
                this.$emit("messagesent", {
                    user: this.user,
                    message: this.newMessage.trim(),
                });

                this.newMessage = "";
                this.showEmojiPicker = false;
                this.resetTextareaHeight();

                // Kirim isTyping false saat kirim pesan
                this.emitTyping(false);

                await new Promise((resolve) => setTimeout(resolve, 300));
            } catch (error) {
                console.error("Error sending message:", error);
            } finally {
                this.isSending = false;
                this.$refs.messageInput.focus();
            }
        },

        handleKeyDown(event) {
            if (event.key === "Enter" && !event.shiftKey) {
                event.preventDefault();
                this.sendMessage();
            }
        },

        handleInput() {
            this.autoResizeTextarea();
            this.emitTypingDebounced(true);

            if (this.newMessage.trim().length === 0) {
                this.emitTypingDebounced(false);
            }
        },

        emitTyping(isTyping) {
            this.$emit("typing", {
                user: this.user,
                isTyping: isTyping,
            });
        },

        autoResizeTextarea() {
            const textarea = this.$refs.messageInput;
            if (textarea) {
                textarea.style.height = "auto";
                textarea.style.height =
                    Math.min(textarea.scrollHeight, 120) + "px";
            }
        },

        resetTextareaHeight() {
            const textarea = this.$refs.messageInput;
            if (textarea) {
                textarea.style.height = "auto";
            }
        },

        toggleEmojiPicker() {
            this.showEmojiPicker = !this.showEmojiPicker;
        },

        addEmoji(emoji) {
            this.newMessage += emoji;
            this.showEmojiPicker = false;
            this.$refs.messageInput.focus();
            this.autoResizeTextarea();
        },
    },

    mounted() {
        this.$refs.messageInput.focus();
        this.emitTypingDebounced = debounce(this.emitTyping, 500);
    },

    beforeUnmount() {
        if (this.emitTypingDebounced.cancel) {
            this.emitTypingDebounced.cancel();
        }
    },
};
</script>

<style scoped>
.chat-form {
    background: #ffffff;
    border-top: 1px solid #e5e7eb;
    padding: 16px 20px;
}

.form-container {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.typing-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: #f3f4f6;
    border-radius: 20px;
    align-self: flex-start;
}

.typing-dots {
    display: flex;
    gap: 2px;
}

.typing-dots span {
    width: 4px;
    height: 4px;
    background: #6b7280;
    border-radius: 50%;
    animation: typing 1.4s infinite ease-in-out;
}

.typing-dots span:nth-child(1) {
    animation-delay: -0.32s;
}

.typing-dots span:nth-child(2) {
    animation-delay: -0.16s;
}

.typing-text {
    font-size: 12px;
    color: #6b7280;
    font-style: italic;
}

.message-form {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.input-wrapper {
    display: flex;
    align-items: flex-end;
    gap: 12px;
    background: #f9fafb;
    border: 2px solid #e5e7eb;
    border-radius: 24px;
    padding: 8px 12px;
    transition: all 0.2s ease;
}

.input-wrapper:focus-within {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.input-container {
    flex: 1;
    display: flex;
    align-items: flex-end;
    gap: 8px;
}

.message-input {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    font-size: 14px;
    line-height: 1.4;
    resize: none;
    min-height: 20px;
    max-height: 120px;
    padding: 6px 0;
    color: #374151;
}

.message-input::placeholder {
    color: #9ca3af;
}

.message-input:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.emoji-button {
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    border-radius: 50%;
    color: #6b7280;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.emoji-button:hover:not(:disabled) {
    background: #e5e7eb;
    color: #374151;
}

.emoji-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.emoji-button svg {
    width: 20px;
    height: 20px;
}

.send-button {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.send-button:hover:not(:disabled) {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.send-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.send-button.sending {
    background: #9ca3af;
}

.send-button svg {
    width: 18px;
    height: 18px;
}

.loading-spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top: 2px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.message-info {
    display: flex;
    justify-content: flex-end;
    padding: 0 12px;
}

.character-count {
    font-size: 11px;
    color: #6b7280;
    transition: color 0.2s ease;
}

.character-count.warning {
    color: #ef4444;
}

.emoji-picker {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 12px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    position: relative;
}

.emoji-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 8px;
}

.emoji-item {
    background: none;
    border: none;
    font-size: 20px;
    padding: 8px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s ease;
}

.emoji-item:hover {
    background: #f3f4f6;
}

/* Animations */
@keyframes typing {
    0%,
    80%,
    100% {
        transform: scale(0);
    }
    40% {
        transform: scale(1);
    }
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .chat-form {
        padding: 12px 16px;
    }

    .input-wrapper {
        padding: 6px 10px;
    }

    .send-button {
        width: 36px;
        height: 36px;
    }

    .emoji-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}
</style>
