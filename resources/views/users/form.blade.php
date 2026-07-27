@php $isEdit = $user->exists; @endphp
<x-layout :title="$isEdit ? 'Edit User' : 'Add User'">
    <a href="{{ route('users.index') }}" class="mb-3 inline-block text-sm text-teal-700">&larr; Back to users</a>

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ $isEdit ? route('users.update', $user) : route('users.store') }}"
        class="space-y-4"
    >
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Name</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Role</label>
            <select name="role" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="cashier" @selected(old('role', $user->role ?? 'cashier') === 'cashier')>Cashier</option>
                <option value="admin" @selected(old('role', $user->role ?? '') === 'admin')>Admin</option>
            </select>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">
                {{ $isEdit ? 'Reset PIN (leave blank to keep current)' : 'PIN (4–6 digits)' }}
            </label>
            <input type="password" inputmode="numeric" name="pin" {{ $isEdit ? '' : 'required' }}
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>

        @if ($isEdit)
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active))>
                Active (can log in)
            </label>
        @endif

        <button type="submit" class="w-full rounded-lg bg-teal-700 py-2.5 text-sm font-medium text-white">
            {{ $isEdit ? 'Save changes' : 'Create user' }}
        </button>
    </form>
</x-layout>
