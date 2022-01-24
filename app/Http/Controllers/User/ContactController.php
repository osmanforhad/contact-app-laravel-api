<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contacts;
use Validator;
use Illuminate\Routing\UrlGenerator;

class ContactController extends Controller
{
    
    protected $contacts;
    protected $base_url;


    public function __construct(UrlGenerator $urlGenerator)
    {
        $this->middleware("auth:users");
        $this->contacts = new Contacts;
        $this->base_url = $urlGenerator->to("/");
    }


    //__Function for create new contact__//
    public function addContacts(Request $request)
    {
        $validator = Validator::make($request->all(), 
        [
            "token" => "required",
            "firstname" => "required|string",
            "phone" => "required|string",
        ]);


        if($validator->fails())
        {
            return response()->json([
                "success" => false,
                "message" => $validator->messages()->toArray(),
            ], 400);
        }

        $profile_picture = $request->profile_image;
        $file_name = "";

        if($profile_picture == null)
        {
            $file_name = "default-avatar.png";
        }
        else {
            $generate_name = uniqid()."_".time().date("Ymd")."_IMG";
            $base64Image = $profile_picture;
            $fileBin = file_get_contents($base64Image);
            $mimetype = mime_content_type($fileBin);
            if("image/png"==$mimetype)
            {
                $file_name = $generate_name.".png";
            }
            else if("image/jpeg"==$mimetype)
            {
                $file_name = $generate_name.".jpeg";
            }
            else if("image/jpg"==$mimetype)
            {
                $file_name = $generate_name.".jpg";
            }
            else {
                return response()->json([
                    "success" => false,
                    "message" => "Only png, jpeg and jpg files are accepted as profile picture",
                ], 401);
            }
        }

        $user_token = $request->token;
        $user = auth("users")->authenticate($user_token);
        $user_id = $user->id;

        $this->contacts->user_id = $user_id;
        $this->contacts->phonenumber = $request->phonenumber;
        $this->contacts->firstname = $request->firstname;
        $this->contacts->lastname = $request->lastname;
        $this->contacts->email = $request->email;
        $this->contacts->image_file = $request->profile_image;
        $this->contacts->save();
        if($profile_picture == null)
        {

        }
        else{
            file_put_contents("./profile_images/".$file_name,$fileBin);
        }

        return response()->json([
            "success" => true,
            "message" => "Contact Saved Successfully",
        ], 200);

    }


     //__Function for fetch conact list__//
     public function getPaginatedData($token, $pagination = null)
     {
         $file_directory = $this->base_url."/profile_images";
         $user = auth("users")->authenticate($token);
         $user_id = $user->user_id;
         if($pagination==null || $pagination=="")
         {
             $contacts = $this->contacts->where("user_id", $user_id)->orderBy("id", "DESC")->get()->toArray();
             return response()->json([
                "success" => true,
                "data" => $contacts,
                "file_directory" => $file_directory,
            ], 200);
         }
         $contacts_paginated = $this->contacts->where("user_id", $user_id)->orderBy("id", "DESC")->paginate($pagination);
         return response()->json([
            "success" => true,
            "data" => $contacts_paginated,
            "file_directory" => $file_directory,
        ], 200);
     }


     //__Function for update conact info__//
     public function editSingleData(Request $request, $id)
     {
        $validator = Validator::make($request->all(), 
        [
            "firstname" => "required|string",
            "phone" => "required|string",
        ]);

        if($validator->fails())
        {
            return response()->json([
                "success" => false,
                "message" => $validator->messages()->toArray(),
            ], 400);
        }

        $findData = $this->contacts::find($id);

        if(!$findData)
        {
            return response()->json([
                "success" => false,
                "message" => "Sorry! This content has no valid id",
            ], 401);
        }
        $getFile = $findData->image_file;
        $getFile== "default-avatar.png"? :unlink("./profile_images/".$getFile);
        $profile_picture = $request->profile_image;
        $file_name = "";

        if($profile_picture == null)
        {
            $file_name = "default-avatar.png";
        }
        else {
            $generate_name = uniqid()."_".time().date("Ymd")."_IMG";
            $base64Image = $profile_picture;
            $fileBin = file_get_contents($base64Image);
            $mimetype = mime_content_type($fileBin);
            if("image/png"==$mimetype)
            {
                $file_name = $generate_name.".png";
            }
            else if("image/jpeg"==$mimetype)
            {
                $file_name = $generate_name.".jpeg";
            }
            else if("image/jpg"==$mimetype)
            {
                $file_name = $generate_name.".jpg";
            }
            else {
                return response()->json([
                    "success" => false,
                    "message" => "Only png, jpeg and jpg files are accepted as profile picture",
                ], 401);
            }

            $findData->firstname = $request->firstname;
            $findData->phonenumber = $request->phonenumber;
            $findData->image_file = $file_name;
            $findData->lastname = $request->lastname;
            $findData->email = $request->email;
            $findData->save();
            if($profile_picture == null)
            {
    
            }
            else{
                file_put_contents("./profile_images/".$file_name,$fileBin);
            }
    
            return response()->json([
                "success" => true,
                "message" => "Contact Updated Successfully",
            ], 200);

        }

     }

     //__Function for DELETE Contact info__//
     public function deleteContacts($id)
     {
        $findData = $this->contacts::find($id);
        if(!$findData)
        {
            return response()->json([
                "success" => true,
                "message" => "There is no contact associated with this id",
            ], 401);
        }

        $getFile = $findData->image_file;
        if($findData->delete())
        {
            $getFile == "default-avatar.png"? :unlink("./profile_images/".$getFile); 
            return response()->json([
                "success" => true,
                "message" => "Contact Deleted Successfully",
            ], 200);
        }

     }


     //__Function for Fetch Single Contact info__//
     public function getSingleData($id)
     {
         $file_directory = $this->base_url."/profile_images";
        $findData = $this->contacts::find($id);
        if(!$findData)
        {
            return response()->json([
                "success" => true,
                "message" => "There is no contact associated with this id",
            ], 401);
        }
        return response()->json([
            "success" => true,
            "data" => $findData,
            "file_directory" => $file_directory,
        ], 201);
     }


     //__Function for Search Contact info__//
     public function searchData($search, $token, $pagination=null)
     {
        $file_directory = $this->base_url."/profile_images";
        $user = auth("users")->authenticate($token);
        $user_id = $user->id;

        if($pagination==null || $pagination="")
        {
            $non_paginated_search_query = $this->contacts::where("user_id", $user_id)
            ->where(function($query) use($search)  {
                $query->where("firstname", "LIKE","%$search%")
                ->orWhere("lastname","LIKE","%$search%")
                ->orWhere("email","LIKE","%$search%")
                ->orWhere("phonenumber","LIKE","%$search%");
            })->orderBy("id", "DESC")->get()->toArray();
            return response()->json([
                "success" => true,
                "data" => $non_paginated_search_query,
                "file_directory" => $file_directory,
            ], 201);
        }

        $paginated_search_query = $this->contacts::where("user_id", $user_id)
            ->where(function($query) use($search)  {
                $query->where("firstname", "LIKE","%$search%")
                ->orWhere("lastname","LIKE","%$search%")
                ->orWhere("email","LIKE","%$search%")
                ->orWhere("phonenumber","LIKE","%$search%");
            })->orderBy("id", "DESC")->paginate($pagination);
            return response()->json([
                "success" => true,
                "data" => $paginated_search_query,
                "file_directory" => $file_directory,
            ], 201);

     }


}
