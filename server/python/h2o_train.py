import h2o
from h2o.automl import H2OAutoML
import sys
import json
import os
import shutil
import numpy as np
import pandas as pd
from sklearn.metrics import (accuracy_score, f1_score, precision_score, recall_score, 
                             mean_squared_error, mean_absolute_error, r2_score)
from sklearn.model_selection import train_test_split
import time

def run_h2o():
    try:
        # 1. Arguments
        dataset_path = sys.argv[1]
        target_column = sys.argv[2]
        features = sys.argv[3].split(',') if sys.argv[3] and sys.argv[3] != 'None' else None
        time_limit = int(sys.argv[4])
        user_metric = sys.argv[5].lower().replace('-score', '').replace(' ', '')
        task_type = sys.argv[6].lower()
        job_id = sys.argv[7] if len(sys.argv) > 7 else "default"

        # 2. Init H2O
        h2o.init(nthreads=-1, max_mem_size="4G")
        h2o.no_progress()

        # 3. Load Data & Clean
        df_tmp = pd.read_csv(dataset_path)

        if df_tmp.columns[0].startswith('Unnamed') or df_tmp.columns[0] == '':
            df_tmp = df_tmp.drop(df_tmp.columns[0], axis=1)

        # Για Regression, βεβαιωνόμαστε ότι ο στόχος είναι NUMERIC πριν το split
        if task_type != 'classification':
            df_tmp[target_column] = pd.to_numeric(df_tmp[target_column], errors='coerce')
            df_tmp = df_tmp.dropna(subset=[target_column])

        X = df_tmp.drop(columns=[target_column])
        y = df_tmp[target_column]

        if task_type == 'classification':
            X_train, X_test, y_train, y_test = train_test_split(
                X, y, test_size=0.2, random_state=42, stratify=y
            )
        else:
            X_train, X_test, y_train, y_test = train_test_split(
                X, y, test_size=0.2, random_state=42
            )

        train_df = X_train.copy()
        train_df[target_column] = y_train

        test_df = X_test.copy()
        test_df[target_column] = y_test

        # Μετατροπή σε H2O Frame
        train = h2o.H2OFrame(train_df)
        test = h2o.H2OFrame(test_df)

        # ΔΙΟΡΘΩΣΗ: Μετατροπή σε factor για classification στα σωστά frames
        if task_type == 'classification':
            train[target_column] = train[target_column].asfactor()
            test[target_column] = test[target_column].asfactor()

        # 4. Train Configuration
        if task_type == 'classification':
            if user_metric in ['recall', 'f1', 'precision']:
                sort_metric = 'mean_per_class_error'
            else:
                sort_metric = 'logloss'
        else:
            if user_metric == 'mae':
                sort_metric = 'mae'
            elif user_metric == 'rmse':
                sort_metric = 'rmse'
            elif user_metric == 'mse':
                sort_metric = 'mse'
            else:
                sort_metric = 'rmse'

        aml = H2OAutoML(max_runtime_secs=time_limit, seed=123, sort_metric=sort_metric)
        
        if features:
            features = [f.strip() for f in features if f.strip() != '']

        start_time = time.time()
        aml.train(x=features, y=target_column, training_frame=train)
        training_time = time.time() - start_time

        leader = aml.leader
        if leader is None:
            raise Exception("No model trained within the time limit.")
            
        winner_algo = leader.algo

        # 5. Scoring με Scikit-Learn
        preds_h2o = aml.leader.predict(test)
        y_true_df = test[target_column].as_data_frame()
        y_pred_df = preds_h2o.as_data_frame()
        
        y_true_raw = y_true_df.iloc[:, 0]
        y_pred_raw = y_pred_df.iloc[:, 0]
        
        if task_type == 'classification':
            y_true_clean = y_true_raw.astype(str)
            y_pred_clean = y_pred_raw.astype(str)
            if user_metric == 'f1':
                final_score = f1_score(y_true_clean, y_pred_clean, average='weighted', zero_division=0)
            elif user_metric == 'precision':
                final_score = precision_score(y_true_clean, y_pred_clean, average='weighted', zero_division=0)
            elif user_metric == 'recall':
                final_score = recall_score(y_true_clean, y_pred_clean, average='weighted', zero_division=0)
            else:
                final_score = accuracy_score(y_true_clean, y_pred_clean)
        else:
            y_true_num = pd.to_numeric(y_true_raw, errors='coerce')
            y_pred_num = pd.to_numeric(y_pred_raw, errors='coerce')

            if y_true_num.mean() > 10 and y_pred_num.mean() < 5:
                print("DEBUG: H2O seems to be in log-scale. Applying exp...")
                y_pred_num = np.exp(y_pred_num)

            print(f"DEBUG - Actual Mean: {y_true_num.mean():.4f}") 
            print(f"DEBUG - Predict Mean: {y_pred_num.mean():.4f}")

            mask = np.isfinite(y_true_num) & np.isfinite(y_pred_num)
            y_t = y_true_num[mask]
            y_p = y_pred_num[mask]

            if user_metric == 'mae':
                final_score = mean_absolute_error(y_t, y_p)
            elif user_metric == 'rmse':
                final_score = np.sqrt(mean_squared_error(y_t, y_p))
            elif user_metric == 'r2':
                final_score = r2_score(y_t, y_p)
            else:
                final_score = mean_squared_error(y_t, y_p)

        # 6. Save & Zip
        script_dir = os.path.dirname(os.path.abspath(__file__))
        model_dir = os.path.join(script_dir, f"h2o_export_{job_id}")
        
        if os.path.exists(model_dir): 
            shutil.rmtree(model_dir)
        os.makedirs(model_dir)

        try:
            model_path = h2o.save_model(model=leader, path=model_dir, force=True)
        except Exception as save_err:
            raise Exception(f"H2O save_model failed: {str(save_err)}")
        
        info = {
            "target_col": target_column,
            "features": features if features else [c for c in df_tmp.columns if c != target_column]
        }
        with open(os.path.join(model_dir, "h2o_info.json"), "w") as f:
            json.dump(info, f)

        model_name_unique = f"model_job_{job_id}_h2o"
        model_zip_full_path = os.path.join(script_dir, model_name_unique)
        
        shutil.make_archive(model_zip_full_path, 'zip', root_dir=model_dir)
        final_zip_name = f"{model_name_unique}.zip"
        shutil.rmtree(model_dir)

        # 7. Output
        print(json.dumps({
            "status": "success",
            "best_model": final_zip_name,
            "best_score": round(float(final_score), 4),
            "best_algorithm": winner_algo,
            "framework": "h2o",
            "training_time": round(training_time, 3)
        }))

    except Exception as e:
        print(json.dumps({"status": "failed", "error": str(e)}))
    finally:
        try:
            h2o.cluster().shutdown()
        except:
            pass

if __name__ == "__main__":
    run_h2o()