<script setup>
import { ref } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
    users: Array,
    invites: Array,
})

const showInviteForm = ref(false)

const inviteForm = useForm({
    email: '',
    first_name: '',
    last_name: '',
    role: 'user',
    expires_hours: 72,
})

const createInvite = () => {
    inviteForm.post(route('admin.invites.store'), {
        onSuccess: () => {
            inviteForm.reset()
            showInviteForm.value = false
        },
    })
}

const deleteUser = (id) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(route('admin.users.destroy', id))
    }
}

const revokeInvite = (id) => {
    router.delete(route('admin.invites.destroy', id))
}
</script>

<template>
    <Head title="Users" />
    <AppLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Users</h1>
            <div class="flex gap-2">
                <button @click="showInviteForm = !showInviteForm"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Invite
                </button>
                <Link :href="route('admin.users.create')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-900 dark:bg-gray-100 hover:bg-gray-800 dark:hover:bg-white text-white dark:text-gray-900 font-medium rounded-xl transition-colors text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Create User
                </Link>
            </div>
        </div>

        <!-- Invite form -->
        <div v-if="showInviteForm" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Send Invite</h2>
            <form @submit.prevent="createInvite" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[140px]">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input v-model="inviteForm.first_name" type="text" placeholder="Jane"
                        class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                </div>
                <div class="flex-1 min-w-[140px]">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input v-model="inviteForm.last_name" type="text" placeholder="Doe"
                        class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                </div>
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input v-model="inviteForm.email" type="email" required placeholder="jane@example.com"
                        class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                    <p v-if="inviteForm.errors.email" class="text-red-500 text-xs mt-1">{{ inviteForm.errors.email }}</p>
                </div>
                <div class="w-28">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                    <select v-model="inviteForm.role"
                        class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="w-28">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expires (hours)</label>
                    <input v-model.number="inviteForm.expires_hours" type="number" min="1" max="720"
                        class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 outline-none" />
                </div>
                <button type="submit" :disabled="inviteForm.processing"
                    class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors text-sm disabled:opacity-50 self-end">
                    {{ inviteForm.processing ? 'Sending…' : 'Send Invite' }}
                </button>
            </form>
        </div>

        <!-- Users table -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden mb-6">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Name</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Email</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Role</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr v-for="user in users" :key="user.id">
                        <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">{{ user.name }}</td>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ user.email }}</td>
                        <td class="px-4 py-3">
                            <span :class="[user.role === 'admin' ? 'bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400']"
                                class="text-xs px-2 py-1 rounded-full font-medium capitalize">{{ user.role }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="route('admin.users.edit', user.id)" class="text-primary-600 hover:text-primary-700 text-xs font-medium mr-3">Edit</Link>
                            <button @click="deleteUser(user.id)" class="text-red-500 hover:text-red-700 text-xs font-medium">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Invites -->
        <div v-if="invites.length" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800">
                <h2 class="font-semibold text-gray-900 dark:text-white">Pending Invites</h2>
            </div>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr v-for="invite in invites" :key="invite.id">
                        <td class="px-4 py-3 text-gray-900 dark:text-white">{{ invite.email }}</td>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 capitalize">{{ invite.role }}</td>
                        <td class="px-4 py-3">
                            <span v-if="invite.used_at" class="text-xs text-green-600">Used</span>
                            <span v-else-if="!invite.is_valid" class="text-xs text-red-500">Expired</span>
                            <span v-else class="text-xs text-yellow-600">Pending</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button v-if="invite.is_valid" @click="revokeInvite(invite.id)" class="text-red-500 hover:text-red-700 text-xs font-medium">Revoke</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
