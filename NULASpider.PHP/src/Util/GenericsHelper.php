<?php

// namespace nulastudio\Util;

// class TypeInfo
// {
//     public $Types = [];

//     public function __construct(...$types)
//     {
//         foreach ($types as $type) {
//             if (is_string($type)) {
//                 $this->Types[] = $type;
//             }
//         }
//     }

//     public function GetTypes()
//     {
//         return $this->Types;
//     }

//     public function __toString()
//     {
//         $res = implode(',', $this->GetTypes());
//         return $res;
//     }
// }

// class ObjectInfo
// {
//     private $Obj     = null;
//     public $FullName = '';
//     public $TypeInfo = null;

//     public function __construct(string $fullName, ...$types)
//     {
//         $this->FullName = $fullName;
//         if (isset($types[0]) && $types[0] instanceof TypeInfo) {
//             $this->TypeInfo = $types[0];
//         } else {
//             $this->TypeInfo = new TypeInfo(...$types);
//         }
//     }

//     public function __invoke(...$params)
//     {
//         return $this->Invoke(...$params);
//     }
//     public function Invoke(...$params)
//     {
//         //new something
//         $type = \System\Type::GetType((string) $this);
//         $a  = $type->Assembly::CreateInstance($type->FullName);
//         var_dump($a);
//         return $a;
//         echo "{$this}\n";
//     }

//     public function __toString()
//     {
//         $TypeInfo  = (string) $this->TypeInfo;
//         $TypeCount = count($this->TypeInfo->GetTypes());
//         $TypeInfo  = $TypeInfo ? "`{$TypeCount}[{$TypeInfo}]" : '';
//         return "{$this->FullName}{$TypeInfo}";
//     }
// }

// class MethodInfo
// {
//     public $ObjectInfo = null;
//     public $Name       = '';
//     public $TypeInfo   = null;

//     public function __construct(string $name, ...$types)
//     {
//         $this->Name = $name;
//         if (isset($types[0]) && $types[0] instanceof TypeInfo) {
//             $this->TypeInfo = $types[0];
//         } else {
//             $this->TypeInfo = new TypeInfo(...$types);
//         }
//     }
//     public function BindObjectInfo(ObjectInfo $objectInfo)
//     {
//         $this->ObjectInfo = $objectInfo;
//         return $this;
//     }

//     public function __invoke(...$params)
//     {
//         return $this->Invoke(...$params);
//     }
//     public function Invoke(...$params)
//     {
//         // invoke something
//         echo "{$this}\n";
//     }

//     public function __toString()
//     {
//         return "{$this->ObjectInfo}.{$this->Name}({$this->TypeInfo})";
//     }
// }

// class GenericsHelper
// {
//     // return TypeInfo
//     public static function NewTypeInfo(...$types)
//     {
//         return new TypeInfo(...$types);
//     }

//     // return ObjectInfo
//     public static function NewInstance(string $fullName, ...$types)
//     {
//         return new ObjectInfo(str_replace('\\', '.', $fullName), ...$types);
//     }

//     // return MethodInfo
//     public static function InvokeMethod(ObjectInfo $obj, string $name, ...$types)
//     {
//         return (new MethodInfo($name, ...$types))->BindObjectInfo($obj);
//     }
// }

// GenericsHelper::NewInstance(MethodInfo::class)();
// GenericsHelper::NewInstance(MethodInfo::class, 'object')(1, null, true);
// GenericsHelper::NewInstance(\stdClass::class, 'object', 'int')('param1', 'param2', 'param3');

// $objectInfo = GenericsHelper::NewInstance(\stdClass::class, 'object', 'int');

// GenericsHelper::InvokeMethod($objectInfo, 'foo', 'object', 'int')('param1', 'param2', 'param3');
