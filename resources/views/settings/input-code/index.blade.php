@extends('layouts.master')

@section('title', 'Input Code (SQL Console)')

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6 sm:p-8">
                
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            <i class="fas fa-terminal mr-2 text-gray-700"></i> Input Code (SQL Console)
                        </h1>
                        <p class="mt-1 text-sm text-gray-500">
                            Execute raw SQL queries directly. 
                            <span class="text-red-600 font-bold bg-red-100 px-2 py-0.5 rounded border border-red-200 text-xs uppercase ml-2">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Danger Zone
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Warning Alert -->
                <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Warning:</strong> You are about to execute raw SQL queries. This action cannot be undone (unless transaction rollback happens on error). 
                                Double-check your queries, especially <code>UPDATE</code> and <code>DELETE</code> statements.
                            </p>
                        </div>
                    </div>
                </div>

                @if(session('results'))
                    <div class="mb-6 bg-gray-900 rounded-lg p-4 overflow-x-auto text-sm font-mono text-green-400">
                        <h3 class="text-gray-400 font-bold mb-2 uppercase text-xs border-b border-gray-700 pb-1">Execution Results</h3>
                        @foreach(session('results') as $index => $res)
                            <div class="mb-4 last:mb-0">
                                <div class="text-gray-500 select-all">> {{ $res['query'] }}</div>
                                @if($res['type'] === 'SELECT')
                                    <div class="text-blue-300 mt-1">{{ count($res['data']) }} rows returned.</div>
                                    @if(count($res['data']) > 0)
                                        <div class="mt-2 border border-gray-700 rounded overflow-hidden">
                                            <table class="min-w-full divide-y divide-gray-700 text-xs">
                                                <thead class="bg-gray-800">
                                                    <tr>
                                                        @foreach(array_keys((array)$res['data'][0]) as $header)
                                                            <th class="px-3 py-2 text-left text-gray-300 font-medium uppercase tracking-wider">{{ $header }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-gray-900 divide-y divide-gray-800">
                                                    @foreach(array_slice($res['data'], 0, 10) as $row)
                                                        <tr>
                                                            @foreach((array)$row as $cell)
                                                                <td class="px-3 py-2 whitespace-nowrap text-gray-400">{{ $cell }}</td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                            @if(count($res['data']) > 10)
                                                <div class="px-3 py-1 bg-gray-800 text-gray-500 italic text-center">... showing first 10 rows ...</div>
                                            @endif
                                        </div>
                                    @endif
                                @else
                                    <div class="text-green-300 mt-1">Statement executed.</div>
                                @endif
                                <hr class="border-gray-800 mt-2">
                            </div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('settings.input-code.execute') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="query" class="block text-sm font-medium text-gray-700 mb-2">SQL Query</label>
                        <textarea name="query" id="query" rows="10" 
                            class="shadow-sm focus:ring-green-500 focus:border-green-500 block w-full sm:text-sm border-gray-300 rounded-md font-mono bg-gray-50 text-gray-900 placeholder-gray-400" 
                            placeholder="INSERT INTO meetings (title, ...) VALUES (...);&#10;SELECT * FROM users WHERE ...;">{{ old('query') }}</textarea>
                        <p class="mt-2 text-xs text-gray-500">Use semicolons <code>;</code> to separate multiple queries.</p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" onclick="return confirm('Are you strictly sure you want to execute this SQL?')"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <i class="fas fa-play mr-2"></i> Execute SQL
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
