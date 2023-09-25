<script setup>
import { Head } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'
import MyLink from '@/Components/MyLink.vue'
import PlaceholderParagraph from '@/Components/PlaceholderParagraph.vue'
import DeleteDialog from "@/Components/DeleteDialog.vue";
import DangerButton from '@/Components/DangerButton.vue'
import SecondaryLink from '@/Components/SecondaryLink.vue'
import FamilyPillbox from '@/Components/FamilyPillbox.vue'
import { PencilSquareIcon, TrashIcon, PlusCircleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  position: Object,
  can_create: Boolean,
  can_update: Boolean,
  can_delete: Boolean,
})

let idToDelete = ref(null)
const deleteDialog = ref(null)

function deletePosition() {
  if (idToDelete.value) {
    router.delete(route('positions.destroy', idToDelete.value));
  }
  idToDelete.value = null
}
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

    <div class="flex items-center">
      <div class="flex items-baseline">
        <h1 class="text-2xl">{{position.name}}</h1>
        <FamilyPillbox class="ml-2" v-if="position.position_family" :text="position.position_family.name" />
      </div>

      <SecondaryLink v-if="can_create" class="ml-auto h-fit" :href="route('positions.create')" >
        <PlusCircleIcon class="-ml-1 text-gray-600 h-6 w-6 shrink-0" />
        <p class="ml-1 whitespace-nowrap">New position</p>
      </SecondaryLink>
    </div>

    <div class="mt-4">
      <div v-if="position.description">
        <p class="text-gray-600">Description</p>
        <p class="">{{position.description}}</p>
      </div>
      <PlaceholderParagraph v-else class="">This position does not have a description yet.</PlaceholderParagraph>
    </div>

    <!-- Incoming and outgoing figures -->
    <div
      v-if="position.incoming_figures.length || position.outgoing_figures.length"
      class="mt-6 grid grid-cols-2 space-x-4 w-fit"
    >
      <!-- Incoming figures -->
      <div class="">
        <h2 class="text-lg text-gray-600">Incoming figures</h2>
        <ul v-if="position.incoming_figures.length" class="space-y-1.5">
          <li v-for="figure in position.incoming_figures" :key="figure.id">
            <MyLink class="inline-block" :href="route('figures.show', figure.id)" >
              {{figure.name}}
            </MyLink>
            <p class="-mt-1 text-sm text-gray-600">
              From <MyLink class="font-medium" :href="route('positions.show', figure.from_position_id)" >{{figure.from_position.name}}</MyLink>
            </p>
          </li>
        </ul>
        <PlaceholderParagraph v-else class="">This position doesn't have any incoming figures.</PlaceholderParagraph>
      </div>
      <!-- Outgoing figures -->
      <div class="">
        <h2 class="text-lg text-gray-600">Outgoing figures</h2>
        <ul v-if="position.outgoing_figures.length" class="space-y-1.5">
          <li v-for="figure in position.outgoing_figures" :key="figure.id">
            <MyLink class="inline-block" :href="route('figures.show', figure.id)" >
              {{figure.name}}
            </MyLink>
            <p class="-mt-1 text-sm text-gray-600">
              To <MyLink class="font-medium" :href="route('positions.show', figure.to_position_id)" >{{figure.to_position.name}}</MyLink>
            </p>
          </li>
        </ul>
        <PlaceholderParagraph v-else class="">This position doesn't have any outgoing figures.</PlaceholderParagraph>
      </div>
    </div>
    <PlaceholderParagraph class="mt-4" v-else>
      There are no figures leading into or out of this position.
    </PlaceholderParagraph>

    <!-- Edit and Delete buttons -->
    <div v-if="can_update || can_delete" class="flex items-center mt-8">
      <SecondaryLink v-if="can_update" :href="route('positions.edit', position.id)" class="flex items-center">
        <PencilSquareIcon class="text-gray-600 h-5 w-5 -ml-1" />
        <p class="ml-1">Edit</p>
      </SecondaryLink>

      <DangerButton v-if="can_delete" @click="idToDelete = position.id; deleteDialog.open()" class="ml-2 flex items-center">
        <TrashIcon class="text-white h-5 w-5 -ml-1" />
        <p class="ml-1">Delete</p>
      </DangerButton>
    </div>

    <DeleteDialog
      ref="deleteDialog"
      description="position"
      @delete="deletePosition"
      @cancel="idToDelete = null"
    />

  </div>
</template>
