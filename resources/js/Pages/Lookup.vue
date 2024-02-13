<script  lang="ts" setup>
  import { ref, computed } from "vue";
  import GuestLayout from '@/Layouts/GuestLayout.vue';
  import { Head } from '@inertiajs/vue3';
  import PrimaryButton from '@/Components/PrimaryButton.vue';
  import SecondaryButton from '@/Components/SecondaryButton.vue';
  import TextInput from '@/Components/TextInput.vue';
  import InputError from '@/Components/InputError.vue';
  import InputLabel from '@/Components/InputLabel.vue';
  import { useForm } from '@inertiajs/vue3';
  import Menu from '@/Pages/Menu.vue';
  import Modal from '@/Components/Modal.vue';
  import { createToaster } from "@meforma/vue-toaster";

  const props = defineProps({
    item: {
        type: Object,
    }
  });
  const toaster = createToaster({ /* options */ });
  const itemInfo = ref(props.item);
  const upcCode = ref('');
  const previewItemModal = ref(false);
  const buttonDisabled = ref(false);

  const form = useForm({
    id: 0,
    item: '',
    upc_code: '',
    description: '',
    isEmpty: 0,
    image: '',
    type: '',
    msrp: 0,
    retail_price: 0,
    scanMethod: 'item'
  });

  const submit = () => {
    itemInfo.value = [];
    if(selected.value == 'upc'){
      form.item = 0;
    }

    if(selected.value == 'item'){
      form.upc_code = '';
    }
    form.post(route('lookupItem'), {
        onSuccess: (response) => {
            console.log(response.props.data)
            if(response.props.data.isEmpty == 0){
              itemInfo.value = response.props.data
              itemInfo.value.upc_code = response.props.data.upc_code

              previewItemModal.value = true
              itemInfo.value.confirmed = false;
            }else{

              itemInfo.value = response.props.data
              itemInfo.value.confirmed = true;
            }
            
            
            form.get(route('lookup'), {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                  
                }
            });
        }
    });
  }

  const closeModal = () => {
    form.get(route('lookup'), {
        onSuccess: () => {
          form.reset();
          form.item = '';
          form.description = '';
          form.msrp = '';
          form.retail_price = '';
          form.image = '';
          form.type = '';
          form.upc_code = '';
          form.image = '';
          itemInfo.value = [];
          previewItemModal.value = false;
          buttonDisabled.value = false;
        }
    });
  };

  const save = () => {
    form.description = itemInfo.value.description;
    form.msrp = itemInfo.value.msrp;
    form.retail_price = itemInfo.value.retail_price;
    form.item = itemInfo.value.item;
    form.upc_code = itemInfo.value.upc_code
    form.type = itemInfo.value.type
    buttonDisabled.value = true;
    //form.image = itemInfo.value.images
    form.post(route('addItem'), {
        onSuccess: () => {
          form.get(route('lookup'), {
            onSuccess: () => {
              form.upc_code = ''
              itemInfo.value = [];
              previewItemModal.value = false;
              buttonDisabled.value = false;
              toaster.info('Item saved successfully.', {
                position: "top-right",
              });
            }
        });
        }
    });
  }

  let onChange = (event) => {
    form.image = event.target.files ? event.target.files[0] : null;
  }

  const selected = ref('item')
</script>

