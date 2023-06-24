<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Move;
use App\Models\Chat;

use App\Helpers\ChatHelper;

class CreateChatRoom extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:chat-room';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create chat room from move';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $moves = Move::has('invitees')->where('status', 1)->get();
        
        if($moves->count() > 0){
            foreach($moves as $move){
                ChatHelper::createChatRoomFromMove($move->uuid);
            }
        }
        return Command::SUCCESS;
    }
}
