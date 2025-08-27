import { useState, useEffect } from 'react';
import { InventoryItem, InventoryStats } from '../types/inventory';
import { ProductsApi } from '../lib/api';

const STORAGE_KEY = 'inventoryItems';

export function useInventory() {
  const [items, setItems] = useState<InventoryItem[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;
    (async () => {
      try {
        const res = await ProductsApi.list();
        const dataArray = Array.isArray(res.data) ? res.data : (res.data ?? res); // support paginate
        const mapped: InventoryItem[] = dataArray.map(mapProductToInventoryItem);
        if (!cancelled) {
          setItems(mapped);
          localStorage.setItem(STORAGE_KEY, JSON.stringify(mapped));
        }
      } catch (e) {
        const savedItems = localStorage.getItem(STORAGE_KEY);
        if (savedItems && !cancelled) {
          setItems(JSON.parse(savedItems));
        }
      } finally {
        if (!cancelled) setLoading(false);
      }
    })();
    return () => { cancelled = true; };
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

  const addItem = async (item: Omit<InventoryItem, 'id' | 'lastUpdated' | 'status'>) => {
    const payload = mapInventoryItemToProductInput(item);
    const created = await ProductsApi.create(payload);
    const newItem = mapProductToInventoryItem(created);
    const newItems = [...items, newItem];
    saveItems(newItems);
    return newItem;
  };

  const updateItem = async (id: string, updates: Partial<InventoryItem>) => {
    const payload = mapInventoryItemToProductInput(updates as any);
    const updated = await ProductsApi.update(id, payload);
    const updatedItem = mapProductToInventoryItem(updated);
    const newItems = items.map(i => i.id === id ? updatedItem : i);
    saveItems(newItems);
  };

  const deleteItem = async (id: string) => {
    await ProductsApi.remove(id);
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

function mapProductToInventoryItem(p: any): InventoryItem {
  const statusMap: Record<string, InventoryItem['status']> = {
    in_stock: 'in-stock',
    low_stock: 'low-stock',
    out_of_stock: 'out-of-stock',
    overstocked: 'in-stock',
  };
  return {
    id: String(p.id),
    name: p.name,
    description: p.description ?? '',
    sku: p.sku,
    category: p.category?.name ?? String(p.category_id ?? ''),
    quantity: Number(p.current_quantity ?? 0),
    minStock: Number(p.min_stock_level ?? 0),
    maxStock: Number(p.max_stock_level ?? 0),
    unitPrice: Number(p.unit_price ?? 0),
    supplier: p.supplier_name ?? '',
    location: p.storage_location ?? '',
    lastUpdated: p.updated_at ?? p.created_at ?? new Date().toISOString(),
    status: statusMap[p.stock_status as string] ?? 'in-stock',
  };
}

function mapInventoryItemToProductInput(item: Partial<InventoryItem>) {
  const payload: any = {};
  if (item.name !== undefined) payload.name = item.name;
  if (item.description !== undefined) payload.description = item.description;
  if (item.sku !== undefined) payload.sku = item.sku;
  if (item.category !== undefined) payload.category_id = Number(item.category); // expect id string -> number
  if (item.quantity !== undefined) payload.current_quantity = Number(item.quantity);
  if (item.minStock !== undefined) payload.min_stock_level = Number(item.minStock);
  if (item.maxStock !== undefined) payload.max_stock_level = Number(item.maxStock);
  if (item.unitPrice !== undefined) payload.unit_price = Number(item.unitPrice);
  if (item.supplier !== undefined) payload.supplier_name = item.supplier;
  if (item.location !== undefined) payload.storage_location = item.location;
  return payload;
}