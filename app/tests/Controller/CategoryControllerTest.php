<?php

namespace Controller;

/**
 * Category Controller test.
 */

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


/**
 * Class CategoryControllerTest.
 */
class CategoryControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/category';

    /**
     * Create user.
     *
     * @param array $roles User roles
     *
     * @return User User entity
     *
     */
    protected function createUser(array $roles, string $email): User
    {
        $passwordHasher = static::getContainer()->get('security.password_hasher');
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                'p@55w0rd'
            )
        );
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->save($user);

        return $user;
    }


    /**
     * Set up tests.
     */
    public function setUp(): void
    {
        $this->httpClient = static::createClient();
    }



    /**
     * @return void
     */
    public function testIndexRouteAnonymousUser(): void
    {
        // given
        $expectedStatusCode = 200;
        $user = null;
        $user = $this->createUser([ UserRole::ROLE_USER->value], 'category_1@example.com');
        $this->httpClient->loginUser($user);
        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }



    //create category
    public function testCreateCategory(): void
    {
        // given
        $user = $this->createUser([ UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value], 'category_5@example.com');

        $this->httpClient->loginUser($user);
        $categoryCategoryTitle = "createdCategor";
        $categoryRepository = static::getContainer()->get(CategoryRepository::class);

        $this->httpClient->request('GET', self::TEST_ROUTE . '/create');
        // when
        $this->httpClient->submitForm(
            'Zapisz',
            ['category' => ['title' => $categoryCategoryTitle]]
        );

        // then
        $savedCategory = $categoryRepository->findOneByTitle($categoryCategoryTitle);
        $this->assertEquals(
            $categoryCategoryTitle,
            $savedCategory->getTitle()
        );


        $result = $this->httpClient->getResponse();
        $this->assertEquals(302, $result->getStatusCode());
    }




    /**
     * @return void
     */
    public function testEditCategory(): void
    {
        // given
        $user = $this->createUser([ UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value], 'category_7@example.com');
        $this->httpClient->loginUser($user);

        $categoryRepository =
            static::getContainer()->get(CategoryRepository::class);
        $testCategory = new Category();
        $testCategory->setTitle('TestCategory');
        $testCategory->setCreatedAt(new DateTimeImmutable('now'));
        $testCategory->setUpdatedAt(new DateTimeImmutable('now'));
        $categoryRepository->save($testCategory);
        $testCategoryId = $testCategory->getId();
        $expectedNewCategoryTitle = 'TestCategoryEdit';

        $this->httpClient->request('GET', self::TEST_ROUTE . '/' .
            $testCategoryId . '/edit');

        // when
        $this->httpClient->submitForm(
            'Zapisz',
            ['category' => ['title' => $expectedNewCategoryTitle]]
        );

        // then
        $savedCategory = $categoryRepository->findOneById($testCategoryId);
        $this->assertEquals(
            $expectedNewCategoryTitle,
            $savedCategory->getTitle()
        );
    }



    /**
     * @return void
     */
    public function testDeleteCategory(): void
    {
        // given

        $user = $this->createUser([ UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value], 'category_9@example.com');
        $this->httpClient->loginUser($user);

        $categoryRepository =
            static::getContainer()->get(CategoryRepository::class);
        $testCategory = new Category();
        $testCategory->setTitle('TestCategoryCreated');
        $testCategory->setCreatedAt(new DateTimeImmutable('now'));
        $testCategory->setUpdatedAt(new DateTimeImmutable('now'));
        $categoryRepository->save($testCategory);
        $testCategoryId = $testCategory->getId();

        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $testCategoryId . '/delete');

        //when
        $this->httpClient->submitForm(
            'Usuń'
        );

        // then
        $this->assertNull($categoryRepository->findOneByTitle('TestCategoryCreated'));
    }

    /**
     * @return void
     */
    public function testCantDeleteCategory(): void
    {
        // given


        $categoryRepository =
            static::getContainer()->get(CategoryRepository::class);
        $testCategory = new Category();
        $testCategory->setTitle('TestCategoryCreated2');
        $testCategory->setCreatedAt(new DateTimeImmutable('now'));
        $testCategory->setUpdatedAt(new DateTimeImmutable('now'));
        $categoryRepository->save($testCategory);
        $testCategoryId = $testCategory->getId();



        //when
        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $testCategoryId . '/delete');

        // then
        $this->assertEquals(302, $this->httpClient->getResponse()->getStatusCode());
        $this->assertNotNull($categoryRepository->findOneByTitle('TestCategoryCreated2'));
    }
}
