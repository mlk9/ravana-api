<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Comment::class);

        $request->validate([
            'status' => ['nullable', 'in:pending,approved,rejected'],
            'order' => ['nullable', 'in:rate,rejected_at,approved_at,created_at'],
            'dir' => ['nullable', 'in:asc,desc']
        ]);

        $comments = Comment::query();

//        if ($request->user()->can('comments.control-other') === false) {
//            $comments->where('commentable_uuid', $request->input('status'));
//        }

        if ($request->filled('status')) {
            $comments->where('status', $request->input('status'));
        }

        if ($request->filled('dir') && $request->filled('order')) {
            $comments->orderBy($request->input('order'), $request->input('dir'));
        }

        $comments = $comments
            ->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1));

        return $this->success(['data' => $comments->toArray()]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Request $request,string $uuid): JsonResponse
    {
        $comment = Comment::query()->where('uuid', $uuid)->firstOrFail();
        Gate::authorize('view', [$request->user(),$comment]);
        return $this->success(['data' => $comment->toArray()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $comment = Comment::query()->where('uuid', $uuid)->firstOrFail();
        Gate::authorize('update', [$request->user(),$comment]);

        $request->validate([
            'text' => ['nullable', 'string', 'min:3'],
            'rate' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'status' => ['nullable', 'string', 'in:pending,rejected,approved'],
        ]);

        $status = $request->input('status', $comment->status);

        $res = $comment->update([
            'text' => $request->input('text', $comment->text),
            'rate' => $request->input('rate', $comment->rate),
            'status' => $status,
            'approved_at' => $status === 'approved' ? now() : null,
            'rejected_at' => $status === 'rejected' ? now() : null,
        ]);

        $comment->refresh();

        return $res ? $this->success([
            'data' => $comment->toArray()
        ]) : $this->error();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request,string $uuid): JsonResponse
    {
        $comment = Comment::query()->where('uuid', $uuid)->firstOrFail();
        Gate::authorize('delete', [$request->user(),$comment]);
        $res = $comment->delete();

        return $res ? $this->success() : $this->error();
    }

    public function answer(Request $request,string $uuid) : JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:rejected,approved'],
            'answer' => ['required', 'string', 'min:2']
        ]);

        $comment = Comment::query()->where('uuid', $uuid)->firstOrFail();

        Gate::authorize('answer', [$request->user(),$comment]);

        $status = $request->input('status', 'pending');
        $res = $comment->update([
            'status' => $status,
            'approved_at' => $status === 'approved' ? now() : null,
            'rejected_at' => $status === 'rejected' ? now() : null,
        ]);

        if ($res === false) {
            return $this->error();
        }

        $answer = new Comment([
            'text' => $request->input('answer'),
            'user_uuid' => $request->user()->uuid,
            'parent_uuid' => $comment->uuid,
        ]);

        $answer->commentable()->associate($comment->commentable);

        if ($answer->save() === false) {
            return $this->error();
        }

        return $this->success();
    }
}
