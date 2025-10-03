<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\NotificationUserModel;
use Illuminate\Http\Request;

class NotificationUserController extends Controller
{
    public function set_status_notification(Request $request){

        // NotificationUserModel::where('id', '=', $request->input('id'))->update(['status' => 1]);

        // return response()->json([
        //     'id' => $request->input('id')
        // ]);
    }

    public function get_notification(Request $request){

        $count = $request->user()->notifaction_recipient->count();

        $notifications = [];

        if ($request->user()->percent < 10) {
            $count++;

            array_push($notifications, [
                'id' => 0,
                'title' => 'Рейтинг',
                'message' => 'Ваш рейтинг ниже 10%',
                'date' => date('d.m.Y')
            ]);
        }

        foreach ($request->user()->notifaction_recipient as $item) {
            array_push($notifications, [
                'id' => $item->id,
                'title' => $item->title,
                'message' => $item->message,
                'date' => $item->date_create()
            ]);
        }

        return response()->json([
            'count' => $count,
            'notifications' => $notifications
        ]);
    }
}
