<?php

use dutchbridge\validators\UserLogValidator;
use Hideyo\Backend\Repositories\UserLogRepositoryInterface;

class UserLogController extends BaseController
{
    public function __construct(UserLogRepositoryInterface $user_log)
    {
        $this->user_log = $user_log;
    }

    public function index()
    {

        if (Request::wantsJson()) {

            return \Datatable::collection($this->user_log->selectAll())
                      ->showColumns('id', 'type', 'message')
            
                ->searchColumns('type')
                ->orderColumns('id')
                ->make();

        } else {
            return \View::make('user_log.index')->with('user_log', $this->user_log->selectAll());
        }
    }

    public function create()
    {
        return \View::make('user_log.create');
    }


    public function store()
    {
        $result  = $this->user_log->create(Request::all());
 
        if ($result->id) {
            Notification::success('The user_log was inserted.');
            return Redirect::route('user_log.index');
        } else {
            Notification::error('field are required');
        }

        return Redirect::back()->withInput()->withErrors($result->errors()->all());
    }

    public function edit($id)
    {
        return \View::make('user_log.edit')->with(array('user_log' => UserLog::find($id)));
    }

    public function update($id)
    {
        $result  = $this->user_log->updateById(Request::all(), $id);

        if ($result->errors()->all()) {
            return Redirect::back()->withInput()->withErrors($result->errors()->all());
        } else {
            Log::info('This is some useful information.');
            Notification::success('The user_log was updated.');
            return Redirect::route('user_log.index');
        }
    }

    public function destroy($id)
    {
        $result  = $this->user_log->destroy($id);

        if ($result) {
            Notification::success('The user_log was deleted.');
            return Redirect::route('user_log.index');
        }
    }
}
