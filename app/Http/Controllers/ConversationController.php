<?php

namespace App\Http\Controllers;

use App\Events\ConversationSent;
use App\Exceptions\CustomBaseException;
use App\Models\Conversation;
use App\Models\Gym;
use App\Models\MeetRegistration;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Illuminate\Support\Facades\Log;
/**
 * Class ConversationController
 */
class ConversationController extends AppBaseController
{
    /**
     * @param  Gym  $gym
     *
     * @return Application|Factory|View
     *
     * @throws CustomBaseException
     */
    public function index(Gym $gym)
    {
        $currentUserGym = Gym::where('id', $gym->id)->whereIn('id', \Auth::user()->gyms()->pluck('id')->toArray())->count();
        if ($currentUserGym <= 0) {
            throw new CustomBaseException('Gym Not Found.');
        }

        $conversationUsers = Conversation::with(['receiver', 'gym'])
            ->orWhere('to_id', $gym->id)
            ->orWhere('from_id', $gym->id)
            ->orderBy('created_at')
            ->get();

        // display conversation user on sidebar
        /** @var Conversation $conversationUsers */
        $uniqueConversationUser = collect();

        foreach ($conversationUsers as $conversationUser) {
            if ($uniqueConversationUser->where('to_id', $conversationUser->from_id)->count()) {
                continue;
            }
            if ($uniqueConversationUser->where('from_id', $conversationUser->to_id)->count()) {
                continue;
            }
            if ($uniqueConversationUser->where('to_id', $conversationUser->to_id)->count()) {
                continue;
            }
            $uniqueConversationUser->push($conversationUser);
        }

        return view('conversation.conversation-dashboard', [
            'current_page' => 'gyms'.$gym->id.'/conversation',
            'gym' => $gym,
            'conversationUsers' => $uniqueConversationUser,
            ]);
    }

