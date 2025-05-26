<template>
    <div class="chat-container">
        <!-- Chat Header -->
        <div class="chat-header">
            <div class="header-content">
                <div class="chat-info">
                    <h3 class="chat-title">Chat Messages</h3>
                    <span class="message-count"
                        >{{ messages.length }} messages</span
                    >
                </div>
                <div class="online-indicator">
                    <div class="status-dot"></div>
                    <span class="status-text">Online</span>
                </div>
            </div>
        </div>

        <!-- Messages Container -->
        <div class="messages-wrapper" ref="messagesContainer">
            <ul class="messages-list">
                <li
                    v-for="(message, index) in messages"
                    :key="message.id"
                    class="message-item"
                    :class="getMessageClass(message, index)"
                >
                    <div class="message-wrapper">
                        <!-- Avatar (only show for other users and at group end) -->
                        <div
                            v-if="shouldShowAvatar(message, index)"
                            class="avatar-container"
                        >
                            <div
                                class="user-avatar"
                                :style="{
                                    backgroundColor: getUserColor(
                                        message.user.name
                                    ),
                                }"
                            >
                                {{ getInitials(message.user.name) }}
                            </div>
                        </div>

                        <!-- Message Content -->
                        <div class="message-content">
                            <!-- User name and timestamp -->
                            <div
                                v-if="shouldShowHeader(message, index)"
                                class="message-header"
                            >
                                <span
                                    class="user-name"
                                    :style="{
                                        color: getUserColor(message.user.name),
                                    }"
                                >
                                    {{ message.user.name }}
                                </span>
                                <span class="message-time">
                                    {{ formatTime(message.created_at) }}
                                </span>
                            </div>

                            <!-- Message bubble -->
                            <div
                                class="message-bubble"
                                :class="getBubbleClass(message, index)"
                            >
                                <p class="message-text">
                                    {{ message.message }}
                                </p>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>

            <!-- Empty state -->
            <div v-if="messages.length === 0" class="empty-state">
                <div class="empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.959 8.959 0 01-4.906-1.681L3 21l2.681-5.094A8.959 8.959 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z"
                        />
                    </svg>
                </div>
                <h3 class="empty-title">No messages yet</h3>
                <p class="empty-description">
                    Start the conversation by sending a message!
                </p>
            </div>
        </div>

        <!-- Scroll to bottom button -->
        <button
            v-if="showScrollButton"
            @click="scrollToBottom"
            class="scroll-to-bottom"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 14l-7 7m0 0l-7-7m7 7V3"
                />
            </svg>
        </button>
    </div>
</template>

<script>
export default {
    name: "ChatMessages",
    props: {
        messages: {
            type: Array,
            default: () => [],
        },
        currentUserId: {
            type: [String, Number],
            default: null,
        },
    },
    data() {
        return {
            showScrollButton: false,
            userColors: {},
            
        };
    },
    mounted() {
        this.scrollToBottom();
        this.setupScrollListener();
        this.$nextTick(() => {
            this.scrollToBottom();
        });
        this.prevScrollPos = 0;
        const container = this.$refs.messagesContainer;

        this.scrollHandler = () => {
            const currentScrollPos = container.scrollTop;
            this.isUserScrollingUp = currentScrollPos < this.prevScrollPos;
            this.prevScrollPos = currentScrollPos;
        };

        container.addEventListener("scroll", this.scrollHandler);
    },
    beforeUnmount() {
        this.removeScrollListener();
        const container = this.$refs.messagesContainer;
        if (container && this.scrollHandler) {
            container.removeEventListener('scroll', this.scrollHandler);
        }
    },
    updated() {

        this.$nextTick(() => {
            if (!this.isUserScrollingUp) {
                this.scrollToBottom();
            }
        });
    },
    methods: {
        getMessageClass(message, index) {
            const isCurrentUser = this.isCurrentUser(message);
            return {
                "message-right": isCurrentUser,
                "message-left": !isCurrentUser,
                "message-group-start": this.isGroupStart(message, index),
                "message-group-end": this.isGroupEnd(message, index),
            };
        },

        getBubbleClass(message, index) {
            const isCurrentUser = this.isCurrentUser(message);
            return {
                "bubble-current": isCurrentUser,
                "bubble-other": !isCurrentUser,
                "bubble-group-start": this.isGroupStart(message, index),
                "bubble-group-end": this.isGroupEnd(message, index),
                "bubble-single": this.isSingleMessage(message, index),
            };
        },

        isCurrentUser(message) {
            return this.currentUserId && message.user.id === this.currentUserId;
        },

        shouldShowAvatar(message, index) {
            return (
                !this.isCurrentUser(message) && this.isGroupEnd(message, index)
            );
        },

        shouldShowHeader(message, index) {
            return this.isGroupStart(message, index);
        },

        isGroupStart(message, index) {
            if (index === 0) return true;
            const prevMessage = this.messages[index - 1];
            return prevMessage.user.id !== message.user.id;
        },

        isGroupEnd(message, index) {
            if (index === this.messages.length - 1) return true;
            const nextMessage = this.messages[index + 1];
            return nextMessage.user.id !== message.user.id;
        },

        isSingleMessage(message, index) {
            return (
                this.isGroupStart(message, index) &&
                this.isGroupEnd(message, index)
            );
        },

        getUserColor(userName) {
            if (!this.userColors[userName]) {
                const colors = [
                    "#3B82F6",
                    "#EF4444",
                    "#10B981",
                    "#F59E0B",
                    "#8B5CF6",
                    "#EC4899",
                    "#06B6D4",
                    "#84CC16",
                    "#F97316",
                    "#6366F1",
                ];
                const index = userName.length % colors.length;
                this.userColors[userName] = colors[index];
            }
            return this.userColors[userName];
        },

        getInitials(name) {
            return name
                .split(" ")
                .map((word) => word[0])
                .join("")
                .toUpperCase()
                .slice(0, 2);
        },

        formatTime(timestamp) {
            if (!timestamp) return "";
            const date = new Date(timestamp);
            const now = new Date();
            const diffInHours = (now - date) / (1000 * 60 * 60);

            if (diffInHours < 24) {
                return date.toLocaleTimeString([], {
                    hour: "2-digit",
                    minute: "2-digit",
                });
            } else {
                return date.toLocaleDateString();
            }
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.messagesContainer;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        },

        setupScrollListener() {
            const container = this.$refs.messagesContainer;
            if (!container) return;

            // Simpan reference ke handler agar bisa dihapus nanti
            this.scrollHandler = () => {
                const { scrollTop, scrollHeight, clientHeight } = container;
                this.showScrollButton =
                    scrollTop < scrollHeight - clientHeight - 100;
            };

            container.addEventListener("scroll", this.scrollHandler);
        },

        removeScrollListener() {
            const container = this.$refs.messagesContainer;
            if (container && this.scrollHandler) {
                container.removeEventListener("scroll", this.scrollHandler);
            }
        },
    },
};
</script>

