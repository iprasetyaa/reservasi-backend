<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommandCenterReservationApprovalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'approval_status' => 'required|in:ALREADY_APPROVED,REJECTED',
            'note' => 'strong|required|max:255',
        ];
    }
}
