import { useNavigate } from 'react-router-dom'
import { useApi } from '../hooks/useApi'
import type { Service } from '../types'

const steps = [
  { num: '01', title: 'Analyse', desc: 'Compréhension de vos besoins, contraintes et objectifs.' },
  { num: '02', title: 'Conception', desc: 'Maquettes et architecture validées avec vous avant le dev.' },
  { num: '03', title: 'Développement', desc: 'Code livré en itérations courtes avec points réguliers.' },
  { num: '04', title: 'Livraison', desc: 'Mise en ligne, formation et support inclus.' },
]

export default function ServicesPage() {
  const navigate = useNavigate()
  const { data: services, loading } = useApi<Service[]>('/api/services')

  return (
    <main className="pt-24 pb-24 px-6 max-w-6xl mx-auto">
      <div className="mb-16">
        <span className="font-mono text-accent text-sm">// services/</span>
        <h1 className="font-display font-bold text-4xl md:text-5xl mt-4 mb-4">Ce qu'on construit</h1>
        <p className="text-muted text-xl max-w-xl">Du site vitrine à l'application métier complexe, chaque projet est développé sur-mesure.</p>
      </div>

      {loading ? (
        <div className="text-muted">Chargement...</div>
      ) : (
        <div className="grid md:grid-cols-2 gap-6 mb-24">
          {(services ?? []).map(s => (
            <div key={s.id} className="bg-card border border-border rounded-lg p-8 hover:border-accent transition-colors">
              <div className="font-mono text-accent text-sm mb-3">{s.label}</div>
              <h2 className="font-display font-semibold text-xl mb-1">{s.title}</h2>
              <p className="text-muted text-sm mb-4">{s.sub}</p>
              <p className="text-text leading-relaxed mb-6">{s.body}</p>
              <div className="flex flex-wrap gap-2 mb-6">
                {s.tags.map(t => (
                  <span key={t} className="text-xs border border-border rounded px-2 py-1 text-muted">{t}</span>
                ))}
              </div>
              <div className="font-display font-bold text-accent">{s.price}</div>
            </div>
          ))}
        </div>
      )}

      {/* Étapes */}
      <section className="border-t border-border pt-16">
        <h2 className="font-display font-bold text-3xl mb-12">Comment on travaille</h2>
        <div className="grid md:grid-cols-4 gap-8">
          {steps.map(s => (
            <div key={s.num}>
              <div className="font-display font-bold text-4xl text-accent mb-4">{s.num}</div>
              <h3 className="font-semibold text-lg mb-2">{s.title}</h3>
              <p className="text-muted text-sm leading-relaxed">{s.desc}</p>
            </div>
          ))}
        </div>
      </section>

      {/* CTA */}
      <div className="mt-16 text-center">
        <button
          onClick={() => navigate('/contact')}
          className="bg-accent text-white px-8 py-3 rounded font-medium hover:bg-accent-dark transition-colors"
        >
          Discutons de votre projet
        </button>
      </div>
    </main>
  )
}
