import { useNavigate } from 'react-router-dom'
import { useApi } from '../hooks/useApi'
import type { Project, Service, Testimonial } from '../types'

const metrics = [
  { value: '12+', label: 'Projets livrés' },
  { value: '3 ans', label: "D'expérience" },
  { value: '100%', label: 'Code sur-mesure' },
  { value: '< 24h', label: 'Délai de réponse' },
]

export default function HomePage() {
  const navigate = useNavigate()
  const { data: projects } = useApi<Project[]>('/api/projects')
  const { data: services } = useApi<Service[]>('/api/services')
  const { data: testimonials } = useApi<Testimonial[]>('/api/testimonials')

  return (
    <main className="pt-16">
      {/* Hero */}
      <section className="min-h-screen flex flex-col justify-center px-6 max-w-6xl mx-auto">
        <div className="border-l-2 border-accent pl-6 mb-8">
          <span className="font-mono text-accent text-sm">// agence web sur-mesure</span>
        </div>
        <h1 className="font-display font-bold text-5xl md:text-7xl leading-tight mb-6">
          Des sites qui<br />
          <span className="text-accent">travaillent</span><br />
          pour vous.
        </h1>
        <p className="text-muted text-xl max-w-xl mb-10">
          Développement web sur-mesure : sites vitrines, applications métier, APIs. Code propre, architecture solide, performances optimisées.
        </p>
        <div className="flex gap-4 flex-wrap">
          <button
            onClick={() => navigate('/contact')}
            className="bg-accent text-white px-8 py-3 rounded font-medium hover:bg-accent-dark transition-colors"
          >
            Démarrer un projet
          </button>
          <button
            onClick={() => navigate('/realisations')}
            className="border border-border text-text px-8 py-3 rounded font-medium hover:border-accent transition-colors"
          >
            Voir les réalisations
          </button>
        </div>
      </section>

      {/* Métriques */}
      <section className="border-y border-border py-12 px-6">
        <div className="max-w-6xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-8">
          {metrics.map(m => (
            <div key={m.label} className="text-center">
              <div className="font-display font-bold text-3xl text-accent">{m.value}</div>
              <div className="text-muted text-sm mt-1">{m.label}</div>
            </div>
          ))}
        </div>
      </section>

      {/* Aperçu services */}
      <section className="py-24 px-6 max-w-6xl mx-auto">
        <div className="flex items-end justify-between mb-12">
          <h2 className="font-display font-bold text-3xl">Ce qu'on fait</h2>
          <button
            onClick={() => navigate('/services')}
            className="text-accent text-sm hover:text-text transition-colors"
          >
            Tous les services →
          </button>
        </div>
        <div className="grid md:grid-cols-3 gap-6">
          {(services ?? []).slice(0, 3).map(s => (
            <div key={s.id} className="bg-card border border-border rounded-lg p-6">
              <div className="font-mono text-accent text-sm mb-3">{s.label}</div>
              <h3 className="font-display font-semibold text-lg mb-2">{s.title}</h3>
              <p className="text-muted text-sm leading-relaxed mb-4">{s.body}</p>
              <div className="flex flex-wrap gap-2">
                {s.tags.map(t => (
                  <span key={t} className="text-xs border border-border rounded px-2 py-1 text-muted">{t}</span>
                ))}
              </div>
            </div>
          ))}
        </div>
      </section>

      {/* Réalisations preview */}
      <section className="py-24 px-6 max-w-6xl mx-auto border-t border-border">
        <div className="flex items-end justify-between mb-12">
          <h2 className="font-display font-bold text-3xl">Réalisations récentes</h2>
          <button
            onClick={() => navigate('/realisations')}
            className="text-accent text-sm hover:text-text transition-colors"
          >
            Voir tout →
          </button>
        </div>
        <div className="grid md:grid-cols-3 gap-6">
          {(projects ?? []).slice(0, 3).map(p => (
            <button
              key={p.id}
              onClick={() => navigate(`/realisations/${p.slug}`)}
              className="bg-card border border-border rounded-lg p-6 text-left hover:border-accent transition-colors w-full"
            >
              <div className="flex justify-between items-start mb-4">
                <span className="text-xs border border-border rounded px-2 py-1 text-muted">{p.category}</span>
                <span className="text-muted text-xs">{p.year}</span>
              </div>
              <h3 className="font-display font-semibold text-lg mb-2">{p.name}</h3>
              <p className="text-muted text-sm leading-relaxed mb-4">{p.summary}</p>
              <div className="flex flex-wrap gap-2">
                {p.tech.map(t => (
                  <span key={t} className="font-mono text-xs text-accent">{t}</span>
                ))}
              </div>
            </button>
          ))}
        </div>
      </section>

      {/* Témoignages */}
      <section className="py-24 px-6 max-w-6xl mx-auto border-t border-border">
        <h2 className="font-display font-bold text-3xl mb-12">Ce qu'ils disent</h2>
        <div className="grid md:grid-cols-2 gap-6">
          {(testimonials ?? []).map(t => (
            <div key={t.id} className="bg-card border border-border rounded-lg p-8">
              <p className="text-text leading-relaxed mb-6 italic">"{t.quote}"</p>
              <div>
                <div className="font-semibold">{t.author}</div>
                <div className="text-muted text-sm">{t.role}</div>
              </div>
            </div>
          ))}
        </div>
      </section>
    </main>
  )
}
