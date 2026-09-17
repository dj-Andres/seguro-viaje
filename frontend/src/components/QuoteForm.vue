<script setup>
import { useForm, useField } from 'vee-validate'
import * as yup from 'yup'
import { useQuoteStore } from '../stores/quote'
import { useToast } from '../composables/useToast'
import { useLoading } from '../composables/useLoading'
import CountrySelect from './CountrySelect.vue'

const store = useQuoteStore()
const toast = useToast()
const { run } = useLoading()

function localDateStr(date) {
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

const today = localDateStr(new Date())
const eighteenYearsAgoDate = new Date()
eighteenYearsAgoDate.setFullYear(eighteenYearsAgoDate.getFullYear() - 18)
const eighteenYearsAgo = localDateStr(eighteenYearsAgoDate)

const schema = yup.object({
  nombres: yup
    .string()
    .trim()
    .required('El campo nombres es obligatorio.')
    .max(255, 'El campo nombres no debe superar los 255 caracteres.'),
  apellidos: yup
    .string()
    .trim()
    .required('El campo apellidos es obligatorio.')
    .max(255, 'El campo apellidos no debe superar los 255 caracteres.'),
  numero_identificacion: yup
    .string()
    .trim()
    .required('El campo número de identificación es obligatorio.')
    .max(20, 'El campo número de identificación no debe superar los 20 caracteres.'),
  correo_electronico: yup
    .string()
    .trim()
    .required('El campo correo electrónico es obligatorio.')
    .email('El campo correo electrónico debe ser una dirección de correo válida.')
    .max(255, 'El campo correo electrónico no debe superar los 255 caracteres.'),
  fecha_nacimiento: yup
    .string()
    .required('El campo fecha de nacimiento es obligatorio.')
    .test(
      'mayor-edad',
      'Debes ser mayor de 18 años para contratar el seguro.',
      (value) => !!value && value <= eighteenYearsAgo,
    ),
  codigo_pais: yup
    .string()
    .required('El campo país de destino es obligatorio.')
    .length(2, 'El código de país debe tener 2 caracteres.'),
  fecha_salida: yup
    .string()
    .required('El campo fecha de salida es obligatorio.')
    .test(
      'hoy-o-futuro',
      'La fecha de salida no puede ser anterior a hoy.',
      (value) => !!value && value >= today,
    ),
  fecha_regreso: yup
    .string()
    .required('El campo fecha de regreso es obligatorio.')
    .test(
      'posterior',
      'La fecha de regreso debe ser posterior a la fecha de salida.',
      (value, context) => !!value && !!context.parent.fecha_salida && value > context.parent.fecha_salida,
    ),
})

const { handleSubmit, setErrors, resetForm } = useForm({
  validationSchema: schema,
  initialValues: {
    nombres: '',
    apellidos: '',
    numero_identificacion: '',
    correo_electronico: '',
    fecha_nacimiento: '',
    codigo_pais: '',
    fecha_salida: '',
    fecha_regreso: '',
  },
})

const { value: nombres, errorMessage: nombresError } = useField('nombres')
const { value: apellidos, errorMessage: apellidosError } = useField('apellidos')
const { value: numeroIdentificacion, errorMessage: identificacionError } = useField('numero_identificacion')
const { value: correoElectronico, errorMessage: correoError } = useField('correo_electronico')
const { value: fechaNacimiento, errorMessage: nacimientoError } = useField('fecha_nacimiento')
const { value: codigoPais, errorMessage: codigoPaisError, handleChange: handleCountryChange } = useField('codigo_pais')
const { value: fechaSalida, errorMessage: salidaError } = useField('fecha_salida')
const { value: fechaRegreso, errorMessage: regresoError } = useField('fecha_regreso')

const onSubmit = handleSubmit(async (values) => {
  try {
    const body = await run(() => store.createQuote(values))
    resetForm()
    toast.success(body?.message || 'Cotización generada correctamente.')
  } catch (error) {
    if (error?.fieldErrors) {
      const mapped = {}
      Object.entries(error.fieldErrors).forEach(([field, messages]) => {
        mapped[field] = Array.isArray(messages) ? messages[0] : messages
      })
      setErrors(mapped)
      toast.error('Revisa los campos marcados en el formulario.')
    } else {
      toast.error(error?.message || 'No se pudo generar la cotización.')
    }
  }
})
</script>

<template>
  <form class="space-y-6" @submit.prevent="onSubmit">
    <section>
      <h2 class="mb-3 text-lg font-semibold text-gray-800">Datos del asegurado</h2>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Nombres</label>
          <input
            v-model="nombres"
            type="text"
            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            placeholder="Juan"
          />
          <p v-if="nombresError" class="mt-1 text-xs text-red-600">{{ nombresError }}</p>
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Apellidos</label>
          <input
            v-model="apellidos"
            type="text"
            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            placeholder="Pérez"
          />
          <p v-if="apellidosError" class="mt-1 text-xs text-red-600">{{ apellidosError }}</p>
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Número de identificación</label>
          <input
            v-model="numeroIdentificacion"
            type="text"
            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            placeholder="1234567890"
          />
          <p v-if="identificacionError" class="mt-1 text-xs text-red-600">{{ identificacionError }}</p>
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Correo electrónico</label>
          <input
            v-model="correoElectronico"
            type="email"
            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            placeholder="juan@example.com"
          />
          <p v-if="correoError" class="mt-1 text-xs text-red-600">{{ correoError }}</p>
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Fecha de nacimiento</label>
          <input
            v-model="fechaNacimiento"
            type="date"
            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
          />
          <p v-if="nacimientoError" class="mt-1 text-xs text-red-600">{{ nacimientoError }}</p>
        </div>
      </div>
    </section>

    <section>
      <h2 class="mb-3 text-lg font-semibold text-gray-800">Datos del viaje</h2>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="sm:col-span-1">
          <label class="mb-1 block text-sm font-medium text-gray-700">País de destino</label>
          <CountrySelect
            :model-value="codigoPais"
            :options="store.countries"
            @update:model-value="handleCountryChange"
          />
          <p v-if="codigoPaisError" class="mt-1 text-xs text-red-600">{{ codigoPaisError }}</p>
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Fecha de salida</label>
          <input
            v-model="fechaSalida"
            type="date"
            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
          />
          <p v-if="salidaError" class="mt-1 text-xs text-red-600">{{ salidaError }}</p>
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Fecha de regreso</label>
          <input
            v-model="fechaRegreso"
            type="date"
            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
          />
          <p v-if="regresoError" class="mt-1 text-xs text-red-600">{{ regresoError }}</p>
        </div>
      </div>
    </section>

    <button
      type="submit"
      :disabled="store.submitting"
      class="w-full rounded-md bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
    >
      {{ store.submitting ? 'Generando cotización...' : 'Cotizar seguro' }}
    </button>
  </form>
</template>
