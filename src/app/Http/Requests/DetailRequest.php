<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Carbon\Carbon;

class DetailRequest extends FormRequest
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
            'comment' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'comment.required' => '備考を記入してください',
        ];
    }

    protected function prepareForValidation(): void
    {
        $fix = function ($v) {
            if ($v === null) return null;
            $v = trim($v);

            $v = str_replace('：', ':', $v);

            if (preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $v)) {
                $v = substr($v, 0, 5);
            }
            return $v;
        };

        $this->merge([
            'start_time' => $fix($this->input('start_time')),
            'end_time'   => $fix($this->input('end_time')),
            'break_in'   => $fix($this->input('break_in')),
            'break_out'  => $fix($this->input('break_out')),
        ]);
    }


    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $start = $this->input('start_time');
            $end = $this->input('end_time');
            $breakIn = $this->input('break_in');
            $breakOut = $this->input('break_out');

            if ($start && $end) {
                $startTime = Carbon::createFromFormat('H:i', $start);
                $endTime = Carbon::createFromFormat('H:i', $end);
                if ($startTime->gt($endTime)) {
                    $validator->errors()->add('start_time', '出勤時間もしくは退勤時間が不適切です');
                }
            }

            if ($start && $end && $breakIn && $breakOut) {
                $startTime = Carbon::createFromFormat('H:i', $start);
                $endTime = Carbon::createFromFormat('H:i', $end);
                $breakInTime = Carbon::createFromFormat('H:i', $breakIn);
                $breakOutTime = Carbon::createFromFormat('H:i', $breakOut);

                if ($breakInTime->lt($startTime) || $breakInTime->gt($endTime)) {
                    $validator->errors()->add('break_in', '休憩時間が勤務時間外です');
                }
            
                if ($breakOutTime->lt($startTime) || $breakOutTime->gt($endTime)) {
                    $validator->errors()->add('break_out', '休憩時間が勤務時間外です');
                }
            }
        });
    }
}
