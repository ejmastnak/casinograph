<script setup>
import { router, useForm, Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import { ExclamationTriangleIcon, TrashIcon, PlusCircleIcon } from '@heroicons/vue/24/outline'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import MyLink from '@/Components/MyLink.vue'
import FuzzyCombobox from '@/Components/FuzzyCombobox.vue'

const props = defineProps({
  figures: Array,
  figure_families: Array,
  length: Number,
})

const errors = ref({})
const processing = ref(false)
const form = useForm({
  length: props.length,
  excluded_figure_ids: [],
  excluded_figure_family_ids: [],
});

const figureSequence = ref([]);
const noValidStartPosition = ref(null);

// Shown temporarily next to e.g. submit button on error
const errorMessage = ref(null)
function setErrorMessageWithTimeout(message) {
  errorMessage.value = message;
  setTimeout(() => {
    errorMessage.value = null;
  }, 5000);
}

// Used to focus inputs after adding new item
const excludedFiguresWrapperDivsRef = ref([])
const excludedFigureFamiliesWrapperDivsRef = ref([])

const excludedFigures = ref([])
const excludedFigureFamilies = ref([])

var nextExcludedFigureId = excludedFigures.value.length + 1
var nextExcludedFigureFamilyId = excludedFigureFamilies.value.length + 1

function addExcludedFigure() {
  excludedFigures.value.push({
    id: nextExcludedFigureId,
    excluded_figure: {
      id: null,
      name: "",
    }
  });

  // Focus the first text input in the just-added (i.e. last) entryDiv
  // Using setTimeout lets div be inserted into DOM
  setTimeout(() => {
    const input = excludedFiguresWrapperDivsRef.value[excludedFiguresWrapperDivsRef.value.length - 1].querySelectorAll('input')[0];
    if (input) input.focus();
  }, 0)

  nextExcludedFigureId += 1;
}

function removeExcludedFigure(idx) {
  if (idx >= 0 && idx < excludedFigures.value.length) excludedFigures.value.splice(idx, 1);
}

function addExcludedFigureFamily() {
  excludedFigureFamilies.value.push({
    id: nextExcludedFigureFamilyId,
    excluded_figure_family: {
      id: null,
      name: "",
    }
  });

  // Focus the first text input in the just-added (i.e. last) entryDiv
  // Using setTimeout lets div be inserted into DOM
  setTimeout(() => {
    const input = excludedFigureFamiliesWrapperDivsRef.value[excludedFigureFamiliesWrapperDivsRef.value.length - 1].querySelectorAll('input')[0];
    if (input) input.focus();
  }, 0)

  nextExcludedFigureFamilyId += 1;
}

function removeExcludedFigureFamily(idx) {
  if (idx >= 0 && idx < excludedFigureFamilies.value.length) excludedFigureFamilies.value.splice(idx, 1);
}


function submit() {
  form.excluded_figure_ids = excludedFigures.value.filter(excludedFigure => excludedFigure.excluded_figure.id).map(excludedFigure => excludedFigure.excluded_figure.id)
  form.excluded_figure_family_ids = excludedFigureFamilies.value.filter(excludedFigureFamily => excludedFigureFamily.excluded_figure_family.id).map(excludedFigureFamily => excludedFigureFamily.excluded_figure_family.id)

  processing.value = true;
  axios.post(route('figure-sequence.figure-sequence'), form)
    .then((response) => {
      processing.value = false;
      errors.value = {};
      errorMessage.value = null;
      figureSequence.value = response.data.figure_sequence;
      noValidStartPosition.value = response.data.no_valid_start_position;
    })
    .catch((error) => {
      // Response beyond 2xx received from server
      processing.value = false

      if (error.response) {
        if (error.response.data.errors) errors.value = error.response.data.errors;
        else setErrorMessageWithTimeout("There was an error.");
      } else if (error.request) {
        setErrorMessageWithTimeout("There was an error.");
      }
      else setErrorMessageWithTimeout("There was an error.");
    });

}

</script>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
export default {
  layout: AppLayout,
}
</script>

<template>
  <div>
    <Head title="Figure sequence" />

    <h1 class="text-2xl">Figure sequence</h1>

    <p class="mt-1 text-gray-900 max-w-xl">
      Use this page to generate a random sequence of figures.
      You might do this to discover and/or practice new figures combinations that you would not have thought of yourself.
    </p>

    <form class="mt-5 p-4 border rounded-md max-w-xl " @submit.prevent="submit">
      <!-- Length -->
      <div class="w-full max-w-xl">
        <InputLabel for="length" value="Length (how many figures to include)" />
        <TextInput
        min="1"
        id="length"
        type="number"
        class="inline-block w-24 py-1.5"
        v-model="form.length"
        required
      />
        <InputError v-for="error in errors.length" :message="error" />
      </div>

      <!-- Excluded figures -->
      <div class="mt-5">

        <InputLabel value="Figures to exclude (optional)" />

        <ol class="ml-5 list-decimal" >
          <li v-for="(excludedFigure, idx) in excludedFigures" :key="excludedFigure.id" >

            <div ref="excludedFiguresWrapperDivsRef" class="flex items-center">

              <div class="mt-2 w-full max-w-[22rem]">
                <FuzzyCombobox
                class="ml-1 block w-full"
                searchKey="name"
                inputClasses="text-sm"
                :bespokeDisplayForFigures="true"
                :options="figures"
                :modelValue="excludedFigure.excluded_figure"
                @update:modelValue="(newValue) => {
                excludedFigure.excluded_figure.id = newValue.id;
                excludedFigure.excluded_figure.name = newValue.name;
                }"
              />
              </div>

              <button
                type="button"
                @click="removeExcludedFigure(idx)"
                class="ml-1 p-1 text-gray-700 hover:text-red-700"
              >
                <TrashIcon class="w-6 h-6" />
              </button>

            </div>
            <InputError v-for="error in errors['excluded_figure_ids.' + idx.toString()]" :message="error" />

          </li>
        </ol>

        <div class="mt-2 max-w-lg">
          <InputError v-for="error in errors.excluded_figure_ids" :message="error" />
        </div>

        <!-- Add an excluded figure -->
        <button
          type="button"
          @click="addExcludedFigure"
          class="mt-2 inline-flex items-center w-fit pl-2 pr-4 py-1 bg-white border border-gray-300 rounded-lg text-gray-500 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
        >
          <PlusCircleIcon class="w-6 h-6"/>
          <span class="ml-2 text-sm">Add figure</span>
        </button>

      </div>

      <!-- Excluded figure families -->
      <div class="mt-5">

        <InputLabel value="Figures families to exclude (optional)" />

        <ol class="ml-5 list-decimal" >
          <li v-for="(excludedFigureFamily, idx) in excludedFigureFamilies" :key="excludedFigureFamily.id" >

            <div ref="excludedFigureFamiliesWrapperDivsRef" class="flex items-center">

              <div class="mt-2 w-full max-w-[22rem]">
                <FuzzyCombobox
                class="ml-1 block w-full"
                searchKey="name"
                inputClasses="text-sm"
                :options="figure_families"
                :modelValue="excludedFigureFamily.excluded_figure_family"
                @update:modelValue="(newValue) => {
                excludedFigureFamily.excluded_figure_family.id = newValue.id;
                excludedFigureFamily.excluded_figure_family.name = newValue.name;
                }"
              />
              </div>

              <button
                type="button"
                @click="removeExcludedFigureFamily(idx)"
                class="ml-1 p-1 text-gray-700 hover:text-red-700"
              >
                <TrashIcon class="w-6 h-6" />
              </button>

            </div>

            <InputError v-for="error in errors['excluded_figure_family_ids.' + idx.toString()]" :message="error" />
          </li>
        </ol>

        <div class="mt-2 max-w-lg">
          <InputError v-for="error in errors.excluded_figure_family_ids" :message="error" />
        </div>

        <!-- Add an excluded figure family -->
        <button
          type="button"
          @click="addExcludedFigureFamily"
          class="mt-2 inline-flex items-center w-fit pl-2 pr-4 py-1 bg-white border border-gray-300 rounded-lg text-gray-500 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
        >
          <PlusCircleIcon class="w-6 h-6"/>
          <span class="ml-2 text-sm">Add figure family</span>
        </button>

      </div>

      <PrimaryButton
        class="mt-5"
        :class="{ 'opacity-25': processing }"
        :disabled="processing"
      >
        Generate
      </PrimaryButton>

      <!-- Error message -->
      <div v-if="errorMessage" class="bg-red-50 px-3 py-2 rounded-lg">
        <p class="flex items-center text-red-950 leading-tight">
          <ExclamationTriangleIcon class="-ml-1 w-6 h-6 text-gray-600 shrink-0" />
          <span class="ml-2">{{errorMessage}}</span>
        </p>
      </div>

    </form>

    <!-- Figure sequence text -->
    <div v-if="figureSequence && figureSequence.length > 0" class="mt-3 ml-1">
      <h2 class="text-lg text-gray-700">Figure sequence</h2>
      <ol
        class="mt-1 list-decimal ml-5 space-y-1 gap-x-5 grid sm:grid-cols-2 sm:grid-flow-col"
      >
        <li
          v-for="(figure, idx) in figureSequence"
          :class="{
            'sm:col-start-1': idx < figureSequence.length/2,
            'sm:col-start-2': idx >= figureSequence.length/2,
          }"
        >
          <MyLink class="inline-block" :href="route('figures.show', figure.figure_id)" >
            {{figure.figure_name}}
          </MyLink>
          <p class="-mt-1 text-sm text-gray-600">
            From
            <MyLink class="font-medium" :href="route('positions.show',figure.from_position_id)" >
              {{figure.from_position_name}}
            </MyLink>
            to
            <MyLink class="font-medium" :href="route('positions.show', figure.to_position_id)" >
              {{figure.to_position_name}}
            </MyLink>
          </p>
        </li>
      </ol>
    </div>

    <!-- No valid start position! -->
    <div v-if="noValidStartPosition" class="!mt-3 pt-2 text-center max-w-sm mx-auto" >
      <ExclamationTriangleIcon class="w-6 h-6 text-yellow-800 shrink-0 mx-auto"/>
      <p class="text-yellow-900">
        You have no positions with outgoing figures from which to begin the figure sequence!
      </p>
    </div>

  </div>

</template>
