<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /automl/login");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoML App - Dashboard</title>
    
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="/automl/src/css/dashboard.css">
   <link rel="icon" href="/automl/src/img/favicon.ico" type="image/x-icon">
</head>
<body>

<div class="d-flex" id="wrapper">
    
    <div id="sidebar-wrapper">
        <a href="/automl" class="text-decoration-none">
        <div class="p-4 fs-4 fw-bold text-white border-bottom border-secondary mb-3" style="position:relative;">
            
            <img src="/automl/src/img/dashboardlogo.png" alt="Logo" height="48px" class="me-1" style="margin-left:-10px!important; vertical-align:bottom!important;" >
            <span style="position:absolute; bottom:28%;">Auto<span class="text-primary">ML</span></span>
        </div>
        </a>
        <div class="list-group list-group-flush">
            <a id="nav-train" class="list-group-item list-group-item-action active">
                <i class="bi bi-cpu-fill me-2"></i> <span>Train Model</span>
            </a>
            <a id="nav-datasets" class="list-group-item list-group-item-action">
                <i class="bi bi-database-fill me-2"></i> <span>My Datasets</span>
            </a>
            <a id="nav-models" class="list-group-item list-group-item-action">
                <i class="bi bi-robot me-2"></i> <span>My Models</span>
            </a>
            <a id="nav-my-predictions" class="list-group-item list-group-item-action">
                <i class="bi bi-clock-history me-2"></i> <span>My Predictions</span>
            </a>
            <a id="nav-predict" class="list-group-item list-group-item-action">
                <i class="bi bi-magic me-2"></i> <span>Predict</span>
            </a>
            <a href="/automl/server/php/logout.php" class="logoutbtn list-group-item list-group-item-action text-danger mt-5">
                <i class="bi bi-box-arrow-right me-2"></i><span> Logout</span>
            </a>
        </div>
        <button id="sidebar-toggle" class="toggle-btn">
    <i class="bi bi-chevron-left"></i>
</button>
    </div>

    <div id="page-content-wrapper">
    <nav class="navbar navbar-expand-lg navbar-light bg-white p-3 mb-4 shadow-sm">
        <div class="container-fluid">
            

        <a class="navbar-brand d-lg-none fw-bold fs-4" href="/automl">
            <img src="/automl/src/img/logo2.png" alt="Logo" height="40px" class="me-1" style="vertical-align:text-bottom!important;">
            Auto<span class="text-primary">ML</span>
        </a>
            <button class="btn btn-outline-dark d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
                <i class="bi bi-list"></i>
            </button>
            <h5 class="mb-0 fw-bold d-none d-lg-block" id="page-title">Train Model</h5>

            <span class="ms-auto badge bg-light text-dark border p-2 d-none d-lg-block">
                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
            </span>
        </div>
    </nav>

    <div class="offcanvas offcanvas-end bg-dark text-white d-lg-none" tabindex="-1" id="mobileSidebar">
        <div class="offcanvas-header border-bottom border-secondary">
            <div class="d-flex align-items-center">
                    <i class="bi bi-person-circle fs-4 me-2 text-primary"></i>
                    <span class="fw-bold small"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column p-0">
            <div class="list-group list-group-flush flex-grow-1" id="mobile-menu">
    <a class="list-group-item list-group-item-action py-3 active" data-bs-dismiss="offcanvas" data-section="train">
        <i class="bi bi-cpu-fill me-2"></i> Train Model
    </a>
    <a class="list-group-item list-group-item-action py-3" data-bs-dismiss="offcanvas" data-section="datasets">
        <i class="bi bi-database-fill me-2"></i> My Datasets
    </a>
    <a class="list-group-item list-group-item-action py-3" data-bs-dismiss="offcanvas" data-section="models">
        <i class="bi bi-robot me-2"></i> My Models
    </a>
    <a class="list-group-item list-group-item-action py-3" data-bs-dismiss="offcanvas" data-section="my-predictions">
        <i class="bi bi-clock-history me-2"></i> My Predictions
    </a>
    <a class="list-group-item list-group-item-action py-3" data-bs-dismiss="offcanvas" data-section="predict">
        <i class="bi bi-magic me-2"></i> Predict
    </a>
