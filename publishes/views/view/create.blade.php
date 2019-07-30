@unless($actions->readonly())
    <div class="btn-group pull-right mt10">
        @foreach($actions->actions()->authorized(auth('admin')->user()) as $action)
            @unless ($action->hideFromView())
                {!! $action->renderBtn() !!}
            @endunless
        @endforeach

        @if ($actions->authorize('update', $item))
            <a href="{{ route('scaffold.edit', ['module' => $module, 'id' => $item->getKey()]) }}"
               class="btn btn-primary btn-quirk">
                <i class="fa fa-pencil"></i>
            </a>
        @endif
        @if ($actions->authorize('delete', $item))
            <a href="{{ route('scaffold.delete', ['module' => $module, 'id' => $item->getKey()]) }}"
               class="btn btn-danger btn-quirk"
               onclick="return confirm('Are you sure?');">
                <i class="fa fa-trash"></i>
            </a>
        @endif
    </div>
@endunless
