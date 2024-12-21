<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-green-50 to-green-100">
        <div class="w-full sm:max-w-md px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <!-- Logo -->
            <div class="flex justify-center mb-6">
                <img src="{{ asset('storage/images/csu.png') }}" 
                     alt="CSU Logo" 
                     class="h-20 w-auto object-contain"
                     onerror="this.onerror=null; this.src='{{ asset('storage/images/csu.jpg') }}'">
            </div>
            
            <!-- Title -->
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-green-800">Welcome Back</h2>
                <p class="text-green-600 text-sm">Sign in to access the ID validation system</p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')" class="text-green-800" />
                    <x-text-input id="email" 
                                 class="block mt-1 w-full rounded-lg border-green-300 focus:border-green-500 focus:ring-green-500" 
                                 type="email" 
                                 name="email" 
                                 :value="old('email')" 
                                 required 
                                 autofocus 
                                 autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="password" :value="__('Password')" class="text-green-800" />
                    <x-text-input id="password" 
                                 class="block mt-1 w-full rounded-lg border-green-300 focus:border-green-500 focus:ring-green-500"
                                 type="password"
                                 name="password"
                                 required 
                                 autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Remember Me -->
                <div class="block mt-4">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" 
                               type="checkbox" 
                               class="rounded border-green-300 text-green-600 shadow-sm focus:ring-green-500" 
                               name="remember">
                        <span class="ms-2 text-sm text-green-600">{{ __('Remember me') }}</span>
                    </label>
                </div>

                <div class="flex items-center justify-end mt-4">
                    @if (Route::has('password.request'))
                        <a class="underline text-sm text-green-600 hover:text-green-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" 
                           href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif

                    <x-primary-button class="ms-3 bg-green-600 hover:bg-green-700 focus:bg-green-700 active:bg-green-800">
                        {{ __('Log in') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
