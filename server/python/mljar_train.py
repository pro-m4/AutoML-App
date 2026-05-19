import sys
import pandas as pd
import numpy as np
import json
import os
import shutil
import joblib  # <-- Εδώ προστέθηκε για να μην ξαναχτυπήσει σφάλμα!

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
        # 2. LOAD DATA
        # =========================
        df = pd.read_csv(dataset_path)

        # remove unnamed columns
        df = df.loc[:, ~df.columns.str.contains('^Unnamed|^index', case=False)]

        # X / y split
        if features:
            valid_features = [f for f in features if f in df.columns]
            X = df[valid_features].copy()
        else:
            X = df.drop(columns=[target_column], errors="ignore").copy()

        y = df[target_column].copy()

        # =========================
        # 3. FEATURE ENCODING
        # =========================
        feature_encoders = {}

        for col in X.columns:
            if X[col].dtype == 'object' or str(X[col].dtype).startswith('string'):
                le = LabelEncoder()
                X[col] = le.fit_transform(X[col].astype(str))
                feature_encoders[col] = le

        # =========================
        # 4. TARGET ENCODING
        # =========================
        target_encoder = None

        if task_type == 'classification':
            target_encoder = LabelEncoder()
            y = target_encoder.fit_transform(y.astype(str))
        else:
            y = pd.to_numeric(y, errors='coerce').fillna(0)

        # =========================
        # 5. MODEL
        # =========================
        results_path = f"mljar_output_{job_id}"

        # Καθορισμός του σωστού task για το MLJAR
        if task_type == 'classification':
            num_classes = len(np.unique(y))
            ml_task = "binary_classification" if num_classes == 2 else "multiclass_classification"
        else:
            ml_task = "regression"

        automl = AutoML(
            results_path=results_path,
            total_time_limit=time_limit,
            mode="Explain",
            ml_task=ml_task,
            random_state=123
        )

        automl.fit(X, y)
        
        # =========================
        # 6. LEADERBOARD
        # =========================
        lb_path = os.path.join(results_path, "leaderboard.csv")
        best_algo = "MLJAR Model"

        if os.path.exists(lb_path):
            lb = pd.read_csv(lb_path)
            if len(lb) > 0 and "model_type" in lb.columns:
                best_algo = lb.iloc[0]["model_type"]

        # =========================
        # 7. PREDICTION
        # =========================
        y_pred = automl.predict(X)
        
        # Αν το y_pred επιστραφεί ως DataFrame ή Series, το κάνουμε numpy array
        if hasattr(y_pred, 'values'):
            y_pred = y_pred.values
            
        y_pred = np.array(y_pred).flatten()

        if task_type == 'classification':
            # Μετατροπή σε int σε περίπτωση που επιστραφούν float labels
            y_pred = y_pred.astype(int)
            y = y.astype(int)
        else:
            y = y.astype(float)
            y_pred = y_pred.astype(float)

        # =========================
        # 8. METRICS
        # =========================
        if task_type == 'classification':
            if user_metric == 'f1':
                score = f1_score(y, y_pred, average='weighted', zero_division=0)
            elif user_metric == 'precision':
                score = precision_score(y, y_pred, average='weighted', zero_division=0)
            elif user_metric == 'recall':
                score = recall_score(y, y_pred, average='weighted', zero_division=0)
            else:
                score = accuracy_score(y, y_pred)
        else:
            if user_metric == 'rmse':
                score = np.sqrt(mean_squared_error(y, y_pred))
            elif user_metric == 'mae':
                score = mean_absolute_error(y, y_pred)
            elif user_metric == 'r2':
                score = r2_score(y, y_pred)
            else:
                score = mean_squared_error(y, y_pred)

        # =========================
        # 9. SAVE MODEL
        # =========================
        model_name = f"model_job_{job_id}_mljar"
        
        if os.path.exists(results_path):
            # 9.1 Αποθήκευση του Target Encoder
            if task_type == 'classification' and target_encoder is not None:
                encoder_save_path = os.path.join(results_path, "mljar_target_encoder.joblib")
                joblib.dump(target_encoder, encoder_save_path)
                print(f"[+] Encoder saved successfully inside {results_path}")
            
            # 9.2 Δημιουργία custom info αρχείου για το Predict (Όπως το H2O/FLAML)
            info_data = {
                "target_col": target_column,
                "task_type": task_type
            }
            with open(os.path.join(results_path, "mljar_info.json"), "w") as f:
                json.dump(info_data, f)
        else:
            print(f"[!] Warning: {results_path} does not exist.")

        # Τώρα δημιουργούμε το zip αρχείο που περιέχει τα πάντα
        shutil.make_archive(model_name, 'zip', results_path)

        if os.path.exists(results_path):
            shutil.rmtree(results_path)

        # =========================
        # 10. OUTPUT
        # =========================
        print(json.dumps({
            "status": "success",
            "best_model": f"{model_name}.zip",
            "best_score": round(float(score), 4),
            "best_algorithm": best_algo,
            "framework": "mljar"
        }))

    except Exception as e:
        print(json.dumps({
            "status": "failed",
            "error": str(e)
        }))


if __name__ == "__main__":
    run_mljar()