<script  lang="ts" setup>
  import { ref, computed } from "vue";
  import GuestLayout from '@/Layouts/GuestLayout.vue';
  import Menu from '@/Pages/Menu.vue';
  import { Head } from '@inertiajs/vue3';
  import PrimaryButton from '@/Components/PrimaryButton.vue';
  import { useForm } from '@inertiajs/vue3';
  import { createToaster } from "@meforma/vue-toaster";

  const form = useForm({
    image: ''
  });

  let onChange = (event) => {
      form.image = event.target.files ? event.target.files[0] : null;
  }

  const submit = () => {
    if (form.image) {
      // form.post(route('scan'), {
      //     preserveScroll: true,
      //     preserveState: true,
      //     onSuccess: (response) => {
      //         console.log(response.props.message)
      //         form.reset()
      //         form.get(route('scanner'), {
      //             preserveScroll: true,
      //             preserveState: true,
      //             onSuccess: () => {
      //                 form.image = ''
      //                 toaster.info(response.props.message, {
      //                   position: "top-right",
      //                 });
      //             }
      //         });
      //     }
      // });
      axios.post('/scan',form).then((response) => {
        console.log(response.data);
      })
    }
  }

  const toaster = createToaster({ /* options */ });

</script>

<template>
  <Head title="Scanned List" />
  <GuestLayout>
    <template #header>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">Scanned List</h2>
    </template>

    <div class="py-3">
      <span class="tag">
        <!-- <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="file_input"></label> -->
        <div>
          <form enctype="multipart/form-data">
          <input type="file" @change="onChange">
          <PrimaryButton class="mt-10" @click.prevent="submit">Submit</PrimaryButton>
        </form>
  <!-- <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-white focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" id="file_input" type="file"  accept="image/*" capture="camera" @change="onChange"> -->
  
          </div>

        
      </span>
    </div>
  </GuestLayout>

    <Menu></Menu>
</template>

<style scoped>
a {
  color: #42b983;
}
.information {
  margin-top: 100px;
}
</style>