<style scoped>
.chat-container {
    display: flex;
    flex-direction: column;
    height: 500px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    position: relative;
}

.chat-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 16px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-info {
    display: flex;
    flex-direction: column;
}

.chat-title {
    font-size: 18px;
    font-weight: 600;
    margin: 0;
}

.message-count {
    font-size: 12px;
    opacity: 0.8;
    margin-top: 2px;
}

.online-indicator {
    display: flex;
    align-items: center;
    gap: 6px;
}

.status-dot {
    width: 8px;
    height: 8px;
    background: #10b981;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

.status-text {
    font-size: 12px;
    opacity: 0.9;
}

.messages-wrapper {
    flex: 1;
    overflow-y: auto;
    background: #f8fafc;
    scroll-behavior: smooth;
}

.messages-list {
    list-style: none;
    margin: 0;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.message-item {
    display: flex;
    animation: slideIn 0.3s ease-out;
}

.message-left {
    justify-content: flex-start;
}

.message-right {
    justify-content: flex-end;
}

.message-wrapper {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    max-width: 70%;
}

.message-right .message-wrapper {
    flex-direction: row-reverse;
}

.avatar-container {
    flex-shrink: 0;
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
    font-weight: 600;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.message-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.message-right .message-content {
    align-items: flex-end;
}

.message-header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0 12px;
}

.message-right .message-header {
    flex-direction: row-reverse;
}

.user-name {
    font-size: 12px;
    font-weight: 600;
}

.message-time {
    font-size: 11px;
    color: #6b7280;
}

.message-bubble {
    padding: 10px 14px;
    border-radius: 18px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
    position: relative;
}

.bubble-other {
    background: #ffffff;
    color: #374151;
    border: 1px solid #e5e7eb;
}

.bubble-current {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.bubble-group-start {
    border-top-left-radius: 18px;
    border-top-right-radius: 18px;
}

.bubble-group-end {
    border-bottom-left-radius: 18px;
    border-bottom-right-radius: 18px;
}

.bubble-other.bubble-group-start,
.bubble-other.bubble-single {
    border-top-left-radius: 4px;
}

.bubble-other.bubble-group-end,
.bubble-other.bubble-single {
    border-bottom-left-radius: 4px;
}

.bubble-current.bubble-group-start,
.bubble-current.bubble-single {
    border-top-right-radius: 4px;
}

.bubble-current.bubble-group-end,
.bubble-current.bubble-single {
    border-bottom-right-radius: 4px;
}

.message-text {
    margin: 0;
    font-size: 14px;
    line-height: 1.4;
    word-wrap: break-word;
}

.message-group-start {
    margin-top: 12px;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    text-align: center;
    padding: 40px 20px;
}

.empty-icon {
    width: 64px;
    height: 64px;
    color: #9ca3af;
    margin-bottom: 16px;
}

.empty-icon svg {
    width: 100%;
    height: 100%;
}

.empty-title {
    font-size: 18px;
    font-weight: 600;
    color: #374151;
    margin: 0 0 8px 0;
}

.empty-description {
    font-size: 14px;
    color: #6b7280;
    margin: 0;
}

.scroll-to-bottom {
    position: absolute;
    bottom: 16px;
    right: 16px;
    width: 40px;
    height: 40px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 50%;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.scroll-to-bottom:hover {
    background: #5a67d8;
    transform: scale(1.1);
}

.scroll-to-bottom svg {
    width: 20px;
    height: 20px;
}

/* Custom scrollbar */
.messages-wrapper::-webkit-scrollbar {
    width: 6px;
}

.messages-wrapper::-webkit-scrollbar-track {
    background: #f1f5f9;
}

.messages-wrapper::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.messages-wrapper::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Animations */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .chat-container {
        height: 400px;
    }

    .message-wrapper {
        max-width: 85%;
    }

    .messages-list {
        padding: 12px;
    }

    .chat-header {
        padding: 12px 16px;
    }
}
</style>
