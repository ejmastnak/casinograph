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

const link = "https://youtu.be/KIJGqON_oi0?t=20"

</script>

<template>
  <Dialog :open="isOpen" @close="close" class="relative z-50">
    <div class="fixed inset-0 flex items-center justify-center p-4 bg-blue-50/80">
      <DialogPanel class="px-6 pt-6 rounded-lg overflow-hidden bg-white shadow">

        <div class="flex">
          Here are the videos.
        </div>

          <!-- title="YouTube video player" -->
        <iframe
          width="560"
          height="315"
          src="https://www.youtube.com/embed/KIJGqON_oi0?start=94&amp;rel=0"
          frameborder="0"
          allow="encrypted-media; web-share"
          referrerpolicy="strict-origin-when-cross-origin"
          allowfullscreen
        >
        </iframe>

        <SecondaryButton @click="close" class="ml-auto" >
          Close
        </SecondaryButton>

      </DialogPanel>
    </div>
  </Dialog>
</template>
