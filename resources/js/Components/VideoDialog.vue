<script setup>
import {ref} from 'vue'
import DangerButton from "@/Components/DangerButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
import {
  TransitionRoot,
  Dialog,
  DialogPanel,
  DialogTitle,
  DialogDescription,
} from '@headlessui/vue'

defineProps({ description: String })
defineExpose({ open })

const isOpen = ref(false)
function open() { isOpen.value = true }
function close() { isOpen.value = false }

</script>

<template>
  <Dialog :open="isOpen" @close="close" class="relative z-50">
    <div class="fixed inset-0 flex items-center justify-center p-4 bg-blue-50/80">
      <DialogPanel class="px-6 pt-6 w-full max-w-sm rounded-lg overflow-hidden bg-white shadow">

        <div class="flex">
          Here are the videos.
        </div>

        <SecondaryButton @click="close" class="ml-auto" >
          Close
        </SecondaryButton>

      </DialogPanel>
    </div>
  </Dialog>
</template>
