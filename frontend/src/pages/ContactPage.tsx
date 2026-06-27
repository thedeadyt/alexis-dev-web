import { useState } from 'react'
import { postContact } from '../hooks/useApi'

const types = ['Site vitrine', 'Application web', 'API & intégration', 'E-commerce', 'Hébergement', 'Maintenance', 'Autre']
const budgets = ['À définir', '< 500€', '500€ – 1 500€', '1 500€ – 5 000€', '5 000€ – 15 000€', '> 15 000€']

export default function ContactPage() {
  const [form, setForm] = useState({ first_name: '', last_name: '', email: '', phone: '', type: 'Site vitrine', budget: 'À définir', message: '' })
  const [rgpd, setRgpd] = useState(false)
  const [submitted, setSubmitted] = useState(false)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)

  const canSubmit = rgpd && form.first_name.trim() && form.email.trim()

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    if (!canSubmit) return
    setLoading(true)
    setError(null)
    try {
      await postContact(form)
      setSubmitted(true)
      window.scrollTo(0, 0)
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : 'Une erreur est survenue.')
    } finally {
      setLoading(false)
    }
  }

  if (submitted) {
    return (
      <main className="pt-32 pb-24 px-6 max-w-2xl mx-auto text-center">
        <div className="text-6xl mb-6">✓</div>
        <h1 className="font-display font-bold text-3xl mb-4">Message reçu !</h1>
        <p className="text-muted text-lg">Je reviens vers vous sous 24h. À bientôt.</p>
      </main>
    )
  }

  return (
    <main className="pt-24 pb-24 px-6 max-w-6xl mx-auto">
      <div className="mb-12">
        <span className="font-mono text-accent text-sm">// contact/</span>
        <h1 className="font-display font-bold text-4xl md:text-5xl mt-4 mb-4">Parlons de votre projet</h1>
        <p className="text-muted text-xl">Réponse garantie sous 24h.</p>
      </div>

      <div className="grid md:grid-cols-2 gap-16">
        {/* Formulaire */}
        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="grid grid-cols-2 gap-4">
            {[['first_name', 'Prénom *', 'text'], ['last_name', 'Nom', 'text']].map(([name, label, type]) => (
              <div key={name}>
                <label className="block text-muted text-sm mb-2">{label}</label>
                <input
                  type={type}
                  name={name}
                  value={form[name as keyof typeof form]}
                  onChange={e => setForm(f => ({ ...f, [name]: e.target.value }))}
                  className="w-full bg-card border border-border rounded px-4 py-3 text-text outline-none focus:border-accent transition-colors"
                />
              </div>
            ))}
          </div>

          {[['email', 'Email *', 'email'], ['phone', 'Téléphone', 'tel']].map(([name, label, type]) => (
            <div key={name}>
              <label className="block text-muted text-sm mb-2">{label}</label>
              <input
                type={type}
                name={name}
                value={form[name as keyof typeof form]}
                onChange={e => setForm(f => ({ ...f, [name]: e.target.value }))}
                className="w-full bg-card border border-border rounded px-4 py-3 text-text outline-none focus:border-accent transition-colors"
              />
            </div>
          ))}

          <div>
            <label className="block text-muted text-sm mb-2">Type de projet</label>
            <select
              name="type"
              value={form.type}
              onChange={e => setForm(f => ({ ...f, type: e.target.value }))}
              className="w-full bg-card border border-border rounded px-4 py-3 text-text outline-none focus:border-accent transition-colors"
            >
              {types.map(t => <option key={t} value={t}>{t}</option>)}
            </select>
          </div>

          <div>
            <label className="block text-muted text-sm mb-2">Budget estimé</label>
            <select
              name="budget"
              value={form.budget}
              onChange={e => setForm(f => ({ ...f, budget: e.target.value }))}
              className="w-full bg-card border border-border rounded px-4 py-3 text-text outline-none focus:border-accent transition-colors"
            >
              {budgets.map(b => <option key={b} value={b}>{b}</option>)}
            </select>
          </div>

          <div>
            <label className="block text-muted text-sm mb-2">Message</label>
            <textarea
              name="message"
              rows={5}
              value={form.message}
              onChange={e => setForm(f => ({ ...f, message: e.target.value }))}
              placeholder="Décrivez votre projet, vos besoins..."
              className="w-full bg-card border border-border rounded px-4 py-3 text-text outline-none focus:border-accent transition-colors resize-none"
            />
          </div>

          <label className="flex items-start gap-3 cursor-pointer">
            <div
              onClick={() => setRgpd(r => !r)}
              className="w-5 h-5 rounded border flex-shrink-0 mt-0.5 flex items-center justify-center transition-colors"
              style={{ background: rgpd ? '#8B00FF' : '#161616', borderColor: rgpd ? '#8B00FF' : '#2A2A2A' }}
            >
              {rgpd && <span className="text-white text-xs">✓</span>}
            </div>
            <span className="text-muted text-sm">
              J'accepte que mes données soient utilisées pour traiter ma demande de contact.
            </span>
          </label>

          {error && <p className="text-red-400 text-sm">{error}</p>}

          <button
            type="submit"
            disabled={!canSubmit || loading}
            className="w-full py-3 rounded font-medium text-white transition-all"
            style={{ background: canSubmit ? '#8B00FF' : '#3D0080', opacity: canSubmit ? 1 : 0.6, cursor: canSubmit ? 'pointer' : 'not-allowed' }}
          >
            {loading ? 'Envoi...' : 'Envoyer la demande'}
          </button>
        </form>

        {/* Cal.com RDV */}
        <div>
          <h2 className="font-display font-bold text-2xl mb-4">Prendre rendez-vous</h2>
          <p className="text-muted text-sm mb-6">Préférez un appel découverte de 30 minutes ? Réservez directement un créneau.</p>
          <div className="bg-card border border-border rounded-lg overflow-hidden" style={{ height: 600 }}>
            <iframe
              src="https://rdv.alexis-rodrigues.fr/alexis"
              className="w-full h-full border-0"
              title="Prise de rendez-vous"
            />
          </div>
        </div>
      </div>
    </main>
  )
}
