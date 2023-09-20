<!-- A dialog to prompt user for a choice between creating either a Simple or a CompoundFigure. -->
<script setup>
import { ref, computed, watch } from 'vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from "@/Components/SecondaryButton.vue";
import MultiSelect from '@/Components/MultiSelect.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import {
  Dialog, DialogPanel, DialogTitle, DialogDescription,
  Popover, PopoverButton, PopoverPanel, PopoverOverlay,
} from '@headlessui/vue'

const props = defineProps({
  figure_families: Array,
})

defineExpose({ open })

const emit = defineEmits([
    'update:selectedFigureFamilies',
    'update:showSimpleFigures',
    'update:showCompoundFigures',
    'update:minWeight',
    'update:maxWeight',
    'close'
])

const isOpen = ref(false)
function open() { isOpen.value = true }

function close() {
  isOpen.value = false
  emit('close')
}

const selectedFigureFamilies = ref([])
const showSimpleFigures = ref(true)
const showCompoundFigures = ref(true)
const minWeight = ref("")
const maxWeight = ref("")

watch(selectedFigureFamilies, (newValue) => {
  emit('update:selectedFigureFamilies', selectedFigureFamilies.value)
})
watch(showSimpleFigures, (newValue) => {
  emit('update:showSimpleFigures', showSimpleFigures.value)
})
watch(showCompoundFigures, (newValue) => {
  emit('update:showCompoundFigures', showCompoundFigures.value)
})
watch(minWeight, (newValue) => {
  emit('update:minWeight', parseInt(minWeight.value))
})
watch(maxWeight, (newValue) => {
  emit('update:maxWeight', parseInt(maxWeight.value))
})

function clearFilters() {
  selectedFigureFamilies.value = []
  showSimpleFigures.value = true
  showCompoundFigures.value = true
  minWeight.value = ""
  maxWeight.value = ""
}

</script>

<template>
  <Dialog :open="isOpen" @close="close" class="relative z-50">
    <div class="fixed inset-0 flex items-center justify-center p-4 bg-blue-50/80">
      <DialogPanel class="px-8 pt-6 w-full max-w-md rounded-xl overflow-hidden bg-white shadow">

        <div class="">

          <DialogTitle class="text-lg font-bold text-gray-600 pb-1 border-b border-gray-200">
            Filters
          </DialogTitle>

          <div class="mt-3 pb-5 border-b border-gray-200">
            <!-- FigureFamily Filter -->
            <MultiSelect
              :options="figure_families"
              labelClasses="!text-base text-gray-800"
              width="w-56"
              labelText="Figure families to display"
              :modelValue="selectedFigureFamilies"
              @update:modelValue="newValue => selectedFigureFamilies = newValue"
            />
          </div>

          <!-- Simple and compound -->
          <div class="mt-4 pb-5 border-b border-gray-200">
            <p class="text-gray-800">Figure types to display</p>

            <div class="mt-1 text-gray-700">
              <!-- Show simple figures -->
              <div class="flex items-center">
                <input
                  type="checkbox"
                  id="simple"
                  name="simple"
                  v-model="showSimpleFigures"
                  class="rounded"
                />
                <label class="ml-1.5" for="simple">Simple</label>
              </div>

              <!-- Show compound figures -->
              <div class="flex items-center">
                <input
                  type="checkbox"
                  id="compound"
                  name="compound"
                  v-model="showCompoundFigures"
                  class="rounded"
                />
                <label class="ml-1.5" for="compound">Compound</label>
              </div>
            </div>
          </div>

          <!-- Min and max weight -->
          <div class="mt-4">
            <p class="text-gray-800">Filter by figure weight</p>
            <div class="mt-1 flex">

              <!-- Min weight -->
              <div>
                <InputLabel class="!text-gray-500" for="min-weight" value="Minimum weight" />
                <TextInput
                  id="min-weight"
                  type="number"
                  class="block w-36"
                  v-model="minWeight"
                />
              </div>

              <!-- Max weight -->
              <div class="ml-4">
                <InputLabel class="!text-gray-500" for="max-weight" value="Maximum weight" />
                <TextInput
                  id="max-weight"
                  type="number"
                  class="block w-36"
                  v-model="maxWeight"
                />
              </div>

            </div>
          </div>
        </div>

        <!-- Bottom panel with Close button -->
        <div class="flex mt-8 -mx-8 px-8 py-3 bg-gray-50">

          <PrimaryButton @click="close" >
            Okay
          </PrimaryButton>

          <SecondaryButton @click="clearFilters" class="ml-2" >
            Clear filters
          </SecondaryButton>

        </div>

      </DialogPanel>
    </div>
  </Dialog>
</template>
