<script setup lang="js">
import { index } from '@/actions/App/Http/Controllers/ConversationController';
import { Link, useForm, router } from '@inertiajs/vue3';
import hljs from 'highlight.js';
import 'highlight.js/styles/github-dark.css';
import MarkdownIt from 'markdown-it';
import { nextTick, ref, watch, computed, onMounted } from 'vue';
import { useStream } from '@laravel/stream-vue';

/* Props */
const props = defineProps({
    activeConversationId: Number,
    messages: Array,
    conversations: Array,
    models: Array,
    selectedModel: String,
});

/* Formulaire Inertia */
const form = useForm({
    prompt: '',
    model: props.selectedModel || (props.models.length > 0 ? props.models[0].id : ''),
});

/* Gestion du scroll */
const messagesEnd = ref(null);
const scrollToBottom = () => {
    nextTick(() => {
        if (messagesEnd.value) {
            messagesEnd.value.scrollTop = messagesEnd.value.scrollHeight;
        }
    });
};
watch(() => props.messages, scrollToBottom, { deep: true });

/* Configuration Markdown */
const md = new MarkdownIt({
    highlight: function (str, lang) {
        if (lang && hljs.getLanguage(lang)) {
            try {
                return hljs.highlight(str, { language: lang }).value;
            } catch {}
        }
        return '';
    },
});
const renderMarkdown = (content) => md.render(content || '');

/* --- STREAMING  --- */
const streamUrl = computed(() => {
    return `/conversations/${props.activeConversationId}/stream`;
});

const { data, isStreaming, send } = useStream(streamUrl, {
    onError: (err) => {
        console.error('Erreur streaming :', err);
    },
});

/* Messages locaux pour le rendu */

const localMessages = ref([...props.messages]);


const submit = () => {
    if (!form.prompt.trim() || isStreaming.value) return;

    const prompt = form.prompt;
    const model = form.model || props.selectedModel || props.models[0]?.id;

    form.prompt = '';

    if (!props.activeConversationId) {
        router.post('/conversations', {
            title: prompt,}, {
            preserveState: false,
            onSuccess: (page) => {
                const newId = page.props.activeConversationId;

                sessionStorage.setItem('pendingMessage', JSON.stringify({
                    message: prompt,
                    model,

                }));

                router.visit(`/conversations/${newId}`, {
                    preserveState: false,
                });
            }
        });
        return;
    }

    sendMessage(prompt, model);
};

const sendMessage = (prompt, model) => {
    localMessages.value.push({
        id: Date.now(),
        role: 'user',
        content: prompt,
    });

    data.value = '';

    send({
        message: prompt,
        model,
        temperature: 1.0,
    });
};

onMounted(() => {
    const pending = sessionStorage.getItem('pendingMessage');

    if (pending && props.activeConversationId) {
        const { message, model } = JSON.parse(pending);
        sessionStorage.removeItem('pendingMessage');

        nextTick(() => {
            sendMessage(message, model);
        });
    }
});

const deleteconversation = (id) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette conversation ?')) {
        router.delete(`/conversations/${id}`, {
            onSuccess: () => { window.location.href = '/conversations'; }
        });
    }
};

const updateModel = () => {
    if (!props.activeConversationId) return;
    router.post(`/conversations/${props.activeConversationId}/model`, {
        model: form.model,
    }, { preserveScroll: true });
};

watch(() => props.activeConversationId, () => {
    localMessages.value = [...props.messages];
    data.value = '';

});

watch(
    () => props.messages,
    (messages) => {
        localMessages.value = [...messages];
    },
    { deep: true }
);


watch(data, (value) => {
    if (!value) return;

    const clean = value
        .replace(/\[REASONING\][\s\S]*?\[\/REASONING\]/g, '')
        .trim();

    if (!clean) return;

    const last = localMessages.value.at(-1);

    if (last?.role === 'assistant') {
        last.content = clean;
    } else {
        localMessages.value.push({
            id: 'streaming',
            role: 'assistant',
            content: clean,
        });
    }
});

</script>

