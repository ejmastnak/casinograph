<script setup>
import { ref, watch, computed, onBeforeUnmount, onMounted } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import fuzzysort from 'fuzzysort'
import throttle from "lodash/throttle";
import { Head } from '@inertiajs/vue3'
import MyLink from '@/Components/MyLink.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from "@/Components/SecondaryButton.vue";
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import { TransitionRoot, Dialog, DialogPanel, DialogTitle, DialogDescription } from '@headlessui/vue'
import { PencilSquareIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  figure_family: Object,
  can_update: Boolean,
})

function removeAccents(str) {
  return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
}

const figureSearchInput = ref(null)
const figureSearchQuery = ref("")
const fuzzysortOptions = {
  keys: ['normalized_name'],
  all: true,        // return all items for empty search query
  limit: 100,       // maximum number of search results
  threshold: -1000  // omit results with scores below threshold
}

const normalizedFigures = computed(() => {
  return props.figure_family.figures.map(figure => ({
    id: figure.id,
    name: figure.name,
    normalized_name: removeAccents(figure.name),
    from_position_id: figure.from_position_id,
    from_position: figure.from_position,
    to_position_id: figure.to_position_id,
    to_position: figure.to_position,
  }))
})
const normalizedCompoundFigures = computed(() => {
  return props.figure_family.compound_figures.map(figure => ({
    id: figure.id,
    name: figure.name,
    normalized_name: removeAccents(figure.name),
    from_position_id: figure.from_position_id,
    from_position: figure.from_position,
    to_position_id: figure.to_position_id,
    to_position: figure.to_position,
  }))
})

// Convert to fuzzysort format with nested obj key
const filteredFigures = ref(normalizedFigures.value.map((figure) => ({
  obj: figure
})))
const filteredCompoundFigures = ref(normalizedCompoundFigures.value.map((figure) => ({
  obj: figure
})))

function search(query) {
  filteredFigures.value = fuzzysort.go(removeAccents(query.trim()), normalizedFigures.value, fuzzysortOptions)
  filteredCompoundFigures.value = fuzzysort.go(removeAccents(query.trim()), normalizedCompoundFigures.value, fuzzysortOptions)
}

watch(figureSearchQuery, throttle(function (value) {
  search(value)
}, 400))

// Used to conditionally display a "No results found" message.
const numDisplayedFigures = computed(() => {
  return filteredFigures.value.length + filteredCompoundFigures.value.length
})

const form = useForm({
  id: props.figure_family.id,
  name: props.figure_family.name,
});

const renameDialogOpen = ref(false)
function cancelRename() {
  renameDialogOpen.value = false
}
function confirmRename() {
  form.put(route('figure-families.update', props.figure_family.id), {
      onSuccess: (() => {renameDialogOpen.value = false}),
  });
}

// Preserve scroll figure and search queries when leaving page.
onBeforeUnmount(() => {
  sessionStorage.setItem('figureFamilyShowScrollX', window.scrollX)
  sessionStorage.setItem('figureFamilyShowScrollY', window.scrollY)
  sessionStorage.setItem('figureFamilyShowSearchQuery', figureSearchQuery.value)
})

// Preserve scroll figure and search queries on manual page reload.
window.onbeforeunload = function() {
  sessionStorage.setItem('figureFamilyShowScrollX', window.scrollX)
  sessionStorage.setItem('figureFamilyShowScrollY', window.scrollY)
  sessionStorage.setItem('figureFamilyShowSearchQuery', figureSearchQuery.value)
}

