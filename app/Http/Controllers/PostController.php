<?php

/**
 * This controller handles the Posts on the database
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    //Index of the Controller
    public function index()
    {

    }

    /**
      * @description Shows the detail of a post
      * @param $id - id of the post to be displayed
      * @return the post object
    */
    public function show($id)
    {
    	$post = DB::table('posts')
    		->select([
    			'posts.id',
    			'posts.title',
    			DB::raw('users.name as username'),
    			'content'
    			])
    		->join('users', 'posts.user_id', '=', 'users.id')
    		->where('posts.id','=', $id)
            ->get();

    	$comments = DB::table('comments')
    		->select('post_id', 'text')
    		->where('comments.post_id','=', $id)
            ->get();
		$post->comments = $comments;
		return $post;
    }

    /**
      * @description Shows the detail of a post
      * @param $id - id of the post to be displayed
      * @return the post object
    */
    public function delete($id)
    {
        $user = DB::table('users')
            ->select([
                'name',
                'email'
                ])
            ->where('id','=', $id)
            ->get();

        if(isset($user[0]))
        {
            DB::transaction(function() use ($id) {
            DB::table('posts')
                ->where('user_id','=', $id)
                ->delete();
            DB::table('comments')
                ->where('user_id','=', $id)
                ->delete();
            DB::table('users')
                ->where('id','=', $id)
                ->delete();
            });

            $content = Storage::get('deleteMail.txt');
            $message = __($content, [
              'name' => $user[0]->name, 
              'company_name' => 'Address Intelligence',
            ]);

            Log::info($message);
        }
        
        else
        {
            return "This user doesn't exist";
        }

    }
}
