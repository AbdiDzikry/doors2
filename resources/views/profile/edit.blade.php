@extends('layouts.master')

@section('content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-8">
                {{ __('Profile') }}
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Update Profile Info --}}
                <div class="p-6 bg-white shadow rounded-2xl border border-gray-100">
                    <div class="max-w-xl">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Informasi Akun</h3>
                        <p class="text-sm text-gray-500 mb-6">Update nama profile dan alamat email akun Anda.</p>
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                {{-- Update Password --}}
                <div class="p-6 bg-white shadow rounded-2xl border border-gray-100">
                    <div class="max-w-xl">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Ganti Password</h3>
                        <p class="text-sm text-gray-500 mb-6">Pastikan akun Anda aman dengan password yang kuat.</p>
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                {{-- Delete Account (Full Width) --}}
                <div class="md:col-span-2 p-6 bg-white shadow rounded-2xl border border-red-100">
                    <div class="max-w-xl">
                        <h3 class="text-lg font-bold text-red-600 mb-2">Hapus Akun</h3>
                        <p class="text-sm text-gray-500 mb-6">Tindakan ini permanen dan tidak dapat dibatalkan.</p>
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection