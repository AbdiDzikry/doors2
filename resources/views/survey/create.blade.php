@extends('layouts.master')

@section('title', 'Survei Kepuasan Pengguna (SUS)')

@section('content')
<div class="min-h-screen bg-gray-50 py-10">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl">
        
        <!-- Header -->
        <div class="bg-white rounded-t-xl shadow-sm border-b border-gray-200 p-6 sm:p-8">
            <h1 class="text-2xl font-bold text-gray-900 border-l-4 border-green-600 pl-4">
                Survei Kepuasan Pengguna
            </h1>
            <p class="mt-2 text-gray-600 pl-4">
                Mohon berikan penilaian Anda terhadap pernyataan berikut mengenai Sistem Doors.
            </p>
        </div>

        <form action="{{ route('survey.store') }}" method="POST" class="bg-white rounded-b-xl shadow-sm p-6 sm:p-8 space-y-8">
            @csrf
            
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-8 rounded-r">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Skala Penilaian: <span class="font-bold">1 (Sangat Tidak Setuju)</span> sd <span class="font-bold">5 (Sangat Setuju)</span>
                        </p>
                    </div>
                </div>
            </div>

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

            <div class="space-y-6">
                @foreach($questions as $index => $question)
                    <div class="border-b border-gray-100 pb-6 last:border-0 last:pb-0">
                        <p class="text-base font-medium text-gray-800 mb-3"><span class="text-gray-400 mr-2">{{ $index }}.</span> {{ $question }}</p>
                        
                        <div class="flex items-center justify-between sm:justify-start sm:space-x-8 max-w-lg">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide hidden sm:inline-block">Sangat Tidak Setuju</span>
                            
                            <div class="flex items-center justify-between w-full sm:w-auto sm:space-x-6 bg-gray-50 rounded-lg px-4 py-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="flex flex-col items-center cursor-pointer hover:bg-green-50 rounded-lg p-2 transition-colors">
                                        <input type="radio" name="q{{ $index }}" value="{{ $i }}" 
                                            class="w-5 h-5 text-green-600 border-gray-300 focus:ring-green-500 mb-1" 
                                            required
                                            {{ (old('q'.$index) == $i || (isset($response) && $response->{'q'.$index} == $i)) ? 'checked' : '' }}>
                                        <span class="text-sm font-semibold text-gray-600">{{ $i }}</span>
                                    </label>
                                @endfor
                            </div>

                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide hidden sm:inline-block">Sangat Setuju</span>
                        </div>

                        <!-- Mobile Labels -->
                        <div class="flex justify-between mt-2 sm:hidden px-1">
                            <span class="text-[10px] font-semibold text-gray-400 uppercase">Sangat Tdk Setuju</span>
                            <span class="text-[10px] font-semibold text-gray-400 uppercase">Sangat Setuju</span>
                        </div>

                         @error('q'.$index)
                            <p class="text-red-500 text-xs mt-2">Mohon pilih salah satu.</p>
                        @enderror
                    </div>
                @endforeach
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100">
                <label for="comments" class="block text-sm font-medium text-gray-700 mb-2">Masukan Tambahan (Opsional)</label>
                <textarea name="comments" id="comments" rows="3" 
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm"
                    placeholder="Saran atau kritik membangun...">{{ old('comments') ?? ($response->comments ?? '') }}</textarea>
            </div>

            <div class="flex items-center justify-end pt-4">
                <a href="{{ url()->previous() }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 mr-6">Batal</a>
                <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                    Kirim Jawaban
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
