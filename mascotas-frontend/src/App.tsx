import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import { ProtectedRoute } from './auth/ProtectedRoute';
import AdminLayout from './layouts/AdminLayout';
import PublicLayout from './layouts/PublicLayout';

// Auth
import Login from './pages/auth/Login';
import Register from './pages/auth/Register';

// Dashboard
import Dashboard from './pages/dashboard/Dashboard';

// Modules
import UserList from './modules/users/UserList';
import AnimalList from './modules/animals/AnimalList';
import AnimalForm from './modules/animals/AnimalForm';
import AnimalDetail from './modules/animals/AnimalDetail';
import CampaignList from './modules/campaigns/CampaignList';
import AdoptionList from './modules/adoptions/AdoptionList';
import StrayAnimalList from './modules/strayAnimals/StrayAnimalList';
import PostList from './modules/posts/PostList';
import NotificationList from './modules/notifications/NotificationList';
import ReportsDashboard from './modules/reports/ReportsDashboard';

// Error Pages
import Unauthorized from './pages/errors/Unauthorized';

function App() {
  return (
    <AuthProvider>
      <Router>
        <Routes>
          {/* Public */}
          <Route element={<PublicLayout />}>
            <Route path="/login" element={<Login />} />
            <Route path="/register" element={<Register />} />
          </Route>

          {/* Protected */}
          <Route element={<ProtectedRoute />}>
            <Route element={<AdminLayout />}>
              <Route path="/" element={<Dashboard />} />
              <Route path="/users" element={<UserList />} />
              <Route path="/animals" element={<AnimalList />} />
              <Route path="/animals/new" element={<AnimalForm />} />
              <Route path="/animals/:id" element={<AnimalDetail />} />
              <Route path="/animals/:id/edit" element={<AnimalForm />} />
              <Route path="/health" element={<AnimalList />} />
              <Route path="/adoptions" element={<AdoptionList />} />
              <Route path="/campaigns" element={<CampaignList />} />
              <Route path="/stray" element={<StrayAnimalList />} />
              <Route path="/posts" element={<PostList />} />
              <Route path="/notifications" element={<NotificationList />} />
              <Route path="/reports" element={<ReportsDashboard />} />
              <Route path="/unauthorized" element={<Unauthorized />} />
            </Route>
          </Route>

          <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
      </Router>
    </AuthProvider>
  );
}

export default App;