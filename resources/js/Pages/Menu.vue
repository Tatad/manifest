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
		            <span class="text-xs text-gray-500 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-500" :class="(path == '/scanner') ? 'text-white group-hover:text-white' : ''">Scan Items</span>
		        </button>
		    </span>
	      <span @click.prevent="form.get(route('scannedList'))" class="border-r-2 border-gray-400 inline-flex flex-col items-center justify-center px-2" :class="(path == '/scanned-list') ? 'bg-indigo-400 hover:bg-indigo-400' : 'hover:bg-gray-50 dark:hover:bg-gray-800 group'">
	        <button type="button">
	            <span class="text-xs text-gray-500 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-500" :class="(path == '/scanned-list') ? 'text-white group-hover:text-white' : ''">Review Scanned Item</span>
	        </button>
	      </span>
	      <span @click.prevent="form.get(route('UpcLookup'))" class="border-r-2 border-gray-400 inline-flex flex-col items-center justify-center px-2" :class="(path == '/upc-lookup') ? 'bg-indigo-400 hover:bg-indigo-400' : 'hover:bg-gray-50 dark:hover:bg-gray-800 group'">
	        <button type="button">
	            <span class="text-xs text-gray-500 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-500" :class="(path == '/upc-lookup') ? 'text-white group-hover:text-white' : ''">UPC Lookup</span>
	        </button>
	      </span>
	      <span @click.prevent="form.get(route('lookup'))" class="rounded-tr-lg border-gray-400 inline-flex flex-col items-center justify-center px-2 " :class="(path == '/lookup') ? 'bg-indigo-400 hover:bg-indigo-400' : 'hover:bg-gray-50 dark:hover:bg-gray-800 group'">
	        <button type="button">
	            <span class="text-xs text-gray-500 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-500" :class="(path == '/lookup') ? 'text-white group-hover:text-white' : ''">Item # Lookup</span>
	        </button>
	      </span>
	    </div>
	  </div>
</template>

