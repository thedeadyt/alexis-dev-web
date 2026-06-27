import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useApi } from '../hooks/useApi'
import type { Project } from '../types'

const filters = ['Tous', 'Sites vitrines', 'Applications', 'Infra']

export default function RealisationsPage() {
  const navigate = useNavigate()
  const { data: projects, loading } = useApi<Project[]>('/api/projects')
  const [active, setActive] = useState('Tous')

  const filtered = (projects ?? []).filter(p => active === 'Tous' || p.category === active)

  return (
    <main className="pt-24 pb-24 px-6 max-w-6xl mx-auto">
      <div className="mb-12">
        <span className="font-mono text-accent text-sm">// réalisations/</span>
        <h1 className="font-display font-bold text-4xl md:text-5xl mt-4 mb-4">Mes projets</h1>
        <p className="text-muted text-xl">Code propre, clients satisfaits.</p>
      </div>

      {/* Filtres */}
      <div className="flex gap-3 flex-wrap mb-10">
        {filters.map(f => (
          <button
            key={f}
            onClick={() => setActive(f)}
            className="px-4 py-1.5 rounded border text-sm transition-colors"
            style={{
              background: active === f ? '#8B00FF' : 'transparent',
              color: active === f ? '#fff' : '#888888',
              borderColor: active === f ? '#8B00FF' : '#2A2A2A',
            }}
          >
            {f}
          </button>
        ))}
      </div>

      {loading ? (
        <div className="text-muted">Chargement...</div>
      ) : filtered.length === 0 ? (
        <div className="text-muted">Aucun projet dans cette catégorie.</div>
      ) : (
        <div className="grid md:grid-cols-2 gap-6">
          {filtered.map(p => (
            <button
              key={p.id}
              onClick={() => navigate(`/realisations/${p.slug}`)}
              className="bg-card border border-border rounded-lg p-8 text-left hover:border-accent transition-colors w-full"
            >
              <div className="flex justify-between items-start mb-6">
                <span className="text-xs border border-border rounded px-2 py-1 text-muted">{p.category}</span>
                <span className="text-muted text-xs">{p.year}</span>
              </div>
              <h2 className="font-display font-semibold text-xl mb-1 text-text">{p.name}</h2>
              <p className="text-muted text-sm mb-6 leading-relaxed">{p.summary}</p>
              <div className="flex flex-wrap gap-2">
                {p.tech.map(t => (
                  <span key={t} className="font-mono text-xs text-accent border border-accent/30 rounded px-2 py-0.5">{t}</span>
                ))}
              </div>
            </button>
          ))}
        </div>
      )}
    </main>
  )
}
