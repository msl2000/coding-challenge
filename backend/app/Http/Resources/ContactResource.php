<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'type' => 'contacts',
            'id' => $this->uuid,
            'attributes' => [
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'created_at' => $this->created_at->toAtomString(),
                'updated_at' => $this->updated_at->toAtomString(),
            ],
            'links' => [
                'self' => [
                    'href' => self::getSelfLink($this),
                ],
            ],
        ];
    }

    public static function getSelfLink($item)
    {
        return action('ContactsController@show', ['contact' => $item->uuid]);
    }
}
