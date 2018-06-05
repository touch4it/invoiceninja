@extends('header')

@section('content')

	{!! Former::open($url)
            ->addClass('col-lg-10 col-lg-offset-1 warn-on-exit main-form')
            ->method($method)
            ->rules([
                'name' => 'required',
				'client_id' => 'required',
            ]) !!}

    @if ($project)
        {!! Former::populate($project) !!}
		{!! Former::populateField('task_rate', floatval($project->task_rate) ? Utils::roundSignificant($project->task_rate) : '') !!}
    @endif

    <span style="display:none">
        {!! Former::text('public_id') !!}
		{!! Former::text('action') !!}
    </span>

	<div class="row">
        <div class="col-lg-10 col-lg-offset-1">

            <div class="panel panel-default">
            <div class="panel-body">

				@if ($project)
					{!! Former::plaintext('client_name')
							->value($project->client ? $project->client->present()->link : '') !!}
				@else
					{!! Former::select('client_id')
							->addOption('', '')
							->label(trans('texts.client'))
							->addGroupClass('client-select') !!}
				@endif

                {!! Former::text('name') !!}

				{!! Former::text('task_rate')
						->placeholder($project && $project->client->task_rate ? $project->client->present()->taskRate : $account->present()->taskRate)
				 		->help('task_rate_help') !!}

            </div>
            </div>

        </div>
    </div>


	<center class="buttons">
        {!! Button::normal(trans('texts.cancel'))->large()->asLinkTo(HTMLUtils::previousUrl('/projects'))->appendIcon(Icon::create('remove-circle')) !!}
        {!! Button::success(trans('texts.save'))->submit()->large()->appendIcon(Icon::create('floppy-disk')) !!}
		@if ($project && ( Auth::user()->can('create', ENTITY_TASK) || Auth::user()->hasPermission('manage_own_tasks')))
			{!! DropdownButton::normal(trans('texts.more_actions'))
				  ->withContents([
					  [
						  'url' => url('/tasks/create/' . ($project->client ? $project->client->public_id : '0'). '/' . $project->public_id),
						  'label' => trans('texts.new_task'),
					  ],
					  [
						  'url' => 'javascript:submitAction("invoice")',
						  'label' => trans('texts.invoice_project'),
					  ],
				  	  \DropdownButton::DIVIDER,
					  [
						  'url' => 'javascript:submitAction("archive")',
						  'label' => trans('texts.archive_project')
					  ],
					  [
						  'url' => 'javascript:onDeleteClick()',
						  'label' => trans('texts.delete_project')
					  ],
				  ])
				  ->large() !!}
		@endif
	</center>

	{!! Former::close() !!}

    <script>

		var clients = {!! $clients !!};
		var clientMap = {};

		function submitAction(action) {
            $('#action').val(action);
            $('.main-form').submit();
        }

		function onDeleteClick() {
            sweetConfirm(function() {
                submitAction('delete');
            });
        }

        $(function() {
			var $clientSelect = $('select#client_id');
            for (var i=0; i<clients.length; i++) {
                var client = clients[i];
								clientMap[client.public_id] = client;
                var clientName = getClientDisplayName(client);
                if (!clientName) {
                    continue;
                }
                $clientSelect.append(new Option(clientName, client.public_id));
            }
			@if ($clientPublicId)
				$clientSelect.val({{ $clientPublicId }});
			@endif

			$clientSelect.combobox({highlighter: comboboxHighlighter}).change(function() {
				var client = clientMap[$('#client_id').val()];
				if (client && parseFloat(client.task_rate)) {
					var rate = client.task_rate;
				} else {
					var rate = {{ $account->present()->taskRate }};
				}
				$('#task_rate').attr('placeholder', roundSignificant(rate, true));
			});

			@if ($clientPublicId)
				$('#name').focus();
			@else
				$('.client-select input.form-control').focus();
			@endif
        });

    </script>

@stop