// Restore scroll figure and search queries when loading page
onMounted(() => {
  const scrollX = sessionStorage.getItem('figureFamilyShowScrollX')
  const scrollY = sessionStorage.getItem('figureFamilyShowScrollY')
  if (scrollX && scrollY) {
    setTimeout(() => {
      window.scrollTo(scrollX, scrollY)
    })
  }

  const storedSearchQuery = sessionStorage.getItem('figureFamilyShowSearchQuery');
  if (storedSearchQuery) {
    figureSearchQuery.value = storedSearchQuery
    search(figureSearchQuery.value)
  }
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

    <Head :title="figure_family.name" />

    <div class="flex">
      <div class="">
        <h1 class="text-xl">{{figure_family.name}} (figure family)</h1>
        <p class="mt-1 text-sm text-gray-500 max-w-xs mr-2">This is a list of all figures in this figure family.</p>
      </div>

      <SecondaryButton v-if="can_update" class="ml-auto h-fit" @click="renameDialogOpen = true">
        <PencilSquareIcon class="-ml-1 text-gray-600 h-6 w-6" />
        <p class="ml-1 whitespace-nowrap">Rename</p>
      </SecondaryButton>
    </div>

    <!-- Main panel for table and search -->
    <div class="mt-6 pt-3 border border-gray-100 shadow-md rounded-lg overflow-auto">

      <!-- Search and filter components -->
      <div class="px-3 flex flex-wrap items-end space-y-2 gap-x-4">

        <!-- Fuzzy search by name -->
        <div class="w-80 sm:w-fit">
          <label for="figure-search-query" class="ml-1 text-sm text-gray-500">
            Search by name
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <MagnifyingGlassIcon class="w-5 h-5 text-gray-500" />
            </div>
            <TextInput
              class="w-80 max-w-full py-1.5 pl-10 text-gray-700 bg-gray-50"
              type="text"
              id="figure-search-query"
              v-model="figureSearchQuery"
              ref="figureSearchInput"
            />
          </div>
        </div>

      </div>

      <table class="mt-6 w-full text-sm sm:text-base text-left">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
          <tr>
            <th
              scope="col"
              class="px-6 py-2 w-9/12 bg-blue-100"
            >
              Name
            </th>
            <th scope="col" class="px-5 py-2 bg-blue-200" >
              Type
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="figure in filteredFigures"
            :key="figure.obj.id"
            class="bg-white border-b"
          > <td scope="row" class="px-5 py-2">
          <MyLink class="inline-block" :href="route('figures.show', figure.obj.id)"> {{figure.obj.name}}
              </MyLink>
              <p class="text-sm text-gray-500">
                <MyLink class="font-medium" :href="route('positions.show', figure.obj.from_position_id)" >{{figure.obj.from_position.name}}</MyLink>
                to
                <MyLink class="font-medium" :href="route('positions.show', figure.obj.to_position_id)" >{{figure.obj.to_position.name}}</MyLink>
              </p>
            </td>
            <td class="px-4 py-2 text-gray-600">
              Foundational
            </td>
          </tr>
          <tr
            v-for="figure in filteredCompoundFigures"
            :key="figure.obj.id"
            class="bg-white border-b"
          >
            <td scope="row" class="px-5 py-2">
              <MyLink class="inline-block" :href="route('compound-figures.show', figure.obj.id)">
                {{figure.obj.name}}
              </MyLink>
              <p class="text-sm text-gray-500">
                <MyLink class="font-medium" :href="route('positions.show', figure.obj.from_position_id)" >{{figure.obj.from_position.name}}</MyLink>
                to
                <MyLink class="font-medium" :href="route('positions.show', figure.obj.to_position_id)" >{{figure.obj.to_position.name}}</MyLink>
              </p>
            </td>
            <td class="px-4 py-2 text-gray-600">
              Compound
            </td>
          </tr>

        </tbody>
      </table>

      <p v-show="numDisplayedFigures === 0" class="px-6 py-4" >
        No results found. Try a less restrictive filter or search?
      </p>

    </div>

    <!-- Rename dialog -->
    <Dialog v-if="can_update" :open="renameDialogOpen" @close="cancelRename" class="relative z-50">
      <div class="fixed inset-0 flex items-center justify-center p-4 bg-blue-50/80">
        <DialogPanel class="p-6 w-full max-w-sm rounded-lg overflow-hidden bg-white shadow">
          <form @submit.prevent="confirmRename" class="">
            <!-- Name input -->
            <div class="w-full max-w-xl">
              <InputLabel for="name" value="New name" />
              <TextInput
                id="name"
                type="text"
                class="block w-80"
                v-model="form.name"
                required
              />
              <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <!-- Submit and Cancel buttons -->
            <div class="mt-6">
              <PrimaryButton
                class=""
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
              >
                Rename
              </PrimaryButton>

              <SecondaryButton @click="cancelRename" class="ml-2" >
                Cancel
              </SecondaryButton>
            </div>
          </form>
        </DialogPanel>
      </div>
    </Dialog>

  </div>
</template>
