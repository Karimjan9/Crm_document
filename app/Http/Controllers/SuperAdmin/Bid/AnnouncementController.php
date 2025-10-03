<?php

namespace App\Http\Controllers\SuperAdmin\Bid;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bid\Announcement\AnnouncementRequestCreate;
use App\Models\AnnouncementModel;
use Illuminate\Http\Request;
use PDF as DomPdf;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        return view('super.bid.announcement.index', ['announcements' => AnnouncementModel::with('teacher')->where('status', '=', $request->status)->where('unfulfilled','=',$request->unfulfilled)->get(), "status" => $request->status,'unfulfilled'=>$request->unfulfilled]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AnnouncementRequestCreate $request)
    {

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $announcement = AnnouncementModel::where('id', '=', $id)->first();
        
        $data = [
            'date' => $announcement->date_format(), 
            'time' => $announcement->time,
            'pair' => $announcement->pair,
            'location' => $announcement->location,
            'subject' => $announcement->subject,
            'teacher' => $announcement->teacher->full_name,
            'group' => $announcement->group,
            'group_name' => $announcement->group_name,
            'theme' => $announcement->theme,
            'data' => $announcement->description
        ];
    
        $pdf = DomPdf::loadView('template_pdf.announcement', $data);  
        
        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function get_count_active_announcement_counts() 
    {
        return response()->json(['announcement_count' => AnnouncementModel::where('status', '=', 0)->count() ]);
    }

    public function update_announcement_status(Request $request, $id)
    {
        AnnouncementModel::where('id', '=', $id)->update(['status' => $request->status]);

        return redirect()->route('superadmin.announcement.index', ['status' => 0]);
    }
    public function update_announcement_unfulfilled($id,$unfulfulled){

        AnnouncementModel::where('id', '=', $id)->update(['unfulfilled' => $unfulfulled]);

        return redirect()->route('superadmin.announcement.index', ['status' => 0]);
    }
}
