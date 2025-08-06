<?php namespace App\Events; use Illuminate\Broadcasting\Channel; use Illuminate\Broadcasting\InteractsWithSockets; use Illuminate\Contracts\Broadcasting\ShouldBroadcast; use Illuminate\Foundation\Events\Dispatchable; use 
Illuminate\Queue\SerializesModels; class StatsUpdated implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels; public $serviceId; public $statsData; public function __construct($serviceId, $statsData) { $this->serviceId = $serviceId; $this->statsData = $statsData;
    }
    public function broadcastOn() { return new Channel('stats.service.' . $this->serviceId);
    }
    public function broadcastAs() { return 'stats.updated';
    }
    public function broadcastWith() { return [ 'service_id' => $this->serviceId, 'current_count' => $this->statsData['current_count'], 'today_total' => $this->statsData['today_total'], 'current_listeners' => 
            $this->statsData['current_listeners'], 'timestamp' => now()->toISOString()
        ];
    }
}
