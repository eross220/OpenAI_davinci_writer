<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use App\Models\Workbook;
use App\Models\Content;
use App\Models\Image;
use Yajra\DataTables\DataTables;
use DB;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Content::where('user_id', Auth::user()->id)->latest()->get();
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('actions', function($row){
                        $actionBtn = '<div>
                                            <a href="'. route("user.documents.show", $row["id"] ). '"><i class="fa-solid fa-file-lines table-action-buttons edit-action-button" title="View Document"></i></a>
                                            <a class="deleteResultButton" id="'. $row["id"] .'" href="#"><i class="fa-solid fa-trash-xmark table-action-buttons delete-action-button" title="Delete Document"></i></a> 
                                        </div>';
                        return $actionBtn;
                    })
                    ->addColumn('created-on', function($row){
                        $created_on = '<span class="font-weight-bold">'.date_format($row["created_at"], 'd M Y').'</span><br><span>'.date_format($row["created_at"], 'H:i A').'</span>';
                        return $created_on;
                    })
                    ->addColumn('custom-title', function($row){
                        $custom = '<a class="font-weight-bold" href="'. route("user.documents.show", $row["id"] ). '">'.ucfirst($row["title"]).'</a>'; 
                        return $custom;
                    })
                    ->addColumn('custom-template', function($row){
                        $custom = '<span class="font-weight-bold">'.ucfirst($row["template_name"]).'</span>';
                        return $custom;
                    })
                    ->addColumn('custom-language', function($row) {
                        $language = '<span class="vendor-image-sm overflow-hidden"><img class="mr-2" src="' . URL::asset($row['language_flag']) . '">'. $row['language_name'] .'</span> ';            
                        return $language;
                    })
                    ->rawColumns(['actions', 'created-on', 'custom-language', 'custom-title', 'template_name'])
                    ->make(true);
                    
        }


        return view('user.documents.documents.index');
    }


    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function images(Request $request)
    {   
        if ($request->ajax()) {
            $data = Image::where('user_id', Auth::user()->id)->latest()->get();
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('actions', function($row){
                        $actionBtn = '<div>
                                            <a download href=" '. url($row["image"]) .'"><i class="fa-solid fa-cloud-arrow-down table-action-buttons edit-action-button" title="Download Image"></i></a>
                                            <a class="deleteResultButton" id="'. $row["id"] .'" href="#"><i class="fa-solid fa-trash-xmark table-action-buttons delete-action-button" title="Delete Image"></i></a> 
                                        </div>';
                        return $actionBtn;
                    })
                    ->addColumn('created-on', function($row){
                        $created_on = '<span class="font-weight-bold">'.date_format($row["created_at"], 'd M Y').'</span><br><span>'.date_format($row["created_at"], 'H:i A').'</span>';
                        return $created_on;
                    })
                    ->addColumn('link', function($row){
                        $custom = '<a class="file-name font-weight-bold" href="#" id="'.$row["id"].'">' . $row['name'] . '</a>'; 
                        return $custom;
                    })
                    ->rawColumns(['actions', 'created-on', 'link'])
                    ->make(true);
                    
        }

        return view('user.documents.images.index');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Content $id)
    {
        if ($id->user_id == Auth::user()->id){

            $workbooks = Workbook::where('user_id', auth()->user()->id)->latest()->get();

            return view('user.documents.documents.show', compact('id', 'workbooks'));     

        } else{
            return redirect()->route('user.documents');
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        if ($request->ajax()) {

            $result = Content::where('id', request('id'))->firstOrFail();  

            if ($result->user_id == Auth::user()->id){

                $result->delete();

                return response()->json('success');    
    
            } else{
                return response()->json('error');
            } 
        }              
    }

}
