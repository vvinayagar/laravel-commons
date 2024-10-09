<?php

namespace Commons\Http\Resources;

use Commons\Constants\ErrorString;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource as Resource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BaseResource extends Resource
{
    use ResourceMeta;

    /**
     * BaseResource constructor.
     * @param $resource
     */
    public function __construct($resource = null)
    {
        if (is_null($resource)) {
            $resource = collect();
        }

        if (is_array($resource)) {
            $resource = collect($resource);
        }

        parent::__construct($resource);
    }

    /**
     * @param $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        if ($this->statusCode >= 400) {
            $this->message = 'Failure!!';
        }
        return $this;
    }

    /**
     * @param $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    public static function collection($resource)
    {
        return new AnonymousResourceCollection($resource, get_called_class());
    }

    /**
     * @param Model|Builder|mixed $resource
     * @return AnonymousResourceCollection|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     * @throws \HttpRequestException
     */
    public static function paginable($resource)
    {
        $request = app(Request::class);
        /*
         * Adding isset($request->pagination) for KT Datatables
         * author : amit@dlideas.com
         */
        if ($request->has('pagination')) {
            if (isset($request->get('pagination')['page']))
                $request->request->add(['page' => $request->get('pagination')['page']]);
            if (isset($request->get('pagination')['perpage']))
                $request->request->add(['per_page' => $request->get('pagination')['perpage']]);
        }
        if ($resource instanceof LengthAwarePaginator) {
            throw new \HttpRequestException("Don't call ->paginate(). Pass the query instead");
        }

        $paginate = filter_var($request->get('paginate', true), FILTER_VALIDATE_BOOLEAN);
        $page = $request->get('page', 1);
        if ($paginate) {
            $perPage = $request->get('per_page', 15);
            if (method_exists($resource, 'paginate')) {
                $collection = $resource->paginate($perPage);
            } else {
                $resourceAsCollection = $resource instanceof Collection ? $resource : collect($resource);
                $paginator = new LengthAwarePaginator($resourceAsCollection->forPage($page, $perPage), $resourceAsCollection->count(), $perPage, $page);
                $collection = $paginator;
            }
            return self::collection($collection)->additionalMerge([
                'meta' => [
                    'page' => $collection->currentPage(),
                    'perpage' => $collection->perPage(),
                    'pages' => $collection->lastPage()
                ],
            ]);
        }
        if (method_exists($resource, 'get')) {
            return self::create($resource->get());
        }
        return self::create($resource);
    }


    public static function create($resource)
    {
        if ($resource instanceof LengthAwarePaginator) {
            $request = app(Request::class);
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 100);
            $paginator = new LengthAwarePaginator($resource->forPage($page, $perPage), $resource->total(), $perPage, $page);
            return self::collection($resource);
        }
        return new self($resource);
    }

    public static function errors($errors = [], $message = null, $statusCode = 500, $errorString = ErrorString::UNKNOWN_ERROR)
    {
        if (is_null($message)) {
            $message = 'An error has occurred!';
        }
        $instance = new static;


        $errorMeta = [
            'errorMessage' => $message,
            'errorString' => $errorString,
        ];
        $errorsArray = $errors ?? $errorMeta;

        $instance->setStatusCode($statusCode);

        $instance->additionalMerge([
            'meta' => $errorMeta,
            'errors' => $errorsArray
        ]);

        return $instance;
    }

    /**
     * @param \Exception $e
     * @param bool $log
     * @return BaseResource
     */
    public static function exception($e, $log = true)
    {
        if ($log) {
            \Log::error($e->getMessage(), [
                'exception' => $e
            ]);
        }
        return static::errors(
            [
                'exception' => (array)$e
            ],
            'An exception has occurred!',
            500,
            ErrorString::EXCEPTION_ERROR
        );

    }

    public static function validationErrors($errors)
    {
        return static::errors(
            $errors,
            'Validation error',
            422,
            ErrorString::VALIDATION_ERROR
        );
    }

    public static function ok($message = 'Success!', $statusCode = 200)
    {
        $instance = new static;
        $instance->message = $message;
        $instance->statusCode = $statusCode;

        return $instance;
    }
}
