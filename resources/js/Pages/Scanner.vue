<script  lang="ts" setup>
  import { ref, computed } from "vue";
  import GuestLayout from '@/Layouts/GuestLayout.vue';
  import Menu from '@/Pages/Menu.vue';
  import { Head } from '@inertiajs/vue3';
  import PrimaryButton from '@/Components/PrimaryButton.vue';
  import { useForm } from '@inertiajs/vue3';
  import { createToaster } from "@meforma/vue-toaster";

  const form = useForm({
    image: {}
  });
  const image = ref('');
  let onChange = async (event) => {
    //form.image = event.target.files ? event.target.files[0] : null;
    // const imageFile = event.target.files[0];
    image.value = event.target.files[0];
    

  }

  const fileUpload = ref(null);

  const buttonDisabled = ref(false);

  const submit = () => {
    console.log('test')
    const formdata = new FormData();
        formdata.append('image', image.value);
        console.log(formdata);
    // if (form.image) {
    //   buttonDisabled.value = true;
    //   form.post(route('scan'), {
    //       preserveScroll: true,
    //       preserveState: true,
    //       onSuccess: (response) => {
    //           form.reset()
    //           form.get(route('scanner'), {
    //               preserveScroll: true,
    //               preserveState: true,
    //               onSuccess: () => {
    //                   buttonDisabled.value = false;
    //                   form.image = ''
    //                   toaster.info(response.props.message, {
    //                     position: "top-right",
    //                   });
    //               }
    //           });
    //       }
    //   });
    // }
    axios.post('/scan',formdata).then((response) => {
          window.location.reload('/')
        });
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
          <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-white focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" type="file" ref="fileUpload" @change="onChange($event)"  accept="image/*">
          <PrimaryButton v-if="buttonDisabled == false" class="mt-10" @click.prevent="submit">Submit</PrimaryButton>
          <button v-if="buttonDisabled == true" type="button" class="mt-10 bg-indigo-400 h-max w-max rounded-lg text-white font-bold hover:bg-indigo-300 hover:cursor-not-allowed duration-[500ms,800ms]" disabled>
              <div class="flex items-center justify-center m-[10px]"> 
                  <div class="h-5 w-5 border-t-transparent border-solid animate-spin rounded-full border-white border-4"></div>
                  <div class="ml-2"> Processing... </div>
              </div>
          </button>
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
