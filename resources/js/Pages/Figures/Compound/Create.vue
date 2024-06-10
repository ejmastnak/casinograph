<script setup>
import { Head } from '@inertiajs/vue3'
import CreateOrEdit from './Partials/CreateOrEdit.vue';
import Warning from '@/Components/Warning.vue'
import MyLink from '@/Components/MyLink.vue'

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

    <Warning v-if="figures.length === 0" class="mt-5 max-w-xl">
      You have not created any foundational figures yet!
      You create compound figures by connecting foundational figures, so you will need to <MyLink :colored="true" :href="route('figures.create')">create some foundational figures</MyLink> before you can create compound figures.
    </Warning>

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
        compound_figure_videos: [],
      }"
    />

  </div>
</template>
