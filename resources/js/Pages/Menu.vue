<script setup>
	import { computed, ref } from "vue";
    import { useRouter } from "vue-router";
  import { useForm } from '@inertiajs/vue3';
	const path = (new URL(location.href).pathname);
	
	const form = useForm({});
</script>

<template>
    <div class="fixed bottom-0 left-0 z-50 h-16 bg-white border-t border-gray-200 dark:bg-gray-700 dark:border-gray-600 rounded-tl-lg rounded-tr-lg xs:w-auto xs:mx-4 sm:w-full sm:mx-0">
	    <div class="grid h-full max-w-lg grid-cols-4 mx-auto font-medium">
	    	<span @click.prevent="form.get(route('scanner'))" :class="(path == '/scanner') ? 'bg-indigo-400 hover:bg-indigo-400 text-white' : 'hover:bg-gray-50 dark:hover:bg-gray-800 group'" class="rounded-tl-lg border-r-2 border-gray-400 inline-flex flex-col items-center justify-center px-2">
		        <button type="button">
		            <span class="text-xs text-gray-500 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-500" :class="(path == '/scanner') ? 'text-white group-hover:text-white' : ''"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
</svg>
</span>
		        </button>
		    </span>
	      <span @click.prevent="form.get(route('scannedList'))" class="border-r-2 border-gray-400 inline-flex flex-col items-center justify-center px-2" :class="(path == '/scanned-list') ? 'bg-indigo-400 hover:bg-indigo-400' : 'hover:bg-gray-50 dark:hover:bg-gray-800 group'">
	        <button type="button">
	            <span class="text-xs text-gray-500 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-500" :class="(path == '/scanned-list') ? 'text-white group-hover:text-white' : ''"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
</svg>
</span>
	        </button>
	      </span>
	      <span @click.prevent="form.get(route('UpcLookup'))" class="border-r-2 border-gray-400 inline-flex flex-col items-center justify-center px-2" :class="(path == '/upc-lookup') ? 'bg-indigo-400 hover:bg-indigo-400' : 'hover:bg-gray-50 dark:hover:bg-gray-800 group'">
	        <button type="button">
	            <span class="text-xs text-gray-500 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-500 inline-flex flex-col items-center justify-center" :class="(path == '/upc-lookup') ? 'text-white group-hover:text-white' : ''"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
</svg>
UPC Lookup</span>
	        </button>
	      </span>
	      <span @click.prevent="form.get(route('lookup'))" class="rounded-tr-lg border-gray-400 inline-flex flex-col items-center justify-center px-2 " :class="(path == '/lookup') ? 'bg-indigo-400 hover:bg-indigo-400' : 'hover:bg-gray-50 dark:hover:bg-gray-800 group'">
	        <button type="button">
	            <span class="text-xs text-gray-500 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-500 inline-flex flex-col items-center justify-center" :class="(path == '/lookup') ? 'text-white group-hover:text-white' : ''"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
</svg>Item # Lookup</span>
	        </button>
	      </span>
	    </div>
	  </div>
</template>

