import { useState, useEffect } from 'react'

export function useApi<T>(url: string): { data: T | null; loading: boolean; error: string | null } {
  const [data, setData] = useState<T | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    setLoading(true)
    fetch(url)
      .then(r => {
        if (!r.ok) throw new Error(`HTTP ${r.status}`)
        return r.json()
      })
      .then(setData)
      .catch(e => setError(e.message))
      .finally(() => setLoading(false))
  }, [url])

  return { data, loading, error }
}

export async function postContact(data: Record<string, string>): Promise<{ success: boolean }> {
  const r = await fetch('/api/contact', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify(data),
  })
  if (!r.ok) {
    const err = await r.json()
    throw new Error(err.message || 'Erreur serveur')
  }
  return r.json()
}
