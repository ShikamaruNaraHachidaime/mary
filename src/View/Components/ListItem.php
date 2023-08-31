<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class ListItem extends Component
{
    public string $uuid;

    public function __construct(
        public object|array $item,
        public string $avatar = 'avatar',
        public string $value = 'name',
        public ?string $subValue = '',
        public ?bool $noSeparator = false,
        public ?string $link = null,

        // Slots
        public mixed $actions = null,
    ) {
        $this->uuid = Str::uuid();
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <div wire:key="{{ $uuid }}">                  
                <div 
                    {{ $attributes->class([
                        "flex justify-start items-center gap-4 hover:bg-base-200/50 px-3", 
                        "cursor-pointer" => $link]) 
                    }}
                >
                    
                    @if($link) 
                        <div>
                            <a href="{{ $link }}" wire:navigate> 
                    @endif

                    <!-- AVATAR -->
                    @if(data_get($item, $avatar))
                        <div class="py-3">                                                    
                            <div class="avatar">
                                <div class="w-11 rounded-full">
                                    <img src="{{ data_get($item, $avatar) }}" />
                                </div>
                            </div>                                                        
                        </div>
                    @endif

                    @if(!is_string($avatar))
                        <div class="py-3">
                            {{ $avatar }}
                        </div>
                    @endif                    


                    @if($link)
                            </a>
                        </div>
                    @endif

                    <!-- CONTENT -->                
                    <div class="flex-1 overflow-hidden whitespace-nowrap text-ellipsis truncate w-0">
                        @if($link) 
                            <a href="{{ $link }}" wire:navigate> 
                        @endif
                        
                        <div class="py-3">
                            <div @if(!is_string($value)) {{ $value->attributes->class(["font-semibold truncate"]) }} @else class="font-semibold truncate" @endif>
                                {{ is_string($value) ? data_get($item, $value) : $value }}
                            </div>

                            <div @if(!is_string($subValue))  {{ $subValue->attributes->class(["text-gray-400 text-sm truncate"]) }} @else class="text-gray-400 text-sm truncate" @endif>
                                {{ is_string($subValue) ? data_get($item, $subValue) : $subValue }}
                            </div>                        
                        </div>

                        @if($link)
                            </a>
                        @endif
                    </div>                    

                    <!-- ACTION -->
                    @if($actions)
                        @if($link && !Str::of($actions)->contains([':click', '@click' , 'href']))
                            <a href="{{ $link }}" wire:navigate> 
                        @endif
                            
                            <div class="py-3 flex items-center gap-3">                            
                                    {{ $actions }}                        
                            </div>
                       
                        @if($link && !Str::of($actions)->contains([':click', '@click' , 'href']))
                            </a>
                        @endif
                    @endif
                </div>                            

                @if(!$noSeparator) 
                    <hr class="border-base-300"/> 
                @endif
            </div>
        HTML;
    }
}
