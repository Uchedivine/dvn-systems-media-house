<?php

namespace App\Filament\Pages;

use App\Models\Landlord\Studio;
use App\Services\TenantProvisioningService;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class StudioOnboardingWizard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationLabel = 'Onboard New Studio';

    protected static ?string $title = 'Studio Onboarding Wizard';

    protected string $view = 'filament.pages.studio-onboarding-wizard';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->defaultData());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Identity')
                        ->description('Who is this studio?')
                        ->schema([
                            TextInput::make('name')
                                ->label('Studio Name')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $set('slug', Str::slug($state));
                                    $set('subdomain', Str::slug($state, ''));
                                }),
                            TextInput::make('slug')->required(),
                            TextInput::make('subdomain')
                                ->required()
                                ->suffix('.dvnsystems.com.ng'),
                            TextInput::make('owner_name')->required(),
                            TextInput::make('owner_phone')->tel()->required(),
                            TextInput::make('owner_email')->email()->required(),
                        ]),

                    Step::make('Brand Colors')
                        ->description('Their visual identity')
                        ->schema([
                            ColorPicker::make('color_primary')->required(),
                            ColorPicker::make('color_secondary')->required(),
                            ColorPicker::make('color_accent')->required(),
                        ]),

                    Step::make('Typography')
                        ->description('Heading and body fonts')
                        ->schema([
                            Select::make('font_heading')
                                ->label('Heading Font')
                                ->options(self::fontOptions())
                                ->required(),
                            Select::make('font_body')
                                ->label('Body Font')
                                ->options(self::fontOptions())
                                ->required(),
                        ]),

                    Step::make('Contact & Bank')
                        ->description('How clients reach them, and where money goes')
                        ->schema([
                            TextInput::make('phone_primary')->tel()->required(),
                            TextInput::make('whatsapp_number')->tel()->required(),
                            TextInput::make('email')->email(),
                            TextInput::make('instagram_handle'),
                            Textarea::make('address')->required(),
                            TextInput::make('city')->required(),
                            TextInput::make('bank_name')->required(),
                            TextInput::make('account_number')->required(),
                            TextInput::make('account_name')->required(),
                        ]),

                    Step::make('Paystack')
                        ->description("The studio's own keys — money goes directly to them")
                        ->schema([
                            TextInput::make('paystack_public_key'),
                            TextInput::make('paystack_secret_key')->password()->revealable(),
                        ]),

                    Step::make('Plan')
                        ->description('Pricing tier')
                        ->schema([
                            Radio::make('plan')
                                ->options([
                                    'basic' => 'Basic — ₦18,000/month',
                                    'growth' => 'Growth — ₦28,000/month',
                                ])
                                ->inline()
                                ->required(),
                        ]),

                    Step::make('Launch')
                        ->description('Review and go live')
                        ->schema([
                            Placeholder::make('summary')
                                ->label('')
                                ->content(function (callable $get) {
                                    return new HtmlString(
                                        '<strong>' . e($get('name')) . '</strong><br>' .
                                        e($get('subdomain')) . '.dvnsystems.com.ng<br>' .
                                        'Plan: ' . e($get('plan'))
                                    );
                                }),
                        ]),
                ])->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                    <x-filament::button type="submit" size="lg">
                        Launch Studio
                    </x-filament::button>
                BLADE))),
            ])
            ->statePath('data');
    }

    public function launch(): void
    {
        $data = $this->form->getState();

        try {
            $studio = Studio::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'subdomain' => $data['subdomain'],
                'owner_name' => $data['owner_name'],
                'owner_phone' => $data['owner_phone'],
                'owner_email' => $data['owner_email'],
                'plan' => $data['plan'],
            ]);
        } catch (QueryException $e) {
            Notification::make()
                ->title('Could not create studio')
                ->body('The slug, subdomain, or owner email is already in use by another studio.')
                ->danger()
                ->send();

            return;
        }

        $studio->settings()->create([
            'studio_name' => $data['name'],
            'color_primary' => $data['color_primary'],
            'color_secondary' => $data['color_secondary'],
            'color_accent' => $data['color_accent'],
            'font_heading' => $data['font_heading'],
            'font_body' => $data['font_body'],
            'phone_primary' => $data['phone_primary'],
            'whatsapp_number' => $data['whatsapp_number'],
            'email' => $data['email'] ?? null,
            'instagram_handle' => $data['instagram_handle'] ?? null,
            'address' => $data['address'],
            'city' => $data['city'],
            'bank_name' => $data['bank_name'],
            'account_number' => $data['account_number'],
            'account_name' => $data['account_name'],
            'paystack_public_key' => $data['paystack_public_key'] ?? null,
            'paystack_secret_key' => $data['paystack_secret_key'] ?? null,
        ]);

        app(TenantProvisioningService::class)->provision($studio);

        Notification::make()
            ->title('Studio launched')
            ->body("{$studio->name} is live — tenant database provisioned.")
            ->success()
            ->send();

        $this->form->fill($this->defaultData());
    }

    protected function defaultData(): array
    {
        return [
            'plan' => 'basic',
            'color_primary' => '#E50914',
            'color_secondary' => '#1F2833',
            'color_accent' => '#C9A84C',
            'font_heading' => 'Plus Jakarta Sans',
            'font_body' => 'Inter',
        ];
    }

    protected static function fontOptions(): array
    {
        $fonts = [
            'Plus Jakarta Sans', 'Inter', 'Poppins', 'Montserrat',
            'Playfair Display', 'Lora', 'Raleway', 'Work Sans',
            'DM Sans', 'Outfit', 'Cormorant Garamond', 'Bebas Neue',
        ];

        return array_combine($fonts, $fonts);
    }
}