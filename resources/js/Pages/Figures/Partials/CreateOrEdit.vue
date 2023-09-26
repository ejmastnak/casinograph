<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryLink from '@/Components/SecondaryLink.vue'
import TextInput from '@/Components/TextInput.vue'
import TextArea from '@/Components/TextArea.vue'
import CustomValueCombobox from '@/Components/CustomValueCombobox.vue'
import FuzzyCombobox from '@/Components/FuzzyCombobox.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import { PlusCircleIcon } from '@heroicons/vue/24/outline'
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  figure: Object,
  figure_families: Array,
  positions: Array,
  action: String,
});

const form = useForm({
  id: props.figure.id,
  name: props.figure.name ?? "",
  description: props.figure.description ?? "",
  from_position_id: props.figure.from_position_id,
  from_position: props.figure.from_position,
  to_position_id: props.figure.to_position_id,
  to_position: props.figure.to_position,
  figure_family_id: props.figure.figure_family_id,
  figure_family: props.figure.figure_family,
});

const submit = () => {
  if (props.action === "create") {
    form.post(route('figures.store'));
  } else if (props.action === "edit") {
    form.put(route('figures.update', props.figure.id));
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
        required
      />
      <InputError class="mt-2" :message="form.errors.name" />
    </div>

    <!-- From/to position -->
    <div class="mt-3">
      <FuzzyCombobox
        class="w-80"
        labelText="From position"
        searchKey="name"
        :options="positions"
        :modelValue="form.from_position"
        @update:modelValue="(newValue) => {
          form.from_position = newValue;
          form.from_position_id = newValue.id;
        }"
      />
      <InputError class="mt-2" :message="form.errors.from_position_id" />
    </div>

    <div class="mt-3 flex items-start border-b pb-6 border-gray-200">
      <div>
        <FuzzyCombobox
          class="w-80"
          labelText="To position"
          searchKey="name"
          :options="positions"
          :modelValue="form.to_position"
          @update:modelValue="(newValue) => {
            form.to_position = newValue;
            form.to_position_id = newValue.id;
          }"
        />
        <InputError class="mt-2" :message="form.errors.to_position_id" />
      </div>
    </div>

    <!-- Figure family and weight -->
    <div class="mt-5 flex items-start">

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

    <!-- Submit and Cancel buttons -->
    <div class="mt-6">
      <PrimaryButton
        class=""
        :class="{ 'opacity-25': form.processing }"
        :disabled="form.processing"
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
