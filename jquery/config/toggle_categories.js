/**
 * Javascript for enabling toggling of categories in course config of X-Ray
 *
 * @author David Castro
 * @param YUI
 * @param data
 */
function config_toggle_categories(YUI, data) {
    var self = this;

    // Global vars.
    var catPrefix = '#cat_';
    var coursePrefix = '#id_courses_';
    var joinedInputName = 'joined_courses';
    var idSubmitBtn = '#id_submitbutton';

    // Get values from backend.
    var json_data = JSON.parse(data);
    self.lang_strs = json_data.lang_strs;
    self.www_root = json_data.www_root;
    self.formIsValid = true;
    self.currLang = json_data.current_language;

    // Self properties.
    self.selection = [];

    // Root category.
    self.rootcat = {id:0};

    // Initializing function.
    self.init = function() {
        self.loadCategory(self.rootcat, self.initExpandCollapseButtons);
        self.loadSelection();
    };

    /**
     * Loads the selection that comes from a hidden field into a data structure.
     */
    self.loadSelection = function() {
        var rawVal = $('[name=' + joinedInputName + ']').val();
        if (rawVal && rawVal !== '') {
            self.selection = rawVal.split(',');
        }
    };

    self.initExpandCollapseButtons = function() {
        $('#xrayexpandallbtn').on('click', function(event) {
            event.preventDefault();
            self.expandAllCategories(function(){});
        });

        $('#xraycollapseallbtn').on('click', function(event) {
            event.preventDefault();
            self.recurseCategoryCollapse(self.rootcat);
        });
    };

    self.expandAllCategories = function(callback) {
        var expandBtnNode = $('#xrayexpandallbtn'), collapseBtnNode = $('#xraycollapseallbtn');
        expandBtnNode.append('<span class="xray_validate_loader"></span>');
        expandBtnNode.prop('disabled', true);
        collapseBtnNode.prop('disabled', true);
        self.recurseCategoryExpand(self.rootcat, function() {
            expandBtnNode.children('.xray_validate_loader').remove();
            expandBtnNode.prop('disabled', false);
            collapseBtnNode.prop('disabled', false);
            callback();
        });
    };

    self.recurseCategoryExpand = function(cat, callback) {
        // Load the category before clicking it to ensure that all subcategories have loaded
        self.toggleCategoryUI(cat, false);
        self.loadCategory(cat, function() {
            if (!cat.categories && !cat.courses) {
                return;
            }

            var catSelector = $(catPrefix + cat.id + '_li');
            if (catSelector.hasClass('xray-collapsible-list-closed')) {
                catSelector.click();
                self.toggleCategoryUI(cat, false);
            }

            if (!cat.categories || cat.categories.length === 0) {
                self.toggleCategoryUI(cat, true);
                callback();
            } else {
                self.recurseCategoriesExpand(0, cat.categories, function() {
                    self.toggleCategoryUI(cat, true);
                    callback();
                });
            }
        });
    };

    self.recurseCategoriesExpand = function(idx, cats, callback) {
        if (idx < cats.length) {
            var nextIdx = idx + 1;
            self.recurseCategoryExpand(cats[idx], function() {
                self.recurseCategoriesExpand(nextIdx, cats, callback);
            });
        } else {
            callback();
        }
    };

    self.recurseCategoryCollapse = function(cat) {
        if (!cat.categories && !cat.courses) {
            return;
        }

        var catSelector = $(catPrefix + cat.id + '_li');
        if (catSelector.hasClass('xray-collapsible-list-open')) {
            catSelector.click();
        }

        if (!cat.categories) {
            return;
        }

        for (var c in cat.categories) {
            self.recurseCategoryCollapse(cat.categories[c]);
        }
    };

    /**
     * Creates category listeners for the categories.
     * @param cats
     */
    self.createCategoryListeners = function(cats) {
        if (!cats) { return; }

        for (var c in cats) {
            self.createListenersInCategory(cats[c]);
        }
    };

    /**
     * Adds the category status update listener to the child courses.
     * @param cat Parent category
     */
    self.addMyListenerToCategories = function(cat) {
        if (cat.categories && cat.myListener) {
            for (var c in cat.categories) {
                $(catPrefix + cat.categories[c].id).change(cat.myListener);
                cat.categories[c].parentListener = cat.myListener;
            }
        }
    };

    /**
     * Adds the category status update listener to the child courses.
     * @param cat Parent category
     */
    self.addMyListenerToCourses = function(cat) {
        if (cat.courses && cat.myListener) {
            for (var c in cat.courses) {
                self.addMyListenerToCourse(cat, cat.courses[c]);
            }
        }
    };

    /**
     * Adds the category status updates listener to a specific course.
     * @param cat Parent category
     * @param course Child course
     */
    self.addMyListenerToCourse = function(cat, course) {
        $(coursePrefix + course.id).change(function(){
            var checked = $(this).is(":checked");
            self.updateCourseSelection(course, checked);
            cat.myListener();
        });
    };

    /**
     * Adds the click listeners to a list of categories for selection and expansion
     * @param cats Categories whose ui will receive the listeners
     */
    self.addClickListenersToCategories = function(cats){
        if (!cats) { return; }

        for (var c in cats) {
            self.addClickListenersToCategory(cats[c]);
        }
    };

    /**
     * Adds the click listeners to a category rendered ui for selection and expansion
     * @param cat Specific category whose ui will receive the listeners
     */
    self.addClickListenersToCategory = function(cat) {
        var selectCategory = function(event) {
            event.stopPropagation();
            var catInput = $(this);
            // Check sub categories and courses.
            self.toggleCategoryUI(cat, false);
            self.checkCategory(cat, catInput.prop('checked'), function() {
                self.toggleCategoryUI(cat, true);
            });
        };

        var expandCategory = function( event ) {
            event.stopPropagation();
            self.toggleCategoryUI(cat, false);
            self.loadCategory(cat, function(){
                self.toggleCategoryUI(cat, true);
            });
        };

        // Add click handler to checks to check children when parent is checked
        $(catPrefix + cat.id).on('click', selectCategory);
        // Add click handler to checks to check children when parent is checked
        $(catPrefix + cat.id + '_li').on('click', expandCategory);
    };

    /**
     * Toggles the usage of the category ui
     * @param cat Category
     * @param allowusage Can it be used?
     */
    self.toggleCategoryUI = function(cat, allowusage) {
        var catInput = $(catPrefix + cat.id), submitBtn = $(idSubmitBtn),
            catLbl = $(catPrefix + cat.id + '_lbl');
            disabledstr = 'disabled';

        catInput.prop(!allowusage);
        submitBtn.prop(disabledstr, !allowusage || !self.formIsValid);
        if (allowusage) {
            catLbl.children('.xray_validate_loader').remove();
        } else {
            catLbl.append('<span class="xray_validate_loader"></span>');
        }

    };

    /**
     * Creates status update listeners in a specific category
     * @param cat Specific category
     */
    self.createListenersInCategory = function(cat) {
        cat.myListener = function() {
            var indeterminate = self.atLeastOneCourseChecked(cat.courses) &&
                self.areCoursesIndeterminate(cat.courses);

            indeterminate = indeterminate || (
                self.atLeastOneCourseChecked(cat.courses) &&
                self.atLeastOneCourseUnChecked(cat.courses));

            indeterminate = indeterminate || (
                self.atLeastOneCatChecked(cat.categories) &&
                self.areCatsIndeterminate(cat.categories));

            if(cat.courses && cat.categories) {
                indeterminate = indeterminate || (
                    self.areCatsChecked(cat.categories) &&
                    !self.areCoursesChecked(cat.courses));

                indeterminate = indeterminate || (
                    self.areCoursesChecked(cat.courses) &&
                    self.atLeastOneCatUnCheckedDisabled(cat.categories));
            }

            var checked = indeterminate ||
                self.atLeastOneCatChecked(cat.categories) ||
                self.atLeastOneCourseChecked(cat.courses);

            var catInput = $(catPrefix + cat.id);
            catInput.prop('checked',checked);
            catInput.prop('indeterminate',indeterminate);

            if (cat.parentListener) {
                cat.parentListener();
            }
        };

        cat.myListener();
    };

    /**
     * Apply a check status to a list of categories
     * @param cats Categories
     * @param checked Check status
     * @param callback Executed after all categories' check status has been applied
     */
    self.checkCategories = function(cats, checked, callback) {
        for (var c in cats) {
            self.toggleCategoryUI(cats[c], false);
        }
        self.recursiveCategoryCheck(0, cats, checked, callback);
    };

    /**
     * Recursive category check that applies check status on idx category in cats
     * @param idx Index where check recursion is at right now
     * @param cats Categories
     * @param checked Check status
     * @param callback Executed after all categories' check status has been applied
     */
    self.recursiveCategoryCheck = function(idx, cats, checked, callback) {
        if (idx < cats.length) {
            var nextIdx = idx + 1;
            self.checkCategory(cats[idx], checked, function() {
                self.recursiveCategoryCheck(nextIdx, cats, checked, callback);
            });
        } else {
            callback();
        }
    };

    /**
     * Applies the check status to a specific category
     * @param cat
     * @param checked
     * @param callback
     */
    self.checkCategory = function(cat, checked, callback) {
        // Allow usage of this category (Even if it is disabled).
        var allowMyUsage = function() {
            self.toggleCategoryUI(cat, true);
            cat.myListener();
            return callback();
        };

        var catInput = $(catPrefix + cat.id);

        catInput.prop('checked', checked);

        var checkMyStuff = function() {
            if (cat.courses) {
                self.checkCourses(cat.courses, checked);
            }

            if (cat.categories) {
                if (cat.categories.length > 0) {
                    self.checkCategories(cat.categories, checked, allowMyUsage);
                } else {
                    allowMyUsage();
                }
            } else {
                allowMyUsage();
            }

            if (cat.parentListener) { cat.parentListener(); }
        };

        self.loadCategory(cat, checkMyStuff);
    };

    /**
     * Applies check status to specific courses
     * @param courses
     * @param checked
     */
    self.checkCourses = function(courses, checked) {
        for (var c in courses) {
            $(coursePrefix + courses[c].id).prop('checked', checked);
            self.updateCourseSelection(courses[c], checked);
        }
    };

    /**
     * Queries the list of categories to see if all are checked
     * @param cats
     * @returns {boolean} true if all are checked, false otherwise
     */
    self.areCatsChecked = function(cats) {
        if (!cats) { return false; }

        var res = true;
        for(var c in cats) {
            res = res && ($(catPrefix + cats[c].id).prop('checked'));
        }
        return res;
    };

    /**
     * Queries the list of categories to see if at least one is checked
     * @param cats
     * @returns {boolean} true if at least one is checked, false otherwise
     */
    self.atLeastOneCatChecked = function(cats) {
        if (!cats) { return false; }

        var res = false;
        for (var c in cats) {
            res = res || $(catPrefix + cats[c].id).prop('checked');
            if (res) {
                break;
            }
        }
        return res;
    };

    self.atLeastOneCatUnCheckedDisabled = function(cats) {
        if (!cats) return false;

        for (var c in cats) {
            if (!$(catPrefix + cats[c].id).prop('checked')) {
                return true;
            }
        }
        return false;
    };

    self.atLeastOneCourseUnChecked = function(courses) {
        if (!courses) return false;

        for (var c in courses) {
            if (!$(coursePrefix + courses[c].id).prop('checked')) {
                return true;
            }
        }
        return false;
    };

    /**
     * Queries the list of courses to see if all are checked
     * @param courses
     * @returns {boolean} true if all are checked, false otherwise
     */
    self.areCoursesChecked = function(courses) {
        if (!courses || Object.keys(courses).length === 0) return true;

        var res = true;
        for (var c in courses) {
            res = res && $(coursePrefix + courses[c].id).prop('checked');
        }
        return res;
    };

    /**
     * Queries the list of categories to see if all their courses are checked
     * @param cats
     * @returns {boolean} true if some categories are incomplete, false if all categories are completely selected
     */
    self.areCatsIndeterminate = function(cats) {
        if (!cats) { return false; }

        var catCount = 0, catIndCount = 0, catLength = Object.keys(cats).length;
        for (var c in cats) {
            catCount += ($(catPrefix + cats[c].id).prop('checked')) ? 1 : 0;
            catIndCount += $(catPrefix + cats[c].id).prop('indeterminate') ? 1 : 0;
        }
        return catCount < catLength || catIndCount > 0;
    };

    /**
     * Queries the list of courses to see if the number of checked courses is equal to the number of courses
     * @param courses
     * @returns {boolean} true if not all course are selected, false otherwise
     */
    self.areCoursesIndeterminate = function(courses) {
        if (!courses) { return false; }

        var courseCount = 0, courseLength = Object.keys(courses).length;
        for (var c in courses) {
            courseCount += $(coursePrefix + courses[c].id).prop('checked') ? 1 : 0;
        }
        return courseCount < courseLength;
    };

    /**
     * Queries the list of courses to check if at least one is checked
     * @param courses
     * @returns {boolean} true if at least one is checked, false otherwise
     */
    self.atLeastOneCourseChecked = function(courses) {
        if (!courses) { return false; }

        var res = false;
        for (var c in courses) {
            res = res || $(coursePrefix + courses[c].id).prop('checked');
            if (res) {
                break;
            }
        }
        return res;
    };

    /**
     * Updates the course selection with the data of a course and its check status
     * @param course Course info
     * @param checked Check status
     */
    self.updateCourseSelection = function(course, checked) {
        if (checked) {
            self.addCourseToSelection(course);
        } else {
            self.remCourseFromSelection(course);
        }
        $('[name=' + joinedInputName + ']').val(self.selection.join(','));
    };

    /**
     * Adds a course to the current selection (To be saved)
     * @param course
     */
    self.addCourseToSelection = function(course) {
        if (self.selection.indexOf(course.id) === -1) {
            self.selection.push(course.id);
        }
    };

    /**
     * Removes a course from the current selection (To be saved)
     * @param course
     */
    self.remCourseFromSelection = function(course) {
        var idx = self.selection.indexOf(course.id);
        while (idx > -1) {
            self.selection.splice(idx, 1);
            idx = self.selection.indexOf(course.id);
        }
    };

    /**
     * Gets the index of a course given the course "to save" data
     * @param selCourse
     * @returns {number} The index of the course, -1 if not found
     */
    self.getSelectedCourseIdx = function(selCourse) {
        var res = -1;
        for (var selIdx in self.selection) {
            if (self.selection[selIdx].cid === selCourse.cid) {
                res = selIdx;
                break;
            }
        }
        return res;
    };

    /**
     * Loads a category via Ajax from moodle, this loads whatever children the category has if they have not been loaded
     * already.
     * @param cat Category whose children will be loaded
     * @param callback Executed after all children have loaded
     */
    self.loadCategory = function(cat, callback) {
        if (cat.loaded) {
            if (callback) { callback(); }
            return;
        }

        cat.loaded = true;

        $.when (
            $.ajax({
                url: self.www_root + '/local/xray/view.php?controller=courseapi&action=listcategories&categoryid=' + cat.id,
                dataType: "json",
                success: function (data, status, xhr) {
                    if (!data || data.length === 0) {
                        return;
                    }

                    cat.categories = data;
                },
                error: function (xhr, status, err) {
                    cat.loaded = false;
                }
            }),
            $.ajax({
                url: self.www_root + '/local/xray/view.php?controller=courseapi&action=listcourses&categoryid=' + cat.id,
                dataType: "json",
                success: function (data, status, xhr) {
                    if (!data || data.length === 0) {
                        return;
                    }

                    cat.courses = data;
                },
                error: function (xhr, status, err) {
                    cat.loaded = false;
                }
            })
        ).then(function() {
            if (!cat.loaded) {
                return callback(); }

            self.emptyCat(cat);
            self.renderCategories(cat, cat.categories);
            self.renderCourses(cat, cat.courses);

            self.createCategoryListeners(cat.categories);
            self.addClickListenersToCategories(cat.categories);

            self.addMyListenerToCategories(cat);
            self.addMyListenerToCourses(cat);

            self.applyStatusToCategories(cat.categories);

            if (callback) { callback(); }
        });
    };

    /**
     * Empties the UI for children in a category, used for emptying the loading dialogue
     * @param cat
     */
    self.emptyCat = function(cat) {
        $(catPrefix + cat.id + '_children').empty();
    };


    /**
     * Renders a los of categories in the UI
     * @param parentCat
     * @param cats
     */
    self.renderCategories = function(parentCat, cats){
        if (!cats) { return; }

        for (var c in cats) {
            self.renderCategory(parentCat, cats[c]);
        }

        CollapsibleLists.applyTo(document.getElementById('cat_' + parentCat.id + '_children'));
    };

    /**
     * Renders a specific category
     * @param parentCat Parent category
     * @param cat Category to be rendered
     */
    self.renderCategory = function(parentCat, cat) {
        var catStr = '<div class="xray-wrapper-category"><li id="cat_' + cat.id + '_li" class="xray-category">'
            + '<button id="cat_' + cat.id + '_lbl" class="btn btn-link cat_label" href="javascript:void(0)"'
            + ' title="' + cat.name + (self.rootcat.id == parentCat.id ? ' Category' : ' Subcategory') + '" type="button"'
            + ' aria-label="' + cat.name + (self.rootcat.id == parentCat.id ? ' Category' : ' Subcategory') + '">' + cat.name + '</button>'
            + '<div class="xray-right-course-inputs">'
            + '<input type="checkbox" name="cat_' + cat.id + '" id="cat_' + cat.id + '" value="1"'
            + ' aria-label="' + cat.name + '" '+'><label for="cat_' + cat.id + '">&nbsp;</label>'
            + '</div>'
            + '<div class="xray-child-separator"></div>'
            + '<div class="xray-child-container">'
            + '<ul id="cat_' + cat.id + '_children" class="xray-category-tree"></ul>'
            + '</div>'
            + '</li>';

        $(catPrefix + parentCat.id + '_children').append(catStr);

    };

    /**
     * Applies the loaded check/selection status to a list of categories
     * @param cats
     */
    self.applyStatusToCategories = function(cats) {
        if (!cats) { return; }

        for (var c in cats) {
            self.applyStatusToCategory(cats[c]);
        }
    };

    /**
     * Applies the loaded check/selection status to a specific category
     * @param cat
     */
    self.applyStatusToCategory = function(cat) {
        $(catPrefix + cat.id).prop('indeterminate',cat.indeterminate);
        $(catPrefix + cat.id).prop('checked',cat.checked);
    };

    /**
     * Renders the UI of a list of courses
     * @param parentCat
     * @param courses
     */
    self.renderCourses = function(parentCat, courses){
        if (!courses) { return; }

        for (var c in courses) {
            self.renderCourse(parentCat, courses[c]);
        }
    };

    /**
     * Renders the Ui of a specific course
     * @param parentCat Parent category of the course
     * @param course Course to be rendered
     */
    self.renderCourse = function(parentCat, course) {

        var courseStr = '<li id="course_' + course.id + '_li" class="xray-course">'
            + '<label for="courses[' + course.id + ']" tabindex="0" class="course_label"'
            + 'aria-label="' + course.name + ' Course">' + course.name + '</label>&nbsp;'
            + '<div class="xray-right-course-inputs">'
            + '<div class="xray-course-link">'
            + '<a target="_blank" href="' + self.www_root + '/course/view.php?id=' + course.id + '" class="xray-course-shortname">' + course.shortname + '</a>'
            + '</div>'
            + '<input type="checkbox" id="id_courses_' + course.id + '" name="courses[' + course.id + ']" value="1" '
            + (course.checked ? 'checked="checked" ' : ' ')
            + 'aria-label="' + course.name + '" '
            + 'class="xray-course-input"><label for="courses[' + course.id + ']">&nbsp;</label>' // This label is empty to show the checkboxes.
            + '</div>'
            + '</li>';


        $(catPrefix + parentCat.id + '_children').append(courseStr);

        /**
         * Validates the form and enables/disables the submit button accordingly
         */
        self.validateForm = function() {
            var valid = true;

            if(self.formIsValid === valid) {
                return;
            }

            self.formIsValid = valid;
            self.processFormSubmit();
        };

        /**
         * Enables form submit according to self.formIsValid
         */
        self.processFormSubmit = function() {
            var submitBtn = $(idSubmitBtn);
            submitBtn.prop('disabled', !self.formIsValid);
        };
    };

    $(document).ready(function () {
        self.init();
    });
}
