<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryLink from '@/Components/SecondaryLink.vue'
import TextInput from '@/Components/TextInput.vue'
import TextArea from '@/Components/TextArea.vue'
import CustomValueCombobox from '@/Components/CustomValueCombobox.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import { PlusCircleIcon } from '@heroicons/vue/24/outline'
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  position: Object,
  position_families: Array,
  action: String,
});

const form = useForm({
  id: props.position.id,
  name: props.position.name ?? "",
  description: props.position.description ?? "",
  position_family_id: props.position.position_family_id,
  position_family: props.position.position_family,
});

const submit = () => {
  if (props.action === "create") {
    form.post(route('positions.store'));
  } else if (props.action === "edit") {
    form.put(route('positions.update', props.position.id));
  }
};
</script>

<template>
  <form @submit.prevent="submit" class="">

    <!-- Name input -->
    <div class="mt-4 w-full max-w-[22rem]">
      <InputLabel for="name" value="Name" />
      <TextInput
        id="name"
        type="text"
        class="block w-full"
        v-model="form.name"
        required
      />
      <InputError class="mt-2" :message="form.errors.name" />
    </div>

    <!-- Position family input -->
    <div class="">
      <CustomValueCombobox
        class="mt-4 max-w-[16rem]"
        :options="position_families"
        :nullable="true"
        labelText="Position family (optional)"
        :modelValue="form.position_family"
        @update:modelValue="(newPositionFamily) => {
          form.position_family = newPositionFamily
          form.position_family_id = newPositionFamily ? newPositionFamily.id : null
        }"
      />
      <InputError class="mt-2" :message="form.errors.position_family_id" />
      <InputError class="mt-2" :message="form.errors.position_family" />
      <InputError class="mt-2" :message="form.errors['position_family.name']" />
    </div>

    <!-- Description input -->
    <div class="mt-4 w-full">
      <InputLabel for="description" value="Description (optional)" />
      <TextArea
        id="description"
        class="block w-full h-64 text-sm"
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

      <SecondaryLink :href="route('positions.index')" class="ml-2" >
        Cancel
      </SecondaryLink>
    </div>

    <pre class="mt-8">
      {{form}}
    </pre>

  </form>
</template>
