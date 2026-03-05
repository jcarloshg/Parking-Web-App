import { vi } from 'vitest'

global.localStorage = {
  getItem: vi.fn(),
  setItem: vi.fn(),
  removeItem: vi.fn(),
  clear: vi.fn(),
} as Storage

global.window = {
  location: {
    href: '',
  },
} as unknown as Window & typeof globalThis
