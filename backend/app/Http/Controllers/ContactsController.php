<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Resources\ContactResource;
use App\Http\Resources\ContactCollection;
use App\Models\Contact;
use App\Http\Requests\ContactUpdateRequest;

class ContactsController extends Controller
{
    /** List all Contacts. */
    public function index()
    {
        return new ContactCollection(Contact::paginate());
    }

    /** Create a new Contact from the given parameters. */
    public function store(Request $request)
    {
        // $data will contain ONLY the validated fields, which means we don't
        // have to worry about extra data sneaking from the request body into the
        // call to create() below.
        $data = $this->validate($request, [
            'first_name' => 'string|required|max:255',
            'last_name' => 'string|present|max:255',
            'email' => 'string|present|max:255',
        ]);

        $contact = Contact::create($data);

        return new ContactResource($contact);
    }

    /** Return a single Contact. */
    public function show(Contact $contact)
    {
        return new ContactResource($contact);
    }

    /** Update a Contact, either wholly via PUT or partially via PATCH. */
    /**
     * TODO make use of the Laravel FormRequest class for validation for
     * less clutter in controllers. Generally, controllers should be used to handle requests and
     * return responses. Validations would preferrably be done separately for good readability and 
     * scalability.
     * 
     * ContactUpdateRequest class has been created as an example
     * 
     * Please check the commented section 
     */
    public function update(ContactUpdateRequest $request, Contact $contact)
    {

        //$data = $request->validated();
        
        if ($request->isMethod('patch')) {
            $data = $this->validate($request, [
                'first_name' => 'string|max:255',
                'last_name' => 'string|max:255',
                'email' => 'string|max:255',
            ]);
        } else {
            $data = $this->validate($request, [
                'first_name' => 'string|required|max:255',
                'last_name' => 'string|present|max:255', //Task 1. changed 'first_name' to 'last_name' for validation and data store.
                'email' => 'string|present|max:255',
            ]);
        }

        $contact->update($data);

        return new ContactResource($contact);
    }

    /** (Soft-)delete the Contact. */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return response()->noContent();
    }
}
