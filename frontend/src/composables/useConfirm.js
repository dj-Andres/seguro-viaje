import Swal from 'sweetalert2'

export function useConfirm() {
  async function confirm(options = {}) {
    const { isConfirmed } = await Swal.fire({
      title: options.title ?? '¿Estás seguro?',
      text: options.text ?? '',
      icon: options.icon ?? 'warning',
      showCancelButton: true,
      confirmButtonColor: options.confirmButtonColor ?? '#dc2626',
      cancelButtonColor: '#6b7280',
      confirmButtonText: options.confirmButtonText ?? 'Sí, continuar',
      cancelButtonText: options.cancelButtonText ?? 'Cancelar',
      focusConfirm: false,
      ...options,
    })

    return isConfirmed
  }

  return { confirm }
}