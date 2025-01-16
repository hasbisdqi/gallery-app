<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Album') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Edit Album</h2>
                    <p class="text-gray-700 dark:text-gray-400">
                        Fill in the form below to edit a new album.
                    </p>
                </div>
                <form action="{{ route('albums.update', $album) }}" method="POST" class="grid space-y-4"
                    enctype="multipart/form-data">
                    @method('PUT')
                    @csrf
                    <div class="mb-3">
                        <x-input-label for="name" value="Album Name" />
                        <x-text-input type="text" class="w-full" :value="old('name', $album->name)" id="name" name="name" required />
                        <x-input-error for="name" class="mt-2" :messages="$errors->get('name')" />
                    </div>
                    <div class="mb-3">
                        <x-input-label for="description" value="Description" />
                        <x-text-input class="w-full" id="description" :value="old('description', $album->description)" name="description" rows="3" required />
                        <x-input-error for="description" class="mt-2" :messages="$errors->get('description')" />

                    </div>
                    <div class="pb-3">
                        <img src="{{Storage::url($album->image)}}" class=" rounded-lg w-[300px]" alt="">
                        <x-input-label for="image" value="Cover Image" />
                        <x-text-input type="file" class="w-full" :value="old('image', $album->image)" id="image" name="image"/>
                        <x-input-error for="image" class="mt-2" :messages="$errors->get('image')" />
                    </div>
                    <div class="flex">
                        <x-primary-button type="submit" class="btn btn-primary">Edit Album</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