</div>
            
            <div class="p-3 border-top border-secondary bg-black">
                
                <a href="/automl/server/php/logout.php" class="btn btn-sm btn-outline-danger w-100">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </a>
            </div>
        </div>
    </div>
        <div class="container-fluid px-4">
            
            <div id="section-train" class="content-section active">
                <div id="trainingOverlay" style="display:none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.9); z-index: 100; flex-direction: column; align-items: center; justify-content: center; border-radius: 15px;">
        <div class="text-center" style="max-width: 80%;">
            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
            <h4 class="fw-bold">Training in Progress...</h4>
            
            <p class="small text-muted mt-3">You can navigate the menu or log out.<br>The training will continue in the background.</p>
        </div>
    </div>
    <div id="train-form-content">
                <div class="card main-card p-4 mb-4 border-start border-primary border-5">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">1. Select from Library</label>
                            <select id="libraryDatasetSelect" class="form-select mb-2">
                                <option value="" selected disabled>-- Select from Existing --</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">2. Upload New CSV</label>
                            
                            <input type="file" id="csvFileInput" class="form-control" accept=".csv">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Visibility (for new files)</label>
                            <select id="filePrivacy" class="form-select">
                                <option value="0">Private</option>
                                <option value="1">Public</option>
                            </select>
                        </div>
                        
                        <div class="col-md-8 d-flex align-items-end">
                            <button class="btn btn-primary w-100 fw-bold" id="uploadBtn">
                                <i class="bi bi-search"></i> Upload Data
                            </button>
                        </div>
                    </div>
                </div>

                <div id="dataSection" style="display:none;">
                    <div class="card main-card mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-muted">Data Preview</span>
                            <span id="rowCounter" class="badge bg-secondary"></span>
                        </div>
                        <div class="table-responsive" style="max-height: 250px;">
                            <table class="table table-sm table-hover mb-0" id="csvTable"></table>
                        </div>
                    </div>

                    <div class="card main-card mb-4 p-4">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-danger">Target</label>
                                <select id="targetSelect" class="form-select form-select-lg mb-3 border-danger"></select>
                                
                                <div class="row g-2">
                                    <div class="col-4">
                                        <label class="small fw-bold">Sampling</label>
                                        <input type="number" id="sampleSize" class="form-control form-control-sm" value="500">
                                    </div>
                                    <div class="col-4">
                                        <label class="small fw-bold">Time (Seconds)</label>
                                        <input type="number" id="timeLimit" class="form-control form-control-sm" value="60">
                                    </div>
                                    <div class="col-4">
     <label class="small fw-bold">Metric</label>
     <select id="metricSelect" class="form-select form-select-sm">
        <option value="" disabled selected>Select...</option>
        
        <option value="accuracy" class="opt-class">Accuracy</option>
        <option value="f1" class="opt-class">F1 Score</option>
        <option value="precision" class="opt-class">Precision</option>
        <option value="recall" class="opt-class">Recall</option>
        
        <option value="rmse" class="opt-regr" style="display:none;">RMSE</option>
        <option value="mse" class="opt-regr" style="display:none;">MSE</option>
        <option value="mae" class="opt-regr" style="display:none;">MAE</option>
        <option value="r2" class="opt-regr" style="display:none;">R² Score</option>
      </select>
      </div>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="d-flex mobile-pleft justify-content-between mb-2 ps-3">
                                    <label class="fw-bold text-primary">Features</label>
                                    <div id="featureButtons" class="d-none">
                                        <button class="btn btn-xs btn-outline-dark" id="selectAllFeatures">All</button>
                                        <button class="btn btn-xs btn-outline-dark" id="selectNoneFeatures">None</button>
                                    </div>
                                </div>
                                <div id="featuresContainer" class="ms-3"><div class="text-muted small italic p-3 border rounded bg-light">
            <i class="bi bi-info-circle me-2"></i>Please select the target column first to display the available features.
        </div></div>
                            </div>
                        </div>
                        <div class="col-md-4 mt-3">
      <label class="fw-bold text-primary">Task Type</label>
      <select id="taskTypeSelect" class="form-select mb-3 shadow-sm" disabled>
        <option value="classification" selected>Classification</option>
        <option value="regression">Regression</option>
       </select>
    
    
       </div>

                        <hr class="my-4">

                        <div class="text-center">
                            <label class="fw-bold text-primary d-block mb-3">Choose Frameworks</label>
                            <div class="d-flex justify-content-center flex-wrap gap-4 mb-4">
                                <div class="form-check">
                                    <input class="form-check-input fw-check" type="checkbox" value="mljar" id="fw_mljar" checked>
                                    <label class="form-check-label fw-bold" for="fw_mljar">MLJAR</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input fw-check" type="checkbox" value="flaml" id="fw_flaml" checked>
                                    <label class="form-check-label fw-bold" for="fw_flaml">FLAML</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input fw-check" type="checkbox" value="h2o" id="fw_h2o">
                                    <label class="form-check-label fw-bold" for="fw_h2o">H2O.ai</label>
                                </div>
                                
                            </div>
                            <button class="btn btn-primary btn-lg px-5 py-3 shadow fw-bold" id="startTrainingBtn">
                                <i class="bi bi-lightning-charge-fill me-2"></i>Start AUTO-ML
                            </button>
                            
                        </div>
                    </div>
                </div>

