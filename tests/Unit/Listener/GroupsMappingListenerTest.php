<?php

declare(strict_types=1);

namespace OCA\OidcGroupsMapping\Tests\Unit\Listener;

use OCA\OidcGroupsMapping\Listener\GroupsMappingListener;
use OCA\OidcGroupsMapping\Service\MappingService;
use OCA\UserOIDC\Event\AttributeMappedEvent;
use OCP\EventDispatcher\Event;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;

class GroupsMappingListenerTest extends TestCase {

    private MappingService $mappingService;
    private LoggerInterface $logger;
    private GroupsMappingListener $listener;

    protected function setUp(): void {
        $this->mappingService = $this->createMock(MappingService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->listener = new GroupsMappingListener($this->mappingService, $this->logger);
    }

    public function testHandlesGroupsAttribute(): void {
        $claims = (object)['department' => 'IT'];
        $event = new AttributeMappedEvent('mappingGroups', $claims, '["existing"]');

        $this->mappingService->expects($this->once())
            ->method('process')
            ->with($claims, ['existing'])
            ->willReturn(['existing', 'IT']);

        $this->listener->handle($event);

        $this->assertSame('["existing","IT"]', $event->getValue());
        $this->assertTrue($event->isPropagationStopped());
    }

    public function testIgnoresOtherAttributes(): void {
        $claims = (object)['name' => 'Alice'];
        $event = new AttributeMappedEvent('mappingDisplayName', $claims, 'Alice');

        $this->mappingService->expects($this->never())
            ->method('process');

        $this->listener->handle($event);
        $this->assertFalse($event->isPropagationStopped());
    }

    public function testIgnoresNonAttributeMappedEvent(): void {
        $event = new Event();

        $this->mappingService->expects($this->never())
            ->method('process');

        $this->listener->handle($event);
    }

    public function testSetValueCalledWithJsonEncodedGroups(): void {
        $claims = (object)['roles' => ['admin']];
        $event = new AttributeMappedEvent('mappingGroups', $claims, '["users"]');

        $this->mappingService->method('process')
            ->willReturn(['users', 'role_admin']);

        $this->listener->handle($event);

        $decoded = json_decode($event->getValue(), true);
        $this->assertSame(['users', 'role_admin'], $decoded);
    }

    public function testNullResultDoesNotModifyEvent(): void {
        $claims = (object)[];
        $event = new AttributeMappedEvent('mappingGroups', $claims, '["existing"]');

        $this->mappingService->method('process')
            ->willReturn(null);

        $this->listener->handle($event);

        // Value should remain unchanged
        $this->assertSame('["existing"]', $event->getValue());
        $this->assertFalse($event->isPropagationStopped());
    }

    public function testStopPropagationCalled(): void {
        $claims = (object)['department' => 'IT'];
        $event = new AttributeMappedEvent('mappingGroups', $claims, '[]');

        $this->mappingService->method('process')
            ->willReturn(['IT']);

        $this->listener->handle($event);
        $this->assertTrue($event->isPropagationStopped());
    }

    public function testHandlesNullExistingValue(): void {
        $claims = (object)['department' => 'IT'];
        $event = new AttributeMappedEvent('mappingGroups', $claims, null);

        $this->mappingService->method('process')
            ->with($claims, [])
            ->willReturn(['IT']);

        $this->listener->handle($event);
        $this->assertSame('["IT"]', $event->getValue());
    }

    public function testHandlesInvalidJsonExistingValue(): void {
        $claims = (object)['department' => 'IT'];
        $event = new AttributeMappedEvent('mappingGroups', $claims, 'not-json');

        $this->mappingService->method('process')
            ->with($claims, [])
            ->willReturn(['IT']);

        $this->listener->handle($event);
        $this->assertSame('["IT"]', $event->getValue());
    }
}
