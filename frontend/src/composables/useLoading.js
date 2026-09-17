import { computed, reactive } from 'vue'

const state = reactive({ tasks: 0 })

export function useLoading() {
  const active = computed(() => state.tasks > 0)

  async function run(task) {
    state.tasks += 1

    try {
      return await (typeof task === 'function' ? task() : task)
    } finally {
      state.tasks = Math.max(0, state.tasks - 1)
    }
  }

  return { state, active, run }
}