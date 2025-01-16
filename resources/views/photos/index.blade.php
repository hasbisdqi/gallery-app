<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('photos') }}
            </h2>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @foreach ($albums as $album)
                <div class="mb-6 border-b pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">{{ $album->name }}</h2>
                    <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
                        @foreach ($album->photos as $photo)
                            <div class="photo bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden relative">
                                <a class="inset-0 size-full absolute" href="{{ route('photos.show', $photo) }}"></a>
                                <img src="{{ Storage::url($photo->image) }}" class="w-full h-48 object-cover">
                                <div class="p-4">
                                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                                        {{ $photo->name }}
                                    </h2>
                                    <p class="text-gray-600 dark:text-gray-400">{{ $photo->description }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
