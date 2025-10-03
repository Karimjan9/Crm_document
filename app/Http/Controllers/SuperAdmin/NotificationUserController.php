<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationUser\NotificationUserCreateRequest;
use App\Models\Criteria;
use App\Models\NotificationUserModel;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $count_element = 5;

        if(empty($request->input('page'))){

            $nomer = 0;
        }else{
            $nomer = ( (intval($request->input('page')) - 1) * $count_element );
        }

        $news = new NotificationUserModel;

        if(!empty($request->input('sort_date')) || empty($request->input('page'))){

            if($request->input('sort_date') == 'top' || empty($request->input('page'))){
                $news = $news->orderBy('created_at', 'desc');
            }else{
                $news = $news->orderBy('created_at', 'asc');
            }
        }

        if(!empty($request->input('sort_title'))){

            $news = $news->where('title', '=', $request->input('sort_title'));
        }

        if(!empty($request->input('sort_sender'))){

            $news = $news->where('sender_id', '=', $request->input('sort_sender'));
        }

        if(!empty($request->input('sort_recipient'))){

            $news = $news->where('recipient_id', '=', $request->input('sort_recipient'));
        }

        $news = $news->paginate($count_element);

        $news->appends([
            'sort_date' => $request->input('sort_date'),
            'sort_title' => $request->input('sort_title'),
            'sort_sender' => $request->input('sort_sender'),
            'sort_recipient' => $request->input('sort_recipient')
        ]);

        return view('super.notification_user.index', [
            'news' => $news, 
            'sort_date' => $request->input('sort_date'),
            'sort_title' => $request->input('sort_title'),
            'sort_sender' => $request->input('sort_sender'),
            'sort_recipient' => $request->input('sort_recipient'),
            'themes' => Criteria::all(),
            'senders' => User::where('level_id', '=', 1)->get(),
            'recipients' => User::where('level_id', '=', 3)->get(),
            'page' => empty($request->input('page')) ? 1 : $request->input('page'),
            'nomer' => $nomer
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        return view('super.notification_user.create', [
            'criterias' => Criteria::all(),
            'users' => User::where('level_id', '=', '3')->where('id', '!=', $request->user()->id)->get() 
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(NotificationUserCreateRequest $request)
    {
        foreach ($request->input('recipients') as $key => $value) {

            if(is_numeric($request->input('title'))){

                $title = Criteria::where('id', '=', $request->input('title'))->first()->name;
            }else{

                $title = $request->input('title');
            }
            
            NotificationUserModel::create([
                'title' => $title,
                'message' => $request->input('message'),
                'sender_id' => $request->user()->id,
                'recipient_id' => $value,
            ]);
        }

        return redirect()->route('superadmin.notification_user.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
}
