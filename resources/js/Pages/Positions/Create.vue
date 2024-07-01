<script setup>
import { Head } from '@inertiajs/vue3'
import CreateOrEdit from './Partials/CreateOrEdit.vue';

const props = defineProps({
  position_families: Array,
})
</script>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
export default {
  layout: AppLayout,
}
</script>

<template>
  <div class="">
    <Head title="New Position" />
    <h1 class="text-xl">New Position</h1>

    <CreateOrEdit
      action="create"
      :position_families="position_families"
      :position="{
        id: null,
        name: '',
        description: null,
        position_family_id: null,
        position_family: null,
        position_images: [],
      }"
    />

  </div>
</template>
