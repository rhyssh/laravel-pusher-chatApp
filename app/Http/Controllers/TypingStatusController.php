<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\StatusTyping;
use App\Models\User;

class TypingStatusController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'isTyping' => 'required|boolean',
        ]);

        $user = User::findOrFail($request->user_id);

        broadcast(new StatusTyping($user, $request->isTyping))->toOthers();

        return response()->json(['status' => 'typing event broadcasted']);
    }
}
