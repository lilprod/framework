<?php
namespace AvoRed\Framework\Catalog\Controllers;

use AvoRed\Framework\Database\Contracts\PropertyModelInterface;
use AvoRed\Framework\Database\Models\Property;
use AvoRed\Framework\Catalog\Requests\PropertyRequest;

class PropertyController
{
    /**
     * Property Repository for the Property Controller
     * @var \AvoRed\Framework\Database\Repository\PropertyRepository $propertyRepository
     */
    protected $propertyRepository;
    
    /**
     * Construct for the AvoRed install command
     * @param \AvoRed\Framework\Database\Repository\PropertyRepository $propertyRepository
     */
    public function __construct(
        PropertyModelInterface $propertyRepository
    ) {
        $this->propertyRepository = $propertyRepository;
    }

    /**
     * Show Dashboard of an AvoRed Admin
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $properties = $this->propertyRepository->all();

        return view('avored::catalog.property.index')
            ->with('properties', $properties);
    }

     /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $dataTypeOptions = Property::PROPERTY_DATATYPES;
        $fieldTypeOptions = Property::PROPERTY_FIELDTYPES;
        
        return view('avored::catalog.property.create')
            ->with('dataTypeOptions', $dataTypeOptions)
            ->with('fieldTypeOptions', $fieldTypeOptions);
    }

    /**
     * Store a newly created resource in storage.
     * @param \AvoRed\Framework\Catalog\Requests\PropertyRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(PropertyRequest $request)
    {
        $property = $this->propertyRepository->create($request->all());
        $this->savePropertyDropdownOptions($property, $request);

        return redirect()->route('admin.property.index')
            ->with('successNotification', __(
                'avored::system.notification.store',
                ['attribute' => __('avored::catalog.property.title')]
            ));
    }

    /**
     * Show the form for editing the specified resource.
     * @param \AvoRed\Framework\Database\Models\Property $property
     * @return \Illuminate\Http\Response
     */
    public function edit(Property $property)
    {
        $dataTypeOptions = Property::PROPERTY_DATATYPES;
        $fieldTypeOptions = Property::PROPERTY_FIELDTYPES;

        return view('avored::catalog.property.edit')
            ->with('property', $property)
            ->with('dataTypeOptions', $dataTypeOptions)
            ->with('fieldTypeOptions', $fieldTypeOptions);
    }

    /**
     * Update the specified resource in storage.
     * @param \AvoRed\Framework\Catalog\Requests\PropertyRequest $request
     * @param \AvoRed\Framework\Database\Models\Property  $property
     * @return \Illuminate\Http\Response
     */
    public function update(PropertyRequest $request, Property $property)
    {
        $property->update($request->all());
        $this->savePropertyDropdownOptions($property, $request);

        return redirect()->route('admin.property.index')
            ->with('successNotification', __(
                'avored::system.notification.updated',
                ['attribute' => __('avored::catalog.property.title')]
            ));
    }

    /**
     * Remove the specified resource from storage.
     * @param \AvoRed\Framework\Database\Models\Property  $property
     * @return \Illuminate\Http\Response
     */
    public function destroy(Property $property)
    {
        $property->delete();

        return [
            'success' => true,
            'message' => __(
                'avored::system.notification.delete',
                ['attribute' => __('avored::catalog.property.title')]
            )
        ];
    }

    /**
     * Save Property Dropdown options
     * @param \\AvoRed\Framework\Database\Models\Property  $property
     * @param \AvoRed\Framework\Catalog\Requests\PropertyRequest $request
     * @return void
     */
    public function savePropertyDropdownOptions(Property $property, PropertyRequest $request)
    {
        if (!($request->get('field_type') === 'RADIO' || $request->get('field_type') === 'SELECT')) {
            $property->dropdownOptions()->delete();
        }
        if (($request->get('field_type') === 'RADIO' ||
            $request->get('field_type') === 'SELECT') &&
            count($request->get('dropdown_option')) > 0
        ) {
            foreach ($request->get('dropdown_option') as $key => $option) {
                if (empty($option)) {
                    continue;
                }

                if (is_string($key)) {
                    $property->dropdownOptions()->create(['display_text' => $option]);
                } else {
                    $optionModel = $property->dropdownOptions()->find($key);
                    $optionModel->update(['display_text' => $option]);
                }
            }
        }
    }
}