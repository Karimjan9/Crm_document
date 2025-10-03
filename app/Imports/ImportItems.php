<?php

namespace App\Imports;

use App\Models\ItemsModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportItems implements ToCollection
{
    /**
    * @param Collection $collection
    */

    public function collection(Collection $collection)
    {
        // $users = $collection->groupBy('1');
        // dd($users);
        foreach($collection as $key => $value){
            // dd(strval($collection[$key][10]));
           
            if ($key>=3 && $key<=18) {
                // dd($collection[$key][10]);
                return(ItemsModel::create(["name"=>strval($value[10]),"bodily"=>1,"first"=>1,"second"=>1,"unity_id"=>2]));

                dd(strval($collection[$key][10]));
                // dd($collection[$key][10]);
                // dd($collection[$key][11]=="дона");
                if($collection[$key][11]=="дона"){
                   
                    ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>1,"unity_id"=>2,"description"=>$value[10]]);

                }elseif($collection[$key][11]=="комп"){
                    ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>1,"unity_id"=>1,"description"=>$value[10]]);

                }elseif($collection[$key][11]=="кв/метр"){
                    ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>1,"unity_id"=>3,"description"=>$value[10]]);

                }elseif($collection[$key][11]=="м2"){
                    ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>1,"unity_id"=>4,"description"=>$value[10]]);

                }
            }
            // if ($key>=20 && $key<=25) {
            //     // dd($value);
            //     if($collection[$key][11]=="дона"){
            //         ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>2,"unity_id"=>2,"description"=>$value[10]]);

            //     }elseif($collection[$key][11]=="комп"){
            //         ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>2,"unity_id"=>1,"description"=>$value[10]]);

            //     }elseif($collection[$key][11]=="кв/метр"){
            //         ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>2,"unity_id"=>3,"description"=>$value[10]]);

            //     }elseif($collection[$key][11]=="м2"){
            //         ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>2,"unity_id"=>4,"description"=>$value[10]]);

            //     }
            // }
            // if ($key>=26 && $key<=35) {
            //     // dd($value);
            //     if($collection[$key][11]=="дона"){
            //         ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>4,"unity_id"=>2,"description"=>$value[10]]);

            //     }elseif($collection[$key][11]=="комп"){
            //         ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>4,"unity_id"=>1,"description"=>$value[10]]);

            //     }elseif($collection[$key][11]=="кв/метр"){
            //         ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>4,"unity_id"=>3,"description"=>$value[10]]);

            //     }elseif($collection[$key][11]=="м2"){
            //         ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>4,"unity_id"=>4,"description"=>$value[10]]);

            //     }
            // }

            // if ($key>=26 && $key<=34) {
            //     // dd($value);
            //     if($collection[$key][11]=="дона"){
            //         ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>4,"unity_id"=>2,"description"=>$value[10]]);

            //     }elseif($collection[$key][11]=="комп"){
            //         ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>4,"unity_id"=>1,"description"=>$value[10]]);

            //     }elseif($collection[$key][11]=="кв/метр"){
            //         ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>4,"unity_id"=>3,"description"=>$value[10]]);

            //     }elseif($collection[$key][11]=="м2"){
            //         ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>4,"unity_id"=>4,"description"=>$value[10]]);

            //     }
            // }
            // if ($key>=35 && $key<=40) {
            //     // dd($value);
            //     if($collection[$key][11]=="дона"){
            //         ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>3,"unity_id"=>2,"description"=>$value[10]]);

            //     }elseif($collection[$key][11]=="комп"){
            //         ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>3,"unity_id"=>1,"description"=>$value[10]]);

            //     }elseif($collection[$key][11]=="кв/метр"){
            //         ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>3,"unity_id"=>3,"description"=>$value[10]]);

            //     }elseif($collection[$key][11]=="м2"){
            //         ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>3,"unity_id"=>4,"description"=>$value[10]]);

            //     }
            // }
            // if ($key>=42 && $key<=75) {
            //     if($collection[$key][11]=="дона"){
            //         ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>3,"unity_id"=>2,"description"=>$value[10]]);

            //     }elseif($collection[$key][11]=="комп"){
            //         ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>3,"unity_id"=>1,"description"=>$value[10]]);

            //     }elseif($collection[$key][11]=="кв/метр"){
            //         ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>3,"unity_id"=>3,"description"=>$value[10]]);

            //     }elseif($collection[$key][11]=="м2"){
            //         ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>3,"unity_id"=>4,"description"=>$value[10]]);

            //     }

            //     if ($key>=77 && $key<=79) {
            //         if($collection[$key][11]=="дона"){
            //             ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>6,"unity_id"=>2,"description"=>$value[10]]);
    
            //         }elseif($collection[$key][11]=="комп"){
            //             ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>6,"unity_id"=>1,"description"=>$value[10]]);
    
            //         }elseif($collection[$key][11]=="кв/метр"){
            //             ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>6,"unity_id"=>3,"description"=>$value[10]]);
    
            //         }elseif($collection[$key][11]=="м2"){
            //             ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>6,"unity_id"=>4,"description"=>$value[10]]);
    
            //         }
            //     // dd($value);
            //     }

            //     if ($key>=80 && $key<=83) {
            //         if($collection[$key][11]=="дона"){
            //             ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>7,"unity_id"=>2,"description"=>$value[10]]);
    
            //         }elseif($collection[$key][11]=="комп"){
            //             ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>7,"unity_id"=>1,"description"=>$value[10]]);
    
            //         }elseif($collection[$key][11]=="кв/метр"){
            //             ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>7,"unity_id"=>3,"description"=>$value[10]]);
    
            //         }elseif($collection[$key][11]=="м2"){
            //             ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>7,"unity_id"=>4,"description"=>$value[10]]);
    
            //         }
            //     // dd($value);
            //     }

            //     if ($key>=85 && $key<=99) {
            //         if($collection[$key][11]=="дона"){
            //             ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>6,"unity_id"=>2,"description"=>$value[10]]);
    
            //         }elseif($collection[$key][11]=="комп"){
            //             ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>6,"unity_id"=>1,"description"=>$value[10]]);
    
            //         }elseif($collection[$key][11]=="кв/метр"){
            //             ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>6,"unity_id"=>3,"description"=>$value[10]]);
    
            //         }elseif($collection[$key][11]=="м2"){
            //             ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>6,"unity_id"=>4,"description"=>$value[10]]);
    
            //         }
            //     }

            //         if ($key>=100 && $key<=102) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>6,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>6,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>6,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>6,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }

            //         if ($key>=104 && $key<=105) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>9,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>9,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>9,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>9,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }
            //         if ($key>=106 && $key<=115) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>8,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>8,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>8,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>8,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }
            //         if ($key>=117 && $key<=121) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>10,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>10,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>10,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>1,"second"=>10,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }
            //         if ($key>=123 && $key<=131) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>11,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>11,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>11,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>11,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }
            //         if ($key>=133 && $key<=136) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>12,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>12,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>12,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>12,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }

            //         if ($key>=137 && $key<=142) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>13,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>13,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>13,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>13,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }

            //         if ($key>=144 && $key<=148) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>14,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>14,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>14,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>14,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }

            //         if ($key>=150 && $key<=158) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>15,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>15,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>15,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>15,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }

            //         if ($key==159 ) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>16,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>16,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>16,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>16,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }

            //         if ($key>=161 && $key<=169) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>17,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>17,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>17,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>17,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }

            //         if ($key==170 ) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>18,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>18,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>18,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>18,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }

            //         if ($key>=171 && $key<=177) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>17,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>17,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>17,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>17,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }
            //         if ($key==178 ) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>18,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>18,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>18,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>18,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }

            //         if ($key>=179 && $key<=182) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>17,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>17,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>17,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>17,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }

            //         if ($key>=184 && $key<=237) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>19,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>19,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>19,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>19,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }

            //         if ($key>=239 && $key<=240) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>21,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>21,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>21,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>21,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }
            //         if ($key>=241 && $key<=245) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>22,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>22,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>22,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>22,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }

            //         if ($key>=250 && $key<=256) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>23,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>23,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>23,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>23,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }

            //         if ($key>=257 && $key<=260) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>24,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>24,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>24,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>24,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }

            //         if ($key>=262 && $key<=294) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>4,"second"=>27,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>4,"second"=>27,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>4,"second"=>27,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>4,"second"=>27,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }

            //         if ($key>=296 && $key<=299) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>25,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>25,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>25,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>25,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }


            //         if ($key>=300 && $key<=302) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>26,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>26,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>26,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>2,"second"=>26,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }

                    
            //         if ($key>=303 && $key<=345) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>3,"second"=>28,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>3,"second"=>28,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>3,"second"=>28,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>3,"second"=>28,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }

            //         if ($key>=347 && $key<=376) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>3,"second"=>29,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>3,"second"=>29,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>3,"second"=>29,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>3,"second"=>29,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }

            //         if ($key>=378 && $key<=398) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>5,"second"=>30,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>5,"second"=>30,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>5,"second"=>30,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>5,"second"=>30,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }

            //         if ($key>=400 && $key<=409) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>5,"second"=>31,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>5,"second"=>31,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>5,"second"=>31,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>5,"second"=>31,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }

            //         if ($key>=411 && $key<=421) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>5,"second"=>32,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>5,"second"=>32,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>5,"second"=>32,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>5,"second"=>32,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }

            //         if ($key>=411 && $key<=421) {
            //             if($collection[$key][11]=="дона"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>5,"second"=>32,"unity_id"=>2,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="комп"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>5,"second"=>32,"unity_id"=>1,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="кв/метр"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>5,"second"=>32,"unity_id"=>3,"description"=>$value[10]]);
        
            //             }elseif($collection[$key][11]=="м2"){
            //                 ItemsModel::create(["name"=>$value[10],"bodily"=>1,"first"=>5,"second"=>32,"unity_id"=>4,"description"=>$value[10]]);
        
            //             }
               
            //         }

                
        }
    }
}
    