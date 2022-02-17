<?php

namespace App\Http\Controllers;

use App\Article;
use App\Http\Requests\ArticleRequest;
use App\Tag;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function __construct()
    {
        // ArticlePolicyを使う
        $this->authorizeResource(Article::class, 'article');
    }

    public function index()
    {
        $articles = Article::all()->sortByDesc('created_at')
                    ->load(['user', 'likes', 'tags']);
    
        return view('articles.index', ['articles' => $articles]);
    }

    public function create()
    {
        $allTagNames = Tag::all()->map(function ($tag) {
            return ['text' => $tag->name];
        });

        return view('articles.create', ['allTagNames' => $allTagNames]);
    }

    public function store(ArticleRequest $request, Article $article)
    {
        $article->fill($request->all());
        $article->user_id = $request->user()->id;
        $article->save();
        
        $request->tags->each(function ($tagName) use ($article) {
            // firstOrCreateでテーブルに存在しなければ、そのレコードをテーブルに保存した上で、モデルを返します
            $tag = Tag::firstOrCreate(['name' => $tagName]);
            $article->tags()->attach($tag);
        });

        return redirect()->route('articles.index');
    }

    public function edit(Article $article)
    {
        $tagNames = $article->tags->map(function ($tag) {
            return ['text' => $tag->name];
        });
        
        $allTagNames = Tag::all()->map(function ($tag) {
            return ['text' => $tag->name];
        });
        
        return view('articles.edit', ['article' => $article, 'tagNames' => $tagNames, 'allTagNames' => $allTagNames]);
    }

    public function update(ArticleRequest $request, Article $article)
    {
        $article->fill($request->all())->save();

        // 一旦関連するタグをすべて削除してから、改めてリクエストの内容で作成する
        $article->tags()->detach();
        $request->tags->each(function ($tagName) use ($article) {
            $tag = Tag::firstOrCreate(['name' => $tagName]);
            $article->tags()->attach($tag);
        });
        
        return redirect()->route('articles.index');
    }

    public function destroy(Article $article)
    {
        $article->delete();
        return redirect()->route('articles.index');
    }

    public function show(Article $article)
    {
        return view('articles.show', ['article' => $article]);
    }

    public function like(Request $request, Article $article)
    {
        // 既存のいいねがあれば一旦削除することで重複したいいねを防ぐ
        $article->likes()->detach($request->user()->id);
        $article->likes()->attach($request->user()->id);

        return [
            'id' => $article->id,
            'countLikes' => $article->count_likes,
        ];
    }

    public function unlike(Request $request, Article $article)
    {
        $article->likes()->detach($request->user()->id);

        return [
            'id' => $article->id,
            'countLikes' => $article->count_likes,
        ];
    }
}
