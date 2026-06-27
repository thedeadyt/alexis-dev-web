import { BrowserRouter, Routes, Route } from 'react-router-dom'
import Navbar from './components/Navbar'
import Footer from './components/Footer'
import HomePage from './pages/HomePage'
import ServicesPage from './pages/ServicesPage'
import RealisationsPage from './pages/RealisationsPage'
import RealisationDetailPage from './pages/RealisationDetailPage'
import AgencePage from './pages/AgencePage'
import ContactPage from './pages/ContactPage'

export default function App() {
  return (
    <BrowserRouter>
      <Navbar />
      <Routes>
        <Route path="/" element={<HomePage />} />
        <Route path="/services" element={<ServicesPage />} />
        <Route path="/realisations" element={<RealisationsPage />} />
        <Route path="/realisations/:slug" element={<RealisationDetailPage />} />
        <Route path="/agence" element={<AgencePage />} />
        <Route path="/contact" element={<ContactPage />} />
      </Routes>
      <Footer />
    </BrowserRouter>
  )
}
