<x-guest-layout title="Log in">
    <div x-data="loginPage()">
        <div class="mb-6 text-center">
            <h1 class="text-xl font-semibold text-teal-700">{{ $storeSettings->store_name }}</h1>
            <p class="text-sm text-slate-500">MobilePOS</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-center text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" x-ref="loginForm" class="hidden">
            @csrf
            <input type="hidden" name="user_id" x-ref="userField">
            <input type="hidden" name="pin" x-ref="pinField">
        </form>

        {{-- Step 1: choose who's clocking in --}}
        <div x-show="!selectedUserId" class="grid grid-cols-2 gap-3">
            @forelse ($users as $user)
                <button
                    type="button"
                    @click="selectUser({{ $user->id }}, {{ Js::from($user->name) }})"
                    class="rounded-xl border border-slate-200 bg-white p-4 text-center shadow-sm"
                >
                    <span class="block text-2xl">👤</span>
                    <span class="mt-1 block text-sm font-medium">{{ $user->name }}</span>
                    <span class="block text-xs text-slate-400">{{ ucfirst($user->role) }}</span>
                </button>
            @empty
                <p class="col-span-2 text-center text-sm text-slate-500">No active users found.</p>
            @endforelse
        </div>

        {{-- Step 2: PIN pad --}}
        <div x-show="selectedUserId" x-cloak>
            <button @click="back()" type="button" class="mb-3 text-sm text-teal-700">&larr; Not <span x-text="selectedUserName"></span>?</button>

            <p class="mb-3 text-center text-sm text-slate-600">
                Enter PIN for <span class="font-medium" x-text="selectedUserName"></span>
            </p>

            <div class="mb-4 flex justify-center gap-2">
                <template x-for="i in 6" :key="i">
                    <span
                        class="h-3 w-3 rounded-full border border-teal-700"
                        :class="pin.length >= i ? 'bg-teal-700' : 'bg-transparent'"
                    ></span>
                </template>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <template x-for="digit in [1,2,3,4,5,6,7,8,9]" :key="digit">
                    <button
                        @click="pressDigit(digit)"
                        type="button"
                        class="rounded-xl border border-slate-200 bg-white py-3 text-lg font-medium shadow-sm"
                        x-text="digit"
                    ></button>
                </template>
                <button @click="backspace()" type="button" class="rounded-xl border border-slate-200 bg-white py-3 text-sm text-slate-500 shadow-sm">
                    &larr;
                </button>
                <button
                    @click="pressDigit(0)"
                    type="button"
                    class="rounded-xl border border-slate-200 bg-white py-3 text-lg font-medium shadow-sm"
                >0</button>
                <button
                    @click="submit()"
                    type="button"
                    :disabled="!canSubmit"
                    class="rounded-xl bg-teal-700 py-3 text-sm font-medium text-white shadow-sm disabled:opacity-40"
                >OK</button>
            </div>
        </div>
    </div>
</x-guest-layout>