    /**
     * @param  Gym  $gym
     * @param $id
     *
     * @return JsonResponse
     */
    public function addConversationUser(Gym $gym, $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $currentGym = Gym::with(['meets'])->where('id', $gym->id)->get();
            $currentGymMeets = $currentGym->pluck('meets')->collapse()->pluck('id')->toArray();

            $selectedUserGym = Gym::where('id', $id)->first();
            $meetRegistrations = $selectedUserGym->registrations()->whereIn('meet_id', $currentGymMeets)
                ->select([
                    'id', 'gym_id', 'meet_id', 'status'
                ])->where('status', '!=', MeetRegistration::STATUS_CANCELED)
                ->with([
                    'meet' => function ($q) {
                        $q->select([
                            'id', 'gym_id', 'profile_picture', 'name',
                        ]);
                    },
                    'meet.gym' => function ($q) {
                        $q->select([
                            'id', 'name', 'short_name', 'user_id'
                        ]);
                    },
                    'meet.gym.user' => function ($q) {
                        $q->select([
                            'id', 'profile_picture'
                        ]);
                    },
                ])->get()->unique('meet_id')->toArray();

            $users = [];
            foreach ($meetRegistrations as $meet) {
                $users[] = array(
                    'meetName' => $meet['meet']['name'],
                    'gymName' => $meet['meet']['gym']['name'],
                    'gymId' => $meet['meet']['gym']['id'],
                    'profilePicture' => $meet['meet']['gym']['user']['profile_picture'],
                );
            }

            // on conversation table read_at date fill
            Conversation::where('read_at', null)->where('from_id', $id)->where('to_id', $gym->id)->update(['read_at' => Carbon::now()]);

            // conversation data
            $conversationData = [];
            /** @var Conversation $conversations */
            $conversations = Conversation::with(['sender', 'receiver'])
                     ->where(function (Builder $q) use ($gym, $id) {
                         $q->where(function (Builder $q) use ($gym, $id) {
                             $q->where('from_id', '=', $gym->id);
                             $q->where('to_id', '=', $id);
                         })->orWhere(function (Builder $q) use ($gym, $id) {
                             $q->where('to_id', '=', $gym->id);
                             $q->where('from_id', '=', $id);
                         });
                     })
                ->orderBy('created_at')->get();

            foreach ($conversations as $conversation) {
                $currentDate = strtotime(date("Y-m-d"));
                $conversationDate = strtotime($conversation->created_at);
                $dateDiff = $conversationDate - $currentDate;
                $difference = floor($dateDiff/(60*60*24));
                if($difference==0) {
                    $date = 'Today';
                } else if($difference < -1) {
                    $date = Carbon::parse($conversation->created_at)->format('D,M jS Y');
                } else {
                    $date = 'Yesterday';
                }
                $conversationData[] = array(
                    'date' => $date,
                    'senderMessage' => ($conversation->from_id == $gym->id),
                    'receiveMessage' => ($conversation->to_id == $gym->id),
                    'message' => $conversation->message,
                    'time' => Carbon::parse($conversation->created_at)->format(' H:i A'),
                    'senderImage' => $conversation->sender->profile_picture,
                    'receiverImage' => $conversation->receiver->profile_picture,
                );
            }

            $data['users'] = $users;
            $data['conversation'] = collect($conversationData)->groupBy('date');
            $data['gym'] = Gym::with(['user'])->where('id', $id)->first();
            DB::commit();

            return $this->sendResponse($data, 'User Retrieved Successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * @param  Gym  $gym
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function sendMessage(Gym $gym, Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $input = $request->all();
            $conversation = Conversation::create([
                'message' => $input['message'],
                'from_id' => $gym->id,
                'to_id' => $input['toId'],
            ]);
            $currentDate = strtotime(date("Y-m-d"));
            $conversationDate = strtotime($conversation->created_at);
            $dateDiff = $conversationDate - $currentDate;
            $difference = floor($dateDiff/(60*60*24));
            if($difference==0) {
                $date = 'Today';
            } else if($difference < -1) {
                $date = Carbon::parse($conversation->created_at)->format('D,M jS Y');
            } else {
                $date = 'Yesterday';
            }
            $data = [
                'date' => $date,
                'senderMessage' => !($conversation->from_id == $gym->id),
                'receiveMessage' =>! ($conversation->to_id == $gym->id),
                'message' => $conversation->message,
                'time' => Carbon::parse($conversation->created_at)->format(' H:i A'),
                'senderImage' => $conversation->sender->profile_picture,
                'receiverImage' => $conversation->receiver->profile_picture,
                'receiverGymId' => $input['toId'],
                'senderGymId' => $gym->id,
                'type' => Conversation::CONVERSATION,
                'gym' => Gym::with(['user'])->where('id', $gym->id)->first()
            ];
            // Log::info('broadcastOn controller' . $input['toId']);
            broadcast(new ConversationSent($data, $input['toId']))->toOthers();
            //event(new ConversationSent($data, $input['toId']));
            
            DB::commit();
            return $this->sendResponse($input['toId'], 'User Retrieved Successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    /**
     * @param  Gym  $gym
     *
     * @return JsonResponse
     */
    public function displayMeetRegisterUser(Gym $gym): JsonResponse
    {
        try {
            $currentGym = Gym::with(['meets'])->where('id', $gym->id)->get();
            $currentGymMeetsIds = $currentGym->pluck('meets')->collapse()
                ->pluck('registrations')->collapse()->unique('gym_id')
                ->pluck('gym_id')->toArray();
            $conversationUsers = Conversation::with(['receiver', 'gym'])
                ->orWhere('to_id', $gym->id)
                ->orWhere('from_id', $gym->id)
                ->orderBy('created_at')
                ->get();

            $gyms = Gym::with(['user'])->whereIn('id', $currentGymMeetsIds)
                ->whereNotIn('id', $conversationUsers->where('from_id', $gym->id)
                    ->where('to_id', '!=', $gym->id)
                    ->pluck('to_id')->toArray())->get();
            $data = [];
            foreach ($gyms as $gym) {
                $data[] = [
                    'id' => $gym->id,
                    'name' => $gym->name,
                    'email' => $gym->user->email,
                    'image' => $gym->profile_picture,
                ];
            }

            return $this->sendResponse($data, 'Gym Retrieved Successfully.');
        } catch (Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    /**
     * @param  Gym  $gym
     *
     * @return JsonResponse
     */
    public function unreadCount(Gym $gym): JsonResponse
    {
        $currentUserGym = Gym::where('id', $gym->id)->whereIn('id', \Auth::user()->gyms()->pluck('id')->toArray())->count();
        if ($currentUserGym <= 0) {
            echo "no data";die;
        }
        
        try {
            $unReadCount = Conversation::where('to_id', $gym->id)->where('read_at', null)->select('from_id')->distinct()->get()->count();

            return $this->sendResponse($unReadCount, 'Unread Message Count Retrieved Successfully');
        } catch (Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    public function realTimeReadAt(Gym $gym, $senderGymId)
    {
        Conversation::where('read_at', null)
            ->where('from_id', $senderGymId)
            ->where('to_id', $gym->id)
            ->update(['read_at' => Carbon::now()]);

        return $this->sendSuccess('Conversation Read Successfully.');
    }
}
