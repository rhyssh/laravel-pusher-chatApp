<!-- resources/views/chat.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="chat-page">
        <div class="chat-container">
            <div class="chat-wrapper">
                <!-- Chat Messages -->
                <div class="chat-body">
                    <chat-messages :messages="messages" :current-user-id="{{ Auth::id() }}"></chat-messages>
                </div>

                <!-- Chat Form -->
                <div class="chat-footer">
                    <chat-form :user="currentUser" :typing-user="typingUser" :is-typing-from-other="isTyping"
                        @messagesent="addMessage" @typing="handleTyping"></chat-form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .chat-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chat-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }

        .chat-wrapper {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            height: 600px;
            display: flex;
            flex-direction: column;
        }

        .chat-body {
            flex: 1;
            overflow: hidden;
        }

        .chat-footer {
            flex-shrink: 0;
        }

        @media (max-width: 768px) {
            .chat-page {
                padding: 10px;
            }

            .chat-wrapper {
                height: 500px;
                border-radius: 12px;
            }
        }
    </style>
    <script>
        window.currentUser = @json(auth()->user());
    </script>
@endsection
