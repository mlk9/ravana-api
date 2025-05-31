<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Bookmark;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{

    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $bookmarks = Bookmark::query();
        $bookmarks->where('user_uuid', $request->user()->uuid);
        $bookmarks = $bookmarks
            ->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1));
        return $this->success(['data' => $bookmarks->toArray()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'string', 'in:article'],
            'id' => ['required', 'string'],
        ]);

        $user = $request->user();

        $object = null;

        if ($request->input('type') == 'article') {

            $exists = Bookmark::query()->where('bookmark_able_type', 'App\Models\Article')
                ->where('bookmark_able_id', $request->input('id'))
                ->where('user_uuid', $user->uuid)
                ->first();

            if (is_null($exists)) {
                $object = Article::query()->where('uuid', $request->input('id'))->firstOrFail();
            } else {
                if ($exists->delete()) {
                    return $this->success(['data' => false, 'code' => 200]);
                } else {
                    return $this->error();
                }
            }
        }

        $bookmark = new Bookmark([
            'user_uuid' => $user->uuid,
        ]);

        $bookmark->bookmark_able()->associate($object);

        if ($bookmark->save() === false) {
            return $this->error();
        }

        return $this->success(['data' => true, 'code' => 201]);
    }
}
