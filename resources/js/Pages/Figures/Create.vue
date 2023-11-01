<script setup>
import { Head } from '@inertiajs/vue3'
import CreateOrEdit from './Partials/CreateOrEdit.vue';

const props = defineProps({
  figure_families: Array,
  positions: Array,
  from_position: Object,
  to_position: Object,
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
        from_position_id: from_position ? from_position.id : null,
        from_position: from_position,
        to_position_id: to_position ? to_position.id : null,
        to_position: to_position,
        figure_family_id: null,
        figure_family: null,
      }"
    />

  </div>
</template>
