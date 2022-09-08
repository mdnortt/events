<?php
/**
 * Event service.
 */

namespace App\Service;

use App\Entity\Event;
use App\Entity\User;
use App\Repository\EventRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class EventService.
 */
class EventService implements EventServiceInterface
{

    /**
     * Category service.
     */
    private CategoryServiceInterface $categoryService;

    /**
     * Event repository.
     */
    private EventRepository $eventRepository;
    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

     /** Constructor.
     *
     * @param CategoryServiceInterface $categoryService Category service
     * @param PaginatorInterface       $paginator       Paginator
     * @param EventRepository          $eventRepository Event repository
     */
    public function __construct(CategoryServiceInterface $categoryService, PaginatorInterface $paginator, EventRepository $eventRepository)
    {
        $this->categoryService = $categoryService;
        $this->paginator = $paginator;
        $this->eventRepository = $eventRepository;
    }

//    /**
//     * Constructor.
//     *
//     * @param CategoryServiceInterface $categoryService Category service
//     * @param PaginatorInterface       $paginator       Paginator
//     * @param EventRepository          $eventRepository Event repository
//     */
//    public function __construct(CategoryServiceInterface $categoryService, PaginatorInterface $paginator, EventRepository $eventRepository)
//    {
//        $this->categoryService = $categoryService;
//        $this->paginator = $paginator;
//        $this->eventRepository = $eventRepository;
//    }
//    /**
//     * @param CategoryServiceInterface $categoryService
//     */
//    public function __construct(CategoryServiceInterface $categoryService)
//    {
//        $this->categoryService = $categoryService;
//    }

//    /**
//     * @param int $page costam
//     * @return PaginationInterface costam
//     */
//    public function getPaginatedList(int $page): PaginationInterface
//    {
//        return $this->paginator->paginate(
//            $this->eventRepository->queryAll(),
//            $page,
//            EventRepository::PAGINATOR_ITEMS_PER_PAGE
//        );
//    }
    /**
     * Get paginated list.
     *
     * @param int                $page    Page number
     * @param array<string, int> $filters Filters array
     *
     * @return PaginationInterface<SlidingPagination> Paginated list
     */
    public function getPaginatedList(int $page, array $filters = []): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        return $this->paginator->paginate(
            $this->eventRepository->queryAll($filters),
            $page,
            EventRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * @param Event $event Event
     *
     * @return void Void
     */
    public function save(Event $event): void
    {
        if (null === $event->getId()) {
            $event->setCreatedAt(new \DateTimeImmutable());
        }
        $event->setUpdatedAt(new \DateTimeImmutable());
        $this->eventRepository->save($event);
    }

    /**
     * Delete entity.
     *
     * @param Event $event Event entity
     */
    public function delete(Event $event): void
    {
        $this->eventRepository->delete($event);
    }

    /**
     * Prepare filters for the tasks list.
     *
     * @param array<string, int> $filters Raw filters from request
     *
     * @return array<string, object> Result array of filters
     */
    private function prepareFilters(array $filters): array
    {
        $resultFilters = [];
        if (!empty($filters['category_id'])) {
            $category = $this->categoryService->findOneById($filters['category_id']);
            if (null !== $category) {
                $resultFilters['category'] = $category;
            }
        }

        return $resultFilters;
    }
}
