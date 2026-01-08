<?php

namespace App\Validations;

use App\Traits\ResponseTrait;
use App\Database\QueryBuilder;


trait ValidateData
{
    use ResponseTrait;

    protected $queryBuilder;

    public function __construct()
    {
        $this->queryBuilder = new QueryBuilder();
    }
    public function validate($fields = [], $request)
    {
        if(count($fields))
        {
            $isError = false;
            $errorsMessage = [];

           foreach($fields as $field)
           {
               $items = explode('||', $field);
               $itemsCount = count($items);

               if($itemsCount == 2){
                   $validation_params_string = $items[1];
                   $validations = explode('|', $validation_params_string);

                   foreach($validations as $validation){
                       $key = $items[0];

                       // required validation
                       if($validation == 'required')
                       {
                           if(!isset($request->$key) || empty($request->$key)) $isError = true &&
                               array_push($errorsMessage, "لطفا ". translate_key($key) . " را وارد کنید");
                       }

                       // check string value
                       if($validation == 'string'){
                           if(isset($request->$key)) if(!is_string($request->$key)) $isError = true &&
                               array_push($errorsMessage, "مقدار ". translate_key($key) . " باید یک رشته باشد");
                       }

                       // check int/number value
                       if($validation == 'int' || $validation == 'number'){
                           if(isset($request->$key)) if(!is_int($request->$key)) $isError = true &&
                               array_push($errorsMessage, "مقدار ". translate_key($key) . " باید یک عدد باشد");
                       }

                       // check bool/boolean value
                       if($validation == 'bool' || $validation == 'boolean'){
                           if(isset($request->$key)) if(!is_bool($request->$key)) $isError = true &&
                               array_push($errorsMessage, "مقدار ". translate_key($key) . " باید یک عبارت منطقی (true یا false) باشد");
                       }

                       // min chars validation
                       if(str_contains($validation, 'min')){
                           $min_value = (int)explode(':', $validation)[1];

                           if(isset($request->$key)) if(mb_strlen($request->$key,'utf8') < $min_value) $isError = true &&
                               array_push($errorsMessage, "مقدار ". translate_key($key) . " حداقل باید ".$min_value." کاراکتر باشد");
                       }

                       // max chars validation
                       if(str_contains($validation, 'max')){
                           $max_value = (int)explode(':', $validation)[1];

                           if(isset($request->$key)) if(mb_strlen($request->$key,'utf8') > $max_value) $isError = true &&
                               array_push($errorsMessage, "مقدار ". translate_key($key) . " نمی تواند بیشتر از ".$max_value." کاراکتر باشد");
                       }

                       // check chars length
                       if(str_contains($validation, 'length')){
                           $length_value = (int)explode(':', $validation)[1];
                           if(isset($request->$key)) if(mb_strlen($request->$key,'utf8') !== $length_value) $isError = true &&
                               array_push($errorsMessage, "مقدار ". translate_key($key) . " باید برابر با ".$length_value." کاراکتر باشد");
                       }
                   }
               }
               elseif($itemsCount == 1){
                   if(!isset($request->$field) || empty($request->$field)) $isError = true &&
                      array_push($errorsMessage, "لطفا ". translate_key($field) . " را وارد کنید");
               }
               else{
                   $this->sendResponse(message: 'ورودی های ولیدیشن شما اشتباه است', error: true, status: HTTP_BadREQUEST);
                   return exit();
               }



//               if(!isset($request->$field) || empty($request->$field)) $isError = true &&
//                   array_push($errorsMessage, "لطفا ". $field . " را وارد کنید");
           }

            if($isError)
            {
                $this->sendResponse(message: $errorsMessage, error: true, status: HTTP_BadREQUEST);
                return exit();
            }

        } return true;
    }

    public function checkUnique($table, $key, $value)
    {
        // unique Resource check
        $hasResource = $this->queryBuilder->table($table)->where($key, '=', $value)->get()->execute();

        if($hasResource)
        {
            $this->sendResponse(message: "مقدار " . translate_key($key) . " از قبل وجود دارد");
            return exit();
        }
    }
}