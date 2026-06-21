import mysql.connector
import time
import subprocess
import json
import os
import shutil
import sys

# Ρυθμίσεις Βάσης
db_config = {
    "host": "localhost",
    "user": "",
    "password": "",
    "database": ""
}

# Paths για Linux
current_dir = os.path.dirname(os.path.abspath(__file__))
VENV_PYTHON = os.path.join(current_dir, "venv", "bin", "python3")
MODELS_DESTINATION = os.path.abspath(os.path.join(current_dir, "../../models"))

def get_job():
    try:
        db = mysql.connector.connect(**db_config)
        cursor = db.cursor(dictionary=True)
        cursor.execute("SELECT * FROM jobs WHERE status = 'pending' ORDER BY created_at ASC LIMIT 1")
        job = cursor.fetchone()
        db.close()
        return job
    except Exception as e:
        print(f"[!] Database Error (get_job): {e}")
        return None

def update_job_status(job_id, status, results=None):
    try:
        db = mysql.connector.connect(**db_config)
        cursor = db.cursor()
        if results:
            cursor.execute("UPDATE jobs SET status = %s, results_json = %s WHERE id = %s", 
                           (status, json.dumps(results), job_id))
        else:
            cursor.execute("UPDATE jobs SET status = %s WHERE id = %s", (status, job_id))
        db.commit()
        db.close()
    except Exception as e:
        print(f"[!] Database Error (update_status): {e}")

# ......

def run_training():
    print(f"--- Worker Started: Waiting for jobs ---")
    
    while True:
        job = get_job()
        if job:
            job_id = job['id']
            user_id = job.get('user_id') if job.get('user_id') else 1
            
            print(f"[*] Processing Job ID: {job_id}")
            update_job_status(job_id, 'processing')
            
            all_results = {}
            frameworks = job['selected_frameworks'].split(',')
            
            best_model_data = None
            is_minimizing = job['metric'].lower() in ['rmse', 'mse', 'mae', 'logloss']
            best_score = float('inf') if is_minimizing else -float('inf')

            for idx, fw in enumerate(frameworks):
                fw = fw.strip()
                script_path = os.path.normpath(os.path.join(current_dir, f"{fw}_train.py"))
                dataset_full_path = os.path.normpath(os.path.abspath(os.path.join(current_dir, "../../uploads/datasets/", job['dataset_path'])))

                print(f"  > [{idx+1}/{len(frameworks)}] Running {fw}...")

                try:
                    process = subprocess.run([
                        VENV_PYTHON, script_path, 
                        dataset_full_path, 
                        job['target_column'], 
                        job['selected_features'] if job['selected_features'] else "None",
                        str(job['time_limit']), 
                        str(job['metric']), 
                        job['task_type'],
                        str(job_id)
                    ], capture_output=True, text=True, encoding='utf-8')

                    stdout_str = process.stdout.strip()
                    start_idx = stdout_str.rfind('{')
                    end_idx = stdout_str.rfind('}') + 1
                    
                    if start_idx != -1 and end_idx != -1:
                        res_data = json.loads(stdout_str[start_idx:end_idx])
                        res_data['metric_used'] = job['metric']
                        all_results[fw] = res_data

                        if res_data.get('status') == 'success':
                            print(f"    ⏱ {fw} training time: {res_data['training_time']} sec")
                            current_score = float(res_data['best_score'])
                            is_better = (current_score < best_score) if is_minimizing else (current_score > best_score)
                            
                            if is_better:
                                # Αν υπήρχε προηγούμενο "καλύτερο" μοντέλο άλλου framework, το σβήνουμε
                                if best_model_data:
                                    old_file = os.path.join(current_dir, best_model_data['best_model'])
                                    if os.path.exists(old_file): os.remove(old_file)
                                
                                best_score = current_score
                                best_model_data = res_data
                                best_model_data['framework_name'] = fw
                                print(f"    [+] New Best Score: {best_score} ({fw})")
                            else:
                                # Αν το τρέχον framework είναι χειρότερο, σβήνουμε το αρχείο του αμέσως
                                trash_file = os.path.join(current_dir, res_data['best_model'])
                                if os.path.exists(trash_file): os.remove(trash_file)
                        else:
                            print(f"    [X] {fw} failed: {res_data.get('error')}")

                except Exception as e:
                    all_results[fw] = {"status": "failed", "error": str(e)}

            # --- ΤΕΛΙΚΗ ΦΑΣΗ: Μεταφορά και Μετονομασία Νικητή ---
            if best_model_data:
                try:
                    os.makedirs(MODELS_DESTINATION, exist_ok=True)
                    
                    temp_filename = best_model_data['best_model']
                    source_zip = os.path.abspath(temp_filename)
                    
                    extension = os.path.splitext(temp_filename)[1]
                    final_filename = f"model_job_{job_id}{extension}"
                    dest_zip = os.path.join(MODELS_DESTINATION, final_filename)
                    
                    # Ορισμός του νικητή framework για χρήση παρακάτω
                    winner_fw = best_model_data['framework_name']
                    
                    if os.path.exists(source_zip):
                        shutil.move(source_zip, dest_zip)
                        os.chmod(dest_zip, 0o644)
                        
                        # Ενημέρωση του ονόματος αρχείου στο all_results
                        if winner_fw in all_results:
                            all_results[winner_fw]['best_model'] = final_filename
                        
                        print(f"    [V] Model moved & renamed to: {final_filename}")

                    # Αποθήκευση στη Βάση (trained_models)
                    db = mysql.connector.connect(**db_config)
                    cursor = db.cursor()
                    winner_algorithm = best_model_data.get('best_algorithm', 'Unknown')
                    
                    sql = """INSERT INTO trained_models 
                             (user_id, dataset_id, dataset_name, target_column, framework, algorithm, score, metric_used, task_type, model_path) 
                             VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"""
                    
                    values = (
                        user_id, 
                        job['dataset_id'],
                        os.path.basename(job['dataset_path']),
                        job['target_column'], 
                        winner_fw, 
                        winner_algorithm,
                        best_model_data['best_score'], 
                        job['metric'],      
                        job['task_type'],   
                        final_filename
                    )
                    
                    cursor.execute(sql, values)
                    # Εδώ ορίζεται το new_id
                    inserted_id = cursor.lastrowid 
                    db.commit()
                    db.close()
                    
                    # Τώρα προσθέτουμε το ID στο all_results για το modal
                    if winner_fw in all_results:
                        all_results[winner_fw]['model_id'] = inserted_id
                    
                    print(f"    [+] Saved to DB with ID: {inserted_id}")

                except Exception as e:
                    print(f"[!] Error in final stage: {e}")

            # Τελικό update στο table 'jobs'
            update_job_status(job_id, 'completed', all_results)
            print(f"[V] Job {job_id} Finished.\n")
            
        time.sleep(5)

if __name__ == "__main__":
    run_training()
