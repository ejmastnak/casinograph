<script setup>
import { Head } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'
import MyLink from '@/Components/MyLink.vue'
import Warning from '@/Components/Warning.vue'
import PlaceholderParagraph from '@/Components/PlaceholderParagraph.vue'
import DeleteDialog from "@/Components/DeleteDialog.vue";
import DangerButton from '@/Components/DangerButton.vue'
import SecondaryLink from '@/Components/SecondaryLink.vue'
import FamilyPillbox from '@/Components/FamilyPillbox.vue'
import PlainButton from '@/Components/PlainButton.vue'
import { PencilSquareIcon, TrashIcon, PlusCircleIcon, ArrowsPointingOutIcon, ArrowsUpDownIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import {
  Dialog, DialogPanel, DialogTitle, DialogDescription,
} from '@headlessui/vue'

const props = defineProps({
  position: Object,
  can_create: Boolean,
  can_update: Boolean,
  can_delete: Boolean,
  graph_path: String,
  graph_is_nonempty: Boolean,
})

let idToDelete = ref(null)
const deleteDialog = ref(null)

function deletePosition() {
  if (idToDelete.value) {
    router.delete(route('positions.destroy', idToDelete.value));
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
    <Head :title="position.name" />

    <div class="flex items-center">
      <div class="mr-4">
        <h1 class="text-2xl">{{position.name}}</h1>
      </div>

      <SecondaryLink v-if="can_create" class="ml-auto h-fit" :href="route('positions.create')" >
        <PlusCircleIcon class="-ml-1 text-gray-600 h-6 w-6 shrink-0" />
        <p class="ml-1 whitespace-nowrap">New <span class="hidden sm:inline">position</span></p>
      </SecondaryLink>
    </div>

    <div v-if="position.position_family" class="mt-0">
      <div class="text-gray-600">
          Position family:
          <MyLink class="font-semibold" :href="route('position-families.show', position.position_family_id)">
            {{position.position_family.name}}
          </MyLink>
      </div>
    </div>

    <div class="mt-4">
      <div v-if="position.description">
        <p class="text-gray-600">Description</p>
        <p class="max-w-xl">{{position.description}}</p>
      </div>
      <PlaceholderParagraph v-else>
        This position does not have a description yet.
      </PlaceholderParagraph>
    </div>

    <!-- Graph -->
    <div v-if="graph_is_nonempty" class="relative mt-8 -mx-2">
      <div class="border overflow-auto border-gray-200 shadow rounded-lg h-96 sm:h-[30rem] grid place-items-center">

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
        <Transition name="zoom" appear>
          <object class="p-1 mx-auto max-w-xl md:max-w-3xl lg:max-w-4xl" type="image/svg+xml" :data="graph_path"></object>
        </Transition>
      </div>
    </div>

    <!-- Incoming and outgoing figures -->
    <div
      class="mt-6 grid grid-cols-2 gap-x-12 w-fit max-w-2xl"
    >
      <!-- Incoming figures -->
      <div>
        <h2 class="text-lg text-gray-600">Incoming figures</h2>
        <ul v-if="position.incoming_figures.length" class="space-y-1.5 ml-4 list-disc">
          <li v-for="figure in position.incoming_figures" :key="figure.id">
            <MyLink class="inline-block" :href="route('figures.show', figure.id)" >
              {{figure.name}}
            </MyLink>
            <p class="-mt-1 text-sm text-gray-600">
              From <MyLink class="font-medium" :href="route('positions.show', figure.from_position_id)" >{{figure.from_position.name}}</MyLink>
            </p>
          </li>
        </ul>
        <PlaceholderParagraph class="mt-1 text-sm" v-else>
          This position doesn't have any incoming figures.
        </PlaceholderParagraph>

        <!-- Add incoming figures -->
        <MyLink
          v-if="can_create"
          :href="route('figures.create-to-position', position.id)"
          class="mt-3 w-fit h-fit inline-flex items-center rounded border border-gray-300 px-3 py-1 text-sm text-gray-800"
        >
          <PlusCircleIcon class="-ml-1 text-gray-600 h-5 w-5 shrink-0" />
          <p class="ml-1 leading-tight">Add incoming figure</p>
        </MyLink>

      </div>

      <!-- Outgoing figures -->
      <div class="h-fit">
        <h2 class="text-lg text-gray-600">Outgoing figures</h2>
        <ul v-if="position.outgoing_figures.length" class="space-y-1.5 list-disc ml-4">
          <li v-for="figure in position.outgoing_figures" :key="figure.id">
            <MyLink class="inline-block" :href="route('figures.show', figure.id)" >
              {{figure.name}}
            </MyLink>
            <p class="-mt-1 text-sm text-gray-600">
              To <MyLink class="font-medium" :href="route('positions.show', figure.to_position_id)" >{{figure.to_position.name}}</MyLink>
            </p>
          </li>
        </ul>
        <PlaceholderParagraph class="mt-1 text-sm" v-else>
          This position doesn't have any outgoing figures.
        </PlaceholderParagraph>

        <!-- Add outgoing figures -->
        <MyLink
          v-if="can_create"
          :href="route('figures.create-from-position', position.id)"
          class="mt-3 w-fit h-fit inline-flex items-center rounded border border-gray-300 px-3 py-1 text-sm text-gray-800"
        >
          <PlusCircleIcon class="-ml-1 text-gray-600 h-5 w-5 shrink-0" />
          <p class="ml-1 leading-tight">Add outgoing figure</p>
        </MyLink>
      </div>
    </div>

    <!-- Edit and Delete buttons -->
    <div v-if="can_update || can_delete" class="flex items-center mt-10">
      <SecondaryLink v-if="can_update" :href="route('positions.edit', position.id)" class="flex items-center">
        <PencilSquareIcon class="text-gray-600 h-5 w-5 -ml-1" />
        <p class="ml-1">Update</p>
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

    <!-- Full screen graph dialog -->
    <Dialog :open="graphIsFullscreen" @close="setGraphIsFullScreen">
      <DialogPanel class="fixed inset-0 bg-white overflow-auto">

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
