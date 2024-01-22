<script  lang="ts" setup>
  import { ref, computed } from "vue";
  import GuestLayout from '@/Layouts/GuestLayout.vue';
  import { Head } from '@inertiajs/vue3';
  import PrimaryButton from '@/Components/PrimaryButton.vue';
  import TextInput from '@/Components/TextInput.vue';
  import InputError from '@/Components/InputError.vue';
  import InputLabel from '@/Components/InputLabel.vue';
  import { useForm } from '@inertiajs/vue3';

  const props = defineProps({
    item: {
        type: Object,
    }
  });
  const itemInfo = ref(props.item)
  //const itemInfo = ref({});

  const form = useForm({
    item: ''
  });

  const submit = () => {
    form.post(route('lookupItem'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (response) => {
            console.log(response.props.data)
            itemInfo.value = response.props.data
            form.reset()
            form.get(route('lookup'), {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                  
                }
            });
        }
    });
  }
</script>

<template>
  <Head title="Lookup" />
  <GuestLayout>
    <template #header>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Lookup</h2>
    </template>

    <div class="py-3">
      <InputLabel for="item" value="Item Number"/>
      <TextInput
          id="item"
          v-model="form.item"
          type="readonly"
          class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
          placeholder="Item Number"
      />

      <PrimaryButton class="mt-10" @click.prevent="submit">Lookup Record</PrimaryButton>

      {{itemInfo}}

    </div>
  </GuestLayout>
</template>

<style scoped>
a {
  color: #42b983;
}
.information {
  margin-top: 100px;
}
</style>
