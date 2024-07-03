<script setup>
import { Head } from '@inertiajs/vue3'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import MyLink from '@/Components/MyLink.vue'
import RandomWalkPosition from './Partials/RandomWalkPosition.vue'
import RandomWalkFigure from './Partials/RandomWalkFigure.vue'
import RandomWalkCompoundFigure from './Partials/RandomWalkCompoundFigure.vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
  length: Number,
  walk: Array,
  dead_ended: Boolean,
  no_valid_start_position: Boolean,
})

const form = useForm({
  length: props.length,
});

function submit() {
  form.post(route('random-walk.random-walk'));
}

</script>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
export default {
  layout: AppLayout,
}
</script>

<template>
  <div>
    <Head title="Random walk" />

    <h1 class="text-2xl">Random walk</h1>

    <p class="mt-1 text-gray-900 max-w-xl">
      Use this page to generate a random sequence of positions and figures (or, in nerd speak, a random walk on the CasinoGraph).
      You might do this to discover and/or practice new figures combinations that you would not have thought of yourself.
    </p>

    <form class="mt-5 p-4 border rounded-md max-w-xl " @submit.prevent="submit">
      <!-- Length -->
      <div class="w-full max-w-xl">
        <InputLabel for="length" value="Length (how many figures to include)" />
        <TextInput
        min="1"
        id="length"
        type="number"
        class="inline-block w-24 py-1.5"
        v-model="form.length"
        required
      />
        <InputError class="mt-2" :message="form.errors.length" />
      </div>

      <PrimaryButton
        class="mt-4"
        :disabled="form.processing"
      >
        Generate
      </PrimaryButton>

    </form>

    <!-- Walk contents -->
    <div v-if="walk && walk.length > 0" class="mt-4 max-w-xl space-y-3">
      <div v-for="stop in walk">
        <RandomWalkPosition v-if="stop.type === 'position'" :position="stop.payload" />
        <RandomWalkFigure v-else-if="stop.type === 'figure'" :figure="stop.payload" />
        <RandomWalkCompoundFigure v-else-if="stop.type === 'compound_figure'" :compound_figure="stop.payload" />
      </div>  

      <div 
        v-if="walk && walk.length > 0 && dead_ended"
        class="!mt-3 pt-2 text-center max-w-sm mx-auto border-t border-yellow-900 border-opacity-50"
      >
        <ExclamationTriangleIcon class="w-6 h-6 text-yellow-800 shrink-0 mx-auto"/>
        <div class="text-yellow-900">
          <p>The random walk reached a position with no outgoing figures and ended early!</p>
          <p class="mt-1 text-sm">You can regenerate the random walk for a new sequence, or, for a more permanent solution,
            <MyLink class="font-medium" :href="route('figures.create-from-position', walk[walk.length-1].payload.id)">
              add a figure leaving the final position, {{walk[walk.length - 1].payload.name}}.
            </MyLink>
          </p>

        </div>
      </div>

    </div>





  </div>
</template>