<template>
  <Head title="Lookup" />
  <GuestLayout>
    <template #header>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Item Lookup</h2>
    </template>

    <div class="py-3 mb-40">

      <div>
        <InputLabel class="dark:text-white" for="item" value="Item Number"/>
        <div class="grid grid-cols-2 gap-1">
          <TextInput
              id="item"
              v-model="form.item"
              type="readonly"
              class="border-solid border-2 border-black-600 p-2 mt-1 block block w-full"
              placeholder="Item Number"
          />
        </div>
      </div>

      <PrimaryButton class="mt-10 dark:bg-gray-400 dark:text-white" v-if="form.item.length == 0" disabled>Lookup Item Record</PrimaryButton>
      <PrimaryButton class="mt-10" @click.prevent="submit" v-if="form.item.length > 0">Lookup Item Record</PrimaryButton>

      <!--preview item modal-->
      <Modal :show="previewItemModal" @close="closeModal">
          <div class="p-6" v-if="itemInfo">
            <h2 class="text-2xl font-extrabold dark:text-black">Is this the item you are looking for?</h2>
            <div class="mt-6">
               <div class="mt-2">
                  <InputLabel class="dark:text-black" for="item" value="Item Description"/>
                  <TextInput
                      id="item"
                      v-model="itemInfo.description"
                      type="text"
                      class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
                      placeholder="Item Description" readonly
                  />
                </div>

                <div class="mt-2">
                  <InputLabel for="item" class="dark:text-black" value="Item Number"/>
                  <TextInput
                      id="item"
                      v-model="itemInfo.item"
                      type="number"
                      class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
                      placeholder="Item Number" readonly
                  />
                </div>

                <div class="mt-2">
                  <InputLabel for="item" value="Item UPC Code"/>
                  <TextInput
                      id="item"
                      v-model="itemInfo.upc_code"
                      type="number"
                      class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
                      placeholder="Item UPC Code" readonly
                  />
                </div>

                <div class="mt-2">
                  <InputLabel for="item" value="Item MSRP"/>
                  <TextInput
                      id="item"
                      v-model="itemInfo.msrp"
                      type="number"
                      class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
                      placeholder="Item MSRP" readonly
                  />
                </div>

                <div class="mt-2">
                  <InputLabel for="item" value="Item Retail Price"/>
                  <TextInput
                      id="item"
                      v-model="itemInfo.retail_price"
                      type="number"
                      class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
                      placeholder="Item Retail Price" readonly
                  />
                </div>

                <div class="mt-2">
                  <InputLabel for="item" value="Item Type"/>
                  <TextInput
                      id="item"
                      v-model="itemInfo.type"
                      type="text"
                      class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
                      placeholder="Item Type" readonly
                  />
                </div>

                <div class="mt-2" v-if="itemInfo.images" v-for="image in JSON.parse(itemInfo.images)">
                  <InputLabel for="item" value="Item image"/>
                  <img :src="image"  class="pt-6 object-cover w-52">
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <SecondaryButton class="mr-4" @click="closeModal"> No </SecondaryButton>
                <PrimaryButton class="mr-4" @click="itemInfo.confirmed = true;previewItemModal = false"> Yes </PrimaryButton>
            </div>
          </div>
      </Modal>

      <div class="mt-6 border-2 pl-5 pt-6" v-if="itemInfo && itemInfo.confirmed == true">
        <div v-if="buttonDisabled == false" class="dark:text-white float-right pr-5" @click.prevent="itemInfo = [];form.item = ''">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
          </svg>

        </div>
        <div class="clear-both pt-5"></div>
        <h2 class="text-2xl pb-3 font-extrabold dark:text-white" v-if="itemInfo.isEmpty == 1">Item not found. Please enter the details below.</h2>
        <div class="mt-2">
          <InputLabel for="item" class="dark:text-white" value="Item Description"/>
          <TextInput
              id="item"
              v-model="itemInfo.description"
              type="text"
              class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
              placeholder="Item Description" required
          />

          <InputError class="mt-2" :message="form.errors.description" />
        </div>

        <div class="mt-2">
          <InputLabel for="item" class="dark:text-white" value="Item Number"/>
          <TextInput
              id="item"
              v-model="itemInfo.item"
              type="number"
              maxlength="8"
              oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
              class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4 bg-gray-200"
              placeholder="Item Number" :readonly="selected == 'item'"
          />
        </div>

        <div class="mt-2">
          <InputLabel for="item" class="dark:text-white" value="Item UPC Code"/>
          <TextInput
              id="item"
              v-model="itemInfo.upc_code"
              type="number"
              class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
              placeholder="Item UPC Code" :readonly="selected == 'upc'" required
          />
        </div>

        <div class="mt-2">
          <InputLabel for="item" class="dark:text-white" value="Item MSRP"/>
          <TextInput
              id="item"
              v-model="itemInfo.msrp"
              type="number"
              class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
              placeholder="Item MSRP" required
          />
        </div>

        <div class="mt-2">
          <InputLabel for="item" class="dark:text-white" value="Item Retail Price"/>
          <TextInput
              id="item"
              v-model="itemInfo.retail_price"
              type="number"
              class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
              placeholder="Item Retail Price" required
          />
        </div>

        <div class="mt-2" v-if="itemInfo.images" v-for="image in JSON.parse(itemInfo.images)">
          <img :src="image"  class="pt-6 object-cover w-52">
        </div>

        <div class="mt-2">
          <InputLabel class="dark:text-white" for="item" value="Item Image"/>
          <input class="block text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 w-3/4" id="file_input" type="file"  accept="image/*" capture="camera" @change="onChange">
        </div>

        <div class="mt-2">
          <InputLabel class="dark:text-white" for="item" value="Item type"/>
          <select
            class="w-3/4"
            v-model="itemInfo.type"
            name="type"
          >
            <option value="Clothing">Clothing</option>
            <option value="Mixed">Mixed</option>
          </select>
        </div>

        <div class="mt-6 mb-6 flex justify-end">
            <PrimaryButton v-if="buttonDisabled == false" class="mr-4" @click="save"> Submit </PrimaryButton>


            <button v-if="buttonDisabled == true" type="button" class="bg-indigo-400 h-max w-max rounded-lg text-white font-bold hover:bg-indigo-300 hover:cursor-not-allowed duration-[500ms,800ms]" disabled>
              <div class="flex items-center justify-center m-[10px]"> 
                  <div class="h-5 w-5 border-t-transparent border-solid animate-spin rounded-full border-white border-4"></div>
                  <div class="ml-2"> Processing... </div>
              </div>
          </button>
        </div>
      </div>

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
