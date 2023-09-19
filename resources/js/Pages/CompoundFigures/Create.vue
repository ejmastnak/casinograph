<script setup>
import { Head } from '@inertiajs/vue3'
import CreateOrEdit from './Partials/CreateOrEdit.vue';

const props = defineProps({
  figure_families: Array,
  figures: Array,
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
    <Head title="New Compound Figure" />
    <h1 class="text-xl">New Compound Figure</h1>

    <CreateOrEdit
      action="create"
      :figures="figures"
      :figure_families="figure_families"
      :compound_figure="{
        id: null,
        name: '',
        description: null,
        weight: null,
        figure_family_id: null,
        figure_family: null,
        compound_figure_figures: [],
      }"
    />

  </div>
</template>
