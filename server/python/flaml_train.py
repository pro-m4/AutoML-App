import sys
import pandas as pd
import numpy as np
import json
import joblib
from flaml import AutoML
from sklearn.metrics import (accuracy_score, f1_score, precision_score, recall_score, 
                             mean_squared_error, mean_absolute_error, r2_score)
from sklearn.preprocessing import LabelEncoder

def run_flaml():
    try:
        # 1. Arguments
        dataset_path = sys.argv[1]
        target_column = sys.argv[2]
        features = sys.argv[3].split(',') if sys.argv[3] and sys.argv[3] != 'None' else None
        time_limit = int(sys.argv[4])
        user_metric = sys.argv[5].lower().replace('-score', '').replace(' ', '')
        task_type = sys.argv[6].lower() 
        job_id = sys.argv[7] if len(sys.argv) > 7 else "default"

        # 2. Load Data
        df = pd.read_csv(dataset_path)

        if df.columns[0].startswith('Unnamed') or df.columns[0] == '':
            df = df.drop(df.columns[0], axis=1)

        if features:
            features = [f.strip() for f in features if f.strip() != '']
            X = df[features].copy()
        else:
            X = df.drop(columns=[target_column]).copy()
        
        y = df[target_column].copy()

        # --- Η ΔΙΟΡΘΩΣΗ ΓΙΑ ΤΟ STRINGDTYPE ---
        # Μετατρέπουμε ρητά το X σε object type για να μην υπάρχουν StringDtypes
        X = X.astype(object) 

        # 3. Categorical Data Handling
        # Χρησιμοποιούμε έναν LabelEncoder ανά στήλη και τους αποθηκεύουμε
        label_encoders = {} 
        for col in X.columns:
            if X[col].dtype == 'object' or str(X[col].dtype).startswith('string'):
                le = LabelEncoder()
                # Γεμίζουμε τα κενά με "Unknown" για να μην κρασάρει ο encoder
                X[col] = X[col].astype(str).replace('nan', 'Unknown')
                X[col] = le.fit_transform(X[col])
                label_encoders[col] = le
        
        # 3.5 Handling Target (y)
        target_encoder = None
        if task_type == 'classification':
            y = y.astype(str).replace('nan', 'Unknown')
            target_encoder = LabelEncoder()
            y = target_encoder.fit_transform(y)
        else:
            y = pd.to_numeric(y, errors='coerce').fillna(0)

        # 4. Data Conversion
        # Μετατροπή σε float32 για να είναι σίγουρα συμβατό με FLAML
        X_values = X.values.astype(np.float32)
        y_values = np.array(y).ravel()

        # 5. Setup FLAML
        automl = AutoML()
        
        # 5. Metric Mapping (FLAML-safe)
        num_classes = len(np.unique(y_values))
        
        if task_type == 'classification':
            if num_classes > 2:
                # Για πολυταξικά (π.χ. Iris), το macro_f1 είναι ο καλύτερος οδηγός
                metric_mapping = {
                    'accuracy': 'accuracy', 
                    'f1': 'macro_f1', 
                    'precision': 'macro_f1', 
                    'recall': 'macro_f1'
                }
            else:
                # Για Binary
                metric_mapping = {
                    'accuracy': 'accuracy', 
                    'f1': 'f1', 
                    'precision': 'precision', 
                    'recall': 'recall'
                }
            search_metric = metric_mapping.get(user_metric, 'accuracy')
        else:
            # Για regression
            search_metric = user_metric if user_metric in ['rmse', 'mae', 'mse', 'r2'] else 'rmse'

        settings = {
            "time_budget": time_limit,
            "metric": search_metric,
            "task": task_type,
            "n_splits": 3,
            "seed": 123,
            "verbose": 0
        }
        # 6. Train
        automl.fit(X_train=X_values, y_train=y_values, **settings)

        # 7. Αποθήκευση Μοντέλου ΚΑΙ των Encoders
        # Πρέπει να αποθηκεύσουμε και τους encoders για να ξέρουμε τα ονόματα στο Predict
        model_data = {
            "model": automl,
            "encoders": label_encoders,
            "target_encoder": target_encoder,
            "features": list(X.columns),
            'target_col': target_column
        }
        
        model_filename = f"model_job_{job_id}.joblib"
        joblib.dump(model_data, model_filename)

        # 8. Output & Scoring
        y_pred = automl.predict(X_values)
        
        if y_pred is None:
            raise Exception("FLAML failed to produce predictions (y_pred is None).")

        if task_type == 'classification':
            # Υπολογίζουμε τη μετρική που ζήτησε ο χρήστης με weighted μέσο όρο
            if user_metric == 'f1':
                final_score = f1_score(y_values, y_pred, average='weighted', zero_division=0)
            elif user_metric == 'precision':
                final_score = precision_score(y_values, y_pred, average='weighted', zero_division=0)
            elif user_metric == 'recall':
                final_score = recall_score(y_values, y_pred, average='weighted', zero_division=0)
            else:
                final_score = accuracy_score(y_values, y_pred)
        else:
            # REGRESSION: Εδώ υπολογίζουμε ΑΥΤΟ ΠΟΥ ΖΗΤΗΣΕ Ο ΧΡΗΣΤΗΣ
            if user_metric == 'mse':
                final_score = mean_squared_error(y_values, y_pred)
            elif user_metric == 'rmse':
                final_score = np.sqrt(mean_squared_error(y_values, y_pred))
            elif user_metric == 'mae':
                final_score = mean_absolute_error(y_values, y_pred)
            elif user_metric == 'r2':
                final_score = r2_score(y_values, y_pred)
            else:
                # Fallback αν κάτι πάει λάθος
                final_score = mean_squared_error(y_values, y_pred)

        print(json.dumps({
            "status": "success",
            "best_model": model_filename,
            "best_score": round(float(final_score), 4),
            "best_algorithm": automl.best_estimator,
            "framework": "flaml"
        }))

    except Exception as e:
        import traceback
        print(json.dumps({"status": "failed", "error": str(e), "traceback": traceback.format_exc()}))

if __name__ == "__main__":
    run_flaml()