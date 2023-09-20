<script setup>
import { Head } from '@inertiajs/vue3'
import CreateOrEdit from './Partials/CreateOrEdit.vue'

const props = defineProps({
  position: Object,
  position_families: Array,
});
</script>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
export default {
  layout: AppLayout,
}
</script>

<template>
  <div class="">
    <Head :title="'Edit ' + position.name " />
    <h1 class="text-xl">Edit {{position.name}}</h1>

    <CreateOrEdit
      action="edit"
      :position="position"
      :position_families="position_families"
    />

  </div>
</template>
