@extends('layouts.master')

@section('title', 'Edit Configuration')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Edit Configuration</h1>

    <form action="{{ route('settings.configurations.update', $config) }}" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label for="key" class="block text-gray-700 text-sm font-bold mb-2">Key:</label>
            <input type="text" name="key" id="key" value="{{ old('key', $config->key) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('key') border-red-500 @enderror" readonly>
            @error('key')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-4">
            <label for="value" class="block text-gray-700 text-sm font-bold mb-2">Value:</label>
            <textarea name="value" id="value" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('value') border-red-500 @enderror">{{ old('value', $config->value) }}</textarea>
            @error('value')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-4">
            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description (Optional):</label>
            <textarea name="description" id="description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('description') border-red-500 @enderror">{{ old('description', $config->description) }}</textarea>
            @error('description')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-primary hover:bg-primary-dark text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update Configuration</button>
            <a href="{{ route('settings.configurations.index') }}" class="inline-block align-baseline font-bold text-sm text-primary hover:text-primary-dark">Cancel</a>
        </div>
    </form>
@endsection