# Zepekegno ObfuscateBundle Documentation
## 1. Introduction
The **Zepekegno ObfuscateBundle** provides the ability to obfuscate and deobfuscate IDs in your Symfony application. It supports obfuscating IDs in both URLs and Twig templates, and it allows you to use **Symfony Attributes** to automatically resolve obfuscated IDs to actual entities in your controllers.

## 2. Installation
### Step 1 : Install the bundle via Composer
You can install the bundle using Composer by running:
```shell
composer require zepekegno/obfuscate-id-bundle
```
### Step 2 : Register the bundle
Symfony 7 should automatically register the bundle, but if it doesn’t, manually add it to your ```config/bundles.php```:

```php
// config/bundles.php
return [
    // Other bundles...
    Zepekegno\ObfuscateIdBundle\ObfuscateIdBundle::class => ['all' => true],
];
```
### Step 3 : Configure the secret key
In your ```.env``` file, add a secret key that will be used to obfuscate and deobfuscate IDs:
```dotenv
# .env
OBFUSCATE_SECRET_KEY=your_secret_key_here
```
This key will be used in the obfuscation algorithm. The secret key must be 32 bytes long for AES-256 encryption

## 3. Usage with Symfony Attributes
Using ```ObfuscateId``` in a controller
The bundle provides a custom attribute ```ObfuscateId``` that you can use to automatically resolve obfuscated IDs in your controller methods.

Below are examples of how to use this attribute in different scenarios.
### Example 1: Basic ID Obfuscation
In this example, the ``ObfuscateId`` attribute is used with the id route parameter, which is obfuscated in the URL and resolved to the Post entity in the controller.

````php
// src/Controller/PostController.php

namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zepekegno\ObfuscateBundle\Attribute\ObfuscateId;

class PostController extends AbstractController
{
    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(
        #[ObfuscateId] Post $post
    ): Response {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }
}
````
#### Explanation:
- ```#[ObfuscateId]``` without any parameters tells Symfony to use the default route parameter (id in this case).
- ``The ValueResolver`` automatically deobfuscates the id parameter and resolves it to a Post entity.
- The resulting URL might look like ``/post/abc123`` instead of ``/post/123``.

### Example 2: Custom Route Parameter
In this example, the ObfuscateId attribute specifies a custom route parameter, ``post``, instead of the default id.
````php
// src/Controller/PostController.php

namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zepekegno\ObfuscateBundle\Attribute\ObfuscateId;

class PostController extends AbstractController
{
    #[Route('/{post}', name: 'app_post_show', methods: ['GET'])]
    public function show(
        #[ObfuscateId(routeParam: 'post')] Post $post
    ): Response {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }
}
````
#### Explanation:
- ``#[ObfuscateId(routeParam: 'post')]`` specifies that the route parameter post should be used.
- The ``ValueResolver`` will use the ``post`` parameter in the URL, obfuscate it, and resolve it as a Post entity.
- The URL will be ``/post/abc123`` instead of ``/post/123``, where ``abc123`` is the obfuscated ID.
### Example 3: Custom Identifier Field
In this example, the ``ObfuscateId`` attribute uses a custom identifier field called ``post``.
```php
// src/Controller/PostController.php

namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zepekegno\ObfuscateBundle\Attribute\ObfuscateId;

class PostController extends AbstractController
{
    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(
        #[ObfuscateId(identifierField: 'post')] Post $post
    ): Response {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }
}
```
#### Explanation
- ``#[ObfuscateId(identifierField: 'post')]`` specifies that the ID field in the Post entity should be called post.
- This is useful if your database uses a different primary key field name than ``id``.
- The URL will look like ``/post/abc123`` instead of ``/post/123``, where ``abc123`` is the obfuscated ID.
### Example 4: 
In this example, we use the ``ObfuscateId`` attribute the index method  specifying both a custom route parameter ``(post)`` and the ``Post`` entity type.
```php
// src/Controller/PostController.php

namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zepekegno\ObfuscateBundle\Attribute\ObfuscateId;

class PostController extends AbstractController
{
    #[Route('/{post}', name: 'app_post_show', methods: ['GET'])]
    public function show(
        #[ObfuscateId(routeParam: 'post', entity: Post::class)] $post,
    ): Response {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }
}
```
#### Explanation
- ``#[ObfuscateId(routeParam: 'post', entity: Post::class)]``:
    - ``routeParam: 'post'`` specifies the route parameter used in the URL.
    - ``entity: Post::class`` tells Symfony to resolve the obfuscated ID as a Post entity.
- The URL will be ``/post/abc123`` instead of ``/post/123``, where ``abc123`` is the obfuscated ID.
### Example 5: Custom Route Parameter
In this example, we specify the Post entity in the ObfuscateId attribute so Symfony knows to resolve the ID as a Post entity.
````php
// src/Controller/PostController.php

namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zepekegno\ObfuscateBundle\Attribute\ObfuscateId;

class PostController extends AbstractController
{
    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(
        #[ObfuscateId(enity:Post::class)] $post
    ): Response {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }
}
````
#### Explanation
- ```#[ObfuscateId(entity: Post::class)]``` specifies that the id route parameter should be deobfuscated and resolved as a Post entity.
- The URL ```/post/abc123``` will be mapped to the actual Post entity with ID ``123`` after deobfuscation.
- This configuration is useful when you want to define the entity type explicitly.

### 4. Usage in Twig Templates
In addition to using obfuscated IDs in routes, you can also obfuscate IDs in Twig templates using the provided filters.
#### Available Twig Filters
- ```obfuscate``` : To obfuscate an ID.
- ```deobfuscate``` : To deobfuscate an obfuscated ID.
##### Example Twig Template
```html
{# templates/post/show.html.twig #}

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Post Details</title>
</head>
<body>
    <h1>{{ post.title }}</h1>

    {# Generate a link with an obfuscated ID #}
    <a href="{{ path('app_post_show', { 'id': post.id|offusquer }) }}">View Post</a>

    {# Display obfuscated and deobfuscated IDs #}
    <p>Obfuscated ID: {{ post.id|obfuscate }}</p>
    <p>Deobfuscated ID: {{ 'YWJjMTIz'|deobfuscate }}</p>
</body>
</html>
```
### Generating URLs with Obfuscated IDs
To generate a URL that contains an obfuscated ID:
```twig
<a href="{{ path('app_post_show', { 'id': post.id|obfuscate }) }}">View Post</a>
```
In this example, post.id|offusquer will obfuscate the post’s ID and pass it into the id parameter of the path() function.
### 6. Customization
You can customize the obfuscation algorithm by using your owner obfuscate service.
Here how you can customize the obfuscation algorithm
```php
//src/service/CustomObfuscate.php
class CustomObfuscateService implements ObfuscateInterface{
  public function obfuscate(int $value):string{
   // Do something here
  }
  public function deobfuscate(string $value):?int{
   // Do something here
  }
}
```
```yaml
#config/packages/obfuscate_id
obfuscate_id:
  obfuscate_secret_key: "30e5d8b844cdace13dfb87e0ffbe9512"
  obfuscateIdInterface: "@App\Service\CustomObfuscateService"
```
### 7. Conclusion
The **Zepekegno ObfuscateBundle** simplifies the process of hiding sensitive IDs in URLs using obfuscation techniques. It integrates seamlessly with Symfony’s routing system and Twig templating engine, allowing you to easily obfuscate and deobfuscate IDs both in the backend and frontend.
With support for **Symfony Attributes**, you can resolve obfuscated IDs into entities directly in your controller methods, making your code cleaner and more secure.
