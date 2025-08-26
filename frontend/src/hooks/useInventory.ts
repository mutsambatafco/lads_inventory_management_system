import { useState, useEffect } from 'react';
import { InventoryItem, InventoryStats } from '../types/inventory';

const STORAGE_KEY = 'inventoryItems';

// Sample data for demonstration
const sampleData: InventoryItem[] = [
  {
    id: '1',
    name: 'Wireless Headphones',
    description: 'Premium noise-cancelling wireless headphones',
    sku: 'WH-001',
    category: 'Electronics',
    quantity: 45,
    minStock: 10,
    maxStock: 100,
    unitPrice: 199.99,
    supplier: 'Tech Supplier Co.',
    location: 'Warehouse A-1',
    lastUpdated: new Date().toISOString(),
    status: 'in-stock'
  },
  {
    id: '2',
    name: 'Office Chair',
    description: 'Ergonomic office chair with lumbar support',
    sku: 'OC-002',
    category: 'Furniture',
    quantity: 5,
    minStock: 8,
    maxStock: 50,
    unitPrice: 299.99,
    supplier: 'Office Furniture Ltd.',
    location: 'Warehouse B-2',
    lastUpdated: new Date().toISOString(),
    status: 'low-stock'
  },
  {
    id: '3',
    name: 'Smartphone Case',
    description: 'Protective case for latest smartphone models',
    sku: 'SC-003',
    category: 'Accessories',
    quantity: 0,
    minStock: 15,
    maxStock: 200,
    unitPrice: 24.99,
    supplier: 'Mobile Accessories Inc.',
    location: 'Warehouse A-3',
    lastUpdated: new Date().toISOString(),
    status: 'out-of-stock'
  },
];

export function useInventory() {
  const [items, setItems] = useState<InventoryItem[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const savedItems = localStorage.getItem(STORAGE_KEY);
    if (savedItems) {
      setItems(JSON.parse(savedItems));
    } else {
      // Initialize with sample data
      setItems(sampleData);
      localStorage.setItem(STORAGE_KEY, JSON.stringify(sampleData));
    }
    setLoading(false);
  }, []);

  const saveItems = (newItems: InventoryItem[]) => {
    setItems(newItems);
    localStorage.setItem(STORAGE_KEY, JSON.stringify(newItems));
  };

  const updateItemStatus = (item: InventoryItem): InventoryItem => {
    let status: InventoryItem['status'] = 'in-stock';
    if (item.quantity === 0) {
      status = 'out-of-stock';
    } else if (item.quantity <= item.minStock) {
      status = 'low-stock';
    }
    return { ...item, status, lastUpdated: new Date().toISOString() };
  };

  const addItem = (item: Omit<InventoryItem, 'id' | 'lastUpdated' | 'status'>) => {
    const newItem = updateItemStatus({
      ...item,
      id: Date.now().toString(),
      lastUpdated: new Date().toISOString(),
      status: 'in-stock'
    });
    const newItems = [...items, newItem];
    saveItems(newItems);
    return newItem;
  };

  const updateItem = (id: string, updates: Partial<InventoryItem>) => {
    const newItems = items.map(item => {
      if (item.id === id) {
        const updatedItem = { ...item, ...updates };
        return updateItemStatus(updatedItem);
      }
      return item;
    });
    saveItems(newItems);
  };

  const deleteItem = (id: string) => {
    const newItems = items.filter(item => item.id !== id);
    saveItems(newItems);
  };

  const getItemById = (id: string) => {
    return items.find(item => item.id === id);
  };

  const getStats = (): InventoryStats => {
    const totalItems = items.length;
    const totalValue = items.reduce((sum, item) => sum + (item.quantity * item.unitPrice), 0);
    const lowStockItems = items.filter(item => item.status === 'low-stock').length;
    const outOfStockItems = items.filter(item => item.status === 'out-of-stock').length;

    const categoryMap = items.reduce((acc, item) => {
      acc[item.category] = (acc[item.category] || 0) + 1;
      return acc;
    }, {} as Record<string, number>);

    const categories = Object.entries(categoryMap).map(([name, count]) => ({
      name,
      count
    }));

    const recentActivity = items
      .sort((a, b) => new Date(b.lastUpdated).getTime() - new Date(a.lastUpdated).getTime())
      .slice(0, 10)
      .map(item => ({
        id: item.id,
        action: `Updated ${item.name}`,
        item: item.name,
        timestamp: item.lastUpdated
      }));

    return {
      totalItems,
      totalValue,
      lowStockItems,
      outOfStockItems,
      categories,
      recentActivity
    };
  };

  return {
    items,
    loading,
    addItem,
    updateItem,
    deleteItem,
    getItemById,
    getStats
  };
}