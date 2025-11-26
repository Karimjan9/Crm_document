<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Requests\Article\ArticleRequestCreate;
use App\Models\ArticleModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $article = ArticleModel::with('teacher')->where('status', '=', $request->status)->orderBy('created_at', 'desc')->paginate(5);
        $article->appends(['status' => $request->status]);

        return view('super.article.index', [
            'articles' => $article,
            "status" => $request->status
        ]);
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
    public function store(ArticleRequestCreate $request)
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

    public function get_count_active_article_counts() 
    {
        return response()->json(['article_count' => ArticleModel::where('status', '=', 0)->count() ]);
    }

    public function update_article_status(Request $request, $id)
    {
        ArticleModel::where('id', '=', $id)->update(['status' => $request->status]);

        return redirect()->route('superadmin.article.index', ['status' => 0]);
    }
}
