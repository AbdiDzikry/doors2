@extends('layouts.master')

@section('title', 'Give Feedback')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6 sm:p-8 bg-green-600">
            <h1 class="text-2xl font-bold text-white flex items-center">
                <i class="far fa-smile mr-3"></i> We Value Your Feedback
            </h1>
            <p class="text-green-100 mt-2">
                Help us improve the Meeting Room Booking System experience.
            </p>
        </div>
        
        <form action="{{ route('survey.store') }}" method="POST" class="p-6 sm:p-8">
            @csrf
            
            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2">How would you rate your experience?</label>
                <div class="flex items-center space-x-1" x-data="{ rating: 0, hover: 0 }">
                    <input type="hidden" name="rating" x-model="rating">
                    <template x-for="star in 5">
                        <button type="button" 
                            @click="rating = star" 
                            @mouseover="hover = star" 
                            @mouseleave="hover = 0"
                            class="focus:outline-none transition-transform duration-150 transform hover:scale-110">
                            <i class="fas fa-star text-4xl" 
                               :class="(hover || rating) >= star ? 'text-yellow-400' : 'text-gray-200'"></i>
                        </button>
                    </template>
                </div>
                @error('rating')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="comments" class="block text-gray-700 font-bold mb-2">Additional Comments (Optional)</label>
                <textarea name="comments" id="comments" rows="4" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    placeholder="Tell us what you like or what could be improved..."></textarea>
                @error('comments')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg shadow transition-colors flex items-center">
                    <i class="fas fa-paper-plane mr-2"></i> Submit Feedback
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
