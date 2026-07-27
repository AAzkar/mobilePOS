<x-layout title="Users">
    <a href="{{ route('settings') }}" class="mb-3 inline-block text-sm text-teal-700">&larr; Back to settings</a>

    <a href="{{ route('users.create') }}" class="mb-4 flex w-full items-center justify-center rounded-lg border-2 border-dashed border-teal-600 py-2 text-sm font-medium text-teal-700">
        + Add user
    </a>

    <div class="space-y-2">
        @foreach ($users as $user)
            <a href="{{ route('users.edit', $user) }}" class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-3">
                <div>
                    <p class="font-medium">{{ $user->name }}</p>
                    <p class="text-xs text-slate-500">{{ ucfirst($user->role) }}</p>
                </div>
                @unless ($user->is_active)
                    <span class="rounded bg-slate-100 px-2 py-0.5 text-xs text-slate-500">Inactive</span>
                @endunless
            </a>
        @endforeach
    </div>
</x-layout>
