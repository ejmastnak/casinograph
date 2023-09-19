<!-- Used to add CompoundFigureFigures to parent CompoundFigure -->
<script setup>
import { Dialog, DialogPanel, DialogTitle, DialogDescription } from '@headlessui/vue'
import { PlusCircleIcon, TrashIcon } from '@heroicons/vue/24/outline'
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import cloneDeep from "lodash/cloneDeep";
import { ref } from 'vue'

const props = defineProps({
  compound_figure_figures: Array,
})
const emit = defineEmits(['update:compound_figure_figures'])

const isOpen = ref(false)

// A local "buffer" copy to edit in the scope of this component
const compoundFigureFiguresCopy = ref([])

// Used to focus input after adding new figure
const entryDivRefs = ref([])

var nextID = 1
function addCompoundFigureFigure() {
  compoundFigureFiguresCopy.value.push({id: nextID, name: ""});

  // Focus the first text input in the just-added (i.e. last) entryDiv
  // Using setTimeout lets div be inserted into DOM
  setTimeout(() => {
    const input = entryDivRefs.value[entryDivRefs.value.length - 1].querySelectorAll('input')[0];
    if (input) input.focus();
  }, 0)

  nextID += 1;
}

// Remove CompoundFigureFigure with given index from list
function removeCompoundFigureFigure(idx) {
  if (idx >= 0 && idx < compoundFigureFiguresCopy.value.length) compoundFigureFiguresCopy.value.splice(idx, 1);
}

// Opens the Dialog (add other key/values to copy as necessary)
function open() {
  compoundFigureFiguresCopy.value = cloneDeep(props.compound_figure_figures)
  if (compoundFigureFiguresCopy.value.length === 0) addCompoundFigureFigure();
  isOpen.value = true;
}

// Closes the dialog
function close() {
  isOpen.value = false
  compoundFigureFiguresCopy.value = []
}

function updateAndClose() {
  emit('update:compound_figure_figures', compoundFigureFiguresCopy.value)
  close()
}

</script>

<template>
  <div class="">

    <!-- Open dialog button -->
    <button
      type="button"
      @click="open"
      class="mt-4 inline-flex compound_figure_figures-center px-3 py-1 bg-white border border-gray-300 rounded-md text-gray-500 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
    >
      Edit figures
    </button>

    <Dialog :open="isOpen" @close="close" class="relative z-50">
      <div class="fixed inset-0 flex compound_figure_figures-center justify-center p-4 bg-white/80 overflow-y-auto">

        <DialogPanel class="w-full flex flex-col p-8 max-w-xl min-h-[300px] shadow-sm border border-gray-200 rounded-xl bg-white">

          <DialogTitle class="text-xl text-gray-700">
            Manage compound_figure_figures
          </DialogTitle>

          <!-- Display list of compound_figure_figures -->
          <div v-if="compoundFigureFiguresCopy.length" class="mt-3">
            <template v-for="(compound_figure_figure, idx) in compoundFigureFiguresCopy" >
              <div ref="entryDivRefs" class="flex compound_figure_figures-center">
                <TextInput
                  type="text"
                  class="mt-1 block w-full text-sm"
                  v-model="compound_figure_figure.name"
                />
                <button
                  type="button"
                  @click="removeCompoundFigureFigure(idx)"
                  class="ml-1 p-1 text-gray-700 hover:text-red-700"
                >
                  <TrashIcon class="w-6 h-6" />
                </button>
              </div>
              </template>
          </div>
          <p v-else class="mt-6 text-gray-500 text-sm" >
            No compound_figure_figures added yet
          </p>

          <!-- Add Foo -->
          <button
            type="button"
            @click="addCompoundFigureFigure"
            class="mt-4 mb-8 inline-flex compound_figure_figures-center w-fit pl-2 pr-4 py-1 bg-white border border-gray-300 rounded-lg text-gray-500 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
          >
            <PlusCircleIcon class="w-6 h-6"/>
            <span class="ml-2 text-sm">New compound_figure_figure</span>
          </button>

          <div class="flex mt-auto">
            <PrimaryButton
              class="ml"
              @click="updateAndClose"
            >
              Save
            </PrimaryButton>

            <SecondaryButton
              class="ml-4"
              @click="close"
            >
              Cancel
            </SecondaryButton>
          </div>

        </DialogPanel>
      </div>
    </Dialog>

  </div>
  </template>
