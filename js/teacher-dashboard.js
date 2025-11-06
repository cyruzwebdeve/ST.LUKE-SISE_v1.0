// ========================================
// LOAD TEACHER SECTIONS
// ========================================
async function loadTeacherSections() {
    try {
        const response = await fetch('api/getTeacherSections.php');
        const result = await response.json();
        
        if (result.success) {
            const gradeFilter = document.getElementById('gradeFilter');
            const uniqueGrades = [...new Set(result.data.map(s => s.grade_level))];
            
            gradeFilter.innerHTML = '<option value="">Select Grade Level</option>';
            uniqueGrades.forEach(grade => {
                gradeFilter.innerHTML += `<option value="${grade}">${grade}</option>`;
            });
            
            // Store sections data for filtering
            window.teacherSections = result.data;
            
            // Load grade filter event
            gradeFilter.addEventListener('change', () => {
                const selectedGrade = gradeFilter.value;
                const sectionFilter = document.getElementById('sectionFilter');
                
                sectionFilter.innerHTML = '<option value="">Select Section</option>';
                
                if (selectedGrade) {
                    const sections = window.teacherSections.filter(s => s.grade_level === selectedGrade);
                    sections.forEach(section => {
                        sectionFilter.innerHTML += `<option value="${section.section_id}">${section.section_name}</option>`;
                    });
                }
            });
        }
    } catch (error) {
        console.error('Error loading sections:', error);
    }
}

// ========================================
// LOAD TEACHER SUBJECTS
// ========================================
async function loadTeacherSubjects() {
    try {
        const response = await fetch('api/getTeacherSubjects.php');
        const result = await response.json();
        
        if (result.success) {
            const subjectFilter = document.getElementById('subjectFilter');
            subjectFilter.innerHTML = '<option value="">Select Subject</option>';
            
            result.data.forEach(subject => {
                subjectFilter.innerHTML += `<option value="${subject.subject_code}">${subject.subject_name}</option>`;
            });
            
            window.teacherSubjects = result.data;
        }
    } catch (error) {
        console.error('Error loading subjects:', error);
    }
}

// ========================================
// FILTER AND DISPLAY STUDENTS
// ========================================
async function filterStudents() {
    const gradeLevel = document.getElementById('gradeFilter').value;
    const sectionId = document.getElementById('sectionFilter').value;
    const subjectCode = document.getElementById('subjectFilter').value;
    const gradingPeriod = document.getElementById('periodFilter').value;
    
    if (!gradeLevel || !sectionId || !subjectCode) {
        alert('Please select grade level, section, and subject.');
        return;
    }
    
    try {
        const response = await fetch(
            `api/getTeacherStudents.php?grade_level=${encodeURIComponent(gradeLevel)}&section_id=${encodeURIComponent(sectionId)}&subject_code=${encodeURIComponent(subjectCode)}&grading_period=${encodeURIComponent(gradingPeriod)}`
        );
        const result = await response.json();
        
        if (result.success) {
            const tableBody = document.getElementById('studentTable');
            const container = document.getElementById('studentTableContainer');
            
            if (result.data.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center py-4 text-gray-500">
                            No students found in this section.
                        </td>
                    </tr>
                `;
            } else {
                tableBody.innerHTML = result.data.map(student => `
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">${student.student_id}</td>
                        <td class="px-4 py-2">${student.student_name}</td>
                        <td class="px-4 py-2 text-center">
                            ${student.grade_score !== null ? 
                                `<span class="font-semibold">${student.grade_score}</span>` : 
                                '<span class="text-gray-400">Not graded</span>'}
                        </td>
                        <td class="px-4 py-2">
                            <input 
                                type="number" 
                                id="grade_${student.student_id}" 
                                value="${student.grade_score || ''}" 
                                class="border border-gray-300 rounded p-2 w-24" 
                                min="0" 
                                max="100"
                                step="0.01"
                                placeholder="0-100">
                        </td>
                        <td class="px-4 py-2 text-center">
                            <button 
                                onclick="saveGrade('${student.student_id}')" 
                                class="btn-blue">
                                Save
                            </button>
                        </td>
                    </tr>
                `).join('');
            }
            
            container.classList.remove('hidden');
            
            // Store current selection for save function
            window.currentSelection = {
                subjectCode: subjectCode,
                gradingPeriod: gradingPeriod
            };
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error loading students:', error);
        alert('Error loading students');
    }
}

// ========================================
// SAVE GRADE
// ========================================
async function saveGrade(studentId) {
    const input = document.getElementById(`grade_${studentId}`);
    const gradeValue = input.value.trim();
    
    if (gradeValue === '') {
        alert('Please enter a grade value.');
        return;
    }
    
    if (gradeValue < 0 || gradeValue > 100) {
        alert('Grade must be between 0 and 100.');
        return;
    }
    
    const formData = new FormData();
    formData.append('student_id', studentId);
    formData.append('subject_code', window.currentSelection.subjectCode);
    formData.append('grading_period', window.currentSelection.gradingPeriod);
    formData.append('grade_score', gradeValue);
    
    try {
        const response = await fetch('api/saveGrade.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            // Show success message
            const button = event.target;
            const originalText = button.textContent;
            button.textContent = '✓ Saved';
            button.classList.add('opacity-75');
            
            setTimeout(() => {
                button.textContent = originalText;
                button.classList.remove('opacity-75');
            }, 2000);
            
            // Reload the table to show updated grades
            filterStudents();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error saving grade:', error);
        alert('Error saving grade');
    }
}

// ========================================
// LOAD MY SCHEDULE
// ========================================
async function loadMySchedule() {
    try {
        const response = await fetch('api/getTeacherSchedule.php');
        const result = await response.json();
        
        if (result.success) {
            const tableBody = document.getElementById('scheduleTableBody');
            
            if (result.data.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="px-4 py-2 text-center text-gray-500">
                            No schedule assigned yet.
                        </td>
                    </tr>
                `;
            } else {
                tableBody.innerHTML = result.data.map(schedule => {
                    const datetime = new Date(schedule.day_time);
                    const dayName = datetime.toLocaleDateString('en-US', { weekday: 'long' });
                    const timeStr = datetime.toLocaleTimeString('en-US', { 
                        hour: '2-digit', 
                        minute: '2-digit' 
                    });
                    
                    return `
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2">${schedule.subject_name}</td>
                            <td class="px-4 py-2">${schedule.section_name} (${schedule.grade_level})</td>
                            <td class="px-4 py-2">${dayName}, ${timeStr}</td>
                            <td class="px-4 py-2">Room ${schedule.room_number}</td>
                        </tr>
                    `;
                }).join('');
            }
        }
    } catch (error) {
        console.error('Error loading schedule:', error);
    }
}

