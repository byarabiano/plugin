( function( $ ) {

    wp.hooks.addFilter(
        'tutor_course_builder/side_panels',
        'tutor-lms-academic-pro/add-academic-panel',
        function(panels) {

            panels.push({
                name: 'academic_path',
                title: 'Academic Path',
                content: function() {

                    return wp.element.createElement(
                        'div',
                        { className: 'tlms-academic-panel' },
                        [

                            wp.element.createElement('h4', {}, 'Academic Path'),

                            wp.element.createElement('div', { style: { display:'flex', gap:'12px', marginTop:'10px' } }, [

                                wp.element.createElement('select', { name: 'tlms_university', style: { flex:'1' } },
                                    [
                                        wp.element.createElement('option', { value:'' }, 'Select University'),
                                        ...tlms_academic_data.universities.map(u =>
                                            wp.element.createElement('option', { value: u.id }, u.name )
                                        )
                                    ]
                                ),

                                wp.element.createElement('select', { name: 'tlms_faculty', style: { flex:'1' } },
                                    [
                                        wp.element.createElement('option', { value:'' }, 'Select Faculty'),
                                        ...tlms_academic_data.faculties.map(f =>
                                            wp.element.createElement('option', { value: f.id }, f.name )
                                        )
                                    ]
                                ),

                                wp.element.createElement('select', { name: 'tlms_department', style: { flex:'1' } },
                                    [
                                        wp.element.createElement('option', { value:'' }, 'Select Department'),
                                        ...tlms_academic_data.departments.map(d =>
                                            wp.element.createElement('option', { value: d.id }, d.name )
                                        )
                                    ]
                                )

                            ])

                        ]
                    );
                }
            });

            return panels;
        }
    );

})( jQuery );
