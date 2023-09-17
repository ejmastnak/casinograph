<script setup>
import {ref} from 'vue'
import DangerButton from "@/Components/DangerButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import {
  TransitionRoot,
  Dialog,
  DialogPanel,
  DialogTitle,
  DialogDescription,
} from '@headlessui/vue'

const props = defineProps({ description: String })
defineExpose({ open })
const emit = defineEmits(['delete', 'cancel'])

const isOpen = ref(false)
function open() { isOpen.value = true }

function cancel() {
  isOpen.value = false
  emit('cancel')
}

function closeAndDelete() {
  isOpen.value = false
  emit('delete')
}

</script>

<template>
  <Dialog :open="isOpen" @close="cancel" class="relative z-50">
    <div class="fixed inset-0 flex items-center justify-center p-4 bg-blue-50/80">
      <DialogPanel class="px-6 pt-6 w-full max-w-sm rounded-lg overflow-hidden bg-white shadow">

        <div class="flex">
          <div class="">
            <div class="rounded-full bg-red-100 p-1">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-red-700">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
              </svg>
            </div>
          </div>

          <div class="ml-5">
            <DialogTitle class="text-lg font-bold text-gray-600">
              Delete {{description}}
            </DialogTitle>
            <DialogDescription class="mt-2 text-sm">
              This will permanently delete the {{description}}.
              Are you sure?
            </DialogDescription>
          </div>
        </div>

        <div class="flex mt-5 -mx-6 px-4 py-3 bg-gray-50">
          <SecondaryButton @click="cancel" class="ml-auto" >
            Cancel
          </SecondaryButton>
          <DangerButton @click="closeAndDelete" class="ml-2" >
            Delete
          </DangerButton>
        </div>

      </DialogPanel>
    </div>
  </Dialog>
</template>
