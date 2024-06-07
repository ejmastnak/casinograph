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
import PlainButton from '@/Components/PlainButton.vue'
import FamilyPillbox from '@/Components/FamilyPillbox.vue'
import VideoDialog from '@/Components/VideoDialog.vue'
import { PencilSquareIcon, TrashIcon, PlayIcon, ListBulletIcon, PlusCircleIcon, ArrowsPointingOutIcon, ArrowsRightLeftIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { Dialog, DialogPanel, DialogTitle, DialogDescription } from '@headlessui/vue'

const props = defineProps({
  compound_figure: Object,
  can_create: Boolean,
  can_update: Boolean,
  can_delete: Boolean,
  graph_path: String,
})

let idToDelete = ref(null)
const deleteDialogRef = ref(null)
const videoDialogRef = ref(null)
const showingFigureSequence = ref(false)

function deleteFigure() {
  if (idToDelete.value) {
    router.delete(route('compound-figures.destroy', idToDelete.value));
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

    <!-- Description -->
    <div v-if="compound_figure.description" class="mt-3">
      <p class="max-w-xl">{{compound_figure.description}}</p>
    </div>

    <!-- Graph -->
    <div class="mt-6 relative">

        <!-- Enter full screen -->
        <PlainButton class="absolute left-2 bottom-2 sm:bottom-auto sm:top-2 z-10" @click="setGraphIsFullScreen(true)">
          <ArrowsPointingOutIcon class="-ml-1 w-6 h-6 text-gray-500 shrink-0" />
          <p class="ml-1">Full screen</p>
        </PlainButton>

        <!-- Scroll to explore -->
        <div class="absolute left-2 bottom-14 sm:bottom-auto sm:top-14 px-2 py-1 bg-white/95 flex items-center rounded z-10">
          <ArrowsRightLeftIcon class="-ml-1 w-6 h-6 text-gray-500 shrink-0" />
          <p class="ml-1 -mt-0.5 text-sm text-gray-600">Scroll to explore</p>
        </div>

      <div class="mt-1 relative border overflow-auto border-gray-200 shadow rounded-lg h-[15rem] grid place-items-center">

        <!-- Figure title -->
        <h2 class="absolute top-2 text-xl sm:text-2xl text-gray-700 px-2 py-1 bg-white/95 rounded-xl text-center z-20">
          Figure sequence
        </h2>

        <!-- SVG -->
        <Transition name="quickzoom" appear>
          <object class="p-1 mx-auto max-w-xl md:max-w-3xl lg:max-w-4xl" type="image/svg+xml" :data="graph_path"></object>
        </Transition>
      </div>
    </div>

    <div class="mt-4 space-x-2">

      <!-- Videos -->
      <SecondaryButton @click="videoDialogRef.open()" class="flex items-center">
        <PlayIcon class="h-5 w-5 -ml-1" />
        <p class="ml-1">Show videos</p>
      </SecondaryButton>

      <!-- Show/hide text description -->
      <SecondaryButton @click="showingFigureSequence = !showingFigureSequence" class="flex items-center">
        <ListBulletIcon class="h-5 w-5 -ml-1" />
        <p class="ml-1">{{showingFigureSequence ? 'Hide' : 'Show'}} text</p>
      </SecondaryButton>

    </div>

    <!-- Figure sequence text -->
    <div v-if="showingFigureSequence" class="mt-3">
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


    <!-- Edit and Delete buttons -->
    <div v-if="can_update || can_delete" class="flex items-center mt-6">
      <SecondaryLink v-if="can_update" :href="route('compound-figures.edit', compound_figure.id)" class="flex items-center">
        <PencilSquareIcon class="text-gray-600 h-5 w-5 -ml-1" />
        <p class="ml-1">Update</p>
      </SecondaryLink>

      <DangerButton v-if="can_delete" @click="idToDelete = compound_figure.id; deleteDialogRef.open()" class="ml-2 flex items-center">
        <TrashIcon class="text-white h-5 w-5 -ml-1" />
        <p class="ml-1">Delete</p>
      </DangerButton>
    </div>

    <DeleteDialog
    ref="deleteDialogRef"
    description="figure"
    @delete="deleteFigure"
    @cancel="idToDelete = null"
  />

    <VideoDialog ref="videoDialogRef" />

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
