<script setup>
import { Head } from '@inertiajs/vue3'
import MyLink from '@/Components/MyLink.vue'
import PlaceholderParagraph from '@/Components/PlaceholderParagraph.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import DangerLink from '@/Components/DangerLink.vue'
import SecondaryLink from '@/Components/SecondaryLink.vue'
import { PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  position: Object,
})
</script>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
export default {
  layout: AppLayout,
}
</script>

<template>
  <div class="">
    <Head :title="position.name" />
    <h1 class="text-2xl">{{position.name}}</h1>

    <div v-if="position.description" class="mt-4">
      <p class="text-gray-600">Description</p>
      <p class="">{{position.description}}</p>
    </div>
    <PlaceholderParagraph v-else class="">This position does not have a description yet.</PlaceholderParagraph>

    <!-- Incoming and outgoing figures -->
    <div
      v-if="position.incoming_figures.length || position.outgoing_figures.length"
      class="mt-6 grid grid-cols-2 space-x-2"
    >
      <!-- Incoming figures -->
      <div class="">
        <h2 class="text-lg text-gray-600">Incoming figures</h2>
        <ul v-if="position.incoming_figures.length">
          <li v-for="figure in position.incoming_figures" :key="figure.id">
            <MyLink :href="route('figures.show', figure.id)" >
              {{figure.name}}
            </MyLink>
          </li>
        </ul>
        <PlaceholderParagraph v-else class="">This position doesn't have any incoming figures.</PlaceholderParagraph>
      </div>
      <!-- Outgoing figures -->
      <div class="">
        <h2 class="text-lg text-gray-600">Outgoing figures</h2>
        <ul v-if="position.outgoing_figures.length">
          <li v-for="figure in position.outgoing_figures" :key="figure.id">
            <MyLink :href="route('figures.show', figure.id)" >
              {{figure.name}}
            </MyLink>
          </li>
        </ul>
        <PlaceholderParagraph v-else class="">This position doesn't have any outgoing figures.</PlaceholderParagraph>
      </div>
    </div>
    <PlaceholderParagraph class="mt-4" v-else>
      There are no figures leading into or out of this position.
    </PlaceholderParagraph>

    <!-- Edit and Delete buttons -->
    <div class="flex items-center mt-12">
      <SecondaryLink :href="route('positions.edit', position.id)" class="flex items-center">
        <PencilSquareIcon class="text-gray-600 h-5 w-5 -ml-1" />
        <p class="ml-1">Edit</p>
      </SecondaryLink>
      <DangerLink :href="route('positions.destroy', position.id)" method="delete" as="button" class="ml-2 flex items-center">
        <TrashIcon class="text-white h-5 w-5 -ml-1" />
        <p class="ml-1">Delete</p>
      </DangerLink>
    </div>

  </div>
</template>
