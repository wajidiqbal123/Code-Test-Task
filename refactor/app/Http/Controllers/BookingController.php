<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{

    /**
     * @var BookingRepository
     */
    protected $repository;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $userType = $request->__authenticatedUser->user_type;
        $response = null;
        if ($request->has('user_id')) {
            $response = $this->repository->getUsersJobs($request->user_id);
        } elseif ($userType == env('ADMIN_ROLE_ID') || $userType == env('SUPERADMIN_ROLE_ID')) {
            $response = $this->repository->getAll($request);
        } 
        return response($response);
    }
    
    /**
     * @param $id
     * @return mixed
     */

    public function show($id)
    {
        try{
          $job = $this->repository->with(['translatorJobRel.user'])->findOrFail($id);
          return response($job);
        }catch (ModelNotFoundException $exception){
          return response(['success' => 'Job not found']);
        }
    }


    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $data = $request->all();

        $response = $this->repository->store($request->__authenticatedUser, $data);

        return response($response);

    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update($id, Request $request)
    {
        $data = $request->all();
        $cuser = $request->__authenticatedUser;
        $response = $this->repository->updateJob($id, array_except($data, ['_token', 'submit']), $cuser);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->storeJobEmail($data);
        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {

        $userId   = $request->get('user_id');
        $history  = null;
        if(!empty($userId)) {
            $history = $this->repository->getUsersJobsHistory($userId, $request);
        }
        return response($history);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->repository->acceptJob($data, $user);

        return response($response);
    }

    public function acceptJobWithId(Request $request)
    {
        $data = $request->get('job_id');
        $user = $request->__authenticatedUser;
        $response = null;
        if(!empty($data) && !empty($user)){
            $response = $this->repository->acceptJobWithId($data, $user);
        }
        return response($response);

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->repository->cancelJobAjax($data, $user);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request)
    {
        $data = $request->all();

        $response = $this->repository->endJob($data);

        return response($response);

    }

    public function customerNotCall(Request $request)
    {
        $data = $request->all();

        $response = $this->repository->customerNotCall($data);

        return response($response);

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request)
    {
        $user = $request->__authenticatedUser;

        $response = $this->repository->getPotentialJobs($user);

        return response($response);
    }

    public function distanceFeed(Request $request)
    {
       
        $data = $request->all();
        $distance = $time = $jobid = $session = $admincomment = '';
        $flagged = $manually_handled = $by_admin = 'no';
        if (!empty($data['distance'])) {
            $distance = $data['distance'];
        }
        if (!empty($data['time'])) {
            $time = $data['time'];
        }
        if (!empty($data['jobid'])) {
            $jobid = $data['jobid'];
        }
        if (!empty($data['session_time'])) {
            $session = $data['session_time'];
        }
        if ($data['flagged'] == 'true') {
            if (empty($data['admincomment'])) {
               return "Please, add comment";
            }
            $flagged = 'yes';
        }
        if ($data['manually_handled'] == 'true') {
            $manually_handled = 'yes';
        }
        if ($data['by_admin'] == 'true') {
            $by_admin = 'yes';
        }

        if (!empty($data['admincomment'])) {
            $admincomment = $data['admincomment'];
        }

        if (!empty($time) || !empty($distance)) {
            Distance::where('job_id', '=', $jobid)->update(['distance' => $distance, 'time' => $time]);
        }

        if (!empty($admincomment) || !empty($session) || $flagged || $manually_handled || $by_admin ) {
            Job::where('id', '=', $jobid)->update(['admin_comments' => $admincomment, 'flagged' => $flagged, 'session_time' => $session, 'manually_handled' => $manually_handled, 'by_admin' => $by_admin]);
        }

        return response('Record updated!');

    }

    public function reopen(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->reopen($data);

        return response($response);
    }

    public function resendNotifications(Request $request)
    {
        $data = $request->all();
        $response = ['success' => 'Notfication not push'];
        if(!empty($data['jobid'])){
            $job = $this->repository->find($data['jobid']);
            if(!empty($job)){
               $job_data = $this->repository->jobToData($job);
               if(!empty($job) && !empty($job_data)){
                   $this->repository->sendNotificationTranslator($job, $job_data, '*');
                   $response = ['success' => 'Notfication sent'];
               }
            }
        }
        return response($response);
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        try {
            if(!empty($job)){
                $this->repository->sendSMSNotificationToTranslator($job);
                return response(['success' => 'SMS sent']);
            }else{
                return response(['success' => 'SMS not sent']);
            }
        } catch (\Exception $e) {
            return response(['success' => $e->getMessage()]);
        }

    }

}
