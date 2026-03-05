import { mount } from '@vue/test-utils'
import { describe, it, expect, beforeEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import PlateInput from '@/components/PlateInput.vue'

describe('PlateInput', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('renders with placeholder', () => {
    const wrapper = mount(PlateInput, {
      props: { placeholder: 'ABC-1234' }
    })
    expect(wrapper.find('input').attributes('placeholder')).toBe('ABC-1234')
  })

  it('displays error message', () => {
    const wrapper = mount(PlateInput, {
      props: { error: 'Formato de placa inválido' }
    })
    expect(wrapper.text()).toContain('Formato de placa inválido')
  })

  it('disables input when disabled prop is true', () => {
    const wrapper = mount(PlateInput, {
      props: { disabled: true }
    })
    expect(wrapper.find('input').attributes('disabled')).toBe('')
  })

  it('displays label when provided', () => {
    const wrapper = mount(PlateInput, {
      props: { label: 'Placa del vehículo' }
    })
    expect(wrapper.find('label').text()).toBe('Placa del vehículo')
  })

  it('initializes with modelValue', () => {
    const wrapper = mount(PlateInput, {
      props: { modelValue: 'XYZ-5678' }
    })
    expect(wrapper.find('input').element.value).toBe('XYZ-5678')
  })

  it('renders input element', () => {
    const wrapper = mount(PlateInput)
    expect(wrapper.find('input').exists()).toBe(true)
  })

  it('has correct input type', () => {
    const wrapper = mount(PlateInput)
    expect(wrapper.find('input').attributes('type')).toBe('text')
  })

  it('updates modelValue when props change', async () => {
    const wrapper = mount(PlateInput, {
      props: { modelValue: 'ABC-1234' }
    })
    
    await wrapper.setProps({ modelValue: 'XYZ-9999' })
    
    expect(wrapper.find('input').element.value).toBe('XYZ-9999')
  })
})
