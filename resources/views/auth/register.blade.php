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
                <h2 class="text-2xl font-bold text-green-800">Create Account</h2>
                <p class="text-green-600 text-sm">Register to access the ID validation system</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Name -->
                <div>
                    <x-input-label for="name" :value="__('Name')" class="text-green-800" />
                    <x-text-input id="name" 
                                 class="block mt-1 w-full rounded-lg border-green-300 focus:border-green-500 focus:ring-green-500" 
                                 type="text" 
                                 name="name" 
                                 :value="old('name')" 
                                 required 
                                 autofocus 
                                 autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <!-- Email Address -->
                <div class="mt-4">
                    <x-input-label for="email" :value="__('Email')" class="text-green-800" />
                    <x-text-input id="email" 
                                 class="block mt-1 w-full rounded-lg border-green-300 focus:border-green-500 focus:ring-green-500" 
                                 type="email" 
                                 name="email" 
                                 :value="old('email')" 
                                 required 
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
                                 autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div class="mt-4">
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-green-800" />
                    <x-text-input id="password_confirmation" 
                                 class="block mt-1 w-full rounded-lg border-green-300 focus:border-green-500 focus:ring-green-500"
                                 type="password"
                                 name="password_confirmation" 
                                 required 
                                 autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end mt-6">
                    <a class="underline text-sm text-green-600 hover:text-green-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" 
                       href="{{ route('login') }}">
                        {{ __('Already registered?') }}
                    </a>

                    <x-primary-button class="ms-4 bg-green-600 hover:bg-green-700 focus:bg-green-700 active:bg-green-800">
                        {{ __('Register') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
