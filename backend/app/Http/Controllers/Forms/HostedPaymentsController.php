<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\HostedPaymentsDefaultFieldsRequest;
use App\Http\Requests\Forms\HostedPaymentsFieldsRequest;
use App\Http\Resources\Forms\HostedPaymentsCustomFieldsResource;
use App\Http\Resources\Forms\HostedPaymentsFieldsResource;
use App\Models\Departments\Department;
use App\Models\Merchants\Merchant;
use App\Services\Forms\HostedPaymentsFieldsService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HostedPaymentsController extends Controller
{
    public function __construct(
        protected HostedPaymentsFieldsService $service
    )
    {
    }

    public function editDefaults(): AnonymousResourceCollection
    {
        $fields = $this->service->getDefaultFields();

        return HostedPaymentsCustomFieldsResource::collection($fields);
    }

    public function saveDefaults(HostedPaymentsDefaultFieldsRequest $request): AnonymousResourceCollection
    {
        $this->service->saveDefaultFields(requestData: $request->validated());

        return $this->editDefaults();
    }

    public function edit(Merchant $merchant, Department $department): HostedPaymentsFieldsResource
    {
        $fields = [];
        $fields['default'] = $this->service->getDefaultFieldsDepartment(departmentId: $department->getKey());
        $fields['custom'] = $this->service->getCustomFieldsDepartment(departmentId: $department->getKey());

        return HostedPaymentsFieldsResource::make(
            $fields,
            $merchant->getAttribute('name'),
            $department->getAttribute('name'),
        );
    }

    public function save(HostedPaymentsFieldsRequest $request, Merchant $merchant, Department $department): HostedPaymentsFieldsResource
    {
        $this->service->save(
            requestData: $request->validated(),
            department: $department,
        );

        return $this->edit(merchant: $merchant, department: $department);
    }
}
