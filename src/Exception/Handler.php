<?php

namespace ExmentAdminCore\Admin\Exception;

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

class Handler
{
    /**
     * Render exception.
     *
     * @param \Exception $exception
     *
     * @return string
     */
    public static function renderException(\Exception $exception)
    {
        \Log::error($exception);
        $error = new MessageBag([
            'type'    => get_class($exception),
            'message' => $exception->getMessage(),
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
            'trace'   => $exception->getTraceAsString(),
        ]);

        $errors = new ViewErrorBag();
        $errors->put('exception', $error);

        return view('admin::partials.exception', [
            'errors' => $errors,
            'isShowDetail' => boolval(config('app.debug', false)),
        ])->render();
    }

    /**
     * Flash a error message to content.
     *
     * @param string|\Illuminate\Contracts\Support\Htmlable $title
     * @param string|\Illuminate\Contracts\Support\Htmlable $message shown as text, unless an Htmlable
     *
     * @return void
     */
    public static function error($title = '', $message = '')
    {
        $title = admin_message_value($title);
        $message = admin_message_value($message);

        $error = new MessageBag(compact('title', 'message'));

        session()->flash('error', $error);
    }
}
