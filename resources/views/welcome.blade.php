<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CSU ID Validation System</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-gradient-to-br from-green-50 to-green-100 min-h-screen">
        <div style="display:none">
            Debug Path: {{ public_path('QrCodeData') }}<br>
            Files: {{ print_r(scandir(public_path('QrCodeData')), true) }}
        </div>
        <div class="relative min-h-screen">
            <!-- Navigation -->
            <nav class="bg-white/80 backdrop-blur-sm border-b border-green-100 fixed w-full z-10">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <div class="flex items-center">
                            <!-- Updated School Logo path -->
                            <img src="{{ asset('storage/images/csu.png') }}" 
                                 alt="CSU Logo" 
                                 class="h-16 w-auto object-contain"
                                 onerror="this.onerror=null; this.src='{{ asset('storage/images/csu.jpg') }}'">
                            <div class="ml-4">
                                <h1 class="text-xl font-semibold text-green-800">Caraga State University</h1>
                                <p class="text-sm text-green-600">ID Validation System</p>
                            </div>
                        </div>

                        <!-- Navigation Links -->
                        <div class="flex items-center space-x-4">
                            @if (Route::has('login'))
                                <div class="flex space-x-2">
                                    @auth
                                        <a href="{{ url('/dashboard') }}" 
                                           class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-150">
                                            Dashboard
                                        </a>
                                    @else
                                        <a href="{{ route('login') }}" 
                                           class="text-green-600 hover:text-green-800 px-4 py-2 rounded-lg hover:bg-green-50 transition duration-150">
                                            Log in
                                        </a>

                                        @if (Route::has('register'))
                                            <a href="{{ route('register') }}" 
                                               class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-150">
                                                Register
                                            </a>
                                        @endif
                                    @endauth
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="pt-20 pb-12">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <!-- Hero Section with Image -->
                    <div class="text-center py-16 relative">
                        <!-- Background Image Container -->
                        <div class="absolute inset-0 flex justify-center items-center -z-10 opacity-10">
                            <img src="{{ asset('storage/images/body.png') }}" 
                                 alt="CSU Background" 
                                 class="w-full h-full object-cover"
                                 onerror="this.onerror=null; this.style.display='none'"
                                 style="mix-blend-mode: multiply;">
                        </div>

                        <!-- Main Image -->
                        <div class="mb-12 flex justify-center">
                            <img src="{{ asset('storage/images/body.png') }}" 
                                 alt="CSU Building" 
                                 class="rounded-2xl shadow-xl w-full max-w-4xl h-auto object-cover"
                                 onerror="this.onerror=null; this.style.display='none'"
                                 style="aspect-ratio: 16/9;">
                        </div>

                        <h2 class="text-4xl font-bold text-green-800 mb-4">
                            ID Validation Now Open
                        </h2>
                        <p class="text-lg text-green-600 mb-8">
                            Validate your student ID quickly and securely through our digital system.
                        </p>
                        @auth
                            <a href="{{ url('/dashboard') }}" 
                               class="inline-flex items-center px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition duration-150">
                                <span>Go to Scanner</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        @else
                            <a href="{{ route('login') }}" 
                               class="inline-flex items-center px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition duration-150">
                                <span>Start Validation</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        @endauth
                    </div>

                    <!-- Features Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12">
                        <!-- Quick Scanning -->
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-green-100">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-green-800 mb-2">Quick Scanning</h3>
                            <p class="text-green-600">Validate IDs instantly with our efficient QR code scanning system.</p>
                        </div>

                        <!-- Secure Validation -->
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-green-100">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-green-800 mb-2">Secure Validation</h3>
                            <p class="text-green-600">Your ID information is protected with our secure validation process.</p>
                        </div>

                        <!-- Real-time Results -->
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-green-100">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-green-800 mb-2">Real-time Results</h3>
                            <p class="text-green-600">Get immediate validation results and verification status.</p>
                        </div>
                    </div>

                    <!-- Announcement Section -->
                    <div class="mt-16 bg-white p-8 rounded-xl shadow-sm border border-green-100">
                        <h3 class="text-2xl font-semibold text-green-800 mb-4">Important Announcement</h3>
                        <div class="prose prose-green">
                            <p class="text-green-600">
                                The ID validation system is now operational. All students are required to have their IDs validated 
                                through this system. Please ensure your student ID is in good condition for proper scanning.
                            </p>
                            <ul class="mt-4 space-y-2 text-green-600">
                                <li>Validation is mandatory for all students</li>
                                <li>Bring your official CSU student ID</li>
                                <li>Process takes less than a minute</li>
                                <li>Available during office hours</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-green-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <div class="text-center text-green-600">
                        <p>&copy; {{ date('Y') }} Caraga State University. All rights reserved.</p>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
