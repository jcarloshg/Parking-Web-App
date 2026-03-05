import { mount } from '@vue/test-utils'
import { describe, it, expect, beforeEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import PaymentForm from '@/components/PaymentForm.vue'

describe('PaymentForm', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('renders both payment methods', () => {
    const wrapper = mount(PaymentForm)
    expect(wrapper.text()).toContain('Efectivo')
    expect(wrapper.text()).toContain('Tarjeta')
  })

  it('displays method icons', () => {
    const wrapper = mount(PaymentForm)
    expect(wrapper.find('.method-icon').exists()).toBe(true)
  })

  it('disables button when no payment method selected', () => {
    const wrapper = mount(PaymentForm)
    expect(wrapper.find('button').attributes('disabled')).toBe('')
  })

  it('enables button when payment method is selected', async () => {
    const wrapper = mount(PaymentForm, {
      props: { modelValue: 'efectivo' }
    })
    expect(wrapper.find('button').attributes('disabled')).toBeUndefined()
  })

  it('displays custom button text', () => {
    const wrapper = mount(PaymentForm, {
      props: { buttonText: 'Pagar ahora' }
    })
    expect(wrapper.text()).toContain('Pagar ahora')
  })

  it('displays error message', () => {
    const wrapper = mount(PaymentForm, {
      props: { error: 'Error en el pago' }
    })
    expect(wrapper.text()).toContain('Error en el pago')
  })

  it('shows selected method as active', () => {
    const wrapper = mount(PaymentForm, {
      props: { modelValue: 'tarjeta' }
    })
    
    const options = wrapper.findAll('.method-option')
    expect(options[1].attributes('class')).toContain('selected')
  })

  it('disables button when disabled prop is true', () => {
    const wrapper = mount(PaymentForm, {
      props: { 
        modelValue: 'efectivo',
        disabled: true 
      }
    })
    expect(wrapper.find('button').attributes('disabled')).toBe('')
  })

  it('renders form title', () => {
    const wrapper = mount(PaymentForm)
    expect(wrapper.find('h3').text()).toBe('Método de Pago')
  })

  it('renders all payment method options', () => {
    const wrapper = mount(PaymentForm)
    const options = wrapper.findAll('.method-option')
    expect(options).toHaveLength(2)
  })

  it('has correct button text by default', () => {
    const wrapper = mount(PaymentForm)
    expect(wrapper.find('button').text()).toBe('Pagar y Salir')
  })
})
