# twirfony: twirp for Symfony

Twirfony is made up of two parts, codegen and a runtime Symfony bundle.

## Code generation

Code generation is written as a protoc plugin in golang, because the go tooling for proto is fantastic.

To generate the twirp interfaces
```bash
go install ./protoc-gen-twirp_php
protoc --twirp_php_out src --php_out src haberdasher.proto
```

:bangbang: At 99designs you should use `99dev twirp generate {app}` instead, and read the docs over at https://github.com/99designs/twirpgen


## Runtime

The runtime component is a Symfony bundle that allows you to mount classes implementing the generated twirp service interfaces directly into your router.

1. Add a twirfony dependency
```bash
composer require 99designs/twirfony
```
2. Register the bundle
```php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        return [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new AppBundle\AppBundle(),
            new Twirfony\TwirfonyBundle\TwirfonyBundle(), // add this line
        ];
    }
```

3. Register the router in routing.yml

```yaml
twirp_api:
    resource: 'twirp.service_registry::loadRoutes'
    type: service
    prefix: /twirp
```

4. Create implement your twirp service
```php
namespace AppBundle\Service;

use AppBundle\Twirp\HaberdasherInterface;
use AppBundle\Twirp\Hat;
use AppBundle\Twirp\Size;
use Twirfony\TwirpService;

class HaberdasherService implements TwirpService, HaberdasherInterface
{
    public function makeHat(Size $size): Hat
    {
        return (new Hat)
            ->setInches($size->getInches())
            ->setColor("blue")
            ->setName("Fedora");
    }
}
```

5. Register and tag your service
```yaml
    haberdasher_service:
        class: AppBundle\Service\HaberdasherService
        tags: ['twirp.service']
```
