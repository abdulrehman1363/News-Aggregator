<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPreferenceWithMessageResource extends JsonResource
{
    protected $message;

    public function __construct($resource, $message = null)
    {
        parent::__construct($resource);
        $this->message = $message;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'preferred_sources' => $this->preferred_sources,
            'preferred_categories' => $this->preferred_categories,
            'preferred_authors' => $this->preferred_authors,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        $data = [];
        
        if ($this->message) {
            $data['message'] = $this->message;
        }
        
        return $data;
    }
}
