import sys
import pandas as pd
import joblib
import os
import json
import traceback
import numpy as np
import re
import shutil
import zipfile

try:
    from supervised.automl import AutoML
except ImportError:
    pass

def run_predict():
    h2o_started = False
    extract_path = None
    target_col_name = None 
    
    try:
        if len(sys.argv) < 5:
            raise Exception("Missing arguments.")

        model_path = os.path.normpath(sys.argv[1])
        input_csv = os.path.normpath(sys.argv[2])
        output_csv = os.path.normpath(sys.argv[3])
        framework = sys.argv[4].lower()

        df = pd.read_csv(input_csv)
        
        # --- FRAMEWORK LOGIC & DYNAMIC TARGET DETECTION ---
        
        if framework == "h2o":
            import h2o
            h2o.init(nthreads=-1, max_mem_size="2G")
            h2o_started = True
            
            extract_path = os.path.join(os.path.dirname(model_path), f"temp_h2o_{os.getpid()}")
            if os.path.exists(extract_path): shutil.rmtree(extract_path)
            with zipfile.ZipFile(model_path, 'r') as zip_ref:
                zip_ref.extractall(extract_path)
            
            info_path = os.path.join(extract_path, "h2o_info.json")
            trained_features = []
            if os.path.exists(info_path):
                with open(info_path, 'r') as f:
                    info = json.load(f)
                    target_col_name = info.get('target_col')
                    trained_features = info.get('features', [])

            model_files = [f for f in os.listdir(extract_path) if not f.endswith('.json')]
            if not model_files: raise Exception("No H2O model file found in zip.")
            model = h2o.load_model(os.path.join(extract_path, model_files[0]))

            X_input = df.copy()
            if target_col_name and target_col_name in X_input.columns:
                X_input = X_input.drop(columns=[target_col_name])
            
            X_input = X_input.loc[:, ~X_input.columns.str.contains('^Unnamed|^index', case=False)]

            if len(X_input.columns) >= len(trained_features):
                X_final_pd = X_input.iloc[:, :len(trained_features)]
                X_final_pd.columns = trained_features
            else:
                X_final_pd = X_input

            h2o_X = h2o.H2OFrame(X_final_pd)

            preds_h2o = model.predict(h2o_X)
            preds_pd = preds_h2o.as_data_frame()

            if preds_pd.empty:
                raise Exception("H2O predictions are empty. Check data types.")

            predictions = preds_pd.iloc[:, 0].values

        elif framework == "mljar":
            extract_path = os.path.join(os.path.dirname(model_path), f"temp_mljar_{os.getpid()}")
            if os.path.exists(extract_path): shutil.rmtree(extract_path)
            with zipfile.ZipFile(model_path, 'r') as zip_ref:
                zip_ref.extractall(extract_path)
            
            # 1. Διάβασμα του custom info για να βρούμε τον στόχο
            info_path = os.path.join(extract_path, "mljar_info.json")
            if os.path.exists(info_path):
                with open(info_path, 'r') as f:
                    info = json.load(f)
                    target_col_name = info.get('target_col')

            # 2. Διάβασμα των στηλών εκπαίδευσης από το data_info.json του MLJAR
            data_info_path = os.path.join(extract_path, "data_info.json")
            trained_features = []
            if os.path.exists(data_info_path):
                with open(data_info_path, 'r') as f:
                    d_info = json.load(f)
                    # Το MLJAR κρατάει ΕΔΩ όλες τις στήλες (μαζί με το target)
                    trained_features = d_info.get('columns', [])

            # ΚΡΑΤΑΜΕ ΜΟΝΟ ΤΑ FEATURES (Αφαιρούμε τον στόχο αν υπάρχει στη λίστα)
            if target_col_name and target_col_name in trained_features:
                trained_features = [f for f in trained_features if f != target_col_name]

            # 3. ΠΡΟΕΤΟΙΜΑΣΙΑ X (Αυστηρή ευθυγράμμιση βάσει ονόματος ΚΑΙ σειράς)
            # Καθαρίζουμε το input DataFrame από Unnamed στήλες
            X_input = df.copy()
            X_input = X_input.loc[:, ~X_input.columns.str.contains('^Unnamed|^index', case=False)]

            # Χτίζουμε το X_final με τη σωστή σειρά των trained_features
            X_final = pd.DataFrame(index=X_input.index)
            for col in trained_features:
                if col in X_input.columns:
                    X_final[col] = X_input[col]
                else:
                    X_final[col] = 0  # fallback αν λείπει κάτι

            # Εξασφαλίζουμε ότι η σειρά των στηλών είναι ΕΚΑΤΟ ΤΟΙΣ ΕΚΑΤΟ αυτή που θέλει το MLJAR
            X_final = X_final[trained_features]

            # 4. Φόρτωση AutoML και Πρόβλεψη
            automl = AutoML(results_path=extract_path)
            preds_raw = automl.predict(X_final)
            
            if hasattr(preds_raw, 'values'):
                preds_np = preds_raw.values
            else:
                preds_np = np.array(preds_raw)

            preds_flat = np.array(preds_np).flatten()
            
            # 5. Αποκωδικοποίηση αν είναι Classification
            encoder_path = os.path.join(extract_path, "mljar_target_encoder.joblib")
            if os.path.exists(encoder_path):
                try:
                    target_encoder = joblib.load(encoder_path)
                    preds_int = np.array([int(round(float(v))) for v in preds_flat])
                    predictions = target_encoder.inverse_transform(preds_int)
                except Exception as enc_err:
                    print(f"[!] Target encoder decoding failed: {enc_err}. Using raw predictions.")
                    predictions = preds_flat
            else:
                predictions = preds_flat

        else: # FLAML / Joblib
            data_bundle = joblib.load(model_path)
            
            if isinstance(data_bundle, dict) and 'model' in data_bundle:
                automl_model = data_bundle['model']
                feature_encoders = data_bundle.get('encoders', {})
                target_encoder = data_bundle.get('target_encoder', None)
                trained_features = data_bundle.get('features', [])
                target_col_name = data_bundle.get('target_col')

                X_processed = pd.DataFrame(index=df.index)
                for col in trained_features:
                    if col in df.columns:
                        if col in feature_encoders:
                            le = feature_encoders[col]
                            val_str = df[col].astype(str).replace('nan', 'Unknown')
                            X_processed[col] = val_str.map(lambda s: le.transform([s])[0] if s in le.classes_ else -1)
                        else:
                            X_processed[col] = pd.to_numeric(df[col], errors='coerce').fillna(0)
                    else:
                        X_processed[col] = 0
                
                raw_predictions = automl_model.predict(X_processed.values.astype(np.float32))
                predictions = target_encoder.inverse_transform(raw_predictions.astype(int)) if target_encoder else raw_predictions
            else:
                predictions = data_bundle.predict(df)

        # --- SMART METRICS SETUP (FIXED FOR MLJAR CASE-SENSITIVITY) ---
        has_target = False
        actual_target_in_file = None
        
        if target_col_name:
            # Μετατρέπουμε όλες τις στήλες του αρχείου σε lowercase για τον έλεγχο
            cols_lowercase = [str(c).lower().strip() for c in df.columns]
            target_lower = str(target_col_name).lower().strip()
            
            if target_lower in cols_lowercase:
                # Βρίσκουμε το πραγματικό όνομα της στήλης όπως είναι γραμμένο στο df (π.χ. CLASS)
                matched_idx = cols_lowercase.index(target_lower)
                actual_target_in_file = df.columns[matched_idx]
                has_target = True
                print(f"[+] Target column detected in input file as: '{actual_target_in_file}'")

        # --- POST-PROCESSING ---
        clean_preds = []
        for p in np.array(predictions).flatten():
            s = str(p).strip()
            if not s.replace('.', '', 1).isdigit() and not (s.startswith('-') and s[1:].replace('.', '', 1).isdigit()):
                s = re.split(r'[\n\r]+', s)[0].strip()
            clean_preds.append(s)

        df['prediction_result'] = clean_preds

        # --- METRICS CALCULATION ---
        metrics = {}
        if has_target:
            try:
                from sklearn.metrics import (accuracy_score, f1_score, precision_score, recall_score,
                                             mean_squared_error, mean_absolute_error, r2_score)
                y_true = df[actual_target_in_file]
                try:
                    y_true_num = pd.to_numeric(y_true, errors='raise')
                    y_pred_num = pd.to_numeric(pd.Series(clean_preds), errors='raise')
                    is_regression = True
                except:
                    is_regression = False

                if is_regression:
                    mse = mean_squared_error(y_true_num, y_pred_num)
                    metrics = {
                        "type": "regression",
                        "target_detected": actual_target_in_file,
                        "RMSE": round(float(np.sqrt(mse)), 4),
                        "MSE": round(float(mse), 4),
                        "MAE": round(float(mean_absolute_error(y_true_num, y_pred_num)), 4),
                        "R2 Score": round(float(r2_score(y_true_num, y_pred_num)), 4)
                    }
                else:
                    y_true_str = y_true.astype(str).str.strip()
                    y_pred_str = pd.Series(clean_preds).astype(str).str.strip()
                    metrics = {
                        "type": "classification",
                        "target_detected": actual_target_in_file,
                        "Accuracy": f"{round(float(accuracy_score(y_true_str, y_pred_str)) * 100, 2)}%",
                        "F1-Score": round(float(f1_score(y_true_str, y_pred_str, average='weighted', zero_division=0)), 4),
                        "Precision": round(float(precision_score(y_true_str, y_pred_str, average='weighted', zero_division=0)), 4),
                        "Recall": round(float(recall_score(y_true_str, y_pred_str, average='weighted', zero_division=0)), 4)
                    }
            except Exception as me:
                metrics = {"error": f"Metrics failed: {str(me)}"}

        df.to_csv(output_csv, index=False)
        print(json.dumps({"status": "SUCCESS", "metrics": metrics, "message": "Inference completed"}))

    except Exception as e:
        print(json.dumps({"status": "ERROR", "message": str(e), "traceback": traceback.format_exc()}))
        sys.exit(1)
    finally:
        if extract_path and os.path.exists(extract_path): shutil.rmtree(extract_path)

if __name__ == "__main__":
    run_predict()