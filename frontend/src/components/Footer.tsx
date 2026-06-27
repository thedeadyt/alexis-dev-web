import { NavLink } from 'react-router-dom'

export default function Footer() {
  return (
    <footer className="border-t border-border mt-24">
      <div className="max-w-6xl mx-auto px-6 py-12 flex flex-col md:flex-row justify-between gap-8">
        <div>
          <div className="font-display font-bold text-lg mb-2">
            alexis dev web<span className="text-accent">.</span>
          </div>
          <p className="text-muted text-sm">Des sites qui travaillent pour vous.</p>
        </div>
        <div className="flex gap-12">
          <div>
            <div className="text-xs text-muted uppercase tracking-widest mb-3">Navigation</div>
            {[['/', 'Accueil'], ['/services', 'Services'], ['/realisations', 'Réalisations'], ['/agence', 'Agence'], ['/contact', 'Contact']].map(([to, label]) => (
              <NavLink key={to} to={to} className="block text-sm text-muted hover:text-text no-underline mb-2 transition-colors">
                {label}
              </NavLink>
            ))}
          </div>
          <div>
            <div className="text-xs text-muted uppercase tracking-widest mb-3">Contact</div>
            <a href="mailto:rodriguesdosreisalexis@gmail.com" className="block text-sm text-muted hover:text-text no-underline mb-2 transition-colors">
              rodriguesdosreisalexis@gmail.com
            </a>
            <a href="tel:+33768882766" className="block text-sm text-muted hover:text-text no-underline mb-2 transition-colors">
              07 68 88 27 66
            </a>
            <NavLink to="/contact" className="block text-sm text-accent hover:text-text no-underline mb-2 transition-colors">
              Prendre RDV →
            </NavLink>
          </div>
        </div>
      </div>
      <div className="border-t border-border text-center py-4 text-xs text-muted">
        © {new Date().getFullYear()} alexis dev web — Tous droits réservés
      </div>
    </footer>
  )
}
