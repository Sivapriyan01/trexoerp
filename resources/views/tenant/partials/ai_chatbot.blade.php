<div x-data="{ 
    isOpen: false, 
    messages: [
        { role: 'bot', text: 'Hello! I am your trexoerp AI Assistant. How can I help you today?' }
    ], 
    userInput: '', 
    isTyping: false,
    async sendMessage() {
        if (!this.userInput.trim()) return;
        
        const text = this.userInput;
        this.messages.push({ role: 'user', text: text });
        this.userInput = '';
        this.isTyping = true;
        
        try {
            const response = await fetch('{{ route('tenant.ai.chat') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ message: text })
            });
            
            const data = await response.json();
            this.isTyping = false;
            this.messages.push({ 
                role: 'bot', 
                text: data.text, 
                action: data.action,
                suggestions: data.suggestions 
            });
            
            this.$nextTick(() => {
                const container = this.$refs.chatContainer;
                container.scrollTop = container.scrollHeight;
            });
        } catch (error) {
            this.isTyping = false;
            this.messages.push({ role: 'bot', text: 'Sorry, I encountered an error. Please try again.' });
        }
    },
    triggerAction(action) {
        if (action.route) {
            window.location.href = action.route;
        }
    }
}" class="ai-chatbot-container fixed bottom-3 right-3 z-[200]">

    <!-- Chat Toggle Button -->
    <button @click="isOpen = !isOpen"
        class="w-14 h-14 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full shadow-2xl flex items-center justify-center transition-all transform hover:scale-110 active:scale-95 group relative">
        <div
            class="absolute inset-0 bg-indigo-400 rounded-full animate-ping opacity-20 group-hover:opacity-0 transition-opacity pointer-events-none">
        </div>
        <svg x-show="!isOpen" class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 pointer-events-none" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-transition>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
        <svg x-show="isOpen" x-cloak class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 pointer-events-none" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-transition>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    <!-- Chat Window -->
    <div x-show="isOpen" x-cloak x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-8 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute bottom-20 right-0 w-[350px] md:w-[400px] h-[500px] md:h-[600px] bg-white dark:bg-slate-900 rounded-[3rem] shadow-2xl border border-slate-100 dark:border-slate-800 flex flex-col overflow-hidden">

        <!-- Header -->
        <div
            class="p-8 bg-gradient-to-br from-indigo-600 via-violet-600 to-blue-600 text-white flex items-center gap-5 relative overflow-hidden shrink-0">
            <div
                class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10">
            </div>
            <div
                class="relative w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-xl flex items-center justify-center border border-white/30 shadow-inner">
                <img src="/assets/images/ai-bot.png" class="w-12 h-12 rounded-xl object-cover shadow-lg" alt="AI">
                <div
                    class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 border-2 border-white rounded-full animate-pulse">
                </div>
            </div>
            <div class="relative">
                <h3 class="text-lg font-black uppercase tracking-tight leading-none">Smart Assistant</h3>
                <p class="text-[10px] font-bold opacity-80 uppercase tracking-[0.2em] mt-1">trexoerp
                    AI v2.0</p>
            </div>
        </div>

        <!-- Messages Container -->
        <div x-ref="chatContainer"
            class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar bg-slate-50/50 dark:bg-slate-900">
            <template x-for="(msg, index) in messages" :key="index">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.role === 'user' ? 'bg-white dark:bg-slate-800 text-black dark:text-white border border-indigo-100 dark:border-slate-700 shadow-md rounded-2xl rounded-tr-none' : 'bg-white dark:bg-slate-800 text-black dark:text-slate-200 rounded-2xl rounded-tl-none border border-slate-100 dark:border-slate-700 shadow-sm'"
                        class="max-w-[85%] p-4 text-xs font-black leading-relaxed">
                        <span x-html="msg.text.replace(/\n/g, '<br>')"></span>

                        <!-- Action Button -->
                        <template x-if="msg.action">
                            <button @click="triggerAction(msg.action)"
                                class="mt-3 w-full py-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-100 transition-all flex items-center justify-center gap-2">
                                <span x-text="msg.action.label"></span>
                                <kbd x-if="msg.action.shortcut"
                                    class="bg-white/50 px-1.5 py-0.5 rounded border border-indigo-200"
                                    x-text="msg.action.shortcut"></kbd>
                            </button>
                        </template>

                        <!-- Suggestions -->
                        <template x-if="msg.suggestions">
                            <div class="mt-3 flex flex-wrap gap-2">
                                <template x-for="s in msg.suggestions" :key="s">
                                    <button @click="userInput = s; sendMessage()"
                                        class="px-3 py-1.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-600 dark:text-slate-300 rounded-lg text-[9px] font-black uppercase tracking-tight transition-all">
                                        <span x-text="s"></span>
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Typing Indicator -->
            <div x-show="isTyping" class="flex justify-start" x-transition>
                <div
                    class="bg-white dark:bg-slate-800 p-4 rounded-2xl rounded-tl-none border border-slate-100 dark:border-slate-700 shadow-sm">
                    <div class="flex gap-1">
                        <span class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce"></span>
                        <span
                            class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce [animation-delay:0.2s]"></span>
                        <span
                            class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce [animation-delay:0.4s]"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-6 bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800">
            <form @submit.prevent="sendMessage()" class="relative">
                <input type="text" x-model="userInput" placeholder="Ask me anything..."
                    class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 pr-12 text-xs font-black text-black dark:text-white focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all">
                <button type="submit"
                    class="absolute right-2 top-2 w-10 h-10 bg-indigo-600 text-white rounded-xl flex items-center justify-center hover:bg-indigo-700 transition-all">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>