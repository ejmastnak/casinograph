<script setup>
import {ref} from 'vue'
import SecondaryButton from "@/Components/SecondaryButton.vue";
import {
  TransitionRoot,
  Dialog,
  DialogPanel,
  DialogTitle,
  DialogDescription,
} from '@headlessui/vue'

const props = defineProps({
  position_images: Array,
  position_name: String,  // used for image alt text
})
defineExpose({ open })

const isOpen = ref(false)
function open() { isOpen.value = true }
function close() { isOpen.value = false }

</script>

<template>
  <Dialog :open="isOpen" @close="close" class="relative z-50">
    <div class="fixed inset-0 flex items-center justify-center p-4 bg-blue-50/80">
      <DialogPanel class="p-6 rounded-lg bg-white shadow max-h-screen overflow-auto bg-blue-100">

        <div class="space-y-5">
          <div v-for="position_image in position_images" :key="position_image.id">

            <img
              :src="'/' + position_image.path"
              :alt="position_name"
              class="max-w-lg"
            >

            <p v-if="position_image.description" class="mt-2 px-1 max-w-xl">
              <span class="font-semibold text-gray-800">Description:</span>
              {{position_image.description}}
            </p>

          </div>
        </div>

        <SecondaryButton @click="close" class="mt-8" >
          Close
        </SecondaryButton>

      </DialogPanel>
    </div>
  </Dialog>
</template>
