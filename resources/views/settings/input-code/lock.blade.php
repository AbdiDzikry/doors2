@extends('layouts.master')

@section('title', 'Input Code - Locked')

@section('content')
    <div class="min-h-screen flex flex-col justify-center items-center bg-gray-100 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <div class="sm:mx-auto sm:w-full sm:max-w-md mb-6 text-center">
                    <i class="fas fa-lock text-4xl text-gray-400 mb-4"></i>
                    <h2 class="text-2xl font-extrabold text-gray-900">
                        Restricted Area
                    </h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Please enter the super admin code to continue.
                    </p>
                </div>

                <form class="space-y-6" action="{{ route('settings.input-code.unlock') }}" method="POST">
                    @csrf
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Access Code
                        </label>
                        <div class="mt-1">
                            <input id="password" name="password" type="password" required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                        </div>
                    </div>

                    @if(session('error'))
                        <div class="rounded-md bg-red-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-times-circle text-red-400"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">
                                        {{ session('error') }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div>
                        <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Unlock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection