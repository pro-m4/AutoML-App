
let currentDatasetId = null; 
let currentMetricName = "Score";


let selectedModelPath = null;
let selectedFramework = null;
function updatePredictModelsDropdown() {
    $.get('/automl/server/php/get_user_models.php', function(response) {
        try {
            const models = (typeof response === 'object') ? response : JSON.parse(response);
            let options = '<option value="" selected disabled>-- Choose a Model --</option>';
            
            models.forEach(m => {
                const fileName = m.dataset_name.replace(/^\d+_/, '');
                const displayName = `[ID:${m.id}] ${fileName} (${m.framework.toUpperCase()}) - Score: ${m.score} ${m.metric_used}`;
                options += `<option value="${m.model_path}" data-framework="${m.framework}">${displayName}</option>`;
            });
            
            $('#modelSelectPredict').html(options);

            if (selectedModelPath) {
                $('#modelSelectPredict').val(selectedModelPath);
            }

        } catch (e) { 
            console.error("Error loading models for dropdown", e); 
        }
    });
}
window.loadUserPredictions = function() {
    console.log("Fetching predictions...");
    
    $.get('/automl/server/php/get_user_predictions.php', function(response) {
        console.log("Server Response:", response);
        
        try {
            const preds = (typeof response === 'object') ? response : JSON.parse(response);
            let rows = '';
            
            
            if (preds.status === 'error') {
                console.error("PHP Error:", preds.message);
                $('#predictionsTable tbody').html(`<tr><td colspan="5" class="text-danger text-center">Error: ${preds.message}</td></tr>`);
                return;
            }

            if (!preds || preds.length === 0) {
                rows = '<tr><td colspan="5" class="text-center text-muted">No predictions found in database.</td></tr>';
            } else {
               preds.forEach(p => {

    const modelDisplay = (p.model_id && p.model_id !== "0")
        ? p.model_id
        : '<span class="text-muted">N/A</span>';

    rows += `

                    <tr>
                    <td><small>${p.created_at}</small></td>
    <td>${p.input_file}</td>
    <td><span class="badge bg-info text-dark">${p.framework}</span></td>
    <td>${modelDisplay}</td>
    <td class="text-end">
        <button class="btn btn-sm btn-outline-info" title="Preview" onclick="previewPredictionCSV('${p.output_file}')">
            <i class="bi bi-eye"></i>
        </button>
         <button class="btn btn-sm btn-outline-primary" 
        title="View Metrics"
        onclick='showPredictionMetrics(${JSON.stringify(p.metrics)})'>
    <i class="bi bi-graph-up"></i>
</button>
        <a href="uploads/inference/${p.output_file}" class="btn btn-sm btn-outline-success" title="Download Predictions">
            <i class="bi bi-download"></i>
        </a>
        <button class="btn btn-sm btn-outline-danger" title="delete" onclick="deletePrediction(${p.id})">
            <i class="bi bi-trash"></i>
        </button>
    </td>
</tr>`;
                });
            }
            
            
            $('#predictionsTable tbody').html(rows);
            
        } catch (e) { 
            console.error("JSON Parse Error:", e); 
            $('#predictionsTable tbody').html('<tr><td colspan="5" class="text-danger text-center">Error parsing data. Check console.</td></tr>');
        }
    }).fail(function(xhr) {
        console.error("Ajax Request Failed:", xhr.responseText);
    });
}
window.showPredictionMetrics = function(metrics) {

    try {

        if (!metrics || metrics === "[]" || metrics === "{}") {
            return alert("No metrics available for this prediction.");
        }

        if (typeof metrics === "string") {
            metrics = JSON.parse(metrics);
        }

        let html = `
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Metric</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        Object.entries(metrics).forEach(([key, value]) => {

            if (typeof value === 'object') return;

            html += `
                <tr>
                    <td class="fw-bold">${key}</td>
                    <td>${value}</td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;

       $('#previewModalLabel').html(
            '<i class="bi bi-graph-up me-2"></i> Model Metrics'
        );

        $('#previewModalTable').html(html);

        const modalElement = document.getElementById('previewModal');

        // 🔥 ΔΙΟΡΘΩΣΗ: Αφαιρεί το focus από το κουμπί που πατήθηκε 
        // ώστε να μην εγκλωβιστεί memory/focus μέσα στο κρυφό modal
        if (document.activeElement) {
            document.activeElement.blur();
        }

        // Καλούμε το Bootstrap Modal
        const myModal = bootstrap.Modal.getOrCreateInstance(modalElement); 
        myModal.show();

    } catch(err) {
        console.error(err);
        alert("Failed to load metrics.");
    }
};
window.deletePrediction = function(id) {
    if (!confirm('Delete this file?')) return;

    $.post('/automl/server/php/delete_prediction.php', { id: id }, function(res) {
        if (res.status === 'success') {
            
            window.loadUserPredictions();
            
            
            console.log("Prediction deleted successfully");
        } else {
            alert("Error: " + res.message);
        }
    }, 'json');
};
window.markJobAsSeen = function(jobId) {
    if (!jobId) return;

    $.post('/automl/server/php/mark_as_seen.php', { job_id: jobId }, function(res) {
        console.log("Database updated: Job " + jobId + " is now notified.");
    }).fail(function(xhr) {
        console.error("Failed to update database. Check if /automl/server/php/mark_as_seen.php exists.");
    });
};loadUserModels = function() {
    $.get('/automl/server/php/get_user_models.php', function(response) {
        try {
            const models = (typeof response === 'object') ? response : JSON.parse(response);
            let rows = '';
            
            if (models.length === 0) {
                rows = '<tr><td colspan="9" class="text-center text-muted">No models trained yet.</td></tr>';
            } else {
               models.forEach(m => {
    const displayName = m.dataset_name.replace(/^\d+_/, '');
    
   
    const isClass = (m.task_type === 'classification');
    const taskBadgeClass = isClass ? 'bg-primary' : 'bg-warning text-dark';
    const taskIcon = isClass ? 'bi-tags' : 'bi-graph-up';
    const taskText = m.task_type ? m.task_type.toUpperCase() : 'N/A';
    const algoText = m.algorithm ? m.algorithm.toUpperCase() : 'N/A';

    rows += `
    <tr>
        <td><span class="badge bg-secondary">#${m.id}</span></td>
        <td><i class="bi bi-file-earmark-text me-1"></i>${displayName}</td>
        <td><code class="text-danger">${m.target_column}</code></td>
        <td>
            <span class="badge ${taskBadgeClass} small">
                <i class="bi ${taskIcon} me-1"></i>${taskText}
            </span>
        </td>
        <td><span class="badge bg-info text-dark">${m.framework}</span></td>
        <td><span class="badge border text-primary bg-white">${algoText}</span></td>
        <td><span class="badge bg-light text-dark border">${m.metric_used || 'N/A'}</span></td>
        <td><strong class="text-success">${parseFloat(m.score).toFixed(4)}</strong></td>
        <td><small class="text-muted">${m.created_at}</small></td>
        <td class="text-end">
            <button class="btn btn-sm btn-primary" onclick="openUseModelModal('${m.model_path}', '${m.framework}', '${m.algorithm || 'N/A'}' )" title="Predict">
                <i class="bi bi-play-circle-fill"></i>
            </button>
            <a href="/automl/server/php/download_model.php?id=${m.id}" class="btn btn-sm btn-outline-success" title="Download">
                <i class="bi bi-download"></i>
            </a>
            <button class="btn btn-sm btn-outline-danger" onclick="deleteModel(${m.id})" title="Delete">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>`;
});
            }
            $('#userModelsTable tbody').html(rows);
        } catch (e) { console.error(e); }
    });
   
}
 window.deleteModel = function(id) {
    if (confirm("Are you sure you want to delete this model?")) {
        $.post('/automl/server/php/delete_model.php', { id: id }, function(response) {
            
            const res = (typeof response === 'object') ? response : JSON.parse(response);
            
            if (res.status === 'success') {
                
                loadUserModels(); 
            } else {
                alert("Error deleting model: " + res.message);
            }
        }).fail(function() {
            alert("Server connection error.");
        });
    }
};
$(document).ready(function() {
    const savedJobId = localStorage.getItem('activeJobId');
    if (savedJobId) {
        // Κρύβουμε όλη τη φόρμα
        $('#train-form-content').hide();
        // Δείχνουμε το overlay
        $('#trainingOverlay').css('display', 'flex').show();
        checkJobStatus(savedJobId);
    }
    $(document).on('hidden.bs.modal', '.modal', function () {
        if ($('.modal.show').length === 0) {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            $('body').css('padding-right', '');
        }
    });
    checkNewResults();
   
$(document).on('change', '#modelSelectPredict', function() {
    const selectedOption = $(this).find(':selected');
    
    
    selectedModelPath = selectedOption.val();
    selectedFramework = selectedOption.data('framework');

    console.log("Manual selection updated:", selectedModelPath, selectedFramework);

    
    if (selectedModelPath) {
        $('#display-model-id').text(selectedModelPath.split('/').pop());
        $('#display-framework').text(selectedFramework.toUpperCase());
        $('#predict-placeholder').hide();
        $('#predict-container').show();
    }
});
    
$('#nav-predict').click(function() {
    updatePredictModelsDropdown();
});


});

function checkNewResults() {
    $.ajax({
        url: '/automl/server/php/check_notifications.php',
        method: 'GET',
        cache: false, 
        success: function(response) {
            try {
                // Ασφαλές parsing
                let data;
                if (typeof response === 'object') {
                    data = response;
                } else {
                    // Αν είναι άδειο string, σταμάτα για να μην κρασάρει η JSON.parse
                    if (!response || !response.trim()) return; 
                    data = JSON.parse(response);
                }
                
                if (data && data.has_new) {
                    console.log("New Job Notification Detected:", data.job_id);

                    // 1. ΑΝΑΚΤΗΣΗ ΜΕΤΡΙΚΗΣ
                    let foundMetric = data.metric; 
                    
                    // Προσθήκη ελέγχου: typeof data.results === 'object' && data.results !== null
                    if (!foundMetric && data.results && typeof data.results === 'object') {
                        const keys = Object.keys(data.results);
                        if (keys.length > 0) {
                            const firstFw = keys[0];
                            foundMetric = data.results[firstFw].metric_used || data.results[firstFw].metric;
                        }
                    }

                    // 2. ΑΠΟΘΗΚΕΥΣΗ
                    if (foundMetric && typeof foundMetric === 'string' && !foundMetric.toLowerCase().includes("select")) {
                        // Χρησιμοποίησε window. αν είναι global μεταβλητή για να μην κρασάρει σε strict mode
                        if (typeof currentMetricName !== 'undefined') {
                            currentMetricName = foundMetric;
                        }
                        localStorage.setItem('pendingMetricName', foundMetric); 
                        localStorage.setItem('pendingMetricKey', foundMetric);  
                    }

                    // 3. ΕΜΦΑΝΙΣΗ ΑΠΟΤΕΛΕΣΜΑΤΩΝ
                    if (typeof displayResults === "function") {
                        displayResults(data.results);
                        if (typeof window.markJobAsSeen === "function") {
                            window.markJobAsSeen(data.job_id);
                        }
                    }
                    
                    if (typeof loadUserModels === "function") {
                        loadUserModels();
                    }
                }
            } catch (e) {
                // Εδώ τυπώνουμε όλο το error object για να σου πει ακριβώς τι φταίει
                console.error("Notification Error Details:", e.message, e);
            }
        },
        error: function(err) {
            console.error("AJAX Error in checkNewResults:", err);
        }
    });
}

