<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'ru' => 'rus',
    'kz' => 'kaz',
    'en' => 'eng',
    'qz' => 'qaz',
    'information' => 'Information',
    'view' => 'View',
    'show' => 'Show',
    'add' => 'Add',
    'edit' => 'Edit',
    'title_add' => 'Title add',
    'title_edit' => 'Title edit',
    'extended_edit' => 'Extended edit',
    'delete' => 'Delete',
    'save' => 'Save',
    'new_record' => 'New record',
    'edit_record' => 'Editing a record',
    'delete_record_question' => 'Delete record',
    'viewing_record' => 'Viewing record',
    'select' => 'Select',
    'select_record_for_work' => 'Select an entry to work with',
    'return' => 'Return',
    'cancel' => 'Cancel',
    'search' => 'Search',
    'search_by_code' => 'Search by code',
    'search_by_name' => 'Search by name',
    'empty_to_cancel' => 'Empty to cancel',
    'empty' => 'Empty',
    'full' => 'Full',
    'used' => 'Used',
    'not_used' => 'Not used',
    'on' => 'On',
    'no_information' => 'No information',
    'no_information_on' => 'No information on',
    'no_data' => 'No data',
    'no_data_on' => 'No data on',
    'transaction_not_completed' =>'Transaction not completed',
    'another_attitude' => 'Another Attitude',
    'serial_number' => 'Number',
    'login' => 'Login',
    'logout' => 'Logout',
    'bases' => 'Bases',
    'base' => 'Base',
    'links' => 'Links',
    'link' => 'Link',
    'items' => 'Objects',
    'item' => 'Object',
    'mains' => 'Values',
    'main' => 'Value',
    'templates' => 'Templates',
    'template' => 'Template',
    'projects' => 'Projects',
    'project' => 'Project',
    'my_projects' => 'My projects',
    'my_project' => 'My project',
    'all_projects' => 'All projects',
    'all_project' => 'Project',
    'subscriptions' => 'Project subscriptions',
    'subscription' => 'Subscribe to project',
    'my_subscriptions' => 'My project subscriptions',
    'my_subscription' => 'My project subscription',
    'users' => 'Users',
    'user' => 'User',
    'roles' => 'Roles',
    'role' => 'Role',
    'robas' => 'Bases settings',
    'roba' => 'Bases setting',
    'rolis' => 'Links settings',
    'roli' => 'Links setting',
    'accesses' => 'Accesses',
    'access' => 'Access',
    'date_created' => 'Creation date',
    'date_updated' => 'Date of change',
    'format_date' => 'd.m.Y',
    'code' => 'Code',
    'name' => 'Name',
    'names' => 'Names',
    'child' => 'Child',
    'parent' => 'Parent',
    'child_label' => 'Child_Label',
    'child_labels' => 'Child_Labels',
    'parent_label' => 'Parent_Label',
    'type'=>'Type',
    'list'=>'List',
    'number'=>'Number',
    'string'=>'String',
    'date'=>'Date',
    'boolean'=>'Boolean',
    'photo'=>'Photo',
    'document'=>'Document',
    'is_code_needed'=>'Code needed',
    'is_code_number'=>'Numeric code (otherwise string)',
    'is_limit_sign_code'=>'Limit the significance of the code',
    'significance_code'=>'Significance of the code',
    'is_code_zeros'=>'Pad number with zeros on the left',
    'is_suggest_code'=>'Suggest code when adding an entry',
    'is_suggest_max_code'=>'Suggest the code by the maximum value, otherwise - by the first free value',
    'is_recalc_code'=>'Recalculation of codes',
    'digits_num' =>'Number of digits after decimal point',
    'is_required_lst_num_str_img_doc'=>'Required',
    'is_one_value_lst_str'=>'One value in all languages',
    'parent_is_parent_related'=>'Automatically populate from parent input field',
    'parent_parent_related_start_link_id'=>'What field will we take as a basis?',
    'parent_parent_related_result_link_id'=>'What to display?',
    'parent_is_child_related'=>'Automatically filter input fields',
    'parent_child_related_start_link_id'=>'Filter field',
    'parent_child_related_result_link_id'=>'Route',
    'is_calcname_lst'=>'Calculated field',
    'sepa_calcname'=>'Separator character for computed name',
    'is_same_small_calcname'=>'Use a short form of the calculated name with homogeneous (identical Bases) dependencies, otherwise - the main view',
    'sepa_same_left_calcname'=>'Left delimiter symbol for a computed denomination with homogeneous (same Bases) dependencies',
    'sepa_same_right_calcname'=>'Right delimiter character for a computed denomination with homogeneous (same Bases) dependencies',
    'calculate'=>'Calculate',
    'calculate_name'=>'Calculate name',
    'parent_is_calcname'=>'For a computed name',
    'parent_is_small_calcname'=>'It is included in the short form of the calculated name with homogeneous (identical Bases) dependencies',
    'parent_is_left_calcname'=>'Computed name on the left (otherwise on the right)',
    'parent_calcname_prefix'=>'Computed name prefix',
    'parent_is_enter_refer'=>'Enter as a reference',
    'parent_is_numcalc'=>'Calculate value for numeric field',
    'parent_is_nc_screencalc'=>'Screen computing',
    'parent_is_nc_viewonly'=>'Calculated value only view',
    'parent_is_nc_parameter'=>'Parameter for calculated fields',
    'recalculation_codes'=>'Recalculation_codes',
    'sort_by_code'=>'Sort by code',
    'sort_by_name'=>'Sort by name',
    'select_from_refer'=>'select from the reference book',
    'default'=>'Default',
    'is_default_for_external'=>'Default role for external users',
    'admin'=>'Admin',
    'e-mail'=>'E-Mail',
    'password'=>'Password',
    'change_password'=>'Change password',
    'confirm_password'=>'Confirm password',
    'uniqueness_of_fields_violated'=>'Uniqueness of fields violated',
    'author'=>'Author',
    'project_role_selection'=>'Project and role selection',
    'is_author'=>'Author',
    'is_list_base_sndb'=>'Show Bases with types String, Number, Date, Boolean',
    'is_list_base_pd'=>'Show Bases with types Photo, Document',
    'is_list_base_create'=>'Creating Bases in the list',
    'is_list_base_read'=>'Reading Bases in the list',
    'is_list_base_update'=>'Updating the Bases in the List',
    'is_list_base_delete'=>'Delete the Bases from the list',
    'is_list_base_byuser'=>'Filter by user in the list',
    'is_edit_base_read'=>'Reading Bases in the form',
    'is_edit_base_update'=>'Update the Bases in the form',
    'is_list_base_enable'=>'Show Base in list',
    'is_list_link_enable'=>'Show Link in list',
    'is_show_base_enable'=>'Reading Bases when viewing',
    'is_show_link_enable'=>'Read Links when viewing',
    'is_edit_link_read'=>'Reading Links in the form',
    'is_edit_link_update'=>'Update of the Link in the form',
    'is_list_base_create_rule'=>'Create Basics in List and Read Basics in Form must not be the same (True)',
    'is_list_base_read_rule'=>'In this case, you need to uncheck "Reading Bases in the list" (incompatibility with "Creating Bases in the list", "Updating the Bases in the List", "Delete the Bases from the list")',
    'is_edit_base_read_rule'=>'This option is not allowed ("Reading Bases in the form" = True and "Update the Bases in the form" = True)',
    'is_edit_link_read_rule'=>'This option is not allowed ("Reading Links in the form" = True and "Update of the Link in the form" = True)',
];
