<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupJoinRequest;
use App\Models\Notification;
use App\Models\Request as ModelsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GroupController extends Controller
{
    public function index()
    {
        $groups = Group::all();

        return view('pages.groups.list', ['groups' => $groups]);
    }

    public function create()
    {
        return view('pages.groups.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'isPrivate' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->route('groups.create')
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->only(['name', 'description']);
        $data['isprivate'] = $request->has('isPrivate');

        $group = new Group($data);
        $group->save();

        $group->members()->attach(Auth::id(), ['isowner' => true]);

        return redirect()->route('groups.show', $group)->with('success', 'Group created successfully!');
    }

    public function show(Group $group)
    {
        $user = auth()->user();
        $isMember = $user && $group->members->contains($user);
        $isOwner = $user && $group->members()->wherePivot('isowner', true)->where('users.id', $user->id)->exists();

        if (! $group->isprivate || $isMember || $isOwner) {
            $posts = \App\Models\Post\Post::getPostsWithDetails(auth()->id());
            $posts = array_filter($posts, function ($post) use ($group) {
                return isset($post->groupid) ? $post->groupid == $group->id : false;
            });
            $posts = array_values($posts);
        } else {
            $posts = [];
        }

        $friendsInGroup = collect();
        $pendingRequest = null;
        $pendingRequests = collect();
        $isOwner = false;

        if (Auth::check()) {
            $user = Auth::user();
            if (method_exists($user, 'friends')) {
                $friends = $user->friends()->pluck('id');
                $friendsInGroup = $group->members()->whereIn('users.id', $friends)->get();
            }

            $pendingRequest = ModelsRequest::where('senderid', Auth::id())
                ->whereHas('groupJoinRequest', function ($query) use ($group) {
                    $query->where('groupid', $group->id);
                })
                ->where('status', 'pending')
                ->first();

            $isOwner = $group->members()->wherePivot('isowner', true)->where('users.id', Auth::id())->exists();

            if ($isOwner) {
                $pendingRequests = ModelsRequest::where('status', 'pending')
                    ->whereHas('groupJoinRequest', function ($query) use ($group) {
                        $query->where('groupid', $group->id);
                    })
                    ->with('groupJoinRequest')
                    ->get();
            }
        }

        // Add users_count attribute for the view
        $group->users_count = $group->members()->count();

        return view('pages.groups.show', [
            'group' => $group,
            'posts' => $posts,
            'friendsInGroup' => $friendsInGroup,
            'pendingRequest' => $pendingRequest,
            'pendingRequests' => $pendingRequests,
            'isOwner' => $isOwner,
        ]);
    }

    public function join(Group $group)
    {
        if ($group->isprivate) {
            $owner = $group->owner()->first();
            if ($owner) {
                $existingRequest = ModelsRequest::where('senderid', Auth::id())
                    ->whereHas('groupJoinRequest', function ($query) use ($group) {
                        $query->where('groupid', $group->id);
                    })
                    ->where('status', 'pending')
                    ->first();

                if ($existingRequest) {
                    return back()->with('info', 'You have already sent a request to join this group.');
                }

                $notification = Notification::create([
                    'receiverid' => $owner->id,
                    'message' => Auth::user()->name.' wants to join your group '.$group->name,
                ]);

                $request = ModelsRequest::create([
                    'notificationid' => $notification->id,
                    'senderid' => Auth::id(),
                    'status' => 'pending',
                ]);

                GroupJoinRequest::create([
                    'requestid' => $request->notificationid,
                    'groupid' => $group->id,
                ]);

                return back()->with('success', 'Your request to join the group has been sent.');
            }

            return back()->with('error', 'Could not find an owner for this group.');
        }

        $userId = Auth::id();
        $membersCount = $group->members()->count();
        $isOwner = false;
        if (! $group->isprivate && $membersCount == 0) {
            $isOwner = true;
        }
        $group->members()->attach($userId, ['isowner' => $isOwner]);

        return back()->with('success', 'You have joined the group!');
    }

    public function leave(Group $group)
    {
        $userId = Auth::id();
        $isOwner = $group->members()->wherePivot('isowner', true)->where('users.id', $userId)->exists();

        $group->members()->detach($userId);

        $remainingMembers = $group->members()->get();

        if ($isOwner) {
            if ($remainingMembers->count() > 0) {
                $oldestMember = $group->members()
                    ->withPivot('joinedAt')
                    ->orderBy('membership.joinedAt', 'asc')
                    ->first();
                if ($oldestMember) {
                    \DB::table('membership')
                        ->where('groupId', $group->id)
                        ->update(['isOwner' => false]);
                    \DB::table('membership')
                        ->where('groupId', $group->id)
                        ->where('userId', $oldestMember->id)
                        ->update(['isOwner' => true]);
                }
            } else {
                if ($group->isprivate) {
                    $group->isprivate = false;
                    $group->save();

                    return back()->with('success', 'You have left the group. The group is now public and will assign a new owner when someone joins.');
                }
            }
        }

        return back()->with('success', 'You have left the group.');
    }

    public function cancelRequest(Group $group)
    {
        $request = ModelsRequest::where('senderid', Auth::id())
            ->whereHas('groupJoinRequest', function ($query) use ($group) {
                $query->where('groupid', $group->id);
            })
            ->where('status', 'pending')
            ->first();

        if ($request) {
            GroupJoinRequest::where('requestid', $request->notificationid)->delete();

            Notification::where('id', $request->notificationid)->delete();

            $request->delete();

            return back()->with('success', 'Your request has been cancelled.');
        }

        return back()->with('error', 'No pending request found.');
    }

    public function acceptRequest(Group $group, $requestId)
    {
        $isOwner = $group->members()->wherePivot('isowner', true)->where('users.id', Auth::id())->exists();

        if (! $isOwner) {
            return back()->with('error', 'You are not authorized to accept requests.');
        }

        $request = ModelsRequest::where('notificationid', $requestId)
            ->whereHas('groupJoinRequest', function ($query) use ($group) {
                $query->where('groupid', $group->id);
            })
            ->first();

        if ($request && $request->status === 'pending') {
            $group->members()->attach($request->senderid, ['isowner' => false]);

            $request->update(['status' => 'accepted']);

            return back()->with('success', 'Request accepted. User has been added to the group.');
        }

        return back()->with('error', 'Request not found or already processed.');
    }

    public function rejectRequest(Group $group, $requestId)
    {
        $isOwner = $group->members()->wherePivot('isowner', true)->where('users.id', Auth::id())->exists();

        if (! $isOwner) {
            return back()->with('error', 'You are not authorized to reject requests.');
        }

        $request = ModelsRequest::where('notificationid', $requestId)
            ->whereHas('groupJoinRequest', function ($query) use ($group) {
                $query->where('groupid', $group->id);
            })
            ->first();

        if ($request && $request->status === 'pending') {
            $request->update(['status' => 'rejected']);

            return back()->with('success', 'Request rejected.');
        }

        return back()->with('error', 'Request not found or already processed.');
    }

    public function storePost(Request $request, Group $group)
    {
        $user = Auth::user();
        if (! $user || ! $group->members->contains($user)) {
            return response()->json(['success' => false, 'message' => 'Only group members can post.'], 403);
        }

        $validated = $request->validate([
            'content' => 'required_without:image|string|max:2000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $post = new \App\Models\Post\Post;
        $post->userid = $user->id;
        $post->groupid = $group->id;
        $post->createdat = now();
        $post->save();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $fileName = $request->file('image')->hashName();
            $request->file('image')->storeAs('post', $fileName, 'FileStorage');
            $imagePath = $fileName;
        }

        $standardPost = new \App\Models\Post\StandardPost;
        $standardPost->postid = $post->id;
        $standardPost->text = $validated['content'] ?? null;
        $standardPost->imageurl = $imagePath;
        $standardPost->save();

        return response()->json(['success' => true, 'message' => 'Post created!', 'post_id' => $post->id]);
    }
}
