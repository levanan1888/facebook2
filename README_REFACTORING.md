# 🏗️ Cấu trúc dự án sau khi tái cấu trúc

## 📋 Tổng quan

Dự án đã được tái cấu trúc để tuân thủ các nguyên tắc SOLID và dễ dàng maintain, mở rộng.

## 🗂️ Cấu trúc thư mục

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── BaseController.php          # Base controller với common methods
│   │   ├── FacebookDashboardController.php
│   │   ├── FacebookSyncController.php
│   │   └── Api/                        # API Controllers
│   ├── Requests/                       # Form Request Validation
│   │   ├── FacebookSyncRequest.php
│   │   └── DashboardFilterRequest.php
│   └── Resources/                      # API Response Resources
│       ├── FacebookAdResource.php
│       ├── FacebookAdAccountResource.php
│       ├── FacebookCampaignResource.php
│       └── FacebookAdSetResource.php
├── Repositories/                       # Data Access Layer
│   ├── FacebookAdRepository.php
│   └── FacebookCampaignRepository.php
├── Services/                           # Business Logic Layer
│   ├── FacebookAdsService.php
│   ├── FacebookAdsSyncService.php
│   ├── FacebookSync/                   # Tách services lớn
│   │   └── BusinessSyncService.php
│   └── ...
└── Providers/
    └── RepositoryServiceProvider.php   # Dependency Injection

routes/
├── web.php                            # Main routes
├── facebook.php                       # Facebook module routes
└── api.php                           # API routes
```

## 🔧 Các cải tiến chính

### 1. **Repository Pattern**
- Tách logic truy vấn database ra khỏi Controllers
- Dễ dàng test và mock
- Tái sử dụng logic truy vấn

```php
// Sử dụng Repository
class FacebookDashboardController extends BaseController
{
    public function __construct(private FacebookAdRepository $adRepository)
    {
    }

    public function overview(Request $request): View
    {
        $data = $this->adRepository->getOverviewData($request->all());
        return view('facebook.dashboard.overview', compact('data'));
    }
}
```

### 2. **Form Request Validation**
- Tách validation logic ra khỏi Controllers
- Tự động authorize và validate
- Custom error messages

```php
class FacebookSyncRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'sync_type' => 'sometimes|string|in:full,incremental,insights_only',
            'account_id' => 'sometimes|string',
            // ...
        ];
    }
}
```

### 3. **API Resources**
- Format response nhất quán
- Tự động transform data
- Conditional relationships

```php
class FacebookAdResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'ad_account' => new FacebookAdAccountResource($this->whenLoaded('adAccount')),
            // ...
        ];
    }
}
```

### 4. **Base Controller**
- Common response methods
- Giảm code duplication
- Consistent API responses

```php
// Trong controller
return $this->successResponse($data, 'Lấy dữ liệu thành công');
return $this->errorResponse('Có lỗi xảy ra', 400);
```

### 5. **Tách Services lớn**
- Chia `FacebookAdsSyncService` (933 lines) thành các services nhỏ
- Mỗi service chỉ làm một việc cụ thể
- Dễ test và maintain

### 6. **Routes Organization**
- Tách routes theo module
- Dễ quản lý và tìm kiếm
- Clear separation of concerns

## 🚀 Cách sử dụng

### 1. **Thêm Repository mới**
```php
// app/Repositories/NewModelRepository.php
class NewModelRepository
{
    public function __construct(private NewModel $model)
    {
    }
    
    // Add methods...
}

// app/Providers/RepositoryServiceProvider.php
$this->app->bind(NewModelRepository::class, function ($app) {
    return new NewModelRepository(new NewModel());
});
```

### 2. **Thêm Request Validation**
```php
// app/Http/Requests/NewRequest.php
class NewRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // validation rules
        ];
    }
}

// Trong Controller
public function store(NewRequest $request)
{
    $validated = $request->validated();
    // ...
}
```

### 3. **Thêm API Resource**
```php
// app/Http/Resources/NewModelResource.php
class NewModelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // transform data
        ];
    }
}
```

## 📝 Quy tắc coding

### 1. **Controllers**
- Chỉ điều phối request → service
- Sử dụng Form Requests cho validation
- Trả về Resources cho API responses
- Kế thừa BaseController

### 2. **Services**
- Chứa business logic chính
- Không chứa logic truy vấn database
- Sử dụng Repositories để lấy dữ liệu
- Mỗi service chỉ làm một việc cụ thể

### 3. **Repositories**
- Chứa logic truy vấn database
- Trả về Eloquent Collections hoặc Models
- Không chứa business logic
- Có thể cache kết quả

### 4. **Models**
- Chỉ khai báo relationships, casts, fillable
- Có thể có scopes và accessors
- Không chứa business logic

### 5. **Requests**
- Validate dữ liệu đầu vào
- Custom error messages
- Authorization logic

### 6. **Resources**
- Format response JSON
- Transform data types
- Conditional relationships

## 🔍 Testing

### 1. **Repository Tests**
```php
class FacebookAdRepositoryTest extends TestCase
{
    public function test_get_overview_data()
    {
        // Test repository methods
    }
}
```

### 2. **Service Tests**
```php
class FacebookAdsServiceTest extends TestCase
{
    public function test_sync_businesses()
    {
        // Mock repository, test business logic
    }
}
```

### 3. **Controller Tests**
```php
class FacebookDashboardControllerTest extends TestCase
{
    public function test_overview_page()
    {
        // Test controller responses
    }
}
```

## 📚 Tài liệu tham khảo

- [Laravel Repository Pattern](https://laravel.com/docs/11.x/eloquent#repository-pattern)
- [Form Request Validation](https://laravel.com/docs/11.x/validation#form-request-validation)
- [API Resources](https://laravel.com/docs/11.x/eloquent-resources)
- [Service Providers](https://laravel.com/docs/11.x/providers)

## 🎯 Lợi ích

1. **Dễ maintain**: Code được tổ chức rõ ràng, dễ tìm và sửa
2. **Dễ test**: Mỗi layer có thể test độc lập
3. **Dễ mở rộng**: Thêm tính năng mới không ảnh hưởng code cũ
4. **Dễ đọc**: Người mới có thể hiểu cấu trúc nhanh chóng
5. **Tuân thủ SOLID**: Single Responsibility, Open/Closed, Dependency Inversion
6. **Performance**: Có thể cache và optimize từng layer
