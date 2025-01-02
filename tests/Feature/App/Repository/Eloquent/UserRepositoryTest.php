<?php

use App\Models\User;
use App\Repository\Contracts\UserRepositoryInterface;
use App\Repository\Eloquent\UserRepository;
use App\Repository\Exceptions\NotFoundException;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    protected $repository;

    protected function setUp(): void
    {
        $this->repository = new UserRepository(new User());

        parent::setUp();
    }

    public function testImplementsInterface()
    {
        $this->assertInstanceOf(
            UserRepositoryInterface::class,
            $this->repository
        );
    }

    public function testFindAllEmpty()
    {
        $response = $this->repository->findAll();

        $this->assertIsArray($response);
        $this->assertCount(0, $response);
    }

    public function testFindAll()
    {
        User::factory()->count(10)->create();

        $response = $this->repository->findAll();

        $this->assertCount(10, $response);
    }

    public function testCreate()
    {
        $data = [
            'name' => 'fake_name',
            'email' => 'fake_email@gmail.com.br',
            'password' => bcrypt('fake_password@'),
        ];

        $response = $this->repository->create($data);

        $this->assertNotNull($response);
        $this->assertIsObject($response);
        $this->assertDatabaseHas('users', [
            'name' => $data['name'],
            'email' => $data['email'],
        ]);
    }

    public function testCreateException()
    {
        $this->expectException(QueryException::class);

        $data = [
            'name' => 'fake_name',
            'password' => bcrypt('fake_password@'),
        ];

        $this->repository->create($data);
    }

    public function testUpdate()
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'new fake_name',
        ];

        $response = $this->repository->update($user->email, $data);

        $this->assertNotNull($response);
        $this->assertIsObject($response);
        $this->assertDatabaseHas('users', [
            'name' => $data['name'],
        ]);
    }

    public function testDelete()
    {
        $user = User::factory()->create();

        $deleted = $this->repository->delete($user->email);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('users', [
            'email' => $user->email,
        ]);
    }

    public function testDeleteNotFound()
    {
        $this->expectException(NotFoundException::class);
        $this->repository->delete('fake_email');
    }

    public function testFindNotFound()
    {
        $response = $this->repository->find('fake_email');

        $this->assertNull($response);
    }
}
