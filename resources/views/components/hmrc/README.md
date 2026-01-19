# HMRC UI Components

Reusable components for HMRC pages with consistent styling and branding.

## Components

### Primary Button (`<x-hmrc.primary-button>`)

The primary action button for HMRC forms and pages. Uses the HMRC teal color scheme.

#### Basic Usage
```blade
<x-hmrc.primary-button type="submit">
    Submit
</x-hmrc.primary-button>
```

#### With Icon
```blade
<x-hmrc.primary-button type="submit" icon="fas fa-check" iconPosition="left">
    Submit Return
</x-hmrc.primary-button>

<x-hmrc.primary-button icon="fas fa-arrow-right" iconPosition="right">
    Next Step
</x-hmrc.primary-button>
```

#### Sizes
```blade
<x-hmrc.primary-button size="sm">Small</x-hmrc.primary-button>
<x-hmrc.primary-button size="md">Medium (Default)</x-hmrc.primary-button>
<x-hmrc.primary-button size="lg">Large</x-hmrc.primary-button>
```

#### Full Width
```blade
<x-hmrc.primary-button fullWidth>
    Full Width Button
</x-hmrc.primary-button>
```

#### Disabled State
```blade
<x-hmrc.primary-button disabled>
    Disabled Button
</x-hmrc.primary-button>
```

#### With Additional Classes
```blade
<x-hmrc.primary-button class="mt-3">
    Button with Margin
</x-hmrc.primary-button>
```

### Secondary Button (`<x-hmrc.secondary-button>`)

The secondary action button for HMRC forms and pages. Uses a neutral color scheme for non-primary actions.

#### Basic Usage
```blade
<x-hmrc.secondary-button type="button">
    Cancel
</x-hmrc.secondary-button>
```

#### With Icon
```blade
<x-hmrc.secondary-button icon="fas fa-arrow-left" iconPosition="left">
    Back
</x-hmrc.secondary-button>
```

#### All Features (Same as Primary Button)
The secondary button supports all the same props as the primary button:
- `size`: sm, md, lg
- `fullWidth`: true/false
- `disabled`: true/false
- `icon`: Font Awesome class
- `iconPosition`: left/right

### Stat Cards (`<x-hmrc.stat-cards>`)

Display statistics cards with colored left borders.

#### Usage
```blade
<x-hmrc.stat-cards :stats="[
    [
        'label' => 'Total Submissions',
        'value' => '45',
        'icon' => 'fas fa-file-alt',
        'color' => 'blue'
    ],
    [
        'label' => 'Pending',
        'value' => '3',
        'icon' => 'fas fa-clock',
        'color' => 'yellow'
    ],
    [
        'label' => 'Completed',
        'value' => '42',
        'icon' => 'fas fa-check-circle',
        'color' => 'green'
    ]
]" />
```

## Component Props

### Button Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `type` | string | 'button' | Button type (button, submit, reset) |
| `size` | string | 'md' | Button size (sm, md, lg) |
| `disabled` | boolean | false | Disabled state |
| `icon` | string | null | Font Awesome icon class |
| `iconPosition` | string | 'left' | Icon position (left, right) |
| `fullWidth` | boolean | false | Full width button |

## Migration Examples

### Before
```blade
<button type="submit" class="btn btn-hmrc-primary">
    <i class="fas fa-check me-2"></i>
    Submit
</button>
```

### After
```blade
<x-hmrc.primary-button type="submit" icon="fas fa-check">
    Submit
</x-hmrc.primary-button>
```

### Before (with custom styling)
```blade
<button type="button" class="btn btn-outline-secondary prev-btn" style="padding: 0.75rem 1.5rem; border-radius: 6px;">
    <i class="fas fa-arrow-left me-2"></i>
    Previous
</button>
```

### After
```blade
<x-hmrc.secondary-button type="button" class="prev-btn" icon="fas fa-arrow-left">
    Previous
</x-hmrc.secondary-button>
```

## Design Guidelines

- **Primary buttons** should be used for main actions (Submit, Save, Continue, etc.)
- **Secondary buttons** should be used for alternative actions (Cancel, Back, Close, etc.)
- Use icons consistently across the application
- Maintain the HMRC teal color scheme (#17848e) for primary actions
- All buttons have a 6px border radius for consistency
- Hover states include subtle elevation effects
- Focus states include visible outlines for accessibility

## Color Palette

- **Primary Teal**: #17848e
- **Primary Teal Hover**: #136770
- **Secondary Gray**: #6c757d
- **Border Gray**: #dee2e6
