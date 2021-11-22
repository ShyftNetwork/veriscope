<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

use Opis\JsonSchema\{ Validator, JsonPointer, ValidationResult };
use Opis\JsonSchema\Errors\{ ErrorFormatter, ValidationError };


class JSONSchema
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $type
     * @return mixed
     */
    public function handle($request, Closure $next, $type = 'complete')
    {

      try {

        // Create a new validator
        $validator = new Validator();

        // Register a schema
        $validator->resolver()->registerFile('file://complete-ivms101-v1.json',storage_path('schemas/complete-ivms101-v1.json'));
        $validator->resolver()->registerFile('file://beneficiary-ivms101-v1.json',storage_path('schemas/beneficiary-ivms101-v1.json'));
        $validator->resolver()->registerFile('file://originator-ivms101-v1.json',storage_path('schemas/originator-ivms101-v1.json'));

        // Our data
        $data =  json_decode($request->getContent(), true);

        switch ($type) {

          case 'beneficiary':
          $result = $validator->validate($data,'file://beneficiary-ivms101-v1.json');
          break;

          case 'originator':
          $result = $validator->validate($data,'file://originator-ivms101-v1.json');
          break;

          default:
          $result = $validator->validate($data,'file://complete-ivms101-v1.json');
          break;
        }



        if (!$result->isValid()) {

            // Get the error
            $error = $result->error();

            // Create an error formatter
            $formatter = new ErrorFormatter();

            $custom = function (ValidationError $error) use ($formatter) {
                $schema = $error->schema()->info();

                return [
                    'schema' => [
                        'id' => $schema->id(),
                        'base' => $schema->base(),
                        'root' => $schema->root(),
                        'draft' => $schema->draft(),
                        'path' => JsonPointer::pathToFragment($schema->path()),
                        'contents' => $schema->data(),
                        // see Opis\JsonSchema\Info\SchemaInfo for more properties
                    ],
                    'error' => [
                        'keyword' => $error->keyword(),
                        'args' => $error->args(),
                        'message' => $error->message(),
                        'formattedMessage' => $formatter->formatErrorMessage($error),
                    ],
                    'data' => [
                        'type' => $error->data()->type(),
                        'value' => $error->data()->value(),
                        'fullPath' => $error->data()->fullPath(),
                        // see Opis\JsonSchema\Info\DataInfo for more properties
                    ],
                ];
            };

            $custom_key = function (ValidationError $error): string {
                return implode('.', $error->data()->fullPath());
            };



            return response()->json(['message' => 'JSON Schema Validation Error','fields' => $formatter->format($error, true, $custom, $custom_key)  ,'code' => 'validation_failed'], 400, array(), JSON_PRETTY_PRINT);

        }


      } catch (\Exception $e) {

        return response()->json(['message' => 'Invalid JSON','code' => 'invalid_json'], 400, array(), JSON_PRETTY_PRINT);

      }


      return $next($request);


    }
}
