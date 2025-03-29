<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\DB;

class CreateOrderCustom extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';
    
    protected static string $resource = OrderResource::class;
    
    protected static ?string $navigationLabel = 'Crear Orden';
    
    protected static ?string $title = 'Crear Nueva Orden';
    
    protected static ?string $slug = 'orders/create-custom';
    
    protected ?string $heading = 'Crear Nueva Orden';
    
    protected ?string $subheading = 'Complete los detalles para crear una nueva orden';
    
    // State para el formulario completo
    public $data = [
        'user_id' => null,
        'order_status' => null,
        'products' => [],
        'payments' => [],
    ];
    
    public $orderTotal = 0;
    public $paidAmount = 0;
    public $balanceDue = 0;
    
    public function mount()
    {
        $this->form->fill([
            'user_id' => null,
            'order_status' => OrderStatusEnum::PENDING->value,
            'products' => [],
            'payments' => [],
        ]);
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()
                    ->schema([
                        // Sección de información del cliente (2/3 del ancho)
                        Section::make('Información del Cliente')
                            ->schema([
                                Select::make('user_id')
                                    ->label('Cliente')
                                    ->relationship('user', 'name')
                                    ->searchable(['name', 'email'])
                                    ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->name} ({$record->email})")
                                    ->preload()
                                    ->native(false)
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label('Nombre')
                                            ->required(),
                                        TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->required()
                                            ->unique(),
                                        TextInput::make('password')
                                            ->label('Contraseña')
                                            ->password()
                                            ->required()
                                            ->minLength(8),
                                    ])
                                    ->createOptionAction(
                                        fn (Action $action) => $action
                                            ->modalHeading('Crear nuevo cliente')
                                            ->modalWidth('md')
                                    ),
                            ])
                            ->columnSpan(['lg' => 2]),
                            
                        // Sección de estado de la orden (1/3 del ancho)
                        Section::make('Estado de la Orden')
                            ->schema([
                                Select::make('order_status')
                                    ->label('Estado')
                                    ->options(OrderStatusEnum::class)
                                    ->default(OrderStatusEnum::PENDING)
                                    ->required(),
                                    
                                Placeholder::make('resumen_totales')
                                    ->label('Resumen')
                                    ->content(function ($get, $set) {
                                        // Calcular los totales basados en los productos y pagos actuales
                                        $this->calculateTotals($get, $set);
                                        
                                        return view('components.order-summary', [
                                            'orderTotal' => $this->orderTotal,
                                            'paidAmount' => $this->paidAmount,
                                            'balanceDue' => $this->balanceDue,
                                        ]);
                                    }),
                            ])
                            ->columnSpan(['lg' => 1]),
                    ])
                    ->columns(['lg' => 3]),
                    
                // Sección de productos
                Section::make('Productos')
                    ->schema([
                        Repeater::make('products')
                            ->label('')
                            ->schema([
                                Grid::make(12)
                                    ->schema([
                                        Select::make('product_id')
                                            ->label('Producto')
                                            ->searchable()
                                            ->options(function () {
                                                return Product::query()
                                                    ->where('is_active', true)
                                                    ->where('stock_quantity', '>', 0)
                                                    ->get()
                                                    ->pluck('name', 'id');
                                            })
                                            ->getSearchResultsUsing(function (string $search) {
                                                return Product::query()
                                                    ->where('is_active', true)
                                                    ->where('stock_quantity', '>', 0)
                                                    ->where('name', 'like', "%{$search}%")
                                                    ->orWhere('sku', 'like', "%{$search}%")
                                                    ->limit(50)
                                                    ->get()
                                                    ->mapWithKeys(fn (Product $product) => [
                                                        $product->id => $product->name . ' (' . $product->sku . ') - $' . number_format($product->getEffectivePrice(), 2),
                                                    ]);
                                            })
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                if (!$state) return;
                                                
                                                $product = Product::find($state);
                                                if (!$product) return;
                                                
                                                $price = $product->getEffectivePrice();
                                                $set('price', $price);
                                                $set('stock_available', $product->stock_quantity);
                                                
                                                // Actualizar subtotal
                                                $quantity = $get('quantity') ?: 1;
                                                $set('subtotal', $price * $quantity);
                                                
                                                // Re-calcular el total de la orden
                                                $this->calculateTotals($get, $set);
                                            })
                                            ->columnSpan([
                                                'md' => 6,
                                            ]),
                                            
                                        TextInput::make('quantity')
                                            ->label('Cantidad')
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                $price = $get('price') ?: 0;
                                                $set('subtotal', $price * $state);
                                                
                                                // Re-calcular el total de la orden
                                                $this->calculateTotals($get, $set);
                                            })
                                            ->columnSpan([
                                                'md' => 2,
                                            ]),
                                            
                                        TextInput::make('price')
                                            ->label('Precio')
                                            ->numeric()
                                            ->required()
                                            ->prefix('$')
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                $quantity = $get('quantity') ?: 1;
                                                $set('subtotal', $state * $quantity);
                                                
                                                // Re-calcular el total de la orden
                                                $this->calculateTotals($get, $set);
                                            })
                                            ->columnSpan([
                                                'md' => 2,
                                            ]),
                                            
                                        TextInput::make('subtotal')
                                            ->label('Subtotal')
                                            ->numeric()
                                            ->prefix('$')
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpan([
                                                'md' => 2,
                                            ]),
                                            
                                        // Campo oculto para el stock disponible
                                        TextInput::make('stock_available')
                                            ->hidden(),
                                    ]),
                            ])
                            ->minItems(1)
                            ->defaultItems(1)
                            ->columns(1)
                            ->addActionLabel('Agregar Producto')
                            ->reorderable(false)
                            ->cloneable(false)
                            ->columnSpanFull(),
                    ]),
                    
                // Sección de pagos
                Section::make('Pagos')
                    ->schema([
                        Repeater::make('payments')
                            ->label('')
                            ->schema([
                                Grid::make(12)
                                    ->schema([
                                        TextInput::make('amount')
                                            ->label('Monto')
                                            ->required()
                                            ->numeric()
                                            ->minValue(0.01)
                                            ->prefix('$')
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                // Re-calcular el total pagado
                                                $this->calculateTotals($get, $set);
                                            })
                                            ->columnSpan([
                                                'md' => 4,
                                            ]),
                                            
                                        Select::make('payment_method')
                                            ->label('Método de Pago')
                                            ->options(PaymentMethodEnum::class)
                                            ->required()
                                            ->columnSpan([
                                                'md' => 4,
                                            ]),
                                            
                                        Select::make('payment_status')
                                            ->label('Estado')
                                            ->options(PaymentStatusEnum::class)
                                            ->default(PaymentStatusEnum::PAID)
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                // Re-calcular el total pagado si cambia el estado
                                                $this->calculateTotals($get, $set);
                                            })
                                            ->columnSpan([
                                                'md' => 4,
                                            ]),
                                    ]),
                            ])
                            ->minItems(0)
                            ->defaultItems(0)
                            ->columns(1)
                            ->addActionLabel('Agregar Pago')
                            ->reorderable(false)
                            ->cloneable(false)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }
    
    // Método para calcular totales
    protected function calculateTotals(Get $get, Set $set): void
    {
        // Calcular total de la orden
        $products = $get('products') ?: [];
        $this->orderTotal = collect($products)->sum(function ($product) {
            return ($product['subtotal'] ?? 0);
        });
        
        // Calcular total pagado
        $payments = $get('payments') ?: [];
        $this->paidAmount = collect($payments)
            ->filter(function ($payment) {
                return ($payment['payment_status'] ?? null) === PaymentStatusEnum::PAID->value;
            })
            ->sum('amount');
            
        // Calcular balance pendiente
        $this->balanceDue = max(0, $this->orderTotal - $this->paidAmount);
    }
    
    public function create()
    {
        $this->validate();
        
        try {
            DB::beginTransaction();
            
            // Crear la orden
            $order = Order::create([
                'user_id' => $this->data['user_id'],
                'order_status' => $this->data['order_status'],
                'total' => $this->orderTotal,
            ]);
            
            // Crear los detalles de la orden
            foreach ($this->data['products'] as $productData) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $productData['product_id'],
                    'quantity' => $productData['quantity'],
                    'price' => $productData['price'],
                    'subtotal' => $productData['subtotal'],
                ]);
                
                // Actualizar el stock del producto
                $product = Product::find($productData['product_id']);
                if ($product) {
                    $product->update([
                        'stock_quantity' => $product->stock_quantity - $productData['quantity']
                    ]);
                }
            }
            
            // Crear los pagos
            foreach ($this->data['payments'] as $paymentData) {
                Payment::create([
                    'order_id' => $order->id,
                    'amount' => $paymentData['amount'],
                    'payment_status' => $paymentData['payment_status'],
                    'payment_method' => $paymentData['payment_method'],
                ]);
            }
            
            // Actualizar estado de pago
            $order->updatePaymentStatus();
            
            DB::commit();
            
            Notification::make()
                ->title('Orden creada con éxito')
                ->success()
                ->send();
                
            return redirect()->to(OrderResource::getUrl('edit', ['record' => $order]));
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Notification::make()
                ->title('Error al crear la orden')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Crear Orden')
                ->submit('create'),
                
            Action::make('cancel')
                ->label('Cancelar')
                ->url(OrderResource::getUrl())
                ->color('gray'),
        ];
    }
    
    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::SevenExtraLarge;
    }
}
