import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { ArrowLeft, Save } from 'lucide-react';
import { useInventory } from '../../hooks/useInventory';
import { CategoriesApi } from '../../lib/api';

export default function AddItem() {
  const navigate = useNavigate();
  const { addItem } = useInventory();

  const [formData, setFormData] = useState({
    name: '',
    description: '',
    sku: '',
    category: '',
    quantity: 0,
    minStock: 0,
    maxStock: 0,
    unitPrice: 0,
    supplier: '',
    location: ''
  });

  const [errors, setErrors] = useState<Record<string, string>>({});
  const [categories, setCategories] = useState<Array<{ id: number; name: string }>>([]);

  useEffect(() => {
    let cancelled = false;
    (async () => {
      try {
        const data = await CategoriesApi.list();
        if (!cancelled) setCategories(data);
      } catch {}
    })();
    return () => { cancelled = true; };
  }, []);

  const validateForm = () => {
    const newErrors: Record<string, string> = {};

    if (!formData.name.trim()) newErrors.name = 'Name is required';
    if (!formData.sku.trim()) newErrors.sku = 'SKU is required';
    if (!formData.category) newErrors.category = 'Category is required';
    if (formData.quantity < 0) newErrors.quantity = 'Quantity must be 0 or greater';
    if (formData.minStock < 0) newErrors.minStock = 'Minimum stock must be 0 or greater';
    if (formData.maxStock < formData.minStock) newErrors.maxStock = 'Maximum stock must be greater than minimum stock';
    if (formData.unitPrice <= 0) newErrors.unitPrice = 'Unit price must be greater than 0';
    if (!formData.supplier.trim()) newErrors.supplier = 'Supplier is required';
    if (!formData.location.trim()) newErrors.location = 'Location is required';

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    if (validateForm()) {
      addItem(formData);
      navigate('/inventory');
    }
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: (name === 'unitPrice' || name === 'quantity' || name === 'minStock' || name === 'maxStock')
        ? parseFloat(value) || 0
        : value
    }));

    // Clear error when user starts typing
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: '' }));
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center space-x-4">
        <button
          onClick={() => navigate('/inventory')}
          className="p-2 text-gray-400 hover:text-gray-600"
        >
          <ArrowLeft className="h-6 w-6" />
        </button>
        <div>
          <h2 className="text-3xl font-bold text-gray-900">Add New Item</h2>
          <p className="mt-2 text-gray-600">Create a new inventory item</p>
        </div>
      </div>

      <form onSubmit={handleSubmit} className="max-w-4xl">
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            {/* Basic Information */}
            <div className="md:col-span-2">
              <h3 className="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Item Name *
              </label>
              <input
                type="text"
                name="name"
                value={formData.name}
                onChange={handleInputChange}
                className={`w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 ${
                  errors.name ? 'border-red-300' : 'border-gray-300'
                }`}
                placeholder="Enter item name"
              />
              {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                SKU *
              </label>
              <input
                type="text"
                name="sku"
                value={formData.sku}
                onChange={handleInputChange}
                className={`w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 ${
                  errors.sku ? 'border-red-300' : 'border-gray-300'
                }`}
                placeholder="Enter SKU"
              />
              {errors.sku && <p className="mt-1 text-sm text-red-600">{errors.sku}</p>}
            </div>

            <div className="md:col-span-2">
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Description
              </label>
              <textarea
                name="description"
                value={formData.description}
                onChange={handleInputChange}
                rows={3}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                placeholder="Enter item description"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Category *
              </label>
              <select
                name="category"
                value={formData.category}
                onChange={handleInputChange}
                className={`w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 ${
                  errors.category ? 'border-red-300' : 'border-gray-300'
                }`}
              >
                <option value="">Select a category</option>
                {categories.map(c => (
                  <option key={c.id} value={String(c.id)}>{c.name}</option>
                ))}
              </select>
              {errors.category && <p className="mt-1 text-sm text-red-600">{errors.category}</p>}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Unit Price * ($)
              </label>
              <input
                type="number"
                name="unitPrice"
                value={formData.unitPrice}
                onChange={handleInputChange}
                min="0"
                step="0.01"
                className={`w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 ${
                  errors.unitPrice ? 'border-red-300' : 'border-gray-300'
                }`}
                placeholder="0.00"
              />
              {errors.unitPrice && <p className="mt-1 text-sm text-red-600">{errors.unitPrice}</p>}
            </div>

            {/* Stock Information */}
            <div className="md:col-span-2 mt-6">
              <h3 className="text-lg font-medium text-gray-900 mb-4">Stock Information</h3>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Current Quantity *
              </label>
              <input
                type="number"
                name="quantity"
                value={formData.quantity}
                onChange={handleInputChange}
                min="0"
                className={`w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 ${
                  errors.quantity ? 'border-red-300' : 'border-gray-300'
                }`}
                placeholder="0"
              />
              {errors.quantity && <p className="mt-1 text-sm text-red-600">{errors.quantity}</p>}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Minimum Stock Level *
              </label>
              <input
                type="number"
                name="minStock"
                value={formData.minStock}
                onChange={handleInputChange}
                min="0"
                className={`w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 ${
                  errors.minStock ? 'border-red-300' : 'border-gray-300'
                }`}
                placeholder="0"
              />
              {errors.minStock && <p className="mt-1 text-sm text-red-600">{errors.minStock}</p>}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Maximum Stock Level *
              </label>
              <input
                type="number"
                name="maxStock"
                value={formData.maxStock}
                onChange={handleInputChange}
                min="0"
                className={`w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 ${
                  errors.maxStock ? 'border-red-300' : 'border-gray-300'
                }`}
                placeholder="0"
              />
              {errors.maxStock && <p className="mt-1 text-sm text-red-600">{errors.maxStock}</p>}
            </div>

            {/* Supplier & Location */}
            <div className="md:col-span-2 mt-6">
              <h3 className="text-lg font-medium text-gray-900 mb-4">Supplier & Location</h3>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Supplier *
              </label>
              <input
                type="text"
                name="supplier"
                value={formData.supplier}
                onChange={handleInputChange}
                className={`w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 ${
                  errors.supplier ? 'border-red-300' : 'border-gray-300'
                }`}
                placeholder="Enter supplier name"
              />
              {errors.supplier && <p className="mt-1 text-sm text-red-600">{errors.supplier}</p>}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Storage Location *
              </label>
              <input
                type="text"
                name="location"
                value={formData.location}
                onChange={handleInputChange}
                className={`w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 ${
                  errors.location ? 'border-red-300' : 'border-gray-300'
                }`}
                placeholder="e.g., Warehouse A-1"
              />
              {errors.location && <p className="mt-1 text-sm text-red-600">{errors.location}</p>}
            </div>
          </div>

          <div className="mt-8 flex justify-end space-x-4">
            <button
              type="button"
              onClick={() => navigate('/inventory')}
              className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              className="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              <Save className="h-4 w-4 mr-2" />
              Save Item
            </button>
          </div>
        </div>
      </form>
    </div>
  );
}