<template>
    <div class="flex min-h-screen bg-gradient-to-br from-blue-100 to-green-100">

        <aside class="flex w-72 flex-col border-r border-gray-200 bg-white p-4 shadow-xl">
            <h2 class="mb-4 text-xl font-bold text-gray-800">
                💬 Conversations
            </h2>
            <nav class="flex-1 space-y-2 overflow-y-auto pr-2">
                    <div
                        v-for="c in props.conversations"
                        :key="c.id"
                        class="flex items-center justify-between rounded-xl bg-gray-100 p-3 shadow-sm transition hover:bg-gray-200"
                        :class="{
                            'border-l-4 border-blue-600 bg-blue-100 font-semibold':
                                c.id === props.activeConversationId,
                        }"
                    >
                        <Link
                            :href="index(c.id)"
                            class="flex-1 truncate font-semibold text-gray-800"
                        >
                            {{ c.title }}
                        </Link>
                        <button
                            @click="deleteconversation(c.id)"
                            class="ml-2 rounded-full bg-red-500 px-2 py-1 text-sm font-bold text-white hover:bg-red-600"
                            title="Supprimer la conversation"
                        >
                            🗑️
                        </button>
                    </div>

            </nav>
            <Link
                href="/conversations"
                class="mt-4 w-full rounded-xl bg-purple-600 px-4 py-2 text-center font-bold text-white shadow-md transition hover:bg-purple-700">
                ➕ Nouvelle Conversation
            </Link>
            <Link
                href="/chat/instructions"
                class="mt-2 w-full rounded-xl bg-green-600 px-4 py-2 text-center font-bold text-white shadow-md transition hover:bg-green-700">
                ⚙️ Instructions personnalisées
            </Link>
        </aside>

        <main class="flex-1">
            <div
                class="mx-auto mt-12 max-w-4xl rounded-3xl border border-purple-300 bg-gradient-to-br from-blue-50 to-green-50 p-6 shadow-2xl"
            >
                <div class="mb-6 flex items-center space-x-4">
                    <span class="animate-bounce text-5xl">🧠</span>
                    <h1 class="text-3xl font-extrabold text-gray-900">
                        Chat Éducatif -
                        {{
                            props.activeConversationId
                                ? 'Conversation Existante'
                                : 'Nouvelle Conversation'
                        }}
                    </h1>
                </div>

                <div
                    ref="messagesEnd"
                    class="flex h-96 flex-col gap-4 overflow-y-auto rounded-xl border border-gray-200 bg-white p-4 shadow-inner"
                >
                    <div
                        v-if="!props.messages.length && !isStreaming"
                        class="p-8 text-center text-gray-500"
                    >
                        Commencez une nouvelle conversation!
                    </div>

                    <div
                        v-for="message in localMessages"
                        :key="message.id"
                        :class="{
                            'flex justify-end': message.role === 'user',
                            'flex justify-start': message.role === 'assistant',
                        }"
                    >
                        <div
                            :class="[
                                'animate-fade-in max-w-[75%] rounded-xl p-4 shadow-md',
                                message.role === 'user'
                                ? 'bg-blue-200 text-gray-900'
                                : 'bg-green-200 text-gray-900',
                                ]"
                                >
                                <span class="font-semibold"
                                >{{
                                    message.role === 'user' ? 'Vous' : 'Chat'
                                }}
                                :</span>
                                <div
                                class="prose mt-1 max-w-none prose-slate dark:prose-invert"
                                v-html="renderMarkdown(message.content)"
                                ></div>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submit" class="mt-6 flex flex-col gap-4">
                    <label for="prompt" class="font-medium text-gray-700">Votre Question :</label>
                    <textarea
                        id="prompt"
                        v-model="form.prompt"
                        rows="4"
                        placeholder="Écrivez votre question ici..."
                        class="rounded-xl border border-gray-300 bg-white p-3 text-gray-900 transition focus:border-transparent focus:ring-2 focus:ring-blue-400"
                        :disabled="isStreaming"
                    ></textarea>

                    <div v-if="props.models.length" class="flex w-full flex-col gap-2">
                        <label class="font-medium text-gray-700">Modèle :</label>
                        <select
                            v-model="form.model" @change="updateModel"
                            class="w-full rounded-xl border border-gray-300 bg-white p-2 text-gray-900 transition focus:ring-2 focus:ring-purple-400"
                            :disabled="isStreaming"
                        >
                            <option
                                v-for="m in props.models"
                                :key="m.id"
                                :value="m.id"
                            >
                                {{ m.name }}
                            </option>
                        </select>
                    </div>

                    <button
                        type="submit"
                        :disabled="isStreaming || form.prompt.length === 0"
                        class="mt-4 flex items-center justify-center gap-2 rounded-xl bg-purple-600 px-6 py-3 font-bold text-white shadow-lg shadow-purple-500/50 transition hover:bg-purple-700 disabled:opacity-50"
                    >
                        <span v-if="!isStreaming">Envoyer</span>
                        <span v-else class="flex items-center gap-2">
                            <svg class="h-5 w-5 animate-spin text-white" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            En cours...
                        </span>
                    </button>
                </form>
            </div>
        </main>
    </div>
</template>

<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}
div {
    scroll-behavior: smooth;
}
</style>
