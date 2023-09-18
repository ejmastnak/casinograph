<script setup>
import { Head } from '@inertiajs/vue3'
import CreateOrEdit from './Partials/CreateOrEdit.vue';

const props = defineProps({
  figure_families: Array,
  positions: Array,
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
    <Head title="New Figure" />
    <h1 class="text-xl">New Figure</h1>

    <CreateOrEdit
      action="create"
      :positions="positions"
      :figure_families="figure_families"
      :figure="{
        id: null,
        name: '',
        weight: null,
        description: null,
        from_position_id: null,
        from_position: null,
        to_position_id: null,
        to_position: null,
        figure_family_id: null,
        figure_family: null,
      }"
    />

  </div>
</template>
