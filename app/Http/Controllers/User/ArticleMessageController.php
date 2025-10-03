<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Criteria;
use App\Models\NotificationUserModel;
use App\Models\User;

class ArticleMessageController extends Controller
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

        $news = NotificationUserModel::where('recipient_id','=',$request->user()->id);

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

        $news = $news->paginate($count_element);

        $news->appends([
            'sort_date' => $request->input('sort_date'),
            'sort_title' => $request->input('sort_title'),
            'sort_sender' => $request->input('sort_sender')
        ]);

        return view('user.article.news_index', [
            'news' => $news, 
            'sort_date' => $request->input('sort_date'),
            'sort_title' => $request->input('sort_title'),
            'sort_sender' => $request->input('sort_sender'),
            'themes' => Criteria::all(),
            'senders' => User::where('level_id', '=', 1)->get(),
            'page' => empty($request->input('page')) ? 1 : $request->input('page'),
            'nomer' => $nomer
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
