# ObfuscateIdBundle

## Introduction

ObfuscateIdBundle is a Symfony extension that enables obfuscation of identifiers in URLs and API responses.
This new version introduces several improvements, including:

- **Automatic obfuscation of IDs when loading fixtures with Doctrine**
- **Support for dynamic properties** via the `#[ObfuscateId]` annotation
- **Improved Twig filters**
- **Persistent default key generation** when no key is provided
- **Preserved compatibility with both entity-based and raw ID controller actions**

## Installation

Install the bundle via Composer:

```sh
composer require zepekegno/obfuscate-id-bundle
```

Then, activate it in `bundles.php` if not automatically enabled:

```php
return [
    Zepekegno\ObfuscateIdBundle\ObfuscateIdBundle::class => ['all' => true],
];
```

## Configuration

Add the following configuration in `config/packages/obfuscate_id.yaml`:

```yaml
obfuscate_id:
    secret_key: '%env(OBFUSCATE_ID_SECRET)%'
```

Define the environment variables in `.env`:

```ini
OBFUSCATE_ID_SECRET="your_secret_key"
```

If `OBFUSCATE_ID_SECRET` is not defined, the bundle will automatically generate a **persistent default secret key** at installation time. This ensures a consistent encryption key across application restarts and deployments.

The persistent IV will also be generated.

## Usage

### 1. **Obfuscation in Controllers**

Obfuscation is now automatic, meaning you no longer need to manually annotate route parameters. The bundle will automatically deobfuscate IDs in controller actions:

```php
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route('/user/{id}', name: 'user_show')]
public function show(int $id): Response
{
    return new Response("Deobfuscated ID: " . $id);
}
```

For entity-based routes, it automatically resolves the entity:

```php
#[Route('/user/{id}', name: 'user_show')]
public function show(User $user): Response
{
    return new Response("User: " . $user->getId());
}
```

You may also use the annotation explicitly to control deobfuscation:

```php
use Zepekegno\ObfuscateIdBundle\ValueResolver\Attribute\ObfuscateId;

#[Route('/user/{id}', name: 'user_show')]
public function show(#[ObfuscateId(entity: User::class)] User $user): Response
{
    return new Response("User: " . $user->getId());
}
```

### 2. **Automatic Obfuscation of IDs in Doctrine**

When an entity is loaded, its ID is automatically obfuscated.
Add the `#[Obfuscate]` annotation to the relevant property:

```php
use Zepekegno\ObfuscateIdBundle\Attribute\Obfuscate;

#[ORM\Entity]
class User
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    #[Obfuscate] // This property will be automatically obfuscated
    private ?int $id = null;
}
```

### 3. **Using Obfuscation in Twig**

Use the `obfuscate` filter to mask an identifier in a template:

```twig
<a href="{{ path('user_show', { id: user.id|obfuscate }) }}">View User</a>
```

## Doctrine Events

The bundle listens for the following events:

- **postLoad** → Automatically applies obfuscation to loaded entities.

## Development

Install dependencies:

```
## Contributing

1. Fork the repository
2. Clone your fork
3. Create your feature branch: `git checkout -b feature/YourFeature`
4. Commit your changes: `git commit -am 'Add new feature'`
5. Push to the branch: `git push origin feature/YourFeature`
6. Create a new Pull Request

### Guidelines

- Ensure test coverage for your changes.
- Follow PSR coding standards.
- Prefer small, focused pull requests.

## Support

If you encounter any issues, open an issue on [GitHub](https://github.com/zepekegno224/ObfuscateIdBundle/issues).

