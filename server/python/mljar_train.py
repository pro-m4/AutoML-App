import sys
import pandas as pd
import numpy as np
import json
import os
import shutil
import joblib  
import time
from supervised.automl import AutoML
from sklearn.metrics import (
    accuracy_score,
    f1_score,
    precision_score,
    recall_score,
    mean_squared_error,
    mean_absolute_error,
    r2_score
)
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import LabelEncoder


def run_mljar():
    try:
        # =========================
        # 1. ARGUMENTS
        # =========================
        dataset_path = sys.argv[1]
        target_column = sys.argv[2]

        raw_features = sys.argv[3].split(',') if sys.argv[3] and sys.argv[3] != 'None' else None
        features = [f.strip() for f in raw_features if f.strip()] if raw_features else None

        time_limit = int(sys.argv[4])
        user_metric = sys.argv[5].lower().replace('-score', '').replace(' ', '')
        task_type = sys.argv[6].lower()
        job_id = sys.argv[7] if len(sys.argv) > 7 else "default"

        # =========================
        # 2. LOAD DATA & CLEAN
        # =========================
        df = pd.read_csv(dataset_path)

        # remove unnamed/index columns
        df = df.loc[:, ~df.columns.str.contains('^Unnamed|^index', case=False)]

        # X / y split
        if features:
            valid_features = [f for f in features if f in df.columns]
            X = df[valid_features].copy()
        else:
            X = df.drop(columns=[target_column], errors="ignore").copy()

        y = df[target_column].copy()

        # =========================
        # 3. TARGET ENCODING & CLEANING
        # =========================
        target_encoder = None

        if task_type == 'classification':
            target_encoder = LabelEncoder()
            y = target_encoder.fit_transform(y.astype(str))
        else:
            # Για regression, καθαρίζουμε NaN values από το target
            y = pd.to_numeric(y, errors='coerce')
            # Κρατάμε μόνο τις γραμμές που έχουν έγκυρο target
            valid_idx = y.notna()
            X = X[valid_idx].reset_index(drop=True)
            y = y[valid_idx].reset_index(drop=True)

        # =========================
        # 4. TRAIN / TEST SPLIT (Για σωστό evaluation)
        # =========================
        if task_type == 'classification':
            X_train, X_test, y_train, y_test = train_test_split(
                X, y, test_size=0.2, random_state=42, stratify=y
            )
        else:
            X_train, X_test, y_train, y_test = train_test_split(
                X, y, test_size=0.2, random_state=42
            )

        # =========================
        # 5. MODEL CONFIGURATION
        # =========================
        results_path = f"mljar_output_{job_id}"

        if task_type == 'classification':
            num_classes = len(np.unique(y_train))
            ml_task = "binary_classification" if num_classes == 2 else "multiclass_classification"
        else:
            ml_task = "regression"

        # Επιλογή βέλτιστου metric για το εσωτερικό validation του MLJAR
        eval_metric = "logloss" if task_type == 'classification' else "rmse"
        if user_metric in ["f1", "accuracy", "rmse", "mae", "r2"]:
            eval_metric = user_metric
        elif user_metric == "precision" or user_metric == "recall":
            eval_metric = "logloss" # Το MLJAR δεν υποστηρίζει direct precision/recall optimization εύκολα

        automl = AutoML(
            results_path=results_path,
            total_time_limit=time_limit,
            mode="Explain", # Ή "Compete" αν θέλεις μέγιστη ακρίβεια με Ensembles
            ml_task=ml_task,
            eval_metric=eval_metric,
            random_state=123
        )
        
        start_time = time.time()
        automl.fit(X_train, y_train)
        training_time = time.time() - start_time
        
        # =========================
        # 6. LEADERBOARD
        # =========================
        lb_path = os.path.join(results_path, "leaderboard.csv")
        best_algo = "MLJAR Model"

        if os.path.exists(lb_path):
            try:
                lb = pd.read_csv(lb_path)
                if len(lb) > 0 and "model_type" in lb.columns:
                    best_algo = lb.iloc[0]["model_type"]
            except:
                pass

        # =========================
        # 7. PREDICTION (Στο Test Set!)
        # =========================
        y_pred = automl.predict(X_test)
        
        if hasattr(y_pred, 'values'):
            y_pred = y_pred.values
            
        y_pred = np.array(y_pred).flatten()

        if task_type == 'classification':
            y_pred = y_pred.astype(int)
            y_test = y_test.astype(int)
        else:
            y_test = y_test.astype(float)
            y_pred = y_pred.astype(float)

        # =========================
        # 8. METRICS
        # =========================
        if task_type == 'classification':
            if user_metric == 'f1':
                score = f1_score(y_test, y_pred, average='weighted', zero_division=0)
            elif user_metric == 'precision':
                score = precision_score(y_test, y_pred, average='weighted', zero_division=0)
            elif user_metric == 'recall':
                score = recall_score(y_test, y_pred, average='weighted', zero_division=0)
            else:
                score = accuracy_score(y_test, y_pred)
        else:
            if user_metric == 'rmse':
                score = np.sqrt(mean_squared_error(y_test, y_pred))
            elif user_metric == 'mae':
                score = mean_absolute_error(y_test, y_pred)
            elif user_metric == 'r2':
                score = r2_score(y_test, y_pred)
            else:
                score = mean_squared_error(y_test, y_pred)

        # =========================
        # 9. SAVE MODEL & ARTIFACTS
        # =========================
        model_name = f"model_job_{job_id}_mljar"
        
        if os.path.exists(results_path):
            # Αποθήκευση Target Encoder
            if task_type == 'classification' and target_encoder is not None:
                encoder_save_path = os.path.join(results_path, "mljar_target_encoder.joblib")
                joblib.dump(target_encoder, encoder_save_path)
            
            # Αποθήκευση Metadata
            info_data = {
                "target_col": target_column,
                "task_type": task_type,
                "features": list(X.columns)
            }
            with open(os.path.join(results_path, "mljar_info.json"), "w") as f:
                json.dump(info_data, f)

        # Δημιουργία zip
        shutil.make_archive(model_name, 'zip', results_path)

        if os.path.exists(results_path):
            shutil.rmtree(results_path)

        # =========================
        # 10. OUTPUT JSON
        # =========================
        print(json.dumps({
            "status": "success",
            "best_model": f"{model_name}.zip",
            "best_score": round(float(score), 4),
            "best_algorithm": best_algo,
            "framework": "mljar",
            "training_time": round(training_time, 3)
        }))

    except Exception as e:
        print(json.dumps({
            "status": "failed",
            "error": str(e)
        }))


if __name__ == "__main__":
    run_mljar()