</div>
            </div>

            <div id="section-datasets" class="content-section">
                <div class="card main-card p-4">
                    <h5 class="mb-4 fw-bold"><i class="bi bi-collection-fill me-2"></i>My Datasets</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="userDatasetsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>File</th>
                                    <th>Visibility</th>
                                    <th>Date</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div id="section-models" class="content-section">
          <div class="card main-card p-4">
          <h5 class="mb-4 fw-bold"><i class="bi bi-robot me-2"></i>Trained Models History</h5>
         <div class="table-responsive">
            <table class="table table-hover align-middle" id="userModelsTable">
                <thead class="table-light">
                    <tr>
                        <th>Model ID</th>
                        <th>Dataset</th>
                        <th>Target</th>
                        <th>Task Type</th>
                        <th>Framework</th>
                        <th>Algorithm</th>
                        <th>Metric</th>
                        <th>Score</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    </tbody>
            </table>
        </div>
    </div>
</div>
<div id="section-my-predictions" class="content-section" style="display:none;">
    <div class="card main-card p-4 shadow-sm border-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-clock-history text-primary me-2"></i>My Predictions History
            </h5>
           
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="predictionsTable">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Input File</th>
                        <th>Framework</th>
                        <th>Model</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5" class="text-center p-4">
                            Empty Library.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div id="section-predict" class="content-section" style="display:none;">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-4"><i class="bi bi-cpu me-2"></i> Run Inference Analysis</h5>
            
            <div class="mb-4">
                <label class="form-label fw-bold text-muted small uppercase">1. Select Trained Model</label>
                <select id="modelSelectPredict" class="form-select form-select-lg">
                    <option value="" selected disabled>-- Choose a Model --</option>
                </select>
            </div>

            <div id="predict-upload-area" class="mb-4">
                <label class="form-label fw-bold text-muted small uppercase">2. Upload Data for Prediction (.csv)</label>
                <input type="file" id="predictFile" class="form-control" accept=".csv">
            </div>

            <button id="runPredictBtn" class="btn btn-primary btn-lg w-100 fw-bold">
                <i class="bi bi-play-circle-fill me-2"></i> RUN PREDICTION
            </button>

            <div id="predictResultArea"></div>
        </div>
    </div>
</div>

        </div>
    </div>
</div>

<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="previewModalLabel">
                    <i class="bi bi-eye me-2"></i>Dataset Preview
                </h5>
                <!-- Προσθήκη κουμπιού Χ στην κορυφή για σωστό accessibility escape -->
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table id="previewModalTable" class="table table-sm table-striped table-hover mb-0"></table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <small class="text-muted me-auto" id="previewModalFooterNote">Showing first 10 rows</small>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
    <div class="modal fade" id="useModelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-cpu me-2"></i> Use Model</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="modelInfoText" class="fw-bold mb-3"></p>
                <div class="alert alert-info small">
                    <i class="bi bi-info-circle me-1"></i> 
                    Upload a CSV. If it contains the target column, we will perform <b>Validation</b>. If not, we will <b>Predict</b> the missing values.
                </div>
                <label class="form-label">Select CSV File:</label>
                <input type="file" id="useModelFile" class="form-control" accept=".csv">
                
                <div id="useModelStatus" class="mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="runUseModelBtn">Run Analysis</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="resultsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-trophy"></i> Your model is ready!</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="resultsModalBody">
        </div>
    </div>
  </div>
</div>
 
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/automl/src/js/dashboard.js"></script>
</body>
</html>
