<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold uppercase tracking-widest text-slate-500">Administration</p>
            <h1 class="text-2xl font-black text-slate-950">Users</h1>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3">User</th>
                            <th class="px-5 py-3">Role</th>
                            <th class="px-5 py-3">Owned brackets</th>
                            <th class="px-5 py-3">Votes</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach ($users as $user)
                            <tr>
                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-950">{{ $user->name }}</div>
                                    <div class="text-sm text-slate-500">{{ $user->email }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm font-semibold text-slate-600">{{ $user->is_admin ? 'Admin' : 'User' }}</td>
                                <td class="px-5 py-4 font-bold text-slate-900">{{ $user->brackets_count }}</td>
                                <td class="px-5 py-4 font-bold text-slate-900">{{ $user->votes_count }}</td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('admin.users.show', $user) }}" class="text-sm font-black text-slate-700 hover:text-slate-950">View details →</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8">{{ $users->links() }}</div>
    </div>
</x-app-layout>
