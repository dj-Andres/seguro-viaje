import Swal from 'sweetalert2'

const base = {
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timerProgressBar: true,
}

export function useToast() {
  function success(message) {
    Swal.fire({ ...base, icon: 'success', title: message || 'Operación exitosa.', timer: 3500 })
  }

  function error(message) {
    Swal.fire({ ...base, icon: 'error', title: message || 'Ha ocurrido un error.', timer: 6000 })
  }

  function warning(message) {
    Swal.fire({ ...base, icon: 'warning', title: message || 'Atención.', timer: 4500 })
  }

  function info(message) {
    Swal.fire({ ...base, icon: 'info', title: message || 'Información.', timer: 4000 })
  }

  return { success, error, warning, info }
}