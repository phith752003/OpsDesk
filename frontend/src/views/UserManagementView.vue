<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold tracking-tight text-text-primary">
          User &amp; Access Governance
        </h1>
        <p class="text-xs text-text-muted mt-0.5 font-mono">
          Role-based authorization directory and account provisioning
        </p>
      </div>
      <button
        v-if="authStore.isAdmin"
        type="button"
        class="inline-flex items-center px-3.5 py-2 rounded bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold shadow-sm transition-subtle space-x-1.5 self-start sm:self-auto"
        @click="openCreateModal"
      >
        <UserPlus class="w-4 h-4" />
        <span>Provision User</span>
      </button>
    </div>

    <!-- User Table -->
    <div class="bg-bg-surface border border-border-default rounded-lg overflow-hidden shadow-sm">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-bg-surface-raised border-b border-border-subtle text-text-muted font-mono uppercase tracking-wider">
            <tr>
              <th class="px-4 py-3">User</th>
              <th class="px-4 py-3">Assigned Role</th>
              <th class="px-4 py-3">Department</th>
              <th class="px-4 py-3">Account Status</th>
              <th class="px-4 py-3 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border-subtle">
            <tr
              v-for="u in users"
              :key="u.id"
              class="hover:bg-bg-surface-hover/50 transition-subtle"
            >
              <td class="px-4 py-3 text-text-primary whitespace-nowrap">
                <div class="font-semibold">{{ u.name }}</div>
                <div class="text-[11px] text-text-muted font-mono">{{ u.email }}</div>
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                <span
                  class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-mono font-medium border uppercase"
                  :class="roleBadgeClasses(u.role)"
                >
                  {{ u.role_label || u.role }}
                </span>
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-text-secondary">
                {{ u.department?.name || 'General Operations' }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                <span
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-mono font-medium"
                  :class="u.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-300' : 'bg-red-50 text-red-700 border border-red-300'"
                >
                  <span class="w-1.5 h-1.5 rounded-full mr-1" :class="u.is_active ? 'bg-emerald-600' : 'bg-red-600'" />
                  {{ u.is_active ? 'Active' : 'Deactivated' }}
                </span>
              </td>
              <td class="px-4 py-3 text-right whitespace-nowrap">
                <button
                  v-if="authStore.isAdmin && u.id !== authStore.user?.id"
                  type="button"
                  class="px-2.5 py-1 rounded text-xs font-mono font-medium transition-subtle border border-border-default hover:border-border-strong"
                  :class="u.is_active ? 'text-amber-700 hover:bg-amber-50' : 'text-emerald-700 hover:bg-emerald-50'"
                  @click="toggleUserActive(u)"
                >
                  {{ u.is_active ? 'Deactivate' : 'Activate' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Provision User Modal -->
    <Modal :is-open="isModalOpen" title="Provision New System User" @close="isModalOpen = false">
      <form @submit.prevent="handleCreateUser" class="space-y-4 text-xs">
        <div v-if="modalError" class="p-2.5 rounded bg-red-50 border border-red-200 text-red-700 font-medium">
          {{ modalError }}
        </div>

        <div>
          <label class="block font-medium text-text-secondary mb-1">Full Name</label>
          <input
            v-model="createForm.name"
            type="text"
            required
            class="w-full px-3 py-2 bg-bg-base border border-border-default rounded text-text-primary focus:outline-none focus:border-blue-500"
            placeholder="e.g. Jordan Hayes"
          />
        </div>

        <div>
          <label class="block font-medium text-text-secondary mb-1">Email Address</label>
          <input
            v-model="createForm.email"
            type="email"
            required
            class="w-full px-3 py-2 bg-bg-base border border-border-default rounded text-text-primary focus:outline-none focus:border-blue-500 font-mono"
            placeholder="jordan.h@opsdesk.local"
          />
        </div>

        <div>
          <label class="block font-medium text-text-secondary mb-1">Initial Password</label>
          <input
            v-model="createForm.password"
            type="password"
            required
            minlength="8"
            class="w-full px-3 py-2 bg-bg-base border border-border-default rounded text-text-primary focus:outline-none focus:border-blue-500"
            placeholder="Minimum 8 characters"
          />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-medium text-text-secondary mb-1">System Role</label>
            <select
              v-model="createForm.role"
              required
              class="w-full px-3 py-2 bg-bg-base border border-border-default rounded text-text-primary focus:outline-none focus:border-blue-500 font-mono"
            >
              <option value="requester">Requester</option>
              <option value="agent">Support Agent</option>
              <option value="manager">Operations Manager</option>
              <option value="admin">Administrator</option>
            </select>
          </div>

          <div>
            <label class="block font-medium text-text-secondary mb-1">Department</label>
            <select
              v-model="createForm.department_id"
              class="w-full px-3 py-2 bg-bg-base border border-border-default rounded text-text-primary focus:outline-none focus:border-blue-500 font-mono"
            >
              <option :value="null">None</option>
              <option
                v-for="dept in authStore.meta?.departments || []"
                :key="dept.id"
                :value="dept.id"
              >
                {{ dept.name }}
              </option>
            </select>
          </div>
        </div>

        <div class="pt-3 border-t border-border-default flex justify-end space-x-2">
          <button
            type="button"
            class="px-3 py-1.5 rounded text-text-secondary hover:text-text-primary"
            @click="isModalOpen = false"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="px-4 py-1.5 rounded bg-blue-600 hover:bg-blue-500 text-white font-medium"
          >
            {{ isSubmitting ? 'Provisioning...' : 'Confirm Provisioning' }}
          </button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { userApi } from '@/services/userApi'
import { useAuthStore } from '@/stores/auth'
import Modal from '@/components/common/Modal.vue'
import type { User } from '@/types'
import { UserPlus } from 'lucide-vue-next'

const authStore = useAuthStore()

const users = ref<User[]>([])
const isModalOpen = ref(false)
const isSubmitting = ref(false)
const modalError = ref('')

const createForm = ref({
  name: '',
  email: '',
  password: 'Password123!',
  role: 'agent',
  department_id: null as number | null,
})

function roleBadgeClasses(role: string) {
  switch (role) {
    case 'admin':
      return 'bg-purple-50 text-purple-700 border-purple-200'
    case 'manager':
      return 'bg-indigo-50 text-indigo-700 border-indigo-200'
    case 'agent':
      return 'bg-blue-50 text-blue-700 border-blue-200'
    default:
      return 'bg-slate-100 text-slate-700 border-slate-300'
  }
}

async function loadUsers() {
  try {
    const res = await userApi.getUsers()
    users.value = res.data
  } catch (e) {
    console.error('Failed to load users', e)
  }
}

async function toggleUserActive(u: User) {
  try {
    const res = await userApi.toggleActive(u.id)
    u.is_active = res.user.is_active
  } catch (e) {
    console.error('Failed to toggle status', e)
  }
}

function openCreateModal() {
  modalError.value = ''
  isModalOpen.value = true
}

async function handleCreateUser() {
  isSubmitting.value = true
  modalError.value = ''
  try {
    await userApi.createUser(createForm.value)
    isModalOpen.value = false
    await loadUsers()
  } catch (err: any) {
    modalError.value = err.response?.data?.message || 'Failed to create user.'
  } finally {
    isSubmitting.value = false
  }
}

onMounted(async () => {
  await Promise.all([
    loadUsers(),
    authStore.fetchMeta(),
  ])
})
</script>

