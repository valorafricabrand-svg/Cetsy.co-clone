@extends('layouts.app')

@section('title', 'Order Chat')

@section('content')
<style>
    body { background: #e5eee9 !important; }
    .wa-app-container {
        max-width: 600px;
        margin: 2rem auto;
        background: #f6fffb;
        box-shadow: 0 4px 32px 0 rgba(18,24,40,.09), 0 1.5px 3px rgba(15,30,90,.06);
        border-radius: 18px;
        overflow: hidden;
        min-height: 550px;
        display: flex;
        flex-direction: column;
    }
    .wa-header {
        background: #079A29;
        color: #fff;
        padding: 1rem 1.5rem .8rem 1.5rem;
        border-bottom: 1px solid #065C23;
        display: flex;
        align-items: center;
        gap: 1rem;
        min-height: 60px;
    }
    .wa-header .wa-title {
        font-size: 1.17rem;
        font-weight: 600;
        letter-spacing: .02rem;
        flex: 1;
    }
    .wa-header .wa-order-id {
        font-size: 0.93rem;
        font-weight: 400;
        color: #c2e9d6;
    }
    .wa-chat-bg {
        flex: 1 1 auto;
        background: url('https://raw.githubusercontent.com/rajathkmp/whatsapp-chat-backgrounds/main/whatsapp-bg-light.png') center center repeat;
        background-size: cover;
        padding: 1.2rem 1rem .8rem 1.1rem;
        overflow-y: auto;
        position: relative;
    }
    .wa-bubble {
        display: inline-block;
        position: relative;
        margin: .28rem 0;
        padding: .65rem 1.1rem .85rem 1rem;
        font-size: 1.04rem;
        max-width: 82vw;
        min-width: 28px;
        word-break: break-word;
        border-radius: 8px;
        line-height: 1.5;
        transition: box-shadow 0.13s, background 0.18s;
        cursor: pointer;
        box-shadow: 0 2px 5px rgba(39,174,96,0.07);
    }
    .wa-bubble.me {
        background: #25d366;
        color: #fff;
        margin-left: auto;
        margin-right: .6rem;
        border-bottom-right-radius: 2px;
        border-top-right-radius: 14px;
        border-top-left-radius: 12px;
        border-bottom-left-radius: 13px;
        border: 1px solid #1ab957;
        box-shadow: 0 2px 8px rgba(39,174,96,0.10);
    }
    .wa-bubble.them {
        background: #fff;
        color: #333;
        margin-right: auto;
        margin-left: .6rem;
        border-bottom-left-radius: 2px;
        border-top-left-radius: 14px;
        border-top-right-radius: 12px;
        border-bottom-right-radius: 13px;
        border: 1px solid #e5e5e5;
        box-shadow: 0 2px 8px rgba(30,65,90,0.08);
    }
    .wa-bubble.me:hover,
    .wa-bubble.them:hover {
        filter: brightness(.97);
        box-shadow: 0 4px 16px rgba(37,211,102,0.12);
    }
    .wa-username {
        font-size: .94rem;
        font-weight: 600;
        color: #155724;
        margin-bottom: .12rem;
        letter-spacing: .01rem;
    }
    .wa-bubble.me .wa-username {
        color: #d2ffe3;
        font-weight: 500;
    }
    .wa-meta {
        font-size: .78rem;
        color: #e2ffe8;
        opacity: .85;
        position: absolute;
        right: .85rem;
        bottom: .3rem;
        white-space: nowrap;
    }
    .wa-meta.left {
        left: .85rem;
        right: unset;
        color: #a2b4a7;
        text-align: left;
    }
    .wa-footer {
        padding: .75rem 1.1rem;
        background: #f3f7f2;
        border-top: 1px solid #b7d7c4;
        display: flex;
        align-items: center;
        gap: .7rem;
    }
    .wa-footer input[type="text"] {
        border: none;
        background: #e5f6ed;
        border-radius: 19px;
        padding: .63rem 1.1rem;
        font-size: 1.09rem;
        flex: 1 1 auto;
        margin-right: .5rem;
        box-shadow: 0 1.5px 5px rgba(40,120,40,.05);
    }
    .wa-footer input:focus {
        outline: none;
        background: #fff;
    }
    .wa-footer button {
        border-radius: 100rem !important;
        background: #079A29;
        color: #fff;
        font-weight: 600;
        padding: .53rem 1.33rem;
        font-size: 1.14rem;
        border: none;
        box-shadow: 0 2px 8px rgba(37,211,102,0.10);
        transition: background .12s;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .wa-footer button:disabled {
        background: #c2efcb;
        color: #f2f2f2;
    }
    .wa-footer button:hover:enabled {
        background: #25d366;
        color: #fff;
    }
    @media (max-width: 800px) {
        .wa-app-container { max-width: 99vw; }
    }
    @media (max-width: 600px) {
        .wa-header { padding: .7rem .7rem; font-size: .98rem; }
        .wa-chat-bg { padding: .6rem .2rem .8rem .23rem;}
        .wa-footer { padding: .29rem .4rem; }
        .wa-bubble { font-size: .96rem; max-width: 96vw;}
    }
</style>

<div class="content">
<div class="wa-app-container" x-data="chatComponent()" x-init="init()">
    <!-- Header -->
    <div class="wa-header">
        <svg width="32" height="32" viewBox="0 0 32 32" style="border-radius:7px; background:#fff1; margin-right:7px;">
            <circle cx="16" cy="16" r="16" fill="#25d366"/>
            <path fill="#fff" d="M22.67 18.48c-.31-.16-1.82-.9-2.1-1-..."/>
        </svg>
        <div class="wa-title">Order Chat</div>
        <div class="wa-order-id">Order #{{ $order->id }}</div>
    </div>
    <!-- Chat area -->
    <div class="wa-chat-bg" x-ref="chatBox" style="height: 400px;">
        <template x-for="(msg, idx) in messages" :key="msg.id">
            <div :class="['d-flex', msg.user.id == myId ? 'justify-content-end' : 'justify-content-start']" style="width:100%;">
                <div :class="['wa-bubble', msg.user.id == myId ? 'me' : 'them']">
                    <template x-if="idx == 0 || messages[idx-1].user.id !== msg.user.id">
                        <div class="wa-username" x-text="msg.user.id == myId ? 'You' : msg.user.name"></div>
                    </template>
                    <div x-text="msg.body"></div>
                    <div :class="['wa-meta', msg.user.id == myId ? '' : 'left']"
                         x-text="formatTime(msg.created_at)">
                    </div>
                </div>
            </div>
        </template>
        <template x-if="loading">
            <div class="text-center text-muted py-2">Loading messages...</div>
        </template>
    </div>
    <!-- Footer/Input -->
    <form @submit.prevent="sendMessage()" class="wa-footer">
        @csrf
        <input type="text"
               x-model="newMessage"
               placeholder="Type a message"
               autocomplete="off"
               required
               @keydown.enter.exact.prevent="sendMessage()">
        <button :disabled="sending || !newMessage.trim()" type="submit" title="Send">
            <i class="bi bi-send-fill"></i>
        </button>
    </form>
</div>
</div>
@endsection

@push('scripts')
<!-- Alpine.js and Bootstrap Icons CDN -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<script>
function chatComponent() {
    return {
        fetchUrl: @json(route('orders.chat.fetch', $order->id)),
        sendUrl: @json(route('orders.chat.send', $order->id)),
        myId: @json(auth()->id()),
        messages: [],
        newMessage: '',
        loading: true,
        sending: false,
        lastFetchedId: null,
        pollInterval: null,

        init() {
            this.loadMessages();
            this.pollInterval = setInterval(() => this.fetchNewMessages(), 1500);
        },
        scrollToBottom() {
            this.$refs.chatBox.scrollTop = this.$refs.chatBox.scrollHeight;
        },
        loadMessages() {
            this.loading = true;
            fetch(this.fetchUrl, { headers: { 'Accept': 'application/json' } })
                .then(res => res.json())
                .then(data => {
                    this.messages = data;
                    if (this.messages.length)
                        this.lastFetchedId = this.messages[this.messages.length - 1].id;
                    this.loading = false;
                    this.$nextTick(() => this.scrollToBottom());
                });
        },
        fetchNewMessages() {
            if (!this.lastFetchedId) return this.loadMessages();
            fetch(`${this.fetchUrl}?after=${this.lastFetchedId}`, { headers: { 'Accept': 'application/json' } })
                .then(res => res.json())
                .then(data => {
                    if (data.length) {
                        this.messages.push(...data);
                        this.lastFetchedId = this.messages[this.messages.length - 1].id;
                        this.$nextTick(() => this.scrollToBottom());
                    }
                });
        },
        sendMessage() {
            if (!this.newMessage.trim() || this.sending) return;
            let msgBody = this.newMessage;
            this.sending = true;

            // Optimistic UI (show immediately)
            let tempId = 't' + Date.now();
            let myUser = {id: this.myId, name: "You"};
            let tempMsg = {
                id: tempId,
                user: myUser,
                body: msgBody,
                created_at: new Date().toISOString()
            };
            this.messages.push(tempMsg);
            this.$nextTick(() => this.scrollToBottom());

            fetch(this.sendUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ body: msgBody })
            })
            .then(res => {
                if (!res.ok) throw new Error('Failed to send');
                return res.json();
            })
            .then(serverMsg => {
                // Replace temp msg with real one from server
                let idx = this.messages.findIndex(m => m.id === tempId);
                if (idx !== -1) this.messages[idx] = serverMsg;
                this.lastFetchedId = serverMsg.id;
                this.$nextTick(() => this.scrollToBottom());
            })
            .catch(err => {
                alert('Could not send: ' + err.message);
            })
            .finally(() => {
                this.newMessage = '';
                this.sending = false;
            });
        },
        formatTime(dt) {
            let d = new Date(dt);
            return d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        }
    }
}
</script>
@endpush
