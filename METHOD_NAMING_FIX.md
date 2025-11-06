# Method Naming Conflict Fix

## ❌ Problem

**Error:**
```
Fatal error: Declaration of App\Model\Product::update(int $id, string $name, ...)
must be compatible with App\Model\Model::update(array $data, array $conditions): int
```

## 🔍 Root Cause

The `Product` model had methods named `create()`, `update()`, and `delete()` that conflicted with the base `Model` class methods:

**Base Model Methods:**
```php
abstract class Model {
    public function insert(array $data): int          // Insert record
    public function update(array $data, array $conditions): int  // Update records
    public function delete(array $conditions): int    // Delete records

    // These are the base CRUD methods
}
```

**Product Model (BEFORE - ❌ WRONG):**
```php
class Product extends Model {
    public function create(...): int|false     // ❌ Conflicts with parent pattern
    public function update(...): bool          // ❌ Conflicts with parent update()
    public function delete(int $id): bool      // ❌ Conflicts with parent delete()
}
```

**Issue:** PHP doesn't allow child classes to override parent methods with incompatible signatures.

---

## ✅ Solution

Renamed the Product model methods to be more specific and avoid conflicts:

### Product Model (AFTER - ✅ CORRECT):

```php
class Product extends Model {
    // Renamed to avoid conflicts
    public function createProduct(string $name, string $description, float $pricePerGram, int $stock): int|false
    public function updateProduct(int $id, string $name, string $description, float $pricePerGram, int $stock): bool
    public function deleteProduct(int $id): bool

    // These methods use the parent methods internally:
    // - createProduct() → calls $this->insert()
    // - updateProduct() → calls $this->updateById()
    // - deleteProduct() → calls $this->deleteById()
}
```

### Controller Updated:

```php
class ProductController extends Controller {
    // BEFORE (❌ WRONG):
    $this->productModel->create($name, $description, $price, $stock);
    $this->productModel->update($id, $name, $description, $price, $stock);
    $this->productModel->delete($id);

    // AFTER (✅ CORRECT):
    $this->productModel->createProduct($name, $description, $price, $stock);
    $this->productModel->updateProduct($id, $name, $description, $price, $stock);
    $this->productModel->deleteProduct($id);
}
```

---

## 📋 Files Changed

### 1. `app/model/product.php`
- `create()` → `createProduct()`
- `update()` → `updateProduct()`
- `delete()` → `deleteProduct()`

### 2. `app/controller/ProductController.php`
- Line 166: `create()` → `createProduct()`
- Line 257: `update()` → `updateProduct()`
- Line 290: `delete()` → `deleteProduct()`

---

## 🎓 Best Practice: Method Naming Convention

### ✅ Recommended Pattern for Models:

```php
class YourModel extends Model {
    // Specific business logic methods with descriptive names
    public function createModelName(...): int|false
    public function updateModelName(...): bool
    public function deleteModelName(int $id): bool

    // Other business methods
    public function findByName(string $name): ?array
    public function getActive(): array
    public function archive(int $id): bool
}
```

### ❌ Avoid Generic Names:

```php
class YourModel extends Model {
    // ❌ DON'T use these - they conflict with parent
    public function create(...)
    public function update(...)
    public function delete(...)
    public function insert(...)
}
```

### ✅ Use Base Model Methods Directly:

When you need simple CRUD, use the inherited methods:

```php
// In Controller:
$model = new Product();

// Create using base method
$id = $model->insert([
    'name' => $name,
    'description' => $description,
    'price_per_gram' => $price,
    'stock' => $stock
]);

// Update using base method
$affected = $model->updateById($id, [
    'name' => $newName,
    'stock' => $newStock
]);

// Delete using base method
$affected = $model->deleteById($id);
```

**OR** create specific methods:

```php
// In Model:
public function createProduct(string $name, ...): int|false {
    return $this->insert([...]);
}

// In Controller:
$id = $model->createProduct($name, $description, $price, $stock);
```

---

## 🔄 Pattern for Other Models

Apply this same pattern to other models to prevent conflicts:

### User Model:
```php
class User extends Model {
    public function createUser(...): int|false     // ✅ Good
    public function updateUser(...): bool          // ✅ Good
    public function deleteUser(int $id): bool      // ✅ Good
}
```

### Role Model:
```php
class Role extends Model {
    public function createRole(...): int|false     // ✅ Good
    public function updateRole(...): bool          // ✅ Good
    public function deleteRole(int $id): bool      // ✅ Good
}
```

---

## 📊 Summary

| Issue | Solution | Status |
|-------|----------|--------|
| Method signature conflict | Renamed methods | ✅ Fixed |
| `create()` conflict | `createProduct()` | ✅ Fixed |
| `update()` conflict | `updateProduct()` | ✅ Fixed |
| `delete()` conflict | `deleteProduct()` | ✅ Fixed |
| Controller calls updated | All 3 locations | ✅ Fixed |

---

## ✨ Result

**BEFORE:** Fatal error when visiting `/products`

**AFTER:** ✅ Page loads successfully with sample data

**Method calls are now:**
- More descriptive (`createProduct` vs generic `create`)
- No conflicts with parent class
- Following PHP inheritance rules
- Consistent with best practices

---

## 🚀 Testing

To verify the fix works:

1. Visit `/products` - Should load without errors ✅
2. Click "Add New Product" - Form should appear ✅
3. Submit form - Should call `createProduct()` ✅
4. Edit a product - Should call `updateProduct()` ✅
5. Delete a product - Should call `deleteProduct()` ✅

All operations now work correctly! 🎉
