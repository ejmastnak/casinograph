<script setup>
import { Head } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'
import MyLink from '@/Components/MyLink.vue'
import PlaceholderParagraph from '@/Components/PlaceholderParagraph.vue'
import DeleteDialog from "@/Components/DeleteDialog.vue";
import SecondaryLink from '@/Components/SecondaryLink.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import PlainButton from '@/Components/PlainButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import FamilyPillbox from '@/Components/FamilyPillbox.vue'
import { PencilSquareIcon, TrashIcon, PlusCircleIcon, ArrowsPointingOutIcon, ArrowsUpDownIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import {
  Dialog, DialogPanel, DialogTitle, DialogDescription,
} from '@headlessui/vue'

const props = defineProps({
  figure: Object,
  can_create: Boolean,
  can_update: Boolean,
  can_delete: Boolean,
  graph_path: String,
})

let idToDelete = ref(null)
const deleteDialog = ref(null)

function deleteFigure() {
  if (idToDelete.value) {
    router.delete(route('figures.destroy', idToDelete.value));
  }
  idToDelete.value = null
}

const graphIsFullscreen = ref(false)
function setGraphIsFullScreen(value) {
  graphIsFullscreen.value = value
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
    <Head :title="figure.name" />

    <div class="flex items-center">
      <!-- Figure and figure family name -->
      <div class="mr-4">
        <h1 class="text-2xl">{{figure.name}}</h1>
      </div>

      <!-- New Figure button -->
      <SecondaryLink v-if="can_create" class="ml-auto h-fit" :href="route('figures.create')" >
        <PlusCircleIcon class="-ml-1 text-gray-600 h-6 w-6 shrink-0" />
        <p class="ml-1 whitespace-nowrap">New figure</p>
      </SecondaryLink>

    </div>

    <div v-if="figure.figure_family">
      <div class="text-gray-600 ">
          Figure family:
          <MyLink class="font-semibold" :href="route('figure-families.show', figure.figure_family_id)">
            {{figure.figure_family.name}}
          </MyLink>
      </div>
    </div>

    <!-- Description -->
    <div v-if="figure.description" class="mt-3">
      <p class="max-w-xl">{{figure.description}}</p>
    </div>

    <!-- Graph -->
    <div class="mt-6">
      <div class="mt-1 relative border overflow-auto border-gray-200 shadow rounded-lg h-96 sm:h-[18rem] grid place-items-center">

        <!-- Enter full screen -->
        <PlainButton class="absolute left-2 top-2" @click="setGraphIsFullScreen(true)">
          <ArrowsPointingOutIcon class="-ml-1 w-6 h-6 text-gray-500 shrink-0" />
          <p class="ml-1">Full screen</p>
        </PlainButton>

        <!-- Scroll to explore -->
        <div class="absolute left-2 top-14 px-2 py-1 bg-white/95 flex items-center rounded">
          <ArrowsUpDownIcon class="-ml-1 w-6 h-6 text-gray-500 shrink-0" />
          <p class="ml-1 -mt-0.5 text-sm text-gray-600">Scroll to explore</p>
        </div>

        <!-- SVG -->
        <Transition name="quickzoom" appear>
          <object class="p-1 mx-auto max-w-xl md:max-w-3xl lg:max-w-4xl" type="image/svg+xml" :data="graph_path"></object>
        </Transition>
      </div>
    </div>

    <!-- Edit and Delete buttons -->
    <div v-if="can_update || can_delete" class="flex items-center mt-6">
      <SecondaryLink v-if="can_update" :href="route('figures.edit', figure.id)" class="flex items-center">
        <PencilSquareIcon class="text-gray-600 h-5 w-5 -ml-1" />
        <p class="ml-1">Update</p>
      </SecondaryLink>

      <DangerButton v-if="can_delete" @click="idToDelete = figure.id; deleteDialog.open()" class="ml-2 flex items-center">
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

    <!-- Full screen graph dialog -->
    <Dialog :open="graphIsFullscreen" @close="setGraphIsFullScreen">
      <DialogPanel class="fixed inset-0 bg-white overflow-auto z-30">

        <!-- Graph -->
        <Transition name="quickzoom" appear>
          <object class="p-1 mx-auto max-w-xl md:max-w-3xl lg:max-w-4xl" type="image/svg+xml" :data="graph_path"></object>
        </Transition>

        <!-- Close button -->
        <Transition name="quickzoom" appear>
          <DangerButton class="fixed top-4 right-4" @click="setGraphIsFullScreen(false)" >
            <XMarkIcon class="-ml-1 w-6 h-6 text-white shrink-0"/>
            <p class="ml-1">Close</p>
          </DangerButton>
        </Transition>
      </DialogPanel>
    </Dialog>
  </div>
</template>

<style>
.zoom-enter-active {animation: zoom-in 0.5s ease-in-out;}
.zoom-leave-active {animation: zoom-out 0.5s ease-in-out;}
.quickzoom-enter-active {animation: zoom-in 0.25s ease-in-out;}
.quickzoom-leave-active {animation: zoom-out 0.25s ease-in-out;}
@keyframes zoom-in {
from {transform: scale(0,0);}
to {transform: scale(1,1);}
}
@keyframes zoom-out {
from {transform: scale(1,1);}
to {transform: scale(0,0);}
}
</style>
