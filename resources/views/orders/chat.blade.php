@extends('theme.'.theme().'.layouts.app')

@section('title', 'Order Chat')

@push('styles')
<style>
    .order-chat-page {
        background: linear-gradient(180deg, #ecfdf5, #f8fafc);
        min-height: 100%;
    }
    .wa-app-container {
        max-width: 760px;
        margin: 0 auto;
        background: #f6fffb;
        box-shadow: 0 4px 32px 0 rgba(18,24,40,.09), 0 1.5px 3px rgba(15,30,90,.06);
        border-radius: 18px;
        overflow: hidden;
        min-height: 560px;
        display: flex;
        flex-direction: column;
        border: 1px solid rgba(15,23,42,.08);
    }
    .wa-header {
        background: #079A29;
        color: #fff;
        padding: 1rem 1.25rem .9rem;
        border-bottom: 1px solid #065C23;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        min-height: 60px;
    }
    .wa-title {
        font-size: 1.05rem;
        font-weight: 700;
        letter-spacing: .01rem;
    }
    .wa-order-id {
        font-size: .85rem;
        font-weight: 500;
        color: #d1fae5;
    }
    .wa-chat-bg {
        flex: 1 1 auto;
        background: radial-gradient(circle at 20% 20%, rgba(16,185,129,.08), transparent 40%), #effaf4;
        padding: 1rem;
        overflow-y: auto;
        position: relative;
    }
    .wa-bubble {
        display: inline-block;
        position: relative;
        margin: .28rem 0;
        padding: .65rem 1.1rem .95rem 1rem;
        font-size: .95rem;
        max-width: min(82vw, 520px);
        min-width: 28px;
        word-break: break-word;
        border-radius: 10px;
        line-height: 1.5;
        box-shadow: 0 2px 6px rgba(39,174,96,0.08);
    }
    .wa-bubble.me {
        background: #25d366;
        color: #fff;
        border-bottom-right-radius: 2px;
        border: 1px solid #1ab957;
    }
    .wa-bubble.them {
        background: #fff;
        color: #334155;
        border-bottom-left-radius: 2px;
        border: 1px solid #e2e8f0;
    }
    .wa-username {
        font-size: .8rem;
        font-weight: 700;
        margin-bottom: .1rem;
    }
    .wa-bubble.me .wa-username { color: #dcfce7; }
    .wa-bubble.them .wa-username { color: #166534; }
    .wa-meta {
        font-size: .7rem;
        opacity: .82;
        position: absolute;
        right: .75rem;
        bottom: .3rem;
        white-space: nowrap;
    }
    .wa-meta.me { color: #dcfce7; }
    .wa-meta.them { color: #64748b; }
    .wa-footer {
        padding: .75rem;
        background: #f1f5f9;
        border-top: 1px solid #cbd5e1;
        display: flex;
        align-items: center;
        gap: .6rem;
    }
    .wa-footer input[type="text"] {
        border: 1px solid #cbd5e1;
        background: #fff;
        border-radius: 999px;
        padding: .62rem .95rem;
        font-size: .95rem;
        flex: 1 1 auto;
    }
    .wa-footer input:focus {
        outline: none;
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16,185,129,.16);
    }
    .wa-send-btn {
        border-radius: 999px;
        background: #059669;
        color: #fff;
        font-weight: 700;
        padding: .56rem .95rem;
        font-size: .92rem;
        border: none;
    }
    .wa-send-btn:disabled {
        background: #94a3b8;
        cursor: not-allowed;
    }
</style>
@endpush

@section('main')
<div class="order-chat-page py-8">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 lg:col-span-3">
                @include('buyer.partials.sidebar')
            </div>

            <div class="col-span-12 lg:col-span-9">
                <div class="mb-3 flex flex-wrap items-center gap-2">
                    <a href="{{ route('orders.show', $order->id) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Back to Order
                    </a>
                </div>

                <div class="wa-app-container" x-data="chatComponent()" x-init="init()">
                    <div class="wa-header">
                        <div class="wa-title">Order Chat</div>
                        <div class="wa-order-id">Order #{{ $order->id }}</div>
                    </div>

                    <div class="wa-chat-bg" x-ref="chatBox" style="height: 420px;">
                        <template x-for="(msg, idx) in messages" :key="msg.id">
                            <div class="flex w-full" :class="msg.user.id == myId ? 'justify-end' : 'justify-start'">
                                <div :class="['wa-bubble', msg.user.id == myId ? 'me' : 'them']">
                                    <template x-if="idx == 0 || messages[idx-1].user.id !== msg.user.id">
                                        <div class="wa-username" x-text="msg.user.id == myId ? 'You' : msg.user.name"></div>
                                    </template>
                                    <div x-text="msg.body"></div>
                                    <div :class="['wa-meta', msg.user.id == myId ? 'me' : 'them']" x-text="formatTime(msg.created_at)"></div>
                                </div>
                            </div>
                        </template>
                        <template x-if="loading">
                            <div class="py-2 text-center text-sm text-slate-500">Loading messages...</div>
                        </template>
                    </div>

                    <form @submit.prevent="sendMessage()" class="wa-footer">
                        @csrf
                        <input type="text" x-model="newMessage" placeholder="Type a message" autocomplete="off" required @keydown.enter.exact.prevent="sendMessage()">
                        <button :disabled="sending || !newMessage.trim()" type="submit" class="wa-send-btn" title="Send">
                            Send
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
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

            let tempId = 't' + Date.now();
            let myUser = {id: this.myId, name: 'You'};
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
