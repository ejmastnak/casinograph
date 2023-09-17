<script setup>
import { InformationCircleIcon, ExclamationCircleIcon } from '@heroicons/vue/24/outline'
import { router, usePage } from '@inertiajs/vue3'
import {watch, ref, computed } from 'vue'

const props = defineProps({
  flash: Object,
})

const show = ref(true)
function hide() {
  show.value = false;
}

// Show flash message when props.flash.message changes,
// then hide the dialog after a few seconds.
watch(() => props.flash, () => {
  if (props.flash.message) {
    show.value = true;
    setTimeout(() => {
      props.flash.message = null
      show.value = false
    }, 3000);
  }
  if (props.flash.error) {
    show.value = true;
    setTimeout(() => {
      props.flash.error = null
      show.value = false
    }, 10000);
  }
});

</script>

<template>
  <div class="">
    <Transition>
      <div
        v-show="flash.message && show"
        @click="hide"
        class="absolute inset-x-0 mx-auto  flex items-center text-sm text-gray-800 bg-blue-50 px-4 py-3 rounded-b-md border-b-2 border-blue-400 z-50"
      >
        <InformationCircleIcon class="w-5 h-5 text-gray-600 shrink-0" />
        <span class="ml-1 translate-y-px">{{ flash.message }}</span>
      </div>
    </Transition>

    <Transition>
      <div
        v-show="flash.error && show"
        @click="hide"
        class="absolute inset-x-0 mx-auto  flex items-center text-sm text-gray-800 bg-red-200 px-4 py-3 rounded-b-md border-b-2 border-red-400 z-50"
      >
        <ExclamationCircleIcon class="w-6 h-6 text-gray-600 shrink-0" />
        <span class="ml-2 translate-y-px">{{ flash.error }}</span>
      </div>
    </Transition>

  </div>

</template>

<style>
.v-enter-active {
transition: opacity 0.1s ease;
}
.v-leave-active {
transition: opacity 0.5s ease;
}
.v-enter-from,
.v-leave-to {
opacity: 0;
}
</style>
