@extends('layouts.master')

@section('title', 'Survei Kepuasan Pengguna (SUS)')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6 sm:p-8 bg-green-600">
            <h1 class="text-2xl font-bold text-white flex items-center">
                <i class="fas fa-clipboard-check mr-3"></i> Survei Kepuasan Pengguna
            </h1>
            <p class="text-green-100 mt-2">
                Mohon berikan penilaian Anda terhadap pernyataan berikut mengenai Sistem Doors.
            </p>
        </div>
        
        <form action="{{ route('survey.store') }}" method="POST" class="p-6 sm:p-8" x-data>
            @csrf
            
            <div class="space-y-8 mb-8">
                @php
                    $questions = [
                        1 => 'Saya kira saya akan sering menggunakan sistem ini.',
                        2 => 'Saya merasa sistem ini terlalu rumit padahal bisa dibuat lebih sederhana.',
                        3 => 'Saya rasa sistem ini mudah digunakan.',
                        4 => 'Saya pikir saya akan membutuhkan bantuan teknis untuk dapat menggunakan sistem ini.',
                        5 => 'Saya menemukan berbagai fungsi dalam sistem ini terintegrasi dengan baik.',
                        6 => 'Saya rasa banyak hal yang tidak konsisten dalam sistem ini.',
                        7 => 'Saya rasa kebanyakan orang akan belajar menggunakan sistem ini dengan sangat cepat.',
                        8 => 'Saya menemukan sistem ini sangat kaku/merepotkan untuk digunakan.',
                        9 => 'Saya merasa sangat percaya diri menggunakan sistem ini.',
                        10 => 'Saya perlu belajar banyak hal sebelum saya bisa mulai menggunakan sistem ini.'
                    ];
                @endphp

                @foreach($questions as $index => $question)
                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                        <p class="font-semibold text-gray-800 mb-4">{{ $index }}. {{ $question }}</p>
                        
                        <div class="flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0 text-sm text-gray-600">
                            <span class="font-medium text-red-500">Sangat Tidak Setuju</span>
                            
                            <div class="flex space-x-6">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="flex flex-col items-center cursor-pointer group">
                                        <input type="radio" name="q{{ $index }}" value="{{ $i }}" class="w-6 h-6 text-green-600 focus:ring-green-500 border-gray-300" required>
                                        <span class="mt-1 group-hover:text-green-600 transition-colors">{{ $i }}</span>
                                    </label>
                                @endfor
                            </div>
                            
                            <span class="font-medium text-green-500">Sangat Setuju</span>
                        </div>
                        @error('q'.$index)
                            <p class="text-red-500 text-sm mt-2 text-center">Mohon pilih salah satu opsi.</p>
                        @enderror
                    </div>
                @endforeach
            </div>

            <div class="mb-8">
                <label for="comments" class="block text-gray-700 font-bold mb-2">Komentar Tambahan (Opsional)</label>
                <textarea name="comments" id="comments" rows="3" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    placeholder="Ada masukan spesifik?"></textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg shadow transition-colors flex items-center text-lg">
                    <i class="fas fa-paper-plane mr-2"></i> Kirim Survei
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
