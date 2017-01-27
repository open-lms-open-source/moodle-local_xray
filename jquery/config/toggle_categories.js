/**
 * Javascript for enabling toggling of categories in course config of X-Ray
 *
 * @author David Castro
 * @param YUI
 * @param data
 */
function config_toggle_categories(YUI, data) {
    var self = this;
    
    // Global vars
    var catPrefix = '#cat_';
    var coursePrefix = '#id_courses_';
    var joinedInputName = 'joined_courses';
    var idSubmitBtn = '#id_submitbutton';

    // Get values from backend
    var json_data = JSON.parse(data);
    self.lang_strs = json_data.lang_strs;
    self.www_root = json_data.www_root;
    self.cats = json_data.categories;
    
    // Self properties
    self.selection = [];
    
    // Initialize
    self.init = function() {
        self.loadCategory({id:0});
        self.loadSelection();
    };
    
    self.loadSelection = function() {
        var rawVal = $('[name='+joinedInputName+']').val();
        if(rawVal && rawVal !== '') {
            self.selection = rawVal.split(',');
        }
    };
    
    self.createCategoryListeners = function(cats) {
        if(!cats) return;
        
        for(var c in cats) {
            self.createListenersInCategory(cats[c]);
        }
    };
    
    self.addMyListenerToCategories = function(cat) {
        if(cat.categories && cat.myListener) {
            for(var c in cat.categories) {
                $(catPrefix + cat.categories[c].id).change(cat.myListener);
                cat.categories[c].parentListener = cat.myListener;
            }
        }
    };
    
    self.addMyListenerToCourses = function(cat) {
        if(cat.courses && cat.myListener) {
            for(var c in cat.courses) {
                self.addMyListenerToCourse(cat, cat.courses[c]);
            }
        }
    };
    
    self.addMyListenerToCourse = function(cat, course) {
        $(coursePrefix + course.id).change(function(){
            self.updateCourseSelection(course, $(this).is(":checked"));
            cat.myListener();
        });
    };
    
    self.addClickListenersToCategories = function(cats){
        if(!cats) return;
        
        for(var c in cats) {
            self.addClickListenersToCategory(cats[c]);
        }
    };
    
    self.addClickListenersToCategory = function(cat) {
        // Add click handler to checks to check children when parent is checked
        $(catPrefix + cat.id).on('click', function( event ) {
            event.stopPropagation();
            var catInput = $(this), submitBtn = $(idSubmitBtn),
            catLbl = $(catPrefix + cat.id + '_lbl');
            catInput.prop('disabled', 'disabled');
            submitBtn.prop('disabled', 'disabled');
            catLbl.append('<span class="xray_validate_loader"></span>');
            // Check sub categories and courses
            self.checkCategory(cat, catInput.prop('checked'), function() {
                catInput.prop('disabled', false);
                submitBtn.prop('disabled', false);
                catLbl.children('.xray_validate_loader').remove();
            });
        });
        
        // Add click handler to checks to check children when parent is checked
        $(catPrefix + cat.id + '_li').on('click', function( event ) {
            event.stopPropagation();
            var catInput = $(catPrefix + cat.id), submitBtn = $(idSubmitBtn),
            catLbl = $(catPrefix + cat.id + '_lbl');
            catInput.prop('disabled', 'disabled');
            submitBtn.prop('disabled', 'disabled');
            catLbl.append('<span class="xray_validate_loader"></span>');
            self.loadCategory(cat, function(){
                catInput.prop('disabled', false);
                submitBtn.prop('disabled', false);
                catLbl.children('.xray_validate_loader').remove();
            });
        });
        
        // Disable category label click listening
        $(catPrefix + cat.id + '_lbl').on('click', function( event ) {
            event.preventDefault();
            event.stopPropagation();
        });
    };
    
    self.createListenersInCategory = function(cat) {
        cat.myListener = function() {
            var indeterminate = 
                self.atLeastOneCourseChecked(cat.courses) &&
                self.areCoursesIndeterminate(cat.courses);
        
            indeterminate = indeterminate || (
                self.atLeastOneCatChecked(cat.categories) &&
                self.areCatsIndeterminate(cat.categories));
        
            if(cat.courses && cat.categories) {
                indeterminate = indeterminate || (
                    !self.areCatsChecked(cat.categories) &&
                    self.areCoursesChecked(cat.courses));

                indeterminate = indeterminate || (
                    self.areCatsChecked(cat.categories) &&
                    !self.areCoursesChecked(cat.courses));
            }

            var checked = indeterminate ||
                self.atLeastOneCatChecked(cat.categories) ||
                self.atLeastOneCourseChecked(cat.courses);

            var catInput = $(catPrefix + cat.id);
            catInput.prop('checked',checked);
            catInput.prop('indeterminate',indeterminate);
            
            if(cat.parentListener) {
                cat.parentListener();
            }
        };
        
        cat.myListener();
    };
    
    self.checkCategories = function(cats, checked, callback) {
        for(var c in cats) {
            self.checkCategory(cats[c], checked, callback);
        }
    };
    
    self.checkCategory = function(cat, checked, callback) {
        var catInput = $(catPrefix + cat.id);
    
        catInput.prop('checked', checked);
        catInput.prop('indeterminate', false);
        
        var checkMyStuff = function() {
            if(cat.courses) {
                self.checkCourses(cat.courses, checked);
            }
            
            if(cat.categories) {
                if (cat.categories.length > 0) {
                    self.checkCategories(cat.categories, checked, callback);
                } else {
                    callback();
                }
            } else {
                callback();
            }
            
            if(cat.parentListener) cat.parentListener();
        };
        
        self.loadCategory(cat, checkMyStuff);
    };
    
    self.checkCourses = function(courses, checked) {
        for(var c in courses) {
            $(coursePrefix + courses[c].id).prop('checked', checked);
            self.updateCourseSelection(courses[c], checked);
        }
    };
    
    self.areCatsChecked = function(cats) {
        if(!cats) return false;
        
        var res = true;
        for(var c in cats) {
            res = res && $(catPrefix + cats[c].id).prop('checked');
        }
        return res;
    };
    
    self.atLeastOneCatChecked = function(cats) {
        if(!cats) return false;
        
        var res = false;
        for(var c in cats) {
            res = res || $(catPrefix + cats[c].id).prop('checked');
        }
        return res;
    };
    
    self.areCoursesChecked = function(courses) {
        if(!courses || courses.length === 0) return true;
        
        var res = true;
        for(var c in courses) {
            res = res && $(coursePrefix + courses[c].id).prop('checked');
        }
        return res;
    };
    
    self.areCatsIndeterminate = function(cats) {
        if(!cats) return false;
        
        var catCount = 0, catIndCount = 0, catLength = Object.keys(cats).length;
        for(var c in cats) {
            catCount += $(catPrefix + cats[c].id).prop('checked') ? 1 : 0;
            catIndCount += $(catPrefix + cats[c].id).prop('indeterminate') ? 1 : 0;
        }
        return catCount < catLength || catIndCount > 0;
    };
    
    self.areCoursesIndeterminate = function(courses) {
        if(!courses) return false;
        
        var courseCount = 0, courseLength = courses.length;
        for(var c in courses) {
            courseCount += $(coursePrefix + courses[c].id).prop('checked') ? 1 : 0;
        }
        return courseCount < courseLength;
    };
    
    self.atLeastOneCourseChecked = function(courses) {
        if(!courses) return false;
        
        var res = false;
        for(var c in courses) {
            res = res || $(coursePrefix + courses[c].id).prop('checked');
        }
        return res;
    };
    
    self.updateCourseSelection = function(course, checked) {
        if(checked) {
            self.addCourseToSelection(course);
        } else {
            self.remCourseFromSelection(course);
        }
        $('[name='+joinedInputName+']').val(self.selection.join(','));
    };
    
    self.addCourseToSelection = function(course) {
        if(self.selection.indexOf(course.id) === -1) {
            self.selection.push(course.id);
        }
    };
    
    self.remCourseFromSelection = function(course) {
        var idx = self.selection.indexOf(course.id);
        if(idx > -1) {
            self.selection.splice(idx, 1);
        }
    };
    
    self.loadCategory = function(cat, callback) {
        if (cat.loaded) {
            if(callback) callback();
            return;
        }
        
        cat.loaded = true;
        
        $.when (
            $.ajax({
                url: self.www_root + '/local/xray/view.php?controller=courseapi&action=listcategories&categoryid='+cat.id,
                dataType: "json",
                success: function (data, status, xhr) {
                    if(!data || data.length === 0)
                        return;

                    cat.categories = data;
                },
                error: function (xhr, status, err) {
                    cat.loaded = false;
                }
            }),
            $.ajax({
                url: self.www_root + '/local/xray/view.php?controller=courseapi&action=listcourses&categoryid='+cat.id,
                dataType: "json",
                success: function (data, status, xhr) {
                    if(!data || data.length === 0)
                        return;

                    cat.courses = data;
                },
                error: function (xhr, status, err) {
                    cat.loaded = false;
                }
            })
        ).then(function() {
            if (!cat.loaded)
                return callback();

            self.emptyCat(cat);
            self.renderCategories(cat, cat.categories);
            self.renderCourses(cat, cat.courses);
            
            self.createCategoryListeners(cat.categories);
            self.addClickListenersToCategories(cat.categories);
            
            self.addMyListenerToCategories(cat);
            self.addMyListenerToCourses(cat);
            
            self.applyStatusToCategories(cat.categories);
            
            if(callback) callback();
        });
    };
    
    self.emptyCat = function(cat) {
        $(catPrefix + cat.id + '_children').empty();
    };
    
    self.renderCategories = function(parentCat, cats){
        if(!cats) return;
        
        for(var c in cats) {
            self.renderCategory(parentCat, cats[c]);
        }
        
        CollapsibleLists.applyTo(document.getElementById('cat_' + parentCat.id + '_children'));
    };
    
    self.renderCategory = function(parentCat, cat) {
        var catStr = '<li id="cat_' + cat.id + '_li">'
            +'<input type="checkbox" id="cat_' + cat.id + '" value="1">'
            +'<label id="cat_' + cat.id + '_lbl" for="cat_' + cat.id + '" class="cat_label">' + cat.name + '&nbsp;&nbsp;</label>'
            +'<ul id="cat_' + cat.id + '_children">'
            +'</ul>'
            +'</li>';
    
        $(catPrefix + parentCat.id + '_children').append(catStr);
        
    };
    
    self.applyStatusToCategories = function(cats) {
        if(!cats) return;
        
        for(var c in cats) {
            self.applyStatusToCategory(cats[c]);
        }
    };
    
    self.applyStatusToCategory = function(cat) {
        $(catPrefix + cat.id).prop('indeterminate',cat.indeterminate);
        $(catPrefix + cat.id).prop('checked',cat.checked);
    };
    
    self.renderCourses = function(parentCat, courses){
        if(!courses) return;
        
        for(var c in courses) {
            self.renderCourse(parentCat, courses[c]);
        }
    };
    
    self.renderCourse = function(parentCat, course) {
        var catStr = '<li id="course_' + course.id + '_li">'
            +'<input type="checkbox" id="id_courses_' + course.id + '" name="courses['+course.id+']" value="1" '
            +(course.checked ? 'checked="checked"' : '')
            +(course.disabled ? 'disabled="disabled"' : '') + '>'
            +'<label for="id_courses_' + course.id + '" class="course_label">' + course.name + '</label>'
            +'</li>';
    
        $(catPrefix + parentCat.id + '_children').append(catStr);
    };
    
    $(document).ready(function () {
        self.init();
    });
}
