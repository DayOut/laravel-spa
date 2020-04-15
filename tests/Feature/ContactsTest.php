<?php

namespace Tests\Feature;

use App\Contact;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use function foo\func;

class ContactsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    /** @test */
    public function a_list_of_contacts_can_be_fetched_for_the_authenticated_user()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $anotherUser = factory(User::class)->create();

        $contact = factory(Contact::class)->create(['user_id' => $user->id]);
        $anotherContact = factory(Contact::class)->create(['user_id' => $anotherUser->id]);

        $response = $this->get('api/contacts?api_token=' . $user->api_token);

        //dd(json_decode($response->getContent()));

        $response->assertJsonCount(1)->assertJson([
            'data' => [
                ['contact_id' => $contact->id]
            ]
        ]);
    }


    /** @test */
    public function is_unauthenticated_user_should_be_redirected_to_login()
    {
        $response = $this->post('/api/contacts' ,
            array_merge($this->data(), ['api_token' => '']));
        $response->assertRedirect('/login');
        $this->assertCount(0, Contact::all());
    }


    /** @test */
    public function an_authenticated_user_can_add_a_contact()
    {
        $this->withoutExceptionHandling();

        $this->post('/api/contacts' ,
            array_merge($this->data()));

        $contact = Contact::first();
        //$this->assertCount(1, $contact);
        $this->assertEquals('Test Name', $contact->name);
        $this->assertEquals('test@mail.com', $contact->email);
        $this->assertEquals('02/14/1995', $contact->birthday->format('m/d/Y'));
        $this->assertEquals('ABC string', $contact->company);

    }

    /** @test */
    public function fields_are_required()
    {
        collect(['name', 'email', 'birthday', 'company'])
            ->each(function($field){
                $response = $this->post('/api/contacts' ,
                    array_merge($this->data(), [$field => '']));

                $contact = Contact::first();

                $response->assertSessionHasErrors($field);
                $this->assertCount(0, Contact::all());
        });
    }

    /** @test */
    public function is_email_valid()
    {
        $response = $this->post('/api/contacts' ,
            array_merge($this->data(), ['email' => 'Not an email']));

        $contact = Contact::first();

        $response->assertSessionHasErrors('email');
        $this->assertCount(0, Contact::all());
    }

    /** @test */
    public function birthday_are_properly_stored()
    {
        $response = $this->post('/api/contacts',
            array_merge($this->data()));

        $this->assertCount(1, Contact::all());
        $this->assertInstanceOf(Carbon::class, Contact::first()->birthday);
        $this->assertEquals('02-14-1995', Contact::first()->birthday->format('m-d-Y'));
    }


    /** @test */
    public function a_contact_can_be_retrieved()
    {
        $contact = factory(Contact::class)->create(['user_id' => $this->user->id]);

        $response = $this->get('/api/contacts/' . $contact->id . '?api_token=' . $this->user->api_token);

        //dd(json_decode($response->getContent()));
        $response->assertJson([
            'data' =>
                [
                    'contact_id' => $contact->id,
                    'name' => $contact->name,
                    'email' => $contact->email,
                    'company' => $contact->company,
                    'birthday' => $contact->birthday->format("m/d/Y"),
                    'last_updated' => $contact->updated_at->diffForHumans(),
                ]
            ]);
    }

    /** @test */
    public function only_the_users_contacts_can_be_retrieved()
    {
        $contact = factory(Contact::class)->create(['user_id' => $this->user->id]);

        $anotherUser = factory(User::class)->create();

        $response = $this->get('/api/contacts/' . $contact->id . '?api_token=' . $anotherUser->api_token);

        $response->assertStatus(403);
    }

    /** @test */
    public function a_contact_can_be_patched()
    {
        $this->withoutExceptionHandling();

        $contact = factory(Contact::class)->create(['user_id' => $this->user->id]);

        $response = $this->patch('/api/contacts/' . $contact->id, $this->data());

        $contact = $contact->fresh();

        $this->assertEquals('Test Name', $contact->name);
        $this->assertEquals('test@mail.com', $contact->email);
        $this->assertEquals('02/14/1995', $contact->birthday->format('m/d/Y'));
        $this->assertEquals('ABC string', $contact->company);
    }

    /** @test */
    public function only_the_owner_of_the_contact_can_patch_the_contact()
    {
        $contact = factory(Contact::class)->create(['user_id' => $this->user->id]);
        $anotherUser = factory(User::class)->create();


        $response = $this->patch('/api/contacts/' . $contact->id,
            array_merge($this->data(), ['api_token' => $anotherUser->api_token]));

        $response->assertStatus(403);

    }

    /** @test */
    public function a_contact_can_be_deleted()
    {
        $contact = factory(Contact::class)->create(['user_id' => $this->user->id]);

        $response = $this->delete('/api/contacts/' . $contact->id,
            ['api_token' => $this->user->api_token]);

        $this->assertCount(0, Contact::all());
    }

    /** @test */
    public function only_the_owner_can_delete_the_contact()
    {
        $contact = factory(Contact::class)->create(['user_id' => $this->user->id]);
        $anotherUser = factory(User::class)->create();

        $response = $this->delete('/api/contacts/' . $contact->id,
            ['api_token' => $anotherUser->api_token]);

        $response->assertStatus(403);
    }


    /**
     * Valid array with data
     *
     * @return array
     */
    protected function data()
    {
        return [
            'name' => 'Test Name',
            'email' => 'test@mail.com',
            'birthday' => '02/14/1995',
            'company' => 'ABC string',
            'api_token' => $this->user->api_token
        ];
    }
}
