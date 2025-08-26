export interface InventoryItem {
  id: string;
  name: string;
  description: string;
  sku: string;
  category: string;
  quantity: number;
  minStock: number;
  maxStock: number;
  unitPrice: number;
  supplier: string;
  location: string;
  lastUpdated: string;
  status: 'in-stock' | 'low-stock' | 'out-of-stock';
}

export interface User {
  id: string;
  email: string;
  name: string;
  role: 'admin' | 'user';
  avatar?: string;
  createdAt: string;
}

export interface InventoryStats {
  totalItems: number;
  totalValue: number;
  lowStockItems: number;
  outOfStockItems: number;
  categories: { name: string; count: number }[];
  recentActivity: Array<{
    id: string;
    action: string;
    item: string;
    timestamp: string;
  }>;
}