export type HttpMethod = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';

const API_BASE = '/api';

function getAuthToken(): string | null {
  return localStorage.getItem('authToken');
}

export async function apiFetch<T>(path: string, options: { method?: HttpMethod; body?: unknown; auth?: boolean } = {}): Promise<T> {
  const { method = 'GET', body, auth = false } = options;
  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  };
  if (auth) {
    const token = getAuthToken();
    if (token) headers['Authorization'] = `Bearer ${token}`;
  }

  const response = await fetch(`${API_BASE}${path}`, {
    method,
    headers,
    body: body ? JSON.stringify(body) : undefined,
    credentials: 'include',
  });

  if (!response.ok) {
    let message = 'Request failed';
    try {
      const err = await response.json();
      message = err.message || JSON.stringify(err);
    } catch {}
    throw new Error(message);
  }

  if (response.status === 204) {
    return undefined as unknown as T;
  }

  return response.json() as Promise<T>;
}

export const AuthApi = {
  async register(name: string, email: string, password: string) {
    return apiFetch<{ token: string; user: any }>(`/register`, { method: 'POST', body: { name, email, password } });
  },
  async login(email: string, password: string) {
    return apiFetch<{ token: string; user: any }>(`/login`, { method: 'POST', body: { email, password } });
  },
  async me() {
    return apiFetch<any>(`/user`, { auth: true });
  },
  async logout() {
    return apiFetch<{ message: string }>(`/logout`, { method: 'POST', auth: true });
  }
};

export const ProductsApi = {
  async list(params?: Record<string, string | number | boolean>) {
    const query = params ? `?${new URLSearchParams(Object.entries(params).map(([k,v]) => [k, String(v)]))}` : '';
    return apiFetch<any>(`/products${query}`, { auth: true });
  },
  async get(id: string | number) {
    return apiFetch<any>(`/products/${id}`, { auth: true });
  },
  async create(data: any) {
    return apiFetch<any>(`/products`, { method: 'POST', body: data, auth: true });
  },
  async update(id: string | number, data: any) {
    return apiFetch<any>(`/products/${id}`, { method: 'PUT', body: data, auth: true });
  },
  async remove(id: string | number) {
    return apiFetch<{ message: string }>(`/products/${id}`, { method: 'DELETE', auth: true });
  }
};

export const CategoriesApi = {
  async list() {
    return apiFetch<any[]>(`/categories`, { auth: true });
  }
};

export const DashboardApi = {
  async summary() {
    return apiFetch<any>(`/dashboard/summary`, { auth: true });
  }
};

