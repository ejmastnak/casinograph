<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import PlainButton from '@/Components/PlainButton.vue'
import SecondaryLink from '@/Components/SecondaryLink.vue'
import TextInput from '@/Components/TextInput.vue'
import TextArea from '@/Components/TextArea.vue'
import CustomValueCombobox from '@/Components/CustomValueCombobox.vue'
import FuzzyCombobox from '@/Components/FuzzyCombobox.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import { PlusCircleIcon, TrashIcon, Bars3Icon } from '@heroicons/vue/24/outline'
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useSortable } from '@vueuse/integrations/useSortable'

const props = defineProps({
  compound_figure: Object,
  figure_families: Array,
  figures: Array,
  action: String,
});

const form = useForm({
  id: props.compound_figure.id,
  name: props.compound_figure.name ?? "",
  description: props.compound_figure.description ?? "",
  figure_family_id: props.compound_figure.figure_family_id,
  figure_family: props.compound_figure.figure_family,
  figure_ids: [],
  compound_figure_videos: [],
});

const compoundFigureFigures = ref(props.compound_figure.compound_figure_figures.map((cff, idx) => ({
  id: idx,
  compound_figure_figure: {
    figure_id: cff.figure_id,
    figure: cff.figure,
  }
})))
var nextCompoundFigureFigureId = compoundFigureFigures.value.length

const list = ref(null)
useSortable(list, compoundFigureFigures.value, {
  handle: '.handle',
  animation: 250,
})

const compoundFigureVideos = ref(props.compound_figure.compound_figure_videos.map((cfv, idx) => ({
  id: idx,
  compound_figure_video: {
    url: cfv.url,
    description: cfv.description,
  }
})));
var nextCompoundFigureVideoId = compoundFigureVideos.value.length;

// Used to focus inputs after adding new figure video
const compoundFigureVideoEntryDivRefs = ref([])

function addCompoundFigureVideo() {
  compoundFigureVideos.value.push({
    id: nextCompoundFigureVideoId,
    compound_figure_video: {
      url: "",
      description: "",
    }
  });

  // Focus the first text input in the just-added (i.e. last) entryDiv
  // Using setTimeout lets div be inserted into DOM
  setTimeout(() => {
    const input = compoundFigureVideoEntryDivRefs.value[compoundFigureVideoEntryDivRefs.value.length - 1].querySelectorAll('input')[0];
    if (input) input.focus();
  }, 0)

  nextCompoundFigureVideoId += 1;
}

// Remove figure video with given index from list
function removeCompoundFigureVideo(idx) {
  if (idx >= 0 && idx < compoundFigureVideos.value.length) compoundFigureVideos.value.splice(idx, 1);
}

// Boolean flag for the presence of incompatible figures
const incompatibleCompoundFigureFigures = computed(() => {
  let incompatible = false
  for (let i = 1; i < compoundFigureFigures.value.length; i++) {
    if (compoundFigureFigures.value[i].compound_figure_figure.figure === null || compoundFigureFigures.value[i - 1].compound_figure_figure.figure === null) continue;
    if (compoundFigureFigures.value[i].compound_figure_figure.figure.from_position_id !== compoundFigureFigures.value[i - 1].compound_figure_figure.figure.to_position_id) {
      incompatible = true;
      break;
    }
  }
  return incompatible;
})

// Used to focus input after adding new figure
const compoundFigureFigureEntryDivRefs = ref([])

function addCompoundFigureFigure() {
  compoundFigureFigures.value.push({
    id: nextCompoundFigureFigureId,
    compound_figure_figure: {
      figure_id: null,
      figure: null,
    }
  });

  // Focus the first text input in the just-added (i.e. last) entryDiv
  // Using setTimeout lets div be inserted into DOM
  setTimeout(() => {
    const input = compoundFigureFigureEntryDivRefs.value[compoundFigureFigureEntryDivRefs.value.length - 1].querySelectorAll('input')[0];
    if (input) input.focus();
  }, 0)

  nextCompoundFigureFigureId += 1;
}

// Remove CompoundFigureFigure with given index from list
function removeCompoundFigureFigure(idx) {
  if (idx >= 0 && idx < compoundFigureFigures.value.length) compoundFigureFigures.value.splice(idx, 1);
}