// ========================================
// VIEW GRADES TABLE
// ========================================
async function loadViewGradeDropdowns() {
    // Load sections
    try {
        const sectionsResponse = await fetch('api/getTeacherSections.php');
        const sectionsResult = await sectionsResponse.json();
        
        if (sectionsResult.success) {
            const viewSectionFilter = document.getElementById('viewSectionFilter');
            viewSectionFilter.innerHTML = '<option value="">Select Section</option>';
            
            sectionsResult.data.forEach(section => {
                viewSectionFilter.innerHTML += `<option value="${section.section_id}">${section.section_name} (${section.grade_level})</option>`;
            });
        }
        
        // Load subjects
        const subjectsResponse = await fetch('api/getTeacherSubjects.php');
        const subjectsResult = await subjectsResponse.json();
        
        if (subjectsResult.success) {
            const viewSubjectFilter = document.getElementById('viewSubjectFilter');
            viewSubjectFilter.innerHTML = '<option value="">Select Subject</option>';
            
            subjectsResult.data.forEach(subject => {
                viewSubjectFilter.innerHTML += `<option value="${subject.subject_code}">${subject.subject_name}</option>`;
            });
        }
    } catch (error) {
        console.error('Error loading dropdowns:', error);
    }
}

async function viewGradesTable() {
    const sectionId = document.getElementById('viewSectionFilter').value;
    const subjectCode = document.getElementById('viewSubjectFilter').value;
    
    if (!sectionId || !subjectCode) {
        alert('Please select both section and subject.');
        return;
    }
    
    try {
        const response = await fetch(
            `api/viewGrades.php?section_id=${encodeURIComponent(sectionId)}&subject_code=${encodeURIComponent(subjectCode)}`
        );
        const result = await response.json();
        
        if (result.success) {
            const tableBody = document.getElementById('gradesTableBody');
            const container = document.getElementById('gradesTableContainer');
            
            if (result.data.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-3 py-2 text-center text-gray-500">
                            No students found.
                        </td>
                    </tr>
                `;
            } else {
                tableBody.innerHTML = result.data.map(student => `
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-3 py-2">${student.student_name}</td>
                        <td class="px-3 py-2 text-center">${student.grades['1st'] || '-'}</td>
                        <td class="px-3 py-2 text-center">${student.grades['2nd'] || '-'}</td>
                        <td class="px-3 py-2 text-center">${student.grades['3rd'] || '-'}</td>
                        <td class="px-3 py-2 text-center">${student.grades['4th'] || '-'}</td>
                        <td class="px-3 py-2 text-center font-semibold ${
                            parseFloat(student.average) >= 75 ? 'text-blue-600' : 'text-red-600'
                        }">${student.average}</td>
                    </tr>
                `).join('');
            }
            
            container.classList.remove('hidden');
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error loading grades:', error);
        alert('Error loading grades');
    }
}