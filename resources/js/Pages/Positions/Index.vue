<script setup>
import { Head } from '@inertiajs/vue3'
import MyLink from '@/Components/MyLink.vue'
import SecondaryLink from '@/Components/SecondaryLink.vue'
import { PencilSquareIcon, TrashIcon, PlusCircleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
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
    <Head title="Positions" />

    <div class="flex">
      <div class="">
        <h1 class="text-xl">Positions</h1>
        <p class="mt-1 text-sm text-gray-500 max-w-xs">This is a list of all positions. You can use this page to view, edit, delete, or add new positions.</p>
      </div>

      <SecondaryLink class="ml-auto h-fit" :href="route('positions.create')" >
        <PlusCircleIcon class="-ml-1 text-gray-600 h-6 w-6" />
        <p class="ml-1 whitespace-nowrap">New position</p>
      </SecondaryLink>
    </div>

    <table class="mt-6 sm:table-fixed w-full text-sm sm:text-base text-left">
      <thead class="text-xs text-gray-700 uppercase bg-gray-50">
        <tr>
          <th scope="col" class="px-6 py-2 w-8/12 bg-blue-100">
            Name
          </th>
          <th scope="col" class="px-6 py-2 w-3/12 bg-blue-200">
            Family
          </th>
          <!-- For trash and edit icons -->
          <th scope="col" class="bg-blue-100" />
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="position in positions" :key="position.id"
          class="bg-white border-b"
        >
          <!-- Name -->
          <td scope="row" class="px-5 py-2">
            <MyLink :href="route('positions.show', position.id)">
              {{position.name}}
            </MyLink>
          </td>
          <!-- PositionFamily -->
          <td class="px-6 py-2 text-gray-700">
            {{position.position_family?.name}}
          </td>
          <!-- Delete/Edit -->
          <td>
            <div class="flex items-center">
              <MyLink :href="route('positions.edit', position.id)">
                <PencilSquareIcon class="text-gray-500 h-5 w-5"/>
              </MyLink>
              <MyLink as="button" method="delete" :href="route('positions.destroy', position.id)">
                <TrashIcon class="text-gray-500 h-5 w-5"/>
              </MyLink>
            </div>
          </td>
        </tr>
      </tbody>
    </table>




  </div>
</template>
