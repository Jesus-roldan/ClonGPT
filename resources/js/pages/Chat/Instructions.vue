<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import { ref } from 'vue'


const props = defineProps({
    instruction: {
        type: Object,
        default: () => null,
    },
})

const instruction = props.instruction ?? {}


const form = useForm({
    about: instruction.about ?? {
        profession: '',
        interests: '',
        level: '',
        goals: '',
    },
    behaviour: instruction.behaviour ?? {
        tone: '',
        format: '',
        length: '',
        style: '',
    },
    commands: instruction.commands ?? {},
})

const newCommand = ref('')
const newDefinition = ref('')

const addCommand = () => {
  if (!newCommand.value.startsWith('/')) return
  if (!newDefinition.value) return

  form.commands[newCommand.value] = newDefinition.value

  newCommand.value = ''
  newDefinition.value = ''
}

const submit = () => {

    form.post('/chat/instructions')
}
</script>

<template>
<div class="min-h-screen bg-gradient-to-br from-blue-100 to-green-100 p-8">

  <div class="mx-auto max-w-4xl rounded-3xl border border-purple-300 bg-gradient-to-br from-blue-50 to-green-50 p-8 shadow-2xl">

    <div class="mb-8 flex items-center gap-4">
      <span class="text-5xl animate-bounce">⚙️</span>
      <div>
        <h1 class="text-3xl font-extrabold text-gray-900">
          Instructions personnalisées
        </h1>
        <p class="text-gray-600">Personnalisez le comportement de votre assistant</p>
      </div>
    </div>

    <form @submit.prevent="submit" class="space-y-10">

      <!-- ================= À PROPOS DE VOUS ================= -->
      <section class="rounded-2xl bg-white p-6 shadow-md space-y-4">
        <h2 class="text-xl font-bold text-gray-800">👤 À propos de vous</h2>

        <div>
          <label class="font-medium text-gray-700">Votre profession</label>
          <input
            v-model="form.about.profession"
            class="w-full rounded-xl border border-gray-300 bg-white p-3 text-gray-900
                   transition focus:outline-none focus:ring-2 focus:ring-purple-400"
            placeholder="Ex: Étudiant, développeur web..."
          />
          <p class="mt-1 text-sm text-gray-500">Réponses adaptées à votre niveau</p>
        </div>

        <div>
          <label class="font-medium text-gray-700">Vos centres d’intérêt</label>
          <input
            v-model="form.about.interests"
            class="w-full rounded-xl border border-gray-300 bg-white p-3 text-gray-900
                   transition focus:outline-none focus:ring-2 focus:ring-purple-400"
            placeholder="Ex: Vue.js, IA, UX..."
          />
          <p class="mt-1 text-sm text-gray-500">Exemples et analogies pertinents</p>
        </div>

        <div>
          <label class="font-medium text-gray-700">Niveau d’expertise</label>
          <select
            v-model="form.about.level"
            class="w-full rounded-xl border border-gray-300 bg-white p-3 text-gray-900
                   transition focus:outline-none focus:ring-2 focus:ring-purple-400"
          >
            <option value="">-- Choisir --</option>
            <option>Débutant</option>
            <option>Intermédiaire</option>
            <option>Avancé</option>
          </select>
          <p class="mt-1 text-sm text-gray-500">Explications ni trop simples ni trop complexes</p>
        </div>

        <div>
          <label class="font-medium text-gray-700">Vos objectifs</label>
          <textarea
            v-model="form.about.goals"
            rows="3"
            class="w-full rounded-xl border border-gray-300 bg-white p-3 text-gray-900
                   transition focus:outline-none focus:ring-2 focus:ring-purple-400"
            placeholder="Ex: Réussir mes examens, progresser en Vue..."
          />
          <p class="mt-1 text-sm text-gray-500">Aide orientée vers vos vrais besoins</p>
        </div>
      </section>

      <!-- ================= COMPORTEMENT ================= -->
      <section class="rounded-2xl bg-white p-6 shadow-md space-y-4">
        <h2 class="text-xl font-bold text-gray-800">🎭 Comportement de l’assistant</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="font-medium">Ton</label>
            <select v-model="form.behaviour.tone" class="w-full rounded-xl border border-gray-300 p-3">
              <option value="">-- Choisir --</option>
              <option>Formel</option>
              <option>Décontracté</option>
              <option>Technique</option>
              <option>Pédagogique</option>
            </select>
          </div>

          <div>
            <label class="font-medium">Format</label>
            <select v-model="form.behaviour.format" class="w-full rounded-xl border border-gray-300 p-3">
              <option value="">-- Choisir --</option>
              <option>Listes</option>
              <option>Paragraphes</option>
              <option>Tableaux</option>
              <option>Code first</option>
            </select>
          </div>

          <div>
            <label class="font-medium">Longueur</label>
            <select v-model="form.behaviour.length" class="w-full rounded-xl border border-gray-300 p-3">
              <option value="">-- Choisir --</option>
              <option>Concis</option>
              <option>Détaillé</option>
              <option>Va droit au but</option>
            </select>
          </div>

          <div>
            <label class="font-medium">Style d’explication</label>
            <select v-model="form.behaviour.style" class="w-full rounded-xl border border-gray-300 p-3">
              <option value="">-- Choisir --</option>
              <option>Analogies</option>
              <option>Exemples pratiques</option>
              <option>Théorie</option>
            </select>
          </div>
        </div>
      </section>

      <!-- ================= COMMANDES ================= -->
      <section class="rounded-2xl bg-white p-6 shadow-md space-y-6">
        <h2 class="text-xl font-bold text-gray-800">⌨️ Commandes personnalisées</h2>

        <!-- Ajout d’une commande -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input
            v-model="newCommand"
            placeholder="/résumé"
            class="rounded-xl border border-gray-300 p-3 font-mono"
            />

            <input
            v-model="newDefinition"
            placeholder="Définition de la commande"
            class="md:col-span-2 rounded-xl border border-gray-300 p-3"
            />
        </div>

        <button
            type="button"
            @click="addCommand"
            class="rounded-xl bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700"
        >
            ➕ Ajouter la commande
        </button>

        <div
            v-for="(definition, command) in form.commands"
            :key="command"
            class="flex justify-between items-start rounded-xl bg-gray-50 p-4"
        >
            <div>
            <p class="font-mono font-semibold">{{ command }}</p>
            <p class="text-sm text-gray-600">{{ definition }}</p>
            </div>

            <button
            type="button"
            @click="delete form.commands[command]"
            class="text-red-500 hover:text-red-700"
            >
            🗑️
            </button>
        </div>
        </section>

      <!-- ================= BOUTONS ================= -->
      <div class="flex justify-between items-center">
        <Link href="/conversations" class="text-gray-600 hover:underline">
          ← Retour au chat
        </Link>

        <button
          type="submit"
          :disabled="form.processing"
          class="rounded-xl bg-purple-600 px-6 py-3 font-bold text-white shadow-lg shadow-purple-500/50
                 transition hover:bg-purple-700 disabled:opacity-50"
        >
          💾 Enregistrer les instructions
        </button>
      </div>
    </form>
  </div>
</div>
</template>
