import { useApi } from '../hooks/useApi'
import type { Testimonial } from '../types'

const philosophies = [
  { title: "Code d'abord", body: "Un site est avant tout un logiciel. Je privilégie un code propre, durable et performant plutôt que les effets de surface." },
  { title: "Transparence totale", body: "Devis détaillés, points réguliers, code qui vous appartient. Aucune boîte noire, aucune dépendance imposée." },
  { title: "Partenariat long terme", body: "Je ne livre pas pour disparaître. Maintenance, évolutions et support : je reste disponible dans la durée." },
]
const techGroups = [
  { name: 'Frontend', items: ['React', 'Tailwind', 'TypeScript', 'Vite'] },
  { name: 'Backend', items: ['PHP', 'Laravel', 'MySQL', 'PostgreSQL'] },
  { name: 'Infra', items: ['Docker', 'Linux', 'Nginx', 'Cloudflare'] },
  { name: 'Outils', items: ['Git', 'Figma', 'Postman', 'CI/CD'] },
]
const stats = [
  { value: '12+', label: 'Projets livrés' },
  { value: '3 ans', label: "D'expérience" },
  { value: '100%', label: 'Réalisé en interne' },
]

export default function AgencePage() {
  const { data: testimonials } = useApi<Testimonial[]>('/api/testimonials')

  return (
    <main className="pt-24 pb-24 px-6 max-w-6xl mx-auto">
      <div className="mb-16">
        <span className="font-mono text-accent text-sm">// agence/</span>
        <h1 className="font-display font-bold text-4xl md:text-5xl mt-4 mb-4">alexis dev web</h1>
        <p className="text-muted text-xl max-w-xl">Agence web indépendante spécialisée dans le développement sur-mesure.</p>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-3 gap-8 border border-border rounded-lg p-8 mb-16">
        {stats.map(s => (
          <div key={s.label} className="text-center">
            <div className="font-display font-bold text-4xl text-accent">{s.value}</div>
            <div className="text-muted text-sm mt-1">{s.label}</div>
          </div>
        ))}
      </div>

      {/* Philosophie */}
      <section className="mb-16">
        <h2 className="font-display font-bold text-3xl mb-10">Ma philosophie</h2>
        <div className="grid md:grid-cols-3 gap-6">
          {philosophies.map(p => (
            <div key={p.title} className="bg-card border border-border rounded-lg p-6">
              <h3 className="font-display font-semibold text-lg mb-3 text-accent">{p.title}</h3>
              <p className="text-muted text-sm leading-relaxed">{p.body}</p>
            </div>
          ))}
        </div>
      </section>

      {/* Stack tech */}
      <section className="mb-16 border-t border-border pt-16">
        <h2 className="font-display font-bold text-3xl mb-10">Ma stack</h2>
        <div className="grid md:grid-cols-4 gap-6">
          {techGroups.map(g => (
            <div key={g.name}>
              <div className="font-mono text-accent text-sm mb-4">// {g.name.toLowerCase()}/</div>
              <ul className="space-y-2">
                {g.items.map(item => (
                  <li key={item} className="text-text text-sm flex items-center gap-2">
                    <span className="w-1 h-1 bg-accent rounded-full inline-block"></span>
                    {item}
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </div>
      </section>

      {/* Témoignages */}
      {testimonials && testimonials.length > 0 && (
        <section className="border-t border-border pt-16">
          <h2 className="font-display font-bold text-3xl mb-10">Ce qu'ils disent</h2>
          <div className="grid md:grid-cols-2 gap-6">
            {testimonials.map(t => (
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
      )}
    </main>
  )
}