const submit = () => {
  if (incompatibleCompoundFigureFigures.value) { return }
  form.figure_ids = compoundFigureFigures.value.map(cff => cff.compound_figure_figure.figure_id);
  form.compound_figure_videos = compoundFigureVideos.value.map(cfv => cfv.compound_figure_video);
  if (props.action === "create") {
    form.post(route('compound-figures.store'));
  } else if (props.action === "edit") {
    form.put(route('compound-figures.update', props.compound_figure.id));
  }
};

</script>

<template>
  <form @submit.prevent="submit" class="">

    <!-- Name input -->
    <div class="mt-4 w-full max-w-xl">
      <InputLabel for="name" value="Name" />
      <TextInput
        id="name"
        type="text"
        class="block w-80"
        v-model="form.name"
        :autofocus="action === 'create'"
        required
      />
      <InputError class="mt-2" :message="form.errors.name" />
    </div>

    <!-- CompoundFigureFigures -->
    <div class="mt-6 border-y border-gray-200 w-fit pt-4 pb-5">
      <h2 class="text-md text-gray-700">Figure sequence</h2>
      <p class="text-sm text-gray-500">Add at least two figures</p>

      <ol
        ref="list"
        class="mt-2 list-decimal ml-5 space-y-3"
      >
        <li v-for="(cff, idx) in compoundFigureFigures" :key="cff.id" >

          <div ref="compoundFigureFigureEntryDivRefs" class="flex items-center">

            <FuzzyCombobox
              class="w-80 ml-2"
              searchKey="name"
              inputClasses="text-sm"
              :bespokeDisplayForCompoundFigureFigures="true"
              :options="(idx > 0 && compoundFigureFigures[idx - 1].compound_figure_figure.figure) ? figures.filter(figure => figure.from_position_id === compoundFigureFigures[idx - 1].compound_figure_figure.figure.to_position_id) : figures"
              :modelValue="cff.compound_figure_figure.figure"
              @update:modelValue="(newValue) => {
                cff.compound_figure_figure.figure_id = newValue.id;
                cff.compound_figure_figure.figure = newValue;
              }"
            />

            <button
              type="button"
              @click="removeCompoundFigureFigure(idx)"
              class="ml-1 p-1 text-gray-700 hover:text-red-700"
            >
              <TrashIcon class="w-6 h-6" />
            </button>

            <Bars3Icon class="handle w-6 h-6 text-gray-600 hover:cursor-pointer" />

          </div>
          <!-- Figure's from position and to position -->
          <p v-if="cff.compound_figure_figure.figure" class="mt-1 ml-3 text-sm text-gray-600">
            ({{cff.compound_figure_figure.figure.from_position.name}} to {{cff.compound_figure_figure.figure.to_position.name}})
          </p>

          <div class="ml-3 ">
            <!-- Warn user about incompatible neighboring figures -->
            <InputError
              v-if="idx > 0
                && cff.compound_figure_figure.figure !== null
                && compoundFigureFigures[idx - 1].compound_figure_figure.figure !== null
                && cff.compound_figure_figure.figure.from_position_id !== compoundFigureFigures[idx - 1].compound_figure_figure.figure.to_position_id"
              class="mt-2 max-w-md"
              :message="'Incompatible starting and ending positions: this figure starts from ' + cff.compound_figure_figure.figure.from_position.name + ', but the previous figure ends in ' + compoundFigureFigures[idx - 1].compound_figure_figure.figure.to_position.name + '.'"
            />
            <InputError class="mt-2" :message="form.errors['figure_ids.' + idx.toString()]" />
          </div>

        </li>
      </ol>
      <p v-if="compoundFigureFigures.length === 0" class="mt-3 text-gray-500 text-sm" >
        This compound figure's figure sequence is empty.
        Consider adding some figures.
      </p>

      <!-- Error -->
      <InputError class="mt-2 max-w-lg" :message="form.errors.figure_ids" />

      <!-- Add CompoundFigureFigure -->
      <button
        type="button"
        @click="addCompoundFigureFigure"
        class="mt-4 inline-flex compound_figure_figures-center w-fit pl-2 pr-4 py-1 bg-white border border-gray-300 rounded-lg text-gray-500 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
      >
        <PlusCircleIcon class="w-6 h-6"/>
        <span class="ml-2 text-sm">Add figure</span>
      </button>

    </div>

    <!-- Figure family and weight -->
    <div class="mt-6 flex items-start">

      <!-- Figure family input -->
      <div>
        <CustomValueCombobox
          class="w-72"
          :options="figure_families"
          :nullable="true"
          labelText="Figure family (optional)"
          :modelValue="form.figure_family"
          @update:modelValue="(newFigureFamily) => {
            form.figure_family = newFigureFamily
            form.figure_family_id = newFigureFamily ? newFigureFamily.id : null
          }"
        />
        <InputError class="mt-2" :message="form.errors.figure_family_id" />
        <InputError class="mt-2" :message="form.errors.figure_family" />
        <InputError class="mt-2" :message="form.errors['figure_family.name']" />
      </div>

    </div>

    <!-- Description input -->
    <div class="mt-5 w-full">
      <InputLabel for="description" value="Description (optional)" />
      <TextArea
        id="description"
        class="block w-full h-64 text-sm max-w-xl"
        v-model="form.description"
      />
      <InputError class="mt-2" :message="form.errors.description" />
    </div>

    <!-- CompoundFigureVideos -->
    <div class="mt-5 pb-8 border-b border-gray-200">
      <h2 class="text-md text-gray-700">Videos (optional)</h2>

      <ol
        ref="list"
        class="mt-2 space-y-3"
      >
        <li v-for="(cfv, idx) in compoundFigureVideos" :key="cfv.id" >
          <!-- Wrapper div to allow focusing URL input programatically -->
          <div ref="compoundFigureVideoEntryDivRefs" class="bg-gray-50 p-3 rounded-lg border border-gray-100">

            <!-- Div to align URL input and delete/rearrange icons -->
            <div class="flex items-center">
              <!-- URL input -->
              <div class="w-full">
                <InputLabel :for="'compound-figure-video-url-' + cfv.id" value="URL" />
                <TextInput
                :id="'compound-figure-video-url-' + cfv.id"
                type="text"
                class="w-full max-w-xl"
                v-model="cfv.compound_figure_video.url"
                required
              />
                <InputError class="mt-2" :message="form.errors['compound_figure_videos.' + idx.toString() + '.url']" />
              </div>

              <!-- Hardcoded top margin aligns with text input -->
              <PlainButton
                type="button"
                @click="removeCompoundFigureVideo(idx)"
                class="ml-auto p-1 mt-[1.25rem] text-gray-700 inline-flex"
              >
                <TrashIcon class="-ml-1 w-6 h-6 text-gray-600 shrink-0" />
                <span class="ml-1">Delete</span>
              </PlainButton>

            </div>

            <!-- Description input -->
            <div class="mt-3 w-full">
              <InputLabel :for="'compound-figure-video-description-' + cfv.id" value="Description (optional)" />
              <TextArea
              id="'compound-figure-video-description-' + cfv.id"
              class="block w-full h-20 text-sm max-w-xl"
              v-model="cfv.compound_figure_video.description"
            />
              <InputError class="mt-2" :message="form.errors['compound_figure_videos.' + idx.toString() + '.description']" />
            </div>

          </div>

        </li>
      </ol>
      <p v-if="compoundFigureVideos.length === 0" class="text-gray-500 text-sm" >
        No videos added for this figure.
      </p>

      <InputError class="mt-2 max-w-lg" :message="form.errors.compound_figure_videos" />

      <!-- Add CompoundFigureVideo -->
      <button
        type="button"
        @click="addCompoundFigureVideo"
        class="mt-4 inline-flex compound_figure_figures-center w-fit pl-2 pr-4 py-1 bg-white border border-gray-300 rounded-lg text-gray-500 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
      >
        <PlusCircleIcon class="w-6 h-6"/>
        <span class="ml-2 text-sm">Add video</span>
      </button>

    </div>

    <!-- Submit and Cancel buttons -->
    <div class="mt-6">
      <PrimaryButton
        :class="{ 'opacity-25': form.processing || incompatibleCompoundFigureFigures }"
        :disabled="form.processing || incompatibleCompoundFigureFigures"
      >
        <span v-if="action === 'edit'">Update</span>
        <span v-else>Create</span>
      </PrimaryButton>

      <SecondaryLink :href="route('figures.index')" class="ml-2" >
        Cancel
      </SecondaryLink>
    </div>

  </form>
</template>
