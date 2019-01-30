<?php

namespace Twirp;

use Throwable;

class TwirpError extends \Exception
{
    // Canceled indicates the operation was cancelled (typically by the caller).
    const CANCELED = "canceled";

    // Unknown error. For example when handling errors raised by APIs that do not
    // return enough error information.
    const UNKNOWN = "unknown";

    // InvalidArgument indicates client specified an invalid argument. It
    // indicates arguments that are problematic regardless of the state of the
    // system (i.e. a malformed file name, required argument, number out of range,
    // etc.).
    const INVALID_ARGUMENT = "invalid_argument";

    // DeadlineExceeded means operation expired before completion. For operations
    // that change the state of the system, this error may be returned even if the
    // operation has completed successfully (timeout).
    const DEADLINE_EXCEEDED = "deadline_exceeded";

    // NotFound means some requested entity was not found.
    const NOT_FOUND = "not_found";

    // BadRoute means that the requested URL path wasn't routable to a Twirp
    // service and method. This is returned by the generated server, and usually
    // shouldn't be returned by applications. Instead, applications should use
    // NotFound or Unimplemented.
    const BAD_ROUTE = "bad_route";

    // AlreadyExists means an attempt to create an entity failed because one
    // already exists.
    const ALREADY_EXISTS = "already_exists";

    // PermissionDenied indicates the caller does not have permission to execute
    // the specified operation. It must not be used if the caller cannot be
    // identified (Unauthenticated).
    const PERMISSION_DENIED = "permission_denied";

    // Unauthenticated indicates the request does not have valid authentication
    // credentials for the operation.
    const UNAUTHENTICATED = "unauthenticated";

    // ResourceExhausted indicates some resource has been exhausted, perhaps a
    // per-user quota, or perhaps the entire file system is out of space.
    const RESOURCE_EXHAUSTED = "resource_exhausted";

    // FailedPrecondition indicates operation was rejected because the system is
    // not in a state required for the operation's execution. For example, doing
    // an rmdir operation on a directory that is non-empty, or on a non-directory
    // object, or when having conflicting read-modify-write on the same resource.
    const FAILED_PRECONDITION = "failed_precondition";

    // Aborted indicates the operation was aborted, typically due to a concurrency
    // issue like sequencer check failures, transaction aborts, etc.
    const ABORTED = "aborted";

    // OutOfRange means operation was attempted past the valid range. For example,
    // seeking or reading past end of a paginated collection.
    //
    // Unlike InvalidArgument, this error indicates a problem that may be fixed if
    // the system state changes (i.e. adding more items to the collection).
    //
    // There is a fair bit of overlap between FailedPrecondition and OutOfRange.
    // We recommend using OutOfRange (the more specific error) when it applies so
    // that callers who are iterating through a space can easily look for an
    // OutOfRange error to detect when they are done.
    const OUT_OF_RANGE = "out_of_range";

    // Unimplemented indicates operation is not implemented or not
    // supported/enabled in this service.
    const UNIMPLEMENTED = "unimplemented";

    // Internal errors. When some invariants expected by the underlying system
    // have been broken. In other words, something bad happened in the library or
    // backend service. Do not confuse with HTTP Internal Server Error; an
    // Internal error could also happen on the client code, i.e. when parsing a
    // server response.
    const INTERNAL = "internal";

    // Unavailable indicates the service is currently unavailable. This is a most
    // likely a transient condition and may be corrected by retrying with a
    // backoff.
    const UNAVAILABLE = "unavailable";

    // DataLoss indicates unrecoverable data loss or corruption.
    const DATA_LOSS = "data_loss";

    private static $statusCodeMap = [
        self::CANCELED => 408, // RequestTimeout
        self::UNKNOWN => 500, // Internal Server Error
        self::INVALID_ARGUMENT => 400, // BadRequest
        self::DEADLINE_EXCEEDED => 408, // RequestTimeout
        self::NOT_FOUND => 404, // Not Found
        self::BAD_ROUTE => 404, // Not Found
        self::ALREADY_EXISTS => 409, // Conflict
        self::PERMISSION_DENIED => 403, // Forbidden
        self::UNAUTHENTICATED => 401, // Unauthorized
        self::RESOURCE_EXHAUSTED => 403, // Forbidden
        self::FAILED_PRECONDITION => 412, // Precondition Failed
        self::ABORTED => 409, // Conflict
        self::OUT_OF_RANGE => 400, // Bad Request
        self::UNIMPLEMENTED => 501, // Not Implemented
        self::INTERNAL => 500, // Internal Server Error
        self::UNAVAILABLE => 503, // Service Unavailable
        self::DATA_LOSS => 500, // Internal Server Error
    ];

    private $twirpCode;
    private $meta = [];

    public static function internalError($message)
    {
        return new self(self::INTERNAL, $message);
    }

    public static function internalErrorWith(\Exception $exception)
    {
        $err = new self(self::INTERNAL, $exception->getMessage(), $exception);
        $err->addMeta('cause', get_class($exception));
        return $err;
    }

    public static function notFoundError($message)
    {
        return new self(self::NOT_FOUND, $message);
    }

    public static function invalidArgumentError($argument, $message)
    {
        $err = new self(self::INVALID_ARGUMENT, $argument . ' ' . $message);
        $err->addMeta('argument', $argument);
        return $err;
    }

    public static function requiredArgumentError($argument)
    {
        return self::invalidArgumentError($argument, 'is required');
    }

    public function __construct($code, $message, Throwable $previous = null)
    {
        $this->twirpCode = $code;
        parent::__construct($message, $this->getStatusCode(), $previous);
    }

    public function addMeta($key, $value)
    {
        $this->meta[$key] = $value;
        return $this;
    }

    public function getStatusCode() {
        if (isset(self::$statusCodeMap[$this->twirpCode])) {
            return self::$statusCodeMap[$this->twirpCode];
        }
        return self::UNKNOWN;
    }

    public function toWireFormat()
    {
        return [
            'msg' => $this->getMessage(),
            'code' => $this->twirpCode,
            'meta' => $this->meta,
        ];
    }
}
