@extends('layouts.master')

@section('title', 'SUS Survey Results')

@section('content')
<div class="container mx-auto px-4 py-8">
    
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">System Usability Scale (SUS) Results</h1>
            <p class="text-gray-500 text-sm mt-1">Measuring usability performance based on 10 standard questions.</p>
        </div>
        <div class="text-sm bg-red-100 text-red-800 px-3 py-1 rounded-full font-medium">
            Superadmin Access Only
        </div>
    </div>

    @php
        $grade = 'F';
        $color = 'text-red-600';
        $bg = 'bg-red-100';
        
        if ($averageScore >= 80.3) {
            $grade = 'A (Excellent)';
            $color = 'text-green-600';
            $bg = 'bg-green-100';
        } elseif ($averageScore >= 68) {
            $grade = 'B (Good)';
            $color = 'text-blue-600';
            $bg = 'bg-blue-100';
        } elseif ($averageScore >= 51) {
            $grade = 'C (OK)';
            $color = 'text-yellow-600';
            $bg = 'bg-yellow-100';
        }
    @endphp

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Average Score -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Average SUS Score</p>
                    <p class="text-4xl font-extrabold text-gray-800 mt-2">{{ number_format($averageScore, 1) }}</p>
                    <p class="text-xs text-gray-400 mt-1">Scale: 0 - 100</p>
                </div>
                <div class="p-3 rounded-full bg-green-50 text-green-600">
                    <i class="fas fa-poll text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Grade -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Usability Grade</p>
                    <p class="text-4xl font-extrabold {{ $color }} mt-2">{{ $grade }}</p>
                    <p class="text-xs text-gray-400 mt-1">Based on industry standards</p>
                </div>
                <div class="p-3 rounded-full {{ $bg }} {{ $color }}">
                    <i class="fas fa-award text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Responses -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Total Respondents</p>
                    <p class="text-4xl font-extrabold text-gray-800 mt-2">{{ $totalResponses }}</p>
                    <p class="text-xs text-gray-400 mt-1">Users submitted</p>
                </div>
                <div class="p-3 rounded-full bg-purple-50 text-purple-600">
                    <i class="fas fa-users text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Grading Guide -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-8">
        <h3 class="font-bold text-blue-900 mb-2 flex items-center">
            <i class="fas fa-info-circle mr-2"></i> Understanding SUS Scores
        </h3>
        <p class="text-sm text-blue-800 mb-3">The System Usability Scale (SUS) is a globally recognized standard. Ratings are based on the percentile rank:</p>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-green-500"></span>
                <span class="font-bold text-gray-700">> 80.3</span>
                <span class="text-gray-600">= Grade A (Excellent)</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                <span class="font-bold text-gray-700">68 - 80.3</span>
                <span class="text-gray-600">= Grade B (Good)</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                <span class="font-bold text-gray-700">51 - 68</span>
                <span class="text-gray-600">= Grade C (OK)</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                <span class="font-bold text-gray-700">< 51</span>
                <span class="text-gray-600">= Grade F (Poor)</span>
            </div>
        </div>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
            <h3 class="font-bold text-gray-800">Recent Submissions</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comments</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($responses as $response)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $response->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-medium text-gray-900">{{ $response->user->name ?? 'Anonymous' }}</span>
                                <div class="text-xs text-gray-500">{{ $response->user->email ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                                    {{ $response->sus_score >= 80 ? 'bg-green-100 text-green-800' : ($response->sus_score >= 68 ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800') }}">
                                    {{ number_format($response->sus_score, 1) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ $response->comments ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500 italic">
                                No survey results yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $responses->links() }}
        </div>
    </div>
</div>
@endsection
