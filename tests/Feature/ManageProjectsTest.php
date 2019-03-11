<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ManageProjectsTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    /** @test */
    public function a_user_can_view_their_project() {
        $this->be(factory('App\User')->create());

        $this->withoutExceptionHandling();

        //create() persists object in db, make() does not
        $project = factory('App\Project')->create(['owner_id' => auth()->id()]);

        $this->get($project->path())->assertSee($project->title)->assertSee($project->description);
    }

    /** @test */
    public function an_authenticated_user_cannot_view_the_projects_of_others()
    {
        $this->be(factory('App\User')->create());

        //$this->withoutExceptionHandling();

        //create() persists object in db, make() does not
        $project = factory('App\Project')->create();

        $this->get($project->path())->assertStatus(403);
    }

    /** @test */
    public function a_user_can_create_a_project()
    {
        $this->actingAs(factory('App\User')->create());
        $this->withoutExceptionHandling();

        $attributes = [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'owner_id' => auth()->id()
        ];

        $this->post('/projects', $attributes)->assertRedirect('/projects');

        $this->assertDatabaseHas('projects', $attributes);

        //$this->get('/projects')->assertSee($attributes['title']);
    }

    /** @test */
    public function a_project_requires_a_title()
    {
        //$this->withoutExceptionHandling();
        //  need to keep exception handling on in this case, because with it turned off
        //  an exception will be thrown before assertSessionHasErrors() evaluates

        $this->actingAs(factory('App\User')->create());
        
        //use raw() here because it produces an array, not an object
        $attributes = factory('App\Project')->raw(['title' => '']);

        $this->post('/projects', $attributes)->assertSessionHasErrors('title');
    }

    /** @test */
    public function a_project_requires_a_description()
    {
        $this->actingAs(factory('App\User')->create());
        //use raw() here because it produces an array, not an object
        $attributes = factory('App\Project')->raw(['description' => '']);

        $this->post('/projects', $attributes)->assertSessionHasErrors('description');
    }

    /** @test */
    public function guests_cannot_manage_projects()
    {
        //$this->withoutExceptionHandling();

        $project = factory('App\Project')->create();

        $this->post('/projects', $project->toArray())->assertRedirect('login');

        $this->get('/projects/create')->assertRedirect('login');

        $this->get('/projects')->assertRedirect('login');

        $this->get($project->path())->assertRedirect('login');
    }
}
