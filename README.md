# LaravelCommons

## JSON Response formatting

### Single Resource Response

For resource that doesnt require additional transformation, you may use the `BaseResource` like below

```php
<?php

use Commons\Http\Resources\BaseResource;
use App\User;

class UserController
{
    public function show(User $user) {
        return new BaseResource($user);
    }
}
```

For Resource that require transformation you may create a resource and extend `BaseResource`

```php
<?php

use Commons\Http\Resources\BaseResource;
use App\User;

class UserResource extends BaseResource
{
    public function toArray($request) {
        return [
            'id' => $this->id,
            // ...
        ];
    }
}

class UserController
{
    public function show(User $user) {
        return new UserResource($user);
    }
}
```

### Multiple resources Response

For a collection, you paginate the response like below

```php

<?php

use Commons\Http\Resources\BaseResource;
use App\User;

class UserController
{
    public function index() {
        return BaseResource::collection(User::paginate());
    }
}
```

Please be noted that the example above `collection` function can accept Collection and AbstractPaginator instance, but 
it is recommended to always return multiple resource with pagination.

### Message Response

At times, you might want to response a message. This is particularly useful to prompt a message after a resource is 
deleted

```php
<?php

use Commons\Http\Resources\BaseResource;
use App\User;

class UserController
{
    public function destroy(User $user) {
        $user->delete();
        
        return BaseResource::ok('User deleted');
    }
}
```

### Error Response

For custom error response you may use the `errors` function  

```php
<?php

use Commons\Http\Resources\BaseResource;

class UserController
{
    public function index() {
        $errorMessage = 'Something bad happened';
        
        // this contain additional information like hints
        $errors = [
            'hint' => 'watch out'
        ];
        
        $statusCode = 500;
        
        return BaseResource::errors($errorMessage, $errors, $statusCode);
    }
}

```
### Formatting Exceptions Handler 

Laravel by default handle and format Exception in `app\Exceptions\handler.php` file. To override the default formatting
functions, you can apply `Traits\HandlerTransformer.php` trait in the handler file like below:

For validation response and uncaught exceptions, 

```php
<?php

namespace App\Exceptions;

use Commons\Traits\HandlerTransformer;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    use HandlerTransformer;
    
    // ...
}
```

The trait above will format ValidationException and uncaught Exception in TriSquare format.

## StoreFile trait

To handle your everyday file upload, you can apply `Traits\StoreFile` trait to your controller. The trait will provide
putFile and deleteFileIfExists function. 

```php
<?php

use Commons\Traits\StoreFile;
use App\User;
use Illuminate\Http\Request;

class UserController
{
    use StoreFile;
    
    public function uploadProfilePicture(Request $request, User $user) {
        if ($request->hasFile('profile_picture')) {
            $this->deleteFileIfExists($user->profile_picture_url); // in case you want to discard the previous file
            $user->profile_picture_url = $this->putFile($request->file('profile_picture')); // putFile function will return the url to access the file
        }
        $user->save();
    }
}
```

## Lowercase email mutator trait

You may want to mutate your email input before saving into database. This steps is important as email is not case-sensitive
and when the user try to login, you can also lowercase the user's email input to match the value you store in database.
To do so, you only have to apply `Traits\LowercaseEmailMutator` in your eloquent model

```php
<?php

namespace App;

use Commons\Traits\LowercaseEmailMutator;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use LowercaseEmailMutator;
}
```

## Lowercase input middleware

As explained above, a user may enter uppercase email during login. To match with the email value in database, you can
lowercase input(s) using `Http\Middleware\LowercaseInput`. First register the middleware in your `Http/Kernel.php`

```php
<?php
    protected $routeMiddleware = [
        // ...
        'lowercase' => \Commons\Http\Middleware\LowercaseInput::class,
    ];
```