setInterval(checkNewResults, 10000);
$(document).ready(function() {
    let headers = [];
    let totalRows = 0;

   
    $('#sidebar-toggle').click(function() {
        $('#wrapper').toggleClass('collapsed');
        const icon = $(this).find('i');
        if ($('#wrapper').hasClass('collapsed')) {
            icon.removeClass('bi-chevron-left').addClass('bi-chevron-right');
        } else {
            icon.removeClass('bi-chevron-right').addClass('bi-chevron-left');
        }
    });

    

$('#nav-train, #nav-datasets, #nav-models, #nav-predict, #nav-my-predictions').click(function(e) {
    e.preventDefault();
    
    // 1. Αφαίρεση active από όλα τα items (PC Sidebar & Mobile Offcanvas)
    $('.list-group-item').removeClass('active');
    
    // 2. Προσθήκη active στο στοιχείο που πατήθηκε
    $(this).addClass('active');

    // 3. ΣΥΓΧΡΟΝΙΣΜΟΣ: Βρίσκουμε το κείμενο του κουμπιού (π.χ. "Train Model")
    // και κάνουμε active το αντίστοιχο κουμπί στο mobile μενού
    var linkText = $(this).find('span').text().trim();
    $(".offcanvas-body .list-group-item:contains('" + linkText + "')").addClass('active');

    $('.content-section').hide();
    
    // Η λογική των sections παραμένει ως είχε, προσθέτοντας την ενημέρωση και του mobile τίτλου
    if (this.id === 'nav-train') {
        $('#section-train').fadeIn();
        updateDisplayTitles('Train Model');
    } else if (this.id === 'nav-datasets') {
        $('#section-datasets').fadeIn();
        updateDisplayTitles('My Datasets');
        loadUserDatasets(); 
    } else if (this.id === 'nav-models') {
        $('#section-models').fadeIn();
        updateDisplayTitles('My Models');
        loadUserModels(); 
    } else if (this.id === 'nav-predict') {
        $('#section-predict').fadeIn();
        updateDisplayTitles('Predict Analysis');
    } else if (this.id === 'nav-my-predictions') {
        $('#section-my-predictions').fadeIn();
        updateDisplayTitles('My Predictions');
        loadUserPredictions();
    }
});

// Βοηθητική συνάρτηση για να αλλάζουν οι τίτλοι παντού
function updateDisplayTitles(title) {
    $('#page-title').text(title);        // Τίτλος στο λευκό Navbar (PC)
    $('#mobile-page-title').text(title); // Τίτλος μέσα στο content (Mobile)
}
    
    $('#uploadBtn').click(function() {
        const fileInput = $('#csvFileInput')[0];
        const file = fileInput.files[0];
        const privacy = $('#filePrivacy').val();

        if (!file) {
            alert("Please select a CSV file!");
            return;
        }

        let formData = new FormData();
        formData.append('csvFile', file);
        formData.append('privacy', privacy);

        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

        $.ajax({
            url: '/automl/server/php/save_dataset.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log("Raw Server Response:", response);
                try {
                    const res = (typeof response === 'object') ? response : JSON.parse(response);
                    if (res.status === "success") {
                        
                        currentDatasetId = res.dataset_id; 
                        
                        
                        analyzeFileLocally(file);
                        
                        
                        loadUserDatasets();

                       
                        setTimeout(() => {
                            $('#libraryDatasetSelect').val(res.file_path);
                            
                            if (!$('#libraryDatasetSelect').val()) {
                                $('#libraryDatasetSelect').append(
                                    $('<option>', {
                                        value: res.file_path,
                                        text: file.name,
                                        selected: true
                                    }).data('id', res.dataset_id)
                                );
                            }
                        }, 500);

                        alert("Dataset uploaded and selected!");
                    } else {
                        alert("Error: " + res.message);
                    }
                } catch (e) {
                    console.error("Parsing Error:", e);
                    alert("The server sent an invalid response.");
                }
                btn.prop('disabled', false).html('<i class="bi bi-search"></i> Upload Data');
            },
            error: function() {
                alert("Failed to connect to the server.");
                btn.prop('disabled', false).html('<i class="bi bi-search"></i> Upload Data');
            }
        });
    });

    
    function analyzeFileLocally(file) {
        $('#dataSection').hide();
        $('#featuresContainer').html(`
        <div class="text-muted small italic p-3 border rounded bg-light w-100">
            <i class="bi bi-info-circle me-2"></i>Please select the target column first to display the available features.
        </div>
    `);
        $('#taskTypeSelect').prop('disabled', true).val("");
        $('#targetSelect').html('<option value="" selected disabled>Select Target...</option>');
        $('#csvTable').html('');
        $('#resultsSection').hide(); 
        $('#featureButtons').addClass('d-none');
        
        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const content = e.target.result;
                const lines = content.split('\n').filter(l => l.trim() !== "");
                if (lines.length < 1) throw "The file is empty.";

                const delimiter = lines[0].includes(';') ? ';' : ',';
                headers = lines[0].split(delimiter).map(h => h.trim().replace(/['"]+/g, ''));
                totalRows = lines.length - 1;

                updatePreviewTable(lines, delimiter);

                let targetOptions = '<option value="" selected disabled>Choose Target...</option>';
                headers.forEach(h => targetOptions += `<option value="${h}">${h}</option>`);
                $('#targetSelect').html(targetOptions);

                
                if (totalRows > 5000) {
                    
                    $('#sampleSize').val(1000).prop('disabled', false);
                    $('#rowCounter').html(`<span class="text-warning fw-bold"><i class="bi bi-exclamation-triangle"></i> Large file (${totalRows} rows). Sampling recommended.</span>`);
                } else {
                    
                    $('#sampleSize').val(totalRows).prop('disabled', true);
                    $('#rowCounter').text(`${totalRows} rows (Full Dataset)`);
                }
                $('#dataSection').fadeIn();
                // Στο τέλος του reader.onload, μετά το $('#dataSection').fadeIn();
// Στο τέλος του reader.onload, μέσα στο function analyzeFileLocally
$('#targetSelect').off('change').on('change', function() {
    const targetCol = $(this).val();
    if (targetCol) {
        // 1. Ξεκλειδώνουμε το Task Type Select
        $('#taskTypeSelect').prop('disabled', false);}
    const currentDelimiter = lines[0].includes(';') ? ';' : ',';
    const colIndex = headers.indexOf(targetCol);
    
    if (colIndex === -1) return;

    // 1. --- ΑΥΤΟΜΑΤΗ ΕΠΙΛΟΓΗ TASK (Αυτά που βάλαμε πριν) ---
    let sampleValues = [];
    for (let i = 1; i < Math.min(lines.length, 101); i++) {
        let cells = lines[i].split(currentDelimiter);
        if (cells[colIndex] !== undefined && cells[colIndex].trim() !== "") {
            sampleValues.push(cells[colIndex].trim().replace(/['"]+/g, ''));
        }
    }
    const cleanValues = sampleValues.filter(v => v !== "");
    const isNumeric = cleanValues.length > 0 && cleanValues.every(val => !isNaN(parseFloat(val)) && isFinite(val));
    const uniqueValues = [...new Set(cleanValues)].length;

    const taskDropdown = $('#taskTypeSelect');
    taskDropdown.find('option').prop('disabled', false);

    if (isNumeric && uniqueValues > 15) {
        taskDropdown.val('regression').trigger('change');
        taskDropdown.find('option[value="classification"]').prop('disabled', true);
    } else {
        taskDropdown.val('classification').trigger('change');
        taskDropdown.find('option[value="regression"]').prop('disabled', true);
    }

    // 2. --- ΔΙΟΡΘΩΣΗ ΓΙΑ ΤΑ FEATURES (Προσθήκη αυτού) ---
    let featuresHtml = '';
    headers.forEach(h => {
        // Εμφανίζουμε ως features όλα εκτός από το επιλεγμένο Target
        if (h !== targetCol) {
            featuresHtml += `
                <div class="form-check col-md-1 mb-2">
                    <input class="form-check-input feature-checkbox" type="checkbox" value="${h}" id="feat-${h}" checked>
                    <label class="form-check-label text-truncate" for="feat-${h}" title="${h}">
                        ${h}
                    </label>
                </div>`;
        }
    });

    $('#featuresContainer').html(featuresHtml);
    $('#featureButtons').removeClass('d-none'); // Εμφάνιση των Select All/None κουμπιών
});
            } catch (err) {
                alert("Error during analysis: " + err);
            }
        };
        reader.readAsText(file);
    }

    
   $('#startTrainingBtn').click(function() {
    const dataset_id = $('#libraryDatasetSelect option:selected').data('id') || currentDatasetId;
    const dataset_path = $('#libraryDatasetSelect').val();
    const target = $('#targetSelect').val();
    const metric = $('#metricSelect').val(); 
    const task_type = $('#taskTypeSelect').val(); 

    // ΔΙΟΡΘΩΣΗ: Χρησιμοποιούμε μόνο το metricDisplayName και ελέγχουμε αν είναι έγκυρο
    const metricDisplayName = $('#metricSelect option:selected').text();
    
    if (metric && !metric.toLowerCase().includes('select')) {
        localStorage.setItem('pendingMetricName', metricDisplayName);
        localStorage.setItem('pendingMetricKey', metric);
    }

    // Επιλογή Features και Frameworks
    const selectedFeatures = $('.feature-checkbox:checked').map(function() { 
        return $(this).val(); 
    }).get().join(',');
    
    const selectedFrameworks = $('.fw-check:checked').map(function() { 
        return $(this).val(); 
    }).get().join(',');

    // Validations
    if (!dataset_id || !dataset_path) return alert("Please select or upload a dataset first!");
    if (!target) return alert("Please select a Target Column!");
    if (!task_type) return alert("Please select Task Type!");
    if (!metric || metric.toLowerCase().includes('select')) return alert("Please select a Metric!");
    if (!selectedFeatures) return alert("Please select at least one Feature!");
    if (!selectedFrameworks) return alert("Please select at least one Framework!");

    const btn = $(this);
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>In Progress...');

    $.post('/automl/server/php/start_training.php', {
        dataset_id: dataset_id,
        dataset_path: dataset_path,
        target: target,
        features: selectedFeatures,
        frameworks: selectedFrameworks,
        time_limit: $('#timeLimit').val(),
        metric: metric, 
        task_type: task_type,
        sample_size: $('#sampleSize').val()
    }, function(response) {
        try {
            const res = (typeof response === 'object') ? response : JSON.parse(response);
            if (res.status === "success") {
                localStorage.setItem('activeJobId', res.job_id);
                
                // Εναλλαγή περιεχομένου UI
                $('#train-form-content').fadeOut(300, function() {
                    $('#trainingOverlay').css('display', 'flex').fadeIn();
                });
                
                checkJobStatus(res.job_id);
            } else {
                alert("Error: " + res.message);
                btn.prop('disabled', false).text('Start AUTO-ML');
            }
        } catch (e) {
            console.error("Parsing error:", e);
            btn.prop('disabled', false).text('Start AUTO-ML');
        }
    });
});

    
    window.loadUserDatasets = function() {
        $.get('/automl/server/php/get_user_datasets.php', function(response) {
            try {
                const datasets = (typeof response === 'object') ? response : JSON.parse(response);
                renderDatasetsUI(datasets);
            } catch (e) { console.error("JSON Parse Error in Library", e); }
        });
    }

 function renderDatasetsUI(datasets) {
    let rows = '';
    let options = '<option value="" disabled selected>-- Choose from Library --</option>';
    
    datasets.forEach(ds => {
        const canDelete = (ds.can_delete && ds.is_public == 0);
        options += `<option value="${ds.file_path}" data-id="${ds.id}" data-name="${ds.file_name}">${ds.file_name}</option>`;

        
        rows += `
            <tr id="row-${ds.id}">
                <td><i class="bi bi-file-earmark-spreadsheet me-2 text-success"></i>${ds.file_name}</td>
                <td>${ds.is_public == 1 ? 'Public' : 'Private'}</td>
                <td>${ds.upload_date}</td>
                <td class="text-end">
                    
                        

                        <button class="btn btn-sm btn-outline-info" title="Preview" onclick="previewDataset('${ds.file_path}', '${ds.file_name}')">
                            <i class="bi bi-eye"></i>
                        </button>
                        
                        <button class="btn btn-sm btn-primary" title="Use for Training" onclick="useDatasetForTrain('${ds.file_path}', '${ds.file_name}')">
                            <i class="bi bi-play-fill"></i>
                        </button>
                        
                        <a href="/automl/server/php/download_dataset.php?file=${ds.file_path}" 
                           class="btn btn-sm btn-outline-success" 
                           title="Download Dataset">
                            <i class="bi bi-download"></i>
                        </a>

                        ${canDelete ? `<button class="btn btn-sm btn-outline-danger" title="Delete" onclick="deleteDataset(${ds.id})"><i class="bi bi-trash"></i></button>` : ''}
                    
                </td>
            </tr>`;
    });
    
    $('#userDatasetsTable tbody').html(rows || '<tr><td colspan="4" class="text-center">Empty Library.</td></tr>');
    $('#libraryDatasetSelect').html(options);
}

    
    $('#targetSelect').change(function() {
        const selectedTarget = $(this).val();
        $('#featureButtons').removeClass('d-none'); 
        let html = '';
        headers.forEach(h => {
            if (h !== selectedTarget) {
                html += `<div class="feat-item shadow-sm">
                    <input class="form-check-input me-2 feat-check" type="checkbox" value="${h}" id="f_${h}" checked>
                    <label class="small mb-0 fw-bold" for="f_${h}">${h}</label>
                </div>`;
            }
        });
        $('#featuresContainer').html(html);
    });

    $('#libraryDatasetSelect').change(function() {
        const selectedOption = $(this).find(':selected');
        const fileNameOnDisk = selectedOption.val(); 
        const originalName = selectedOption.data('name'); 
        if (!fileNameOnDisk) return;

        fetch("/automl/uploads/datasets/" + fileNameOnDisk)
            .then(r => r.text())
            .then(data => {
                const file = new File([new Blob([data])], originalName, { type: 'text/csv' });
                analyzeFileLocally(file);
            });
    });

    window.useDatasetForTrain = function(filePath, fileName) {
        $('#nav-train').click();
        setTimeout(() => {
            $('#libraryDatasetSelect').val(filePath).trigger('change');
        }, 200);
    };

    window.deleteDataset = function(id) {
        if(confirm("Delete this file?")) {
            $.post('/automl/server/php/delete_dataset.php', { id: id }, function() { loadUserDatasets(); });
        }
    };

    function updatePreviewTable(lines, delimiter) {
        let tableHtml = '<thead class="table-light"><tr>';
        headers.forEach(h => tableHtml += `<th>${h}</th>`);
        tableHtml += '</tr></thead><tbody>';
        for (let i = 1; i < Math.min(lines.length, 11); i++) {
            const cells = lines[i].split(delimiter);
            tableHtml += '<tr>' + cells.map(c => `<td>${c.trim()}</td>`).join('') + '</tr>';
        }
        tableHtml += '</tbody>';
        $('#csvTable').html(tableHtml);
    }
$('#metricSelect').change(function() {
   
    currentMetricName = $(this).find('option:selected').text();
    console.log("Selected Metric Name:", currentMetricName);
});
    
$(document).on('click', '#runPredictBtn', function() {
    console.log("Prediction started...");
    console.log("Model Path:", selectedModelPath);
    console.log("Framework:", selectedFramework);

    const fileInput = $('#predictFile')[0];
    
    
    if (!fileInput || fileInput.files.length === 0) {
        return alert("Please select a CSV file for prediction!");
    }

    
    if (!selectedModelPath || !selectedFramework) {
        return alert("No model selected. Please go to 'My Models' and click the Play icon first.");
    }

    const btn = $(this);
    const originalHtml = btn.html();
    
   
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');

    let formData = new FormData();
    formData.append('csvFile', fileInput.files[0]);
    formData.append('model_path', selectedModelPath);
    formData.append('framework', selectedFramework);

    $.ajax({
        url: '/automl/server/php/run_inference.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            btn.prop('disabled', false).html(originalHtml);
            
            try {
                const res = (typeof response === 'object') ? response : JSON.parse(response);
                if(res.status === 'success') {
                    
                    // --- 1. ΔΗΜΙΟΥΡΓΙΑ HTML ΓΙΑ ΤΑ METRICS ---
                    let metricsHtml = '';
                    if (res.metrics && Object.keys(res.metrics).length > 0) {
                        metricsHtml = `
                            <div class="row g-2 mt-3 animate__animated animate__fadeIn">
                                <div class="col-12"><small class="text-muted fw-bold uppercase">Model Performance</small></div>`;
                        
                        for (let [key, value] of Object.entries(res.metrics)) {
                            if (key === 'type') continue; // Παραλείπουμε το κρυφό πεδίο type
                            metricsHtml += `
                                <div class="col-md-3 col-6">
                                    <div class="p-2 border rounded bg-white shadow-sm text-center">
                                        <div class="text-muted small" style="font-size:0.75rem">${key}</div>
                                        <div class="fw-bold text-primary">${value}</div>
                                    </div>
                                </div>`;
                        }
                        metricsHtml += `</div>`;
                    }

                    // --- 2. ΕΜΦΑΝΙΣΗ ΜΗΝΥΜΑΤΟΣ ΚΑΙ METRICS ---
                    $('#predictResultArea').html(`
    ${metricsHtml} 

    <div id="directPreviewArea" class="mt-4 p-3 border rounded bg-white shadow-sm animate__animated animate__fadeInUp" style="max-height: 500px; overflow-y: auto;">
        <div class="text-center p-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Generating preview table...</p>
        </div>
    </div>

    <div class="alert alert-success border-0 shadow-sm mt-4 animate__animated animate__fadeIn">
        <div class="d-flex justify-content-between align-items-center gap-3">
            <div>
                <h6 class="fw-bold mb-1"><i class="bi bi-check-circle-fill me-2"></i>Analysis Complete!</h6>
                <p class="small mb-0">The model processed your data successfully. You can download the results below.</p>
            </div>
            <a href="${res.download_url}" class="btn btn-success fw-bold" download>
                <i class="bi bi-download me-2"></i>Download Full CSV
            </a>
        </div>
    </div>
`);

                    // --- 3. FETCH ΓΙΑ ΤΟ PREVIEW (Όπως το είχες) ---
                    fetch(res.download_url)
                        .then(r => r.text())
                        .then(data => {
                            const lines = data.split('\n').filter(l => l.trim() !== "");
                            const delimiter = lines[0].includes(';') ? ';' : ',';
                            const displayRows = lines.slice(0, 21); 

                            let html = '<table class="table table-sm table-hover table-bordered mb-0">';
                            html += '<thead class="table-light text-nowrap"><tr>';
                            
                            const headers = displayRows[0].split(delimiter);
                            headers.forEach(h => html += `<th class="fw-bold text-uppercase" style="font-size:0.8rem">${h.trim()}</th>`);
                            html += '</tr></thead><tbody class="text-nowrap">';

                            for (let i = 1; i < displayRows.length; i++) {
                                const cells = displayRows[i].split(delimiter);
                                html += '<tr>' + cells.map(c => `<td>${c.trim()}</td>`).join('') + '</tr>';
                            }
                            html += '</tbody></table>';

                            if (lines.length > 21) {
                                html += `<div class="text-center p-2 text-muted small border-top bg-light">
                                            Showing first 20 rows. Download for all ${lines.length - 1} predictions.
                                         </div>`;
                            }

                            $('#directPreviewArea').html(html);
                        })
                        .catch(err => {
                            $('#directPreviewArea').html('<div class="alert alert-warning">Preview error. Please download the file.</div>');
                        });

                    if (typeof window.loadUserPredictions === 'function') {
                        window.loadUserPredictions();
                    }

                } else { 
                    alert("Error: " + res.message); 
                }
            } catch(e) { 
                console.error("Parse error:", e);
                alert("Invalid response from server."); 
            }
        },
        error: function(xhr, status, error) {
            btn.prop('disabled', false).html(originalHtml);
            console.error("AJAX Error:", status, error);
            alert("Connection error. Please check the console (F12).");
        }
    });
});

    $('#taskTypeSelect').on('change', function() {
        const taskType = $(this).val(); 
        const metricSelect = $('#metricSelect');

        metricSelect.val(''); 

        if (taskType === 'classification') {
            $('.opt-class').show();
            $('.opt-regr').hide();
        } else {
            $('.opt-regr').show();
            $('.opt-class').hide();
        }
    });
    loadUserDatasets();
});



window.openUseModelModal = function(modelPath, framework, algorithm) {
    document.activeElement.blur();
    $('.modal').modal('hide');

    
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open'); 
    $('body').css('padding-right', ''); 

    
    selectedModelPath = modelPath;
    selectedFramework = framework;
    
    
    $('#display-model-id').text(modelPath.split('/').pop()); 
    $('#display-framework').text(framework.toUpperCase());
    $('#modalAlgorithmDisplay').text(algorithm.toUpperCase());
    $('#predict-placeholder').hide();
    $('#predict-container').show();
    $('#predictResultArea').html(''); 
    
    
    function goToSection(section) {
    const el = $(`[data-section="${section}"]`).first();
    if (el.length) {
        el.trigger('click');
    } else {
        // fallback για desktop αν κάτι λείπει
        $('#nav-' + section).trigger('click');
    }
}
goToSection('predict');
};

function checkJobStatus(jobId) {
    const progressBar = $('#mainProgressBar');
    const progressText = $('#mainProgressText');
    const overlay = $('#trainingOverlay');

    const interval = setInterval(() => {
        $.get(`/automl/server/php/get_job_status.php?id=${jobId}`, function(res) {
            try {
                const data = (typeof res === 'object') ? res : JSON.parse(res);
                
                if (data.status === 'completed' || data.status === 'failed') {
                    clearInterval(interval);
                    localStorage.removeItem('activeJobId');
                    
                    if (data.status === 'completed') {
                        progressBar.css('width', '100%');
                        progressText.text('100%');
                    }

                    setTimeout(() => {
                        // Επαναφορά: Κλείνουμε το overlay και δείχνουμε πάλι τη φόρμα
                        overlay.fadeOut(300, function() {
                            $('#train-form-content').fadeIn();
                            $('#startTrainingBtn').prop('disabled', false).text('Start AUTO-ML');
                        });
                        
                        if (data.status === 'completed' && data.results_json) {
                            displayResults(JSON.parse(data.results_json));
                            window.markJobAsSeen(jobId);
                        } else if (data.status === 'failed') {
                            alert("Training Failed");
                        }
                    }, 1000);

                } else if (data.status === 'running') {
                    let width = parseInt(progressBar[0].style.width) || 0;
                    if (width < 95) {
                        width += 2;
                        progressBar.css('width', width + '%');
                        progressText.text(width + '%');
                    }
                }
            } catch (e) { console.error(e); }
        });
    }, 3000);
}

function displayResults(results) {
    // 1. ΑΝΑΚΤΗΣΗ ΜΕΤΡΙΚΗΣ ΑΠΟΚΛΕΙΣΤΙΚΑ ΑΠΟ ΤΟΝ WORKER
    let displayLabel = "Score"; 
    const frameworks = Object.keys(results);
    
    // Ψάχνουμε στα αποτελέσματα των frameworks για το πεδίο metric_used που βάλαμε στον Worker
    for (let fw of frameworks) {
        const fwMetric = results[fw].metric_used || results[fw].metric;
        if (fwMetric && !fwMetric.toLowerCase().includes("select")) {
            displayLabel = fwMetric;
            break; // Βρήκαμε την έγκυρη μετρική από τη βάση, σταματάμε
        }
    }

    // 2. ΠΡΟΣΔΙΟΡΙΣΜΟΣ ΤΑΞΙΝΟΜΗΣΗΣ (Lower is Better)
    // Βασιζόμαστε στο displayLabel που ήρθε από τη βάση
    const checkMetric = displayLabel.toLowerCase();
    const lowerIsBetter = ['mse', 'rmse', 'mae', 'logloss'].some(m => checkMetric.includes(m));

    // 3. ΤΑΞΙΝΟΜΗΣΗ
    const sortedFws = frameworks.sort((a, b) => {
        let scoreA = results[a].best_score !== null ? results[a].best_score : (lowerIsBetter ? Infinity : -Infinity);
        let scoreB = results[b].best_score !== null ? results[b].best_score : (lowerIsBetter ? Infinity : -Infinity);
        return lowerIsBetter ? scoreA - scoreB : scoreB - scoreA;
    });

    // 4. ΔΗΜΙΟΥΡΓΙΑ ΓΡΑΜΜΩΝ ΠΙΝΑΚΑ
    let rows = '';
    sortedFws.forEach((fw) => {
        const res = results[fw];
        const algoName = res.best_algorithm ? res.best_algorithm.toUpperCase() : (res.status === 'success' ? 'N/A' : '-');
        let rawScore = (res.best_score !== undefined && res.best_score !== null) ? res.best_score : null;
        let formattedScore = (rawScore !== null) ? parseFloat(rawScore).toFixed(4) : "N/A";
        const mId = res.model_id || null;
        
        const isWinner = (mId !== null && formattedScore !== "N/A");
        const rowClass = isWinner ? 'table-primary fw-bold' : '';
        const trophy = isWinner ? '🏆 ' : '';

        let actionButtons = isWinner ? `
            <div class="text-end">
                <button class="btn btn-sm btn-primary" onclick="openUseModelModal('${res.best_model}', '${fw}', '${algoName}')" title="Use Model">
                    <i class="bi bi-play-circle"></i>
                </button>
                <a href="/automl/server/php/download_model.php?id=${mId}" class="btn btn-sm btn-outline-success" title="Download">
                    <i class="bi bi-download"></i>
                </a>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteModelFromResult('${fw}', ${mId})" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>` : '<span class="text-muted small">N/A</span>';

        rows += `
        <tr id="res-row-${fw}" class="${rowClass}">
            <td>${trophy}${fw.toUpperCase()}</td>
            <td><span class="badge border text-primary bg-white">${algoName}</span></td>
            <td><span class="badge bg-dark">${formattedScore}</span></td>
            <td><small>${res.best_model || "No model"}</small></td>
            <td class="text-end">${actionButtons}</td>
        </tr>`;
    });

    // 5. ΕΜΦΑΝΙΣΗ ΣΤΟ MODAL
    const modalHtml = `
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Framework</th>
                        <th>Algorithm</th>
                        <th>${displayLabel}</th> 
                        <th>Best Model</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>`;

    $('#resultsModalBody').html(modalHtml);
    
    const modalElement = document.getElementById('resultsModal');
    if (!$(modalElement).hasClass('show')) {
        const myModal = new bootstrap.Modal(modalElement);
        myModal.show();
    }

    $('#resultsTable tbody').html(rows);
    $('#resultsSection').fadeIn();
}


window.previewDataset = function(filePath, fileName) {
    
    $('#previewModalLabel').html('<i class="bi bi-eye me-2"></i> Preview: ' + fileName);
    $('#previewModalTable').html('<tr><td class="text-center p-5"><div class="spinner-border text-primary"></div><br>Reading file...</td></tr>');
    
    
    const modalElement = document.getElementById('previewModal');
    const myModal = new bootstrap.Modal(modalElement);
    myModal.show();

   
    fetch("/automl/uploads/datasets/" + filePath)
        .then(r => {
            if (!r.ok) throw new Error("File not found");
            return r.text();
        })
        .then(data => {
            const lines = data.split('\n').filter(l => l.trim() !== "");
            if (lines.length === 0) {
                $('#previewModalTable').html('<tr><td class="text-center p-3 text-muted">The file is empty.</td></tr>');
                return;
            }

            const delimiter = lines[0].includes(';') ? ';' : ',';
            const displayRows = lines.slice(0, 11); 

            let html = '<thead class="table-dark"><tr>';
            const headers = displayRows[0].split(delimiter);
            headers.forEach(h => html += `<th>${h.trim().replace(/['"]+/g, '')}</th>`);
            html += '</tr></thead><tbody>';

            for (let i = 1; i < displayRows.length; i++) {
                const cells = displayRows[i].split(delimiter);
                html += '<tr>' + cells.map(c => `<td>${c.trim()}</td>`).join('') + '</tr>';
            }
            html += '</tbody>';
            
            $('#previewModalTable').html(html);
        })
        .catch(err => {
            console.error("Preview error:", err);
            $('#previewModalTable').html('<tr><td class="text-danger text-center p-3">Failed to load preview. The file may be missing or protected.</td></tr>');
        });
};

window.deleteModelFromResult = function(fwName, id) {
    if (!id) return alert("Model ID not found.");
    if (confirm("Delete this model permanently?")) {
        $.post('/automl/server/php/delete_model.php', { id: id }, function(response) {
            $(`#res-row-${fwName}`).fadeOut();
            if (typeof loadUserModels === "function") loadUserModels(); // Ανανέωση και του άλλου tab
        });
    }
};

$(document).on('click', '#selectAllFeatures', function() {
    $('.feat-check').prop('checked', true);
});

$(document).on('click', '#selectNoneFeatures', function() {
    $('.feat-check').prop('checked', false);
});
window.previewPredictionCSV = function(fileName) {
    
    $('#previewModalLabel').html('<i class="bi bi-eye me-2"></i> Prediction Preview: ' + fileName);
    $('#previewModalTable').html('<tr><td class="text-center p-5"><div class="spinner-border text-primary"></div></td></tr>');
    
    const modalElement = document.getElementById('previewModal');
    const myModal = new bootstrap.Modal(modalElement);
    myModal.show();

    fetch("/automl/uploads/inference/" + fileName)
        .then(r => r.text())
        .then(data => {
            const lines = data.split('\n').filter(l => l.trim() !== "");
            const delimiter = lines[0].includes(';') ? ';' : ',';
            const displayRows = lines.slice(0, 20);

            let html = '<thead class="table-dark"><tr>';
            const headers = displayRows[0].split(delimiter);
            headers.forEach(h => html += `<th>${h.trim()}</th>`);
            html += '</tr></thead><tbody>';

            for (let i = 1; i < displayRows.length; i++) {
                const cells = displayRows[i].split(delimiter);
                html += '<tr>' + cells.map(c => `<td>${c.trim()}</td>`).join('') + '</tr>';
            }
            html += '</tbody>';
            $('#previewModalTable').html(html);
        })
        .catch(err => {
            $('#previewModalTable').html('<tr><td class="text-danger text-center">Error loading file.</td></tr>');
        });
};
$(document).ready(function() {
    // Πιάνουμε το κλικ σε οποιοδήποτε μενού (PC ή Mobile)
    $('.list-group-item[data-section]').on('click', function(e) {
        e.preventDefault();
        
        // 1. Παίρνουμε το όνομα του section (π.χ. train)
        const section = $(this).data('section');

        // 2. Αφαιρούμε το active από ΟΛΑ τα μενού
        $('.list-group-item').removeClass('active');

        // 3. Προσθέτουμε το active σε ΟΛΑ τα links που αφορούν αυτό το section
        // (Έτσι θα ανάψει το μπλε και στο sidebar και στο offcanvas ταυτόχρονα)
        $('[data-section="' + section + '"]').addClass('active');

        // 4. Εμφάνιση του σωστού section περιεχομένου
        $('.content-section').hide();
        $('#section-' + section).fadeIn();

        // 5. Ενημέρωση Τίτλων
        const titles = {
            'train': 'Train Model',
            'datasets': 'My Datasets',
            'models': 'My Models',
            'my-predictions': 'My Predictions History',
            'predict': 'Predict Analysis'
        };
        
        $('#page-title').text(titles[section]);
        $('#mobile-page-title').text(titles[section]);

        // 6. Φόρτωση δεδομένων (AJAX calls)
        if (section === 'datasets') loadUserDatasets();
        if (section === 'models') loadUserModels();
        if (section === 'my-predictions') loadUserPredictions();
        if (section === 'predict') {
    updatePredictModelsDropdown();
}
        
        
    });
});
// Χρησιμοποιούμε $(document).on για να πιάνουμε τα κλικ ακόμα και αν το HTML ανανεώνεται
$(document).on('click', '#selectAllFeatures', function(e) {
    e.preventDefault(); // Αποφεύγουμε τυχόν submit της φόρμας
    $('.feature-checkbox').prop('checked', true);
    console.log("All features selected");
});

$(document).on('click', '#selectNoneFeatures', function(e) {
    e.preventDefault();
    $('.feature-checkbox').prop('checked', false);
    console.log("All features deselected");
});