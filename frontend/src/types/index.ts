export interface Project {
  id: number
  slug: string
  name: string
  client: string
  category: string
  year: string
  summary: string
  full_text: string[]
  tech: string[]
  rendered: string[]
  sort_order: number
  active: boolean
}

export interface Service {
  id: number
  slug: string
  label: string
  title: string
  sub: string
  body: string
  tags: string[]
  price: string
  sort_order: number
  active: boolean
}

export interface Testimonial {
  id: number
  quote: string
  author: string
  role: string
  sort_order: number
  active: boolean
}

export interface ContactForm {
  first_name: string
  last_name: string
  email: string
  phone: string
  type: string
  budget: string
  message: string
}
