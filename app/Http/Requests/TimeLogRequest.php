<?php

namespace App\Http\Requests;

use App\Models\TimeLog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class TimeLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->isMethod('post')) {
            // For creating a time log (store)
            return [
                'project_id' => ['required', 'exists:projects,id'],
                'start_time' => ['nullable', 'date'],
                'end_time' => ['nullable', 'date', 'after:start_time'],
                'description' => ['required', 'string'],
                'hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
                'is_billable' => ['boolean'],
                'tags' => ['nullable', 'array'],
                'tags.*' => ['string', 'max:50'],
            ];
        }
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            // For updating a time log (update)
            return [
                'project_id' => ['sometimes', 'exists:projects,id'],
                'start_time' => ['sometimes', 'nullable', 'date'],
                'end_time' => ['sometimes', 'nullable', 'date', 'after:start_time'],
                'description' => ['sometimes', 'nullable', 'string'],
                'hours' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:24'],
                'is_billable' => ['sometimes', 'boolean'],
                'tags' => ['sometimes', 'nullable', 'array'],
                'tags.*' => ['string', 'max:50'],
            ];
        }
        // Default fallback
        return [];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Only check for running logs when creating a time log without an end_time
            if ($this->isMethod('post') && 
                (!$this->has('end_time') || $this->end_time === null)) {
                
                $user = $this->user();
                
                // Check if user already has a running time log
                $hasRunningLog = TimeLog::query()
                    ->whereHas('project.client', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->running()
                    ->exists();

                if ($hasRunningLog) {
                    $validator->errors()->add('end_time', 'You already have a running time log. Please stop it before starting a new one.');
                }
            }
        });
    }
}
