<script setup>
import { Head } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'
import MyLink from '@/Components/MyLink.vue'
import PlaceholderParagraph from '@/Components/PlaceholderParagraph.vue'
import DeleteDialog from "@/Components/DeleteDialog.vue";
import DangerButton from '@/Components/DangerButton.vue'
import SecondaryLink from '@/Components/SecondaryLink.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import FamilyPillbox from '@/Components/FamilyPillbox.vue'
import { PencilSquareIcon, TrashIcon, PlusCircleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  compound_figure: Object,
  can_create: Boolean,
  can_update: Boolean,
  can_delete: Boolean,
})

let idToDelete = ref(null)
const deleteDialog = ref(null)

function deleteFigure() {
  if (idToDelete.value) {
    router.delete(route('compound-figures.destroy', idToDelete.value));
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
    <Head :title="compound_figure.name" />

    <div class="flex items-center">
      <!-- Figure and figure family name -->
      <div class="mr-4">
        <h1 class="text-2xl">{{compound_figure.name}}</h1>
      </div>

      <!-- New Figure button -->
      <SecondaryLink v-if="can_create" class="ml-auto h-fit" :href="route('compound-figures.create')" >
        <PlusCircleIcon class="-ml-1 text-gray-600 h-6 w-6 shrink-0" />
        <p class="ml-1 whitespace-nowrap">New figure</p>
      </SecondaryLink>


    </div>

    <!-- From position / to position -->
    <div class="mt-0.5 text-gray-600">
      From
      <MyLink :href="route('positions.show', compound_figure.compound_figure_figures[0].figure.from_position_id)" class="font-semibold" >
        {{compound_figure.compound_figure_figures[0].figure.from_position.name}}
      </MyLink>
      to
      <MyLink :href="route('positions.show', compound_figure.compound_figure_figures[compound_figure.compound_figure_figures.length - 1].figure.to_position_id)" class="font-semibold" >
        {{compound_figure.compound_figure_figures[compound_figure.compound_figure_figures.length - 1].figure.to_position.name}}
      </MyLink>
    </div>

    <!-- Figure sequence -->
    <div class="mt-2">
      <h2 class="text-lg text-gray-700">Figure sequence</h2>
      <ol class="mt-1 list-decimal ml-5 space-y-1">
        <li v-for="compound_figure_figure in compound_figure.compound_figure_figures" :key="compound_figure_figure.id">
          <MyLink class="inline-block" :href="route('figures.show', compound_figure_figure.figure_id)" >
            {{compound_figure_figure.figure.name}}
          </MyLink>
          <p class="-mt-1 text-sm text-gray-600">
            From
            <MyLink class="font-medium" :href="route('positions.show', compound_figure_figure.figure.from_position_id)" >
              {{compound_figure_figure.figure.from_position.name}}
            </MyLink>
            to
            <MyLink class="font-medium" :href="route('positions.show', compound_figure_figure.figure.to_position_id)" >
              {{compound_figure_figure.figure.to_position.name}}
            </MyLink>
          </p>
        </li>
      </ol>
    </div>

    <!-- Description -->
    <div v-if="compound_figure.description" class="mt-3">
      <p class="max-w-xl">{{compound_figure.description}}</p>
    </div>

    <!-- Edit and Delete buttons -->
    <div v-if="can_update || can_delete" class="flex items-center mt-6">
      <SecondaryLink v-if="can_update" :href="route('compound-figures.edit', compound_figure.id)" class="flex items-center">
        <PencilSquareIcon class="text-gray-600 h-5 w-5 -ml-1" />
        <p class="ml-1">Update</p>
      </SecondaryLink>

      <DangerButton v-if="can_delete" @click="idToDelete = compound_figure.id; deleteDialog.open()" class="ml-2 flex items-center">
        <TrashIcon class="text-white h-5 w-5 -ml-1" />
        <p class="ml-1">Delete</p>
      </DangerButton>
    </div>

    <DeleteDialog
      ref="deleteDialog"
      description="figure"
      @delete="deleteFigure"
      @cancel="idToDelete = null"
    />

  </div>
</template>
