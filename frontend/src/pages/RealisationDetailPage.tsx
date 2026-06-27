import { useParams, useNavigate } from 'react-router-dom'
import { useApi } from '../hooks/useApi'
import type { Project } from '../types'

export default function RealisationDetailPage() {
  const { slug } = useParams<{ slug: string }>()
  const navigate = useNavigate()
  const { data: project, loading, error } = useApi<Project>(`/api/projects/${slug}`)

  if (loading) return <main className="pt-32 px-6 max-w-6xl mx-auto text-muted">Chargement...</main>

  if (error || !project) return (
    <main className="pt-32 px-6 max-w-6xl mx-auto">
      <p className="text-muted">Projet introuvable.</p>
      <button onClick={() => navigate('/realisations')} className="mt-4 text-accent hover:underline">
        ← Retour aux réalisations
      </button>
    </main>
  )

  return (
    <main className="pt-24 pb-24 px-6 max-w-4xl mx-auto">
      <button
        onClick={() => navigate('/realisations')}
        className="text-muted text-sm mb-8 flex items-center gap-2 hover:text-text transition-colors"
      >
        ← Toutes les réalisations
      </button>

      <div className="flex gap-3 items-center mb-6">
        <span className="text-xs border border-border rounded px-2 py-1 text-muted">{project.category}</span>
        <span className="text-muted text-xs">{project.year}</span>
        <span className="text-muted text-xs">· {project.client}</span>
      </div>

      <h1 className="font-display font-bold text-4xl md:text-5xl mb-4 text-text">{project.name}</h1>
      <p className="text-muted text-xl leading-relaxed mb-12">{project.summary}</p>

      {/* Texte complet */}
      {project.full_text.length > 0 && (
        <div className="space-y-6 mb-12">
          {project.full_text.map((para, i) => (
            <p key={i} className="text-text leading-relaxed">{para}</p>
          ))}
        </div>
      )}

      {/* Tech + livrables */}
      <div className="grid md:grid-cols-2 gap-8 border-t border-border pt-10">
        <div>
          <div className="font-mono text-accent text-sm mb-3">// technologies/</div>
          <div className="flex flex-wrap gap-2">
            {project.tech.map(t => (
              <span key={t} className="font-mono text-sm border border-accent/30 text-accent rounded px-3 py-1">{t}</span>
            ))}
          </div>
        </div>

        {project.rendered.length > 0 && (
          <div>
            <div className="font-mono text-accent text-sm mb-3">// livrables/</div>
            <ul className="space-y-2">
              {project.rendered.map((r, i) => (
                <li key={i} className="flex items-center gap-2 text-text text-sm">
                  <span className="text-accent">✓</span> {r}
                </li>
              ))}
            </ul>
          </div>
        )}
      </div>

      <div className="mt-16 text-center">
        <button
          onClick={() => navigate('/contact')}
          className="bg-accent text-white px-8 py-3 rounded font-medium hover:bg-accent-dark transition-colors"
        >
          Démarrer un projet similaire
        </button>
      </div>
    </main>
  )
}
