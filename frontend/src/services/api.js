import axios from 'axios';

const API_BASE_URL = 'http://localhost/ZetaCuts/backend/public/api';

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true,
});

api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

api.interceptors.response.use(
  (response) => response,
  (error) => {
    return Promise.reject(error);
  }
);

export const authService = {
  register: async (userData) => {
    const response = await api.post('/register', userData);
    if (response.data.success) {
      localStorage.setItem('auth_token', response.data.data.token);
      localStorage.setItem('user', JSON.stringify(response.data.data.user));
    }
    return response.data;
  },

  login: async (credentials) => {
    const response = await api.post('/login', credentials);
    if (response.data.success) {
      localStorage.setItem('auth_token', response.data.data.token);
      localStorage.setItem('user', JSON.stringify(response.data.data.user));
    }
    return response.data;
  },

  logout: async () => {
    try {
      await api.post('/logout');
    } catch (error) {
      console.error('Error al cerrar sesión:', error);
    } finally {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
    }
  },

  getCurrentUser: async () => {
    const response = await api.get('/user');
    return response.data;
  },

  getProfile: async () => {
    const response = await api.get('/user');
    return response.data;
  },
};

export const appointmentService = {
  getAll: async () => {
    const response = await api.get('/appointments');
    return response.data;
  },

  create: async (appointmentData) => {
    const response = await api.post('/appointments', appointmentData);
    return response.data;
  },

  getById: async (id) => {
    const response = await api.get(`/appointments/${id}`);
    return response.data;
  },

  update: async (id, appointmentData) => {
    const response = await api.put(`/appointments/${id}`, appointmentData);
    return response.data;
  },

  delete: async (id) => {
    const response = await api.delete(`/appointments/${id}`);
    return response.data;
  },
};

export const userService = {
  getProfile: async () => {
    const response = await api.get('/profile');
    return response.data;
  },

  updateProfile: async (profileData) => {
    const response = await api.put('/profile', profileData);
    return response.data;
  },

  getPoints: async () => {
    const response = await api.get('/points');
    return response.data;
  },

  updateEmail: async (emailData) => {
    const response = await api.put('/profile/email', emailData);
    return response.data;
  },
};

export const barberoService = {
  getAll: async (search = '', sort = 'asc') => {
    const response = await api.get('/barberos', {
      params: { search, sort }
    });
    return response.data;
  },

  create: async (barberoData) => {
    const response = await api.post('/barberos', barberoData);
    return response.data;
  },

  getById: async (id) => {
    const response = await api.get(`/barberos/${id}`);
    return response.data;
  },

  update: async (id, barberoData) => {
    const response = await api.put(`/barberos/${id}`, barberoData);
    return response.data;
  },

  delete: async (id) => {
    const response = await api.delete(`/barberos/${id}`);
    return response.data;
  },
};

export const publicBarberoService = {
  getAvailable: async () => {
    const response = await api.get('/barberos/available');
    return response.data;
  },
};

export default api; 