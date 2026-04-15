<?php

namespace App\Http\Requests\Api\Integrations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GoogleCalendarSelectCalendarsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $businessId = (int) $this->input('business_id');

        return [
            'business_id' => ['required', 'integer', 'exists:negocios,id'],
            'calendars' => ['required', 'array'],
            'calendars.*.google_calendar_id' => ['required', 'string', 'max:255'],
            'calendars.*.summary' => ['nullable', 'string', 'max:255'],
            'calendars.*.timezone' => ['nullable', 'string', 'max:255'],
            'calendars.*.is_primary' => ['nullable', 'boolean'],
            'calendars.*.selected' => ['nullable', 'boolean'],
            'calendars.*.resource_id' => [
                'nullable',
                'integer',
                Rule::exists('recursos', 'id')->where(fn ($query) => $query->where('negocio_id', $businessId)),
            ],
            'calendars.*.background_color' => ['nullable', 'string', 'max:20'],
            'calendars.*.access_role' => ['nullable', 'string', 'max:50'],
        ];
    }
}
