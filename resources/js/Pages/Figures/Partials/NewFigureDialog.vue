<!-- A dialog to prompt user for a choice between creating either a Simple or a CompoundFigure. -->
<script setup>
import {ref} from 'vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PlainButton from '@/Components/PlainButton.vue'
import { QuestionMarkCircleIcon, InformationCircleIcon } from '@heroicons/vue/24/outline'
import {
  Dialog, DialogPanel, DialogTitle, DialogDescription,
  Popover, PopoverButton, PopoverPanel, PopoverOverlay,
} from '@headlessui/vue'

defineExpose({ open })
const emit = defineEmits(['simple', 'compound', 'cancel'])

const isOpen = ref(false)
function open() { isOpen.value = true }

function cancel() {
  isOpen.value = false
  emit('cancel')
}

function closeAndChooseSimple() {
  isOpen.value = false
  emit('simple')
}

function closeAndChooseCompound() {
  isOpen.value = false
  emit('compound')
}

</script>

<template>
  <Dialog :open="isOpen" @close="cancel" class="relative z-50">
    <div class="fixed inset-0 flex items-center justify-center p-4 bg-blue-50/80">
      <DialogPanel class="px-6 pt-6 w-full max-w-sm rounded-lg overflow-hidden bg-white shadow">

        <div class="">

          <DialogTitle class="text-lg font-bold text-gray-600">
            Choose figure type
          </DialogTitle>

          <DialogDescription class="mt-2 text-sm text-gray-600">
            Create a simple figure or a compound figure?
          </DialogDescription>

          <div class="mt-6 mx-auto w-full grid place-items-center">
            <div class="flex">
              <SecondaryButton
                class="w-28 grid place-items-center !bg-blue-100 !py-3 !border-gray-400"
                @click="closeAndChooseSimple">
                Simple
              </SecondaryButton>
              <SecondaryButton
                class="ml-4 w-28 grid place-items-center !bg-blue-100 !py-3 !border-gray-400"
                @click="closeAndChooseCompound">
                Compound
              </SecondaryButton>
            </div>
          </div>

        </div>

        <!-- More info and cancel buttons -->
        <div class="flex mt-8 -mx-6 px-4 py-3 bg-gray-50">

          <Popover class="absolute">
            <PopoverButton class="ml-auto inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-md font-semibold text-xs text-gray-600 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150" >
              <InformationCircleIcon class="-ml-1 h-6 w-6 text-gray-500 shrink-0" />
              <p class="ml-1.5">Explanation</p>
            </PopoverButton>
            <PopoverOverlay class="fixed inset-0 bg-black opacity-20" />

            <PopoverPanel class="absolute mt-0.5 px-6 py-3 w-96 max-w-sm rounded-lg overflow-hidden bg-gray-50 shadow text-gray-800">
              Definition:
              <ul class="mt-2 list-disc ml-4">
                <li>A <span class="font-semibold">simple figure</span> takes a single 8-count.</li>
                <li>A <span class="font-semibold">compound figure</span> takes two or more 8-counts and is composed of a sequence of simple figures.</li>
              </ul>
              <p class="mt-4">If your figure takes one 8-count, make it a simple figure. Otherwise choose a compound figure.</p>
            </PopoverPanel>
          </Popover>

          <SecondaryButton @click="cancel" class="ml-40 !py-3 !text-gray-600 !border-gray-200" >
            Cancel
          </SecondaryButton>
        </div>

      </DialogPanel>
    </div>
  </Dialog>
</template>
