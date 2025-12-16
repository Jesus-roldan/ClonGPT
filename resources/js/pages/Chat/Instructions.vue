<script setup>
import { useForm, Link } from '@inertiajs/vue3'


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

const commandsList = {
    '/météo': 'Affiche la météo actuelle et les prévisions',
    '/citation': 'Génère une citation inspirante',
    '/résumé': 'Résume le texte fourni',
    '/review': 'Analyse et critique du code',
    '/explain': 'Explique comme si j’avais 5 ans',
    '/feedback': 'Formule un retour constructif',
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
      <section class="rounded-2xl bg-white p-6 shadow-md space-y-4">
        <h2 class="text-xl font-bold text-gray-800">⌨️ Commandes personnalisées</h2>

        <div
          v-for="(desc, cmd) in commandsList"
          :key="cmd"
          class="flex items-center gap-3 rounded-xl bg-gray-50 p-3 hover:bg-gray-100"
        >
          <input type="checkbox" v-model="form.commands[cmd]" class="h-4 w-4" />
          <div>
            <span class="font-mono font-semibold">{{ cmd }}</span>
            <p class="text-sm text-gray-600">{{ desc }}</p>
          </div>
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
